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

        /* Qualification Section */
        .qualification-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
            margin: 15px 0;
        }

        .qualification-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .qualification-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .qualification-approved {
            background-color: #28a745;
            color: #fff;
        }

        .qualification-rejected {
            background-color: #dc3545;
            color: #fff;
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

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        $breadcrumbs[] = ['url' => null, 'label' => 'Reservation Details'];
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Reservation Details"
        :breadcrumbs="$breadcrumbs" />

    <main class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>

        <section class="my-account container">
            <h2 class="page-title">Reservation Details</h2>

            <div class="row">
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <div class="col-lg-10">
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
                            @php
                                $groupedDates = [];
                                if ($payment->grouped_availabilities && $payment->grouped_availabilities->isNotEmpty()) {
                                    $sortedAvailabilities = $payment->grouped_availabilities->sortBy('date_from');
                                    $currentGroup = [];
                                    
                                    foreach ($sortedAvailabilities as $avail) {
                                        if (empty($currentGroup)) {
                                            $currentGroup = [
                                                'start' => $avail->date_from,
                                                'end' => $avail->date_to
                                            ];
                                        } elseif (Carbon\Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                                            $currentGroup['end'] = $avail->date_to;
                                        } else {
                                            $groupedDates[] = $currentGroup;
                                            $currentGroup = [
                                                'start' => $avail->date_from,
                                                'end' => $avail->date_to
                                            ];
                                        }
                                    }
                                    
                                    if (!empty($currentGroup)) {
                                        $groupedDates[] = $currentGroup;
                                    }
                                } else if ($payment->availability) {
                                    $groupedDates[] = [
                                        'start' => $payment->availability->date_from,
                                        'end' => $payment->availability->date_to
                                    ];
                                }
                            @endphp
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><span class="date-label">Reservation Period:</span></p>
                                    <div class="date-ranges">
                                        @if(!empty($payment->grouped_dates))
                                            @foreach($payment->grouped_dates as $range)
                                                <div class="date-range-item" style="margin-bottom: 5px;">
                                                    @if($range['start'] === $range['end'])
                                                        {{ \Carbon\Carbon::parse($range['start'])->format('M j, Y') }}
                                                    @else
                                                        @php
                                                            $startDate = \Carbon\Carbon::parse($range['start']);
                                                            $endDate = \Carbon\Carbon::parse($range['end']);
                                                        @endphp
                                                        @if($startDate->format('M Y') === $endDate->format('M Y'))
                                                            {{ $startDate->format('M j') }} - {{ $endDate->format('j, Y') }}
                                                        @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                            {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                                                        @else
                                                            {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No dates available</span>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Display time information from grouped availabilities --}}
                                @if (!empty($payment->grouped_dates))
                                    @php
                                        // Get time from first availability group
                                        $firstRange = $payment->grouped_dates[0];
                                        $hasTime = !empty($firstRange['time_start']) && !empty($firstRange['time_end']);
                                    @endphp
                                    
                                    @if ($hasTime)
                                        <div class="col-md-6">
                                            <p><span class="date-label">Time:</span> 
                                                <span class="date-value">
                                                    {{ \Carbon\Carbon::parse($firstRange['time_start'])->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($firstRange['time_end'])->format('h:i A') }}
                                                </span>
                                            </p>
                                            
                                            {{-- Show if multiple time periods exist --}}
                                            @if (count($payment->grouped_dates) > 1)
                                                @foreach(array_slice($payment->grouped_dates, 1) as $index => $range)
                                                    @if (!empty($range['time_start']) && !empty($range['time_end']))
                                                        <p><span class="date-label">Time {{ $index + 2 }}:</span> 
                                                            <span class="date-value">
                                                                {{ \Carbon\Carbon::parse($range['time_start'])->format('h:i A') }} - 
                                                                {{ \Carbon\Carbon::parse($range['time_end'])->format('h:i A') }}
                                                            </span>
                                                        </p>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                @elseif ($payment->availability && $payment->availability->time_start && $payment->availability->time_end)
                                    {{-- Fallback to single availability time --}}
                                    <div class="col-md-6">
                                        <p><span class="date-label">Time:</span> 
                                            <span class="date-value">
                                                {{ \Carbon\Carbon::parse($payment->availability->time_start)->format('h:i A') }} - 
                                                {{ \Carbon\Carbon::parse($payment->availability->time_end)->format('h:i A') }}
                                            </span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            
                            @if ($days > 0)
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><span class="date-label">Total Duration:</span> 
                                            <span class="date-value">{{ $days }} day(s)</span>
                                        </p>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="text-end">
                                <a class="btn btn-custom btn-outline-primary" href="{{ route('user.reservations') }}">Back to Reservations</a>
                            </div>

                    <!-- Facility Information -->
                    <div class="wg-box">
                        <h5>Facility Information</h5>
                        <div class="facility-info">
                            {{-- @if ($payment->availability->facility->image)
                                <img src="{{ asset('uploads/facilities/' . $payment->availability->facility->image) }}"
                                    alt="{{ $payment->availability->facility->name }}">
                            @else
                                <img src="{{ asset('images/default-facility.jpg') }}"
                                    alt="Default Facility Image">
                            @endif --}}
                            <div class="facility-details">
                                <h6>{{ $payment->availability->facility->name }}</h6>
                                <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $payment->availability->facility->facility_type)) }}</p>
                                <p><strong>Description:</strong> {{ Str::limit($payment->availability->facility->description, 200) }}</p>
                                @if ($payment->availability->facilityAttribute)
                                    <p><strong>Room/Area:</strong> {{ $payment->availability->facilityAttribute->room_name }}</p>
                                    @if ($payment->availability->facilityAttribute->capacity)
                                        <p><strong>Capacity:</strong> {{ $payment->availability->facilityAttribute->capacity }} person(s)</p>
                                    @endif
                                    @if ($payment->availability->facilityAttribute->sex_restriction)
                                        <p><strong>Restriction:</strong> {{ ucfirst($payment->availability->facilityAttribute->sex_restriction) }} only</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details -->
