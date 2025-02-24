@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap p-4">
        <!-- Header Section -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-2 fs-1">Product Reports</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted fs-5">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <span class="text-muted fs-5">Product Reports</span>
                        </li>
                    </ol>
                </nav>
            </div>
            <form action="{{ route('admin.report-product.download') }}" method="GET">
                <button type="submit" class="btn btn-danger d-flex align-items-center fs-5">
                    <i class="bi bi-file-pdf me-2"></i>
                    Download PDF
                </button>
            </form>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up text-primary fs-3 me-2"></i>
                    <h5 class="fw-bold m-0 fs-3">Product Reports Overview</h5>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Most Frequent Products Table -->
                <div class="mb-5">
                    <h6 class="fw-bold mb-3 text-dark fs-4">
                        <i class="bi bi-arrow-up-circle text-success me-2"></i>
                        Most Ordered Products
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4 border-0 fs-5">Product Name</th>
                                    <th class="py-3 px-4 border-0 text-center fs-5">Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mostFrequentLabels as $index => $label)
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark rounded-pill me-2 fs-5">
                                                    #{{ $index + 1 }}
                                                </span>
                                                <span class="fw-medium fs-5">{{ $label }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 fs-5">
                                                {{ number_format($mostFrequentData[$index]) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Least Bought Products Table -->
                <div>
                    <h6 class="fw-bold mb-3 text-dark fs-4">
                        <i class="bi bi-arrow-down-circle text-danger me-2"></i>
                        Least Ordered Products
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4 border-0 fs-5">Product Name</th>
                                    <th class="py-3 px-4 border-0 text-center fs-5">Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leastBoughtLabels as $index => $label)
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark rounded-pill me-2 fs-5">
                                                    #{{ $index + 1 }}
                                                </span>
                                                <span class="fw-medium fs-5">{{ $label }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 fs-5">
                                                {{ number_format($leastBoughtData[$index]) }}
                                            </span>
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
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* Card Styles */
    .card {
        border-radius: 0.75rem;
        transition: box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    
    /* Table Styles */
    .table {
        margin-bottom: 0;
        font-size: 1.1rem;
    }
    
    .table > :not(caption) > * > * {
        background-color: transparent;
        box-shadow: none !important;
        padding: 1rem 1.25rem;
    }
    
    .table > tbody > tr {
        transition: background-color 0.15s ease-in-out;
    }
    
    .table > tbody > tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Badge Styles */
    .badge {
        font-weight: 500;
        padding: 0.5rem 1rem;
    }
    
    /* Scrollbar Styles */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 #f8f9fa;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background-color: #adb5bd;
    }
    
    /* Button Styles */
    .btn-primary {
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    }
    
    /* Breadcrumb Styles */
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.3rem;
        line-height: 1;
        color: #6c757d;
    }
</style>
@endpush