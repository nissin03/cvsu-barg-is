<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Registrations Report PDF</title>
    <style>
        /* Page margins */
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in; /* top, right, bottom, left */
        }

        /* Embed DejaVu Sans font - ensure the TTF file exists at public/fonts/DejaVuSans.ttf */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        /* General PDF Styles */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #333;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 10px;
        }
        /* Page break style */
        .page-break {
            page-break-before: always;
        }

        /* --------------------- */
        /* Header Section Styles */
        /* --------------------- */
        .header {
            width: 100%;
            margin-top: 20px;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin: 0;
            padding: 0;
        }
        /* Remove borders for header cells */
        .header-table td {
            vertical-align: middle;
            padding: 0;
            margin: 0;
            border: none;
        }
        /* Fixed widths for the logo cells */
        .logo-left, .logo-right {
            width: 70px;
            padding: 0;
        }
        .logo-left {
            text-align: right;
        }
        .logo-right {
            text-align: left;
        }
        /* Apply negative margins directly to the images to bring them closer to the center text */
        .logo-left img {
            height: 80px;
            max-width: 80px;
            margin-right: -100px;  /* Adjust as needed */
            margin-top: -20px;
        }
        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -110px;  /* Adjust as needed */
            margin-top: -20px;
        }
        /* Center cell styles */
        .center-cell {
            text-align: center;
            vertical-align: middle;
        }
        .university-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
            white-space: nowrap;
            word-break: keep-all;
        }
        .subtext {
            font-size: 12px;
            margin: 0;
        }
        /* --------------------- */
        /* End Header Section    */
        /* --------------------- */

            .prepared-by {
      margin-top: 40px;
      text-align: right;
      width: 100%;
    }
    .signature-line {
      border-top: 1px solid #000;
      width: 250px;
      /* margin-top: 40px; */
      margin-left: auto;
    }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-left">
                    <img src="{{ public_path('images/logo/cvsu-logo.png') }}" alt="CVSU Logo">
                </td>
                <td class="center-cell">
                    <div class="university-name">
                        Republic of the Philippines<br>
                        CAVITE STATE UNIVERSITY<br>
                        Don Severino delas Alas Campus
                    </div>
                    <div class="subtext">
                        Indang, Cavite<br>
                        <a href="http://www.cvsu.edu.ph" target="_blank">www.cvsu.edu.ph</a>
                    </div>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo/BaRG-logo.png') }}" alt="BaRG Logo">
                </td>
            </tr>
        </table>
    </div>
    <!-- End Header Section -->

    <!-- Report Title and Download Timestamp -->
    <h2>User Registrations Report for Marketing Center</h2>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
    </p>

    @php
        // Define month names for heading and monthly table.
        $monthNames = [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        // Array for full month names to display in the monthly table.
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        // Convert the comma-separated strings into arrays.
        $monthlyCounts = explode(',', $userRegistrationsByMonth);
        $weeklyCounts  = explode(',', $weeklyChartData);
        $days          = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dailyCounts   = explode(',', $dailyChartData);
    @endphp

    <!-- Monthly Registered Accounts -->
    <h3>Monthly Registered Accounts in Year {{ $selectedYear }}</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Registrations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($months as $index => $month)
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ isset($monthlyCounts[$index]) ? trim($monthlyCounts[$index]) : 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Weekly Registered Accounts (Starts on a new page) -->
    <div class="page-break"></div>
    <h3>Weekly Registered Accounts ({{ $monthNames[$selectedMonth] ?? $selectedMonth }} {{ $selectedYear }})</h3>
    <table>
        <thead>
            <tr>
                <th>Week</th>
                <th>Registrations</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < count($weeklyCounts); $i++)
                <tr>
                    <td>Week {{ $i + 1 }}</td>
                    <td>{{ trim($weeklyCounts[$i]) }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Daily Registered Accounts -->
    <h3>Daily Registered Accounts (Week {{ $selectedWeekId }})</h3>
    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Registrations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $index => $day)
                <tr>
                    <td>{{ $day }}</td>
                    <td>{{ isset($dailyCounts[$index]) ? trim($dailyCounts[$index]) : 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Recently Registered Users (Starts on a new page) -->
    <div class="page-break"></div>
    <h3>Recent Registrations</h3>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recentUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
        <div class="prepared-by">
    <div>Prepared by:</div>
    <div class="signature-line"></div>
    <div>{{ Auth::user()->name }}</div>
    <div>{{ Auth::user()->role ? ucfirst(Auth::user()->role) : 'Administrator' }}</div>
    <div>Business Affairs and Marketing Office</div>
    <div>Cavite State University</div>
</body>
</html>
