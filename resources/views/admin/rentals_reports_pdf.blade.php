<!DOCTYPE html>
<html>
<head>
    <title>Rentals Reports PDF</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 20px; 
            font-size: 24px;
        }
        .summary {
            font-size: 1.1rem;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .report-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        .no-data {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <h2>Rentals Reports</h2>

    <!-- Summary Section -->
    <div class="summary">
        <p><strong>Total Payment Amount:</strong> ${{ number_format($totalAmounts['total_payment'], 2) }}</p>
        <p><strong>Payment Pending Amount:</strong> ${{ number_format($totalAmounts['pending_amount'], 2) }}</p>
        <p><strong>Payment Completed Amount:</strong> ${{ number_format($totalAmounts['completed_amount'], 2) }}</p>
        <p><strong>Payment Canceled Amount:</strong> ${{ number_format($totalAmounts['canceled_amount'], 2) }}</p>
    </div>

    <!-- Monthly Rentals Table -->
    <div class="report-section">
        <div class="report-title">Monthly Rentals Report</div>
        @if($monthlyData && count($monthlyData) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Rental Count</th>
                        <th>Total Rental Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyData as $month => $data)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>{{ $data['rental_count'] }}</td>
                            <td>${{ number_format($data['total_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No data available for Monthly Rentals.</p>
        @endif
    </div>

    <!-- Weekly Rentals Table -->
    <div class="report-section">
        <div class="report-title">Weekly Rentals Report</div>
        @if($weeklyData && count($weeklyData) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Rental Count</th>
                        <th>Total Rental Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeklyData as $week => $data)
                        <tr>
                            <td>{{ $week }}</td>
                            <td>{{ $data['rental_count'] }}</td>
                            <td>${{ number_format($data['total_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No data available for Weekly Rentals.</p>
        @endif
    </div>

    <!-- Daily Rentals Table -->
    <div class="report-section">
        <div class="report-title">Daily Rentals Report</div>
        @if($dailyData && count($dailyData) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Rental Count</th>
                        <th>Total Rental Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyData as $day => $data)
                        <tr>
                            <td>{{ $day }}</td>
                            <td>{{ $data['rental_count'] }}</td>
                            <td>${{ number_format($data['total_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">No data available for Daily Rentals.</p>
        @endif
    </div>
</body>
</html>

