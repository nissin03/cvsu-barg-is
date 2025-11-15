@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-2 display-5">Product Reports</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted fs-4">
                                    <i class="bi bi-house-door me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="text-muted fs-4">Product Reports</span>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.report-product.download') }}" method="GET" target="_blank">
                        <button type="submit" class="btn btn-dark d-flex align-items-center fs-4 px-4 py-2">
                            <i class="bi bi-file-pdf me-2"></i>
                            PDF
                        </button>
                    </form>

                    {{-- <form action="{{ route('admin.report-product.print') }}" method="GET" target="_blank">
                        <button type="submit" class="btn btn-success d-flex align-items-center fs-4 px-4 py-2">
                            <i class="bi bi-printer me-2"></i>
                            Print
                        </button>
                    </form> --}}


                </div>


            </div>

            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-graph-up text-primary fs-2 me-3"></i>
                        <h5 class="fw-bold m-0 display-6">Product Reports Overview</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="mb-5">
                        <h6 class="fw-bold mb-4 text-dark fs-3">
                            <i class="bi bi-arrow-up-circle text-success me-2"></i>
                            Most Ordered Products
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3 px-4 border-0 fs-4">Product Name</th>
                                        <th class="py-3 px-4 border-0 text-center fs-4">Total Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mostFrequentLabels as $index => $label)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-light text-dark rounded-pill me-3 fs-3 px-3 py-1">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                    <span class="fw-medium fs-3">{{ $label }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-4 py-1 fs-3">
                                                    {{ number_format($mostFrequentData[$index]) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr class="my-5 border-2">

                    <div>
                        <h6 class="fw-bold mb-4 text-dark fs-3">
                            <i class="bi bi-arrow-down-circle text-danger me-2"></i>
                            Least Ordered Products
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3 px-4 border-0 fs-4">Product Name</th>
                                        <th class="py-3 px-4 border-0 text-center fs-4">Total Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leastBoughtLabels as $index => $label)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-light text-dark rounded-pill me-3 fs-3 px-3 py-1">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                    <span class="fw-medium fs-3">{{ $label }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span class=" px-4 py-1 fs-3">
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
    <style>
        .card {
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 1rem 1.5rem rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-3px);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            padding: 0 0.5rem;
            color: #6c757d;
            font-size: 1.5rem;
            line-height: 1;
        }

        .table {
            font-size: 1.2rem;
        }

        .table> :not(caption)>*>* {
            padding: 1.25rem 1.5rem;
        }

        .table-hover>tbody>tr:hover {
            background-color: rgba(248, 249, 250, 0.8);
        }

        .badge {
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            font-size: 1em;
        }

        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #ced4da;
            border-radius: 6px;
        }

        .btn {
            padding: 0.75rem 1.75rem;
            font-weight: 600;
        }

        .bi {
            font-size: 1.3em;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mb-5 {
            margin-bottom: 3rem !important;
        }

        .py-3 {
            padding-top: 1.25rem !important;
            padding-bottom: 1.25rem !important;
        }
    </style>
@endpush
