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
            margin: 0.25in 0.75in 0.75in 0.75in; /* top, right, bottom, left */
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
            max-width: 90px;
            margin-right: -250px; 
            margin-bottom: 20px; 
        }
        .logo-right img {
            height: 80px;
            max-width: 110px;
            margin-left: -260px; 
            margin-bottom: 20px; 
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
        table.inventory-table, table.inventory-table th, table.inventory-table td {
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
    </style>
</head>
<body>
    <!-- Header Section -->
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
    <!-- End Header Section -->

    <h2>Inventory Report of Marketing Center</h2>
    <p>
        Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
        @if(isset($statusLabel))
            Status: {{ $statusLabel }}<br>
        @endif
        {{-- Date Range:
        @if ($startDate && $endDate)
            {{ $startDate }} to {{ $endDate }}
        @else
            N/A
        @endif --}}
        
    </p>

    <!-- Inventory Table -->
    <table class="inventory-table">
        <thead>
            <tr>
                <!-- <th>Product ID</th> -->
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                @if(!isset($statusLabel))
                    <th>Stock Status</th>
                @endif
                <th>Current Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <!-- Product Details -->
                <!-- <td>{{ $product->id }}</td> -->
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name }}</td>
                <td>
                    @if($product->attributeValues->isNotEmpty())
                        {{ $product->attributeValues->first()->price }}
                    @else
                        {{ $product->price }}
                    @endif
                </td>
                @if(!isset($statusLabel))
                    <td>
                        @php
                            $currentStock = $product->attributeValues->isNotEmpty()
                                ? $product->attributeValues->sum('quantity')
                                : $product->current_stock;
                        @endphp

                        @if($currentStock <= $product->outofstock_quantity)
                            <span class="">Low Stock</span>
                        @elseif($currentStock <= $product->reorder_quantity)
                            <span class="">Reorder Level</span>
                        @else
                            <span class="">In Stock</span>
                        @endif
                    </td>
                @endif
                <td>
                    @php
                        $currentStock = $product->attributeValues->isNotEmpty()
                            ? $product->attributeValues->sum('quantity')
                            : $product->current_stock;
                    @endphp
                    {{ $currentStock }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>