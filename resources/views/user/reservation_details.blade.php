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
            border-left: 4px solid #007bff;
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
            background-color: #007bff;
            color: #fff;
        }

        .qualification-rejected {
            background-color: #dc3545;
            color: #fff;
        }

        .discount-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }

        .discount-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .discount-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .discount-approved {
            background-color: #28a745;
            color: #fff;
        }

        .discount-rejected {
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

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Reservation Details" :breadcrumbs="$breadcrumbs" />

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
                            {{-- <div class="left-section">
                                <div class="reservation-status">
                                    Status:
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
                            </div> --}}
                        </div>

                        <!-- Date Range Information -->
                        <div class="date-range">
                            @php
                                // Get earliest date_from and latest date_to using pluck
                                $dateFrom = null;
                                $dateTo = null;

                                if (
                                    $payment->grouped_availabilities &&
                                    $payment->grouped_availabilities->isNotEmpty()
                                ) {
                                    $dateFrom = $payment->grouped_availabilities
                                        ->pluck('date_from')
                                        ->filter()
                                        ->sort()
                                        ->first();

                                    $dateTo = $payment->grouped_availabilities
                                        ->pluck('date_to')
                                        ->filter()
                                        ->sort()
                                        ->last();
                                } elseif ($payment->availability) {
                                    $dateFrom = $payment->availability->date_from;
                                    $dateTo = $payment->availability->date_to;
                                }
                            @endphp

                            <div class="row">
                                <div class="col-md-6">
                                    <p><span class="date-label">Reservation Period:</span></p>
                                    <div class="date-ranges">
                                        @if ($dateFrom && $dateTo)
                                            <div class="date-range-item" style="margin-bottom: 5px;">
                                                @if ($dateFrom === $dateTo)
                                                    {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }}
                                                @else
                                                    @php
                                                        $startDate = \Carbon\Carbon::parse($dateFrom);
                                                        $endDate = \Carbon\Carbon::parse($dateTo);
                                                    @endphp
                                                    @if ($startDate->format('M Y') === $endDate->format('M Y'))
                                                        {{ $startDate->format('M j') }} -
                                                        {{ $endDate->format('j, Y') }}
                                                    @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                        {{ $startDate->format('M j') }} -
                                                        {{ $endDate->format('M j, Y') }}
                                                    @else
                                                        {{ $startDate->format('M j, Y') }} -
                                                        {{ $endDate->format('M j, Y') }}
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">No dates available</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-end">
                                        <a class="btn btn-custom btn-outline-primary"
                                            href="{{ route('user.reservations') }}">Back
                                            to Reservations</a>
                                    </div>
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
                                                {{ \Carbon\Carbon::parse($firstRange['time_start'])->format('h:i A') }}
                                                -
                                                {{ \Carbon\Carbon::parse($firstRange['time_end'])->format('h:i A') }}
                                            </span>
                                        </p>
                                        @if (count($payment->grouped_dates) > 1)
                                            @foreach (array_slice($payment->grouped_dates, 1) as $index => $range)
                                                @if (!empty($range['time_start']) && !empty($range['time_end']))
                                                    <p><span class="date-label">Time {{ $index + 2 }}:</span>
                                                        <span class="date-value">
                                                            {{ \Carbon\Carbon::parse($range['time_start'])->format('h:i A') }}
                                                            -
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
                                            {{ \Carbon\Carbon::parse($payment->availability->time_start)->format('h:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($payment->availability->time_end)->format('h:i A') }}
                                        </span>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Facility Information -->
                        <div class="wg-box">
                            <h5>Facility Information</h5>
                            <div class="facility-info">
                                <div class="facility-details">
                                    <h6>{{ $payment->availability->facility->name }}</h6>
                                    <p><strong>Type:</strong>
                                        {{ ucfirst(str_replace('_', ' ', $payment->availability->facility->facility_type)) }}
                                    </p>
                                    <p><strong>Description:</strong>
                                        {{ Str::limit($payment->availability->facility->description, 200) }}</p>
                                    @if ($payment->availability->facilityAttribute)
                                        <p><strong>Room/Area:</strong>
                                            {{ $payment->availability->facilityAttribute->room_name }}</p>
                                        @if ($payment->availability->facilityAttribute->capacity)
                                            <p><strong>Capacity:</strong>
                                                {{ $payment->availability->facilityAttribute->capacity }} person(s)</p>
                                        @endif
                                        @if ($payment->availability->facilityAttribute->sex_restriction)
                                            <p><strong>Restriction:</strong>
                                                {{ ucfirst($payment->availability->facilityAttribute->sex_restriction) }}
                                                only</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Qualification Status -->
                        @if ($qualificationApproval)
                            <div class="wg-box">
                                <h5>Qualification Status</h5>
                                <div class="qualification-section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong>
                                                <span
                                                    class="qualification-status qualification-{{ $qualificationApproval->status }}">
                                                    {{ ucfirst($qualificationApproval->status) }}
                                                </span>
                                            </p>
                                            <p><strong>Submitted:</strong>
                                                {{ $qualificationApproval->created_at->format('M d, Y H:i') }}</p>
                                            @if ($qualificationApproval->updated_at != $qualificationApproval->created_at)
                                                <p><strong>Last Updated:</strong>
                                                    {{ $qualificationApproval->updated_at->format('M d, Y H:i') }}</p>
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

                        @php
                            $nonRefundableAddons = collect();

                            // Filter non-refundable addon transactions
                            $nonRefundableTransactions = $payment->addonTransactions->filter(function ($addonTrx) {
                                return $addonTrx->addon &&
                                    $addonTrx->addon->is_refundable == false &&
                                    $addonTrx->addonReservation;
                            });

                            // Get unique addon IDs
                            $uniqueAddonIds = $nonRefundableTransactions->pluck('addon.id')->unique();

                            // Build display data for each unique addon
                            foreach ($uniqueAddonIds as $addonId) {
                                $addonTransactions = $nonRefundableTransactions->where('addon.id', $addonId);

                                // Count occurrences
                                $addonCount = $addonTransactions->count();

                                // Get first transaction for basic info
                                $firstTrx = $addonTransactions->first();

                                // Get date range (earliest date_from and latest date_to)
                                $dateFrom = $addonTransactions
                                    ->pluck('addonReservation.date_from')
                                    ->filter()
                                    ->sort()
                                    ->first();

                                $dateTo = $addonTransactions
                                    ->pluck('addonReservation.date_to')
                                    ->filter()
                                    ->sort()
                                    ->last();

                                $nonRefundableAddons->push([
                                    'name' => $firstTrx->addon->name,
                                    'base_price' => $firstTrx->addon->base_price,
                                    'billing_cycle' => $firstTrx->addon->billing_cycle,
                                    'date_from' => $dateFrom,
                                    'date_to' => $dateTo,
                                    'quantity' => $firstTrx->addonReservation->quantity,
                                    'days' => $firstTrx->addonReservation->days,
                                    'count' => $addonCount,
                                ]);
                            }
                        @endphp

                        @if ($nonRefundableAddons->count() > 0)
                            <div class="wg-box">
                                <h5>Add-ons</h5>
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>Add-on Name</th>
                                            <th>Price</th>
                                            <th>Billing Cycle</th>
                                            <th>Date From</th>
                                            <th>Date To</th>
                                            <th>Quantity</th>
                                            <th>Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($nonRefundableAddons as $addon)
                                            <tr>
                                                <td>
                                                    {{ $addon['name'] }}
                                                </td>
                                                <td>&#8369;{{ number_format($addon['base_price'], 2) }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $addon['billing_cycle'])) }}</td>
                                                <td>{{ $addon['date_from'] ? \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td>{{ $addon['date_to'] ? \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') : 'N/A' }}
                                                </td>
                                                <td>{{ $addon['quantity'] ?? 'N/A' }}</td>
                                                <td>{{ $addon['days'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif


                        <!-- Payment Summary -->
                        <div class="wg-box">
                            <h5>Payment Summary</h5>
                            <table class="table-custom">
                                <tbody>
                                    {{-- Reservation Price --}}
                                    <tr>
                                        <td><strong>Reservation Price</strong></td>
                                        <td style="text-align: right;">
                                            <strong>&#8369;{{ number_format($payment->total_price, 2) }}</strong>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Payment Status</strong></td>
                                        <td style="text-align: right;">
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

                                    {{-- Refundable Add-Ons Section --}}
                                    @php
                                        $refundableAddons = collect();
                                        $refundableTotal = 0;
                                        $hasDownpayment = false;
                                        $downpaymentAmount = 0;

                                        // Collect refundable addons from addon_transactions
                                        if ($payment->addonTransactions && $payment->addonTransactions->count() > 0) {
                                            foreach ($payment->addonTransactions as $addonTrx) {
                                                if ($addonTrx->addon && $addonTrx->addon->is_refundable == true) {
                                                    $addonBasePrice = $addonTrx->addon->base_price;

                                                    $refundableAddons->push([
                                                        'name' => $addonTrx->addon->name,
                                                        'base_price' => $addonBasePrice,
                                                        'status' => $addonTrx->status,
                                                    ]);

                                                    $refundableTotal += $addonBasePrice;

                                                    // Check for downpayment from addon_payment
                                                    if (
                                                        $addonTrx->addonPayment &&
                                                        $addonTrx->addonPayment->downpayment_amount > 0
                                                    ) {
                                                        $hasDownpayment = true;
                                                        $downpaymentAmount +=
                                                            $addonTrx->addonPayment->downpayment_amount;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp

                                    @if ($refundableAddons->count() > 0)
                                        <tr>
                                            <td colspan="2" style="padding-top: 20px;"><strong>Refundables</strong>
                                            </td>
                                        </tr>

                                        @foreach ($refundableAddons as $addon)
                                            <tr>
                                                <td style="padding-left: 30px;">
                                                    {{ $addon['name'] }}
                                                </td>
                                                <td style="text-align: right;">
                                                    &#8369;{{ number_format($addon['base_price'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Refundable Status --}}
                                        <tr>
                                            <td style="padding-left: 30px;"><strong>Refundable Status</strong></td>
                                            <td style="text-align: right;">
                                                @if ($refundableAddons->first()['status'] == 'paid')
                                                    <span class="badge badge-success">Paid</span>
                                                @elseif($refundableAddons->first()['status'] == 'refunded')
                                                    <span class="badge badge-info">Refunded</span>
                                                @elseif($refundableAddons->first()['status'] == 'forfeit')
                                                    <span class="badge badge-danger">Forfeit</span>
                                                @else
                                                    <span class="badge badge-warning">Unpaid</span>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Downpayment if exists --}}
                                        @if ($hasDownpayment)
                                            <tr>
                                                <td style="padding-left: 30px;"><strong>Downpayment</strong></td>
                                                <td style="text-align: right;">
                                                    &#8369;{{ number_format($downpaymentAmount, 2) }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Refundable Total --}}
                                        <tr style="background-color: #f5f5f5;">
                                            <td><strong>Refundable Total</strong></td>
                                            <td style="text-align: right;">
                                                <strong>&#8369;{{ number_format($refundableTotal, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- Total Amount --}}
                                    <tr style="background-color: #e3f2fd; border-left: 4px solid #2196F3;">
                                        <td style="font-size: 1.1rem;"><strong>Total Amount</strong></td>
                                        <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: #2196F3;">
                                            <strong>&#8369;{{ number_format($payment->total_price + $refundableTotal, 2) }}</strong>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Payment Date</strong></td>
                                        <td style="text-align: right;">{{ $payment->created_at->format('M d, Y H:i') }}
                                        </td>
                                    </tr>

                                    @if ($payment->discount_proof_path)
                                        <tr>
                                            <td><strong>Discount Proof</strong></td>
                                            <td style="text-align: right;">
                                                <a href="{{ asset('storage/' . $payment->discount_proof_path) }}"
                                                    target="_blank" class="btn btn-custom btn-outline-primary btn-sm">
                                                    View Document
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
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
