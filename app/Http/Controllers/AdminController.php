<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\User;
use App\Models\Order;
use App\Models\Slide;
use App\Models\Rental;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use App\Models\MonthName;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Mail\ReplyToContact;
use Illuminate\Http\Request;
use App\Models\DormitoryRoom;
use App\Models\ContactReplies;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProductAttribute;
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


class AdminController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $yearRange = range($currentYear, $currentYear - 10);

        // Get low stock products
        $products = $this->getLowStockProducts();
        $dashboardData = [$this->getDashboardSummary($currentYear)];

        // Get recent orders
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

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
    public function categories()
    {
        // $categories = Category::orderBy('id', 'DESC')->paginate(10);
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('id', 'DESC')
            ->paginate(5);
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
            'slug' => 'unique:categories,slug',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ], [
            'name.required' => 'The category name is required.',
            'slug.unique' => 'The slug must be unique. This slug is already taken.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbnailsImage($image, $file_name);
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
            'slug' => 'unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->parent_id;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateCategoryThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been updated successfully!');
    }

    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->getRealPath());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully!');
    }


    public function products(Request $request)
    {
        $archived = $request->query('archived', 0);
        $search = $request->input('search');
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'DESC');

        $query = Product::with([
            'category' => function ($query) {
                $query->with('parent');
            },
            'attributes',
            'attributeValues.productAttribute'
        ])
            ->where('archived', $archived);
        $isNumeric = is_numeric($search);

        if ($search) {
            $query->where(function ($q) use ($search, $isNumeric) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if ($isNumeric) {
                    $q->orWhere('quantity', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%");
                }
            });
            if ($isNumeric) {
                $exactQuantityMatch = Product::where('quantity', $search)->exists();
                if ($exactQuantityMatch) {
                    $sortColumn = 'quantity';
                } else {
                    $priceMatch = Product::where('price', 'like', "%{$search}%")->exists();
                    if ($priceMatch) {
                        $sortColumn = 'price';
                    }
                }
            } else {
                $sortColumn = 'name';
            }
        }
        $query->orderBy($sortColumn, $sortDirection);

        $products = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'products' => view('partials._products-table', compact('products'))->render(),
                'pagination' => view('partials._products-pagination', compact('products'))->render()
            ]);
        }

        return view('admin.products', compact('products', 'archived'));
    }

    public function product_add()
    {

        $categories = Category::with('children')->whereNull('parent_id')->get();
        $productAttributes = ProductAttribute::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'productAttributes'));
    }

    public function product_store(Request $request)
    {
        // Determine if the product has variants based on the presence of variant fields
        $hasVariant = $request->filled('variant_name') &&
            $request->filled('product_attribute_id') &&
            $request->filled('variant_price') &&
            $request->filled('variant_quantity');

        // Validate the incoming request data
        $request->validate([
            'name' => 'required',
            'slug' => 'unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer',
            'featured' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:10240',
            'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',

            // **Added Validation Rules for Stock Status Fields**
            // 'instock_quantity' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'required|integer|min:0',
        ], [
            'category_id.integer' => 'Please select a valid category.',
            'sex.in' => 'Please select a valid gender category.',
            // **Optional Custom Messages for Stock Status Fields**
            // 'instock_quantity.required' => 'In Stock Quantity is required.',
            'reorder_quantity.required' => 'Reorder Quantity is required.',
            'outofstock_quantity.required' => 'Out of Stock Quantity is required.',
        ]);

        // Create a new Product instance and assign values from the request
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->price = $hasVariant ? null : $request->price;
        $product->quantity = $hasVariant ? null : $request->quantity;
        $product->stock_status = $hasVariant ? 'instock' : 'outofstock';
        $product->featured = $request->featured;
        $product->sex = $request->sex;
        $product->category_id = $request->category_id;

        // **Assign the New Stock Status Fields to the Product**
        // $product->instock_quantity = $request->instock_quantity;
        $product->reorder_quantity = $request->reorder_quantity;
        $product->outofstock_quantity = $request->outofstock_quantity;
        $current_timestamp = Carbon::now()->timestamp;


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }

        // Handle gallery images upload
        $gallery_arr = [];
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);

                if ($gcheck) {
                    $gFileName = $current_timestamp . "." . $counter . '.' . $gextension;
                    $this->GenerateProductThumbnailsImage($file, $gFileName);
                    array_push($gallery_arr, $gFileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;

        $product->save();

        // Handle variants
        if ($hasVariant && is_array($request->variant_name)) {
            $attributeValues = [];
            foreach ($request->variant_name as $index => $variantName) {
                $attributeValues[] = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $variantName,
                    'price' => $request->variant_price[$index],
                    'quantity' => $request->variant_quantity[$index],
                ];
            }

            foreach ($attributeValues as $value) {
                ProductAttributeValue::create($value);
            }

            // $totalVariantQuantity = $product->attributeValues->sum('quantity');

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
            // Determine stock status for products without variants
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

        // Determine if product has variants
        $hasVariant = !empty($request->variant_name) && is_array($request->variant_name);

        // Validation
        $request->validate([
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'price' => $hasVariant ? 'nullable' : 'required|numeric',
            'quantity' => $hasVariant ? 'nullable' : 'required|integer|min:0',
            'featured' => 'required|boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:5120', // 5MB limit
            'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:5120', // 5MB limit
            'sex' => 'required|in:male,female,all',
            'category_id' => 'required|integer|exists:categories,id',
            'reorder_quantity' => 'required|integer|min:0',
            'outofstock_quantity' => 'required|integer|min:0',
        ]);

        // Store previous stock status
        $previousStockStatus = $product->stock_status;

        // Update product fields
        $product->fill($request->except(['image', 'images', 'variant_name', 'product_attribute_id', 'variant_price', 'variant_quantity', 'existing_variant_ids', 'removed_variant_ids']));

        $current_timestamp = Carbon::now()->timestamp;

        // Handle image upload (retain existing image if no new upload)
        if ($request->hasFile('image')) {
            if (!empty($product->image) && File::exists(public_path("uploads/products/{$product->image}"))) {
                File::delete(public_path("uploads/products/{$product->image}"));
            }
            $image = $request->file('image');
            $imageName = "{$current_timestamp}.{$image->extension()}";
            $this->GenerateProductThumbnailsImage($image, $imageName);
            $product->image = $imageName;
        }

        // Handle gallery images
        $existingImages = !empty($product->images) ? explode(',', $product->images) : [];
        $removedImages = $request->input('removed_images', []);

        // Remove deleted images
        foreach ($removedImages as $removedImage) {
            if (File::exists(public_path("uploads/products/{$removedImage}"))) {
                File::delete(public_path("uploads/products/{$removedImage}"));
            }
            $existingImages = array_diff($existingImages, [$removedImage]);
        }

        // Handle new gallery images
        if ($request->hasFile('images')) {
            $newImages = [];
            $maxGalleryImages = 5;
            $remainingSlots = $maxGalleryImages - count($existingImages);

            if ($remainingSlots > 0) {
                foreach ($request->file('images') as $index => $file) {
                    if ($index >= $remainingSlots) break; // Stop if we've reached the limit

                    $gfilename = "{$current_timestamp}-" . ($index + 1) . ".{$file->extension()}";
                    $this->GenerateProductThumbnailsImage($file, $gfilename);
                    $newImages[] = $gfilename;
                }
            }

            // Combine existing and new images
            $allImages = array_merge($existingImages, $newImages);
            $product->images = implode(',', $allImages);
        } else {
            // If no new images, just update with existing ones
            $product->images = implode(',', $existingImages);
        }

        $product->save();

        // Handle variant removal first
        $removedVariantIds = $request->input('removed_variant_ids', []);
        if (!empty($removedVariantIds)) {
            ProductAttributeValue::whereIn('id', $removedVariantIds)->delete();
        }

        // Handle variants
        if ($hasVariant) {
            $existingVariantIds = $request->input('existing_variant_ids', []);
            $attributeValues = [];

            foreach ($request->variant_name as $index => $name) {
                $attributeValue = [
                    'product_id' => $product->id,
                    'product_attribute_id' => $request->product_attribute_id[$index],
                    'value' => $name,
                    'price' => $request->variant_price[$index] ?? null,
                    'quantity' => $request->variant_quantity[$index] ?? 0,
                ];

                // Check if this is an existing variant or a new one
                if (isset($existingVariantIds[$index]) && !empty($existingVariantIds[$index])) {
                    // Update existing variant
                    $existingVariant = ProductAttributeValue::find($existingVariantIds[$index]);
                    if ($existingVariant) {
                        $existingVariant->update($attributeValue);
                        $attributeValues[] = $attributeValue;
                    }
                } else {
                    // Create new variant
                    ProductAttributeValue::create($attributeValue);
                    $attributeValues[] = $attributeValue;
                }
            }

            // Get total quantity from all variants (including newly created ones)
            $variantTotalQuantity = $product->attributeValues()->sum('quantity');

            // Stock status based on variant quantities
            if ($variantTotalQuantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($variantTotalQuantity <= $product->reorder_quantity && $variantTotalQuantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
        } else {
            // If no variants, remove all existing variants
            $product->attributeValues()->delete();

            // Determine stock status for products without variants
            if ($product->quantity > $product->reorder_quantity) {
                $product->stock_status = 'instock';
            } elseif ($product->quantity <= $product->reorder_quantity && $product->quantity > $product->outofstock_quantity) {
                $product->stock_status = 'reorder';
            } else {
                $product->stock_status = 'outofstock';
            }
        }

        $product->save();

        // Notify users if stock status changes
        if ($product->stock_status === 'instock' && $previousStockStatus !== 'instock') {
            $users = User::where('utype', 'USR')->get();
            Notification::send($users, new StockUpdate($product, "Good news! The product {$product->name} is now back in stock."));
        }

        return redirect()->route('admin.products')->with('status', 'Product has been updated successfully!');
    }



    public function GenerateProductThumbnailsImage($image, $imageName)
    {

        try {

            $destinationPathThumbnail = public_path('uploads/products/thumbnails');
            $destinationPath = public_path('uploads/products');
            $img = Image::read($image->getRealPath());
            $img->cover(689, 689, "center");
            $img->resize(689, 689, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);

            $img->resize(204, 204, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPathThumbnail . '/' . $imageName);

            \Log::info('Image saved successfully: ' . $imageName);
        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
        }
    }

    public function archivedProducts($id)
    {
        $product = Product::findOrFail($id);
        $product->archived = 1;
        $product->archived_at = \Carbon\Carbon::now();
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

    public function product_attribute_delete($id)
    {
        $attribute = ProductAttribute::find($id);
        $attribute->delete();
        return redirect()->route('admin.product-attributes')->with('status', 'Product Variant has been deleted successfully!');
    }


    public function orders(Request $request)
    {
        $status = $request->input('status');
        $timeSlot = $request->input('time_slot');

        $query = Order::query();

        $query->when($status, fn($q) => $q->where('status', $status))
            ->when($timeSlot, fn($q) => $q->where('time_slot', $timeSlot));

        $orders = $query
            ->orderByRaw("FIELD(status, 'reserved', 'canceled', 'pickedup')")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'orders' => view('partials._orders-table', compact('orders'))->render(),
                'pagination' => view('partials._orders-pagination', compact('orders'))->render(),
                'count' => $orders->total()
            ]);
        }

        return view('admin.orders', compact('orders'));
    }

    // This route handles the filter functionality
    public function filterOrders(Request $request)
    {
        $status = $request->input('status');
        $timeSlot = $request->input('time_slot');

        $query = Order::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($timeSlot) {
            $query->where('time_slot', $timeSlot);
        }

        $orders = $query->orderBy('created_at', 'DESC')->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'orders' => view('partials._orders-table', compact('orders'))->render(),
                'pagination' => view('partials._orders-pagination', compact('orders'))->render(),
                'count' => $orders->total()
            ]);
        }

        return redirect()->route('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function update_order_status(Request $request)
    {
        $request->validate([
            'order_status' => 'required|in:reserved,canceled'
        ]);
        $order = Order::findOrFail($request->order_id);
        $order->status = $request->order_status;


        if ($request->order_status === 'canceled') {
            $order->canceled_date = Carbon::now();
        }
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
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'amount_paid' => $request->amount_paid,
                'change' => $change,
                'status' => 'paid',
            ]);
            $order->update([
                'status' => 'pickedup',
                'picked_up_date' => now(),
            ]);
            return response()->json([
                'message' => 'Payment completed successfully!',
                'order_id' => $order->id,
                'transaction_id' => $transaction->id
            ]);
        });
    }

    public function downloadReceipt(Request $request, Order $order)
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
        return $pdf->download('receipt_order_' . $order->id . '.pdf');
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
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide   added successfully!');
    }

    public function GenerateSlideThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->getRealPath());
        $img->cover(1920, 1080, "top");
        $img->resize(1920, 1080, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
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
            $this->GenerateSlideThumbnailsImage($image, $file_name);
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
    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }
    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route(route: 'admin.contacts')->with('status', 'Contact has been deleted successfully !');
    }

    public function contact_reply(Request $request, $id)
    {
        // Find the contact record by its ID
        $contact = Contact::find($id);
        if (!$contact) {
            // Handle the case where the contact doesn't exist
            return redirect()->route('admin.contacts')->with('error', 'Contact not found.');
        }

        // Validate the reply message input
        $validated = $request->validate([
            'replyMessage' => 'required|string',
        ]);

        // Save the admin's reply to the database
        $reply = new ContactReplies();
        $reply->contact_id = $contact->id;
        $reply->admin_reply = $request->input('replyMessage');
        $reply->admin_id = Auth::id();
        $reply->save();


        Mail::to($contact->email)->send(new ReplyToContact($contact, $request->input('replyMessage')));


        return redirect()->route('admin.contacts')->with('status', 'Reply sent successfully!');
    }



    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }


    public function markMultipleAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids');
        if (!$notificationIds || empty($notificationIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No notification IDs provided.',
            ], 400);
        }

        $notifications = Auth::user()->notifications()->whereIn('id', $notificationIds)->get();

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching notifications found.',
            ], 404);
        }

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }


    public function latest()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10) // Fetch the latest 10 notifications
            ->get();

        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? 'No message available',
                'icon' => match ($notification->type) {
                    'App\\Notifications\\LowStockNotification' => 'fa-solid fa-box',
                    'App\\Notifications\\ContactReceivedMessage' => 'fas fa-envelope',
                    default => 'fas fa-bell',
                },
                'redirect_route' => match ($notification->type) {
                    'App\\Notifications\\LowStockNotification' => route('admin.products'),
                    'App\\Notifications\\ContactReceivedMessage' => route('admin.contacts'),
                    default => '#',
                },
                'created_at' => $notification->created_at->diffForHumans(),
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json([
            'unreadCount' => $user->unreadNotifications->count(),
            'notifications' => $formattedNotifications,
        ]);
    }




    // Delete multiple notifications
    public function deleteMultipleNotifications(Request $request)
    {
        $notificationIds = $request->input('notification_ids');
        Auth::user()->notifications()->whereIn('id', $notificationIds)->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    // Reports Page
    public function generateReport(Request $request)
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

        // Fetch the latest 10 orders
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        // Fetch dashboard data for the selected month and year
        $dashboardDatas = DB::select("SELECT
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                                        COUNT(*) AS Total,
                                        SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                                        SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp,
                                        SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                                      FROM Orders
                                      WHERE created_at BETWEEN ? AND ?", [$startOfMonth, $endOfMonth]);

        // Weekly data for the selected month
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

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
        $totalAmounts = [];
        $reservationAmounts = [];
        $pickedUpAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
            // Fetch total amounts for the week
            $dashboardData = DB::select(
                "SELECT
                                            SUM(total) AS TotalAmount,
                                            SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                            SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                            SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                                        FROM Orders
                                        WHERE created_at BETWEEN ? AND ?",
                [$startOfSelectedWeek, $endOfSelectedWeek]
            )[0];

            $totalAmounts[$week] = $dashboardData->TotalAmount ?? 0;
            $reservationAmounts[$week] = $dashboardData->TotalReservedAmount ?? 0;
            $pickedUpAmounts[$week] = $dashboardData->TotalPickedUpAmount ?? 0;
            $canceledAmounts[$week] = $dashboardData->TotalCanceledAmount ?? 0;
        }

        // Prepare Weekly Data for View
        $AmountW = implode(',', $totalAmounts);
        $ReservationAmountW = implode(',', $reservationAmounts);
        $PickedUpAmountW = implode(',', $pickedUpAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);

        // Calculate overall totals for weekly data display
        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservationAmounts);
        $TotalPickedUpAmountW = array_sum($pickedUpAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);

        // Monthly data for the selected year
        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
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
                                     ORDER BY M.id", [$selectedYear]);

        // Prepare Monthly Data for View
        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', collect($monthlyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        // Calculate overall totals for monthly data display
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmount = collect($monthlyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        // Calculate the range of years to show in the dropdown
        $yearRange = range($currentYear, $currentYear - 10);

        // New Code (Adding daily data)
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();
        $selectedWeekId = $request->input('week', $currentDate->weekOfMonth);

        $selectedWeek = $availableWeeks->firstWhere('week_number', $selectedWeekId);
        if (!$selectedWeek) {
            $selectedWeek = $availableWeeks->first();
            $selectedWeekId = $selectedWeek->week_number;
        }

        // Define the start and end of the selected week
        if (array_key_exists($selectedWeekId, $weekRanges)) {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId];
        } else {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[1]; // Default to week 1
        }

        // Query for daily data within the selected week, grouped by day
        $dailyDatasRaw = DB::select(
            "SELECT DAYOFWEEK(created_at) AS DayNo,
                                        DAYNAME(created_at) AS DayName,
                                        SUM(total) AS TotalAmount,
                                        SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                                        SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                                        SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                                       FROM Orders
                                       WHERE created_at BETWEEN ? AND ?
                                       GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
                                       ORDER BY DayNo",
            [$startOfSelectedWeek, $endOfSelectedWeek]
        );

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
                    'DayNo' => null,
                    'DayName' => $day,
                    'TotalAmount' => 0,
                    'TotalReservedAmount' => 0,
                    'TotalPickedUpAmount' => 0,
                    'TotalCanceledAmount' => 0,
                ];
            }
        });

        // Prepare Daily Data for View
        $AmountD = implode(',', $sortedDailyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', $sortedDailyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', $sortedDailyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', $sortedDailyDatas->pluck('TotalCanceledAmount')->toArray());

        $TotalAmountD = $sortedDailyDatas->sum('TotalAmount');
        $TotalReservedAmountD = $sortedDailyDatas->sum('TotalReservedAmount');
        $TotalPickedUpAmountD = $sortedDailyDatas->sum('TotalPickedUpAmount');
        $TotalCanceledAmountD = $sortedDailyDatas->sum('TotalCanceledAmount');

        // Return View with all data
        $pageTitle = 'Reports';
        return view('admin.reports', compact(
            'orders',
            'dashboardDatas',
            'AmountM',
            'ReservationAmountM',
            'PickedUpAmountM',
            'CanceledAmountM',
            'TotalAmount',
            'TotalReservedAmount',
            'TotalPickedUpAmount',
            'TotalCanceledAmount',
            'AmountW',
            'ReservationAmountW',
            'PickedUpAmountW',
            'CanceledAmountW',
            'TotalAmountW',
            'TotalReservedAmountW',
            'TotalPickedUpAmountW',
            'TotalCanceledAmountW',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'yearRange',
            'sortedDailyDatas',
            'AmountD',
            'ReservationAmountD',
            'PickedUpAmountD',
            'CanceledAmountD',
            'TotalAmountD',
            'TotalReservedAmountD',
            'TotalPickedUpAmountD',
            'TotalCanceledAmountD',
            'selectedWeekId',
            'availableWeeks',
            'pageTitle'
        ));
    }

    public function generateProduct(Request $request)
    {
        // Get the current year and month
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        // Most frequent products data - simplified query for testing
        $mostFrequentProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        // Least bought products data - simplified query for testing
        $leastBoughtProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.name',
                DB::raw('COUNT(order_items.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_orders')
            ->limit(10)
            ->get();

        // Prepare data for charts
        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        // Available months and year range for the form
        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        return view('admin.report-product', compact(
            'mostFrequentLabels',
            'mostFrequentData',
            'leastBoughtLabels',
            'leastBoughtData',
            'availableMonths',
            'yearRange',
            'selectedMonth',
            'selectedYear'
        ));
    }

    public function generateUser(Request $request)
    {
        // Set default year and month from current date.
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        // Retrieve available weeks from the seeded week_names table.
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();

        // (Optional) Variables for a custom date-range report.
        $newUsersCount = null;
        $newUsers      = null;
        $startDate     = null;
        $endDate       = null;
        $chartData     = null;

        // If a date-range form was submitted, validate and compute its data.
        if ($request->isMethod('POST') && $request->has(['start_date', 'end_date'])) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();

            $newUsersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

            $userRegistrations = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $chartData = [
                'dates'  => $userRegistrations->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })->toArray(),
                'counts' => $userRegistrations->pluck('count')->toArray(),
            ];

            $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();
        }

        // Retrieve statistics for the dashboard.
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $activeUsers = User::where('isDefault', false)->count(); // Customize your query as needed.

        $growthRate = ($totalUsers > 0) ? (($newUsersThisMonth / $totalUsers) * 100) : 0;

        // Retrieve recently registered users (limit 10).
        $recentUsers = User::orderBy('created_at', 'DESC')->take(10)->get();

        // === Monthly Chart Data ===
        $userRegistrations = DB::select("
            SELECT COUNT(*) AS TotalUsers, MONTH(created_at) AS MonthNo
            FROM users
            WHERE YEAR(created_at) = ?
            GROUP BY MonthNo
            ORDER BY MonthNo
        ", [$selectedYear]);

        // Build an array with one entry per month (fill missing months with 0).
        $monthlyData = array_fill(1, 12, 0);
        foreach ($userRegistrations as $data) {
            $monthlyData[$data->MonthNo] = $data->TotalUsers;
        }
        $userRegistrationsByMonth = implode(',', $monthlyData);

        // === Weekly Chart Data ===
        $weeklyData = array_fill(1, 6, 0);
        $userCounts = DB::table('users')
            ->selectRaw('WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week_number')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->groupBy('week_number')
            ->get();
        foreach ($userCounts as $count) {
            $weekIndex = $count->week_number;
            if (isset($weeklyData[$weekIndex])) {
                $weeklyData[$weekIndex] = $count->count;
            }
        }
        $weeklyChartData = implode(',', $weeklyData);

        // === Daily Chart Data (Filtered by Week) ===
        // Determine start and end of the selected month.
        $startOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfMonth   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

        // Calculate week ranges for the month.
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

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

        // Get selected week from the request; default to week 1.
        $selectedWeekId = $request->input('week', 1);
        if (!array_key_exists($selectedWeekId, $weekRanges)) {
            $selectedWeekId = 1;
        }
        list($dailyStart, $dailyEnd) = $weekRanges[$selectedWeekId];

        // Query daily registration counts within the selected week.
        $dailyCounts = DB::table('users')
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count')
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        // We want to display data for Monday through Sunday.
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyData = array_fill(0, 7, 0);
        foreach ($dailyCounts as $count) {
            $index = array_search($count->day_name, $daysOfWeek);
            if ($index !== false) {
                $dailyData[$index] = $count->count;
            }
        }
        $dailyChartData = implode(',', $dailyData);

        // Retrieve available months from the seeded month_names table.
        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        $pageTitle = 'User Registrations Report';

        return view('admin.report-user', compact(
            'totalUsers',
            'newUsersThisMonth',
            'activeUsers',
            'growthRate',
            'recentUsers',
            'userRegistrationsByMonth',
            'weeklyChartData',
            'dailyChartData',
            'availableMonths',
            'yearRange',
            'selectedMonth',
            'selectedYear',
            'availableWeeks',
            'pageTitle',
            'newUsersCount',
            'newUsers',
            'startDate',
            'endDate',
            'chartData',
            'selectedWeekId'
        ))->with('showChart', true);
    }

    public function generateInventory(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->back()->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])->withInput();
        }

        // Build the query for products
        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Filter products based on updated_at date
            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        // Get the products
        $products = $productsQuery->get();

        // Apply stock status filter if provided
        $stockStatus = $request->input('stock_status');

        if ($stockStatus) {
            $products = $products->filter(function ($product) use ($stockStatus) {
                $currentStock = $product->attributeValues->isNotEmpty()
                    ? $product->attributeValues->sum('quantity')
                    : $product->current_stock;

                if ($stockStatus == 'instock') {
                    return $currentStock > $product->reorder_quantity;
                } elseif ($stockStatus == 'outofstock') {
                    return $currentStock <= $product->outofstock_quantity;
                } elseif ($stockStatus == 'reorder') {
                    return $currentStock > $product->outofstock_quantity && $currentStock <= $product->reorder_quantity;
                }

                return true;
            });
        }

        // Paginate the results manually
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $products->count();
        $products = $products->forPage($page, $perPage);

        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $products,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.report-inventory', compact('products', 'startDate', 'endDate'));
    }




    public function generateBillingStatement($orderId)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($orderId);
        return view('admin.report-statement', compact('order'));
    }

    public function listBillingStatements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'today' => 'nullable|boolean',
            'search' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->back()->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])->withInput();
        }

        $ordersQuery = Order::with('user');

        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $ordersQuery->whereHas('user', function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.report-statements', compact('orders', 'startDate', 'endDate'));
    }



    // DomPDF for Reports Products Page
    public function downloadBillingStatements(Request $request)
    {
        // Validate the date inputs
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'today' => 'nullable|boolean',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->route('admin.report-statements')
                ->withErrors($validator)
                ->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-statements')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        // Build the query for orders
        $ordersQuery = Order::with('user');

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();

        // Load the view for PDF rendering
        $pdf = PDF::loadView('admin.pdf-billing', [
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate
        ])
            ->setPaper('A4', 'portrait');




        // Return the generated PDF for download
        return $pdf->download('billing_statements.pdf');
    }

    public function downloadPdf(Request $request)
    {
        // Fetch values from the form submission
        $data = [
            'total_amount' => $request->input('total_amount'),
            'total_reserved_amount' => $request->input('total_reserved_amount'),
            'total_picked_up_amount' => $request->input('total_picked_up_amount'),
            'total_canceled_amount' => $request->input('total_canceled_amount'),
            'total_amount_w' => $request->input('total_amount_w'),
            'total_reserved_amount_w' => $request->input('total_reserved_amount_w'),
            'total_picked_up_amount_w' => $request->input('total_picked_up_amount_w'),
            'total_canceled_amount_w' => $request->input('total_canceled_amount_w'),
            'total_amount_d' => $request->input('total_amount_d'),
            'total_reserved_amount_d' => $request->input('total_reserved_amount_d'),
            'total_picked_up_amount_d' => $request->input('total_picked_up_amount_d'),
            'total_canceled_amount_d' => $request->input('total_canceled_amount_d'),
            'selectedYear'             => $request->input('selected_year'),
            'selectedMonth'            => (object)['name' => $request->input('selected_month_name')],
            'selectedWeekId'           => $request->input('selected_week_id'),
        ];

        // Generate the PDF using the data only (no graph images)
        $pdf = PDF::loadView('admin.pdf-reports', $data)
            ->setPaper('A4', 'portrait'); // Set paper size to A4, portrait orientation

        // Download the PDF
        return $pdf->download('sales_report.pdf');
    }



    public function downloadProduct(Request $request)
    {
        // Get the data again for the PDF (same logic as in generateProduct)
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        $mostFrequentProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        $leastBoughtProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.name',
                DB::raw('COUNT(order_items.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_orders')
            ->limit(10)
            ->get();

        // Prepare data for the PDF view
        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        // Initialize DomPDF
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set("isHtml5ParserEnabled", true);
        // Uncomment the following line if you need to load remote images:
        $options->setIsRemoteEnabled(true);
        $dompdf->setOptions($options);

        // Load HTML content from the view (which now includes the header and images)
        $html = view('admin.pdf-products', compact(
            'mostFrequentLabels',
            'mostFrequentData',
            'leastBoughtLabels',
            'leastBoughtData'
        ))->render();

        // Load HTML and render PDF
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Output the generated PDF to browser
        return $dompdf->stream('product-report.pdf');
    }



    public function getMonthlyRegisteredUsers($month, $year)
    {
        // Fetch the number of users registered in the specified month and year
        return User::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count() ?: 0;
    }

    public function getWeeklyRegisteredUsers($month, $year)
    {

        return User::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count() ?: 0;
    }

    public function getDailyRegisteredUsers($month, $year)
    {
        return User::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('DAY(created_at)'))
            ->select(DB::raw('DAY(created_at) as day'), DB::raw('count(*) as count'))
            ->get() ?: [];
    }

    public function getRecentUsers()
    {
        return User::latest()->take(5)->get() ?: [];
    }
    public function downloadUserReportPdf(Request $request)
    {
        $selectedYear  = $request->input('selectedYear');
        $selectedMonth = $request->input('selectedMonth');
        $selectedWeekId = $request->input('week', 1);

        // --- Recompute Monthly Data ---
        $userRegistrations = DB::select("
            SELECT COUNT(*) AS TotalUsers, MONTH(created_at) AS MonthNo
            FROM users
            WHERE YEAR(created_at) = ?
            GROUP BY MonthNo
            ORDER BY MonthNo
        ", [$selectedYear]);

        $monthlyData = array_fill(1, 12, 0);
        foreach ($userRegistrations as $data) {
            $monthlyData[$data->MonthNo] = $data->TotalUsers;
        }
        $userRegistrationsByMonth = implode(',', $monthlyData);

        // --- Recompute Weekly Data ---
        $weeklyData = array_fill(1, 6, 0);
        $userCounts = DB::table('users')
            ->selectRaw('WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week_number')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->groupBy('week_number')
            ->get();

        foreach ($userCounts as $count) {
            $weekIndex = $count->week_number;
            if (isset($weeklyData[$weekIndex])) {
                $weeklyData[$weekIndex] = $count->count;
            }
        }
        $weeklyChartData = implode(',', $weeklyData);

        // --- Recompute Daily Data (for the selected week) ---
        $startOfMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfMonth   = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
        $weekRanges = [];

        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(\Carbon\Carbon::MONDAY);
            $endOfWeek   = $startOfWeek->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

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
        list($dailyStart, $dailyEnd) = $weekRanges[$selectedWeekId];

        $dailyCounts = DB::table('users')
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count')
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyData = array_fill(0, 7, 0);
        foreach ($dailyCounts as $count) {
            $index = array_search($count->day_name, $days);
            if ($index !== false) {
                $dailyData[$index] = $count->count;
            }
        }
        $dailyChartData = implode(',', $dailyData);

        // Get the recent users.
        $recentUsers = User::orderBy('created_at', 'DESC')->take(10)->get();

        // Prepare data for the PDF view.
        $pdfData = [
            'recentUsers'               => $recentUsers,
            'userRegistrationsByMonth'  => $userRegistrationsByMonth,
            'weeklyChartData'           => $weeklyChartData,
            'dailyChartData'            => $dailyChartData,
            'selectedMonth'             => $selectedMonth,
            'selectedYear'              => $selectedYear,
            'selectedWeekId'            => $selectedWeekId,
        ];

        $pdf = PDF::loadView('admin.pdf-user', $pdfData)
            ->setPaper('a4', 'portrait');

        return $pdf->download('user_report_' . $selectedYear . '_' . $selectedMonth . '.pdf');
    }
    public function downloadInventoryReportPdf(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
        ]);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->route('admin.report-inventory')
                ->withErrors($validator)
                ->withInput();
        }

        // Check if "Today" filter is applied
        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        // Additional validation: Ensure end_date is after or equal to start_date
        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-inventory')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        // Build the query for products
        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        // Apply date filter if dates are provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Filter products based on updated_at date
            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        // Get the products
        $products = $productsQuery->get();

        // Apply stock status filter if provided
        $stockStatus = $request->input('stock_status');

        if ($stockStatus) {
            $products = $products->filter(function ($product) use ($stockStatus) {
                $currentStock = $product->attributeValues->isNotEmpty()
                    ? $product->attributeValues->sum('quantity')
                    : $product->current_stock;

                if ($stockStatus == 'instock') {
                    return $currentStock > $product->reorder_quantity;
                } elseif ($stockStatus == 'outofstock') {
                    return $currentStock <= $product->outofstock_quantity;
                } elseif ($stockStatus == 'reorder') {
                    return $currentStock > $product->outofstock_quantity && $currentStock <= $product->reorder_quantity;
                }

                return true;
            });
        }

        // Load the view for PDF rendering
        $pdfView = view('admin.pdf-inventory-report', compact('products', 'startDate', 'endDate'))->render();

        // DomPDF options to improve performance
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Initialize DomPDF and load the HTML
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);

        // Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Download the generated PDF file
        return $dompdf->stream('inventory_report.pdf', ['Attachment' => true]);
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


    public function searchproduct(Request $request)
    {
        $query = strtolower($request->input('query'));

        // Check if the query matches certain keywords and redirect accordingly

        //product
        if (str_contains(strtolower($query), 'add product')) {
            return redirect()->route('admin.product.add');
        } elseif (str_contains(strtolower($query), 'products view')) {
            return redirect()->route('admin.products');
        } elseif (str_contains(strtolower($query), 'add product attributes')) {
            return redirect()->route('admin.product-attribute-add');
        } elseif (str_contains(strtolower($query), 'view product attributes')) {
            return redirect()->route('admin.product-attributes');
        } elseif (str_contains(strtolower($query), 'addcategory')) {
            return redirect()->route('admin.category.add');
        } elseif (str_contains(strtolower($query), 'categories')) {
            return redirect()->route('admin.categories');
        } elseif (str_contains(strtolower($query), 'users')) {
            return redirect()->route('admin.users');
        } elseif (str_contains(strtolower($query), 'order')) {
            return redirect()->route('admin.orders');

            //product report

        } elseif (str_contains(strtolower($query), 'product report')) {
            return redirect()->route('admin.report-product');
        } elseif (str_contains(strtolower($query), 'sales products')) {
            return redirect()->route('admin.reports');
        } elseif (str_contains(strtolower($query), 'user report')) {
            return redirect()->route('admin.report-user');
        } elseif (str_contains(strtolower($query), 'inventory')) {
            return redirect()->route('admin.report-inventory');
        } elseif (str_contains(strtolower($query), 'statements')) {
            return redirect()->route('admin.report-statements');


            //rentals
        } elseif (str_contains(strtolower($query), 'add rental')) {
            return redirect()->route('admin.rental.add');
        } elseif (str_contains(strtolower($query), 'rentals view')) {
            return redirect()->route('admin.rentals');
        } elseif (str_contains(strtolower($query), 'rental reservation')) {
            return redirect()->route('admin.reservation');


            //rental report

        } elseif (str_contains(strtolower($query), 'sales rental')) {
            return redirect()->route('admin.rentals_reports');
        } elseif (str_contains(strtolower($query), 'messages')) {
            return redirect()->route('admin.contacts');
        } elseif (str_contains(strtolower($query), 'slides')) {
            return redirect()->route('admin.slides');
        } elseif (str_contains(strtolower($query), 'Reservation Reports')) {
            return redirect()->route('admin.rentalsReportsName');
        }

        // Add more conditions as needed
        return back()->with('error', 'No matching results found.');
    }

    public function updateStatus(Request $request,  Reservation $reservation)
    {

        $reservation->update([
            'payment_status' => $request->input('payment_status'),
            'rent_status' => $request->input('rent_status'),
        ]);


        // Validate the incoming request
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'rent_status' => 'required|in:pending,reserved,completed,canceled',
        ]);

        try {
            // Find the reservation by ID
            $reservation = Reservation::findOrFail($request->reservation_id);

            // Update the status
            $reservation->rent_status = $request->rent_status;
            $reservation->save();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Reservation status updated successfully!',
            ]);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the reservation status.',
            ], 500);
        }
    }

    public function filterReservations(Request $request)
    {
        $query = Reservation::query();

        // Filter by Rent Status
        if ($request->filled('rent_status')) {
            $query->where('rent_status', $request->rent_status);
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by Rental Type
        if ($request->filled('rental_type')) {
            $query->whereHas('rental', function ($q) use ($request) {
                $q->where('name', $request->rental_type);
            });
        }

        $reservations = $query->get();

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('reservations.filtered', compact('reservations'))->render()
            ]);
        }

        // If not AJAX, return regular view
        return view('reservations.index', compact('reservations'));
    }


    public function updatePaymentStatus(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:reservations,id',  // Ensure reservation exists
            'payment_status' => 'required|string',     // Ensure valid payment status
        ]);

        // Find the reservation by its ID
        $reservation = Reservation::find($request->id);

        if ($reservation) {
            // Update the payment status
            $reservation->payment_status = $request->payment_status;
            $reservation->save();

            // Return a success response
            return response()->json(['success' => true, 'message' => 'Payment status updated successfully.']);
        }

        // If the reservation is not found, return an error response
        return response()->json(['success' => false, 'message' => 'Reservation not found.']);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($results);
    }


    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
    public function users_destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    // public function filterOrders(Request $request)
    // {
    //     $query = Order::query();

    //     // Apply filters
    //     if ($request->has('time_slot') && $request->time_slot != '') {
    //         $query->where('time_slot', $request->time_slot);
    //     }

    //     if ($request->has('status') && $request->status != '') {
    //         $query->where('status', $request->status);
    //     }

    //     // Fetch the filtered orders with the count of order items
    //     $orders = $query->withCount('orderItems')->get();

    //     return response()->json($orders); // Send the filtered orders as JSON response
    // }


    public function order_filter(Request $request)
    {
        Log::info('Received filter request', [
            'time_slot' => $request->time_slot,
            'status' => $request->status
        ]);

        // Validate filters
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

    public function users_edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user-edit', compact('user'));
    }


    public function users_update(Request $request, $id)
    {
        $request->validate([
            'phone_number' => 'nullable|string|max:15',
            'year_level' => 'nullable|string|max:10',
            'department' => 'nullable|string|max:50',
            'course' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($id);
        $user->phone_number = $request->phone_number;
        $user->year_level = $request->year_level;
        $user->department = $request->department;
        $user->course = $request->course;

        $user->save();
        return redirect()->route('admin.users')->with('status', 'User has been updated successfully!');
    }

    public function users_add()
    {

        $users = User::all();

        return view("admin.user-add", compact('users'));
    }


    public function users_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable',
            'role' => 'required',
            'phone_number' => 'nullable',
            'year_level' => 'nullable',
            'department' => 'nullable',
            'course' => 'nullable',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make('defaultpassword');
        $user->role = $request->role;
        $user->phone_number = $request->phone_number;
        $user->year_level = $request->year_level;
        $user->department = $request->department;
        $user->course = $request->course;

        $user->save();
        return redirect()->route('admin.users')->with('status', 'User has been added successfully!');
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
    public function reservations(Request $request)
    {
        // Initial query for reservations with related models
        $query = Reservation::with('rental', 'user', 'dormitoryRoom')
            ->orderBy('created_at', 'DESC');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })->orWhereHas('rental', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Fetch reservations with pagination
        $reservations = $query->paginate(10)->withQueryString();

        // List of rental names that need specific payment statuses
        $rentalNames = ['Male Dormitory', 'Female Dormitory', 'International House II'];

        // Get rental IDs that match these names
        $filteredRentals = Rental::whereIn('name', $rentalNames)->pluck('id')->toArray();

        // Get available dormitory rooms for the filtered rentals
        $availableRooms = DormitoryRoom::whereIn('rental_id', $filteredRentals)
            ->withCount([
                'reservations' => function ($query) {
                    $query->where('rent_status', 'reserved');
                }
            ])
            ->get()
            ->filter(function ($room) {
                return $room->reservations_count < $room->room_capacity;
            })
            ->groupBy('rental_id');

        // Filter reservations to only include those with rental IDs from the filtered rentals
        $reservations = $reservations->filter(function ($reservation) use ($filteredRentals) {
            return in_array($reservation->rental_id, $filteredRentals);
        });

        // Return the view with the filtered data
        return view("admin.reservation", compact('reservations', 'availableRooms'));
    }

    public function reservationHistory($reservation_id)
    {
        // Fetch the reservation record with related user and admin information
        $reservation = Reservation::with(['user', 'updatedBy'])->find($reservation_id);

        if (!$reservation) {
            return redirect()->route('admin.reservation')->with('error', 'Reservation not found.');
        }

        // Create a history array with admin and user data
        $history = [
            [
                'user_name' => $reservation->user->name,
                'user_email' => $reservation->user->email,
                'admin_email' => $reservation->updatedBy ? $reservation->updatedBy->email : 'N/A', // Admin email
                'payment_status' => $reservation->payment_status,
                'rent_status' => $reservation->rent_status,
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s')
            ]
        ];

        return view('admin.reservation-history', compact('reservation', 'history'));
    }

    public function event_items($reservation_id)
    {
        $reservation = Reservation::with('rental', 'user', 'dormitoryRoom')->find($reservation_id);
        if (!$reservation) {
            return redirect()->route('admin.reservations')->with('error', 'Reservation not found.');
        }

        $reservedDates = [];
        if (in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
            $reservedDates = Reservation::where('rental_id', $reservation->rental_id)
                ->where('rent_status', 'reserved')
                ->pluck('reservation_date')
                ->toArray();
        }


        return view('admin.reservation-events', compact('reservation', 'reservedDates'));
    }

    public function updateReservationStatus(Request $request, $reservation_id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,advance/deposit complete,1st month complete,2nd month complete,3rd month complete,4th month complete,5th month complete,6th month complete,full payment complete,canceled',
            'rent_status' => 'required|in:pending,reserved,completed,canceled',
        ]);

        $reservation = Reservation::with('dormitoryRoom', 'user', 'rental')->find($reservation_id);

        if (!$reservation) {
            return redirect()->route('admin.reservation')->with('error', 'Reservation not found.');
        }

        $rentalName = $reservation->rental->name;

        // Automatically mark as "completed" if the reservation_date equals the end_date
        if ($reservation->reservation_date && $reservation->end_date) {
            $reservationDate = \Carbon\Carbon::parse($reservation->reservation_date);
            $endDate = \Carbon\Carbon::parse($reservation->end_date);

            if ($reservationDate->isSameDay($endDate)) {
                $reservation->rent_status = 'completed';
                $reservation->save();
            }
        }

        if ($reservation->ih_start_date && $reservation->ih_end_date) {
            $ihStartDate = \Carbon\Carbon::parse($reservation->ih_start_date);
            $ihEndDate = \Carbon\Carbon::parse($reservation->ih_end_date);

            if ($ihStartDate->isSameDay($ihEndDate)) {
                $reservation->rent_status = 'completed';
                $reservation->save();
            }
        }
        if ($request->input('rent_status') === 'reserved') {
            // Check for existing reserved status on the same reservation_date
            $existingReservation = Reservation::where('reservation_date', $reservation->reservation_date)
                ->where('rent_status', 'reserved')
                ->where('rental_id', $reservation->rental_id) // Same rental_id if necessary
                ->first();

            if ($existingReservation) {
                // Prevent update if there's already a reserved reservation for the same date
                return redirect()->route('admin.reservation')
                    ->with('error', 'This date is already reserved for another reservation.');
            }

            if (in_array($rentalName, ['Male Dormitory', 'Female Dormitory', 'International House II'])) {
                $rooms = DormitoryRoom::where('rental_id', $reservation->rental_id)
                    ->orderBy('room_number')
                    ->get();

                $roomAssigned = false;

                foreach ($rooms as $room) {
                    $reservedCount = Reservation::where('dormitory_room_id', $room->id)
                        ->where('rent_status', 'reserved')
                        ->count();

                    if ($reservedCount < $room->room_capacity) {
                        $reservation->dormitory_room_id = $room->id;
                        $roomAssigned = true;
                        break;
                    }
                }

                if (!$roomAssigned) {
                    return redirect()->route('admin.reservation')
                        ->with('error', 'No available rooms with sufficient capacity.');
                }
            }
        } elseif ($reservation->rent_status === 'reserved' && $request->input('rent_status') !== 'reserved') {
            // Free up the room if the rent status is being changed from 'reserved' to something else
            $reservation->dormitory_room_id = null;
        }

        $historyEntry = [
            'user_name' => $reservation->user->name,
            'user_email' => $reservation->user->email,
            'admin_email' => Auth::user()->email,
            'payment_status' => $request->input('payment_status'),
            'rent_status' => $request->input('rent_status'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $history = $reservation->history ? json_decode($reservation->history, true) : [];
        array_unshift($history, $historyEntry);

        $reservation->history = json_encode($history);
        $reservation->payment_status = $request->input('payment_status');
        $reservation->rent_status = $request->input('rent_status');
        $reservation->updated_by = Auth::id();

        $reservation->save();

        return redirect()->route('admin.reservation-events', ['reservation_id' => $reservation_id])
            ->with('success', 'Status updated successfully.');
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
