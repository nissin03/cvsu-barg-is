<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        /* Embed DejaVu Sans font - ensure the TTF file exists at public/fonts/DejaVuSans.ttf */
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
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h1, h3 {
            text-align: center;
        }
        
        /* Header Styles */
        .header {
            width: 100%;
            margin-top: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        /* Remove borders from header table cells */
        .header-table td {
            vertical-align: middle;
            padding: 0 10px;
            border: none;
        }
        .logo-left, .logo-right {
            width: 70px;  /* fixed width for the logo cells */
            padding: 0;
        }
        .logo-left {
            text-align: right;
        }
        .logo-right {
            text-align: left;
        }
        /* Negative margins applied directly to the images to shift them closer */
        .logo-left img {
            height: 80px;
            max-width: 80px;
            margin-right: -95px;  /* adjust this value as needed */
            margin-top: -20px;
        }
        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -110px;  /* adjust this value as needed */
            margin-top: -20px;
        }
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
    <!-- Header Section (Centered) -->
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
    <h2 style="text-align: center;">Sales Report of Business and Resource Generation</h2>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
    </p>

    <!-- Monthly Earned Sales -->
    <h3>Monthly Earned Sales for {{ $selectedYear }}</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Weekly Earned Sales -->
    <h3>Weekly Earnings for {{ $selectedMonth->name }} {{ $selectedYear }}</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount_w, 2) }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount_w, 2) }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount_w, 2) }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount_w, 2) }}</td>
        </tr>
    </table>

    <!-- Daily Earned Sales -->
    <h3>Daily Earned Sales for {{ $selectedMonth->name }} {{ $selectedYear }} (Week {{ $selectedWeekId }})</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount_d, 2) }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount_d, 2) }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount_d, 2) }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount_d, 2) }}</td>
        </tr>
    </table>
</body>
</html>
