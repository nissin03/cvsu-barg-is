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
    $startOfWeek=$startOfMonth->copy()->addDays(($week - 1) * 7)->startOfWeek(Carbon::MONDAY);
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