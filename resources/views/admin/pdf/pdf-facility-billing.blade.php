<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite State University Facility Sales Reports</title>
    <style>
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in;
        }

        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            font-size: 12px;
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
            margin-right: -160px;
            margin-top: -10px;
        }

        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -170px;
            margin-top: -10px;
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

        th,
        td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        th {
            background-color: #f4f4f4;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
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

        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-completed {
            background-color: #10b981;
            color: white;
        }

        .badge-canceled {
            background-color: #ef4444;
            color: white;
        }

        .badge-reserved {
            background-color: #3b82f6;
            color: white;
        }

        .badge-pending {
            background-color: #f59e0b;
            color: white;
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
                        <a href="https://cvsu.edu.ph/">www.cvsu.edu.ph</a>
                    </div>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo/BaRG-logo.png') }}" alt="BaRG Logo">
                </td>
            </tr>
        </table>
    </div>

    <div class="office-name">
        PRODUCTION AND RESOURCE GENERATION OFFICE
    </div>

    <h3>Facility Sales Reports</h3>

    @php
        $allSameStatus = false;
        $commonStatus = null;

        if (!$payments->isEmpty()) {
            $firstStatus = $payments->first()->status;
            $allSameStatus = $payments->every(function ($payment) use ($firstStatus) {
                return $payment->status === $firstStatus;
            });

            if ($allSameStatus) {
                $commonStatus = $firstStatus;
            }
        }
    @endphp

    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span
                class="info-value">{{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period Covered</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                @if ($dateFrom != 'N/A' && $dateTo != 'N/A')
                    {{ $dateFrom }} to {{ $dateTo }}
                @else
                    N/A
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Facility</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                @if ($selectedFacility)
                    {{ $selectedFacility->name }}
                @else
                    All Facilities
                @endif
            </span>
        </div>
        @if ($allSameStatus && $commonStatus)
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ ucfirst($commonStatus) }}</span>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Name</th>
                @if ($showAllFacilities)
                    <th class="text-center">Facility</th>
                @endif
                <th class="text-center">Reservation Dates</th>
                @if (!$allSameStatus)
                    <th class="text-center">Status</th>
                @endif
                <th class="text-center">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                @php
                    $allDates = $payment->transactionReservations
                        ->pluck('availability')
                        ->filter()
                        ->flatMap(function ($availability) {
                            $dates = [];
                            if ($availability->date_from && $availability->date_to) {
                                if ($availability->date_from == $availability->date_to) {
                                    $dates[] = $availability->date_from;
                                } else {
                                    $dates[] = $availability->date_from;
                                    $dates[] = $availability->date_to;
                                }
                            }
                            return $dates;
                        })
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();

                    if ($allDates->count() > 0) {
                        $dateFrom = $allDates->first();
                        $dateTo = $allDates->last();
                    } else {
                        $dateFrom = $payment->availability->date_from ?? null;
                        $dateTo = $payment->availability->date_to ?? null;
                    }
                @endphp
                <tr>
                    <td>{{ $payment->user->name }}</td>
                    @if ($showAllFacilities)
                        <td class="text-center">{{ $payment->availability->facility->name }}</td>
                    @endif
                    <td class="text-center">
                        @if ($dateFrom && $dateTo)
                            @if ($dateFrom == $dateTo)
                                {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} -
                                {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    @if (!$allSameStatus)
                        <td class="text-center">

                            {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    @endif
                    <td class="text-right">₱{{ number_format($payment->total_price, 2) }}</td>
                </tr>
            @endforeach

            @if ($payments->isEmpty())
                <tr>
                    @php
                        $colspan = 2;
                        if ($showAllFacilities) {
                            $colspan++;
                        }
                        if (!$allSameStatus) {
                            $colspan++;
                        }
                        $colspan++;
                    @endphp
                    <td colspan="{{ $colspan }}" class="text-center">No records found</td>
                </tr>
            @else
                <tr class="total-row">
                    @php
                        $colspan = 2;
                        if ($showAllFacilities) {
                            $colspan++;
                        }
                        if (!$allSameStatus) {
                            $colspan++;
                        }
                    @endphp
                    <td colspan="{{ $colspan }}" class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right">₱{{ number_format($payments->sum('total_price'), 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

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
                'name' => Auth::user()->name,
                'position' => Auth::user()->position,
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
