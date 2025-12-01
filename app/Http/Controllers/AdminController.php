<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\User;
use App\Models\Order;
use App\Models\Slide;
use App\Models\Course;
use App\Models\Rental;
use App\Models\College;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use App\Models\MonthName;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Mail\ReplyToContact;
use App\Models\ContactReply;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\TimeSlotHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductAttribute;
use App\Services\ImageProcessor;
use App\Notifications\StockUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Intervention\Image\Laravel\Facades\Image;
use App\Notifications\OrderCanceledNotification;


class AdminController extends Controller
{

    protected $imageProcessor;
    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }
    public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $yearRange = range($currentYear, $currentYear - 10);
        $products = $this->getLowStockProducts();
        $dashboardData = [$this->getDashboardSummary($currentYear)];

        $orders = Order::with(
            'user.course.college'
        )
            ->latest()
            ->take(10)
            ->get();

        $pageTitle = 'Admin Dashboard';

        return view('admin.index', compact(
            'orders',
            'yearRange',
            'pageTitle',
            'products',
            'dashboardData'
        ));
    }

    /**
     * API endpoint for dashboard data
     */
    public function getDashboardData(Request $request)
    {
        $view = $request->input('view', 'monthly'); // monthly, weekly, daily
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $week = $request->input('week', 1);

        switch ($view) {
            case 'weekly':
                return $this->getWeeklyData($year, $month);
            case 'daily':
                return $this->getDailyData($year, $month, $week);
            default:
                return $this->getMonthlyData($year);
        }
    }

    private function getMonthlyData($year)
    {
        // Get dashboard summary
        $dashboardData = $this->getDashboardSummary($year);

        // Get monthly breakdown
        $monthlyData = DB::select("
            SELECT M.id AS MonthNo, M.name AS MonthName,
                IFNULL(D.TotalAmount, 0) AS TotalAmount,
                IFNULL(D.TotalReservedAmount, 0) AS TotalReservedAmount,
                IFNULL(D.TotalPickedUpAmount, 0) AS TotalPickedUpAmount,
                IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount
            FROM month_names M
            LEFT JOIN (
                SELECT
                    MONTH(created_at) AS MonthNo,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status='reserved', total, 0)) AS TotalReservedAmount,
                    SUM(IF(status='pickedup', total, 0)) AS TotalPickedUpAmount,
                    SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount
                FROM Orders
                WHERE YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
            ) D ON D.MonthNo = M.id
            ORDER BY M.id
        ", [$year]);

        return response()->json([
            'view' => 'monthly',
            'year' => $year,
            'summary' => $dashboardData,
            'chartData' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'series' => [
                    [
                        'name' => 'Total',
                        'data' => collect($monthlyData)->pluck('TotalAmount')->toArray()
                    ],
                    [
                        'name' => 'Reserved',
                        'data' => collect($monthlyData)->pluck('TotalReservedAmount')->toArray()
                    ],
                    [
                        'name' => 'Picked Up',
                        'data' => collect($monthlyData)->pluck('TotalPickedUpAmount')->toArray()
                    ],
                    [
                        'name' => 'Canceled',
                        'data' => collect($monthlyData)->pluck('TotalCanceledAmount')->toArray()
                    ]
                ]
            ],
            'totals' => [
                'total' => collect($monthlyData)->sum('TotalAmount'),
                'reserved' => collect($monthlyData)->sum('TotalReservedAmount'),
                'pickedUp' => collect($monthlyData)->sum('TotalPickedUpAmount'),
                'canceled' => collect($monthlyData)->sum('TotalCanceledAmount')
            ]
        ]);
    }

    private function getWeeklyData($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Generate week ranges
        $weekRanges = $this->generateWeekRanges($startOfMonth, $endOfMonth);

        $weeklyData = [];
        $categories = [];

        foreach ($weekRanges as $week => [$startOfWeek, $endOfWeek]) {
            $data = DB::select("
                SELECT
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                    SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                FROM Orders
                WHERE created_at BETWEEN ? AND ?
            ", [$startOfWeek, $endOfWeek]);

            $result = $data[0] ?? (object)[
                'TotalAmount' => 0,
                'TotalReservedAmount' => 0,
                'TotalPickedUpAmount' => 0,
                'TotalCanceledAmount' => 0
            ];

            $weeklyData[] = $result;
            $categories[] = "Week " . $week;
        }

        return response()->json([
            'view' => 'weekly',
            'year' => $year,
            'month' => $month,
            'chartData' => [
                'categories' => $categories,
                'series' => [
                    [
                        'name' => 'Total',
                        'data' => collect($weeklyData)->pluck('TotalAmount')->toArray()
                    ],
                    [
                        'name' => 'Reserved',
                        'data' => collect($weeklyData)->pluck('TotalReservedAmount')->toArray()
                    ],
                    [
                        'name' => 'Picked Up',
                        'data' => collect($weeklyData)->pluck('TotalPickedUpAmount')->toArray()
                    ],
                    [
                        'name' => 'Canceled',
                        'data' => collect($weeklyData)->pluck('TotalCanceledAmount')->toArray()
                    ]
                ]
            ],
            'totals' => [
                'total' => collect($weeklyData)->sum('TotalAmount'),
                'reserved' => collect($weeklyData)->sum('TotalReservedAmount'),
                'pickedUp' => collect($weeklyData)->sum('TotalPickedUpAmount'),
                'canceled' => collect($weeklyData)->sum('TotalCanceledAmount')
            ]
        ]);
    }

    private function getDailyData($year, $month, $weekNumber)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $weekRanges = $this->generateWeekRanges($startOfMonth, $endOfMonth);

        if (!isset($weekRanges[$weekNumber])) {
            $weekNumber = 1;
        }

        [$startOfWeek, $endOfWeek] = $weekRanges[$weekNumber];

        $dailyData = DB::select("
            SELECT
                DAYOFWEEK(created_at) AS DayNo,
                DAYNAME(created_at) AS DayName,
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
            FROM Orders
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
            ORDER BY DayNo
        ", [$startOfWeek, $endOfWeek]);

        return response()->json([
            'view' => 'daily',
            'year' => $year,
            'month' => $month,
            'week' => $weekNumber,
            'chartData' => [
                'categories' => collect($dailyData)->pluck('DayName')->toArray(),
                'series' => [
                    [
                        'name' => 'Total',
                        'data' => collect($dailyData)->pluck('TotalAmount')->toArray()
                    ],
                    [
                        'name' => 'Reserved',
                        'data' => collect($dailyData)->pluck('TotalReservedAmount')->toArray()
                    ],
                    [
                        'name' => 'Picked Up',
                        'data' => collect($dailyData)->pluck('TotalPickedUpAmount')->toArray()
                    ],
                    [
                        'name' => 'Canceled',
                        'data' => collect($dailyData)->pluck('TotalCanceledAmount')->toArray()
                    ]
                ]
            ],
            'totals' => [
                'total' => collect($dailyData)->sum('TotalAmount'),
                'reserved' => collect($dailyData)->sum('TotalReservedAmount'),
                'pickedUp' => collect($dailyData)->sum('TotalPickedUpAmount'),
                'canceled' => collect($dailyData)->sum('TotalCanceledAmount')
            ]
        ]);
    }

    private function getDashboardSummary($year, $dateRange = null)
    {
        $query = "
            SELECT
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                COUNT(*) AS Total,
                SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp,
                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
            FROM Orders
            WHERE YEAR(created_at) = ?
        ";

        return DB::select($query, [$year])[0] ?? null;
    }

    private function generateWeekRanges($startOfMonth, $endOfMonth)
    {
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }
        return $weekRanges;
    }

    private function getLowStockProducts()
    {
        return Product::with(['category', 'attributeValues'])->get()->filter(function ($product) {
            $currentStock = $product->attributeValues->isNotEmpty()
                ? $product->attributeValues->sum('quantity')
                : $product->current_stock;
            return $currentStock <= $product->reorder_quantity;
        });
    }

    /**
     * Get available months for dropdowns
     */
    public function getAvailableMonths()
    {
        return response()->json([
            'months' => MonthName::orderBy('id')->get()
        ]);
    }

    /**
     * Get available weeks for a given month
     */
    public function getAvailableWeeks(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $weekRanges = $this->generateWeekRanges($startOfMonth, $endOfMonth);

        $weeks = [];
        foreach ($weekRanges as $weekNumber => $range) {
            $weeks[] = [
                'number' => $weekNumber,
                'label' => "Week {$weekNumber}"
            ];
        }

        return response()->json(['weeks' => $weeks]);
    }
    public function categories(Request $request)
    {
        $search = $request->input('search');
        $query = Category::whereNull('parent_id')->with('children');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        }

        $categories = $query->orderBy('id', 'DESC')->paginate(5)->withQueryString();
        if ($request->ajax()) {
            return response()->json([
                'categories' => view('partials._categories-table', compact('categories'))->render(),
                'pagination' => view('partials._categories-pagination', compact('categories'))->render()
            ]);
        }

        $pageTitle = 'Category Dashboard';
        return view('admin.categories', compact('categories', 'pageTitle'));
    }

    public function category_add()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        $pageTitle = 'Category Add Dashboard';
        return view('admin.category-add', compact('pageTitle', 'parentCategories'));
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ], [
            'name.required' => 'The category name is required.',
            'image.required' => 'The category image is required.',
            'image.max' => 'Please upload an image that is 2MB or smaller.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);

        $name = Str::title($request->name);
        $slug = Str::slug($request->name);
        if (Category::where('slug', $slug)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'A category with this name already exists. Please use a different name.']);
        }

        $category = new Category();
        $category->name = $name;
        $category->slug = $slug;
        $category->parent_id = $request->parent_id;

        $image = $request->file('image');
        $file_extention = $image->extension();
        $file_name = now()->timestamp . '.' . $file_extention;

        $this->imageProcessor->process($image, $file_name, [
            [
                'path' => public_path('uploads/categories'),
                'cover' => [300, 300, 'top']
            ]
        ]);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully!');
    }

    public function category_edit($id)
    {
        $category =  Category::find($id);
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->get();
        return view('admin.category-edit', compact('category', 'parentCategories'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $name = Str::title($request->name);
        $slug = Str::slug($request->name);
        if (Category::where('slug', $slug)->where('id', '!=', $request->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'A category with this name already exists. Please use a different name.']);
        }

        $category = Category::find($request->id);
        $category->name = $name;
        $category->slug = $slug;
        $category->parent_id = $request->parent_id;

        if ($request->hasFile('image')) {
            $oldPath = public_path('uploads/categories/' . $category->image);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $image = $request->file('image');
            $file_extention = $image->extension();
            $file_name = now()->timestamp . '.' . $file_extention;

            $this->imageProcessor->process($image, $file_name, [
                [
                    'path' => public_path('uploads/categories'),
                    'cover' => [300, 300, 'top']
                ]
            ]);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been updated successfully!');
    }

    public function category_archive($id)
    {
        $category = Category::findOrFail($id);
        if ($category->children()->count() > 0) {
            $category->children()->each(function ($child) {
                $child->delete();
            });
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been archived successfully!');
    }

    public function archived_categories()
    {
        $archivedCategories = Category::onlyTrashed()
            ->whereNull('parent_id')
            ->with('archivedChildren')
            ->orderBy('id', 'DESC')
            ->paginate(5);

        $orphanedChildren = Category::onlyTrashed()
            ->whereNotNull('parent_id')
            ->whereHas('parent', function ($query) {
                $query->withTrashed()->whereNull('deleted_at');
            })
            ->orderBy('id', 'DESC')
            ->get();

        $pageTitle = 'Archived Categories';
        return view(
            'admin.archived-categories',
            compact('archivedCategories', 'pageTitle', 'orphanedChildren')
        );
    }

    public function restore_categories($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return redirect()->route('admin.archived-categories')->with('status', 'Category restored successfully!');
    }

    public function products(Request $request)
    {
        $archived = $request->query('archived', 0);
        $search = $request->input('search');
        $category = $request->input('category');
        $stockStatus = $request->input('stock_status');
        $sortBy = $request->input('sort_by', 'newest');

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        $query = Product::with([
            'category' => function ($query) {
                $query->with('parent');
            },
            'attributes',
            'attributeValues.productAttribute'
        ])
            ->where('archived', $archived);


        if ($search) {
            $isNumeric = is_numeric($search);
            $query->where(function ($q) use ($search, $isNumeric) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if ($isNumeric) {
                    $q->orWhere('quantity', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%");
                }
            });
        }

        // Category filter
        if ($category) {
            $query->where(function ($q) use ($category) {
                $selectedCategory = Category::find($category);
                if ($selectedCategory) {
                    $categoryIds = [$category];
                    if ($selectedCategory->children->isNotEmpty()) {
                        $categoryIds = array_merge(
                            $categoryIds,
                            $selectedCategory->children->pluck('id')->toArray()
                        );
                    }

                    $q->whereIn('category_id', $categoryIds);
                } else {
                    $q->where('category_id', $category);
                }
            });
        }

        if ($stockStatus) {
            $query->where('stock_status', $stockStatus);
        }


        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'ASC');
                break;
            case 'name_desc':
                $query->orderBy('name', 'DESC');
                break;
            case 'stock_low':
                $query->orderBy('quantity', 'ASC');
                break;
            case 'stock_high':
                $query->orderBy('quantity', 'DESC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'DESC');
                break;
        }

        $products = $query->paginate(10)->withQueryString();
        $count = $products->total();


        if ($request->ajax()) {
            return response()->json([
                'products' => view('partials._products-table', compact('products'))->render(),
                'pagination' => view('partials._products-pagination', compact('products'))->render(),
                'count' => $count
            ]);
        }

        return view('admin.products', compact('products', 'archived', 'categories'));
    }

    public function product_add()
    {

        $categories = Category::with('children')->whereNull('parent_id')->get();
        $productAttributes = ProductAttribute::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'productAttributes'));
    }

    public function product_store(Request $request)
    {
        $hasVariant = $request->filled('variant_name') &&
            $request->filled('product_attribute_id') &&
            $request->filled('variant_price') &&
            $request->filled('variant_quantity');

        $request->validate([
            'name' => 'required',
            'slug' => 'unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer',
            'featured' => 'required',
            'image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            // 'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'nullable|integer|min:0',
            'variant_description.*' => 'nullable|string|max:1000',
        ], [
            'image.required' => 'Main product image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'The image size must not exceed 5MB.',
            'images.*.image' => 'All gallery files must be images.',
            'images.*.mimes' => 'Gallery images must be files of type: png, jpg, jpeg.',
            'images.*.max' => 'Each gallery image must not exceed 5MB.',
            'category_id.integer' => 'Please select a valid category.',
            'sex.in' => 'Please select a valid gender category.',
            'reorder_quantity.required' => 'Reorder Quantity is required.',
            'outofstock_quantity.required' => 'Out of Stock Quantity is required.',
        ]);

        $outofstock_quantity = 0; // Default value

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->price = $hasVariant ? null : $request->price;
        $product->quantity = $hasVariant ? null : $request->quantity;
        $product->stock_status = $hasVariant ? 'instock' : 'outofstock';
        $product->featured = $request->featured;
        // $product->sex = $request->sex;
        $product->category_id = $request->category_id;
        $product->reorder_quantity = $request->reorder_quantity;
        $product->outofstock_quantity = $outofstock_quantity;

        $current_timestamp = now()->timestamp;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();

            $this->imageProcessor->process($image, $imageName, [
                ['path' => public_path('uploads/products'), 'cover' => [689, 689, 'center']],
                ['path' => public_path('uploads/products/thumbnails'), 'resize' => [300, 300]]
            ]);
            $product->image = $imageName;
        }
        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            $counter = 1;
            $gallery_arr = [];

            foreach ($files as $file) {
                $gext = $file->getClientOriginalExtension();

                if (in_array($gext, $allowedFileExtension)) {
                    $gFileName = $current_timestamp . '.' . $counter . '.' . $gext;

                    $this->imageProcessor->process($file, $gFileName, [
                        ['path' => public_path('uploads/products'), 'cover' => [689, 689, 'center']],
                        ['path' => public_path('uploads/products/thumbnails'), 'resize' => [300, 300]]
                    ]);

                    $gallery_arr[] = $gFileName;
                    $counter++;
                }
            }
            if (!empty($gallery_arr)) {
                $product->images = implode(',', $gallery_arr);
            }
        }
        $product->save();
        if ($hasVariant && is_array($request->variant_name)) {
            $attributeValues = [];
            foreach ($request->variant_name as $index => $variantName) {
                $attributeValues[] = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $variantName,
                    'description' => $request->variant_description[$index] ?? null,
                    'price' => $request->variant_price[$index],
                    'quantity' => $request->variant_quantity[$index],
                ];
            }
            foreach ($attributeValues as $value) {
                ProductAttributeValue::create($value);
            }
            $totalVariantQuantity = collect($attributeValues)->sum('quantity');

            if ($totalVariantQuantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($totalVariantQuantity <= $product->reorder_quantity && $totalVariantQuantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
            $product->save();
        } else {
            if ($product->quantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($product->quantity <= $product->reorder_quantity && $product->quantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
            $product->save();
        }

        return redirect()->route('admin.products')->with('status', 'Product has been added successfully!');
    }


    public function product_edit($id)
    {
        $product = Product::with('attributeValues')->findOrFail($id);
        // $categories = Category::select('id', 'name')->orderBy('name')->get();
        $categories = Category::with('children')->whereNull('parent_id')->get();
        $productAttributes = ProductAttribute::select('id', 'name')->orderBy('name')->get();

        $hasVariant = $product->attributeValues->isNotEmpty();
        $variantDetails = null;
        if ($hasVariant) {
            $variantDetails = $product->attributeValues->first();
        }

        return view('admin.product-edit', compact(
            'product',
            'categories',
            'productAttributes',
            'hasVariant',
            'variantDetails'
        ));
    }



    public function product_update(Request $request)
    {
        $product = Product::findOrFail($request->input('id'));
        $hasVariant = !empty($request->variant_name) && is_array($request->variant_name);

        $request->validate([
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer|min:0',
            'featured' => 'required|boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            // 'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'nullable|integer|min:0',
            'variant_description.*' => 'nullable|string|max:1000',
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'The image size must not exceed 5MB.',
            'images.*.image' => 'All gallery files must be images.',
            'images.*.mimes' => 'Gallery images must be files of type: png, jpg, jpeg.',
            'images.*.max' => 'Each gallery image must not exceed 5MB.',
            'category_id.integer' => 'Please select a valid category.',
            'reorder_quantity.required' => 'Reorder Quantity is required.',
            'outofstock_quantity.required' => 'Out of Stock Quantity is required.',
        ]);
        $previousStockStatus = $product->stock_status;
        $product->fill($request->except([
            'image',
            'images',
            'variant_name',
            'product_attribute_id',
            'variant_price',
            'variant_quantity',
            'variant_description',
            'existing_variant_ids',
            'removed_variant_ids',
            'removed_images'
        ]));
        // $product->fill($request->except(['image', 'images', 'variant_name', 'product_attribute_id', 'variant_price', 'variant_quantity', 'existing_variant_ids', 'removed_variant_ids']));

        $current_timestamp = now()->timestamp;
        if ($request->hasFile('image')) {
            if (!empty($product->image) && File::exists(public_path("uploads/products/{$product->image}"))) {
                File::delete(public_path("uploads/products/{$product->image}"));
                File::delete(public_path("uploads/products/thumbnails/{$product->image}"));
            }

            $image = $request->file('image');
            $imageName = "{$current_timestamp}.{$image->extension()}";

            $this->imageProcessor->process($image, $imageName, [
                ['path' => public_path('uploads/products'), 'cover' => [689, 689, 'center']],
                ['path' => public_path('uploads/products/thumbnails'), 'resize' => [300, 300]],
            ]);
            $product->image = $imageName;
        }

        $existingImages = !empty($product->images) ? explode(',', $product->images) : [];
        $removedImages = $request->input('removed_images', []);

        foreach ($removedImages as $removedImage) {
            if (File::exists(public_path("uploads/products/{$removedImage}"))) {
                File::delete(public_path("uploads/products/{$removedImage}"));
                File::delete(public_path("uploads/products/thumbnails/{$removedImage}"));
            }
            $existingImages = array_diff($existingImages, [$removedImage]);
        }
        $existingImages = array_values($existingImages);
        if ($request->hasFile('images')) {
            $newImages = [];
            $maxGalleryImages = 5;
            $remainingSlots = $maxGalleryImages - count($existingImages);

            if ($remainingSlots > 0) {
                foreach ($request->file('images') as $index => $file) {
                    if ($index >= $remainingSlots) break;

                    $gfilename = "{$current_timestamp}-" . ($index + 1) . ".{$file->extension()}";

                    $this->imageProcessor->process($file, $gfilename, [
                        ['path' => public_path('uploads/products'), 'cover' => [689, 689, 'center']],
                        ['path' => public_path('uploads/products/thumbnails'), 'resize' => [204, 204]],
                    ]);

                    $newImages[] = $gfilename;
                }
            }

            $allImages = array_merge($existingImages, $newImages);
            $product->images = !empty($allImages) ? implode(',', $allImages) : null;
        } else {
            $product->images = !empty($existingImages) ? implode(',', $existingImages) : null;
        }

        $product->save();

        $removedVariantIds = $request->input('removed_variant_ids', []);
        if (!empty($removedVariantIds)) {
            ProductAttributeValue::whereIn('id', $removedVariantIds)->delete();
        }

        if ($hasVariant) {
            $existingVariantIds = $request->input('existing_variant_ids', []);

            foreach ($request->variant_name as $index => $name) {
                $attributeValue = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $name,
                    'description' => $request->variant_description[$index] ?? null,
                    'price' => $request->variant_price[$index] ?? null,
                    'quantity' => $request->variant_quantity[$index] ?? 0,
                ];

                if (isset($existingVariantIds[$index]) && !empty($existingVariantIds[$index])) {
                    $existingVariant = ProductAttributeValue::find($existingVariantIds[$index]);
                    if ($existingVariant) {
                        $existingVariant->update($attributeValue);
                    }
                } else {
                    ProductAttributeValue::create($attributeValue);
                }
            }
            $variantTotalQuantity = $product->attributeValues()->sum('quantity');

            if ($variantTotalQuantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($variantTotalQuantity <= $product->reorder_quantity && $variantTotalQuantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
        } else {
            $product->attributeValues()->delete();

            if ($product->quantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($product->quantity <= $product->reorder_quantity && $product->quantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
        }

        $product->save();

        if ($product->stock_status === 'instock' && $previousStockStatus !== 'instock') {
            $users = User::where('utype', 'USR')->get();

            foreach ($users as $user) {
                $user->notify(
                    new StockUpdate($product, "Good news! The product {$product->name} is now back in stock.")
                );
            }
        }

        return redirect()->route('admin.products')->with('status', 'Product has been updated successfully!');
    }


    public function archivedProducts($id)
    {
        $product = Product::findOrFail($id);
        $product->archived = 1;
        $product->archived_at = Carbon::now();
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product archived successfully!');
    }

    public function restoreProducts(Request $request)
    {
        $productIds = $request->input('ids');
        Product::whereIn('id', $productIds)->update(['archived' => 0]);

        return response()->json(['status' => 'Products restored successfully!']);
    }

    public function showArchivedProducts()
    {
        $archivedProducts = Product::archived()->paginate(10);

        foreach ($archivedProducts as $product) {
            if ($product->archived_at) {
                $product->archived_at = Carbon::parse($product->archived_at);
            }
        }

        return view('admin.archived-products', compact('archivedProducts'));
    }
    public function prod_attributes()
    {
        $attributes = ProductAttribute::orderBy('id', 'DESC')->paginate(10);
        return view('admin.product-attributes', compact('attributes'));
    }

    public function prod_attribute_add()
    {
        return view('admin.product-attribute-add');
    }

    public function prod_attribute_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_attributes,name',
        ]);

        $attribute = new ProductAttribute();
        $attribute->name = $request->name;
        $attribute->save();

        return redirect()->route('admin.product-attributes')->with('status', 'Product attribute added successfully!');
    }


    public function product_attribute_edit($id)
    {
        $attribute =  ProductAttribute::find($id);
        return view('admin.product-attribute-edit', compact('attribute'));
    }

    public function product_attribute_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $attribute = ProductAttribute::find($request->id);
        $attribute->name = $request->name;
        $attribute->save();

        return redirect()->route('admin.product-attributes')->with('status', 'Product attribute updated successfully!');
    }

    public function attribute_archive($id)
    {
        $attribute = ProductAttribute::findOrFail($id);
        $attribute->delete(); // Soft delete
        return redirect()->route('admin.product-attributes')->with('status', 'Product Attribute has been archived successfully!');
    }

    public function archived_attributes()
    {
        $archivedAttributes = ProductAttribute::onlyTrashed()
            ->orderBy('id', 'DESC')
            ->paginate(10);

        $pageTitle = 'Archived Product Attributes';
        return view('admin.archived-attributes', compact('archivedAttributes', 'pageTitle'));
    }

    public function restore_attribute($id)
    {
        $attribute = ProductAttribute::onlyTrashed()->findOrFail($id);
        $attribute->restore();
        return redirect()->route('admin.archived-attributes')->with('status', 'Product Attribute restored successfully!');
    }

    // public function product_attribute_delete($id)
    // {
    //     $attribute = ProductAttribute::find($id);
    //     $attribute->delete();
    //     return redirect()->route('admin.product-attributes')->with('status', 'Product Variant has been deleted successfully!');
    // }


    public function orders(Request $request)
    {
        $timeSlots = TimeSlotHelper::time();
        $query = Order::with([
            'user.course.college',
            'orderItems.product',
            'orderItems.variant'
        ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('orderItems.product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhere(function ($transQuery) use ($search) {
                        $searchLower = strtolower($search);
                        if (in_array($searchLower, ['paid', 'unpaid'])) {
                            if ($searchLower === 'paid') {
                                $transQuery->whereHas('transaction', function ($tq) {
                                    $tq->where('status', 'paid');
                                });
                            } else {
                                $transQuery->whereDoesntHave('transaction')
                                    ->orWhereDoesntHave('transaction', function ($tq) {
                                        $tq->where('status', 'paid');
                                    });
                            }
                        }
                    });
            });
        }

        if ($request->filled('order_status')) {
            $query->where('status', $request->order_status);
        }

        if ($request->filled('time_slot_range')) {
            $query->where('time_slot', $request->time_slot_range);
        }

        if ($request->filled('transaction_status')) {
            if ($request->transaction_status === 'paid') {
                $query->whereHas('transaction', function ($q) {
                    $q->where('status', 'paid');
                });
            } elseif ($request->transaction_status === 'unpaid') {
                $query->whereDoesntHave('transaction')
                    ->orWhereDoesntHave('transaction', function ($q) {
                        $q->where('status', 'paid');
                    });
            }
        }

        $dateType = $request->input('date_type', 'created_at');
        $validDateTypes = ['created_at', 'reservation_date', 'picked_up_date', 'canceled_date'];

        if (!in_array($dateType, $validDateTypes)) {
            $dateType = 'created_at';
        }

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();

            if ($dateType === 'created_at') {
                $query->where('created_at', '>=', $dateFrom);
            } elseif ($dateType === 'reservation_date') {
                $query->where('reservation_date', '>=', $dateFrom);
            } elseif ($dateType === 'picked_up_date') {
                $query->where('picked_up_date', '>=', $dateFrom);
            } elseif ($dateType === 'canceled_date') {
                $query->where('canceled_date', '>=', $dateFrom);
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();

            if ($dateType === 'created_at') {
                $query->where('created_at', '<=', $dateTo);
            } elseif ($dateType === 'reservation_date') {
                $query->where('reservation_date', '<=', $dateTo);
            } elseif ($dateType === 'picked_up_date') {
                $query->where('picked_up_date', '<=', $dateTo);
            } elseif ($dateType === 'canceled_date') {
                $query->where('canceled_date', '<=', $dateTo);
            }
        }

        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'amount_high':
                $query->orderBy('total', 'desc');
                break;
            case 'amount_low':
                $query->orderBy('total', 'asc');
                break;
            case 'reservation_date':
                $query->orderBy('reservation_date', 'desc');
                break;
            case 'newest':
            default:

                $query->orderByRaw("FIELD(status, 'reserved', 'canceled', 'pickedup')")
                    ->latest();
                break;
        }

        $orders = $query->paginate(12)->withQueryString();
        $orders->getCollection()->transform(function ($order) {
            $paidTransaction = $order->transaction()->where('status', 'paid')->first();
            $order->transaction_status = $paidTransaction ? 'paid' : 'unpaid';
            $order->items_count = $order->orderItems->sum('quantity');
            return $order;
        });

        if ($request->ajax()) {
            return response()->json([
                'orders' => view('partials._orders-table', compact('orders'))->render(),
                'pagination' => view('partials._orders-pagination', compact('orders'))->render(),
                'count' => $orders->total()
            ]);
        }

        return view('admin.orders', compact('orders', 'timeSlots'));
    }

    public function order_details($order_id)
    {
        $order = Order::with(['orderItems.product', 'user', 'updatedBy'])->findOrFail($order_id);
        // $transaction = Transaction::where('order_id', $order_id)->first();
        $transaction = Transaction::where('order_id', $order_id)
            ->latest()
            ->first();

        $order = Order::with(['orderItems.product', 'user', 'updatedBy'])->findOrFail($order_id);
        // $transaction = Transaction::where('order_id', $order_id)->first();
        $transaction = Transaction::where('order_id', $order_id)
            ->latest()
            ->first();

        return view('admin.order-details', compact('order',  'transaction'));
    }

    private function restoreQuantity(Order $order)
    {
        foreach ($order->orderItems as $item) {
            if ($item->variant_id) {
                $variant = ProductAttributeValue::find($item->variant_id);
                if ($variant) {
                    $variant->quantity += $item->quantity;
                    $variant->stock_status = $variant->quantity > 0 ? 'instock' : 'outofstock';
                    $variant->save();
                }
            } else {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->stock_status = $product->quantity > 0 ? 'instock' : 'outofstock';
                    $product->save();
                }
            }
        }
    }
    public function update_order_status(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'canceled_reason' => 'required_if:order_status,canceled|string|max:500',
            'order_status' => 'required|in:reserved,canceled',
        ]);

        $order = Order::with('orderItems')->findOrFail($request->order_id);
        $originalStatus = $order->status;

        if ($request->order_status === 'canceled' && $originalStatus !== 'canceled') {
            $this->restoreQuantity($order);
            $order->canceled_date = Carbon::now();
            $order->canceled_reason = $request->canceled_reason;
            $order->updated_by = Auth::user()->id;

            // Send notification to user
            $order->user->notify(new OrderCanceledNotification($order));
        }

        $order->status = $request->order_status;
        $order->save();
        return back()->with('status', 'Status updated successfully!');
    }

    public function completePayment(Request $request, $order_id)
    {
        return DB::transaction(function () use ($request, $order_id) {
            $order = Order::findOrFail($order_id);
            $request->merge([
                'amount_paid' => str_replace(',', '', $request->amount_paid)
            ]);
            $request->validate([
                'amount_paid' => ['required', 'numeric', function ($attribute, $value, $fail) use ($order) {
                    if ($value < $order->total) {
                        $fail('The amount paid must be at least ₱' . number_format($order->total, 2));
                    }
                }]
            ]);
            $change = $request->amount_paid - $order->total;
            $transaction = $order->transaction;

            $transaction = Transaction::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'amount_paid' => $request->amount_paid,
                    'change' => $change,
                    'status' => 'paid',
                    'processed_by' => Auth::id(),
                    'processed_at' => now()
                ]
            );

            $order->update([
                'status' => 'pickedup',
                'picked_up_date' => now(),
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Payment completed successfully!',
                'order_id' => $order->id,
                'transaction_id' => $transaction->id
            ]);
        });
    }

    public function downloadReceipt(Order $order)
    {
        $order->load(['orderItems.product', 'user']);
        $transaction = Transaction::where('order_id', $order->id)
            ->latest()
            ->first();
        $orderItems = $order->orderItems;
        if (!$transaction) {
            abort(404, 'Transaction not found for this order');
        }
        $pdf = Pdf::loadView('admin.pdf.receipt', [
            'order' => $order,
            'transaction' => $transaction,
            'orderItems' => $orderItems
        ]);
        return $pdf->stream('receipt_order_' . $order->id . '.pdf');
    }


    // Sliders Page
    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }
    public function slide_add()
    {

        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' =>  'required',
            'title' =>  'required',
            'subtitle' =>  'required',
            'link' =>  'required',
            'status' =>  'required',
            'image' =>  'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->imageProcessor->process($image, $file_name, [
            ['path' => public_path('uploads/slides'), 'cover' => [1920, 1080, 'top'], 'resize' => [1920, 1080]]
        ]);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide   added successfully!');
    }


    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' =>  'required',
            'title' =>  'required',
            'subtitle' =>  'required',
            'link' =>  'required',
            'status' =>  'required',
            'image' =>  'mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
                File::delete(public_path('uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->imageProcessor->process($image, $file_name, [
                ['path' => public_path('uploads/slides'), 'cover' => [1920, 1080, 'top'], 'resize' => [1920, 1080]]
            ]);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide updated successfully!');
    }


    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
            File::delete(public_path('uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide has been deleted successfully!');
    }

    // Contact PAGE
    // public function contacts(Request $request)
    // {
    //     $contacts = Contact::with(['user', 'replies.admin'])
    //         ->latest()
    //         ->paginate(10);
    //     return view('admin.contacts', compact('contacts'));
    // }

    public function contacts(Request $request)
    {
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'newest');
        $dateFilter = $request->input('date_filter');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Contact::with(['user', 'replies.admin']);

        // Search Filter - by user name and email
        if ($search) {
            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Date Filter
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($dateFilter === '30days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Sort Filter
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'replied':
                $query->has('replies')->latest();
                break;
            default: // newest
                $query->latest();
                break;
        }

        $contacts = $query->paginate(10)->withQueryString();

        // For AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'contacts' => view('partials._contacts-cards', compact('contacts'))->render(),
                'pagination' => view('partials._contacts-pagination', compact('contacts'))->render()
            ]);
        }

        return view('admin.contacts', compact('contacts'));
    }
    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()
            ->route('admin.contacts')
            ->with('status', 'Contact has been deleted successfully!');
    }

    public function contact_reply(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        if (Auth::user()->utype !== 'ADM') {
            abort(403, 'Only admins can reply to contacts.');
        }
        $validated = $request->validate([
            'replyMessage' => 'required|string|max:5000',
        ]);

        $reply = ContactReply::create([
            'contact_id' => $contact->id,
            'admin_id' => Auth::id(),
            'admin_reply' => $validated['replyMessage']
        ]);

        Mail::to($contact->user?->email)->send(
            new ReplyToContact($contact, $validated['replyMessage'])
        );

        return redirect()
            ->route('admin.contacts')
            ->with('status', 'Reply sent successfully!');
    }

    public function filter(Request $request)
    {
        $yearLevel = $request->input('year_level');
        $department = $request->input('department');

        $query = User::query();

        if ($yearLevel) {
            $query->where('year_level', $yearLevel);
        }

        if ($department) {
            $query->where('department', $department); // Assuming department represents colleges
        }

        $users = $query->get();

        return response()->json($users);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($results);
    }

    public function order_filter(Request $request)
    {
        Log::info('Received filter request', [
            'time_slot' => $request->time_slot,
            'status' => $request->status
        ]);



        $validatedData = $request->validate([
            'time_slot' => 'nullable|string',
            'status' => 'nullable|string|in:reserved,pickedup,canceled'
        ]);

        $query = Order::query();

        if ($request->filled('time_slot')) {
            $query->where('time_slot', $request->time_slot);
            Log::info('Applied time slot filter', ['time_slot' => $request->time_slot]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
            Log::info('Applied status filter', ['status' => $request->status]);
        }

        $query->latest(); // Default sorting by most recent

        try {
            $orders = $query->paginate(10);
            Log::info('Filtered orders fetched successfully.', [
                'total_orders' => $orders->total(),
                'current_page' => $orders->currentPage()
            ]);

            return response()->json([
                'orders' => $orders->items(),
                'total' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching filtered orders', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while fetching orders.'], 500);
        }
    }


    public function getCoursesByCollege($collegeId)
    {
        // $courses = Course::where('college_id', $collegeId)->orderBy('name')->get();
        $courses = Course::where('college_id', $collegeId)
            ->select('id', 'code', 'name')
            ->orderBy('name')
            ->get();
        return response()->json($courses);
    }
    public function users(Request $request)
    {
        $query = User::with(['college', 'course']);
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('college', function ($collegeQuery) use ($searchTerm) {
                        $collegeQuery->where('code', 'like', '%' . $searchTerm . '%')
                            ->orWhere('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('course', function ($courseQuery) use ($searchTerm) {
                        $courseQuery->where('code', 'like', '%' . $searchTerm . '%')
                            ->orWhere('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->filled('year_level')) {
            $query->where('year_level', $request->year_level);
        }


        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        // Course filter
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Email filter - filter by email domain
        if ($request->filled('email_filter')) {
            $emailDomain = $request->email_filter;
            $query->where('email', 'like', '%@' . $emailDomain);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(10);
        $colleges = College::orderBy('name')->get();
        $courses = Course::with('college')->orderBy('name')->get();

        if ($request->ajax()) {
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'year_level' => $user->year_level,
                    'college' => $user->college ? [
                        'id' => $user->college->id,
                        'code' => $user->college->code,
                        'name' => $user->college->name
                    ] : null,
                    'course' => $user->course ? [
                        'id' => $user->course->id,
                        'code' => $user->course->code,
                        'name' => $user->course->name
                    ] : null,
                ];
            });

            return response()->json([
                'users' => view('partials._users-table', ['users' => $users])->render(),
                'pagination' => view('partials._users-pagination', ['users' => $users])->render(),
                'count' => $users->total(),
            ]);
        }

        return view('admin.users', compact('users', 'colleges', 'courses'));
    }

    public function coursesByCollege($collegeId)
    {
        $courses = Course::where('college_id', $collegeId)->get();
        return response()->json($courses);
    }

    public function users_add()
    {
        $colleges = College::all();
        $courses = Course::all();

        return view("admin.user-add", compact('colleges', 'courses'));
    }

    public function users_store(Request $request)
    {
        $isAdmin = $request->form_type === 'admin';
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|ends_with:@cvsu.edu.ph',
            'phone_number' => 'nullable|string|regex:/^9\d{9}$/',
        ];

        if ($isAdmin) {
            $validationRules['form_type'] = 'required|in:admin';
            $validationRules['sex'] = 'required|in:male,female';
            $validationRules['position'] = 'required|string|max:255';
        } else {
            $validationRules['role'] = 'required|in:student,employee,non-employee';
            $validationRules['year_level'] = 'nullable|in:1st Year,2nd Year,3rd Year,4th Year,5th Year';
            $validationRules['college_id'] = 'nullable|exists:colleges,id';
            $validationRules['course_id'] = 'nullable|exists:courses,id';
            $validationRules['form_type'] = 'nullable|in:user';
            $validationRules['position'] = 'nullable|string|max:255';

            if ($request->role === 'student') {
                $validationRules['year_level'] = 'required|in:1st Year,2nd Year,3rd Year,4th Year,5th Year';
                $validationRules['college_id'] = 'required|exists:colleges,id';
                $validationRules['course_id'] = 'required|exists:courses,id';
            }
            if ($request->role === 'employee') {
                $validationRules['position'] = 'required|string|max:255';
            }
        }

        $customMessages = [
            'email.ends_with' => 'The email must be a @cvsu.edu.ph email address.',
            'phone_number.regex' => 'Phone number must start with 9 and be exactly 10 digits.',
            'year_level.required' => 'Year level is required for students.',
            'college_id.required' => 'College is required for students.',
            'course_id.required' => 'Course is required for students.',
            'position.required' => 'Position is required for employees and admins.',
        ];

        $validated = $request->validate($validationRules, $customMessages);

        if ($isAdmin) {
            $validated['password'] = Hash::make('cvsu-barg-password');
            $validated['utype'] = 'ADM';
            $validated['role'] = 'employee';
            $validated['password_set'] = true;
            $validated['sex'] = $validated['sex'] ?? 'male';
        } else {
            $validated['password'] = Hash::make($request->password ?? 'defaultpassword');
            $validated['utype'] = 'USR';
            $validated['password_set'] = false;
            $validated['sex'] = 'male'; // Default for users

            // Only set student-specific fields for students
            if ($validated['role'] !== 'student') {
                $validated['year_level'] = null;
                $validated['college_id'] = null;
                $validated['course_id'] = null;
            }
            if ($validated['role'] === 'student' || $validated['role'] === 'non-employee') {
                $validated['position'] = null;
            }
        }

        $validated['email_verified_at'] = now();
        $validated['isDefault'] = false;

        try {
            $user = User::create($validated);
            $message = $isAdmin
                ? 'Admin has been added successfully!'
                : 'User has been added successfully!';

            return redirect()->route('admin.users')->with('status', $message);
        } catch (\Exception $e) {
            \Log::error('User creation failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create user. Please try again.']);
        }
    }

    public function users_edit($id)
    {
        $user = User::findOrFail($id);
        $colleges = College::all();
        $courses = Course::all();

        return view('admin.user-edit', compact('user', 'colleges', 'courses'));
    }

    public function users_update(Request $request, $id)
    {

        // dd($request->all());
        $user = User::findOrFail($id);

        $isStudent = $user->role === 'student';
        $isEmployee = $user->role === 'employee' || $user->utype === 'ADM';

        $rules = [
            'phone_number' => ['nullable', 'string', 'regex:/^9\d{9}$/'],
            'year_level'   => $isStudent ? ['required', 'in:1st Year,2nd Year,3rd Year,4th Year,5th Year'] : ['nullable'],
            'college_id'   => $isStudent ? ['required', 'exists:colleges,id'] : ['nullable'],
            'course_id'    => $isStudent ? ['required', 'exists:courses,id'] : ['nullable'],
            'position'     => $isEmployee ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'phone_number.regex' => 'Phone number must start with 9 and be exactly 10 digits.',
            'year_level.required' => 'Year level is required for students.',
            'college_id.required' => 'College is required for students.',
            'course_id.required' => 'Course is required for students.',
            'position.required' => 'Position is required for employees and admins.',
        ];
        $validated = $request->validate($rules, $messages);
        $user->phone_number = $validated['phone_number'] ?? null;

        $user->phone_number = $request->phone_number;

        if ($isStudent) {
            $user->year_level = $validated['year_level'];
            $user->college_id = $validated['college_id'];
            $user->course_id  = $validated['course_id'];
            $user->position = null;
        } else {
            $user->year_level = null;
            $user->college_id = null;
            $user->course_id  = null;

            if ($isEmployee) {
                $user->position = $validated['position'];
            } else {
                $user->position = null;
            }
        }
        $user->save();
        return redirect()->route('admin.users')->with('status', 'User has been updated successfully!');
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');

        // Fetch products with their related data (including variants and categories)
        $products = Product::with(['category.parent', 'attributeValues.productAttribute'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhereHas('category', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->get();

        // Add total quantity, variant prices, and stock status to each product
        foreach ($products as $product) {
            $variantPrices = [];
            $totalQuantity = 0;

            // If the product has variants, calculate total quantity and variant prices
            if ($product->attributeValues->isNotEmpty()) {
                foreach ($product->attributeValues as $variant) {
                    $variantPrices[] = [
                        'value' => $variant->value,
                        'price' => $variant->price
                    ];
                    $totalQuantity += $variant->quantity; // Sum of variant quantities
                }
            } else {
                // If no variants, use the product's base quantity and price
                $totalQuantity = $product->quantity;
                $variantPrices[] = [
                    'value' => 'Base Price',
                    'price' => $product->price
                ];
            }

            // Add the calculated data to the product
            $product->total_quantity = $totalQuantity;
            $product->variant_prices = $variantPrices;

            // Set stock status based on total quantity
            if ($totalQuantity == 0) {
                $product->stock_status = "Out of Stock";
                $product->badge_class = "badge-danger"; // Badge for out of stock
            } elseif ($totalQuantity <= $product->outofstock_quantity) {
                $product->stock_status = "Out of Stock"; // Out of stock level
                $product->badge_class = "badge-danger"; // Badge for out of stock
            } elseif ($totalQuantity <= $product->reorder_quantity) {
                $product->stock_status = "Reorder Level"; // Set reorder level if quantity is below reorder threshold
                $product->badge_class = "badge-warning"; // Badge for reorder level
            } else {
                $product->stock_status = "In Stock"; // This can be used if you have some threshold
                $product->badge_class = "badge-success"; // Badge for in stock
            }

            // Set the product price (either base price or first variant's price)
            $product->price = $product->attributeValues->isNotEmpty() ? $product->attributeValues->first()->price : $product->price;
        }

        return response()->json($products);
    }

    // Rentals Reports

    public function rentalsReports(Request $request)
    {
        // Get the current date and year
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        $currentMonthId = $currentDate->month;

        // Validate the incoming request
        $validated = $request->validate([
            'year'  => 'nullable|integer|min:2000|max:' . $currentYear,
            'month' => 'nullable|integer|min:1|max:12',
            'week'  => 'nullable|integer|min:1|max:6',
        ]);

        // Get selected year, month, and week from request, default to current year/month/week
        $selectedYear  = $validated['year']  ?? $currentYear;
        $selectedMonthId = $validated['month'] ?? $currentMonthId;
        $selectedWeekId = $validated['week']  ?? $currentDate->weekOfMonth;

        // Fetch available months and the selected month
        $availableMonths = DB::table('month_names')->orderBy('id')->get();
        $selectedMonth   = $availableMonths->firstWhere('id', $selectedMonthId);

        if (!$selectedMonth) {
            $selectedMonth   = $availableMonths->first();
            $selectedMonthId = $selectedMonth->id;
        }

        // Calculate the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // Fetch the latest 10 reservations
        $reservations = DB::table('reservations')
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

        // Fetch dashboard data for the selected month and year
        $dashboardDatas = DB::select("
            SELECT
                SUM(total_price) AS TotalPaymentAmount,
                SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
            FROM reservations
            WHERE created_at BETWEEN ? AND ?
        ", [$startOfMonth, $endOfMonth]);

        // Get totals from dashboard data
        $dashboardData = $dashboardDatas[0] ?? null;

        // Initialize totals
        $TotalPaymentAmount = $dashboardData->TotalPaymentAmount ?? 0;
        $TotalPaymentPendingAmount = $dashboardData->TotalPaymentPendingAmount ?? 0;
        $TotalPaymentCompletedAmount = $dashboardData->TotalPaymentCompletedAmount ?? 0;
        $TotalPaymentCanceledAmount = $dashboardData->TotalPaymentCanceledAmount ?? 0;

        // Weekly data for the selected month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            // Ensure the start and end of the week don't exceed the month boundaries
            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            // Add valid week ranges
            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }

        // Fetch totals for each week
        $PaymentAmountW = []; // Initialize Payment Amount for Weekly Data
        $paymentPendingAmounts = [];
        $paymentCompletedAmounts = [];
        $paymentCanceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            // Fetch total amounts for the week
            $weeklyData = DB::select("
                SELECT
                    SUM(total_price) AS TotalPaymentAmount,
                    SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                    SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                    SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
                FROM reservations
                WHERE created_at BETWEEN ? AND ?
            ", [$startOfSelectedWeek, $endOfSelectedWeek])[0] ?? null;

            // Store results in arrays
            $PaymentAmountW[$week] = $weeklyData->TotalPaymentAmount ?? 0;
            $paymentPendingAmounts[$week] = $weeklyData->TotalPaymentPendingAmount ?? 0;
            $paymentCompletedAmounts[$week] = $weeklyData->TotalPaymentCompletedAmount ?? 0;
            $paymentCanceledAmounts[$week] = $weeklyData->TotalPaymentCanceledAmount ?? 0;
        }

        // Prepare Weekly Data for View
        $PaymentAmountW = implode(',', $PaymentAmountW);
        $PaymentPendingAmountW = implode(',', $paymentPendingAmounts);
        $PaymentCompletedAmountW = implode(',', $paymentCompletedAmounts);
        $PaymentCanceledAmountW = implode(',', $paymentCanceledAmounts);

        // Monthly data for the selected year
        $monthlyDatas = DB::select("
            SELECT M.id AS MonthNo, M.name AS MonthName,
                IFNULL(D.TotalPaymentAmount, 0) AS TotalPaymentAmount,
                IFNULL(D.TotalPaymentPendingAmount, 0) AS TotalPaymentPendingAmount,
                IFNULL(D.TotalPaymentCompletedAmount, 0) AS TotalPaymentCompletedAmount,
                IFNULL(D.TotalPaymentCanceledAmount, 0) AS TotalPaymentCanceledAmount
            FROM month_names M
            LEFT JOIN (
                SELECT
                    MONTH(created_at) AS MonthNo,
                    SUM(total_price) AS TotalPaymentAmount,
                    SUM(IF(payment_status='pending', total_price, 0)) AS TotalPaymentPendingAmount,
                    SUM(IF(payment_status='completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                    SUM(IF(payment_status='canceled', total_price, 0)) AS TotalPaymentCanceledAmount
                FROM reservations
                WHERE YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
            ) D ON D.MonthNo = M.id
            ORDER BY M.id
        ", [$selectedYear]);

        // Prepare Monthly Data for View
        $PaymentAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentAmount;
        }, $monthlyDatas));
        $PaymentPendingAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount;
        }, $monthlyDatas));
        $PaymentCompletedAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCompletedAmount;
        }, $monthlyDatas));
        $PaymentCanceledAmountM = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCanceledAmount;
        }, $monthlyDatas));

        // Daily data within the selected week
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();
        $selectedWeek   = $availableWeeks->firstWhere('week_number', $selectedWeekId);

        if (!$selectedWeek) {
            $selectedWeek   = $availableWeeks->first();
            $selectedWeekId = $selectedWeek->week_number;
        }

        // Define the start and end of the selected week
        if (array_key_exists($selectedWeekId, $weekRanges)) {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId];
        } else {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[1] ?? [$startOfMonth, $endOfMonth]; // Default to week 1 or full month
        }

        // Query for daily data within the selected week, grouped by day
        $dailyDatasRaw = DB::select("
            SELECT
                DAYNAME(created_at) AS DayName,
                SUM(IF(payment_status = 'pending', total_price, 0)) AS TotalPaymentPendingAmount,
                SUM(IF(payment_status = 'completed', total_price, 0)) AS TotalPaymentCompletedAmount,
                SUM(IF(payment_status = 'canceled', total_price, 0)) AS TotalPaymentCanceledAmount
            FROM reservations
            WHERE created_at BETWEEN ? AND ?
            GROUP BY DAYNAME(created_at)
            ORDER BY FIELD(DAYNAME(created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ", [$startOfSelectedWeek, $endOfSelectedWeek]);

        // Define the desired order of days
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Create a map of DayName to daily data
        $dailyDataMap = collect($dailyDatasRaw)->keyBy('DayName');

        // Reorder the daily data from Monday to Sunday, filling missing days with zeros
        $sortedDailyDatas = collect($daysOfWeek)->map(function ($day) use ($dailyDataMap) {
            if ($dailyDataMap->has($day)) {
                return $dailyDataMap->get($day);
            } else {
                return (object)[
                    'DayName' => $day,
                    'TotalPaymentPendingAmount' => 0,
                    'TotalPaymentCompletedAmount' => 0,
                    'TotalPaymentCanceledAmount' => 0,
                ];
            }
        });

        // Prepare Daily Data for View
        $AmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount + $item->TotalPaymentCompletedAmount + $item->TotalPaymentCanceledAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentPendingAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentPendingAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentCompletedAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCompletedAmount;
        }, $sortedDailyDatas->toArray()));
        $PaymentCanceledAmountD = implode(',', array_map(function ($item) {
            return $item->TotalPaymentCanceledAmount;
        }, $sortedDailyDatas->toArray()));

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // Return View with all data
        $pageTitle = 'Rentals Reports';
        return view('admin.rentals_reports', compact(
            'reservations',
            'TotalPaymentAmount',
            'TotalPaymentPendingAmount',
            'TotalPaymentCompletedAmount',
            'TotalPaymentCanceledAmount',
            'PaymentAmountW',
            'PaymentPendingAmountW',
            'PaymentCompletedAmountW',
            'PaymentCanceledAmountW',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'yearRange',
            'PaymentAmountM',
            'PaymentPendingAmountM',
            'PaymentCompletedAmountM',
            'PaymentCanceledAmountM',
            'sortedDailyDatas',
            'PaymentPendingAmountD',
            'PaymentCompletedAmountD',
            'PaymentCanceledAmountD',
            'AmountD',
            'selectedWeekId',
            'availableWeeks',
            'pageTitle'
        ));
    }

    // Rental Reports PDF
    public function downloadPdfRentals(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'monthly_rentals_img' => 'nullable|string',
            'weekly_rentals_img'  => 'nullable|string',
            'daily_rentals_img'   => 'nullable|string',
            'total_payment'       => 'nullable|numeric',
            'pending_amount'      => 'nullable|numeric',
            'completed_amount'    => 'nullable|numeric',
            'canceled_amount'     => 'nullable|numeric',
        ]);

        // Retrieve the images from the request (if any)
        $monthlyImage = $request->input('monthly_rentals_img');
        $weeklyImage  = $request->input('weekly_rentals_img');
        $dailyImage   = $request->input('daily_rentals_img');

        // Retrieve other totals from the request
        $totalAmounts = [
            'total_payment'   => $request->input('total_payment'),
            'pending_amount'  => $request->input('pending_amount'),
            'completed_amount' => $request->input('completed_amount'),
            'canceled_amount' => $request->input('canceled_amount'),
        ];

        // Retrieve all rentals (or filter based on criteria)
        $rentals = Rental::all(); // Assuming you have a Rental model with 'name' attribute

        // Generate monthly, weekly, and daily data for each rental
        $monthlyData = [];  // Example: Get monthly counts
        $weeklyData = [];   // Example: Get weekly counts
        $dailyData = [];    // Example: Get daily counts

        foreach ($rentals as $rental) {
            // Example queries to get monthly, weekly, and daily data for each rental
            // Replace these with your actual logic to fetch the data.
            $monthlyData[$rental->name] = $rental->reservations()->monthly()->count();
            $weeklyData[$rental->name]  = $rental->reservations()->weekly()->count();
            $dailyData[$rental->name]   = $rental->reservations()->daily()->count();
        }

        // Pass all data to the view
        $data = [
            'monthlyImage'  => $monthlyImage,
            'weeklyImage'   => $weeklyImage,
            'dailyImage'    => $dailyImage,
            'totalAmounts'  => $totalAmounts,
            'rentals'       => $rentals,  // Pass rentals to the view
            'monthlyData'   => $monthlyData,
            'weeklyData'    => $weeklyData,
            'dailyData'     => $dailyData,
        ];

        // Generate PDF with fixed paper size (A4) and portrait orientation
        $pdf = PDF::loadView('admin.rentals_reports_pdf', $data)
            ->setPaper('A4', 'portrait');

        // Generate a unique filename
        $filename = 'rentals_reports_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        // Return the PDF download
        return $pdf->download($filename);
    }

    public function rentalsReportsName(Request $request)
    {
        // Get the current date and year
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;

        // Validate the incoming request
        $validated = $request->validate([
            'year'  => 'nullable|integer|min:2000|max:' . $currentYear,
        ]);

        // Get selected year from request, default to current year
        $selectedYear  = $validated['year']  ?? $currentYear;

        // Define rental names and corresponding colors
        $rentalColors = [
            'Male Dormitory' => '#6f42c1', // Purple
            'Female Dormitory' => '#fd7e14', // Orange
            'International House II' => '#20c997', // Cyan
            'International Convention Center' => '#e83e8c', // Pink
            'Rolle Hall' => '#007bff', // Blue
            'Swimming Pool' => '#ffc107', // Yellow
        ];

        // Define rental names as objects for iteration
        $rentalNames = collect([
            'Male Dormitory',
            'Female Dormitory',
            'International House II',
            'International Convention Center',
            'Rolle Hall',
            'Swimming Pool',
        ])->map(function ($name) {
            return (object)['name' => $name];
        });

        // Initialize data structures
        $monthlyData = [];
        $weeklyData = [];
        $dailyData = [];
        $reservationsPerRental = [];

        // Prepare Monthly Data
        foreach ($rentalNames as $rental) {
            $monthlyReservations = DB::table('reservations')
                ->select(DB::raw('MONTH(reservations.created_at) as month'), DB::raw('COUNT(*) as count'))
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->groupBy(DB::raw('MONTH(reservations.created_at)'))
                ->orderBy(DB::raw('MONTH(reservations.created_at)'))
                ->pluck('count', 'month')
                ->toArray();

            // Initialize all months to 0
            $monthlyData[$rental->name] = array_fill(1, 12, 0);

            // Fill with actual data
            foreach ($monthlyReservations as $month => $count) {
                $monthlyData[$rental->name][$month] = $count;
            }

            // Calculate total reservations per rental
            $reservationsPerRental[$rental->name] = DB::table('reservations')
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->count();
        }

        // Prepare Weekly Data within Each Month (Weeks 1-6)
        // Initialize weeks 1-6 to 0 for each rental
        foreach ($rentalNames as $rental) {
            $weeklyData[$rental->name] = array_fill(1, 6, 0); // Weeks 1-6

            // Fetch all reservations for the rental in the selected year
            $reservationsForRental = DB::table('reservations')
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->select('reservations.created_at')
                ->get();

            foreach ($reservationsForRental as $reservation) {
                $date = Carbon::parse($reservation->created_at);
                $dayOfMonth = $date->day;

                // Calculate week of month (1-6)
                $weekOfMonth = (int)ceil($dayOfMonth / 7);

                if ($weekOfMonth > 6) {
                    $weekOfMonth = 6; // Assign to week 6 if exceeding
                }

                $weeklyData[$rental->name][$weekOfMonth]++;
            }
        }

        // Prepare Daily Data
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($rentalNames as $rental) {
            $dailyReservations = DB::table('reservations')
                ->select(DB::raw('DAYNAME(reservations.created_at) as day'), DB::raw('COUNT(*) as count'))
                ->join('rentals', 'reservations.rental_id', '=', 'rentals.id')
                ->whereYear('reservations.created_at', $selectedYear)
                ->where('rentals.name', $rental->name)
                ->groupBy(DB::raw('DAYNAME(reservations.created_at)'))
                ->orderByRaw("FIELD(DAYNAME(reservations.created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                ->pluck('count', 'day')
                ->toArray();

            // Initialize all days to 0
            $dailyData[$rental->name] = array_fill_keys($daysOfWeek, 0);

            // Fill with actual data
            foreach ($dailyReservations as $day => $count) {
                $dailyData[$rental->name][$day] = $count;
            }
        }

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // Pass data to the view
        return view('admin.rentals_reports-name', compact(
            'rentalNames',
            'monthlyData',
            'weeklyData',
            'dailyData',
            'reservationsPerRental',
            'yearRange',
            'selectedYear',
            'rentalColors'
        ));
    }

    public function downloadPdfRentalsName(Request $request)
    {
        // Define rental names
        $rentalNames = [
            'Male Dormitory',
            'Female Dormitory',
            'International House II',
            'International Convention Center',
            'Rolle Hall',
            'Swimming Pool',
        ];

        // Define rental colors
        $rentalColors = [
            'Male Dormitory' => '#6f42c1', // Purple
            'Female Dormitory' => '#fd7e14', // Orange
            'International House II' => '#20c997', // Cyan
            'International Convention Center' => '#e83e8c', // Pink
            'Rolle Hall' => '#007bff', // Blue
            'Swimming Pool' => '#ffc107', // Yellow
        ];

        // Validate the incoming request data
        $rules = [
            'monthly_reservations_img' => 'nullable|string',
            'weekly_reservations_img'  => 'nullable|string',
            'daily_reservations_img'   => 'nullable|string',
        ];

        // Add dynamic validation rules for each rental name
        foreach ($rentalNames as $name) {
            $snakeName = Str::snake($name);
            $rules['reservations_' . $snakeName] = 'nullable|integer';
        }

        $request->validate($rules);

        // Retrieve the images
        $monthlyImage = $request->input('monthly_reservations_img');
        $weeklyImage  = $request->input('weekly_reservations_img');
        $dailyImage   = $request->input('daily_reservations_img');

        // Retrieve reservations per rental
        $reservationsPerRental = [];
        foreach ($rentalNames as $name) {
            $snakeName = Str::snake($name);
            $reservationsPerRental[$name] = $request->input('reservations_' . $snakeName, 0);
        }

        // Pass data to the PDF view
        $data = [
            'monthlyImage' => $monthlyImage,
            'weeklyImage'  => $weeklyImage,
            'dailyImage'   => $dailyImage,
            'reservationsPerRental' => $reservationsPerRental,
            'rentalColors' => $rentalColors,
        ];

        // Generate PDF with fixed paper size (A4) and portrait orientation
        $pdf = PDF::loadView('admin.rentals_reports_name_pdf', $data)
            ->setPaper('A4', 'portrait');

        // Generate a unique filename
        $filename = 'rentals_reports_name_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        // Return the PDF download
        return $pdf->download($filename);
    }

    public function showUserReports(Request $request)
    {
        // Get the current year and set the selected year from the request or default to the current year
        $selectedYear = $request->year ?? Carbon::now()->year;

        // Get the current month and set the selected month from the request or default to the current month
        $selectedMonth = $request->month ?? Carbon::now()->month;

        // Manually define the months if no Month model exists
        $availableMonths = collect([
            (object) ['id' => 1, 'name' => 'January'],
            (object) ['id' => 2, 'name' => 'February'],
            (object) ['id' => 3, 'name' => 'March'],
            (object) ['id' => 4, 'name' => 'April'],
            (object) ['id' => 5, 'name' => 'May'],
            (object) ['id' => 6, 'name' => 'June'],
            (object) ['id' => 7, 'name' => 'July'],
            (object) ['id' => 8, 'name' => 'August'],
            (object) ['id' => 9, 'name' => 'September'],
            (object) ['id' => 10, 'name' => 'October'],
            (object) ['id' => 11, 'name' => 'November'],
            (object) ['id' => 12, 'name' => 'December']
        ]);

        // Generate year range (e.g., last 5 years for the dropdown)
        $yearRange = range(Carbon::now()->year - 5, Carbon::now()->year);

        // Fetch user registration data (implement your logic here)
        $userRegistrationsByMonth = $this->getUserRegistrationsByMonth($selectedYear);
        $weeklyChartData = $this->getUserRegistrationsWeekly($selectedMonth, $selectedYear);
        $dailyChartData = $this->getUserRegistrationsDaily($selectedMonth, $selectedYear);

        // Get recent users (you can adjust this query to fetch actual recent users)
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        // Return the view and use 'with()' to pass the variable
        return view('admin.report-user')
            ->with('availableMonths', $availableMonths)
            ->with('selectedYear', $selectedYear)
            ->with('selectedMonth', $selectedMonth)
            ->with('yearRange', $yearRange)
            ->with('userRegistrationsByMonth', $userRegistrationsByMonth)
            ->with('weeklyChartData', $weeklyChartData)
            ->with('dailyChartData', $dailyChartData)
            ->with('recentUsers', $recentUsers);
    }
    public function generateUserReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $availableMonths = collect(range(1, 12))->map(function ($month) {
            return Carbon::createFromDate(null, $month, 1)->format('F');
        });


        // Retrieve input and parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Query the database for new users within the date range
        $newUsersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // Aggregate user registrations by day
        $userRegistrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Prepare data for ApexCharts
        $chartData = [
            'dates' => $userRegistrations->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray(),
            'counts' => $userRegistrations->pluck('count')->toArray(),
        ];

        // Optionally, retrieve user details
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();

        // Return the results to the view
        return view('admin.user-reports', compact('newUsersCount', 'newUsers', 'startDate', 'endDate', 'chartData'));
    }

    // Controller method to generate the sales report
    public function generateInputSales(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Retrieve input and parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Query sales data grouped by day
        $salesData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_sales'),
            DB::raw('SUM(CASE WHEN status = "reserved" THEN total ELSE 0 END) as reserved_sales'),
            DB::raw('SUM(CASE WHEN status = "pickedup" THEN total ELSE 0 END) as pickedup_sales'),
            DB::raw('SUM(CASE WHEN status = "canceled" THEN total ELSE 0 END) as canceled_sales')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aggregate totals
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $reservedSalesTotal = Order::where('status', 'reserved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
        $pickedUpSalesTotal = Order::where('status', 'pickedup')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
        $canceledSalesTotal = Order::where('status', 'canceled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        // Prepare data for the chart
        $chartData = [
            'dates' => $salesData->pluck('date')->toArray(),
            'total_sales' => $salesData->pluck('total_sales')->toArray(),
            'reserved_sales' => $salesData->pluck('reserved_sales')->toArray(),
            'pickedup_sales' => $salesData->pluck('pickedup_sales')->toArray(),
            'canceled_sales' => $salesData->pluck('canceled_sales')->toArray(),
            'total_orders' => $totalOrders,
            'reserved_sales_total' => $reservedSalesTotal,
            'pickedup_sales_total' => $pickedUpSalesTotal,
            'canceled_sales_total' => $canceledSalesTotal,
        ];

        // Pass data to the view
        return view('admin.input-sales', compact('chartData', 'startDate', 'endDate'));
    }
    public function generateInputUsers(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Retrieve input and parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Query users data grouped by registration day
        $usersData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as total_users'),
            DB::raw('COUNT(CASE WHEN role = "student" THEN 1 END) as total_students'),
            DB::raw('COUNT(CASE WHEN role = "employee" THEN 1 END) as total_employees'),
            DB::raw('COUNT(CASE WHEN role = "non-employee" THEN 1 END) as total_non_employees')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aggregate totals
        $totalUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalStudents = User::where('role', 'student')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalEmployees = User::where('role', 'employee')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalNonEmployees = User::where('role', 'non-employee')->whereBetween('created_at', [$startDate, $endDate])->count();

        // Prepare data for the chart
        $chartData = [
            'dates' => $usersData->pluck('date')->toArray(),
            'total_users' => $usersData->pluck('total_users')->toArray(),
            'total_students' => $usersData->pluck('total_students')->toArray(),
            'total_employees' => $usersData->pluck('total_employees')->toArray(),
            'total_non_employees' => $usersData->pluck('total_non_employees')->toArray(),
            'total_users_count' => $totalUsers,
            'total_students_count' => $totalStudents,
            'total_employees_count' => $totalEmployees,
            'total_non_employees_count' => $totalNonEmployees,
        ];

        // Pass data to the view
        return view('admin.input-user', compact('chartData', 'startDate', 'endDate'));
    }

    public function downloadInputSales(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $chartImage = $request->input('chart_image'); // Retrieve the Base64 chart image

        $salesData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_sales'),
            DB::raw('SUM(CASE WHEN status = "reserved" THEN total ELSE 0 END) as reserved_sales'),
            DB::raw('SUM(CASE WHEN status = "pickedup" THEN total ELSE 0 END) as pickedup_sales'),
            DB::raw('SUM(CASE WHEN status = "canceled" THEN total ELSE 0 END) as canceled_sales')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartData = [
            'dates' => $salesData->pluck('date')->toArray(),
            'total_sales' => $salesData->pluck('total_sales')->toArray(),
            'reserved_sales' => $salesData->pluck('reserved_sales')->toArray(),
            'pickedup_sales' => $salesData->pluck('pickedup_sales')->toArray(),
            'canceled_sales' => $salesData->pluck('canceled_sales')->toArray(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'reserved_sales_total' => Order::where('status', 'reserved')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'pickedup_sales_total' => Order::where('status', 'pickedup')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'canceled_sales_total' => Order::where('status', 'canceled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
        ];

        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf-input-sales', compact('chartData', 'startDate', 'endDate', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->stream('sales-report.pdf');
    }

    public function downloadInputUsers(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $chartImage = $request->input('chart_image'); // Retrieve the Base64 chart image

        // Query user data grouped by registration day
        $usersData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as total_users'),
            DB::raw('COUNT(CASE WHEN role = "student" THEN 1 END) as total_students'),
            DB::raw('COUNT(CASE WHEN role = "employee" THEN 1 END) as total_employees'),
            DB::raw('COUNT(CASE WHEN role = "non-employee" THEN 1 END) as total_non_employees')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Prepare data for the chart
        $chartData = [
            'dates' => $usersData->pluck('date')->toArray(),
            'total_users' => $usersData->pluck('total_users')->toArray(),
            'total_students' => $usersData->pluck('total_students')->toArray(),
            'total_employees' => $usersData->pluck('total_employees')->toArray(),
            'total_non_employees' => $usersData->pluck('total_non_employees')->toArray(),
            'total_users_count' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_students_count' => User::where('role', 'student')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_employees_count' => User::where('role', 'employee')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_non_employees_count' => User::where('role', 'non-employee')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf-input-user', compact('chartData', 'startDate', 'endDate', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->stream('user-report.pdf');
    }





    public function generateInputRentalReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Query rental data grouped by payment status and day
        $rentalData = DB::table('reservations')
            ->select(
                DB::raw('DATE(reservation_date) as date'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN total_price ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN payment_status = "full payment complete" THEN total_price ELSE 0 END) as full_payment'),
                DB::raw('SUM(CASE WHEN payment_status = "canceled" THEN total_price ELSE 0 END) as canceled'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aggregate totals
        $reportData = [
            'dates' => $rentalData->pluck('date')->toArray(),
            'pending' => $rentalData->pluck('pending')->toArray(),
            'full_payment' => $rentalData->pluck('full_payment')->toArray(),
            'canceled' => $rentalData->pluck('canceled')->toArray(),
            'pending_total' => $rentalData->sum('pending'),
            'full_payment_total' => $rentalData->sum('full_payment'),
            'canceled_total' => $rentalData->sum('canceled'),
            'total_sales' => $rentalData->sum('total'),
        ];

        // Return to view
        return view('admin.input-rentals-reports', compact('startDate', 'endDate', 'reportData'));
    }
    public function downloadInputRentalsReports(Request $request)
    {
        // Validate the request
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Parse dates
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Retrieve the Base64 chart image
        $chartImage = $request->input('chart_image');

        // Query rental data grouped by payment status and day
        $rentalData = DB::table('reservations')
            ->select(
                DB::raw('DATE(reservation_date) as date'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN total_price ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN payment_status = "full payment complete" THEN total_price ELSE 0 END) as full_payment'),
                DB::raw('SUM(CASE WHEN payment_status = "canceled" THEN total_price ELSE 0 END) as canceled'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Aggregate totals
        $reportData = [
            'dates' => $rentalData->pluck('date')->toArray(),
            'pending' => $rentalData->pluck('pending')->toArray(),
            'full_payment' => $rentalData->pluck('full_payment')->toArray(),
            'canceled' => $rentalData->pluck('canceled')->toArray(),
            'pending_total' => $rentalData->sum('pending'),
            'full_payment_total' => $rentalData->sum('full_payment'),
            'canceled_total' => $rentalData->sum('canceled'),
            'total_sales' => $rentalData->sum('total'),
        ];

        // Generate the PDF
        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf-input-rentals', compact('startDate', 'endDate', 'reportData', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();


        return $pdf->stream('rental-sales-report.pdf');
    }


    // FACILITIES REPORTS
    public function generateFacilitespayment(Request $request)
    {
        $currentDate = Carbon::now(); // Define the current date here
        $currentYear = $currentDate->year;
        $currentMonthId = $currentDate->month;

        // Get selected year and month from request, default to current year/month
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonthId = $request->input('month', $currentMonthId);

        // Fetch available months and the selected month
        $availableMonths = MonthName::orderBy('id')->get();
        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId);

        if (!$selectedMonth) {
            $selectedMonth = $availableMonths->first();
            $selectedMonthId = $selectedMonth->id;
        }

        // Calculate the start and end of the selected month
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Fetch data from the Payments table
        $paymentDatas = DB::select("SELECT
                                        SUM(total_price) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total_price, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'completed', total_price, 0)) AS TotalCompletedAmount,
                                        SUM(IF(status = 'canceled', total_price, 0)) AS TotalCanceledAmount
                                      FROM payments
                                      WHERE created_at BETWEEN ? AND ?", [$startOfMonth, $endOfMonth]);

        // Weekly data for the selected month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            if ($startOfWeek->lt($startOfMonth)) {
                $startOfWeek = $startOfMonth;
            }
            if ($endOfWeek->gt($endOfMonth)) {
                $endOfWeek = $endOfMonth;
            }

            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }

        // Fetch totals for each week
        $totalAmounts = [];
        $reservedAmounts = [];
        $completedAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            $paymentData = DB::select(
                "SELECT
                    SUM(total_price) AS TotalAmount,
                    SUM(IF(status = 'reserved', total_price, 0)) AS TotalReservedAmount,
                    SUM(IF(status = 'completed', total_price, 0)) AS TotalCompletedAmount,
                    SUM(IF(status = 'canceled', total_price, 0)) AS TotalCanceledAmount
                FROM payments
                WHERE created_at BETWEEN ? AND ?",
                [$startOfSelectedWeek, $endOfSelectedWeek]
            )[0];

            $totalAmounts[$week] = $paymentData->TotalAmount ?? 0;
            $reservedAmounts[$week] = $paymentData->TotalReservedAmount ?? 0;
            $completedAmounts[$week] = $paymentData->TotalCompletedAmount ?? 0;
            $canceledAmounts[$week] = $paymentData->TotalCanceledAmount ?? 0;
        }

        // Prepare Weekly Data for View
        $AmountW = implode(',', $totalAmounts);
        $ReservedAmountW = implode(',', $reservedAmounts);
        $CompletedAmountW = implode(',', $completedAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);

        // Calculate overall totals for weekly data display
        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservedAmounts);
        $TotalCompletedAmountW = array_sum($completedAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);

        // Monthly data for the selected year
        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
                                            IFNULL(D.TotalAmount, 0) AS TotalAmount,
                                            IFNULL(D.TotalReservedAmount, 0) AS TotalReservedAmount,
                                            IFNULL(D.TotalCompletedAmount, 0) AS TotalCompletedAmount,
                                            IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount
                                         FROM month_names M
                                         LEFT JOIN (
                                             SELECT
                                                 MONTH(created_at) AS MonthNo,
                                                 SUM(total_price) AS TotalAmount,
                                                 SUM(IF(status='reserved', total_price, 0)) AS TotalReservedAmount,
                                                 SUM(IF(status='completed', total_price, 0)) AS TotalCompletedAmount,
                                                 SUM(IF(status='canceled', total_price, 0)) AS TotalCanceledAmount
                                             FROM payments
                                             WHERE YEAR(created_at) = ?
                                             GROUP BY MONTH(created_at)
                                         ) D ON D.MonthNo = M.id
                                         ORDER BY M.id", [$selectedYear]);

        // Prepare Monthly Data for View
        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $CompletedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCompletedAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        // Calculate overall totals for monthly data display
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalCompletedAmount = collect($monthlyDatas)->sum('TotalCompletedAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        // Fetch year range for dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // Return View with all data
        return view('admin.report-facilities', compact(
            'AmountM',
            'ReservedAmountM',
            'CompletedAmountM',
            'CanceledAmountM',
            'TotalAmount',
            'TotalReservedAmount',
            'TotalCompletedAmount',
            'TotalCanceledAmount',
            'AmountW',
            'ReservedAmountW',
            'CompletedAmountW',
            'CanceledAmountW',
            'TotalAmountW',
            'TotalReservedAmountW',
            'TotalCompletedAmountW',
            'TotalCanceledAmountW',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'yearRange'
        ));
    }
}
