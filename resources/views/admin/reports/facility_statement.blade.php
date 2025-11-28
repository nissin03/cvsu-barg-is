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
                        <li class="breadcrumb-item active" aria-current="page">Facilities Sales Report</li>
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
                <form method="GET" action="{{ route('admin.facility-statement') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <label for="date_from" class="form-label text-gray-700 fw-medium mb-2">From Date</label>
                            <input type="date" class="form-control form-control-lg border-gray-300" id="date_from"
                                name="date_from" value="{{ request('date_from') }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="date_to" class="form-label text-gray-700 fw-medium mb-2">To Date</label>
                            <input type="date" class="form-control form-control-lg border-gray-300" id="date_to"
                                name="date_to" value="{{ request('date_to') }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="facility_id" class="form-label text-gray-700 fw-medium mb-2">Facility</label>
                            <select class="form-select form-select-lg border-gray-300" id="facility_id" name="facility_id"
                                style="border-radius: 8px;">
                                <option value="">All Facilities</option>
                                @foreach ($facilities as $facility)
                                    <option value="{{ $facility->id }}"
                                        {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="d-flex flex-column h-100 justify-content-end">
                                <label for="status" class="form-label text-gray-700 fw-medium mb-2">Status</label>
                                <select class="form-select form-select-lg border-gray-300" id="status" name="status"
                                    style="border-radius: 8px;">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>
                                        Reserved</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>
                                        Canceled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-dark btn-lg flex-fill" style="border-radius: 8px;">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.facility-statement') }}"
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
                            <h6 class="mb-0 fw-semibold text-gray-800">Sales Reports</h6>
                            <small class="text-muted">Payment records and transactions</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.facility-statement.download', request()->query()) }} " target="_blank"
                        class="btn btn-outline-dark fs-5 py-3 px-4 w-auto" style="border-radius: 8px;">
                        <i class="fas fa-file-pdf me-1"></i>PRINT
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Name</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Facility</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Status</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Total Amount</th>
                                <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Reservation Dates</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotal = 0;
                            @endphp
                            @forelse($payments as $payment)
                                @php
                                    $grandTotal += $payment->total_price;
                                @endphp
                                <tr class="border-bottom">
                                    <td class="py-4 px-4">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-medium text-gray-900">{{ $payment->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="fw-medium text-gray-800">{{ $payment->availability->facility->name }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $statusClass =
                                                [
                                                    'completed' => 'badge-completed',
                                                    'canceled' => 'badge-canceled',
                                                    'reserved' => 'badge-reserved',
                                                    'pending' => 'badge-pending',
                                                ][$payment->status] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="fw-bold text-success">₱{{ number_format($payment->total_price, 2) }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($payment->date_from && $payment->date_to)
                                            @if ($payment->date_from == $payment->date_to)
                                                <div class="fw-medium text-gray-800">
                                                    {{ \Carbon\Carbon::parse($payment->date_from)->format('M d, Y') }}
                                                </div>
                                            @else
                                                <div class="fw-medium text-gray-800">
                                                    {{ \Carbon\Carbon::parse($payment->date_from)->format('M d') }} -
                                                    {{ \Carbon\Carbon::parse($payment->date_to)->format('M d, Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-muted">No dates</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center mb-3"
                                                style="width: 60px; height: 60px;">
                                                <i class="fas fa-file-alt text-gray-400" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <h6 class="text-gray-600 mb-1">No Records Found</h6>
                                            <p class="text-gray-500 mb-0 small">No billing statements match your current
                                                filters</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($payments->count() > 0)
                            <tfoot>
                                <tr class="bg-gray-50">
                                    <td colspan="4" class="py-3 px-4 text-end fw-semibold text-gray-800">
                                        Grand Total:
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="fw-bold text-success fs-7">₱{{ number_format($grandTotal, 2) }}</div>
                                    </td>
                                    {{-- <td class="py-3 px-4"></td> --}}
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
        .text-gray-900 {
            color: #0f172a !important;
        }

        .text-gray-800 {
            color: #1e293b !important;
        }

        .text-gray-700 {
            color: #334155 !important;
        }

        .text-gray-600 {
            color: #475569 !important;
        }

        .text-gray-500 {
            color: #64748b !important;
        }

        .text-gray-400 {
            color: #94a3b8 !important;
        }

        .bg-gray-50 {
            background-color: #f8fafc !important;
        }

        .bg-gray-100 {
            background-color: #f1f5f9 !important;
        }

        .border-gray-300 {
            border-color: #cbd5e1 !important;
        }

        .card {
            border-radius: 12px !important;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        .form-control,
        .form-select {
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
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
            border-radius: 8px !important;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        .table th {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            border-color: #e2e8f0;
            font-size: 1.5rem;
        }

        .badge {
            font-size: 0.95rem;
            padding: 0.4em 0.8em;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 85px;
            text-align: center;
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

        .badge-pending {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
        }

        .badge-secondary {
            background: linear-gradient(45deg, #6b7280, #4b5563);
            color: white;
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
            }
        }

        @media print {
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .btn {
                display: none !important;
            }
        }
    </style>
@endpush