<div class="wg-box">
    <h5>Transaction Details</h5>
    <table class="table-custom">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price Type</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $processedItems = [];
            @endphp
            
            @foreach ($payment->transactionReservations as $transaction)
                @php
                    $itemKey = ($transaction->price->id ?? 'facility-booking') . '-' . $transaction->quantity;
                    
                    if (in_array($itemKey, $processedItems)) {
                        continue;
                    }
                    
                    $processedItems[] = $itemKey;
                @endphp
                
                <tr>
                    <td>
                        <div class="facility-details">
                            <p>{{ $transaction->price->name ?? 'Facility Booking' }}</p>
                        </div>
                    </td>
                    <td>{{ ucfirst($transaction->price->price_type ?? 'N/A') }}</td>
                    <td>&#8369;{{ number_format($transaction->price->value ?? 0, 2) }}</td>
                    <td>{{ $transaction->quantity }}</td>
                    <td>&#8369;{{ number_format($payment->total_price ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total Amount:</th>
                <th>&#8369;{{ number_format($payment->total_price ?? 0) }}</th>
            </tr>
        </tfoot>
    </table>
</div>

                    <!-- Qualification Status -->
                    @if ($qualificationApproval)
                        <div class="wg-box">
                            <h5>Qualification Status</h5>
                            <div class="qualification-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> 
                                            <span class="qualification-status qualification-{{ $qualificationApproval->status }}">
                                                {{ ucfirst($qualificationApproval->status) }}
                                            </span>
                                        </p>
                                        <p><strong>Submitted:</strong> {{ $qualificationApproval->created_at->format('M d, Y H:i') }}</p>
                                        @if ($qualificationApproval->updated_at != $qualificationApproval->created_at)
                                            <p><strong>Last Updated:</strong> {{ $qualificationApproval->updated_at->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if ($qualificationApproval->qualification)
                                            <p><strong>Document:</strong> 
                                                <a href="{{ asset('storage/' . $qualificationApproval->qualification) }}" 
                                                   target="_blank" class="btn btn-custom btn-outline-primary">
                                                    View Document
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Summary -->
                    <div class="wg-box">
                        <h5>Payment Summary</h5>
                        <table class="table-custom">
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    @if ($payment->status == 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($payment->status == 'canceled')
                                        <span class="badge badge-danger">Canceled</span>
                                    @elseif($payment->status == 'reserved')
                                        <span class="badge badge-info">Reserved</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Amount</th>
                                <td>&#8369;{{ number_format($payment->total_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Payment Date</th>
                                <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        $(function() {
            console.log('Reservation Details page loaded');
        });
    </script>
@endpush
