<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite State University Product List Report</title>
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

        /* Table Styles for Product List */
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
            font-size: 11px;
        }

        th {
            background-color: #f4f4f4;
            text-align: center;
        }

        .info-container {
            text-align: left;
            margin: 15px 0;
            font-size: 12px;
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

        .product-name {
            font-weight: bold;
            font-size: 12px;
        }

        .variant-row {
            background-color: #f8f9fa;
        }

        .variant-name {
            padding-left: 20px;
            font-style: italic;
        }

        .category-info {
            font-size: 10px;
            color: #666;
        }

        .stock-quantity,
        .price {
            text-align: center;
            font-weight: bold;
        }

        .stock-status {
            font-size: 9px;
            text-align: center;
        }

        .text-warning {
            color: #856404;
        }

        .text-danger {
            color: #721c24;
        }

        .text-success {
            color: #155724;
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
                        <a href="https://cvsu.edu.ph/" target="_blank">www.cvsu.edu.ph</a>
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

    <h3>Product List Report</h3>

    <!-- Information Section -->
    <div class="info-container">
        <div class="info-row">
            <span class="info-label">Downloaded on</span>
            <span class="info-separator">:</span>
            <span
                class="info-value">{{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}</span>
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
        <div class="info-row">
            <span class="info-label">Total Products</span>
            <span class="info-separator">:</span>
            <span class="info-value">{{ $products->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Product Information</th>
                {{-- <th style="width: 25%;">Stock Quantity</th> --}}
                <th style="width: 25%;">Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                {{-- Display base product row if it has no variants OR if it has a base price --}}
                @if ($product->attributeValues->count() == 0 || $product->price)
                    <tr>
                        <td>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="category-info">Category: {{ $product->category->name }}</div>
                            @if ($product->attributeValues->count() > 0)
                                <div class="category-info" style="color: #17a2b8;">
                                    <i class="fas fa-tag"></i> Base Product
                                </div>
                            @endif
                        </td>
                        {{-- <td class="stock-quantity">
                            {{ $product->quantity }}
                            <div class="stock-status">
                                @if ($product->reorder_quantity > 0)
                                    <span class="text-warning">Reorder: {{ $product->reorder_quantity }}</span><br>
                                @endif
                                @if ($product->outofstock_quantity > 0)
                                    <span class="text-danger">Out of Stock: {{ $product->outofstock_quantity }}</span>
                                @endif
                            </div>
                        </td> --}}
                        <td class="price">
                            @if ($product->price)
                                ₱{{ number_format($product->price, 2) }}
                            @else
                                <span style="color: #6c757d;">Not set</span>
                            @endif
                        </td>
                    </tr>
                @endif

                {{-- Display each attribute value as a separate row --}}
                @foreach ($product->attributeValues as $attributeValue)
                    <tr class="variant-row">
                        <td>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="variant-name">- {{ $attributeValue->value }}</div>
                            <div class="category-info">Category: {{ $product->category->name }}</div>
                            @if ($attributeValue->description)
                                <div class="category-info" style="font-style: italic;">
                                    {{ $attributeValue->description }}
                                </div>
                            @endif
                        </td>
                        {{-- <td class="stock-quantity">
                            {{ $attributeValue->quantity }}
                            <div class="stock-status">
                                @if ($attributeValue->stock_status === 'reorder')
                                    <span class="text-warning">Low Stock</span>
                                @elseif($attributeValue->stock_status === 'outofstock')
                                    <span class="text-danger">Out of Stock</span>
                                @else
                                    <span class="text-success">In Stock</span>
                                @endif
                            </div>
                        </td> --}}
                        <td class="price">
                            @if ($attributeValue->price)
                                ₱{{ number_format($attributeValue->price, 2) }}
                            @else
                                <span style="color: #6c757d;">Not set</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px;">
                        No products found
                    </td>
                </tr>
            @endforelse
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
                            <div style="height: 20px;">&nbsp;</div> <!-- Two spaces above the name -->
                            <div><strong>{{ $signature->name }}</strong></div>
                            <div>{{ $signature->position }}</div>
                        </div>
                    @endforeach
                </td>

                <td style="width: 50%; vertical-align: top; text-align: left; border: none;">
                    @foreach ($rightSignatures as $signature)
                        <div class="signature-block" style="margin-bottom: 30px;">
                            <div style="font-weight: bold;">{{ $signature->label }}:</div>
                            <div style="height: 20px;">&nbsp;</div> <!-- Two spaces above the name -->
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
