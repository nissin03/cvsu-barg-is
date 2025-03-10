<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cavite State University Billing Statements</title>
  <style>
    @page {
      margin: 0.25in 0.75in 0.75in 0.75in; /* top, right, bottom, left */
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
    /* Table Styles for Billing Statements */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 8px;
      text-align: center;
      border: 1px solid #ddd;
      font-size: 12px;
    }
    th {
      background-color: #f4f4f4;
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
    PRODUCTION AND RESOURCE GENERATION OFFICE
  </div>

  <h3>Billing Statements</h3>
  <p style="font-size: 14px;">
    Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
    Date Range:
    @if ($startDate && $endDate)
      {{ $startDate }} to {{ $endDate }}
    @else
      N/A
    @endif
  </p>

  <table>
    <thead>
      <tr>
        <!-- <th>Order No</th> -->
        <th>Name</th>
        <th>Email</th>
        <th>Total</th>
        <th>Order Date</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($orders as $order)
      <tr>
        <!-- <td>{{ $order->id }}</td> -->
        <td>{{ $order->user->name }}</td>
        <td>{{ $order->user->email }}</td>
        <td>{{ number_format($order->total, 2) }}</td>
        <td>{{ $order->created_at->format('F d, Y') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
