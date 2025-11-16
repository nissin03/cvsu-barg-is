<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite State University Sales Report</title>
    <style>
        @page {
            margin: 0.25in 0.75in 0.75in 0.75in;
            /* top, right, bottom, left */
        }

        /* Optional: Embed DejaVu Sans font if available */
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

        /* Header Styles */
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

        /* Apply negative margins directly on the images to shift them closer to the center text */
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

        /* Office Name */
        .office-name {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        /* Table Styles for Sales Report */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        th {
            background-color: #f4f4f4;
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

    <div class="office-name">
        OFFICE BUSINESS AFFAIRS AND MARKETING OFFICE
    </div>

    <h3>Sales Report</h3>

    <!-- Information Section -->
    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span
                class="info-value">{{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Reservation Period</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                @if ($startDate && $endDate)
                    {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to
                    {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
                @else
                    N/A
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Category</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                @if ($category && $categoryName)
                    {{ $categoryName }}
                @else
                    All Categories
                @endif
            </span>
        </div>
        @if ($status)
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ ucfirst($status) }}</span>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Product/Variant</th>
                <th>Quantity</th>
                <th>Amount</th>
                @if (!$status)
                    <th>Status</th>
                @endif
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
            @endphp
            @forelse($orders as $order)
                @foreach ($order->orderItems as $item)
                    @php
                        $itemTotal = $item->price * $item->quantity;
                        $grandTotal += $itemTotal;
                    @endphp
                    <tr>
                        <td>{{ $order->user->name }}</td>
                        <td>
                            {{ $item->product->name }}
                            @if ($item->variant)
                                <br><small>Variant: {{ $item->variant->value }}</small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($itemTotal, 2) }}</td>
                        @if (!$status)
                            <td>{{ ucfirst($order->status) }}</td>
                        @endif
                        <td> {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="{{ $status ? 5 : 6 }}" class="text-center">No records found</td>
                </tr>
            @endforelse

            @if ($orders->count() > 0)
                <tr style="border-top: 2px solid #333;">
                    <td colspan="{{ $status ? 4 : 5 }}" style="text-align: right; font-weight: bold;">Grand Total:</td>
                    <td style="font-weight: bold; text-align: center;">₱{{ number_format($grandTotal, 2) }}</td>
                    {{-- <td colspan="{{ $status ? 1 : 2 }}"></td> --}}
                </tr>
            @endif
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
