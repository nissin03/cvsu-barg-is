<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\MonthName;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $selectedYear = $request->input('year', $currentYear);

        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

        $products = Product::all()->filter(function ($product) {
            $currentStock = $product->attributeValues->isNotEmpty() ? $product->attributeValues->sum('quantity') : $product->current_stock;
            return $currentStock <= $product->reorder_quantity;
        });

        $dashboardDatas = DB::table('Orders')
            ->selectRaw('SUM(total) AS TotalAmount,
                        SUM(IF(status = "reserved", total, 0)) AS TotalReservedAmount,
                        SUM(IF(status = "pickedup", total, 0)) AS TotalPickedUpAmount,
                        SUM(IF(status = "canceled", total, 0)) AS TotalCanceledAmount,
                        COUNT(*) AS Total')
            ->whereYear('created_at', $selectedYear)
            ->first();

        $monthlyDatas = DB::table('month_names AS M')
            ->leftJoinSub(
                DB::table('Orders')
                    ->selectRaw('MONTH(created_at) AS MonthNo,
                                 SUM(total) AS TotalAmount,
                                 SUM(IF(status="reserved", total, 0)) AS TotalReservedAmount,
                                 SUM(IF(status="pickedup", total, 0)) AS TotalPickedUpAmount,
                                 SUM(IF(status="canceled", total, 0)) AS TotalCanceledAmount')
                    ->whereYear('created_at', $selectedYear)
                    ->groupBy(DB::raw('MONTH(created_at)')),
                'D', 'D.MonthNo', '=', 'M.id'
            )
            ->orderBy('M.id')
            ->get();

        $AmountM = implode(',', $monthlyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', $monthlyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', $monthlyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', $monthlyDatas->pluck('TotalCanceledAmount')->toArray());

        $totalAmountSum = $monthlyDatas->sum('TotalAmount');
        $yearRange = range($currentYear, $currentYear - 10);

        return view('admin.index', compact(
            'orders', 'dashboardDatas', 'AmountM', 'ReservationAmountM', 'PickedUpAmountM',
            'CanceledAmountM', 'totalAmountSum', 'yearRange', 'selectedYear', 'products'
        ));
    }

    public function indexWeekly(Request $request)
    {
        $currentDate = Carbon::now();
        $availableMonths = MonthName::orderBy('id')->get();

        $selectedMonthId = $request->input('month', $currentDate->month);
        $selectedYear = $request->input('year', $currentDate->year);
        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId) ?: $availableMonths->first();

        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $weekRanges = [];
        for ($week = 1; $week <= 6; $week++) {
            $startOfWeek = $startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek()->min($endOfMonth);
            if ($startOfWeek->lte($endOfMonth)) $weekRanges[$week] = [$startOfWeek, $endOfWeek];
        }

        $products = Product::with(['category', 'attributeValues'])->get()->filter(function ($product) {
            return $product->attributeValues->sum('quantity') <= $product->reorder_quantity;
        });

        $orders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->orderBy('created_at', 'DESC')->take(10)->get();

        $totalAmounts = [];
        foreach ($weekRanges as $week => [$startOfWeek, $endOfWeek]) {
            $weekData = DB::table('Orders')
                ->selectRaw('SUM(total) AS TotalAmount,
                             SUM(IF(status = "reserved", total, 0)) AS TotalReservedAmount,
                             SUM(IF(status = "pickedup", total, 0)) AS TotalPickedUpAmount,
                             SUM(IF(status = "canceled", total, 0)) AS TotalCanceledAmount')
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->first();

            $totalAmounts[$week] = $weekData;
        }

        $AmountW = implode(',', array_column($totalAmounts, 'TotalAmount'));
        $ReservationAmountW = implode(',', array_column($totalAmounts, 'TotalReservedAmount'));
        $PickedUpAmountW = implode(',', array_column($totalAmounts, 'TotalPickedUpAmount'));
        $CanceledAmountW = implode(',', array_column($totalAmounts, 'TotalCanceledAmount'));

        $TotalAmountW = array_sum(array_column($totalAmounts, 'TotalAmount'));

        $yearRange = range($currentDate->year, $currentDate->year - 10);

        return view('admin.index-weekly', compact(
            'orders', 'availableMonths', 'selectedMonth', 'selectedYear', 'AmountW', 'ReservationAmountW',
            'PickedUpAmountW', 'CanceledAmountW', 'TotalAmountW', 'yearRange', 'products'
        ));
    }

    public function indexDaily(Request $request)
    {
        $availableMonths = MonthName::orderBy('id')->get();
        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();

        $currentDate = Carbon::now();
        $selectedMonthId = $request->input('month', $currentDate->month);
        $selectedYear = $request->input('year', $currentDate->year);
        $selectedWeekId = $request->input('week', $currentDate->weekOfMonth);

        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId) ?: $availableMonths->first();
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $weekRanges = [];
        $weekStart = $startOfMonth->copy()->startOfWeek();
        while ($weekStart->lte($endOfMonth)) {
            $weekEnd = $weekStart->copy()->endOfWeek()->min($endOfMonth);
            $weekRanges[] = [$weekStart->copy(), $weekEnd->copy()];
            $weekStart->addWeek();
        }

        [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId - 1] ?? $weekRanges[0];

        $products = Product::with(['category', 'attributeValues'])->get()->filter(function ($product) {
            return $product->attributeValues->sum('quantity') <= $product->reorder_quantity;
        });

        $orders = Order::whereBetween('created_at', [$startOfSelectedWeek, $endOfSelectedWeek])->orderBy('created_at', 'DESC')->take(10)->get();

        $dashboardDatas = DB::table('Orders')
            ->selectRaw('SUM(total) AS TotalAmount,
                         SUM(IF(status = "reserved", total, 0)) AS TotalReservedAmount,
                         SUM(IF(status = "pickedup", total, 0)) AS TotalPickedUpAmount,
                         SUM(IF(status = "canceled", total, 0)) AS TotalCanceledAmount')
            ->whereBetween('created_at', [$startOfSelectedWeek, $endOfSelectedWeek])
            ->first();

        $dailyDatas = DB::table('Orders')
            ->selectRaw('DAYOFWEEK(created_at) AS DayNo, DAYNAME(created_at) AS DayName,
                         SUM(total) AS TotalAmount,
                         SUM(IF(status = "reserved", total, 0)) AS TotalReservedAmount,
                         SUM(IF(status = "pickedup", total, 0)) AS TotalPickedUpAmount,
                         SUM(IF(status = "canceled", total, 0)) AS TotalCanceledAmount')
            ->whereBetween('created_at', [$startOfSelectedWeek, $endOfSelectedWeek])
            ->groupBy('DayOfWeek', 'DayName')
            ->orderBy('DayNo')
            ->get();

        $AmountD = implode(',', $dailyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', $dailyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', $dailyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', $dailyDatas->pluck('TotalCanceledAmount')->toArray());

        $TotalAmountD = $dailyDatas->sum('TotalAmount');

        $yearRange = range($currentDate->year, $currentDate->year - 10);

        return view('admin.index-daily', compact(
            'orders', 'dashboardDatas', 'dailyDatas', 'AmountD', 'ReservationAmountD', 'PickedUpAmountD',
            'CanceledAmountD', 'TotalAmountD', 'yearRange', 'products'
        ));
    }





    public function generateReport(Request $request)
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        $currentMonthId = $currentDate->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonthId = $request->input('month', $currentMonthId);
        $availableMonths = MonthName::orderBy('id')->get();
        $selectedMonth = $availableMonths->firstWhere('id', $selectedMonthId) ?: $availableMonths->first();
        $startOfMonth = Carbon::create($selectedYear, $selectedMonthId, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

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

        $totalAmounts = [];
        $reservationAmounts = [];
        $pickedUpAmounts = [];
        $canceledAmounts = [];

        foreach ($weekRanges as $week => [$startOfSelectedWeek, $endOfSelectedWeek]) {
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

        $AmountW = implode(',', $totalAmounts);
        $ReservationAmountW = implode(',', $reservationAmounts);
        $PickedUpAmountW = implode(',', $pickedUpAmounts);
        $CanceledAmountW = implode(',', $canceledAmounts);

        $TotalAmountW = array_sum($totalAmounts);
        $TotalReservedAmountW = array_sum($reservationAmounts);
        $TotalPickedUpAmountW = array_sum($pickedUpAmounts);
        $TotalCanceledAmountW = array_sum($canceledAmounts);

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

        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $ReservationAmountM = implode(',', collect($monthlyDatas)->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountM = implode(',', collect($monthlyDatas)->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalReservedAmount = collect($monthlyDatas)->sum('TotalReservedAmount');
        $TotalPickedUpAmount = collect($monthlyDatas)->sum('TotalPickedUpAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        $yearRange = range($currentYear, $currentYear - 10);

        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();
        $selectedWeekId = $request->input('week', $currentDate->weekOfMonth);
        $selectedWeek = $availableWeeks->firstWhere('week_number', $selectedWeekId) ?: $availableWeeks->first();

        if (array_key_exists($selectedWeekId, $weekRanges)) {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[$selectedWeekId];
        } else {
            [$startOfSelectedWeek, $endOfSelectedWeek] = $weekRanges[1];
        }

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

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $dailyDataMap = collect($dailyDatasRaw)->keyBy('DayName');
        $sortedDailyDatas = collect($daysOfWeek)->map(function ($day) use ($dailyDataMap) {
            return $dailyDataMap->get($day, (object)[
                'DayNo' => null,
                'DayName' => $day,
                'TotalAmount' => 0,
                'TotalReservedAmount' => 0,
                'TotalPickedUpAmount' => 0,
                'TotalCanceledAmount' => 0,
            ]);
        });

        $AmountD = implode(',', $sortedDailyDatas->pluck('TotalAmount')->toArray());
        $ReservationAmountD = implode(',', $sortedDailyDatas->pluck('TotalReservedAmount')->toArray());
        $PickedUpAmountD = implode(',', $sortedDailyDatas->pluck('TotalPickedUpAmount')->toArray());
        $CanceledAmountD = implode(',', $sortedDailyDatas->pluck('TotalCanceledAmount')->toArray());

        $TotalAmountD = $sortedDailyDatas->sum('TotalAmount');
        $TotalReservedAmountD = $sortedDailyDatas->sum('TotalReservedAmount');
        $TotalPickedUpAmountD = $sortedDailyDatas->sum('TotalPickedUpAmount');
        $TotalCanceledAmountD = $sortedDailyDatas->sum('TotalCanceledAmount');

        $pageTitle = 'Reports';
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
            'pageTitle'
        ));
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

    public function generateUser(Request $request)
    {
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $selectedYear = $request->input('year', $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);


        $availableWeeks = DB::table('week_names')->orderBy('week_number')->get();

        $newUsersCount = null;
        $newUsers      = null;
        $startDate     = null;
        $endDate       = null;
        $chartData     = null;

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

        $totalUsers = User::count();
        $newUsersThisMonth = User::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();
        $activeUsers = User::where('isDefault', false)->count();

        $growthRate = ($totalUsers > 0) ? (($newUsersThisMonth / $totalUsers) * 100) : 0;

        $recentUsers = User::orderBy('created_at', 'DESC')->take(5)->get();

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

        $startOfMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endOfMonth   = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();

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

        $availableMonths = DB::table('month_names')->get();
        $yearRange = range($currentYear, $currentYear - 10);

        $pageTitle = 'User Registrations Report';

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

        return view('admin.reports.product-statements', compact('orders', 'startDate', 'endDate'));
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
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

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

        return view('admin.reports.product-input-sales', compact('chartData', 'startDate', 'endDate'));
    }

    public function generateInputUsers(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

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

        $totalUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalStudents = User::where('role', 'student')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalEmployees = User::where('role', 'employee')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalNonEmployees = User::where('role', 'non-employee')->whereBetween('created_at', [$startDate, $endDate])->count();

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

        return view('admin.reports.product-input-user', compact('chartData', 'startDate', 'endDate'));
    }




    // Facilities Reports
    public function listSalesFacilities(Request $request)
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

        $paymentsQuery = DB::table('payments')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->select('payments.*', 'users.name', 'users.email')
            ->whereBetween('payments.created_at', [$startDate, $endDate]);

        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $paymentsQuery->where(function($query) use ($searchTerm) {
                $query->where('users.name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('users.email', 'like', '%' . $searchTerm . '%');
            });
        }

        $payments = $paymentsQuery->orderBy('payments.created_at', 'desc')->paginate(20);

        return view('admin.reports.facilities-sales', compact('payments', 'startDate', 'endDate'));
    }
    public function showPaymentDetails($paymentId)
    {
        $paymentDetails = PaymentDetail::with('facility')
                                        ->where('payment_id', $paymentId)
                                        ->get();

        $totalPayment = $paymentDetails->sum('total_price');

        return view('admin.reports.facilities-payment-details', compact('paymentDetails', 'totalPayment'));
    }

}
