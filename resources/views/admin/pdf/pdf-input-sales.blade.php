<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
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
        margin-right: -100px; 
        }
        .logo-right img {
        height: 80px;
        max-width: 110px;
        margin-left: -110px; 
        }
        .center-cell {
        text-align: center;
        vertical-align: middle;
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
        .total-row {
            font-weight: bold;
            background-color: #f4f4f4;
        }
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
    <div class="container">
 
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
   
        
        <h2>SALES REPORT OF BUSINESS AFFAIRS AND MARKETING OFFICE</h2>
        <p>
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </p>
        <p style="text-align: left; font-size: 14px;">
            Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
        </p>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Sales</th>
                    <th>Reserved Sales</th>
                    <th>Picked Up Sales</th>
                    <th>Canceled Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chartData['dates'] as $index => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td>&#8369;{{ number_format($chartData['total_sales'][$index], 2) }}</td>
                    <td>&#8369;{{ number_format($chartData['reserved_sales'][$index], 2) }}</td>
                    <td>&#8369;{{ number_format($chartData['pickedup_sales'][$index], 2) }}</td>
                    <td>&#8369;{{ number_format($chartData['canceled_sales'][$index], 2) }}</td>
                </tr>
                @endforeach
                
                <tr class="total-row">
                    <td><strong>Total</strong></td>
                    <td>&#8369;{{ number_format(array_sum($chartData['total_sales']), 2) }}</td>
                    <td>&#8369;{{ number_format(array_sum($chartData['reserved_sales']), 2) }}</td>
                    <td>&#8369;{{ number_format(array_sum($chartData['pickedup_sales']), 2) }}</td>
                    <td>&#8369;{{ number_format(array_sum($chartData['canceled_sales']), 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="prepared-by">
        <div>Prepared by:</div>
        <div class="signature-line"></div>
        <div>{{ Auth::user()->name }}</div>
        <div>{{ Auth::user()->role ? ucfirst(Auth::user()->role) : 'Administrator' }}</div>
        <div>Business Affairs and Marketing Office</div>
        <div>Cavite State University</div>
    </div>
</body>
</html>