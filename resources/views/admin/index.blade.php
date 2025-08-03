@extends('layouts.admin')
@section('content')
    <main class="container">
        <div class="main-content-inner">
            <div class="container mt-6">

                <div class="container my-4">
                    <div class="row row-cols-1 row-cols-md-3 g-4">

                        <!-- Total Payment Amount -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Reservation</div>
                                    <h4>{{ $dashboardData[0]->Total }}</h4>

                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Reserve Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                     ₱
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Amount of Reserve Items</div>
                                     <h4>₱{{ number_format($dashboardData[0]->TotalReservedAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Claimed Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Claimed Items</div>
                                    <h4>{{ $dashboardData[0]->TotalPickedUp }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Claimed Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                     ₱
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Amount of Claimed Items</div>
                                  <h4>₱{{ number_format($dashboardData[0]->TotalPickedUpAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Canceled Orders -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Canceled Orders</div>
                                    <h4>{{ $dashboardData[0]->TotalCanceled }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Cancelled Orders -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div class="image ic-bg me-3">
                                     ₱
                                </div>
                                <div>
                                    <div class="body-text mb-2">Total Amount of Cancelled Orders</div>
                                     <h4>₱{{ number_format($dashboardData[0]->TotalCanceledAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>


                    </div>




                </div>
            </div>

            <div class="main-content-wrap">
                <div class="tf-section-2 mb-30">
                    <div class="flex gap20 flex-wrap-mobile">

                        <div class="wg-box p-4 bg-light shadow-sm rounded-lg w-100" style="max-width: 100%;">
                            <!-- Header Controls -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center"
                                        type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <h5 class="mb-0 me-2" id="chart-title">Monthly Earnings Revenue</h5>
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center p-3" href="#"
                                                data-view="monthly">
                                                <i class="fas fa-calendar-alt me-2"></i> Monthly Earnings Revenue
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center p-3" href="#"
                                                data-view="weekly">
                                                <i class="fas fa-calendar-week me-2"></i> Weekly Earnings Revenue
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center p-3" href="#"
                                                data-view="daily">
                                                <i class="fas fa-calendar-day me-2"></i> Daily Earnings Revenue
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Dynamic Filter Controls -->
                                <div class="d-flex flex-grow-1 align-items-center ms-3" id="filter-controls">
                                    <!-- Year selector (always visible) -->
                                    <select id="year-select" class="form-select me-2">
                                        @foreach ($yearRange as $year)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <!-- Month selector (for weekly and daily views) -->
                                    <select id="month-select" class="form-select me-2" style="display: none;">
                                        <option value="">Select Month</option>
                                    </select>

                                    <!-- Week selector (for daily view only) -->
                                    <select id="week-select" class="form-select me-2" style="display: none;">
                                        <option value="">Select Week</option>
                                    </select>

                                    <!-- Loading indicator -->
                                    <div id="loading-spinner" class="spinner-border spinner-border-sm me-2" role="status"
                                        style="display: none;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Cards -->
                            <div class="row mb-4" id="summary-cards">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t1"></div>
                                            <div class="text-tiny">Total</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 id="total-amount">₱0</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t2"></div>
                                            <div class="text-tiny">Reservation Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 id="reserved-amount">₱0</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t3"></div>
                                            <div class="text-tiny">Received Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 id="pickedup-amount">₱0</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t4"></div>
                                            <div class="text-tiny">Cancelled Orders Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 id="canceled-amount">₱0</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart Container -->
                            <div id="dashboard-chart"></div>
                        </div>

                    </div>

                    <!-- Stock Status Table Container -->
                    <div class="container p-4"
                        style="background-color: #f8f9fc; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex justify-content-between align-items-center mb-3">

                        </div>
                        <div style="overflow-x: auto;">
                            <!-- Updated Stock Status Table -->
                            <table class="table table-bordered table-hover" style="table-layout: auto;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">Product ID</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Stock Status</th>
                                        <th class="text-center">Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($products->isEmpty())
                                        <tr>
                                            <td colspan="10" class="text-center py-5"
                                                style="height: 200px; vertical-align: middle;">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                    <span class="text-muted ">No products found</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($products as $product)
                                            @php
                                                $currentStock = $product->attributeValues->isNotEmpty()
                                                    ? $product->attributeValues->sum('quantity')
                                                    : $product->current_stock;
                                            @endphp

                                            @if ($currentStock == 0)
                                                <!-- Check if the stock is 0 -->
                                                <tr>
                                                    <td class="text-center">{{ $product->id }}</td>
                                                    <td class="text-center">{{ $product->name }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    </td>
                                                    <td class="text-center">{{ $currentStock }}</td>
                                                </tr>
                                            @elseif($currentStock <= $product->reorder_quantity)
                                                <!-- Display 'Reorder' and 'Low Stock' items -->
                                                <tr>
                                                    <td class="text-center">{{ $product->id }}</td>
                                                    <td class="text-center">{{ $product->name }}</td>
                                                    <td class="text-center">
                                                        @if ($currentStock <= $product->outofstock_quantity)
                                                            <span class="badge bg-danger">Low Stock</span>
                                                        @else
                                                            <span class="badge bg-warning">Reorder Level</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $currentStock }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>

                            </table>
                            <!-- End of Updated Stock Status Table -->
                        </div>
                    </div>


                </div>
            </div>


            <div class="tf-section mb-30">

                <div class="wg-box">
                    <div class="flex items-center justify-between">
                        <h5>Recent orders</h5>
                        <div class="dropdown default">
                            <a class="btn btn-secondary dropdown-toggle" href="{{ route('admin.orders') }}">
                                <span class="view-all">View all</span>
                            </a>
                        </div>
                    </div>
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-modern" style="table-layout: auto;">
                                <thead class="table-header-modern">
                                    <tr>
                                        <th class="text-start name-column">Customer</th>
                                        <th class="text-center course-column">Course</th>
                                        <th class="text-center date-column">Reservation Date</th>
                                        <th class="text-center items-column">Total Items</th>
                                        <th class="text-center price-column">Total Price</th>
                                        <th class="text-center date-column">Order Date</th>
                                        <th class="text-center action-column">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr class="table-row-modern">
                                            <td class="name-cell">
                                                <div class="customer-info">
                                                    <div class="customer-name">
                                                        {{ $order->user->name ?? '--' }}
                                                        @if (optional($order->user)->utype === 'ADM')
                                                            <span class="badge badge-admin ms-1">Admin</span>
                                                        @endif      
                                                    </div>
                                                    <div class="order-status">
                                                        @if ($order->status == 'pickedup')
                                                            <span class="badge badge-picked-up">Picked Up</span>
                                                        @elseif($order->status == 'canceled')
                                                            <span class="badge badge-canceled">Canceled</span>
                                                        @else
                                                            <span class="badge badge-reserved">Reserved</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center course-cell">
                                                <span class="course-text">{{ $order->user->course ?? '--' }}</span>
                                            </td>
                                            <td class="text-center date-cell">
                                                <span class="reservation-date">{{ $order->reservation_date ?? '--'}}</span>
                                            </td>
                                            <td class="text-center items-cell">
                                                <span class="items-count">{{ $order->orderItems->count() }}</span>
                                            </td>
                                            <td class="text-center price-cell">
                                                <span class="total-price">₱{{ number_format($order->total ?? 0, 2) }}</span>
                                            </td>
                                            <td class="text-center date-cell">
                                                <div class="order-date-info">
                                                    <div class="order-date">{{ $order->created_at->format('M j, Y') }}</div>
                                                    <div class="order-time">{{ $order->created_at->format('g:i A') }}</div>
                                                </div>
                                            </td>
                                            <td class="text-center action-cell">
                                                <a href="{{ route('admin.order.details', ['order_id' => $order->id]) }}" class="action-btn">
                                                    <div class="list-icon-function view-icon">
                                                        <div class="item eye">
                                                            <i class="icon-eye"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>
@endsection

@push('styles')
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 20px;
            text-align: left;
        }

        .table td {
            white-space: nowrap;
        }

        .row-container {
            display: flex;
            flex-direction: row;
            gap: 20px;
            /* Adjust gap as needed */
            flex-wrap: wrap;
            /* Allows wrapping if items don't fit in one row */
        }

        .wg-chart-default {
            flex: 1;
            /* Distributes available space equally */
            min-width: 200px;
            /* Ensures a minimum width for each item */
        }

        .container {
            max-width: 100%;
        }

        .table {
            font-size: 1rem;
            table-layout: auto;
        }

        .table th {
            font-weight: bold;
            text-align: center;
        }

        .table thead {
            background-color: #f1f3f5;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            font-size: 0.9rem;
        }

        .text-muted {
            font-size: 0.9rem;
            text-decoration: none;
        }

        .text-muted:hover {
            text-decoration: underline;
        }

        table {
            table-layout: auto;
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            word-wrap: break-word;
            white-space: normal;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .badge {
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            color: #fff;
            font-weight: bold;
            font-size: 0.75em;
            display: inline-block;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .empty-state {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .empty-state span {
            color: #6c757d;
            font-size: 1.1rem;
        }

        /* Modern Table Styles for Recent Orders */
        .table-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table-header-modern {
            background: #f8f9fa;
            color: #333;
        }

        .table-header-modern th {
            font-weight: 600;
            font-size: 1.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 18px 16px;
            border: none;
            white-space: nowrap;
            color: #333;
        }

        .table-row-modern {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f3f5;
        }

        .table-row-modern:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table-row-modern:last-child {
            border-bottom: none;
        }

        .table-row-modern td {
            padding: 20px 16px;
            border: none;
            vertical-align: middle;
        }

        /* Customer Info Cell */
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .customer-name {
            font-weight: 600;
            font-size: 1.4rem;
            color: #333;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }

        .order-status {
            margin-top: 4px;
        }

        /* Badge Styles */
        .badge-picked-up {
            background: linear-gradient(45deg, #48bb78, #38a169);
            color: white;
            font-size: 1rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-canceled {
            background: linear-gradient(45deg, #f56565, #e53e3e);
            color: white;
            font-size: 1rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-reserved {
            background: linear-gradient(45deg, #ed8936, #dd6b20);
            color: white;
            font-size: 1rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-admin {
            background: #6c757d;
            color: white;
            font-size: 1rem;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        /* Course Cell */
        .course-text {
            color: #333;
            font-weight: 500;
            font-size: 1.3rem;
        }

        /* Date Cells */
        .reservation-date {
            color: #333;
            font-weight: 500;
            font-size: 1.3rem;
        }

        .order-date-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .order-date {
            color: #333;
            font-weight: 500;
            font-size: 1.3rem;
        }

        .order-time {
            color: #666;
            font-size: 1.1rem;
        }

        /* Items and Price Cells */
        .items-count {
            background: #6c757d;
            color: white;
            padding: 10px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.3rem;
            min-width: 40px;
            display: inline-block;
        }

        .total-price {
            color: #333;
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(45deg, #48bb78, #38a169);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Column Width Controls */
        .name-column {
            min-width: 200px;
            width: 25%;
        }

        .course-column {
            min-width: 120px;
            width: 15%;
        }

        .date-column {
            min-width: 130px;
            width: 15%;
        }

        .items-column {
            min-width: 100px;
            width: 12%;
        }

        .price-column {
            min-width: 120px;
            width: 15%;
        }

        .action-column {
            min-width: 80px;
            width: 10%;
        }

        /* Action Button Styles */
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .list-icon-function {
            background: #6c757d;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .list-icon-function:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .item.eye {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-eye {
            color: white;
            font-size: 20px;
            width: 22px;
            height: 22px;
        }

        .icon-eye::before {
            content: "👁";
            display: inline-block;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .table-header-modern th {
                padding: 12px 8px;
                font-size: 1.1rem;
            }

            .table-row-modern td {
                padding: 16px 8px;
            }

            .customer-name {
                font-size: 1.2rem;
            }

            .customer-info {
                gap: 4px;
            }

            .order-date-info {
                gap: 1px;
            }

            .order-date {
                font-size: 1.1rem;
            }

            .order-time {
                font-size: 1rem;
            }

            .course-text,
            .reservation-date {
                font-size: 1.1rem;
            }

            .total-price {
                font-size: 1.3rem;
            }

            .items-count {
                padding: 8px 12px;
                font-size: 1.1rem;
            }

            .badge-picked-up,
            .badge-canceled,
            .badge-reserved {
                font-size: 0.9rem;
                padding: 5px 10px;
            }

            .badge-admin {
                font-size: 0.9rem;
                padding: 3px 8px;
            }
        }

        @media (max-width: 576px) {
            .name-column,
            .course-column,
            .date-column,
            .items-column,
            .price-column,
            .action-column {
                min-width: auto;
                width: auto;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .customer-name {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
        }

        /* Enhanced Hover Effects */
        .table-row-modern:hover .customer-name {
            color: #333;
        }

        .table-row-modern:hover .total-price {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }

        .table-row-modern:hover .items-count {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }
    </style>
@endpush

@push('scripts')
    <script>
        class DashboardManager {
            constructor() {
                this.chart = null;
                this.currentView = 'monthly';
                this.currentFilters = {
                    year: document.getElementById('year-select').value,
                    month: new Date().getMonth() + 1,
                    week: 1
                };

                this.init();
            }

            init() {
                this.bindEvents();
                this.loadAvailableMonths();
                this.loadDashboardData();
            }

            bindEvents() {
                // View switcher
                document.querySelectorAll('[data-view]').forEach(item => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.switchView(e.target.closest('[data-view]').dataset.view);
                    });
                });

                // Filter changes
                document.getElementById('year-select').addEventListener('change', () => {
                    this.currentFilters.year = document.getElementById('year-select').value;
                    this.loadDashboardData();
                });

                document.getElementById('month-select').addEventListener('change', () => {
                    this.currentFilters.month = document.getElementById('month-select').value;
                    if (this.currentView === 'weekly') {
                        this.loadDashboardData();
                    } else if (this.currentView === 'daily') {
                        this.loadAvailableWeeks();
                    }
                });

                document.getElementById('week-select').addEventListener('change', () => {
                    this.currentFilters.week = document.getElementById('week-select').value;
                    this.loadDashboardData();
                });
            }

            switchView(view) {
                this.currentView = view;
                this.updateUI();
                this.loadDashboardData();
            }

            updateUI() {
                // Update title
                const titles = {
                    'monthly': 'Monthly Earnings Revenue',
                    'weekly': 'Weekly Earnings Revenue',
                    'daily': 'Daily Earnings Revenue'
                };
                document.getElementById('chart-title').textContent = titles[this.currentView];

                // Show/hide filter controls
                const monthSelect = document.getElementById('month-select');
                const weekSelect = document.getElementById('week-select');

                if (this.currentView === 'monthly') {
                    monthSelect.style.display = 'none';
                    weekSelect.style.display = 'none';
                } else if (this.currentView === 'weekly') {
                    monthSelect.style.display = 'block';
                    weekSelect.style.display = 'none';
                } else if (this.currentView === 'daily') {
                    monthSelect.style.display = 'block';
                    weekSelect.style.display = 'block';
                    this.loadAvailableWeeks();
                }
            }

            showLoading(show = true) {
                document.getElementById('loading-spinner').style.display = show ? 'block' : 'none';
            }

            async loadAvailableMonths() {
                try {
                    const response = await fetch('/admin/api/months');
                    const data = await response.json();

                    const monthSelect = document.getElementById('month-select');
                    monthSelect.innerHTML = '<option value="">Select Month</option>';

                    data.months.forEach(month => {
                        const option = document.createElement('option');
                        option.value = month.id;
                        option.textContent = month.name;
                        if (month.id == this.currentFilters.month) {
                            option.selected = true;
                        }
                        monthSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error loading months:', error);
                }
            }

            async loadAvailableWeeks() {
                try {
                    const response = await fetch(
                        `/admin/api/weeks?year=${this.currentFilters.year}&month=${this.currentFilters.month}`);
                    const data = await response.json();

                    const weekSelect = document.getElementById('week-select');
                    weekSelect.innerHTML = '<option value="">Select Week</option>';

                    data.weeks.forEach(week => {
                        const option = document.createElement('option');
                        option.value = week.number;
                        option.textContent = week.label;
                        if (week.number == this.currentFilters.week) {
                            option.selected = true;
                        }
                        weekSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error loading weeks:', error);
                }
            }

            async loadDashboardData() {
                this.showLoading(true);

                try {
                    const params = new URLSearchParams({
                        view: this.currentView,
                        year: this.currentFilters.year,
                        ...(this.currentView !== 'monthly' && {
                            month: this.currentFilters.month
                        }),
                        ...(this.currentView === 'daily' && {
                            week: this.currentFilters.week
                        })
                    });

                    const response = await fetch(`/admin/api/dashboard-data?${params}`);
                    const data = await response.json();

                    this.updateSummaryCards(data.totals);
                    this.updateChart(data.chartData);

                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                } finally {
                    this.showLoading(false);
                }
            }
                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-PH', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount || 0);
                }

                updateSummaryCards(totals) {
                    document.getElementById('total-amount').textContent = 
                        `₱${this.formatCurrency(totals.total)}`;
                    document.getElementById('reserved-amount').textContent =
                        `₱${this.formatCurrency(totals.reserved)}`;
                    document.getElementById('pickedup-amount').textContent =
                        `₱${this.formatCurrency(totals.pickedUp)}`;
                    document.getElementById('canceled-amount').textContent =
                        `₱${this.formatCurrency(totals.canceled)}`;
                }

            updateChart(chartData) {
                const options = {
                    series: chartData.series,
                    chart: {
                        type: 'bar',
                        height: 325,
                        toolbar: {
                            show: false,
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '60%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        labels: {
                            style: {
                                colors: '#212529',
                            },
                        },
                        categories: chartData.categories,
                    },
                    yaxis: {
                        show: false,
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "₱" + val;
                            }
                        }
                    },
                    colors: ['#2E93fA', '#66DA26', '#546E7A', '#E91E63']
                };

                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = new ApexCharts(document.querySelector("#dashboard-chart"), options);
                this.chart.render();
            }
        }

        // Initialize dashboard when page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.dashboardManager = new DashboardManager();
        });
    </script>
@endpush
