@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0 fs-2">Payment Details</h4>
                        <ul class="breadcrumbs d-flex align-items-center gap-2">
                            <li>
                              <a href="{{ route('admin.index') }}">
                                <div class="text-tiny">
                                  <i class="bi bi-house-door me-1"></i>Dashboard
                                </div>
                              </a>
                            </li>
                            <li>
                              <i class="icon-chevron-right"></i>
                            </li>
                            <li>
                              <a href="{{ route('admin.sales-report') }}">
                                <div class="text-tiny">Sales Report</div>
                              </a>
                            </li>
                            <li>
                              <i class="icon-chevron-right"></i>
                            </li>
                            <li>
                              <div class="text-tiny">Payment Details</div>
                            </li>
                          </ul>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Print Payment Details
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Payment Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center fs-5">Payment ID</th>
                                            <th class="text-center fs-5">Facility Name</th>
                                            <th class="text-center fs-5">Quantity</th>
                                            <th class="text-center fs-5">Total Price</th>
                                            <th class="text-center fs-5">Payment Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paymentDetails as $detail)
                                            <tr>
                                                <td class="text-center fs-5">{{ $detail->payment_id }}</td>
                                                <td class="text-center fs-5">{{ $detail->facility->name }}</td>
                                                <td class="text-center fs-5">{{ $detail->quantity }}</td>
                                                <td class="text-center fs-5">${{ number_format($detail->total_price, 2) }}</td>
                                                <td class="text-center fs-5">{{ $detail->created_at->format('F d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Total Summary -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-end">
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="text-end fs-4"><strong>Total Payment:</strong></td>
                                                    <td class="text-end fs-4">${{ number_format($totalPayment, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .card {
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
        background-color: #f8f9fa;
    }

    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }

    /* Print styles */
    @media print {
        .btn-print {
            display: none;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            border: 1px solid #dee2e6;
        }

        .table td, .table th {
            border: 1px solid #dee2e6 !important;
        }
    }

    /* Custom styles for better spacing */
    .table-responsive {
        margin: 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .fs-5 {
        font-size: 1.1rem !important;
    }
</style>
@endpush
