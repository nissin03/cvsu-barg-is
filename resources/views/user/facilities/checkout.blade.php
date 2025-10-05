@extends('layouts.app')
@section('content')

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
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                                        <td>{{ ($reservationData['room_name']) }}</td>
                                    </tr>

                                    @if($selectedPriceDetails)
                                        <tr>
                                            <th>{{ $selectedPriceDetails['name'] }}</th>
                                            <td>₱{{ number_format($selectedPriceDetails['price'], 2) }}</td>
                                        </tr>
                                    @endif

                                    @if(!empty($date_from))
                                        <tr>
                                            <th>Date From</th>
                                            <td>{{ \Carbon\Carbon::parse($date_from)->format('F d, Y') }}</td>
                                        </tr>
                                    @endif

                                    @if(!empty($date_to))
                                        <tr>
                                            <th>Date To</th>
                                            <td>{{ \Carbon\Carbon::parse($date_to)->format('F d, Y') }}</td>
                                        </tr>                  
                                    @endif

                                    @php
                                        $datedPrice = $facility->prices->first(function($price) {
                                            return !$price->is_there_a_quantity && 
                                                $price->is_based_on_days && 
                                                ($price->date_from || $price->date_to);
                                        });
                                    @endphp
                                    @if ($datedPrice && ($datedPrice->date_from || $datedPrice->date_to))
                                        <tr>
                                            <th>Available From</th>
                                            <td>
                                                @if ($datedPrice->date_from)
                                                    {{ \Carbon\Carbon::parse($datedPrice->date_from)->format('F d, Y') }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Available Until</th>
                                            <td>
                                                @if ($datedPrice->date_to)
                                                    {{ \Carbon\Carbon::parse($datedPrice->date_to)->format('F d, Y') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                               
@elseif ($facility->facility_type === 'whole_place')
    @php
        $dateFrom = \Carbon\Carbon::parse($reservationData['date_from'] ?? 'N/A');
        $dateTo = \Carbon\Carbon::parse($reservationData['date_to'] ?? 'N/A');
        $timeStartRaw = $reservationData['time_start'] ?? null;
        $timeEndRaw   = $reservationData['time_end']   ?? null;
        $timeStart = $timeStartRaw ? \Carbon\Carbon::parse($timeStartRaw) : null;
        $timeEnd = $timeEndRaw ? \Carbon\Carbon::parse($timeEndRaw) : null;
        $addons = $reservationData['addons'] ?? [];
        $addonTotal = $reservationData['addon_total'] ?? 0;
        $hasDayBasedPricing = $reservationData['has_day_based_pricing'] ?? false;
        $daysDifference = $reservationData['days_difference'] ?? 1;
        $clientTypeTotal = $reservationData['client_type_total'] ?? 0;
        $clientTypePrice = $reservationData['client_type_price'] ?? 0;
    @endphp
    
    <tr>
        <th>Date From</th>
        <td>{{ $dateFrom->format('F d, Y') }}</td>
    </tr>
    <tr>
        <th>Date To</th>
        <td>{{ $dateTo->format('F d, Y') }}</td>
    </tr>
    
    @if($timeStart && $timeEnd)
        <tr>
            <th>Start Time to End Time</th>
            <td>
                @if($timeStart->eq($timeEnd))
                    {{ $timeStart->format('h:i A') }}
                @else
                    {{ $timeStart->format('h:i A') }} to {{ $timeEnd->format('h:i A') }}
                @endif
            </td>
        </tr>
    @endif
    
    @if(!$hasDayBasedPricing)
        <tr>
            <th>Facilities Price</th>
            <td>₱{{ number_format($clientTypePrice, 2) }} × {{ $daysDifference }} {{ $daysDifference === 1 ? 'day' : 'days' }} = ₱{{ number_format($clientTypeTotal, 2) }}</td>
        </tr>
    @else
        <tr>
            <th>Facilities Price</th>
            <td>₱{{ number_format($clientTypeTotal, 2) }}</td>
        </tr>
    @endif
    
    @if(!empty($addons))
        <th><b>Add-ons</b></th>
        @foreach($addons as $addon)
            <tr>
                <th>{{ $addon['name'] }}</th>
                <td>
                    @php
                        $priceType = $addon['price_type'];
                        $basePrice = $addon['base_price'];
                        $quantity = $addon['quantity'];
                        $nights = $addon['nights'] ?? 1;
                        $isQuantityBased = $addon['is_quantity_based'] ?? false;
                        $calculatedPrice = $addon['calculated_price'];
                    @endphp

                    @if($priceType === 'per_item')
                        @if(!$hasDayBasedPricing && !$isQuantityBased)
                            {{-- Second: is_based_on_days = false, is_based_on_quantity = false --}}
                            {{ $nights }} {{ $nights === 1 ? 'day' : 'days' }} × ₱{{ number_format($basePrice, 2) }} = ₱{{ number_format($calculatedPrice, 2) }}
                        @elseif(!$hasDayBasedPricing && $isQuantityBased)
                            {{-- Third: is_based_on_days = false, is_based_on_quantity = true --}}
                            {{ $quantity }} {{ $quantity === 1 ? 'item' : 'items' }} × ₱{{ number_format($basePrice, 2) }} × {{ $nights }} {{ $nights === 1 ? 'day' : 'days' }} = ₱{{ number_format($calculatedPrice, 2) }}
                        @elseif($hasDayBasedPricing && !$isQuantityBased)
                            {{-- Fourth: is_based_on_days = true, is_based_on_quantity = false --}}
                            ₱{{ number_format($calculatedPrice, 2) }}
                        @elseif($hasDayBasedPricing && $isQuantityBased)
                            {{-- Fifth: is_based_on_days = true, is_based_on_quantity = true --}}
                            {{ $quantity }} {{ $quantity === 1 ? 'item' : 'items' }} × ₱{{ number_format($basePrice, 2) }} = ₱{{ number_format($calculatedPrice, 2) }}
                        @endif

                    @elseif($priceType === 'flat_rate')
                        @if(!$hasDayBasedPricing)
                            {{-- First: is_based_on_days = false --}}
                            {{ $nights }} {{ $nights === 1 ? 'day' : 'days' }} × ₱{{ number_format($basePrice, 2) }} = ₱{{ number_format($calculatedPrice, 2) }}
                        @else
                            {{-- Second: is_based_on_days = true --}}
                            ₱{{ number_format($calculatedPrice, 2) }}
                        @endif

                    @elseif($priceType === 'per_unit')
                        @if(!$hasDayBasedPricing)
                            {{-- First: is_based_on_days = false --}}
                            {{ $nights }} {{ $nights === 1 ? 'day' : 'days' }} × ₱{{ number_format($basePrice, 2) }} = ₱{{ number_format($calculatedPrice, 2) }}
                        @else
                            {{-- Second: is_based_on_days = true --}}
                            ₱{{ number_format($calculatedPrice, 2) }}
                        @endif

                    @elseif($priceType === 'per_night')
                        {{-- Per night logic: base_price * nights --}}
                        {{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }} × ₱{{ number_format($basePrice, 2) }} = ₱{{ number_format($calculatedPrice, 2) }}

                    @else
                        {{-- Default fallback --}}
                        ₱{{ number_format($calculatedPrice, 2) }}
                    @endif
                </td>
            </tr>
        @endforeach
        
        <tr>
            <th>Total Add-ons</th>
            <td>₱{{ number_format($addonTotal, 2) }}</td>
        </tr>
    @endif
                                @elseif($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) 

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
                                        <td>{{ $date_from ? Carbon\Carbon::parse($date_from)->format('F d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date To</th>
                                        <td>{{ $date_to ? Carbon\Carbon::parse($date_to)->format('F d, Y') : 'N/A' }}</td>
                                    </tr>
                                    
                                    @if($reservationData['booking_type'] === 'shared' && !empty($quantityDetails))
                                        @foreach($quantityDetails as $detail)
                                            <tr>
                                                <th>{{ $detail['name'] }}</th>
                                                <td>{{ $detail['quantity'] }} × ₱{{ number_format($detail['price'], 2) }} = ₱{{ number_format($detail['total'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    
                                    @if(isset($selectedPriceDetails))
                                        <tr>
                                            <th>{{ $selectedPriceDetails['name'] }}</th>
                                            <td>
                                                @if($selectedPriceDetails['days'] > 1)
                                                    {{ $selectedPriceDetails['days'] }} days × ₱{{ number_format($selectedPriceDetails['price'], 2) }} = ₱{{ number_format($selectedPriceDetails['total'], 2) }}
                                                @else
                                                    ₱{{ number_format($selectedPriceDetails['total'], 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                @elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
                                    @php
                                        $priceForDates = $facility->prices
                                            ->where('price_type', $reservationData['booking_type'] === 'shared' ? 'individual' : 'whole')
                                            ->first();
                                        
                                        $displayDateFrom = $date_from ?: ($priceForDates->date_from ?? null);
                                        $displayDateTo = $date_to ?: ($priceForDates->date_to ?? null);
                                    @endphp
                                                                    
                                    <tr>
                                        <th>Booking Type</th>
                                        <td>{{ ucfirst($reservationData['booking_type']) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date From</th>
                                        <td>{{ $displayDateFrom ? Carbon\Carbon::parse($displayDateFrom)->format('F d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date To</th>
                                        <td>{{ $displayDateTo ? Carbon\Carbon::parse($displayDateTo)->format('F d, Y') : 'N/A' }}</td>
                                    </tr>
                                    
                                    @if($reservationData['booking_type'] === 'whole' && $time_start && $time_end)
                                    <tr>
                                        <th>Time</th>
                                        <td>
                                            {{ Carbon\Carbon::parse($time_start)->format('h:i A') }} - 
                                            {{ Carbon\Carbon::parse($time_end)->format('h:i A') }}
                                        </td>
                                    </tr>
                                    @endif
                                    
                                    @if(!empty($selectedPriceDetails))
                                    <tr>
                                        <th>Price Option</th>
                                        <td>{{ $selectedPriceDetails['name'] }}: ₱{{ number_format($selectedPriceDetails['price'], 2) }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(!empty($quantityDetails))
                                        @foreach($quantityDetails as $item)
                                        <tr>
                                            <th>{{ $item['name'] }} (x{{ $item['quantity'] }})</th>
                                            <td>₱{{ number_format($item['total'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                @endif
                                
                                {{-- Refundable section --}}
                                @php
                                    $displayAddons = \App\Models\Addon::where('facility_id', $facility->id)
                                        ->where('price_type', 'flat_rate')
                                        ->where('show', 'both')
                                        ->where('is_refundable', true)
                                        ->get();
                                @endphp

                                @if($displayAddons->isNotEmpty())

                                  <th><b>Refundable Fee's</b></th>
                                    @foreach($displayAddons as $addon)
                                        <tr>
                                            <th>{{ $addon->name }}</th>
                                            <td>₱{{ number_format($addon->base_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    
                                    @php
                                        $addonsTotal = $displayAddons->sum('base_price');
                                        $finalTotal = ($reservationData['total_price'] ?? 0) + $addonsTotal;
                                    @endphp
                                    
                                    <tr class="total-row">
                                        <td colspan="2" class="text-end">
                                            <strong>Total Price: ₱{{ number_format($finalTotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="total-row">
                                        <td colspan="2"class="text-end">
                                            <strong>Total Price: ₱{{ number_format($reservationData['total_price'] ?? 0, 2) }}</strong>
                                        </td>
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

                        @if ($facility->facility_type === 'individual')
                            <input type="hidden" name="facility_attribute_id"
                                value="{{ $facilityAttribute->id ?? $reservationData['facility_attribute_id'] }}">
                            <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                            <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                       @elseif($facility->facility_type === 'whole_place')
    <div>
        <input type="hidden" id="date_from" name="date_from" value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
        <input type="hidden" id="date_to" name="date_to" value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
        <input type="hidden" name="addon_total" value="{{ $reservationData['addon_total'] ?? 0 }}">
        <input type="hidden" name="days_difference" value="{{ $reservationData['days_difference'] ?? 1 }}">
        <input type="hidden" name="has_day_based_pricing" value="{{ $reservationData['has_day_based_pricing'] ? '1' : '0' }}">
        <input type="hidden" name="client_type_total" value="{{ $reservationData['client_type_total'] ?? 0 }}">
        <input type="hidden" name="client_type_subtotal" value="{{ $reservationData['client_type_subtotal'] ?? 0 }}">
        <input type="hidden" name="base_price_per_day" value="{{ $reservationData['price'] ?? 0 }}">
        
        @if(!empty($reservationData['addons']))
            @foreach($reservationData['addons'] as $index => $addon)
                <input type="hidden" name="addons[{{ $index }}][addon_id]" value="{{ $addon['addon_id'] }}">
                <input type="hidden" name="addons[{{ $index }}][name]" value="{{ $addon['name'] }}">
                <input type="hidden" name="addons[{{ $index }}][price_type]" value="{{ $addon['price_type'] }}">
                <input type="hidden" name="addons[{{ $index }}][base_price]" value="{{ $addon['base_price'] }}">
                <input type="hidden" name="addons[{{ $index }}][quantity]" value="{{ $addon['quantity'] }}">
                <input type="hidden" name="addons[{{ $index }}][nights]" value="{{ $addon['nights'] }}">
                <input type="hidden" name="addons[{{ $index }}][calculated_price]" value="{{ $addon['calculated_price'] }}">
                <input type="hidden" name="addons[{{ $index }}][is_quantity_based]" value="{{ $addon['is_quantity_based'] ? '1' : '0' }}">
                <input type="hidden" name="addons[{{ $index }}][capacity]" value="{{ $addon['capacity'] }}">
            @endforeach
        @endif
        
        <div id="selected-date">
            <input type="hidden" name="time_start" value="{{ $reservationData['time_start'] ?? '' }}">
            <input type="hidden" name="time_end" value="{{ $reservationData['time_end'] ?? '' }}">
        </div>
    </div>
                        @elseif($facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty()) 
                            <div>
                                <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">
                                <input type="hidden" name="booking_type" value="{{ $reservationData['booking_type'] }}">
                                
                                @if(isset($reservationData['facility_attribute_id']))
                                    <input type="hidden" name="facility_attribute_id" value="{{ $reservationData['facility_attribute_id'] }}">
                                @endif
                                
                                <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                                <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                                <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">
                                
                                @if($reservationData['booking_type'] === 'shared')
                                    @if(isset($reservationData['internal_quantity']) && is_array($reservationData['internal_quantity']))
                                        @foreach($reservationData['internal_quantity'] as $priceId => $quantity)
                                            <input type="hidden" name="internal_quantity[{{ $priceId }}]" value="{{ $quantity }}">
                                        @endforeach
                                    @endif
                                    
                                    @if(isset($reservationData['price_values']) && is_array($reservationData['price_values']))
                                        @foreach($reservationData['price_values'] as $priceId => $value)
                                            <input type="hidden" name="price_values[{{ $priceId }}]" value="{{ $value }}">
                                        @endforeach
                                    @endif
                                    
                                    @if(isset($reservationData['price_names']) && is_array($reservationData['price_names']))
                                        @foreach($reservationData['price_names'] as $priceId => $name)
                                            <input type="hidden" name="price_names[{{ $priceId }}]" value="{{ $name }}">
                                        @endforeach
                                    @endif
                                    
                                    @if(isset($reservationData['price_id']))
                                        <input type="hidden" name="price_id" value="{{ $reservationData['price_id'] }}">
                                    @endif
                                    
                                    @if(isset($reservationData['selected_price']))
                                        <input type="hidden" name="selected_price" value="{{ $reservationData['selected_price'] }}">
                                    @endif
                                @endif
                                
                                @if($reservationData['booking_type'] === 'whole')
                                    @if(isset($reservationData['selected_room_id']))
                                        <input type="hidden" name="selected_room_id" value="{{ $reservationData['selected_room_id'] }}">
                                    @endif
                                    
                                    @if(isset($reservationData['whole_price_id']))
                                        <input type="hidden" name="whole_price_id" value="{{ $reservationData['whole_price_id'] }}">
                                    @endif
                                    
                                    @if(isset($reservationData['selected_whole_price']))
                                        <input type="hidden" name="selected_whole_price" value="{{ $reservationData['selected_whole_price'] }}">
                                    @endif
                                    
                                    @if(isset($reservationData['time_start']))
                                        <input type="hidden" name="time_start" value="{{ $reservationData['time_start'] }}">
                                    @endif
                                    
                                    @if(isset($reservationData['time_end']))
                                        <input type="hidden" name="time_end" value="{{ $reservationData['time_end'] }}">
                                    @endif
                                @endif
                                
                                <input type="hidden" name="room_name" value="{{ $reservationData['room_name'] }}">
                                <input type="hidden" name="room_capacity" value="{{ $reservationData['room_capacity'] }}">
                            </div>
                        @elseif ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
                            <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                            <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">
                            <input type="hidden" name="booking_type" value="{{ $reservationData['booking_type'] }}">
                            <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">
                            
                            @if($reservationData['booking_type'] === 'shared')
                                @php
                                    $finalDateFrom = $date_from ?: ($priceForDates->date_from ?? null);
                                    $finalDateTo = $date_to ?: ($priceForDates->date_to ?? null);
                                @endphp
                                
                                <input type="hidden" name="date_from" value="{{ $finalDateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $finalDateTo }}">
                                <input type="hidden" name="facility_attribute_id" value="{{ $reservationData['facility_attribute_id'] ?? '' }}">
                                
                                @if(!empty($reservationData['price_id']))
                                    <input type="hidden" name="price_id" value="{{ $reservationData['price_id'] }}">
                                    <input type="hidden" name="selected_price" value="{{ $reservationData['selected_price'] ?? '' }}">
                                @endif
                                
                                @if(!empty($reservationData['internal_quantity']))
                                    @foreach($reservationData['internal_quantity'] as $priceId => $quantity)
                                        <input type="hidden" name="internal_quantity[{{ $priceId }}]" value="{{ $quantity }}">
                                        <input type="hidden" name="price_values[{{ $priceId }}]" value="{{ $reservationData['price_values'][$priceId] ?? '' }}">
                                        <input type="hidden" name="price_names[{{ $priceId }}]" value="{{ $reservationData['price_names'][$priceId] ?? '' }}">
                                    @endforeach
                                @endif
                                
                            @elseif($reservationData['booking_type'] === 'whole')
                                @php
                                    $finalDateFrom = $date_from ?: ($priceForDates->date_from ?? null);
                                    $finalDateTo = $date_to ?: ($priceForDates->date_to ?? null);
                                @endphp
                                
                                <input type="hidden" name="date_from" value="{{ $finalDateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $finalDateTo }}">
                                <input type="hidden" name="facility_attribute_id" value="{{ $reservationData['facility_attribute_id'] ?? '' }}">
                                
                                @if($time_start)
                                    <input type="hidden" name="time_start" value="{{ $time_start }}">
                                @endif
                                
                                @if($time_end)
                                    <input type="hidden" name="time_end" value="{{ $time_end }}">
                                @endif
                                
                                @if(!empty($reservationData['whole_client_type']))
                                    <input type="hidden" name="whole_client_type" value="{{ $reservationData['whole_client_type'] }}">
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
                                        $fileExtension = strtolower(pathinfo($facility->requirements, PATHINFO_EXTENSION));
                                        $isImageReq = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                                    @endphp
                                    <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" 
                                    download class="download-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Download Requirements
                                    </a>
                                @else
                                    <p style="color: var(--text-light); margin-bottom: var(--space-md);">No requirements document available</p>
                                @endif
                                @error('requirements')
                                    <div class="error-text">{{ $message }}</div>
                                @enderror

                                <div class="file-upload-wrapper">
                                    <label for="qualification" class="file-upload-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="file-upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="file-upload-text">
                                            <span class="file-upload-title">Upload Qualification Document</span>
                                            <span class="file-upload-subtitle">PDF or DOC files (Max 10MB)</span>
                                        </span>
                                        <input type="file" id="qualification" name="qualification" 
                                            class="file-upload-input"
                                            accept=".pdf,.doc,.docx" 
                                            required>
                                    </label>
                                    <div class="file-upload-preview" id="file-preview">
                                        <span class="file-upload-name" id="file-name">No file selected</span>
                                        <button type="button" class="file-upload-clear" id="clear-file" aria-label="Remove file">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('qualification')
                                        <div class="file-upload-error">{{ $message }}</div>
                                    @enderror
                                </div>



                            </div>
                        </div>

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
                const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
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

        document.querySelector('form[name="checkout-form"]').addEventListener('submit', function(e) {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
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