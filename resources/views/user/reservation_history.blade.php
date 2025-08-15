@extends('layouts.app')

@section('content')
    <style>
        .pt-90 {
            padding-top: 90px !important;
        }

        .my-account .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 13px;
        }

        .my-account .wg-box {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Reservation Summary Styles */
        .reservation-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .reservation-summary .left-section,
        .reservation-summary .right-section {
            width: 48%;
        }

        .reservation-summary .reservation-status {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .reservation-summary .reservation-info {
            font-size: 0.9rem;
            color: #555;
        }

        /* Table Styles */
        .table-custom {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            border: 1px solid #eaeaea;
            text-align: left;
            vertical-align: middle;
        }

        .table-custom th {
            background-color: #f5f5f5;
            font-weight: 600;
        }

        .facility-info {
            display: flex;
            align-items: center;
        }

        .facility-info img {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            object-fit: cover;
        }

        .facility-details {
            font-size: 0.9rem;
            color: #333;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            color: #fff;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        /* Buttons */
        .btn-custom {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-outline-primary {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .text-end {
            text-align: right;
        }

        /* Date Range Display */
        .date-range {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            margin: 10px 0;
        }

        .date-range .date-label {
            font-weight: 600;
            color: #007bff;
        }

        .date-range .date-value {
            color: #333;
        }

        /* History Filter */
        .history-filter {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .filter-info {
            font-size: 0.9rem;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .reservation-summary .left-section,
            .reservation-summary .right-section {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Reservation History"
        :breadcrumbs="$breadcrumbs" />

    <main class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>

        <section class="my-account container">
            <h2 class="page-title">Reservation History</h2>

            <div class="row">
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <div class="col-lg-10">
                    <!-- History Filter Info -->
                    <div class="history-filter">
                        <div class="filter-info">
                            <i class="fa fa-info-circle me-2"></i>
                            Showing reservation history from the last 30 days. 
                            This includes completed, canceled, and reserved facility bookings.
                        </div>
                    </div>

                    @forelse ($payments as $payment)
                        <div class="wg-box">
                            <div class="reservation-summary">
                                <!-- Left Section -->
                                <div class="left-section">
                                    <div class="reservation-status">
                                        @if ($payment->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($payment->status == 'canceled')
                                            <span class="badge badge-danger">Canceled</span>
                                        @elseif($payment->status == 'reserved')
                                            <span class="badge badge-info">Reserved</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </div>
                                    <div class="reservation-info">
                                        <p><strong>Reservation No:</strong> {{ $payment->id }}</p>
                                        <p><strong>Reservation Date:</strong> {{ $payment->created_at->format('M d, Y H:i') }}</p>
                                        <p><strong>Last Updated:</strong> {{ $payment->updated_at->format('M d, Y H:i') }}</p>
                                        @if ($payment->updated_by)
                                            <p><strong>Updated By:</strong> {{ $payment->updatedBy->name }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Section -->
                                <div class="right-section">
                                    <div class="reservation-info">
                                        <p><strong>Facility:</strong> {{ $payment->availability->facility->name }}</p>
                                        <p><strong>Total Price:</strong> &#8369;{{ number_format($payment->total_price, 2) }}</p>
                                        <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
                                        @if ($payment->availability->facilityAttribute)
                                            <p><strong>Room/Area:</strong> {{ $payment->availability->facilityAttribute->room_name ?? 'N/A' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Date Range Information -->
                            <div class="date-range">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><span class="date-label">Date From:</span> 
                                            <span class="date-value">{{ \Carbon\Carbon::parse($payment->availability->date_from)->format('M d, Y') }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><span class="date-label">Date To:</span> 
                                            <span class="date-value">{{ \Carbon\Carbon::parse($payment->availability->date_to)->format('M d, Y') }}</span>
                                        </p>
                                    </div>
                                </div>
                                @if ($payment->availability->time_start && $payment->availability->time_end)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><span class="date-label">Time Start:</span> 
                                                <span class="date-value">{{ \Carbon\Carbon::parse($payment->availability->time_start)->format('h:i A') }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><span class="date-label">Time End:</span> 
                                                <span class="date-value">{{ \Carbon\Carbon::parse($payment->availability->time_end)->format('h:i A') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-end">
                                <a class="btn btn-custom btn-outline-primary" href="{{ route('user.reservation_details', ['payment_id' => $payment->id]) }}">View Details</a>
                            </div>
                        </div>
                    @empty
                        <div class="wg-box">
                            <div class="text-center">
                                <h4>No Reservation History Found</h4>
                                <p class="text-muted">You haven't completed any facility reservations in the last 30 days.</p>
                                <a href="{{ route('user.facilities.index') }}" class="btn btn-custom btn-outline-primary">Browse Facilities</a>
                            </div>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if ($payments->hasPages())
                        <div class="wg-box">
                            <div class="d-flex justify-content-center">
                                {{ $payments->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        $(function() {
            console.log('Reservation History page loaded');
        });
    </script>
@endpush
