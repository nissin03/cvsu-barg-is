@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0 fs-2">Billing Statement</h4>
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
                              <a href="{{ route('admin.report-statements') }}">
                                <div class="text-tiny">Statements</div>
                              </a>
                            </li>
                            <li>
                              <i class="icon-chevron-right"></i>
                            </li>
                            <li>
                              <div class="text-tiny">Billing Statement</div>
                            </li>
                          </ul>

                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Print Statement
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Order Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center fs-5">Order No</th>
                                            <th class="text-center fs-5">Name</th>
                                            <th class="text-center fs-5">Email</th>
                                            <th class="text-center fs-5">Phone</th>
                                            <th class="text-center fs-5">Reservation Date</th>
                                            <th class="text-center fs-5">Status</th>
                                            <th class="text-center fs-5">Order Date</th>
                                            <th class="text-center fs-5">Total Items</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center fs-5">{{ $order->id }}</td>
                                            <td class="text-center fs-5">{{ $order->user->name }}</td>
                                            <td class="text-center fs-5">{{ $order->user->email }}</td>
                                            <td class="text-center fs-5">{{ $order->user->phone_number }}</td>
                                            <td class="text-center fs-5">{{ $order->reservation_date }}</td>
                                            <td class="text-center">
                                                @if ($order->status == 'pickedup')
                                                    <span class="badge bg-success fs-5">Picked Up</span>
                                                @elseif($order->status == 'canceled')
                                                    <span class="badge bg-danger fs-5">Canceled</span>
                                                @else
                                                    <span class="badge bg-warning fs-5">Reserved</span>
                                                @endif
                                            </td>
                                            <td class="text-center fs-5">{{ $order->created_at->format('F d, Y') }}</td>
                                            <td class="text-center fs-5">{{ $order->orderItems->count() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center fs-5">Product Name</th>
                                            <th class="text-center fs-5">Quantity</th>
                                            <th class="text-center fs-5">Price</th>
                                            <th class="text-center fs-5">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->orderItems as $item)
                                            <tr>
                                                <td class="text-center fs-5">{{ $item->product->name }}</td>
                                                <td class="text-center fs-5">{{ $item->quantity }}</td>
                                                <td class="text-center fs-5">${{ number_format($item->price, 2) }}</td>
                                                <td class="text-center fs-5">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-end">
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                {{-- <tr>
                                                    <td class="text-end fs-5"><strong>Subtotal:</strong></td>
                                                    <td class="text-end fs-5">${{ number_format($order->subtotal, 2) }}</td>
                                                </tr> --}}
                                                <tr>
                                                    <td class="text-end fs-4"><strong>Total:</strong></td>
                                                    <td class="text-end fs-4">${{ number_format($order->total, 2) }}</td>
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

    /* Status badge styles */
    .badge.bg-success {
        background-color: #198754 !important;
    }

    .badge.bg-danger {
        background-color: #dc3545 !important;
    }

    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000;
    }
</style>
@endpush