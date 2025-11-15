@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h1 class="h3 mb-1 text-dark fw-semibold">Product List</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small text-muted">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Product List</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-semibold text-gray-800">Filter Products</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('admin.report.product-list') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-6 col-md-8">
                            <label for="category" class="form-label text-gray-700 fw-medium mb-2">Filter by Category</label>
                            <select class="form-control form-control-lg border-gray-300" id="category" name="category"
                                style="border-radius: 8px;" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if (request('category'))
                            <div class="col-lg-6 col-md-4 d-flex align-items-end">
                                <a href="{{ route('admin.report.product-list') }}"
                                    class="btn btn-outline-secondary btn-lg px-4 fw-medium" style="border-radius: 8px;">
                                    <i class="fas fa-refresh me-2"></i>Clear Filter
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="mb-0 fw-semibold text-gray-800">Product List</h5>
                            <small class="text-muted">All products with pricing and stock information</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.report.product-list.download') }}" method="POST" target="_blank">
                                @csrf
                                <input type="hidden" name="category" value="{{ request('category') }}">
                                <button type="submit" class="btn btn-outline-dark fs-5 py-3 px-4 w-auto"
                                    style="border-radius: 8px;">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Product Information</th>
                                {{-- <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Stock Quantity</th> --}}
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                {{-- Display base product row if it has no variants OR if it has a base price --}}
                                @if ($product->attributeValues->count() == 0 || $product->price)
                                    <tr class="border-bottom">
                                        <td class="py-4 px-4" data-label="Product">
                                            <div class="product-info">
                                                <div class="d-flex align-items-start">
                                                    @php
                                                        $imagePath = null;
                                                        if ($product->image) {
                                                            if (str_contains($product->image, 'uploads/products/')) {
                                                                $imagePath = $product->image;
                                                            } else {
                                                                $possiblePaths = [
                                                                    'uploads/products/' . $product->image,
                                                                    'uploads/products/thumbnails/' . $product->image,
                                                                    'uploads/products/' . basename($product->image),
                                                                    'uploads/products/thumbnails/' .
                                                                    basename($product->image),
                                                                ];
                                                                foreach ($possiblePaths as $path) {
                                                                    if (file_exists(public_path($path))) {
                                                                        $imagePath = $path;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    @if ($imagePath && file_exists(public_path($imagePath)))
                                                        <img src="{{ asset($imagePath) }}" alt="{{ $product->name }}"
                                                            class="rounded me-3"
                                                            style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                            style="width: 60px; height: 60px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <span class="product-name">{{ $product->name }}</span>
                                                        <div class="category-info mt-1">
                                                            <small class="text-muted">
                                                                Category: {{ $product->category->name }}
                                                            </small>
                                                        </div>
                                                        @if ($product->attributeValues->count() > 0)
                                                            <small class="text-info">
                                                                <i class="fas fa-tag me-1"></i>Base Product
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        {{-- <td class="py-4 px-4 text-center" data-label="Stock">
                                            <div class="stock-info">
                                                <div class="fw-bold text-gray-800" style="font-size: 1.5rem;">
                                                    {{ $product->quantity }}
                                                </div>
                                                <div class="stock-details mt-2">
                                                    @if ($product->reorder_quantity > 0)
                                                        <small class="text-warning d-block">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            Reorder: {{ $product->reorder_quantity }}
                                                        </small>
                                                    @endif
                                                    @if ($product->outofstock_quantity > 0)
                                                        <small class="text-danger d-block">
                                                            <i class="fas fa-times-circle me-1"></i>
                                                            Out of Stock: {{ $product->outofstock_quantity }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td> --}}
                                        <td class="py-4 px-4 text-center" data-label="Price">
                                            <div class="price-info">
                                                @if ($product->price)
                                                    <span class="base-price fw-bold text-success"
                                                        style="font-size: 1.5rem;">
                                                        ₱{{ number_format($product->price, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Display each attribute value as a separate row --}}
                                @foreach ($product->attributeValues as $attributeValue)
                                    <tr class="border-bottom variant-row">
                                        <td class="py-4 px-4" data-label="Variant">
                                            <div class="product-info">
                                                <div class="d-flex align-items-start">
                                                    {{-- Use product image or variant-specific image if available --}}
                                                    @php
                                                        $imagePath = null;
                                                        if ($product->image) {
                                                            if (str_contains($product->image, 'uploads/products/')) {
                                                                $imagePath = $product->image;
                                                            } else {
                                                                $possiblePaths = [
                                                                    'uploads/products/' . $product->image,
                                                                    'uploads/products/thumbnails/' . $product->image,
                                                                    'uploads/products/' . basename($product->image),
                                                                    'uploads/products/thumbnails/' .
                                                                    basename($product->image),
                                                                ];
                                                                foreach ($possiblePaths as $path) {
                                                                    if (file_exists(public_path($path))) {
                                                                        $imagePath = $path;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    @if ($imagePath && file_exists(public_path($imagePath)))
                                                        <img src="{{ asset($imagePath) }}" alt="{{ $product->name }}"
                                                            class="rounded me-3"
                                                            style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                            style="width: 60px; height: 60px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <span class="product-name me-2">{{ $product->name }}</span>
                                                            <small class="text-muted">-</small>
                                                            <span class="variant-value ms-2 fw-semibold text-gray-700">
                                                                {{ $attributeValue->value }}
                                                            </span>
                                                        </div>
                                                        <div class="category-info mt-1">
                                                            <small class="text-muted">
                                                                Category: {{ $product->category->name }}
                                                            </small>
                                                        </div>
                                                        @if ($attributeValue->description)
                                                            <small class="text-muted d-block mt-1">
                                                                {{ $attributeValue->description }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        {{-- <td class="py-4 px-4 text-center" data-label="Stock">
                                            <div class="stock-info">
                                                <div class="fw-bold text-gray-800" style="font-size: 1.5rem;">
                                                    {{ $attributeValue->quantity }}
                                                </div>
                                                <div class="stock-details mt-2">
                                                    @if ($attributeValue->stock_status === 'reorder')
                                                        <small class="text-warning d-block">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            Low Stock
                                                        </small>
                                                    @elseif($attributeValue->stock_status === 'outofstock')
                                                        <small class="text-danger d-block">
                                                            <i class="fas fa-times-circle me-1"></i>
                                                            Out of Stock
                                                        </small>
                                                    @else
                                                        <small class="text-success d-block">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            In Stock
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td> --}}
                                        <td class="py-4 px-4 text-center" data-label="Price">
                                            <div class="price-info">
                                                @if ($attributeValue->price)
                                                    <span class="base-price fw-bold text-success"
                                                        style="font-size: 1.5rem;">
                                                        ₱{{ number_format($attributeValue->price, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                style="width: 60px; height: 60px;">
                                                <i class="fas fa-boxes text-gray-400" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <h6 class="text-gray-600 mb-1" style="font-size: 1.5rem;">No Products Found
                                            </h6>
                                            <p class="text-gray-500 mb-0 small" style="font-size: 1.25rem;">No products
                                                match your current filters</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->count() > 0)
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                                {{ $products->total() }} entries
                            </div>
                            <div>
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
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
        }

        .table td {
            vertical-align: middle;
            border-color: #e2e8f0;
            font-size: 1.75rem;
        }

        .product-name {
            font-size: 1.50rem;
            font-weight: 500;
            color: var(--bs-gray-900);
        }

        .variant-row {
            background-color: #fafbfe;
        }

        .variant-row:hover {
            background-color: #f1f5fd !important;
        }

        .variant-value {
            font-size: 1.25rem;
            color: var(--bs-gray-700);
        }

        .product-description {
            font-size: 0.875rem;
            color: var(--bs-gray-500);
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .table td {
                font-size: 0.9rem;
            }

            .product-name {
                font-size: 1rem;
            }

            .variant-value {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush
