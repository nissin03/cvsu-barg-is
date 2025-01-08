<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        /* Add your styles here for the PDF layout */
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h1, h3, h2 {
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
            border: none;
            padding: 0 10px;
            width: auto;
        }
        .header-table img {
            height: 60px;
            max-width: 80px;
        }
        .center-text {
            text-align: center;
            padding: 0;
            margin: 0;
        }
        .university-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
        }
        .subtext {
            font-size: 12px;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="header">
        <table class="header-table">
            <tr>
                <td style="text-align: right; width: 35%;">
                    <img src="{{ public_path('images/logo/cvsu-logo.png') }}" alt="">
                </td>
                
                <td class="center-text" style="width: 30%;">
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
                
                <td style="text-align: left; width: 35%;">
                    <img src="{{ public_path('images/logo/BaRG-logo.jpg') }}" alt="">
                </td>
            </tr>
        </table>
    </div>



    <h1>Sales Report</h1>

    <h2>Monthly Earned Sales</h2>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>${{ $total_amount }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>${{ $total_reserved_amount }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>${{ $total_picked_up_amount }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>${{ $total_canceled_amount }}</td>
        </tr>
    </table>

    <h2>Weekly Earnings</h2>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>${{ $total_amount_w }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>${{ $total_reserved_amount_w }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>${{ $total_picked_up_amount_w }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>${{ $total_canceled_amount_w }}</td>
        </tr>
    </table>

    <h2>Daily Earned Sales</h2>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>${{ $total_amount_d }}</td>
        </tr>
        <tr>
            <td>Reservation Amount</td>
            <td>${{ $total_reserved_amount_d }}</td>
        </tr>
        <tr>
            <td>Picked Up Amount</td>
            <td>${{ $total_picked_up_amount_d }}</td>
        </tr>
        <tr>
            <td>Cancelled Orders Amount</td>
            <td>${{ $total_canceled_amount_d }}</td>
        </tr>
    </table>

</body>
</html>
