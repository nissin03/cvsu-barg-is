<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Report</title>
    <style>
         @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in; /* top, right, bottom, left */
        }
        /* General PDF Styles */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .chart-container {
            text-align: center;
            margin-top: 20px;
        }

        /* Header Styles */
        .header {
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0 10px;
            border: none;
        }
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
        .logo-left img {
            height: 80px;
            max-width: 80px;
            margin-right: -100px; /* adjust as needed */
        }
        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -110px; /* adjust as needed */
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-left">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/cvsu-logo.png'))) }}" alt="CVSU Logo">
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
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/BaRG-logo.png'))) }}" alt="BaRG Logo">
                    </td>
                </tr>
            </table>
        </div>
        <!-- End Header Section -->

        <h2>User Report</h2>
        <p>
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </p>
        <p style="text-align: left; font-size: 14px;">
            Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
        </p>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Users</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chartData['dates'] as $index => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td>{{ number_format($chartData['total_users'][$index]) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
