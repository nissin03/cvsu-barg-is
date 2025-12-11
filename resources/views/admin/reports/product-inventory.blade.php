@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h1 class="h3 mb-1 text-dark fw-semibold">Inventory Report</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small text-muted">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-semibold text-gray-800">Filter Inventory</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('admin.report-inventory') }}" id="filterForm" class="filter-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label for="stock_status" class="form-label text-gray-700 fw-medium mb-2">Status</label>
                            <select name="stock_status" id="stock_status"
                                class="form-select form-control-lg border-gray-300" style="border-radius: 8px;">
                                <option value="">All Items</option>
                                <option value="instock" {{ request('stock_status') == 'instock' ? 'selected' : '' }}>In
                                    Stock</option>
                                <option value="outofstock" {{ request('stock_status') == 'outofstock' ? 'selected' : '' }}>
                                    Out of Stock</option>
                                <option value="reorder" {{ request('stock_status') == 'reorder' ? 'selected' : '' }}>Reorder
                                    Level</option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                            <a href="{{ route('admin.report-inventory') }}"
                                class="btn btn-outline-secondary btn-lg px-4 fw-medium" style="border-radius: 8px;">
                                <i class="fas fa-refresh me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        {{-- Inventory Table Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0 fw-semibold text-gray-800">Inventory Report Overview</h6>
                            <small class="text-muted">Product categories and current stock status</small>
                        </div>
                    </div>

                    {{-- Print Form (kept backend logic intact) --}}
                    <form action="{{ route('admin.report-inventory.pdf') }}" method="GET" target="_blank">
                        <input type="hidden" name="stock_status" id="download_stock_status"
                            value="{{ request('stock_status') }}">
                        <button type="submit" class="btn btn-outline-dark fs-5 py-3 px-4 w-auto"
                            style="border-radius: 8px;">
                            <i class="fas fa-file-pdf me-1"></i>PRINT
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                @if ($products->isEmpty())
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center mb-3"
                                style="width: 60px; height: 60px;">
                                <i class="fas fa-box-open text-gray-400" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-gray-600 mb-1" style="font-size: 1.5rem;">No Products Found</h6>
                            <p class="text-gray-500 mb-3 small" style="font-size: 1.25rem;">
                                There are no products matching your current filter criteria.
                            </p>
                            <a href="{{ route('admin.report-inventory') }}" class="btn btn-dark btn-lg px-4"
                                style="border-radius: 8px;">
                                <i class="fas fa-refresh me-2"></i>Reset Filters
                            </a>
                        </div>
                    </div>
                @else
                    <div class="px-4 pt-3 pb-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="small text-gray-600 me-2">Stock:</span>

                            <span class="badge badge-instock">In Stock</span>
                            <span class="badge badge-reorder">Reorder Level</span>
                            <span class="badge badge-outofstock">Out of Stock</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">
                                        Product Name</th>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">
                                        Category</th>
                                    {{-- <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Price</th> --}}
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">
                                        Stock Status</th>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">
                                        Current Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    @php
                                        $currentStock = $product->attributeValues->isNotEmpty()
                                            ? $product->attributeValues->sum('quantity')
                                            : $product->current_stock;
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="py-4 px-4 text-center" data-label="Product Name">
                                            <span class="product-name">{{ $product->name }}</span>
                                        </td>
                                        <td class="py-4 px-4 text-center" data-label="Category">
                                            <span class="category-name text-muted">
                                                {{ $product->category->name }}
                                            </span>
                                        </td>
                                        {{-- 
                                        <td class="py-4 px-4 text-center" data-label="Price">
                                            <div class="price-info">
                                                @if ($product->attributeValues->isNotEmpty())
                                                    ₱{{ number_format($product->attributeValues->first()->price, 2) }}
                                                @else
                                                    ₱{{ number_format($product->price, 2) }}
                                                @endif
                                            </div>
                                        </td>
                                        --}}
                                        <td class="py-4 px-4 text-center" data-label="Stock Status">
                                            @if ($currentStock <= $product->outofstock_quantity)
                                                <span class="badge badge-outofstock">
                                                    Out of Stock
                                                </span>
                                            @elseif ($currentStock <= $product->reorder_quantity)
                                                <span class="badge badge-reorder">
                                                    Reorder Level
                                                </span>
                                            @else
                                                <span class="badge badge-instock">
                                                    In Stock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center" data-label="Current Stock">
                                            <span class="stock-quantity">
                                                {{ number_format($currentStock) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($products->count() > 0)
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="text-muted">
                                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                                    {{ $products->total() }} entries
                                </div>
                                <div>
                                    {{ $products->appends(request()->input())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
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
            font-weight: 500;
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

        /* Inventory-specific typography */
        .product-name {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--bs-gray-900);
        }

        .category-name {
            font-size: 1.1rem;
        }

        .stock-quantity {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--bs-gray-800);
        }

        /* Badges for stock statuses */
        .badge {
            font-size: 1rem;
            padding: 0.4em 0.8em;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 110px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .badge-instock {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }

        .badge-reorder {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
        }

        .badge-outofstock {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        @media (max-width: 1200px) {
            .table-responsive {
                overflow-x: auto;
                position: relative;
                z-index: 0;
            }

            .table {
                min-width: 900px;
                position: relative;
                z-index: 0;
            }
        }

        @media (max-width: 992px) {
            .table {
                min-width: 800px;
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
                min-width: 700px;
                position: relative;
                z-index: 0;
            }

            .table td {
                font-size: 1.25rem;
            }

            .product-name {
                font-size: 1.25rem;
            }

            .stock-quantity {
                font-size: 1.25rem;
            }

            .badge {
                font-size: 0.875rem;
                min-width: 90px;
                padding: 0.3em 0.6em;
            }
        }

        @media (max-width: 576px) {
            .table {
                min-width: 650px;
            }

            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header .d-flex>div:first-child {
                margin-bottom: 1rem;
            }

            .card-header form {
                align-self: flex-start;
                width: 100%;
                margin-top: 0.5rem;
            }

            .filter-form .row {
                flex-direction: column;
            }

            .filter-form .col-lg-4 {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 400px) {
            .table {
                min-width: 600px;
            }

            .table> :not(caption)>*>* {
                padding: 0.75rem 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stockStatus = document.getElementById('stock_status');
            if (stockStatus) {
                stockStatus.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            }

            const downloadStockStatus = document.getElementById('download_stock_status');
            if (downloadStockStatus) {
                downloadStockStatus.value = stockStatus.value;
            }
        });
    </script>
@endpush
