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
    </style>
</head>
<body>

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
