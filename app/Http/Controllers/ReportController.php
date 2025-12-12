<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Options;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Models\MonthName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2010|max:' . date('Y'),
            'week' => 'nullable|integer|between:1,6'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dateParams = $this->getDateParameters($request);
        $periods = $this->getAvailablePeriods();

        $selectedMonth = $periods['availableMonths']->firstWhere('id', $dateParams['selectedMonth']) ?: $periods['availableMonths']->first();
        list($startOfMonth, $endOfMonth) = $this->getMonthRange($dateParams['selectedYear'], $dateParams['selectedMonth']);

        $weekRanges = $this->getWeekRanges($startOfMonth, $endOfMonth);
        list($startOfSelectedWeek, $endOfSelectedWeek) = $this->getSelectedWeekRange($weekRanges, $dateParams['selectedWeek']);

        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        $dashboardDatas = Cache::remember("dashboard-data-{$dateParams['selectedYear']}-{$dateParams['selectedMonth']}", now()->addHours(1), function () use ($startOfMonth, $endOfMonth) {
            return DB::select("SELECT
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                COUNT(*) AS Total,
                SUM(IF(status = 'reserved', 1, 0)) AS TotalReserved,
                SUM(IF(status = 'pickedup', 1, 0)) AS TotalPickedUp,
                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
                FROM orders
                WHERE created_at BETWEEN ? AND ?", [$startOfMonth, $endOfMonth]);
        });

        $totalAmounts = [];
        $reservationAmounts = [];
        $pickedUpAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfWeek, $endOfWeek]) {
            $dashboardData = $this->getWeeklyData($startOfWeek, $endOfWeek);
            $totalAmounts[$week] = $dashboardData->TotalAmount ?? 0;
            $reservationAmounts[$week] = $dashboardData->TotalReservedAmount ?? 0;
            $pickedUpAmounts[$week] = $dashboardData->TotalPickedUpAmount ?? 0;
            $canceledAmounts[$week] = $dashboardData->TotalCanceledAmount ?? 0;
        }

        $AmountW = implode(',', $totalAmounts);
        $ReservationAmountW = implode(',', $reservationAmounts);
        $PickedUpAmountW = implode(',', $pickedUpAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);

        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservationAmounts);
        $TotalPickedUpAmountW = array_sum($pickedUpAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);

        $monthlyDatas = $this->getMonthlyData($dateParams['selectedYear']);

        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', collect($monthlyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmount = collect($monthlyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        $selectedWeek = $periods['availableWeeks']->firstWhere('week_number', $dateParams['selectedWeek']) ?: $periods['availableWeeks']->first();

        $sortedDailyDatas = $this->getDailyData($startOfSelectedWeek, $endOfSelectedWeek);

        $AmountD = implode(',', $sortedDailyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', $sortedDailyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', $sortedDailyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', $sortedDailyDatas->pluck('TotalCanceledAmount')->toArray());

        $TotalAmountD = $sortedDailyDatas->sum('TotalAmount');
        $TotalReservedAmountD = $sortedDailyDatas->sum('TotalReservedAmount');
        $TotalPickedUpAmountD = $sortedDailyDatas->sum('TotalPickedUpAmount');
        $TotalCanceledAmountD = $sortedDailyDatas->sum('TotalCanceledAmount');


        $selectedYear = $dateParams['selectedYear'];
        $selectedMonthId = $dateParams['selectedMonth'];
        $selectedWeekId = $dateParams['selectedWeek'];
        $availableMonths = $periods['availableMonths'];
        $availableWeeks = $periods['availableWeeks'];
        $yearRange = $periods['yearRange'];

        return view('admin.reports.product-reports', compact(
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
        ));
    }

    public function generateUser(Request $request)
    {
        $dateParams = $this->getDateParameters($request);
        $periods = $this->getAvailablePeriods();
        $availableMonths = $periods['availableMonths'];
        $availableWeeks = $periods['availableWeeks'];
        $yearRange = $periods['yearRange'];

        $selectedMonth = $dateParams['selectedMonth'];
        $selectedYear = $dateParams['selectedYear'];

        $newUsersCount = null;
        $newUsers = null;
        $startDate = null;
        $endDate = null;
        $chartData = null;

        if ($request->isMethod('POST') && $request->has(['start_date', 'end_date'])) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

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
                'dates' => $userRegistrations->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })->toArray(),
                'counts' => $userRegistrations->pluck('count')->toArray(),
            ];

            $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->get();
        }

        $totalUsers = User::count();
        $newUsersThisMonth = User::whereYear('created_at', $dateParams['currentYear'])
            ->whereMonth('created_at', $dateParams['currentMonth'])
            ->count();
        $activeUsers = User::where('isDefault', false)->count();

        $currentMonthUsers = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonthUsers = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $declineRate = $lastMonthUsers > 0 ? (($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100 : 0;



        $growthRate = ($totalUsers > 0) ? (($newUsersThisMonth / $totalUsers) * 100) : 0;


        $recentUsers = User::orderBy('created_at', 'DESC')->take(5)->get();

        $userRegistrations = DB::select("
            SELECT COUNT(*) AS TotalUsers, MONTH(created_at) AS MonthNo
            FROM users
            WHERE YEAR(created_at) = ?
            GROUP BY MonthNo
            ORDER BY MonthNo
        ", [$dateParams['selectedYear']]);

        $monthlyData = array_fill(1, 12, 0);
        foreach ($userRegistrations as $data) {
            $monthlyData[$data->MonthNo] = $data->TotalUsers;
        }
        $userRegistrationsByMonth = implode(',', $monthlyData);

        list($startOfMonth, $endOfMonth) = $this->getMonthRange($dateParams['selectedYear'], $dateParams['selectedMonth']);
        $weekRanges = $this->getWeekRanges($startOfMonth, $endOfMonth);

        $weeklyData = array_fill(1, 6, 0);
        $userCounts = DB::table('users')
            ->selectRaw('WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week_number')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('created_at', $dateParams['selectedYear'])
            ->whereMonth('created_at', $dateParams['selectedMonth'])
            ->groupBy('week_number')
            ->get();

        foreach ($userCounts as $count) {
            $weekIndex = $count->week_number;
            if (isset($weeklyData[$weekIndex])) {
                $weeklyData[$weekIndex] = $count->count;
            }
        }
        $weeklyChartData = implode(',', $weeklyData);

        $selectedWeekId = $request->input('week', 1);
        if (!array_key_exists($selectedWeekId, $weekRanges)) {
            $selectedWeekId = 1;
        }
        list($dailyStart, $dailyEnd) = $weekRanges[$selectedWeekId];

        $dailyCounts = DB::table('users')
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count')
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyData = array_fill(0, 7, 0);
        foreach ($dailyCounts as $count) {
            $index = array_search($count->day_name, $daysOfWeek);
            if ($index !== false) {
                $dailyData[$index] = $count->count;
            }
        }
        $dailyChartData = implode(',', $dailyData);

        return view('admin.reports.product-user', compact(
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
            'newUsersCount',
            'newUsers',
            'startDate',
            'endDate',
            'chartData',
            'selectedWeekId'
        ))->with('showChart', true);
    }

    private function getDateParameters(Request $request)
    {
        $currentDate = Carbon::now();
        return [
            'currentDate' => $currentDate,
            'currentYear' => $currentDate->year,
            'currentMonth' => $currentDate->month,
            'selectedYear' => $request->input('year', $currentDate->year),
            'selectedMonth' => $request->input('month', $currentDate->month),
            'selectedWeek' => $request->input('week', $currentDate->weekOfMonth)
        ];
    }

    private function getAvailablePeriods()
    {
        return [
            'availableMonths' => Cache::remember('available-months', now()->addDay(), function () {
                return MonthName::orderBy('id')->get();
            }),
            'availableWeeks' => Cache::remember('available-weeks', now()->addDay(), function () {
                return DB::table('week_names')->orderBy('week_number')->get();
            }),
            'yearRange' => range(Carbon::now()->year, Carbon::now()->year - 10)
        ];
    }

    private function getMonthRange($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        return [$startOfMonth, $startOfMonth->copy()->endOfMonth()];
    }

    private function getWeekRanges($startOfMonth, $endOfMonth)
    {
        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);
            $startOfWeek = $startOfWeek->lt($startOfMonth) ? $startOfMonth : $startOfWeek;
            $endOfWeek = $endOfWeek->gt($endOfMonth) ? $endOfMonth : $endOfWeek;
            if ($startOfWeek->lte($endOfMonth)) {
                $weekRanges[$week] = [$startOfWeek, $endOfWeek];
            }
        }
        return $weekRanges;
    }

    private function getSelectedWeekRange($weekRanges, $selectedWeekId)
    {
        if (array_key_exists($selectedWeekId, $weekRanges)) {
            return $weekRanges[$selectedWeekId];
        }
        return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
    }

    private function getDailyData($startDate, $endDate)
    {
        $dailyDatasRaw = Cache::remember("daily-data-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}", now()->addHours(1), function () use ($startDate, $endDate) {
            return DB::select(
                "SELECT DAYOFWEEK(created_at) AS DayNo,
                    DAYNAME(created_at) AS DayName,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                    SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                FROM orders
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
                ORDER BY DayNo",
                [$startDate, $endDate]
            );
        });

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyDataMap = collect($dailyDatasRaw)->keyBy('DayName');

        return collect($daysOfWeek)->map(function ($day) use ($dailyDataMap) {
            return $dailyDataMap->get($day, (object)[
                'DayNo' => null,
                'DayName' => $day,
                'TotalAmount' => 0,
                'TotalReservedAmount' => 0,
                'TotalPickedUpAmount' => 0,
                'TotalCanceledAmount' => 0,
            ]);
        });
    }

    private function getMonthlyData($year)
    {
        return Cache::remember("monthly-data-{$year}", now()->addHours(1), function () use ($year) {
            return DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
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
                    FROM orders
                    WHERE YEAR(created_at) = ?
                    GROUP BY MONTH(created_at)
                ) D ON D.MonthNo = M.id
                ORDER BY M.id", [$year]);
        });
    }

    private function getWeeklyData($startDate, $endDate)
    {
        return Cache::remember("weekly-data-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}", now()->addHours(1), function () use ($startDate, $endDate) {
            $result = DB::select(
                "SELECT
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'reserved', total, 0)) AS TotalReservedAmount,
                SUM(IF(status = 'pickedup', total, 0)) AS TotalPickedUpAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                FROM orders
                WHERE created_at BETWEEN ? AND ?",
                [$startDate, $endDate]
            );
            return $result[0] ?? (object)[
                'TotalAmount' => 0,
                'TotalReservedAmount' => 0,
                'TotalPickedUpAmount' => 0,
                'TotalCanceledAmount' => 0
            ];
        });
    }







    public function generateProduct(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);

        $mostFrequentProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        $leastBoughtProducts = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.name', DB::raw('COUNT(order_items.id) as total_orders'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_orders')
            ->limit(10)
            ->get();

        $mostFrequentLabels = $mostFrequentProducts->pluck('name');
        $mostFrequentData = $mostFrequentProducts->pluck('total_orders');
        $leastBoughtLabels = $leastBoughtProducts->pluck('name');
        $leastBoughtData = $leastBoughtProducts->pluck('total_orders');

        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        return view('admin.reports.product', compact(
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

    public function productList(Request $request)
    {
        $query = Product::with([
            'category',
            'attributeValues'
        ])->where('archived', 0);

        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(15);
        $categories = Category::all();

        return view('admin.reports.product-list', compact('products', 'categories'));
    }


    public function generateInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'today'         => 'nullable|boolean',
            'stock_status'  => 'nullable|string|in:instock,outofstock,reorder',
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

        $productsQuery = Product::with(['category', 'orderItems', 'attributeValues']);

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $productsQuery->whereBetween('updated_at', [$start, $end]);
        }

        $products = $productsQuery->get();

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

        return view('admin.reports.product-inventory', compact('products', 'startDate', 'endDate'));
    }

    public function generateBillingStatement($orderId)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($orderId);
        return view('admin.reports.product-statement', compact('order'));
    }

    public function listBillingStatements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|in:reserved,pickedup,canceled',
            'category' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return redirect()->back()->withErrors(['end_date' => 'The end date must be after or equal to the start date.'])->withInput();
        }

        $ordersQuery = Order::with(['user', 'orderItems.product.category', 'orderItems.variant']);

        if ($request->has('status') && $request->input('status')) {
            $ordersQuery->where('status', $request->input('status'));
        }

        if ($request->has('category') && $request->input('category')) {
            $categoryId = $request->input('category');
            $ordersQuery->whereHas('orderItems.product', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
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

        $orders = $ordersQuery->orderBy('reservation_date', 'desc')->get();

        if ($request->has('category') && $request->input('category')) {
            $categoryId = $request->input('category');
            foreach ($orders as $order) {
                $order->orderItems = $order->orderItems->filter(function ($item) use ($categoryId) {
                    return $item->product->category_id == $categoryId;
                });
            }
        }

        $grandTotal = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $grandTotal += $item->price * $item->quantity;
            }
        }

        $categories = Category::all();

        return view('admin.reports.product-statements', compact('orders', 'startDate', 'endDate', 'grandTotal', 'categories'));
    }



    public function showUserReports(Request $request)
    {
        $selectedYear = $request->year ?? Carbon::now()->year;
        $selectedMonth = $request->month ?? Carbon::now()->month;

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

        $yearRange = range(Carbon::now()->year - 5, Carbon::now()->year);

        $userRegistrationsByMonth = $this->getUserRegistrationsByMonth($selectedYear);
        $weeklyChartData = $this->getUserRegistrationsWeekly($selectedMonth, $selectedYear);
        $dailyChartData = $this->getUserRegistrationsDaily($selectedMonth, $selectedYear);

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.product-user')
            ->with('availableMonths', $availableMonths)
            ->with('selectedYear', $selectedYear)
            ->with('selectedMonth', $selectedMonth)
            ->with('yearRange', $yearRange)
            ->with('userRegistrationsByMonth', $userRegistrationsByMonth)
            ->with('weeklyChartData', $weeklyChartData)
            ->with('dailyChartData', $dailyChartData)
            ->with('recentUsers', $recentUsers);
    }


    public function generateInputSales(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date|required_with:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2010|max:' . date('Y'),
            'week' => 'nullable|integer|between:1,6'
        ]);

        $dateParams = $this->getDateParameters($request);
        $periods = $this->getAvailablePeriods();

        $availableMonths = $periods['availableMonths'];
        $availableWeeks = $periods['availableWeeks'];
        $yearRange = $periods['yearRange'];

        $selectedYear = $dateParams['selectedYear'];
        $selectedMonth = $availableMonths->firstWhere('id', $dateParams['selectedMonth']) ?: $availableMonths->first();
        $selectedWeekId = $dateParams['selectedWeek'];

        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            return view('admin.reports.product-input-sales', compact(
                'availableMonths',
                'availableWeeks',
                'yearRange',
                'selectedMonth',
                'selectedYear',
                'selectedWeekId'
            ))->with([
                'chartData' => null,
                'startDate' => null,
                'endDate' => null
            ]);
        }

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : $startDate->copy()->endOfDay();

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

        return view('admin.reports.product-input-sales', compact(
            'chartData',
            'startDate',
            'endDate',
            'selectedMonth',
            'selectedYear',
            'selectedWeekId',
            'availableMonths',
            'availableWeeks',
            'yearRange'
        ));
    }

    public function generateInputUsers(Request $request)
    {
        // Check if the request is to clear the filter
        if (!$request->has('start_date') || empty($request->input('start_date'))) {
            // Return view without chart data when no dates are selected
            $dateParams = $this->getDateParameters($request);
            $periods = $this->getAvailablePeriods();

            return view('admin.reports.product-input-user', [
                'availableMonths' => $periods['availableMonths'],
                'availableWeeks' => $periods['availableWeeks'],
                'yearRange' => $periods['yearRange'],
                'selectedMonth' => $dateParams['selectedMonth'],
                'selectedYear' => $dateParams['selectedYear'],
                'selectedWeekId' => $request->input('week', 1),
            ]);
        }

        // Original validation and processing
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = $request->has('end_date') && !empty($request->input('end_date'))
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : $startDate->copy()->endOfDay();

        // Rest of your original method code here...
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $totalUsers = User::count();
        $newUsersThisMonth = User::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $activeUsers = User::where('isDefault', false)->count();
        $growthRate = ($totalUsers > 0) ? (($newUsersThisMonth / $totalUsers) * 100) : 0;

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

        $totalUsersInRange = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalStudents = User::where('role', 'student')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalEmployees = User::where('role', 'employee')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalNonEmployees = User::where('role', 'non-employee')->whereBetween('created_at', [$startDate, $endDate])->count();

        $chartData = [
            'dates' => $usersData->pluck('date')->toArray(),
            'total_users' => $usersData->pluck('total_users')->toArray(),
            'total_students' => $usersData->pluck('total_students')->toArray(),
            'total_employees' => $usersData->pluck('total_employees')->toArray(),
            'total_non_employees' => $usersData->pluck('total_non_employees')->toArray(),
            'total_users_count' => $totalUsersInRange,
            'total_students_count' => $totalStudents,
            'total_employees_count' => $totalEmployees,
            'total_non_employees_count' => $totalNonEmployees,
        ];

        $dateParams = $this->getDateParameters($request);
        $periods = $this->getAvailablePeriods();

        $availableMonths = $periods['availableMonths'];
        $availableWeeks = $periods['availableWeeks'];
        $yearRange = $periods['yearRange'];

        $selectedMonth = $dateParams['selectedMonth'];
        $selectedYear = $dateParams['selectedYear'];
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

        list($startOfMonth, $endOfMonth) = $this->getMonthRange($selectedYear, $selectedMonth);
        $weekRanges = $this->getWeekRanges($startOfMonth, $endOfMonth);

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

        if (!array_key_exists($selectedWeekId, $weekRanges)) {
            $selectedWeekId = 1;
        }
        list($dailyStart, $dailyEnd) = $weekRanges[$selectedWeekId];

        $dailyCounts = DB::table('users')
            ->selectRaw('DAYNAME(created_at) as day_name, DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count')
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day_of_week', 'day_name')
            ->orderBy('day_of_week')
            ->get();

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyData = array_fill(0, 7, 0);
        foreach ($dailyCounts as $count) {
            $index = array_search($count->day_name, $daysOfWeek);
            if ($index !== false) {
                $dailyData[$index] = $count->count;
            }
        }
        $dailyChartData = implode(',', $dailyData);

        return view('admin.reports.product-input-user', compact(
            'chartData',
            'startDate',
            'endDate',
            'totalUsers',
            'newUsersThisMonth',
            'activeUsers',
            'growthRate',
            'userRegistrationsByMonth',
            'weeklyChartData',
            'dailyChartData',
            'availableMonths',
            'yearRange',
            'selectedMonth',
            'selectedYear',
            'availableWeeks',
            'selectedWeekId'
        ));
    }

    public function facilitiesStatement(Request $request)
    {
        $query = Payment::with([
            'user',
            'availability.facility',
            'availability.facilityAttribute',
            'transactionReservations.availability'
        ])
            ->orderBy('created_at', 'desc');

        if ($request->has('facility_id') && $request->facility_id) {
            $query->whereHas('availability', function ($q) use ($request) {
                $q->where('facility_id', $request->facility_id);
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereHas('transactionReservations.availability', function ($q) use ($request) {
                $q->where('date_to', '>=', $request->date_from);
            });
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereHas('transactionReservations.availability', function ($q) use ($request) {
                $q->where('date_from', '<=', $request->date_to);
            });
        }

        $payments = $query->get()->map(function ($payment) {
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

        $facilities = \App\Models\Facility::where('archived', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.facility_statement', [
            'payments' => $payments,
            'facilities' => $facilities,
            'filters' => $request->all()
        ]);
    }
}
