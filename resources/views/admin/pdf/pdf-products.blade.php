<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Report</title>
    <style>
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
        }

        h1,
        h2,
        h3 {
            text-align: center;
        }

        .text-tiny {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .content-table th {
            background-color: #f8f9fa;
        }

        .header {
            width: 100%;
            margin-top: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .header-table,
        .header-table th,
        .header-table td {
            border: none !important;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0 10px;
            width: auto;
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
            margin-right: -180px;
        }

        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -190px;
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

        .page-break {
            page-break-before: always;
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
    </style>
</head>

<body>
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
                        <a href="http://www.cvsu.edu.ph" target="_blank">www.cvsu.edu.ph</a>
                    </div>
                </td>
                <td class="logo-right">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo/BaRG-logo.png'))) }}"
                        alt="BaRG Logo">
                </td>
            </tr>
        </table>
    </div>

    <div class="office-name">
        OFFICE BUSINESS AFFAIRS AND MARKETING OFFICE
    </div>

    <h3>Most Bought Products</h3>

    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span
                class="info-value">{{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}</span>
        </div>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-center">Total Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mostFrequentLabels as $index => $label)
                <tr>
                    <td>{{ $label ?? 'No Product' }}</td>
                    <td class="text-center">{{ $mostFrequentData[$index] ?? '0' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h3>Least Bought Products</h3>
    <table class="content-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-center">Total Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leastBoughtLabels as $index => $label)
                <tr>
                    <td>{{ $label ?? 'No Product' }}</td>
                    <td class="text-center">{{ $leastBoughtData[$index] ?? '0' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures-section">
        @php
            $signatures = \App\Models\Signature::where('category', 'product')
                ->where(function ($query) {
                    $query->where('report_type', 'product')->orWhere('report_type', 'all');
                })
                ->where('is_active', true)
                ->where('is_archived', false)
                ->orderBy('order_by')
                ->get();

            $leftSignatures = [];
            $rightSignatures = [];

            $leftSignatures[] = (object) [
                'label' => 'Prepared by',
                'name' => Auth::user()->name,
                'position' => Auth::user()->role,
                'is_prepared_by' => true,
            ];

            foreach ($signatures as $signature) {
                if ($signature->order_by % 2 == 1) {
                    $leftSignatures[] = $signature;
                } else {
                    $rightSignatures[] = $signature;
                }
            }

            $maxRows = max(count($leftSignatures), count($rightSignatures));
        @endphp

        <table class="signatures-table" style="width: 100%; margin-top: 10px; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 50%; vertical-align: top; text-align: left; border: none;">
                    @foreach ($leftSignatures as $signature)
                        <div class="signature-block" style="margin-bottom: 30px;">
                            <div style="font-weight: bold;">{{ $signature->label }}:</div>
                            <div class="signature-line" style="width: 250px; margin: 5px 0;"></div>
                            <div><strong>{{ $signature->name }}</strong></div>
                            <div>{{ $signature->position }}</div>
                        </div>
                    @endforeach
                </td>

                <td style="width: 50%; vertical-align: top; text-align: left; border: none;">
                    @foreach ($rightSignatures as $signature)
                        <div class="signature-block" style="margin-bottom: 30px;">
                            <div style="font-weight: bold;">{{ $signature->label }}:</div>
                            <div class="signature-line" style="width: 250px; margin: 5px 0;"></div>
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
