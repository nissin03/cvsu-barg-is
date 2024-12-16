<!DOCTYPE html>
<html>
<head>
    <title>Rentals Reports by Name PDF</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 10px; 
            margin: 0;
        }
        h2 { 
            text-align: center; 
            margin-bottom: 10px; 
            font-size: 20px;
            font-weight: bold;
        }
        .table-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px; /* Reduce font size */
            table-layout: fixed; /* Ensures fixed table layout */
        }
        th, td {
            padding: 6px 8px; /* Reduce padding */
            border: 1px solid #ddd;
            text-align: center;
            word-wrap: break-word; /* Break long content */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .number-cell {
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>Rentals Reports by Name</h2>

    <!-- Monthly Rentals Report Table -->
    <div class="table-title">Monthly Reservations by Rental Name for </div>
    <table>
        <thead>
            <tr>
                <th>Rental Name</th>
                <th>Jan</th><th>Feb</th><th>Mar</th>
                <th>Apr</th><th>May</th><th>Jun</th>
                <th>Jul</th><th>Aug</th><th>Sep</th>
                <th>Oct</th><th>Nov</th><th>Dec</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rentalNames as $rental)
                <tr>
                    <td><strong>{{ $rental->name }}</strong></td>
                    @foreach(range(1, 12) as $month)
                        <td class="number-cell">
                            {{ number_format($monthlyData[$rental->name][$month] ?? 0) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Weekly Rentals Report Table -->
    <div class="table-title">Weekly Reservations by Rental Name for {{ $selectedYear }}</div>
    <table>
        <thead>
            <tr>
                <th>Rental Name</th>
                <th>W1</th><th>W2</th><th>W3</th>
                <th>W4</th><th>W5</th><th>W6</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rentalNames as $rental)
                <tr>
                    <td><strong>{{ $rental->name }}</strong></td>
                    @foreach(range(1, 6) as $week)
                        <td class="number-cell">
                            {{ number_format($weeklyData[$rental->name][$week] ?? 0) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Daily Rentals Report Table -->
    <div class="table-title">Daily Reservations by Rental Name for {{ $selectedYear }}</div>
    <table>
        <thead>
            <tr>
                <th>Rental Name</th>
                <th>Mon</th><th>Tue</th><th>Wed</th>
                <th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rentalNames as $rental)
                <tr>
                    <td><strong>{{ $rental->name }}</strong></td>
                    @foreach($daysOfWeek as $day)
                        <td class="number-cell">
                            {{ number_format($dailyData[$rental->name][$day] ?? 0) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
