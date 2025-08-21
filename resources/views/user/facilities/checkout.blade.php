@extends('layouts.app')
@section('content')
    {{-- <style>
        :root {
            --primary: #3B82F6;
            --primary-light: #EFF6FF;
            --primary-dark: #1D4ED8;
            --text: #1F2937;
            --text-light: #6B7280;
            --text-lighter: #9CA3AF;
            --border: #E5E7EB;
            --surface: #FFFFFF;
            --background: #F9FAFB;
            --success: #10B981;
            --error: #EF4444;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --space-xxs: 4px;
            --space-xs: 8px;
            --space-sm: 12px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
            --space-xxl: 48px;
        }

        .checkout-container {
            background-color: var(--background);
            min-height: 100vh;
            padding: var(--space-xl) 0;
        }

        .checkout-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 var(--space-md);
        }

        .checkout-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: var(--space-lg);
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: 0.2px;
        }

        .card-body {
            padding: var(--space-xl);
        }

        .section {
            margin-bottom: var(--space-xl);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-sm);
            border-bottom: 1px solid var(--border);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .section-title svg {
            width: 18px;
            height: 18px;
            color: var(--primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-sm);
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: var(--space-xxs);
            font-weight: 500;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td {
            padding: var(--space-sm) 0;
            text-align: left;
            vertical-align: top;
        }

        .summary-table th {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-light);
        }

        .summary-table td {
            font-size: 15px;
            text-align: right;
        }

        .summary-table tr:not(:last-child) {
            border-bottom: 1px solid var(--border);
        }

        .total-row {
            background: var(--primary-light);
        }

        .total-row td {
            font-weight: 600;
            color: var(--primary-dark);
            padding: var(--space-sm) var(--space-md);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: var(--space-xl) 0;
        }

        .requirements-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: var(--space-lg);
        }

        .requirements-header {
            background: var(--primary-light);
            padding: var(--space-md);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 15px;
        }

        .requirements-body {
            padding: var(--space-md);
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            margin-bottom: var(--space-md);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .download-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .download-btn svg {
            width: 16px;
            height: 16px;
        }

        .file-upload {
            margin-top: var(--space-lg);
        }

        .file-upload label {
            display: block;
            margin-bottom: var(--space-xs);
            font-weight: 500;
            font-size: 14px;
            color: var(--text);
        }

        .file-input {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .file-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .submit-btn {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: var(--space-md);
            font-size: 16px;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: var(--space-md);
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }

        .error-text {
            color: var(--error);
            font-size: 13px;
            margin-top: var(--space-xxs);
            display: flex;
            align-items: center;
            gap: var(--space-xxs);
        }

        @media (max-width: 768px) {
            .checkout-container {
                padding: var(--space-lg) 0;
            }
            
            .card-body {
                padding: var(--space-lg);
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .section {
                margin-bottom: var(--space-lg);
            }
            
            .divider {
                margin: var(--space-lg) 0;
            }
        }

        /* Style for Uploading */
            .file-upload-wrapper {
            margin-top: 1.5rem;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-label:hover {
            border-color: #3b82f6;
            background-color: #f0f7ff;
        }

        .file-upload-icon {
            width: 2.5rem;
            height: 2.5rem;
            color: #3b82f6;
        }

        .file-upload-text {
            display: flex;
            flex-direction: column;
        }

        .file-upload-title {
            font-weight: 600;
            color: #111827;
        }

        .file-upload-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-preview {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding: 0.75rem 1rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
        }

        .file-upload-name {
            font-size: 0.875rem;
            color: #374151;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-upload-clear {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            border: none;
            background: none;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-clear:hover {
            color: #ef4444;
        }

        .file-upload-clear svg {
            width: 1rem;
            height: 1rem;
        }

        .file-upload-error {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #ef4444;
        }
    </style> --}}

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
                                    @endphp

                                    <tr>
                                        <th>Price</th>
                                        <td>₱{{ number_format($reservationData['price'] ?? 0, 2) }}</td>
                                    </tr>
                                
                                    <tr>
                                        <th>Date From</th>
                                        <td>
                                            {{ $dateFrom->format('F d, Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date To</th>
                                        <td>
                                            {{ $dateTo->format('F d, Y') }}
                                        </td>
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
                                    
                                    {{-- @if($reservationData['booking_type'] === 'whole')
                                        @if(isset($reservationData['time_start']) && isset($reservationData['time_end']))
                                            <tr>
                                                <th>Time</th>
                                                <td>{{ $reservationData['time_start'] }} - {{ $reservationData['time_end'] }}</td>
                                            </tr>
                                        @endif
                                    @endif --}}

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
                                
                                <tr class="total-row">
                                    <td colspan="2">
                                        <strong>Total Price: ₱{{ number_format($reservationData['total_price'] ?? 0, 2) }}</strong>
                                    </td>
                                </tr>
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