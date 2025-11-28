@extends('layouts.app')
@section('content')
    <style>
        .summary-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0
        }

        .summary-table th,
        .summary-table td {
            padding: 12px 16px;
            vertical-align: middle
        }

        .summary-table .col-label {
            width: 70%
        }

        .summary-table .col-amount {
            width: 30%;
            text-align: right;
            white-space: nowrap
        }

        .label-ellipsis {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: bottom
        }

        .summary-table .table-active th,
        .summary-table .table-active td {
            background: transparent
        }

        .row-total th,
        .row-total td {
            font-weight: 700;
            border-top: 1px solid #e9ecef
        }
    </style>


    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        if ($currentRoute === 'rentals.checkout') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <link href="{{ asset('css/facility/checkout.css') }}" rel="stylesheet">

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="checkout-container">
        <div class="checkout-wrapper">
            <div class="checkout-card">
                <div class="card-header">
                    <h2>Reservation Summary</h2>
                </div>

                <div class="card-body">
                    <div class="section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Personal Information
                            </h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value">{{ $user->name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value">{{ $user->phone_number }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Reservation Details
                            </h3>
                        </div>
                        <table class="summary-table">
                            <tbody>
                                <tr>
                                    <th>Facility</th>
                                    <td>{{ $facility->name }}</td>
                                </tr>

                                @if ($facility->facility_type === 'individual')
                                    <tr>
                                        <th>Room Name</th>
                                        <td>{{ $reservationData['room_name'] ?? '' }}</td>
                                    </tr>

                                    @php
                                        $dateFrom = !empty($date_from) ? \Carbon\Carbon::parse($date_from) : null;
                                        $dateTo = !empty($date_to) ? \Carbon\Carbon::parse($date_to) : null;

                                        $initialPrice = (float) ($reservationData['initial_price'] ?? 0);
                                        $subtotal = (float) ($reservationData['subtotal'] ?? $initialPrice);
                                        $refTotal = (float) ($reservationData['refundable_total'] ?? 0);
                                        $totalPrice =
                                            (float) ($reservationData['total_price'] ?? $subtotal + $refTotal);

                                        $selectedPriceDetails = $selectedPriceDetails ?? null;
                                    @endphp

                                    @if ($selectedPriceDetails)
                                        <tr>
                                            <th>{{ $selectedPriceDetails['name'] }}</th>
                                            <td>₱{{ number_format((float) $selectedPriceDetails['price'], 2) }}</td>
                                        </tr>
                                    @endif

                                    @if ($dateFrom)
                                        <tr>
                                            <th>Date From</th>
                                            <td>{{ $dateFrom->format('F d, Y') }}</td>
                                        </tr>
                                    @endif

                                    @if ($dateTo)
                                        <tr>
                                            <th>Date To</th>
                                            <td>{{ $dateTo->format('F d, Y') }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <th>Initial Price</th>
                                        <td>₱{{ number_format($initialPrice, 2) }}</td>
                                    </tr>

                                    @if (!empty($reservationData['addon_names']) && count($reservationData['addon_names']) > 0)
                                        @php
                                            $filteredAddons = [];
                                            $filteredAddonTotal = 0;

                                            foreach ($reservationData['addon_names'] as $addonId => $addonName) {
                                                $addonPrice = (float) ($reservationData['addon_values'][$addonId] ?? 0);
                                                $addonType = $reservationData['addon_types'][$addonId] ?? '';
                                                $addonQuantity =
                                                    (int) ($reservationData['addon_quantity'][$addonId] ?? 0);
                                                $addonNights = (int) ($reservationData['addon_nights'][$addonId] ?? 1);
                                                $isQuantityBased =
                                                    (bool) ($reservationData['addon_is_quantity_based'][$addonId] ??
                                                        false);
                                                $addonDateFrom = $reservationData['addon_date_from'][$addonId] ?? null;
                                                $addonDateTo = $reservationData['addon_date_to'][$addonId] ?? null;
                                                $billingCycle =
                                                    $reservationData['addon_billing_cycle'][$addonId] ?? 'per_contract';
                                                $addonCheckbox = $reservationData['addon_checkbox'][$addonId] ?? false;

                                                $addonTotal = 0;

                                                switch ($addonType) {
                                                    case 'per_item':
                                                        if ($billingCycle === 'per_day') {
                                                            $addonTotal = $isQuantityBased
                                                                ? $addonQuantity * $addonPrice * $addonNights
                                                                : $addonPrice * $addonNights;
                                                        } else {
                                                            $addonTotal = $isQuantityBased
                                                                ? $addonQuantity * $addonPrice
                                                                : $addonPrice;
                                                        }
                                                        break;

                                                    case 'flat_rate':
                                                    case 'per_unit':
                                                        if ($billingCycle === 'per_day') {
                                                            $addonTotal = $addonPrice * $addonNights;
                                                            if ($isQuantityBased && $addonQuantity > 0) {
                                                                $addonTotal *= $addonQuantity;
                                                            }
                                                        } else {
                                                            $addonTotal = $addonPrice;
                                                            if ($isQuantityBased && $addonQuantity > 0) {
                                                                $addonTotal *= $addonQuantity;
                                                            }
                                                        }
                                                        break;

                                                    case 'per_night':
                                                        $addonTotal = $addonPrice * $addonNights;
                                                        if ($isQuantityBased && $addonQuantity > 0) {
                                                            $addonTotal *= $addonQuantity;
                                                        }
                                                        break;

                                                    default:
                                                        $addonTotal = $addonPrice;
                                                }

                                                $shouldDisplay = true;
                                                if ($billingCycle === 'per_contract') {
                                                    $shouldDisplay = $isQuantityBased
                                                        ? $addonQuantity > 0
                                                        : !empty($addonCheckbox);
                                                }

                                                if ($addonTotal > 0 && $shouldDisplay) {
                                                    $filteredAddons[$addonId] = [
                                                        'name' => $addonName,
                                                        'total' => $addonTotal,
                                                        'quantity' => $addonQuantity,
                                                        'nights' => $addonNights,
                                                        'type' => $addonType,
                                                        'billing_cycle' => $billingCycle,
                                                        'date_from' => $addonDateFrom,
                                                        'date_to' => $addonDateTo,
                                                        'is_quantity_based' => $isQuantityBased,
                                                    ];
                                                    $filteredAddonTotal += $addonTotal;
                                                }
                                            }
                                        @endphp

                                        @if (count($filteredAddons) > 0)
                                            <tr>
                                                <th colspan="2" class="bg-light"><strong>Add-Ons</strong></th>
                                            </tr>
                                            @foreach ($filteredAddons as $addonId => $addon)
                                                <tr>
                                                    <th>
                                                        {{ $addon['name'] }}
                                                        @if ($addon['quantity'] > 0 && $addon['is_quantity_based'])
                                                            (x{{ $addon['quantity'] }})
                                                        @endif
                                                        @if (
                                                            $addon['nights'] > 1 &&
                                                                in_array($addon['type'], ['per_night', 'flat_rate', 'per_unit']) &&
                                                                $addon['billing_cycle'] === 'per_day')
                                                            ({{ $addon['nights'] }} days)
                                                        @endif
                                                    </th>
                                                    <td>₱{{ number_format($addon['total'], 2) }}</td>
                                                </tr>

                                                @if ($addon['date_from'] && $addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>
                                                                Date Range:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}
                                                            </small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif($addon['date_from'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>Start Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif($addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>End Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            @if ($filteredAddonTotal > 0)
                                                <tr class="row-total">
                                                    <th class="col-label">Add-ons Total</th>
                                                    <td class="col-amount">₱{{ number_format($filteredAddonTotal, 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endif

                                    <tr class="row-total">
                                        <th class="col-label">Subtotal</th>
                                        <td class="col-amount">₱{{ number_format($subtotal, 2) }}</td>
                                    </tr>

                                    @if (!empty($reservationData['refundable_addon_names']) && count($reservationData['refundable_addon_names']) > 0)
                                        <tr>
                                            <th colspan="2" class="bg-light"><strong>Refundable Add-Ons</strong></th>
                                        </tr>
                                        @foreach ($reservationData['refundable_addon_names'] as $addonId => $addonName)
                                            @php
                                                $refundablePrice =
                                                    (float) ($reservationData['refundable_addon_prices'][$addonId] ??
                                                        0);
                                            @endphp
                                            @if ($refundablePrice > 0)
                                                <tr>
                                                    <th>{{ $addonName }}</th>
                                                    <td>₱{{ number_format($refundablePrice, 2) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        @if ($refTotal > 0)
                                            <tr>
                                                <th><strong>Refundable Add-ons Total</strong></th>
                                                <td><strong>₱{{ number_format($refTotal, 2) }}</strong></td>
                                            </tr>
                                        @endif
                                    @endif

                                    <tr class="total-row">
                                        <td colspan="2"><strong>Total Price:
                                                ₱{{ number_format($totalPrice, 2) }}</strong></td>
                                    </tr>
                                @elseif ($facility->facility_type === 'whole_place')
                                    @php
                                        $dateFrom = \Carbon\Carbon::parse($reservationData['date_from'] ?? now());
                                        $dateTo = \Carbon\Carbon::parse($reservationData['date_to'] ?? now());
                                        $timeStartRaw = $reservationData['time_start'] ?? null;
                                        $timeEndRaw = $reservationData['time_end'] ?? null;
                                        $timeStart = $timeStartRaw ? \Carbon\Carbon::parse($timeStartRaw) : null;
                                        $timeEnd = $timeEndRaw ? \Carbon\Carbon::parse($timeEndRaw) : null;

                                        $addons = $reservationData['addons'] ?? [];
                                        $addonsTotal = (float) ($reservationData['addons_total'] ?? 0);
                                        $refundableAddons = $reservationData['refundable_addons'] ?? [];
                                        $refundableAddonsTotal =
                                            (float) ($reservationData['refundable_addons_total'] ?? 0);

                                        $numberOfDays = (int) ($reservationData['number_of_days'] ?? 1);
                                        $isBasedOnDays = (bool) ($reservationData['is_based_on_days'] ?? false);
                                        $basePricePerDay = (float) ($reservationData['price'] ?? 0);

                                        $initialPrice = (float) ($reservationData['initial_price'] ?? 0);
                                        $subtotal =
                                            (float) ($reservationData['subtotal'] ?? $initialPrice + $addonsTotal);
                                        $totalPrice =
                                            (float) ($reservationData['total_price'] ??
                                                $subtotal + $refundableAddonsTotal);
                                    @endphp





                                    <tr>
                                        <th>Date From</th>
                                        <td>{{ $dateFrom->format('F d, Y') }}</td>
                                    </tr>

                                    <tr>
                                        <th>Date To</th>
                                        <td>{{ $dateTo->format('F d, Y') }}</td>
                                    </tr>

                                    @if ($timeStart && $timeEnd)
                                        <tr>
                                            <th>Start Time to End Time</th>
                                            <td>
                                                @if ($timeStart->eq($timeEnd))
                                                    {{ $timeStart->format('h:i A') }}
                                                @else
                                                    {{ $timeStart->format('h:i A') }} to {{ $timeEnd->format('h:i A') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <th>Initial Price</th>
                                        <td>
                                            {{-- @if ($isBasedOnDays)
                ₱{{ number_format($basePricePerDay, 2) }} (Flat Rate)
            @else
                ₱{{ number_format($basePricePerDay, 2) }} × {{ $numberOfDays }} {{ \Illuminate\Support\Str::plural('day', $numberOfDays) }} --}}
                                            ₱{{ number_format($initialPrice, 2) }}
                                            {{-- @endif --}}
                                        </td>
                                    </tr>
                                    @if (!empty($addons) && count($addons) > 0)
                                        @php
                                            $filteredAddons = [];
                                            $filteredAddonTotal = 0;

                                            foreach ($addons as $addon) {
                                                $addonId = $addon['addon_id'] ?? '';
                                                $addonName = $addon['addon_name'] ?? '';
                                                $addonPrice = $addon['base_price'] ?? 0;
                                                $addonType = $addon['addon_type'] ?? '';
                                                $addonQuantity = $addon['quantity'] ?? 0;
                                                $addonNights = $addon['nights'] ?? 1;
                                                $isQuantityBased = $addon['is_quantity_based'] ?? false;
                                                $addonDateFrom = $addon['date_from'] ?? null;
                                                $addonDateTo = $addon['date_to'] ?? null;
                                                $billingCycle = $addon['billing_cycle'] ?? 'per_contract';
                                                $calculatedPrice = $addon['calculated_price'] ?? 0;

                                                // Check if addon should be displayed
                                                $shouldDisplay = true;

                                                // For per_contract billing, only show if actually selected/used
                                                if ($billingCycle === 'per_contract') {
                                                    if ($isQuantityBased) {
                                                        $shouldDisplay = $addonQuantity > 0;
                                                    } else {
                                                        $shouldDisplay = $calculatedPrice > 0;
                                                    }
                                                }

                                                if ($calculatedPrice > 0 && $shouldDisplay) {
                                                    $filteredAddons[$addonId] = [
                                                        'name' => $addonName,
                                                        'total' => $calculatedPrice,
                                                        'quantity' => $addonQuantity,
                                                        'nights' => $addonNights,
                                                        'type' => $addonType,
                                                        'billing_cycle' => $billingCycle,
                                                        'date_from' => $addonDateFrom,
                                                        'date_to' => $addonDateTo,
                                                        'is_quantity_based' => $isQuantityBased,
                                                    ];
                                                    $filteredAddonTotal += $calculatedPrice;
                                                }
                                            }
                                        @endphp

                                        @if (count($filteredAddons) > 0)
                                            <tr>
                                                <th colspan="2" class="bg-light"><strong>Add-Ons</strong></th>
                                            </tr>
                                            @foreach ($filteredAddons as $addonId => $addon)
                                                <tr>
                                                    <th>
                                                        {{ $addon['name'] }}
                                                        @if ($addon['quantity'] > 0 && $addon['is_quantity_based'])
                                                            (x{{ $addon['quantity'] }})
                                                        @endif
                                                        @if (
                                                            $addon['nights'] > 1 &&
                                                                in_array($addon['type'], ['per_night', 'flat_rate', 'per_unit']) &&
                                                                $addon['billing_cycle'] === 'per_day')
                                                            ({{ $addon['nights'] }} days)
                                                        @endif
                                                        @if ($addon['billing_cycle'] === 'per_contract')
                                                            {{-- <small class="text-muted">(one-time)</small> --}}
                                                        @endif
                                                    </th>
                                                    <td>₱{{ number_format($addon['total'], 2) }}</td>
                                                </tr>
                                                @if ($addon['date_from'] && $addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>Date Range:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif($addon['date_from'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>Start Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif($addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th style="padding-left: 30px; font-weight: normal;">
                                                            <small>End Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            @if ($filteredAddonTotal > 0)
                                                <tr class="row-total">
                                                    <th class="col-label">Add-ons Total</th>
                                                    <td class="col-amount">₱{{ number_format($filteredAddonTotal, 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endif
                                    <tr class="row-total">
                                        <th class="col-label">Subtotal</th>
                                        <td class="col-amount">₱{{ number_format($subtotal, 2) }}</td>
                                    </tr>

                                    @if (!empty($refundableAddons))
                                        <tr>
                                            <th colspan="2" class="bg-light"><strong>Refundable Add-Ons</strong></th>
                                        </tr>
                                        @foreach ($refundableAddons as $refundableAddon)
                                            <tr>
                                                <th>
                                                    {{ $refundableAddon['addon_name'] ?? 'Refundable Addon' }}
                                                    {{-- <span class="badge bg-success">Refundable</span> --}}
                                                </th>
                                                <td>₱{{ number_format((float) ($refundableAddon['calculated_price'] ?? 0), 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th><strong>Refundable Add-ons Total<strong></th>
                                            <td><strong>₱{{ number_format($refundableAddonsTotal, 2) }}<strong></td>
                                        </tr>
                                    @endif

                                    <tr class="total-row">
                                        <td colspan="2"><strong>Total Price:
                                                ₱{{ number_format($totalPrice, 2) }}</strong></td>
                                    </tr>
                                @elseif(
                                    $facility->facility_type === 'both' &&
                                        $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
                                    @php
                                        $dateFrom = $reservationData['date_from'] ?? null;
                                        $dateTo = $reservationData['date_to'] ?? null;
                                        $initialPrice = (float) ($reservationData['initial_price'] ?? 0);
                                        $addonsTotal = (float) ($reservationData['addons_total'] ?? 0);
                                        $refTotal = (float) ($reservationData['refundable_total'] ?? 0);
                                        $subtotal = (float) ($reservationData['subtotal'] ?? 0);
                                        $totalPrice = (float) ($reservationData['total_price'] ?? 0);

                                        $sharedAddons = $reservationData['shared_addons'] ?? [];
                                        $wholeAddons = $reservationData['whole_addons'] ?? [];
                                        $currentAddons =
                                            $reservationData['booking_type'] === 'shared'
                                                ? $sharedAddons
                                                : $wholeAddons;

                                        $normalizedAddons = [];
                                        if (!empty($currentAddons['addon_names'])) {
                                            foreach ($currentAddons['addon_names'] as $addonId => $name) {
                                                $quantity = (int) ($currentAddons['addon_quantity'][$addonId] ?? 0);
                                                $checked = isset($currentAddons['addon_checkbox'][$addonId]);
                                                $basePrice = (float) ($currentAddons['addon_values'][$addonId] ?? 0);
                                                $type = $currentAddons['addon_types'][$addonId] ?? '';
                                                $billing = $currentAddons['addon_billing_cycle'][$addonId] ?? '';
                                                $nights = (int) ($currentAddons['addon_nights'][$addonId] ?? 0);
                                                $isQtyBased =
                                                    (bool) ($currentAddons['addon_is_quantity_based'][$addonId] ??
                                                        false);
                                                $aDateFrom = $currentAddons['addon_date_from'][$addonId] ?? null;
                                                $aDateTo = $currentAddons['addon_date_to'][$addonId] ?? null;

                                                if ($quantity > 0 || $checked) {
                                                    $normalizedAddons[] = [
                                                        'name' => $name,
                                                        'quantity' => $quantity,
                                                        'base_price' => $basePrice,
                                                        'type' => $type,
                                                        'billing' => $billing,
                                                        'nights' => $nights,
                                                        'is_quantity_based' => $isQtyBased,
                                                        'date_from' => $aDateFrom,
                                                        'date_to' => $aDateTo,
                                                    ];
                                                }
                                            }
                                        }

                                        $normalizedRefundables = [];
                                        if (!empty($currentAddons['refundable_addon_names'])) {
                                            foreach ($currentAddons['refundable_addon_names'] as $addonId => $name) {
                                                $price =
                                                    (float) ($currentAddons['refundable_addon_prices'][$addonId] ?? 0);
                                                if ($price > 0) {
                                                    $normalizedRefundables[] = ['name' => $name, 'total' => $price];
                                                }
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <th>Booking Type</th>
                                        <td>{{ ucfirst($reservationData['booking_type']) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Room Name</th>
                                        <td>{{ $reservationData['room_name'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Room Capacity</th>
                                        <td>{{ $reservationData['room_capacity'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date From</th>
                                        <td>{{ $dateFrom ? Carbon\Carbon::parse($dateFrom)->format('F d, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date To</th>
                                        <td>{{ $dateTo ? Carbon\Carbon::parse($dateTo)->format('F d, Y') : 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Initial Price</th>
                                        <td>₱{{ number_format($initialPrice, 2) }}</td>
                                    </tr>

                                    @if (count($normalizedAddons) > 0)
                                        <tr>
                                            <th colspan="2" class="bg-light"><strong>Add-Ons</strong></th>
                                        </tr>
                                        @foreach ($normalizedAddons as $addon)
                                            <tr>
                                                <th>
                                                    {{ $addon['name'] }}
                                                    @if ($addon['is_quantity_based'] && $addon['quantity'] > 0)
                                                        (x{{ $addon['quantity'] }})
                                                    @endif
                                                    @if (
                                                        $addon['billing'] === 'per_day' &&
                                                            $addon['nights'] > 1 &&
                                                            in_array($addon['type'], ['per_night', 'flat_rate', 'per_unit', 'per_item']))
                                                        ({{ $addon['nights'] }} days)
                                                    @endif
                                                </th>
                                                <td>
                                                    @if ($addon['is_quantity_based'] && $addon['quantity'] > 0)
                                                        ₱{{ number_format($addon['base_price'] * $addon['quantity'] * ($addon['billing'] === 'per_day' ? max(1, $addon['nights']) : 1), 2) }}
                                                    @else
                                                        ₱{{ number_format($addon['base_price'] * ($addon['billing'] === 'per_day' ? max(1, $addon['nights']) : 1), 2) }}
                                                    @endif
                                                </td>
                                            </tr>

                                            @if ($addon['billing'] === 'per_day')
                                                @if (!empty($addon['date_from']) && !empty($addon['date_to']))
                                                    <tr>
                                                        <th style="padding-left:30px; font-weight:normal;">
                                                            <small>
                                                                Date Range:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}
                                                            </small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif(!empty($addon['date_from']))
                                                    <tr>
                                                        <th style="padding-left:30px; font-weight:normal;">
                                                            <small>Start Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @elseif(!empty($addon['date_to']))
                                                    <tr>
                                                        <th style="padding-left:30px; font-weight:normal;">
                                                            <small>End Date:
                                                                {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                        </th>
                                                        <td></td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach
                                        <tr class="table-active">
                                            <th><strong>Add-ons Total</strong></th>
                                            <td><strong>₱{{ number_format($addonsTotal, 2) }}</strong></td>
                                        </tr>
                                    @endif

                                    <tr class="table-active">
                                        <th><strong>Subtotal</strong></th>
                                        <td><strong>₱{{ number_format($subtotal, 2) }}</strong></td>
                                    </tr>

                                    @if (count($normalizedRefundables) > 0)
                                        <tr>
                                            <th colspan="2" class="bg-light"><strong>Refundable Add-Ons</strong></th>
                                        </tr>
                                        @foreach ($normalizedRefundables as $ref)
                                            <tr>
                                                <th>{{ $ref['name'] }}</th>
                                                <td>₱{{ number_format($ref['total'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th><strong>Refundable Add-ons Total</strong></th>
                                            <td><strong>₱{{ number_format($refTotal, 2) }}</strong></td>
                                        </tr>
                                    @endif

                                    <tr class="total-row">
                                        <td colspan="2"><strong>Total Price:
                                                ₱{{ number_format($totalPrice, 2) }}</strong></td>
                                    </tr>
                                @elseif (
                                    $facility->facility_type === 'both' &&
                                        $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
                                    @php
                                        $priceForDates = $facility->prices
                                            ->where(
                                                'price_type',
                                                $reservationData['booking_type'] === 'shared' ? 'individual' : 'whole',
                                            )
                                            ->first();

                                        $displayDateFrom = $date_from ?: $priceForDates->date_from ?? null;
                                        $displayDateTo = $date_to ?: $priceForDates->date_to ?? null;

                                        $timeStartRaw = $time_start ?? ($reservationData['time_start'] ?? null);
                                        $timeEndRaw = $time_end ?? ($reservationData['time_end'] ?? null);
                                        $timeStart = $timeStartRaw ? \Carbon\Carbon::parse($timeStartRaw) : null;
                                        $timeEnd = $timeEndRaw ? \Carbon\Carbon::parse($timeEndRaw) : null;

                                        $addons =
                                            $reservationData['addons_breakdown'] ?? ($reservationData['addons'] ?? []);
                                        $addonsTotal = (float) ($reservationData['addons_total'] ?? 0);
                                        $refundableAddons = $reservationData['refundable_addons'] ?? [];
                                        $refundableAddonsTotal = (float) ($reservationData['refundable_total'] ?? 0);

                                        $initialPrice = (float) ($reservationData['initial_price'] ?? 0);
                                        $subtotal =
                                            (float) ($reservationData['subtotal'] ?? $initialPrice + $addonsTotal);
                                        $totalPrice =
                                            (float) ($reservationData['total_price'] ??
                                                $subtotal + $refundableAddonsTotal);
                                    @endphp

                                    <tr>
                                        <th class="col-label"><span class="label-ellipsis" title="Booking Type">Booking
                                                Type</span></th>
                                        <td class="col-amount">{{ ucfirst($reservationData['booking_type']) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="col-label"><span class="label-ellipsis" title="Date From">Date
                                                From</span></th>
                                        <td class="col-amount">
                                            {{ $displayDateFrom ? \Carbon\Carbon::parse($displayDateFrom)->format('F d, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="col-label"><span class="label-ellipsis" title="Date To">Date To</span>
                                        </th>
                                        <td class="col-amount">
                                            {{ $displayDateTo ? \Carbon\Carbon::parse($displayDateTo)->format('F d, Y') : 'N/A' }}
                                        </td>
                                    </tr>

                                    @if ($reservationData['booking_type'] === 'whole' && $timeStart && $timeEnd)
                                        <tr>
                                            <th class="col-label"><span class="label-ellipsis" title="Time">Time</span>
                                            </th>
                                            <td class="col-amount">
                                                @if ($timeStart->eq($timeEnd))
                                                    {{ $timeStart->format('h:i A') }}
                                                @else
                                                    {{ $timeStart->format('h:i A') }} - {{ $timeEnd->format('h:i A') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($selectedPriceDetails))
                                        <tr>
                                            <th class="col-label">
                                                <span class="label-ellipsis" title="{{ $selectedPriceDetails['name'] }}">
                                                    {{ $selectedPriceDetails['name'] }}
                                                </span>
                                            </th>
                                            <td class="col-amount">₱{{ number_format($selectedPriceDetails['price'], 2) }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($quantityDetails))
                                        @foreach ($quantityDetails as $item)
                                            <tr>
                                                <th class="col-label">
                                                    <span class="label-ellipsis" title="{{ $item['name'] }}">
                                                        {{ $item['name'] }} (x{{ $item['quantity'] }})
                                                    </span>
                                                </th>
                                                <td class="col-amount">₱{{ number_format($item['total'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif

                                    <tr class="row-total">
                                        <th class="col-label">
                                            <span class="label-ellipsis" title="Initial Price">Initial Price</span>
                                        </th>
                                        <td class="col-amount">₱{{ number_format($initialPrice, 2) }}</td>
                                    </tr>


                                    @if (!empty($addons) && count($addons) > 0)
                                        @php
                                            $filteredAddons = [];
                                            $filteredAddonTotal = 0;

                                            foreach ($addons as $addon) {
                                                $addonId = $addon['addon_id'] ?? ($addon['id'] ?? '');
                                                $addonName = $addon['name'] ?? ($addon['addon_name'] ?? '');
                                                $addonPrice = (float) ($addon['base_price'] ?? ($addon['price'] ?? 0));
                                                $addonType = $addon['price_type'] ?? ($addon['addon_type'] ?? '');
                                                $addonQuantity = (int) ($addon['quantity'] ?? 0);
                                                $addonNights =
                                                    (int) ($addon['days_or_nights'] ?? ($addon['nights'] ?? 0));
                                                $isQtyBased =
                                                    (bool) (($addon['is_quantity'] ??
                                                        ($addon['is_quantity_based'] ?? 0)) ==
                                                        1);
                                                $billingCycle = $addon['billing_cycle'] ?? 'per_contract';
                                                $dateFrom = $addon['date_from'] ?? null;
                                                $dateTo = $addon['date_to'] ?? null;
                                                $lineTotal =
                                                    (float) ($addon['line_total'] ?? ($addon['calculated_price'] ?? 0));

                                                $shouldDisplay = true;
                                                if ($billingCycle === 'per_contract') {
                                                    $shouldDisplay = $isQtyBased ? $addonQuantity > 0 : $lineTotal > 0;
                                                }

                                                if ($lineTotal > 0 && $shouldDisplay) {
                                                    $filteredAddons[$addonId] = [
                                                        'name' => $addonName,
                                                        'total' => $lineTotal,
                                                        'quantity' => $addonQuantity,
                                                        'nights' => $addonNights,
                                                        'type' => $addonType,
                                                        'billing_cycle' => $billingCycle,
                                                        'date_from' => $dateFrom,
                                                        'date_to' => $dateTo,
                                                        'is_quantity_based' => $isQtyBased,
                                                    ];
                                                    $filteredAddonTotal += $lineTotal;
                                                }
                                            }
                                        @endphp

                                        @if (count($filteredAddons) > 0)
                                            <tr>
                                                <th colspan="2" class="bg-light"><strong>Add-Ons</strong></th>
                                            </tr>
                                            @foreach ($filteredAddons as $addonId => $addon)
                                                <tr>
                                                    <th class="col-label">
                                                        <span class="label-ellipsis" title="{{ $addon['name'] }}">
                                                            {{ $addon['name'] }}
                                                            @if ($addon['quantity'] > 0 && $addon['is_quantity_based'])
                                                                (x{{ $addon['quantity'] }})
                                                            @endif
                                                            @if (
                                                                $addon['nights'] > 1 &&
                                                                    in_array($addon['type'], ['per_night', 'flat_rate', 'per_unit']) &&
                                                                    $addon['billing_cycle'] === 'per_day')
                                                                ({{ $addon['nights'] }} days)
                                                            @endif
                                                        </span>
                                                    </th>
                                                    <td class="col-amount">₱{{ number_format($addon['total'], 2) }}</td>
                                                </tr>

                                                @if ($addon['date_from'] && $addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th class="col-label"
                                                            style="padding-left: 30px; font-weight: normal;">
                                                            <span class="label-ellipsis" title="Date Range">
                                                                <small>Date Range:
                                                                    {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}
                                                                    -
                                                                    {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                            </span>
                                                        </th>
                                                        <td class="col-amount"></td>
                                                    </tr>
                                                @elseif($addon['date_from'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th class="col-label"
                                                            style="padding-left: 30px; font-weight: normal;">
                                                            <span class="label-ellipsis" title="Start Date">
                                                                <small>Start Date:
                                                                    {{ \Carbon\Carbon::parse($addon['date_from'])->format('M d, Y') }}</small>
                                                            </span>
                                                        </th>
                                                        <td class="col-amount"></td>
                                                    </tr>
                                                @elseif($addon['date_to'] && $addon['billing_cycle'] === 'per_day')
                                                    <tr>
                                                        <th class="col-label"
                                                            style="padding-left: 30px; font-weight: normal;">
                                                            <span class="label-ellipsis" title="End Date">
                                                                <small>End Date:
                                                                    {{ \Carbon\Carbon::parse($addon['date_to'])->format('M d, Y') }}</small>
                                                            </span>
                                                        </th>
                                                        <td class="col-amount"></td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            @if ($filteredAddonTotal > 0)
                                                <tr class="table-active">
                                                    <th class="col-label"><strong>Add-ons Total</strong></th>
                                                    <td class="col-amount">
                                                        <strong>₱{{ number_format($filteredAddonTotal, 2) }}</strong>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endif

                                    <tr class="table-active">
                                        <th class="col-label"><strong>Subtotal</strong></th>
                                        <td class="col-amount"><strong>₱{{ number_format($subtotal, 2) }}</strong></td>
                                    </tr>

                                    @if (!empty($refundableAddons))
                                        <tr>
                                            <th colspan="2" class="bg-light"><strong>Refundable Add-Ons</strong></th>
                                        </tr>
                                        @foreach ($refundableAddons as $refundableAddon)
                                            @php
                                                $refName =
                                                    $refundableAddon['name'] ??
                                                    ($refundableAddon['addon_name'] ?? 'Refundable Addon');
                                                $refAmt =
                                                    (float) ($refundableAddon['price'] ??
                                                        ($refundableAddon['calculated_price'] ?? 0));
                                            @endphp
                                            <tr>
                                                <th class="col-label"><span class="label-ellipsis"
                                                        title="{{ $refName }}">{{ $refName }}</span></th>
                                                <td class="col-amount">₱{{ number_format($refAmt, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th class="col-label"><strong>Refundable Add-ons Total</strong></th>
                                            <td class="col-amount">
                                                <strong>₱{{ number_format($refundableAddonsTotal, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endif

                                    <tr class="total-row">
                                        <td colspan="2"><strong>Total Price:
                                                ₱{{ number_format($totalPrice, 2) }}</strong></td>
                                    </tr>
                                @endif


                            </tbody>
                        </table>
                    </div>

                    <div class="divider"></div>

                    <form name="checkout-form" action="{{ route('user.facilities.placeReservation') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">

                        @if (!empty($reservationData['discount_id']))
                            <input type="hidden" name="discount_id" value="{{ $reservationData['discount_id'] }}">
                            <input type="hidden" name="discount_percent"
                                value="{{ $reservationData['discount_percent'] }}">
                            <input type="hidden" name="discount_amount"
                                value="{{ $reservationData['discount_amount'] }}">
                            <input type="hidden" name="gross_total" value="{{ $reservationData['gross_total'] }}">
                        @endif

                        @if ($facility->facility_type === 'individual')
                            <input type="hidden" name="facility_attribute_id"
                                value="{{ $facilityAttribute->id ?? $reservationData['facility_attribute_id'] }}">
                            <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                            <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                        @elseif($facility->facility_type === 'whole_place')
                            <div>
                                <input type="hidden" id="date_from" name="date_from"
                                    value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
                                <input type="hidden" id="date_to" name="date_to"
                                    value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
                                <input type="hidden" name="time_start"
                                    value="{{ $reservationData['time_start'] ?? '' }}">
                                <input type="hidden" name="time_end" value="{{ $reservationData['time_end'] ?? '' }}">

                                <input type="hidden" name="price" value="{{ $reservationData['price'] ?? 0 }}">
                                <input type="hidden" name="number_of_days"
                                    value="{{ $reservationData['number_of_days'] ?? 1 }}">
                                <input type="hidden" name="is_based_on_days"
                                    value="{{ !empty($reservationData['is_based_on_days']) ? 1 : 0 }}">
                                <input type="hidden" name="initial_price"
                                    value="{{ $reservationData['initial_price'] ?? 0 }}">

                                <input type="hidden" name="addons_total"
                                    value="{{ $reservationData['addons_total'] ?? 0 }}">
                                <input type="hidden" name="refundable_addons_total"
                                    value="{{ $reservationData['refundable_addons_total'] ?? 0 }}">

                                <input type="hidden" name="subtotal" value="{{ $reservationData['subtotal'] ?? 0 }}">
                                <input type="hidden" name="refundable_total"
                                    value="{{ $reservationData['refundable_total'] ?? ($reservationData['refundable_addons_total'] ?? 0) }}">
                                <input type="hidden" name="total_price"
                                    value="{{ $reservationData['total_price'] ?? 0 }}">

                                @if (!empty($reservationData['addons']))
                                    @foreach ($reservationData['addons'] as $index => $addon)
                                        <input type="hidden" name="addons[{{ $index }}][addon_id]"
                                            value="{{ $addon['addon_id'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][addon_name]"
                                            value="{{ $addon['addon_name'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][addon_type]"
                                            value="{{ $addon['addon_type'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][billing_cycle]"
                                            value="{{ $addon['billing_cycle'] ?? 'per_day' }}">
                                        <input type="hidden" name="addons[{{ $index }}][base_price]"
                                            value="{{ $addon['base_price'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][quantity]"
                                            value="{{ $addon['quantity'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][nights]"
                                            value="{{ $addon['nights'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][is_quantity_based]"
                                            value="{{ $addon['is_quantity_based'] }}">
                                        <input type="hidden" name="addons[{{ $index }}][calculated_price]"
                                            value="{{ $addon['calculated_price'] }}">

                                        <!-- Add these lines for date fields -->
                                        <input type="hidden" name="addons[{{ $index }}][date_from]"
                                            value="{{ $addon['date_from'] ?? '' }}">
                                        <input type="hidden" name="addons[{{ $index }}][date_to]"
                                            value="{{ $addon['date_to'] ?? '' }}">
                                        <input type="hidden" name="addons[{{ $index }}][selected_dates]"
                                            value="{{ json_encode($addon['selected_dates'] ?? []) }}">
                                    @endforeach
                                @endif

                                @if (!empty($reservationData['refundable_addons']))
                                    @foreach ($reservationData['refundable_addons'] as $index => $ref)
                                        <input type="hidden" name="refundable_addons[{{ $index }}][addon_id]"
                                            value="{{ $ref['addon_id'] }}">
                                        <input type="hidden" name="refundable_addons[{{ $index }}][addon_name]"
                                            value="{{ $ref['addon_name'] }}">
                                        <input type="hidden" name="refundable_addons[{{ $index }}][addon_type]"
                                            value="{{ $ref['addon_type'] }}">
                                        <input type="hidden" name="refundable_addons[{{ $index }}][base_price]"
                                            value="{{ $ref['base_price'] }}">
                                        <input type="hidden"
                                            name="refundable_addons[{{ $index }}][billing_cycle]"
                                            value="{{ $ref['billing_cycle'] ?? 'per_contract' }}">
                                        <input type="hidden"
                                            name="refundable_addons[{{ $index }}][calculated_price]"
                                            value="{{ $ref['calculated_price'] }}">
                                        <input type="hidden"
                                            name="refundable_addons[{{ $index }}][is_refundable]" value="1">
                                    @endforeach
                                @endif
                            </div>
                        @elseif(
                            $facility->facility_type === 'both' &&
                                $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
                            <div>
                                <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">
                                <input type="hidden" name="booking_type"
                                    value="{{ $reservationData['booking_type'] }}">

                                @if (isset($reservationData['facility_attribute_id']))
                                    <input type="hidden" name="facility_attribute_id"
                                        value="{{ $reservationData['facility_attribute_id'] }}">
                                @endif

                                <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                                <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                                <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">

                                @if ($reservationData['booking_type'] === 'shared')
                                    @if (isset($reservationData['internal_quantity']) && is_array($reservationData['internal_quantity']))
                                        @foreach ($reservationData['internal_quantity'] as $priceId => $quantity)
                                            <input type="hidden" name="internal_quantity[{{ $priceId }}]"
                                                value="{{ $quantity }}">
                                        @endforeach
                                    @endif

                                    @if (isset($reservationData['price_values']) && is_array($reservationData['price_values']))
                                        @foreach ($reservationData['price_values'] as $priceId => $value)
                                            <input type="hidden" name="price_values[{{ $priceId }}]"
                                                value="{{ $value }}">
                                        @endforeach
                                    @endif

                                    @if (isset($reservationData['price_names']) && is_array($reservationData['price_names']))
                                        @foreach ($reservationData['price_names'] as $priceId => $name)
                                            <input type="hidden" name="price_names[{{ $priceId }}]"
                                                value="{{ $name }}">
                                        @endforeach
                                    @endif

                                    @if (isset($reservationData['price_id']))
                                        <input type="hidden" name="price_id"
                                            value="{{ $reservationData['price_id'] }}">
                                    @endif

                                    @if (isset($reservationData['selected_price']))
                                        <input type="hidden" name="selected_price"
                                            value="{{ $reservationData['selected_price'] }}">
                                    @endif

                                    @if (isset($reservationData['shared_addons']) && is_array($reservationData['shared_addons']))
                                        @foreach ($reservationData['shared_addons'] as $key => $values)
                                            @if (is_array($values))
                                                @foreach ($values as $subKey => $value)
                                                    <input type="hidden"
                                                        name="shared_addons[{{ $key }}][{{ $subKey }}]"
                                                        value="{{ $value }}">
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endif

                                @if ($reservationData['booking_type'] === 'whole')
                                    @if (isset($reservationData['selected_room_id']))
                                        <input type="hidden" name="selected_room_id"
                                            value="{{ $reservationData['selected_room_id'] }}">
                                    @endif

                                    @if (isset($reservationData['whole_price_id']))
                                        <input type="hidden" name="whole_price_id"
                                            value="{{ $reservationData['whole_price_id'] }}">
                                    @endif

                                    @if (isset($reservationData['selected_whole_price']))
                                        <input type="hidden" name="selected_whole_price"
                                            value="{{ $reservationData['selected_whole_price'] }}">
                                    @endif

                                    @if (isset($reservationData['whole_addons']) && is_array($reservationData['whole_addons']))
                                        @foreach ($reservationData['whole_addons'] as $key => $values)
                                            @if (is_array($values))
                                                @foreach ($values as $subKey => $value)
                                                    <input type="hidden"
                                                        name="whole_addons[{{ $key }}][{{ $subKey }}]"
                                                        value="{{ $value }}">
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endif

                                <input type="hidden" name="room_name" value="{{ $reservationData['room_name'] }}">
                                <input type="hidden" name="room_capacity"
                                    value="{{ $reservationData['room_capacity'] }}">
                                <input type="hidden" name="initial_price"
                                    value="{{ $reservationData['initial_price'] }}">
                                <input type="hidden" name="addons_total"
                                    value="{{ $reservationData['addons_total'] }}">
                                <input type="hidden" name="refundable_total"
                                    value="{{ $reservationData['refundable_total'] }}">
                                <input type="hidden" name="subtotal" value="{{ $reservationData['subtotal'] }}">
                            </div>
                        @elseif (
                            $facility->facility_type === 'both' &&
                                $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
                            <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                            <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">
                            <input type="hidden" name="booking_type" value="{{ $reservationData['booking_type'] }}">
                            <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">

                            @if ($reservationData['booking_type'] === 'shared')
                                @php
                                    $finalDateFrom = $date_from ?: $priceForDates->date_from ?? null;
                                    $finalDateTo = $date_to ?: $priceForDates->date_to ?? null;
                                @endphp

                                <input type="hidden" name="date_from" value="{{ $finalDateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $finalDateTo }}">
                                <input type="hidden" name="facility_attribute_id"
                                    value="{{ $reservationData['facility_attribute_id'] ?? '' }}">

                                @if (!empty($reservationData['price_id']))
                                    <input type="hidden" name="price_id" value="{{ $reservationData['price_id'] }}">
                                    <input type="hidden" name="selected_price"
                                        value="{{ $reservationData['selected_price'] ?? '' }}">
                                @endif

                                @if (!empty($reservationData['internal_quantity']))
                                    @foreach ($reservationData['internal_quantity'] as $priceId => $quantity)
                                        <input type="hidden" name="internal_quantity[{{ $priceId }}]"
                                            value="{{ $quantity }}">
                                        <input type="hidden" name="price_values[{{ $priceId }}]"
                                            value="{{ $reservationData['price_values'][$priceId] ?? '' }}">
                                        <input type="hidden" name="price_names[{{ $priceId }}]"
                                            value="{{ $reservationData['price_names'][$priceId] ?? '' }}">
                                    @endforeach
                                @endif
                            @elseif($reservationData['booking_type'] === 'whole')
                                @php
                                    $finalDateFrom = $date_from ?: $priceForDates->date_from ?? null;
                                    $finalDateTo = $date_to ?: $priceForDates->date_to ?? null;
                                @endphp

                                <input type="hidden" name="date_from" value="{{ $finalDateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $finalDateTo }}">
                                <input type="hidden" name="facility_attribute_id"
                                    value="{{ $reservationData['facility_attribute_id'] ?? '' }}">

                                @if ($time_start)
                                    <input type="hidden" name="time_start" value="{{ $time_start }}">
                                @endif

                                @if ($time_end)
                                    <input type="hidden" name="time_end" value="{{ $time_end }}">
                                @endif

                                @if (!empty($reservationData['whole_client_type']))
                                    <input type="hidden" name="whole_client_type"
                                        value="{{ $reservationData['whole_client_type'] }}">
                                @endif
                            @endif
                        @endif

                        <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">

                        <div class="requirements-card">
                            <div class="requirements-header">
                                Requirements & Documents
                            </div>
                            <div class="requirements-body">
                                @if ($facility && $facility->requirements)
                                    @php
                                        $fileExtension = strtolower(
                                            pathinfo($facility->requirements, PATHINFO_EXTENSION),
                                        );
                                        $isImageReq = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                                    @endphp
                                    <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" download
                                        class="download-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Download Requirements
                                    </a>
                                @else
                                    <p style="color: var(--text-light); margin-bottom: var(--space-md);">No requirements
                                        document available</p>
                                @endif
                                @error('requirements')
                                    <div class="error-text">{{ $message }}</div>
                                @enderror

                                <div class="file-upload-wrapper">
                                    <label for="qualification" class="file-upload-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="file-upload-icon" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="file-upload-text">
                                            <span class="file-upload-title">Upload Qualification Document</span>
                                            <span class="file-upload-subtitle">PDF or DOC files (Max 10MB)</span>
                                        </span>
                                        <input type="file" id="qualification" name="qualification"
                                            class="file-upload-input" accept=".pdf,.doc,.docx" required>
                                    </label>
                                    <div class="file-upload-preview" id="file-preview">
                                        <span class="file-upload-name" id="file-name">No file selected</span>
                                        <button type="button" class="file-upload-clear" id="clear-file"
                                            aria-label="Remove file">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('qualification')
                                        <div class="file-upload-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- <pre>
                                    {{ print_r($reservationData, true) }}
                                    </pre> --}}
                                {{-- @if ($requiresDiscountProof)
                                    <div class="booking-section">
                                        <div class="section-header">
                                            <i class="fas fa-file-upload"></i>
                                            <span><strong>Discount Proof Required:</strong></span>
                                        </div>
                                        <div class="section-content">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                The selected price requires discount proof verification.
                                            </div>
                                            <div class="form-group">
                                                <label for="discount_proof">Upload Discount Proof <span
                                                        class="text-danger">*</span></label>
                                                <input type="file"
                                                    class="form-control @error('discount_proof') is-invalid @enderror"
                                                    id="discount_proof" name="discount_proof" accept="image/*,.pdf"
                                                    required>
                                                <small class="form-text text-muted">
                                                    Accepted formats: JPG, PNG, PDF (Max: 5MB)
                                                </small>
                                                @error('discount_proof')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif --}}


                            </div>
                        </div>
                        @if ($requiresDiscountProof)
                            <div class="requirements-card">
                                <div class="requirements-header">
                                    Discount Proof Required
                                </div>

                                <div class="requirements-body">

                                    @if ($discountProofPath ?? false)
                                        @php
                                            $fileExtension = strtolower(
                                                pathinfo($discountProofPath, PATHINFO_EXTENSION),
                                            );
                                            $isImageProof = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                                        @endphp

                                        <a href="{{ asset('/storage/discounts/' . $discountProofPath) }}" download
                                            class="download-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Download Submitted Proof
                                        </a>
                                    @endif

                                    <div class="file-upload-wrapper">
                                        <label for="discount_proof" class="file-upload-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="file-upload-icon"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <span class="file-upload-text">
                                                <span class="file-upload-title">Upload Discount Proof</span>
                                                <span class="file-upload-subtitle">JPG, PNG, or PDF (Max
                                                    5MB)</span>
                                            </span>
                                            <input type="file" id="discount_proof" name="discount_proof"
                                                class="file-upload-input @error('discount_proof') is-invalid @enderror"
                                                accept=".jpg,.jpeg,.png,.pdf" required>
                                        </label>

                                        <div class="file-upload-preview" id="discount-file-preview">
                                            <span class="file-upload-name" id="discount-file-name">No file
                                                selected</span>
                                            <button type="button" class="file-upload-clear" id="clear-discount-file"
                                                aria-label="Remove file">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>

                                        @error('discount_proof')
                                            <div class="file-upload-error">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        @endif

                        <button type="submit" class="submit-btn">
                            Confirm Reservation
                        </button>


                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('qualification');
            const fileName = document.getElementById('file-name');
            const clearButton = document.getElementById('clear-file');
            const filePreview = document.getElementById('file-preview');

            filePreview.style.display = 'none';

            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const file = e.target.files[0];
                    const validTypes = ['application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];
                    const fileType = file.type;

                    if (!validTypes.includes(fileType)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'Please upload only PDF or DOC/DOCX files',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            this.value = '';
                            fileName.textContent = 'No file selected';
                            filePreview.style.display = 'none';
                        });
                        return;
                    }

                    if (file.size > 10 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File too large',
                            text: 'File size exceeds 10MB limit',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            this.value = '';
                            fileName.textContent = 'No file selected';
                            filePreview.style.display = 'none';
                        });
                        return;
                    }

                    fileName.textContent = file.name;
                    filePreview.style.display = 'flex';
                }
            });

            clearButton.addEventListener('click', function() {
                fileInput.value = '';
                fileName.textContent = 'No file selected';
                filePreview.style.display = 'none';
            });


            const discountProofInput = document.getElementById('discount_proof');

            // if (discountProofInput) {
            //     const discountProofName = document.getElementById('discount-proof-name');
            //     const clearDiscountProofButton = document.getElementById('clear-discount-proof');
            //     const discountProofPreview = document.getElementById('discount-proof-preview');

            //     discountProofInput.addEventListener('change', function(e) {
            //         if (e.target.files.length > 0) {
            //             const file = e.target.files[0];
            //             const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

            //             if (!validTypes.includes(file.type)) {
            //                 Swal.fire({
            //                     icon: 'error',
            //                     title: 'Invalid File Type',
            //                     text: 'Please upload only JPG, PNG, or PDF files',
            //                     confirmButtonColor: '#3b82f6',
            //                     confirmButtonText: 'OK'
            //                 }).then(() => {
            //                     this.value = '';
            //                     discountProofName.textContent = 'No file selected';
            //                     discountProofPreview.style.display = 'none';
            //                 });
            //                 return;
            //             }

            //             if (file.size > 10 * 1024 * 1024) {
            //                 Swal.fire({
            //                     icon: 'error',
            //                     title: 'File too large',
            //                     text: 'File size exceeds 10MB limit',
            //                     confirmButtonColor: '#3b82f6',
            //                     confirmButtonText: 'OK'
            //                 }).then(() => {
            //                     this.value = '';
            //                     discountProofName.textContent = 'No file selected';
            //                     discountProofPreview.style.display = 'none';
            //                 });
            //                 return;
            //             }

            //             discountProofName.textContent = file.name;
            //             discountProofPreview.classList.remove('d-none');
            //             discountProofPreview.style.display = 'flex';
            //         }
            //     });

            //     clearDiscountProofButton.addEventListener('click', function() {
            //         discountProofInput.value = '';
            //         discountProofName.textContent = 'No file selected';
            //         discountProofPreview.style.display = 'none';
            //     });
            // }
            if (discountProofInput) {
                const discountProofName = document.getElementById('discount-file-name');
                const clearDiscountProofButton = document.getElementById('clear-discount-file');
                const discountProofPreview = document.getElementById('discount-file-preview');

                discountProofPreview.style.display = 'none';

                discountProofInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {

                        const file = e.target.files[0];
                        const validTypes = [
                            'application/pdf',
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        ];

                        if (!validTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: 'Please upload only JPG, PNG, or PDF files',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => {
                                this.value = '';
                                discountProofName.textContent = 'No file selected';
                                discountProofPreview.style.display = 'none';
                            });
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File too large',
                                text: 'File size exceeds 5MB limit',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => {
                                this.value = '';
                                discountProofName.textContent = 'No file selected';
                                discountProofPreview.style.display = 'none';
                            });
                            return;
                        }

                        discountProofName.textContent = file.name;
                        discountProofPreview.style.display = 'flex';
                    }
                });

                clearDiscountProofButton.addEventListener('click', function() {
                    discountProofInput.value = '';
                    discountProofName.textContent = 'No file selected';
                    discountProofPreview.style.display = 'none';
                });
            }


            document.querySelector('form[name="checkout-form"]').addEventListener('submit', function(e) {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const validTypes = ['application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];

                    if (!validTypes.includes(file.type)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'Please upload only PDF or DOC/DOCX files',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'OK'
                        });
                    }

                    if (file.size > 10 * 1024 * 1024) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'File too large',
                            text: 'File size exceeds 10MB limit',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });
    </script>
@endpush
