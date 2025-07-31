<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facility Reservation Report</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in;
        }
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
        .header {
            width: 100%;
            margin-top: 20px;
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
            margin-right: -95px;
            margin-top: -20px;
        }
        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -110px;
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
    <h2 style="text-align: center;">Facility Reservation Report</h2>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
    </p>
    <h3>Facility: {{ $facilityName ?? 'All Facilities' }}</h3>
    <h4>Report Period: {{ $reportPeriod ?? 'N/A' }}</h4>
    <table>
        <tr>
            <th>Summary</th>
            <th>Count</th>
        </tr>
        <tr>
            <td>Total Reservations</td>
            <td>{{ $totalReservations ?? 0 }}</td>
        </tr>
        <tr>
            <td>Completed Reservations</td>
            <td>{{ $completedReservations ?? 0 }}</td>
        </tr>
        <tr>
            <td>Canceled Reservations</td>
            <td>{{ $canceledReservations ?? 0 }}</td>
        </tr>
        <tr>
            <td>Pending Reservations</td>
            <td>{{ $pendingReservations ?? 0 }}</td>
        </tr>
    </table>
    <h3>Reservation Amounts</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount (₱)</th>
        </tr>
        <tr>
            <td>Total Reserved Amount</td>
            <td>&#8369;{{ number_format($totalReservedAmount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Total Completed Amount</td>
            <td>&#8369;{{ number_format($totalCompletedAmount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Total Canceled Amount</td>
            <td>&#8369;{{ number_format($totalCanceledAmount ?? 0, 2) }}</td>
        </tr>
    </table>
    <h3>Detailed Reservations</h3>
    <table>
        <tr>
            <th>#</th>
            <th>Reservation Date</th>
            <th>User</th>
            <th>Status</th>
            <th>Amount (₱)</th>
        </tr>
        @forelse($reservations as $i => $reservation)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $reservation->date ?? '' }}</td>
            <td>{{ $reservation->user_name ?? '' }}</td>
            <td>{{ ucfirst($reservation->status ?? '') }}</td>
            <td>&#8369;{{ number_format($reservation->amount ?? 0, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center;">No reservation data available.</td>
        </tr>
        @endforelse
    </table>
</body>
</html> 