@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h1 class="h3 mb-1 text-dark fw-semibold">Product Reports</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small text-muted">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Product Reports</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2">
                <form action="{{ route('admin.report-product.download') }}" method="GET" target="_blank">
                    <button type="submit" class="btn btn-outline-dark fs-5 py-3 px-4" style="border-radius: 8px;">
                        <i class="bi bi-file-pdf me-2"></i>PRINT
                    </button>
                </form>
            </div>
        </div>

        <!-- PRODUCT REPORT CARD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up text-primary fs-2 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-semibold text-gray-800">Product Reports Overview</h6>
                        <small class="text-muted">Most & least purchased products</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">

                <!-- MOST ORDERED PRODUCTS -->
                <div class="mb-5">
                    <h6 class="fw-semibold mb-4 text-gray-800 fs-4 d-flex align-items-center">
                        <i class="bi bi-arrow-up-circle text-success me-2"></i>
                        Most Ordered Products
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Product Name</th>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mostFrequentLabels as $index => $label)
                                    <tr class="border-bottom">
                                        <td class="py-4 px-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-gray-100 text-gray-800 rounded-pill fs-6 px-3 py-2">
                                                    #{{ $index + 1 }}
                                                </span>
                                                <span class="fw-semibold text-gray-800 fs-5">{{ $label }}</span>
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 text-center">
                                            <span class="fw-bold fs-5 text-gray-800">
                                                {{ number_format($mostFrequentData[$index]) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="my-5">

                <!-- LEAST ORDERED PRODUCTS -->
                <div>
                    <h6 class="fw-semibold mb-4 text-gray-800 fs-4 d-flex align-items-center">
                        <i class="bi bi-arrow-down-circle text-danger me-2"></i>
                        Least Ordered Products
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Product Name</th>
                                    <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Total Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leastBoughtLabels as $index => $label)
                                    <tr class="border-bottom">
                                        <td class="py-4 px-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-gray-100 text-gray-800 rounded-pill fs-6 px-3 py-2">
                                                    #{{ $index + 1 }}
                                                </span>
                                                <span class="fw-semibold text-gray-800 fs-5">{{ $label }}</span>
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 text-center">
                                            <span class="fw-bold fs-5 text-gray-800">
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

        /* Typography Utilities */
        .text-gray-700 {
            color: var(--bs-gray-700) !important;
        }

        .text-gray-800 {
            color: var(--bs-gray-800) !important;
        }

        .text-gray-900 {
            color: var(--bs-gray-900) !important;
        }

        .bg-gray-100 {
            background-color: var(--bs-gray-100) !important;
        }

        /* Card */
        .card {
            border-radius: 12px !important;
            transition: all 0.25s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.08);
        }

        /* Tables */
        .table th {
            font-size: 1.75rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            font-size: 1.25rem;
        }

        .badge {
            font-size: .95rem;
            font-weight: 600;
            border-radius: 6px;
        }

        /* RESPONSIVE TABLE BREAKPOINTS (MATCHING YOUR FORMAT) */

        @media (max-width: 1200px) {
            .table {
                min-width: 900px;
            }
        }

        @media (max-width: 992px) {
            .table {
                min-width: 850px;
            }
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .table {
                min-width: 780px;
            }

            .table td {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                min-width: 720px;
            }

            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header .d-flex>div:first-child {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 400px) {
            .table {
                min-width: 650px;
            }

            .table> :not(caption)>*>* {
                padding: .75rem 1rem;
            }
        }
    </style>
@endpush
