<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CVSU Receipt</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }

        @font-face {
            font-family: "DejaVu Sans";
            src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format("truetype");
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            font-size: 12px;
            line-height: 1.3;
            width: 100%;
        }

        .container {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .receipt-container {
            border: 1px dashed #333;
            padding: 30px;
            background: #fafafa;
            margin: 0;
            box-sizing: border-box;
            min-height: auto;
        }

        /* === Header Styles === */
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
            margin-right: -140px;
            margin-top: -10px;
        }

        .logo-right img {
            height: 80px;
            max-width: 80px;
            margin-left: -140px;
            margin-top: -10px;
        }

        .center-cell {
            text-align: center;
            vertical-align: middle;
        }

        .university-name {
            font-size: 14px;
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

        /* === Receipt Section Styles === */
        .receipt-info div {
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .receipt-info {
            text-align: left;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .receipt-info .label {
            font-weight: bold;
        }

        .items-section {
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            padding: 10px 0;
            margin-bottom: 15px;
        }

        .item-header {
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .item {
            margin-bottom: 8px;
            font-size: 11px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            color: #555;
            font-size: 10px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .total-line.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 8px 0;
            margin-top: 10px;
        }

        .payment-section {
            border-top: 1px dashed #333;
            padding-top: 10px;
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .generated-time {
            margin-top: 8px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .receipt-container {
                border: none;
                background: white;
                padding: 20px;
            }

            @page {
                margin: 0.5in;
            }

            .header {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    @include('admin.pdf.partials.receipt-content', [
        'order' => $order,
        'orderItems' => $orderItems,
        'transaction' => $transaction,
    ])
</body>

</html>
