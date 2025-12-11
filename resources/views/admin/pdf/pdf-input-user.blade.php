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
            margin: 0.25in 0.75in 0.75in 0.75in;
            /* top, right, bottom, left */
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

        th,
        td {
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

        .logo-left,
        .logo-right {
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
            /* adjust as needed */
        }

        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -110px;
            /* adjust as needed */
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

        .signatures-section {
            width: 100%;
            margin-top: 40px;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures-table td {
            vertical-align: top;
            padding: 0 20px;
        }

        .prepared-by {
            margin-bottom: 30px;
        }

        .signature-block {
            margin-bottom: 30px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 5px 0;
        }

        .office-name {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .info-container {
            text-align: left;
            margin: 15px 0;
            font-size: 14px;
            width: 100%;
        }

        .info-row {
            margin-bottom: 5px;
            white-space: nowrap;
        }

        .info-label {
            display: inline-block;
            font-weight: bold;
            width: 150px;
            text-align: left;
        }

        .info-separator {
            display: inline-block;
            width: 10px;
            text-align: center;
        }

        .info-value {
            display: inline-block;
            text-align: left;
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
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/cvsu-logo.png'))) }}"
                            alt="CVSU Logo">
                    </td>
                    <td class="center-cell">
                        <div class="university-name">
                            Republic of the Philippines<br>
                            CAVITE STATE UNIVERSITY<br>
                            Don Severino delas Alas Campus
                        </div>
                        <div class="subtext">
                            Indang, Cavite<br>
                            <a href="https://cvsu.edu.ph/" target="_blank">www.cvsu.edu.ph</a>
                        </div>
                    </td>
                    <td class="logo-right">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/BaRG-logo.png'))) }}"
                            alt="BaRG Logo">
                    </td>
                </tr>
            </table>
        </div>
        <!-- End Header Section -->

        <div class="office-name">
            OFFICE BUSINESS AFFAIRS AND MARKETING OFFICE
        </div>

        <h3 style="text-align: center;">User Report</h3>



        <div class="info-container">
            <div class="info-row">
                <span class="info-label">Downloaded on</span>
                <span class="info-separator">:</span>
                <span class="info-value">
                    {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Reservation Period</span>
                <span class="info-separator">:</span>
                <span class="info-value">
                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                </span>
            </div>


        </div>


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

    <div class="signatures-section">
        @php
            $signatures = \App\Models\Signature::where('category', 'product')
                ->where(function ($query) {
                    $query->where('report_type', 'users')->orWhere('report_type', 'all');
                })
                ->where('is_active', true)
                ->where('is_archived', false)
                ->orderBy('order_by')
                ->get();

            $leftSignatures = [];
            $rightSignatures = [];

            $leftSignatures[] = (object) [
                'label' => 'Prepared by',
                'name' => strtoupper(Auth::user()->name),
                'position' => optional(Auth::user()->position)->name,
                'is_prepared_by' => true,
            ];

            foreach ($signatures as $signature) {
                if ($signature->order_by % 2 == 1) {
                    $leftSignatures[] = $signature;
                } else {
                    $rightSignatures[] = $signature;
                }
            }
        @endphp

        <table class="signatures-table" style="width: 100%; margin-top: 10px; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 50%; vertical-align: top; text-align: left; border: none;">
                    @foreach ($leftSignatures as $signature)
                        <div class="signature-block" style="margin-bottom: 30px;">
                            <div style="font-weight: bold;">{{ $signature->label }}:</div>
                            <div style="height: 20px;">&nbsp;</div>
                            <div><strong>{{ $signature->name }}</strong></div>
                            <div>{{ $signature->position }}</div>
                        </div>
                    @endforeach
                </td>

                <td style="width: 50%; vertical-align: top; text-align: left; border: none;">
                    @foreach ($rightSignatures as $signature)
                        <div class="signature-block" style="margin-bottom: 30px;">
                            <div style="font-weight: bold;">{{ $signature->label }}:</div>
                            <div style="height: 20px;">&nbsp;</div>
                            <div><strong>{{ $signature->name }}</strong></div>
                            <div>{{ $signature->position }}</div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
