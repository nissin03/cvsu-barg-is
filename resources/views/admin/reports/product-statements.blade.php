@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h1 class="h3 mb-1 text-dark fw-semibold">Sales Report</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small text-muted">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Sales Reports</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-semibold text-gray-800">Filter Options</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('admin.report-statements') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <label for="start_date" class="form-label text-gray-700 fw-medium mb-2">From Date</label>
                            <input type="date" class="form-control form-control-lg border-gray-300" id="start_date"
                                name="start_date" value="{{ request('start_date') }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="end_date" class="form-label text-gray-700 fw-medium mb-2">To Date</label>
                            <input type="date" class="form-control form-control-lg border-gray-300" id="end_date"
                                name="end_date" value="{{ request('end_date') }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="category" class="form-label text-gray-700 fw-medium mb-2">Category</label>
                            <select class="form-control form-control-lg border-gray-300" id="category" name="category"
                                style="border-radius: 8px;">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="status" class="form-label text-gray-700 fw-medium mb-2">Status</label>
                            <select class="form-control form-control-lg border-gray-300" id="status" name="status"
                                style="border-radius: 8px;">
                                <option value="">All Status</option>
                                <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved
                                </option>
                                <option value="pickedup" {{ request('status') == 'pickedup' ? 'selected' : '' }}>Picked Up
                                </option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-dark btn-lg flex-fill" style="border-radius: 8px;">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.report-statements') }}"
                                class="btn btn-outline-secondary btn-lg px-4 fw-medium" style="border-radius: 8px;">
                                <i class="fas fa-refresh me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0 fw-semibold text-gray-800">Sales Report</h6>
                            <small class="text-muted">Order items and transactions</small>
                        </div>
                    </div>
                    <form action="{{ route('admin.report-statements.download') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        <button type="submit" class="btn btn-outline-dark fs-5 py-3 px-4 w-auto"
                            style="border-radius: 8px;">
                            <i class="fas fa-file-pdf me-1"></i>PRINT
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Name</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Product/Variant</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Quantity</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Amount</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Status</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                @foreach ($order->orderItems as $item)
                                    <tr class="border-bottom">
                                        <td class="py-4 px-4" data-label="Customer">
                                            <div class="customer-info">
                                                <span class="customer-name">{{ $order->user->name }}</span>
                                                <span class="customer-email">{{ $order->user->email }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4" data-label="Product">
                                            <div class="product-info">
                                                <span class="product-name">{{ $item->product->name }}</span>
                                                @if ($item->variant)
                                                    <span class="variant-info">Variant: {{ $item->variant->value }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center quantity-cell" data-label="Quantity">
                                            <div class="fw-bold text-gray-800" style="font-size: 1.5rem;">
                                                {{ $item->quantity }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center amount-cell" data-label="Amount">
                                            <div class="amount-details">
                                                <span class="total-amount">
                                                    ₱{{ number_format($item->price * $item->quantity, 2) }}</span>
                                                <span class="unit-price">
                                                    ₱{{ number_format($item->price, 2) }} each</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center status-cell" data-label="Status">
                                            @php
                                                $statusColors = [
                                                    'reserved' => 'badge-reserved',
                                                    'pickedup' => 'badge-completed',
                                                    'canceled' => 'badge-canceled',
                                                ];
                                                $badgeClass = $statusColors[$order->status] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center date-cell" data-label="Order Date">
                                            <span class="text-muted" style="font-size: 1.5rem;">
                                                {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}
                                            </span>
                                            {{-- @if ($order->reservation_date)
                                                <br><small class="text-muted">Reservation:
                                                    {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}</small>
                                            @endif --}}
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                style="width: 60px; height: 60px;">
                                                <i class="fas fa-file-alt text-gray-400" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <h6 class="text-gray-600 mb-1" style="font-size: 1.5rem;">No Records Found
                                            </h6>
                                            <p class="text-gray-500 mb-0 small" style="font-size: 1.25rem;">No billing
                                                statements match your current filters</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($orders->count() > 0)
                            <tfoot>
                                <tr class="border-top">
                                    <td colspan="5" class="py-4 px-4 text-end fw-semibold text-gray-700"
                                        style="font-size: 1.5rem;">
                                        Grand Total:
                                    </td>
                                    <td colspan="1" class="py-4 px-4 text-start fw-bold text-success"
                                        style="font-size: 1.5rem;">
                                        ₱{{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --bs-gray-50: #f8fafc;
            --bs-gray-100: #f1f5f9;
            --bs-gray-300: #cbd5e1;
            --bs-gray-400: #94a3b8;
            --bs-gray-500: #64748b;
            --bs-gray-600: #475569;
            --bs-gray-700: #334155;
            --bs-gray-800: #1e293b;
            --bs-gray-900: #0f172a;
        }

        .text-gray-900 {
            color: var(--bs-gray-900) !important;
        }

        .text-gray-800 {
            color: var(--bs-gray-800) !important;
        }

        .text-gray-700 {
            color: var(--bs-gray-700) !important;
        }

        .text-gray-600 {
            color: var(--bs-gray-600) !important;
        }

        .text-gray-500 {
            color: var(--bs-gray-500) !important;
        }

        .text-gray-400 {
            color: var(--bs-gray-400) !important;
        }

        .bg-gray-50 {
            background-color: var(--bs-gray-50) !important;
        }

        .bg-gray-100 {
            background-color: var(--bs-gray-100) !important;
        }

        .border-gray-300 {
            border-color: var(--bs-gray-300) !important;
        }

        .card {
            border-radius: 12px !important;
            transition: all 0.2s ease-in-out;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        .form-control,
        .form-select {
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease-in-out;
            border: 1px solid var(--bs-gray-300);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .btn {
            font-size: 0.9rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease-in-out;
            border-radius: 8px !important;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
        }

        .table-hover tbody tr:hover {
            background-color: var(--bs-gray-50);
        }

        .table th {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }

        .table td {
            vertical-align: middle;
            border-color: #e2e8f0;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .table-responsive {
            position: relative;
            z-index: 0;
        }

        .table {
            position: relative;
            z-index: 0;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.5rem;
            position: relative;
            z-index: 1;
        }

        .badge {
            font-size: 1rem;
            padding: 0.4em 0.8em;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 85px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .badge-completed {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }

        .badge-canceled {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        .badge-reserved {
            background: linear-gradient(45deg, #3b82f6, #1e40af);
            color: white;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .customer-info {
            min-width: 200px;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .customer-name {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--bs-gray-900);
            display: block;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .customer-email {
            font-size: 1rem;
            color: var(--bs-gray-500);
            display: block;
            word-break: break-all;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            z-index: 1;
        }

        .product-info {
            min-width: 180px;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .product-name {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--bs-gray-800);
            display: block;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .variant-info {
            font-size: 1rem;
            color: var(--bs-gray-500);
            display: block;
            position: relative;
            z-index: 1;
        }

        .quantity-cell,
        .amount-cell,
        .status-cell,
        .date-cell {
            white-space: nowrap;
            text-align: center;
            min-width: 120px;
            position: relative;
            z-index: 1;
        }

        .amount-details {
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 600;
            color: #198754;
            display: block;
            position: relative;
            z-index: 1;
        }

        .unit-price {
            font-size: 1rem;
            color: var(--bs-gray-500);
            display: block;
            position: relative;
            z-index: 1;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.5rem;
            position: relative;
            z-index: 1;
        }

        .table tbody tr {
            position: relative;
            z-index: 0;
        }

        @media (max-width: 1200px) {
            .table-responsive {
                overflow-x: auto;
                position: relative;
                z-index: 0;
            }

            .table {
                min-width: 1000px;
                position: relative;
                z-index: 0;
            }

            .customer-info,
            .product-info {
                min-width: 150px;
            }
        }

        @media (max-width: 992px) {
            .table {
                min-width: 900px;
            }

            .customer-info,
            .product-info {
                min-width: 140px;
            }
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .btn-lg {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .table-responsive {
                font-size: 0.875rem;
                overflow-x: auto;
                position: relative;
                z-index: 0;
            }

            .table {
                min-width: 800px;
                position: relative;
                z-index: 0;
            }

            .table td {
                font-size: 1.25rem;
            }

            .customer-name,
            .product-name {
                font-size: 1.25rem;
            }

            .customer-email,
            .variant-info,
            .unit-price {
                font-size: 0.875rem;
            }

            .total-amount {
                font-size: 1.25rem;
            }

            .badge {
                font-size: 0.875rem;
                min-width: 75px;
                padding: 0.3em 0.6em;
            }

            .table-responsive .table tbody tr {
                display: table-row;
                margin-bottom: 0;
                border: none;
            }

            .table-responsive .table tbody td {
                display: table-cell;
                text-align: center !important;
            }

            .table-responsive .table tbody td::before {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .table {
                min-width: 700px;
            }

            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header .d-flex>div:first-child {
                margin-bottom: 1rem;
            }

            .card-header .d-flex form {
                align-self: flex-end;
            }

            .filter-form .row {
                flex-direction: column;
            }

            .filter-form .col-lg-3 {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 400px) {
            .table {
                min-width: 650px;
            }

            .table> :not(caption)>*>* {
                padding: 0.75rem 1rem;
            }

            .customer-info,
            .product-info {
                min-width: 120px;
            }
        }
    </style>
@endpush
