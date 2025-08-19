@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="d-flex align-items-center justify-content-between mb-4">
            {{-- <h2 class="fw-bold text-dark mb-0">Reports</h2> --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{route('admin.index')}}" class="text-muted text-decoration-none">
                            <small>Dashboard</small>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <small class="text-muted">Inventory Report</small>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="bg-white rounded-3 shadow-sm border-0 overflow-hidden">
            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-0 m-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-light bg-gradient border-bottom p-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 class="mb-0 fw-semibold text-dark">Inventory Report</h4>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <form action="{{ route('admin.report-inventory') }}" method="GET" id="filterForm" class="d-flex flex-wrap align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <label for="stock_status" class="form-label mb-0 fw-semibold text-dark fs-4">Status:</label>
                                <select name="stock_status" id="stock_status" class="form-select fs-5 py-3 px-4 border-light-subtle rounded" style="width: 140px;">
                                    <option value="">All Items</option>
                                    <option value="instock" {{ request('stock_status') == 'instock' ? 'selected' : '' }}>In Stock</option>
                                    <option value="outofstock" {{ request('stock_status') == 'outofstock' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="reorder" {{ request('stock_status') == 'reorder' ? 'selected' : '' }}>Reorder Level</option>
                                </select>
                            </div>
                
                            <button type="submit" class="btn btn-dark btn-lg flex-fill" style="min-width: 120px;">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </form>
                
                        <form action="{{ route('admin.report-inventory.pdf') }}" method="GET" class="d-flex align-items-center">
                            <input type="hidden" name="start_date" id="download_start_date" value="{{ old('start_date', request('start_date')) }}">
                            <input type="hidden" name="end_date" id="download_end_date" value="{{ old('end_date', request('end_date')) }}">
                            <input type="hidden" name="today" value="{{ request('today') ? '1' : '' }}">
                            <input type="hidden" name="stock_status" id="download_stock_status" value="{{ request('stock_status') }}">
                
                            <button type="submit" class="btn btn-outline-dark btn-lg flex-fill" style="min-width: 120px;">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="wg-table table-all-user p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-lg" style="table-layout: auto;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center fw-semibold text-dark py-4 border-0 fs-5">Product Name</th>
                                <th class="text-center fw-semibold text-dark py-4 border-0 fs-5">Category</th>
                                <th class="text-center fw-semibold text-dark py-4 border-0 fs-5">Price</th>
                                <th class="text-center fw-semibold text-dark py-4 border-0 fs-5">Stock Status</th>
                                <th class="text-center fw-semibold text-dark py-4 border-0 fs-5">Current Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr class="border-bottom">
                                <td class="text-center py-4 text-dark fs-4 fw-medium">{{ $product->name }}</td>
                                <td class="text-center py-4 text-muted fs-4">{{ $product->category->name }}</td>
                                <td class="text-center py-4 fw-semibold text-dark fs-4">
                                    @if($product->attributeValues->isNotEmpty())
                                        ₱{{ number_format($product->attributeValues->first()->price, 2) }}
                                    @else
                                        ₱{{ number_format($product->price, 2) }}
                                    @endif
                                </td>
                                <td class="text-center py-4">
                                    @php
                                        $currentStock = $product->attributeValues->isNotEmpty()
                                            ? $product->attributeValues->sum('quantity')
                                            : $product->current_stock;
                                    @endphp
                                    @if($currentStock <= $product->outofstock_quantity)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-4 py-3 rounded-pill fs-5">Out of Stock</span>
                                    @elseif($currentStock <= $product->reorder_quantity)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-4 py-3 rounded-pill fs-5">Reorder Level</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-4 py-3 rounded-pill fs-5">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-center py-4 fw-bold text-dark fs-4">{{ number_format($currentStock) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-top bg-light bg-gradient p-3">
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $products->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 20px;
        text-align: left;
        vertical-align: middle;
    }
    
    .table td {
        white-space: nowrap;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
        transition: background-color 0.15s ease-in-out;
    }
    
    .badge {
        font-weight: 500;
        font-size: 0.9rem;
        letter-spacing: 0.025em;
    }
    
    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .text-warning {
        color: #fd7e14 !important;
    }
    
    .text-success {
        color: #198754 !important;
    }
    
    .border-danger-subtle {
        border-color: rgba(220, 53, 69, 0.2) !important;
    }
    
    .border-warning-subtle {
        border-color: rgba(255, 193, 7, 0.2) !important;
    }
    
    .border-success-subtle {
        border-color: rgba(25, 135, 84, 0.2) !important;
    }
    
    .btn {
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    }
    
    .form-select {
        transition: all 0.15s ease-in-out;
    }
    
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6c757d;
    }
    
    .checkbox-container {
        display: inline-block;
        position: relative;
        padding-left: 0;
        cursor: pointer;
        user-select: none;
    }
    
    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    
    .checkmark {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 38px;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 6px;
        color: #495057;
        transition: all 0.15s ease-in-out;
    }
    
    .checkbox-container input:checked ~ .checkmark {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }
    
    .readonly-input {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        cursor: not-allowed;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .rounded-3 {
        border-radius: 0.5rem !important;
    }
    
    .bg-gradient {
        background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0)) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const todayCheckbox = document.getElementById('today');
        const startDateContainer = document.getElementById('start_date_container');
        const endDateContainer = document.getElementById('end_date_container');
        const downloadStartDate = document.getElementById('download_start_date');
        const downloadEndDate = document.getElementById('download_end_date');
        const stockStatusSelect = document.getElementById('stock_status');
        const downloadStockStatus = document.getElementById('download_stock_status');
        
        function updateDateInputs() {
            if (todayCheckbox && todayCheckbox.checked) {
                if (startDateContainer) {
                    startDateContainer.innerHTML = '<input type="text" name="start_date_display" value="Today" class="form-control readonly-input" readonly>';
                }
                if (endDateContainer) {
                    endDateContainer.innerHTML = '<input type="text" name="end_date_display" value="Today" class="form-control readonly-input" readonly>';
                }
                const today = new Date().toISOString().split('T')[0];
                if (downloadStartDate) downloadStartDate.value = today;
                if (downloadEndDate) downloadEndDate.value = today;
            } else {
                const startDateValue = '{{ old('start_date', request('start_date')) }}';
                const endDateValue = '{{ old('end_date', request('end_date')) }}';
                if (startDateContainer) {
                    startDateContainer.innerHTML = '<input type="date" name="start_date" id="start_date" value="' + startDateValue + '" class="form-control">';
                }
                if (endDateContainer) {
                    endDateContainer.innerHTML = '<input type="date" name="end_date" id="end_date" value="' + endDateValue + '" class="form-control">';
                }
                if (downloadStartDate) downloadStartDate.value = startDateValue;
                if (downloadEndDate) downloadEndDate.value = endDateValue;
            }
        }
        
        if (todayCheckbox) {
            todayCheckbox.addEventListener('change', function () {
                updateDateInputs();
            });
        }
        
        if (stockStatusSelect && downloadStockStatus) {
            stockStatusSelect.addEventListener('change', function () {
                downloadStockStatus.value = this.value;
            });
            downloadStockStatus.value = stockStatusSelect.value;
        }
        
        updateDateInputs();
    });
</script>
@endpush