<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;


class ShopController extends Controller
{
    public function index(Request $request)
    {
        $order = $request->query('order', -1);
        $f_categories = $request->query('categories', '');
        $sex = $request->query('sex', '');
        $priceRange = $request->query('priceRange', '');

        if ($priceRange !== '' && $order == -1) {
            $order = 3;
        }

        $categories = Category::withCount([
            'products as direct_products_count',
            'children' => function ($query) {
                $query->withCount('products');
            }
        ])
            ->whereNull('parent_id')
            ->orderBy('name', 'ASC')
            ->get()
            ->map(function ($category) {
                $childProductsCount = $category->children->sum('products_count');
                $category->total_products = $category->direct_products_count + $childProductsCount;
                return $category;
            });

        $selected_categories = $f_categories ? explode(',', $f_categories) : [];

        $expanded_categories = collect($selected_categories)
            ->map(function ($category_id) {
                return Category::find($category_id);
            })
            ->filter()
            ->flatMap(function ($category) {
                return [$category->id];
            })
            ->unique()
            ->toArray();

        $productsQuery = Product::where('archived', false)
            ->with(['category', 'attributeValues'])
            ->when(!empty($expanded_categories), function ($query) use ($expanded_categories) {
                return $query->whereIn('category_id', $expanded_categories);
            }, function ($query) use ($f_categories) {
                if ($f_categories === '') {
                    $query->whereNotNull('category_id');
                }
            })
            ->when($sex !== '', function ($query) use ($sex) {
                return $query->where('sex', $sex);
            });

        $priceRangeQuery = function ($query) use ($priceRange) {
            if ($priceRange === '') return;

            $priceConditions = [
                '0-50' => [0, 50],
                '50-100' => [50, 100],
                '100-200' => [100, 200],
                '200-500' => [200, 500],
            ];

            if (isset($priceConditions[$priceRange])) {
                [$min, $max] = $priceConditions[$priceRange];
                $query->where(function ($q) use ($min, $max) {
                    $q->where(function ($q2) use ($min, $max) {
                        $q2->where('price', '>', 0)->whereBetween('price', [$min, $max]);
                    })->orWhereHas('attributeValues', function ($attrQuery) use ($min, $max) {
                        $attrQuery->whereBetween('price', [$min, $max]);
                    });
                });
            } elseif ($priceRange === '500+') {
                $query->where(function ($q) {
                    $q->where('price', '>', 500)
                        ->orWhereHas('attributeValues', function ($attrQuery) {
                            $attrQuery->where('price', '>', 500);
                        });
                });
            }
        };

        $productsQuery->where($priceRangeQuery);

        $orderQueries = [
            1 => ['created_at', 'DESC'],
            2 => ['created_at', 'ASC'],
        ];

        if ($order == 5) {
            $bestSellingProductIds = DB::table('order_items')
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                ->groupBy('product_id')
                ->orderBy('total_sold', 'DESC')
                ->pluck('product_id');

            $products = Product::where('archived', false)
                ->with(['category', 'attributeValues'])
                ->whereIn('id', $bestSellingProductIds)
                ->when(!empty($expanded_categories), function ($query) use ($expanded_categories) {
                    return $query->whereIn('category_id', $expanded_categories);
                })
                ->when($sex !== '', function ($query) use ($sex) {
                    return $query->where('sex', $sex);
                })
                ->where($priceRangeQuery)
                ->orderByRaw('FIELD(id, ' . $bestSellingProductIds->implode(',') . ')')
                ->paginate(9);
        } else {
            if ($order == 3) {
                $productsQuery->orderByRaw("
                COALESCE(
                    (SELECT MIN(price) FROM product_attribute_values 
                     WHERE product_attribute_values.product_id = products.id 
                     AND price IS NOT NULL),
                    products.price
                ) ASC
            ");
            } elseif ($order == 4) {
                $productsQuery->orderByRaw("
                COALESCE(
                    (SELECT MAX(price) FROM product_attribute_values 
                     WHERE product_attribute_values.product_id = products.id 
                     AND price IS NOT NULL),
                    products.price
                ) DESC
            ");
            } else {
                $productsQuery->orderBy(
                    $orderQueries[$order][0] ?? 'id',
                    $orderQueries[$order][1] ?? 'DESC'
                );
            }

            $products = $productsQuery->paginate(9);
        }

        if ($request->ajax()) {
            return response()->json([
                'products' => view('partials.products-list', compact('products'))->render(),
                'pagination' => view('partials._products-pagination', compact('products'))->render(),
            ]);
        }

        return view('shop', compact('products', 'order', 'categories', 'f_categories', 'sex', 'priceRange'));
    }


    public function product_details($product_slug)
    {
        $product = Product::with(['attributeValues.productAttribute'])
            ->where('slug', $product_slug)
            ->firstOrFail();

        $groupedAttributes = [];
        $uniqueAttributes = [];
        foreach ($product->attributeValues as $value) {
            $attributeId = $value->product_attribute_id;
            $groupedAttributes[$attributeId][] = $value;
            if (!isset($uniqueAttributes[$attributeId])) {
                $uniqueAttributes[$attributeId] = $value->productAttribute;
            }
        }

        $rproducts = Product::where('slug', '<>', $product_slug)->take(8)->get();
        return view('details', compact('product', 'rproducts', 'groupedAttributes', 'uniqueAttributes'));
    }
}
