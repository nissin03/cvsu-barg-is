<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cavite State University Facility Billing Statements</title>
  <style>
    @page {
      margin: 0.25in 0.75in 0.75in 0.75in;
    }
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
    .office-name {
      font-size: 16px;
      font-weight: bold;
      margin: 20px 0;
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 8px;
      text-align: left;
      border: 1px solid #ddd;
      font-size: 12px;
    }
    th {
      background-color: #f4f4f4;
      text-align: center;
    }
    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }
    /* .badge {
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
      display: inline-block;
    }
    .badge-completed { background-color: #10b981; color: white; }
    .badge-canceled { background-color: #ef4444; color: white; }
    .badge-reserved { background-color: #3b82f6; color: white; }
    .badge-pending { background-color: #f59e0b; color: white; } */

    .total-row {
      font-weight: bold;
      background-color: #f8f9fa;
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
            <a href="http://www.cvsu.edu.ph">www.cvsu.edu.ph</a>
          </div>
        </td>
        <td class="logo-right">
          <img src="{{ public_path('images/logo/BaRG-logo.png') }}" alt="BaRG Logo">
        </td>
      </tr>
    </table>
  </div>

  <div class="office-name">
    PRODUCTION AND RESOURCE GENERATION OFFICE
  </div>

  <h3>Facility Billing Statements</h3>
  <p style="font-size: 14px;">
    Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
    Date Range: {{ $dateFrom }} to {{ $dateTo }}
  </p>

  <table>
    <thead>
      <tr>
        <th class="text-center">User</th>
        <th class="text-center">Facility</th>
        <th class="text-center">Status</th>
        <th class="text-center">Total Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($payments as $payment)
      <tr>
        <td>{{ $payment->user->name }}</td>
        <td  class="text-center">{{ $payment->availability->facility->name }}</td>
        <td class="text-center">
          @php
              $statusClass = [
                  'completed' => 'badge-completed',
                  'canceled' => 'badge-canceled',
                  'reserved' => 'badge-reserved',
                  'pending' => 'badge-pending'
              ][$payment->status] ?? '';
          @endphp
          <span class="badge {{ $statusClass }}">
            {{ ucfirst($payment->status) }}
          </span>
        </td>
        <td class="text-right">₱{{ number_format($payment->total_price, 2) }}</td>
      </tr>
      @endforeach
      
      @if($payments->isEmpty())
      <tr>
        <td colspan="4" class="text-center">No records found</td>
      </tr>
      @else
      <tr class="total-row">
        <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
        <td class="text-right">₱{{ number_format($payments->sum('total_price'), 2) }}</td>
      </tr>
      @endif
    </tbody>
  </table>
</body>
</html>