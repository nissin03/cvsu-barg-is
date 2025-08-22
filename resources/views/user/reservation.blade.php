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

        /* Responsive */
        @media (max-width: 768px) {
            .reservation-summary .left-section,
            .reservation-summary .right-section {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        /* Status Timeline */
        .status-timeline {
            position: relative;
            padding-left: 30px;
        }

        .status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }

        .status-item {
            position: relative;
            margin-bottom: 20px;
        }

        .status-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
        }

        .status-item.completed::before {
            background-color: #28a745;
        }

        .status-item.pending::before {
            background-color: #ffc107;
        }

        .status-item.canceled::before {
            background-color: #dc3545;
        }
    </style>
<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Reservation"
:breadcrumbs="$breadcrumbs" />

<main class="container" style="padding-top: 1em;">
<div class="mb-4 pb-4"></div>

<section class="my-account container">
    <h2 class="page-title">Facility Reservations</h2>

    <div class="row">
        <div class="col-lg-2">
            @include('user.account__nav')
        </div>

        <div class="col-lg-10">
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
                            </div>
                        </div>
                    </div>

                    <!-- Updated Date Range Information with Grouping -->
                    <div class="date-range">
                        @php
                            $groupedDates = [];
                    
                            if ($payment->grouped_availabilities && $payment->grouped_availabilities->isNotEmpty()) {
                                $sortedAvailabilities = $payment->grouped_availabilities->sortBy('date_from');
                                $currentGroup = [];
                    
                                foreach ($sortedAvailabilities as $avail) {
                                    if (empty($currentGroup)) {
                                        $currentGroup = [
                                            'start' => $avail->date_from,
                                            'end' => $avail->date_to,
                                            'time_start' => $avail->time_start,
                                            'time_end' => $avail->time_end
                                        ];
                                    } elseif (\Carbon\Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                                        $currentGroup['end'] = $avail->date_to;
                                        // Keep the time from the first availability in the group
                                    } else {
                                        $groupedDates[] = $currentGroup;
                                        $currentGroup = [
                                            'start' => $avail->date_from,
                                            'end' => $avail->date_to,
                                            'time_start' => $avail->time_start,
                                            'time_end' => $avail->time_end
                                        ];
                                    }
                                }
                    
                                if (!empty($currentGroup)) {
                                    $groupedDates[] = $currentGroup;
                                }
                            } else {
                                // fallback: single availability
                                $groupedDates[] = [
                                    'start' => $payment->availability->date_from,
                                    'end' => $payment->availability->date_to,
                                    'time_start' => $payment->availability->time_start,
                                    'time_end' => $payment->availability->time_end
                                ];
                            }
                        @endphp

                        @if(!empty($groupedDates))
                            <div class="date-ranges-container">
                                <h6 class="mb-3">Reservation Schedule:</h6>
                                @foreach($groupedDates as $index => $range)
                                    @php
                                        $startDate = \Carbon\Carbon::parse($range['start']);
                                        $endDate = \Carbon\Carbon::parse($range['end']);
                                    @endphp
                                    
                                    <div class="date-range-item mb-3 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <p class="mb-1">
                                                    <span class="date-label"><strong>Schedule {{ $index + 1 }}:</strong></span>
                                                    <span class="date-value fw-bold text-primary">
                                                        @if ($startDate->equalTo($endDate))
                                                            {{ $startDate->format('M j, Y') }}
                                                        @else
                                                            @if($startDate->format('M Y') === $endDate->format('M Y'))
                                                                {{ $startDate->format('M j') }} - {{ $endDate->format('j, Y') }}
                                                            @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                                {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                                                            @else
                                                                {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                                                            @endif
                                                        @endif
                                                    </span>
                                                </p>
                                            </div>
                                            
                                            @if ($range['time_start'] && $range['time_end'])
                                                <div class="col-md-6">
                                                    <p class="mb-0">
                                                        <span class="date-label">Time Start:</span>
                                                        <span class="date-value">{{ \Carbon\Carbon::parse($range['time_start'])->format('h:i A') }}</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-0">
                                                        <span class="date-label">Time End:</span>
                                                        <span class="date-value">{{ \Carbon\Carbon::parse($range['time_end'])->format('h:i A') }}</span>
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Fallback to original layout if no grouped dates -->
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
                        @endif
                    </div>

                    <div class="text-end">
                        <a class="btn btn-custom btn-outline-primary" href="{{ route('user.reservation_details', ['payment_id' => $payment->id]) }}">View Details</a>
                    </div>
                </div>
            @empty
                <div class="wg-box">
                    <div class="text-center">
                        <h4>No Facility Reservations Found</h4>
                        <p class="text-muted">You haven't made any facility reservations yet.</p>
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

@push('styles')
<style>
.date-ranges-container {
    margin-top: 15px;
}

.date-range-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.date-range-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.date-label {
    color: #6c757d;
    font-weight: 500;
}

.date-value {
    color: #495057;
}

/* Debug info - remove in production */
.debug-info {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 10px;
    font-size: 0.8rem;
}
</style>
@endpush

@push('scripts')
<script>
    $(function() {
        console.log('Facility Reservations page loaded with date grouping');
    });
</script>
@endpush