<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Report</title>
  <style>
    /* Set PDF page margins */
    @page {
      margin: 0.25in 0.75in 0.75in 0.75in; /* top, right, bottom, left */
    }

    /* Global Styles */
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      margin: 0; /* page margins defined by @page */
    }
    h1, h2, h3 {
      text-align: center;
    }
    .text-tiny {
      font-size: 0.875rem;
      color: #6c757d;
    }

    /* Styles for content tables (with borders) */
    .content-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .content-table th,
    .content-table td {
      border: 1px solid #000;
      padding: 10px;
      text-align: left;
    }
    .content-table th {
      background-color: #f8f9fa;
    }

    /* Header Section Styles (no borders) */
    .header {
      width: 100%;
      margin-top: 20px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    /* Remove borders from header table cells */
    .header-table, 
    .header-table th, 
    .header-table td {
      border: none !important;
    }
    .header-table td {
      vertical-align: middle;
      padding: 0 10px;
      width: auto;
    }
    /* Logo cell fixed widths */
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
    /* Shift the logos closer to the center text by applying negative margins to the images */
    .logo-left img {
      height: 80px;
      max-width: 80px;
      margin-right: -180px; /* adjust as needed */
    }
    .logo-right img {
      height: 80px;
      max-width: 110px;
      margin-left: -190px; /* adjust as needed */
    }
    /* Center cell styles */
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

    /* Class to force a page break */
    .page-break {
      page-break-before: always;
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

  <!-- Report Title & Downloaded Timestamp -->
  <h2>PRODUCT REPORT OF BUSINESS AFFAIRS AND MARKETING OFFICE</h2>
  <p style="text-align: center; font-size: 14px;">
    Downloaded on: {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}
  </p>

  <!-- Most Frequent Products Section -->
  <h3>Most Frequent Products</h3>
  <table class="content-table">
    <thead>
      <tr>
        <th>Product Name</th>
        <th class="text-center">Total Orders</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($mostFrequentLabels as $index => $label)
        <tr>
          <td>{{ $label ?? 'No Product' }}</td>
          <td class="text-center">{{ $mostFrequentData[$index] ?? '0' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Force page break before the Least Bought Products Section -->
  <div class="page-break"></div>

  <!-- Least Bought Products Section -->
  <h3>Least Bought Products</h3>
  <table class="content-table">
    <thead>
      <tr>
        <th>Product Name</th>
        <th class="text-center">Total Orders</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($leastBoughtLabels as $index => $label)
        <tr>
          <td>{{ $label ?? 'No Product' }}</td>
          <td class="text-center">{{ $leastBoughtData[$index] ?? '0' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

    <div class="prepared-by">
    <div>Prepared by:</div>
    <div class="signature-line"></div>
    <div>{{ Auth::user()->name }}</div>
    <div>{{ Auth::user()->role ? ucfirst(Auth::user()->role) : 'Administrator' }}</div>
    <div>Business Affairs and Marketing Office</div>
    <div>Cavite State University</div>
</body>
</html>
