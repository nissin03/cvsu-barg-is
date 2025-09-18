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
    .total-row {
      font-weight: bold;
      background-color: #f8f9fa;
    }
    .status-info {
      font-size: 14px;
      margin-bottom: 10px;
    }
    .prepared-by {
      margin-top: 40px;
      text-align: right;
      width: 100%;
    }
    .signature-line {
      border-top: 1px solid #000;
      width: 250px;
      /* margin-top: 40px; */
      margin-left: auto;
    }
    .badge {
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: bold;
      display: inline-block;
    }
    .badge-completed {
      background-color: #10b981;
      color: white;
    }
    .badge-canceled {
      background-color: #ef4444;
      color: white;
    }
    .badge-reserved {
      background-color: #3b82f6;
      color: white;
    }
    .badge-pending {
      background-color: #f59e0b;
      color: white;
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
  
  @php
    $allSameStatus = false;
    $commonStatus = null;
    
    if (!$payments->isEmpty()) {
      $firstStatus = $payments->first()->status;
      $allSameStatus = $payments->every(function($payment) use ($firstStatus) {
        return $payment->status === $firstStatus;
      });
      
      if ($allSameStatus) {
        $commonStatus = $firstStatus;
      }
    }
  @endphp

  <p class="status-info">
    Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}<br>
    Date Range: {{ $dateFrom }} to {{ $dateTo }}
    @if($allSameStatus)
      <br>Status: {{ ucfirst($commonStatus) }}
    @endif
    @if($selectedFacility)
      <br>Facility: {{ $selectedFacility->name }}
    @endif
  </p>

  <table>
    <thead>
      <tr>
        <th class="text-center">User</th>
        @if($showAllFacilities)
          <th class="text-center">Facility</th>
        @endif
        <th class="text-center">Reservation Dates</th>
        @if(!$allSameStatus)
          <th class="text-center">Status</th>
        @endif
        <th class="text-center">Total Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($payments as $payment)
      @php
        // Extract dates from transaction reservations or availability
        $dates = $payment->transactionReservations->pluck('availability.date_from')->filter()->unique()->sort();
        
        if ($dates->count() > 0) {
          $dateFrom = $dates->first();
          $dateTo = $dates->last();
        } else {
          // Fallback to availability dates
          $dateFrom = $payment->availability->date_from ?? null;
          $dateTo = $payment->availability->date_to ?? null;
        }
        
        // Format dates for display
        $formattedDateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'N/A';
        $formattedDateTo = $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'N/A';
      @endphp
      <tr>
        <td>{{ $payment->user->name }}</td>
        @if($showAllFacilities)
          <td class="text-center">{{ $payment->availability->facility->name }}</td>
        @endif
        <td class="text-center">
          @if($dateFrom && $dateTo)
            {{ $formattedDateFrom }} - {{ $formattedDateTo }}
          @else
            N/A
          @endif
        </td>
        @if(!$allSameStatus)
          <td class="text-center">
            @php
                $statusClass = [
                    'completed' ,
                    'canceled' ,
                    'reserved',
                    'pending'
                ][$payment->status] ?? '';
            @endphp
            <span class="badge {{ $statusClass }}">
              {{ ucfirst($payment->status) }}
            </span>
          </td>
        @endif
        <td class="text-right">₱{{ number_format($payment->total_price, 2) }}</td>
      </tr>
      @endforeach
      
      @if($payments->isEmpty())
      <tr>
        @php
          $colspan = 2; 
          if ($showAllFacilities) $colspan++; 
          if (!$allSameStatus) $colspan++; 
          $colspan++; 
        @endphp
        <td colspan="{{ $colspan }}" class="text-center">No records found</td>
      </tr>
      @else
      <tr class="total-row">
        @php
          $colspan = 2;
          if ($showAllFacilities) $colspan++; 
          if (!$allSameStatus) $colspan++; 
        @endphp
        <td colspan="{{ $colspan }}" class="text-right"><strong>Grand Total:</strong></td>
        <td class="text-right">₱{{ number_format($payments->sum('total_price'), 2) }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  <!-- Prepared By Section -->
  <div class="prepared-by">
    <div>Prepared by:</div>
    <div class="signature-line"></div>
    <div>{{ Auth::user()->name }}</div>
    <div>{{ Auth::user()->role ? ucfirst(Auth::user()->role) : 'Administrator' }}</div>
    <div>Production and Resource Generation Office</div>
    <div>Cavite State University</div>
  </div>
</body>
</html>

