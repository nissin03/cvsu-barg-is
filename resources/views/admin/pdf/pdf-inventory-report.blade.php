<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Inventory Report PDF</title>
    <style>
        /* Embed DejaVu Sans font – ensure the TTF file exists at public/fonts/DejaVuSans.ttf */
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

        /* General Styles */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
        }

        /* HEADER STYLES */
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
            margin-right: -90px;
            margin-top: -10px;
        }

        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -100px;
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

        /* INVENTORY REPORT STYLES */
        h2 {
            text-align: center;
        }

        p {
            text-align: center;
            font-size: 14px;
        }

        table.inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.inventory-table,
        table.inventory-table th,
        table.inventory-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        table.inventory-table th {
            background-color: #f2f2f2;
        }

        .badge {
            padding: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .bg-success {
            background-color: #28a745;
            color: #fff;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .bg-danger {
            background-color: #dc3545;
            color: #fff;
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

        /* Empty State Styles */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            margin: 20px 0;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .empty-state-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }

        .empty-state-message {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
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
    <!-- End Header Section -->

    <div class="office-name">
        OFFICE BUSINESS AFFAIRS AND MARKETING OFFICE
    </div>

    <h3 style="text-align: center;">INVENTORY REPORT</h3>

    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span class="info-value">
                {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
            </span>
        </div>

        @if (isset($statusLabel))
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-separator">:</span>
                <span class="info-value">{{ $statusLabel }}</span>
            </div>
        @endif
    </div>

    @if ($products->isEmpty())
        <!-- Empty State Section -->
        <div class="empty-state">
            <div class="empty-state-title">No Products Found</div>
            <div class="empty-state-message">
                There are no products matching your current filter criteria.
            </div>
            <div style="font-size: 12px; color: #868e96;">
                Filter: {{ isset($statusLabel) ? $statusLabel : 'All Items' }}
            </div>
        </div>
    @else
        <!-- Inventory Table -->
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    {{-- <th>Price</th> --}}
                    @if (!isset($statusLabel))
                        <th>Stock Status</th>
                    @endif
                    <th>Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        {{-- <td>
                            @if ($product->attributeValues->isNotEmpty())
                                ₱{{ number_format($product->attributeValues->first()->price, 2) }}
                            @else
                                ₱{{ number_format($product->price, 2) }}
                            @endif
                        </td> --}}
                        @if (!isset($statusLabel))
                            <td>
                                @php
                                    $currentStock = $product->attributeValues->isNotEmpty()
                                        ? $product->attributeValues->sum('quantity')
                                        : $product->current_stock;
                                @endphp

                                @if ($currentStock <= $product->outofstock_quantity)
                                    <span>Low Stock</span>
                                @elseif($currentStock <= $product->reorder_quantity)
                                    <span>Reorder Level</span>
                                @else
                                    <span>In Stock</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @php
                                $currentStock = $product->attributeValues->isNotEmpty()
                                    ? $product->attributeValues->sum('quantity')
                                    : $product->current_stock;
                            @endphp
                            {{ number_format($currentStock) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (!$products->isEmpty())
        <div class="signatures-section">
            @php
                $signatures = \App\Models\Signature::where('category', 'product')
                    ->where(function ($query) {
                        $query->where('report_type', 'inventory')->orWhere('report_type', 'all');
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

            <table class="signatures-table"
                style="width: 100%; margin-top: 10px; border-collapse: collapse; border: none;">
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
    @endif
</body>

</html>
