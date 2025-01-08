<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite State University Billing Statements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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
            border: none;
            padding: 0 10px;
            width: auto;
        }
        .header-table img {
            height: 60px;
            max-width: 80px;
        }
        .center-text {
            text-align: center;
            padding: 0;
            margin: 0;
        }
        .university-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
        }
        .subtext {
            font-size: 12px;
            margin: 0;
        }
        .office-name {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="text-align: right; width: 35%;">
                    <img src="{{ public_path('images/logo/cvsu-logo.png') }}" alt="">
                </td>
                
                <td class="center-text" style="width: 30%;">
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
                
                <td style="text-align: left; width: 35%;">
                    <img src="{{ public_path('images/logo/BaRG-logo.jpg') }}" alt="">
                </td>
            </tr>
        </table>
    </div>

    <div class="office-name">
        PRODUCTION AND RESOURCE GENERATION OFFICE
    </div>

    <h3 style="text-align: center;">Billing Statements</h3>
    <p style="text-align: center; font-size: 14px;">
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
        Date Range:
        @if ($startDate && $endDate)
            {{ $startDate }} to {{ $endDate }}
        @else
            N/A
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th>Order No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Total</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ $order->user->email }}</td>
                <td>{{ number_format($order->total, 2) }}</td>
                <td>{{ $order->created_at->format('F d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 