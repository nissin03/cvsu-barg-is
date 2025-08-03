<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductAttribute;


class ShopController extends Controller
{
    public function index(Request $request)
    {
        $o_column = "";
        $o_order = "";
        $order = $request->query('order') ? $request->query('order') : -1;
        $f_categories = $request->query('categories', '');
        $sex = $request->query('sex', '');

        switch($order) {
            case 1: $o_column = 'created_at'; $o_order = 'DESC'; break;
            case 2: $o_column = 'created_at'; $o_order = 'ASC'; break;
            case 3: $o_column = 'price'; $o_order = 'ASC'; break;
            case 4: $o_column = 'price'; $o_order = 'DESC'; break;
            default: $o_column = 'id'; $o_order = 'DESC'; break;
        }

        $categories = Category::with(['children', 'products'])
            ->whereNull('parent_id')
            ->orderBy('name', 'ASC')
            ->get()
            ->map(function ($category) {
                $category->total_products = $category->products->count() + 
                    $category->children->sum(function ($child) {
                        return $child->products->count();
                    });
                return $category;
            });

        $selected_categories = $f_categories ? explode(',', $f_categories) : [];
        $expanded_categories = [];

        foreach ($selected_categories as $category_id) {
            $category = Category::find($category_id);
            if ($category) {
                $expanded_categories[] = $category_id;
                if (is_null($category->parent_id)) {
                    $expanded_categories = array_merge(
                        $expanded_categories,
                        $category->children->pluck('id')->toArray()
                    );
                }
            }
        }
        $expanded_categories = array_unique($expanded_categories);

        $products = Product::where(function ($query) use ($expanded_categories, $f_categories) {
                if (!empty($expanded_categories)) {
                    $query->whereIn('category_id', $expanded_categories);
                } elseif ($f_categories === '') {
                    $query->whereNotNull('category_id');
                }
            })
            ->when($sex !== '', function ($query) use ($sex) {
                return $query->where('sex', $sex);
            })
            ->orderBy($o_column, $o_order)
            ->paginate(9);

        if ($request->ajax()) {
            return view('partials.products-list', compact('products')); 
        }

        return view('shop', compact('products', 'order', 'categories', 'f_categories', 'sex'));
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
