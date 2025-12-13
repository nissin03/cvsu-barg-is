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
            margin: 20px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        h1,
        h3 {
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

        .office-name {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .remarks-section {
            width: 100%;
            margin: 20px 0 10px 0;
            page-break-inside: avoid;
        }

        .remarks-title {
            font-weight: bold;
            margin-bottom: 6px;
            text-align: left;
        }

        .remarks-box {
            width: 100%;
            height: 120px;
            border: 1px solid #000;
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
                        <a href="https://cvsu.edu.ph/" target="_blank">www.cvsu.edu.ph</a>
                    </div>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo/BaRG-logo.png') }}" alt="BaRG Logo">
                </td>
            </tr>
        </table>
    </div>

    <div class="office-name">
        OFFICE OF THE DIRECTOR FOR BUSINESS AFFAIRS AND MARKETING
    </div>

    <h3>MARKETING CENTER</h3>
    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
            </span>
        </div>
    </div>

    <h3>Total Earned Sales for {{ $selectedYear }}</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount, 2) }}</td>
            <td></td>
        </tr>
    </table>

    <h3>Total Earned Sales for {{ $selectedMonth->name }} {{ $selectedYear }}</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount_w, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount_w, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount_w, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount_w, 2) }}</td>
            <td></td>
        </tr>
    </table>

    <h3>Total Earned Sales for {{ $selectedMonth->name }} {{ $selectedYear }} (Week {{ $selectedWeekId }})</h3>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>&#8369;{{ number_format($total_amount_d, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>&#8369;{{ number_format($total_reserved_amount_d, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>&#8369;{{ number_format($total_picked_up_amount_d, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>&#8369;{{ number_format($total_canceled_amount_d, 2) }}</td>
            <td></td>
        </tr>
    </table>

    {{-- <div class="remarks-section">
        <div class="remarks-title">Remarks:</div>
        <div class="remarks-box">&nbsp;</div>
    </div> --}}

    <div class="signatures-section">
        @php
            $signatures = \App\Models\Signature::where('category', 'product')
                ->where(function ($query) {
                    $query->where('report_type', 'sales')->orWhere('report_type', 'all');
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
