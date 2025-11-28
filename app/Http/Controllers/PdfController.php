<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PdfController extends Controller
{
    public function downloadBillingStatements(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        ini_set('pcre.backtrack_limit', 1000000);
        ini_set('pcre.recursion_limit', 1000000);

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:reserved,pickedup,canceled',
            'category' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.report-statements')
                ->withErrors($validator)
                ->withInput();
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $category = $request->input('category');

        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-statements')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        $ordersQuery = Order::query();

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        if ($category) {
            $ordersQuery->whereHas('orderItems.product', function ($query) use ($category) {
                $query->where('category_id', $category);
            });
        }

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $ordersQuery->whereBetween('reservation_date', [$start, $end]);
        } elseif ($startDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $ordersQuery->where('reservation_date', '>=', $start);
        } elseif ($endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
            $ordersQuery->where('reservation_date', '<=', $end);
        }

        $orderIds = $ordersQuery->orderBy('reservation_date', 'desc')->pluck('id');

        $orders = Order::with([
            'user:id,name',
            'orderItems' => function ($query) use ($category) {
                $query->select('id', 'order_id', 'product_id', 'variant_id', 'quantity', 'price')
                    ->with([
                        'product:id,name,category_id',
                        'product.category:id,name',
                        'variant:id,value'
                    ]);
                if ($category) {
                    $query->whereHas('product', function ($q) use ($category) {
                        $q->where('category_id', $category);
                    });
                }
            }
        ])->whereIn('id', $orderIds)->get();

        $categoryName = null;
        if ($category) {
            $categoryModel = Category::find($category);
            $categoryName = $categoryModel ? $categoryModel->name : 'Unknown Category';
        }

        $pdf = PDF::loadView('admin.pdf.pdf-billing', [
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'category' => $category,
            'categoryName' => $categoryName
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('billing_statements.pdf');
    }

    public function downloadPdf(Request $request)
    {
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

        $pdf = PDF::loadView('admin.pdf.pdf-reports', $data)
            ->setPaper('A4', 'portrait');

        // Stream the PDF with inline display (opens in browser)
        return $pdf->stream('sales_report.pdf', ['Attachment' => false]);
    }

    public function downloadProduct(Request $request)
    {
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

        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        $dompdf = new Dompdf();
        $options = new Options();
        $options->set("isHtml5ParserEnabled", true);
        $options->setIsRemoteEnabled(true);
        $dompdf->setOptions($options);

        $html = view('admin.pdf.pdf-products', compact(
            'mostFrequentLabels',
            'mostFrequentData',
            'leastBoughtLabels',
            'leastBoughtData'
        ))->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        // Set proper headers for streaming
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="product-report.pdf"')
            ->header('Cache-Control', 'public, must-revalidate, max-age=0')
            ->header('Pragma', 'public')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
    }

    public function downloadProductList(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        ini_set('pcre.backtrack_limit', 1000000);
        ini_set('pcre.recursion_limit', 1000000);

        $validator = Validator::make($request->all(), [
            'category' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.report.product-list')
                ->withErrors($validator)
                ->withInput();
        }

        $category = $request->input('category');

        $query = Product::with([
            'category',
            'attributeValues'
        ])->where('archived', 0);

        if ($category) {
            $query->where('category_id', $category);
        }

        $products = $query->orderBy('name')->get();

        $categoryName = null;
        if ($category) {
            $categoryModel = Category::find($category);
            $categoryName = $categoryModel ? $categoryModel->name : 'Unknown Category';
        }

        $pdf = PDF::loadView('admin.pdf.pdf-product-list', [
            'products' => $products,
            'category' => $category,
            'categoryName' => $categoryName
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('product_list.pdf');
    }


    public function getMonthlyRegisteredUsers($month, $year)
    {
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

        $recentUsers = User::orderBy('created_at', 'DESC')->take(10)->get();

        $pdfData = [
            'recentUsers'               => $recentUsers,
            'userRegistrationsByMonth'  => $userRegistrationsByMonth,
            'weeklyChartData'           => $weeklyChartData,
            'dailyChartData'            => $dailyChartData,
            'selectedMonth'             => $selectedMonth,
            'selectedYear'              => $selectedYear,
            'selectedWeekId'            => $selectedWeekId,
        ];

        $pdf = PDF::loadView('admin.pdf.pdf-user', $pdfData)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('user_report_' . $selectedYear . '_' . $selectedMonth . '.pdf');
    }

    public function downloadInventoryReportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.report-inventory')
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('today') && $request->input('today') == '1') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->route('admin.report-inventory')
                ->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])
                ->withInput();
        }

        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        $products = $productsQuery->get();

        $stockStatus = $request->input('stock_status');
        $statusLabel = null; // Add this variable

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

            // Set the status label based on the filter
            switch ($stockStatus) {
                case 'instock':
                    $statusLabel = 'In Stock';
                    break;
                case 'outofstock':
                    $statusLabel = 'Low Stock';
                    break;
                case 'reorder':
                    $statusLabel = 'Reorder Level';
                    break;
            }
        }

        // Pass the statusLabel to the view
        $pdfView = view('admin.pdf.pdf-inventory-report', compact('products', 'startDate', 'endDate', 'statusLabel'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return $dompdf->stream('inventory_report.pdf', ['Attachment' => false]);
    }

    public function downloadInputSales(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $chartImage = $request->input('chart_image');

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

        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf.pdf-input-sales', compact('chartData', 'startDate', 'endDate', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Stream the PDF with inline display (opens in browser)
        return $pdf->stream('sales-report.pdf', ['Attachment' => false]);
    }

    public function downloadInputUsers(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $chartImage = $request->input('chart_image');

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

        $pdf = new Dompdf();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf->setOptions($options);

        $html = view('admin.pdf.pdf-input-user', compact('chartData', 'startDate', 'endDate', 'chartImage'))->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Stream with inline display
        return $pdf->stream('user-report.pdf', ['Attachment' => false]);
    }

    public function facilityStatement(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        ini_set('pcre.backtrack_limit', 1000000);
        ini_set('pcre.recursion_limit', 1000000);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');
        $facilityId = $request->input('facility_id');

        $paymentsQuery = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability'
        ]);

        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();
            $paymentsQuery->whereHas('availability', function ($query) use ($start, $end) {
                $query->whereBetween('date_from', [$start, $end])
                    ->orWhereBetween('date_to', [$start, $end]);
            });
        }

        if ($status) {
            $paymentsQuery->where('status', $status);
        }

        if ($facilityId) {
            $paymentsQuery->whereHas('availability', function ($query) use ($facilityId) {
                $query->where('facility_id', $facilityId);
            });
        }

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->get();

        $payments = $payments->map(function ($payment) {
            $allDates = $payment->transactionReservations
                ->pluck('availability')
                ->filter()
                ->flatMap(function ($availability) {
                    $dates = [];
                    if ($availability->date_from && $availability->date_to) {
                        if ($availability->date_from == $availability->date_to) {
                            $dates[] = $availability->date_from;
                        } else {
                            $dates[] = $availability->date_from;
                            $dates[] = $availability->date_to;
                        }
                    }
                    return $dates;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($allDates->count() > 0) {
                $payment->date_from = $allDates->first();
                $payment->date_to = $allDates->last();
            } else {
                $payment->date_from = $payment->availability->date_from ?? null;
                $payment->date_to = $payment->availability->date_to ?? null;
            }

            return $payment;
        });

        if ($dateFrom) {
            $payments = $payments->filter(function ($payment) use ($dateFrom) {
                return $payment->date_to && $payment->date_to >= $dateFrom;
            });
        }

        if ($dateTo) {
            $payments = $payments->filter(function ($payment) use ($dateTo) {
                return $payment->date_from && $payment->date_from <= $dateTo;
            });
        }

        $formattedDateFrom = $dateFrom ? Carbon::parse($dateFrom)->format('F j, Y') : 'N/A';
        $formattedDateTo = $dateTo ? Carbon::parse($dateTo)->format('F j, Y') : 'N/A';

        $selectedFacility = null;
        $showAllFacilities = !$facilityId;

        if ($facilityId) {
            $selectedFacility = \App\Models\Facility::find($facilityId);
        }

        $pdf = PDF::loadView('admin.pdf.pdf-facility-billing', [
            'payments' => $payments,
            'dateFrom' => $formattedDateFrom,
            'dateTo' => $formattedDateTo,
            'selectedFacility' => $selectedFacility,
            'showAllFacilities' => $showAllFacilities
        ])->setPaper('A4', 'portrait');

        $filename = 'facility_billing_statements_';
        $filename .= $dateFrom ? Carbon::parse($dateFrom)->format('Y-m-d') : 'all';
        $filename .= '_to_';
        $filename .= $dateTo ? Carbon::parse($dateTo)->format('Y-m-d') : 'all';

        if ($selectedFacility) {
            $filename .= '_' . \Illuminate\Support\Str::slug($selectedFacility->name);
        }

        $filename .= '.pdf';

        return $pdf->stream($filename);
    }
}
