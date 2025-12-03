{{-- <!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
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

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Styles - matching PDF report */
        .header {
            width: 100%;
            margin-top: 20px;
            margin-bottom: 30px;
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

        /* Receipt Title */
        .receipt-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
        }

        .receipt-subtitle {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }

        /* Table Styles - matching PDF report */
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
            font-weight: bold;
        }

        /* Order Info Table */
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f9f9f9;
        }

        /* Items Table */
        .items-table th {
            text-align: center;
        }

        .items-table td:nth-child(2),
        .items-table td:nth-child(3),
        .items-table td:nth-child(4) {
            text-align: center;
        }

        /* Totals Table */
        .totals-table {
            width: 50%;
            margin-left: auto;
            margin-bottom: 20px;
        }

        .totals-table td:first-child {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .grand-total {
            background-color: #e8e8e8 !important;
            font-weight: bold;
            font-size: 14px;
        }

        /* Payment Table */
        .payment-table {
            width: 50%;
            margin-left: auto;
        }

        .payment-table td:first-child {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .payment-table td:last-child {
            text-align: right;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            border-top: 2px solid #ddd;
        }

        .thank-you {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .generated-time {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
        }

        /* Prepared By Section */
        .prepared-by {
            margin-top: 40px;
            text-align: right;
            width: 100%;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin-top: 40px;
            margin-left: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header matching PDF report -->
        <div class="header">
            @php
                $isPdf = request()->routeIs('admin.order-receipt.pdf');
            @endphp
            <table class="header-table">
                <tr>
                    <td class="logo-left">
                        <img src="{{ $isPdf ? public_path('images/logo/cvsu-logo.png') : asset('images/logo/cvsu-logo.png') }}"
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
                        <img src="{{ $isPdf ? public_path('images/logo/BaRG-logo.png') : asset('images/logo/BaRG-logo.png') }}"
                            alt="BaRG Logo">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Receipt Title -->
        <h2 class="receipt-title">ORDER RECEIPT</h2>
        <p class="receipt-subtitle">
            Business Affairs and Marketing Office
        </p>

        <!-- Order Information Table -->
        <table class="info-table">
            <tr>
                <td>Order ID</td>
                <td>{{ $order->id }}</td>
            </tr>
            <tr>
                <td>Date & Time</td>
                <td>{{ $order->created_at->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td>Customer Name</td>
                <td>{{ $order->user->name }}</td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>{{ $order->user->email }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ ucfirst($order->status) }}</td>
            </tr>
        </table>

        <!-- Items Table -->
        <h3 style="margin-top: 30px; margin-bottom: 10px;">Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>&#8369;{{ number_format($item->product->price, 2) }}</td>
                        <td>&#8369;{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Table -->
        <table class="totals-table">
            <tr class="grand-total">
                <td>TOTAL AMOUNT</td>
                <td>&#8369;{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>

        <!-- Payment Information Table -->
        <h3 style="margin-top: 30px; margin-bottom: 10px;">Payment Details</h3>
        <table class="payment-table">
            <tr>
                <td>Amount Paid</td>
                <td>&#8369;{{ number_format($transaction->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <td>Change</td>
                <td>&#8369;{{ number_format($transaction->change, 2) }}</td>
            </tr>
            <tr>
                <td>Payment Method</td>
                <td>{{ ucfirst($transaction->payment_method ?? 'Cash') }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">THANK YOU FOR YOUR PURCHASE!</div>
            <div>Keep this receipt for your records</div>
            <div class="generated-time">
                Generated: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
            </div>
        </div>

        <!-- Prepared By Section (Optional) -->
        <div class="prepared-by">
            <div>Processed by:</div>
            <div class="signature-line"></div>
            <div>{{ Auth::user()->name }}</div>
            <div>{{ Auth::user()->role ? ucfirst(Auth::user()->role) : 'Administrator' }}</div>
            <div>Business Affairs and Marketing Office</div>
            <div>Cavite State University</div>
        </div>
    </div>
</body>

</html> --}}

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: 80mm 297mm;
            /* Thermal receipt width (80mm) with flexible height */
            margin: 5mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            width: 70mm;
        }

        .container {
            width: 100%;
        }

        /* Header Styles - Compact for receipt */
        .header {
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
        }

        .header-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
        }

        .header-logos img {
            height: 35px;
            margin: 0 5px;
        }

        .university-name {
            font-size: 9px;
            font-weight: bold;
            margin: 2px 0;
            line-height: 1.2;
        }

        .subtext {
            font-size: 8px;
            margin: 1px 0;
        }

        .subtext a {
            text-decoration: none;
            color: #000;
        }

        /* Receipt Title */
        .receipt-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 3px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .receipt-subtitle {
            text-align: center;
            font-size: 8px;
            margin-bottom: 8px;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 8px;
            font-size: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .info-label {
            font-weight: bold;
        }

        /* Items Section */
        .items-section {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .section-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
            text-align: center;
        }

        .item {
            margin-bottom: 5px;
            font-size: 8px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            padding-left: 5px;
        }

        /* Totals Section */
        .totals-section {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 8px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            margin: 5px 0;
        }

        /* Payment Section */
        .payment-section {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 8px;
        }

        .thank-you {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-text {
            margin: 2px 0;
        }

        .generated-time {
            font-size: 7px;
            color: #666;
            margin-top: 5px;
        }

        /* Prepared By Section */
        .prepared-by {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 40mm;
            margin: 15px auto 3px auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logos">
                <img src="{{ public_path('images/logo/cvsu-logo.png') }}" alt="CVSU Logo">
                <img src="{{ public_path('images/logo/BaRG-logo.png') }}" alt="BaRG Logo">
            </div>
            <div class="university-name">
                Republic of the Philippines<br>
                CAVITE STATE UNIVERSITY<br>
                Don Severino delas Alas Campus
            </div>
            <div class="subtext">
                Indang, Cavite<br>
                www.cvsu.edu.ph
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">ORDER RECEIPT</div>
        <div class="receipt-subtitle">Business Affairs and Marketing Office</div>

        <!-- Order Information -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span>{{ $order->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span>{{ ucfirst($order->status) }}</span>
            </div>
        </div>

        <!-- Customer Information Section -->
        <div class="info-section">
            <div class="section-title">CUSTOMER'S INFORMATION</div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span>{{ $order->user->name }}</span>
            </div>

            @if ($order->user->role === 'student')
                @if ($order->user->year_level)
                    <div class="info-row">
                        <span class="info-label">Year:</span>
                        <span>{{ $order->user->year_level }}</span>
                    </div>
                @endif
                @if ($order->user->department)
                    <div class="info-row">
                        <span class="info-label">College:</span>
                        <span>{{ $order->user->department }}</span>
                    </div>
                @endif
                @if ($order->user->course)
                    <div class="info-row">
                        <span class="info-label">Course:</span>
                        <span>{{ $order->user->course->name }}</span>
                    </div>
                @endif
                @if ($order->user->college)
                    <div class="info-row">
                        <span class="info-label">College:</span>
                        <span>{{ $order->user->college->name }}</span>
                    </div>
                @endif
            @elseif($order->user->role === 'employee')
                @if ($order->user->position)
                    <div class="info-row">
                        <span class="info-label">Position:</span>
                        <span>{{ $order->user->position }}</span>
                    </div>
                @endif
                @if ($order->user->department)
                    <div class="info-row">
                        <span class="info-label">Department:</span>
                        <span>{{ $order->user->department }}</span>
                    </div>
                @endif
            @endif
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <div class="section-title">ORDER ITEMS</div>
            @foreach ($orderItems as $item)
                <div class="item">
                    <div class="item-name">
                        {{ $item->product->name }}
                        @if ($item->variant)
                            <span style="font-size: 0.9em; color: #666;">({{ $item->variant->value }})</span>
                        @endif
                    </div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} × &#8369;{{ number_format($item->price, 2) }}</span>
                        <span
                            style="float: right;">&#8369;{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span>&#8369;{{ number_format($orderItems->sum(fn($item) => $item->price * $item->quantity), 2) }}</span>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <div class="section-title">PAYMENT DETAILS</div>
            <div class="total-row">
                <span>Amount Paid:</span>
                <span>&#8369;{{ number_format($transaction->amount_paid, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Change:</span>
                <span>&#8369;{{ number_format($transaction->change, 2) }}</span>
            </div>
            @if (isset($transaction->payment_method))
                <div class="total-row">
                    <span>Payment Method:</span>
                    <span>{{ ucfirst($transaction->payment_method) }}</span>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">THANK YOU!</div>
            <div class="footer-text">Keep this receipt for your records</div>
            <div class="generated-time">
                Generated: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('M d, Y g:i a') }}
            </div>
        </div>

        <!-- Prepared By Section -->
        <div class="prepared-by">
            <div>Processed by:</div>
            <div>{{ Auth::user()->name }}</div>
            <div>{{ Auth::user()->position ? ucfirst(Auth::user()->position) : 'Administrator' }}</div>
        </div>
    </div>
</body>

</html>
