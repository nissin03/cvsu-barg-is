
@php
    $filteredAttributes = $facility->facilityAttributes->filter(function($attribute) {
        if (is_null($attribute->sex_restriction)) {
            return true;
        }
        return $attribute->sex_restriction === auth()->user()->sex;
    });
    
    $isBasedOnDays = $facility->prices->where('is_based_on_days', true)->count() > 0;
    
    $datedPriceWithQuantity = $facility->prices->first(function($price) {
        return $price->is_there_a_quantity && 
            $price->is_based_on_days && 
            ($price->date_from || $price->date_to);
    });
    
    $datedPrice = $facility->prices->first(function($price) {
        return !$price->is_there_a_quantity && 
            $price->is_based_on_days && 
            ($price->date_from || $price->date_to);
    });
@endphp

<div class="facility-booking-container mb-4">
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-door-open"></i>
            <span>Available Rooms</span>
        </div>
        <div class="section-content">
            @if($isBasedOnDays)
                @php
                    $hasAvailableRooms = false;
                    $availableRoom = $filteredAttributes->first(function($attribute) {
                        $availability = $attribute->availabilities->sortByDesc('id')->first();
                        $displayCapacity = $availability ? $availability->remaining_capacity : $attribute->capacity;
                        return $displayCapacity > 0;
                    });
                @endphp
                
                @if($availableRoom)
                    @php
                        $hasAvailableRooms = true;
                        $availability = $availableRoom->availabilities->sortByDesc('id')->first();
                        $displayCapacity = $availability ? $availability->remaining_capacity : $availableRoom->capacity;
                    @endphp
                    <div class="capacity-info mb-2">
                        <div class="capacity-card">
                            <i class="fa fa-door-open"></i>
                            <span class="capacity-text">{{ $availableRoom->room_name }}</span>
                            @if($availableRoom->sex_restriction)
                                <span class="badge bg-info ms-2">
                                    <i class="fa fa-{{ $availableRoom->sex_restriction === 'male' ? 'mars' : 'venus' }} me-1"></i>
                                    {{ ucfirst($availableRoom->sex_restriction) }} Only
                                </span>
                            @endif
                            <span class="capacity-value">{{ $displayCapacity }} person(s)</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="facility_attribute_id" value="{{ $availableRoom->id }}">
                    <input type="hidden" name="room_name" value="{{ $availableRoom->room_name }}">
                    <input type="hidden" name="room_capacity" value="{{ $displayCapacity }}">
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <div>No rooms with available capacity at the moment.</div>
                    </div>
                @endif
            @else
                @if($filteredAttributes->isNotEmpty())
                    <div class="time-input-group mb-3">
                        <label for="room_selection" class="time-label">Select Room</label>
                        <select name="facility_attribute_id" id="room_selection" class="client-type-select" onchange="updateRoomInfo()">
                            <option value="">Select a Room</option>
                            @foreach($filteredAttributes as $attribute)
                                <option value="{{ $attribute->id }}" 
                                        data-room-name="{{ $attribute->room_name }}" 
                                        data-capacity="{{ $attribute->capacity }}"
                                        data-sex-restriction="{{ $attribute->sex_restriction }}">
                                    {{ $attribute->room_name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="selected-room-info" class="capacity-info" style="display: none;">
                        <div class="capacity-card">
                            <i class="fa fa-door-open"></i>
                            <span class="capacity-text" id="selected-room-name"></span>
                            <span id="selected-sex-badge" class="badge bg-info ms-2" style="display: none;">
                                <i id="selected-sex-icon" class="me-1"></i>
                                <span id="selected-sex-text"></span> Only
                            </span>
                            <span id="selected-capacity" class="capacity-value"></span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="room_name" id="hidden_room_name" value="">
                    <input type="hidden" name="room_capacity" id="hidden_room_capacity" value="">
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <div>No rooms available for your profile.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0 || 
         $facility->prices->where('is_there_a_quantity', '!=', 1)->isNotEmpty())
        @if ($datedPriceWithQuantity || $datedPrice)
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-calendar-alt"></i>
                    <span>Available Date Range</span>
                </div>
                <div class="section-content">
                    <div class="date-range-display">
                        <div class="date-item">
                            <strong>From:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from ?? $datedPrice->date_from)->format('F d, Y') }}
                        </div>
                        <div class="date-item">
                            <strong>To:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to ?? $datedPrice->date_to)->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-calendar-alt"></i>
                    <span>Select Dates</span>
                </div>
                <div class="section-content">
                    <input type="hidden" id="date_from" name="date_from" required>
                    <input type="hidden" id="date_to" name="date_to" required>
                    
                    <div class="selected-dates-display mb-3">
                        <div class="date-selection-item">
                            <strong>Start Date:</strong> 
                            <span id="start-date-display"></span>
                        </div>
                        <div class="date-selection-item">
                            <strong>End Date:</strong> 
                            <span id="end-date-display"></span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#calendarModal">
                        <i class="fa fa-calendar me-2"></i> Open Calendar
                    </button>
                </div>
            </div>
            
            <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div id="calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="selected-dates-card p-3">
                                            <h6 class="fw-bold mb-3">Selected Dates</h6>
                                            <div class="mb-2">
                                                <small class="text-muted">Start Date:</small>
                                                <div id="modal-start-date" class="fw-bold"></div>
                                            </div>
                                            <div class="mb-3">
                                                <small class="text-muted">End Date:</small>
                                                <div id="modal-end-date" class="fw-bold"></div>
                                            </div>
                                            <div class="d-grid gap-2">
                                               <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="confirm-dates">
                                                        Confirm Selection
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calculator"></i>
                <span>Quantity Selection</span>
            </div>
            <div class="section-content">
                @foreach ($facility->prices as $price)
                    @if ($price->is_there_a_quantity)  
                        <input type="hidden" name="price_values[{{ $price->id }}]" value="{{ $price->value }}">
                        <input type="hidden" name="price_names[{{ $price->id }}]" value="{{ $price->name }}">
                        <div class="time-input-group mb-3">
                            <label for="internal_quantity-{{ $price->id }}" class="time-label">{{ $price->name }} (₱{{ number_format($price->value, 2) }})</label>
                            <input id="internal_quantity-{{ $price->id }}" type="number" 
                                class="time-input quantity-input"
                                name="internal_quantity[{{ $price->id }}]" value="{{ old('internal_quantity.' . $price->id) }}" min="0" step="1" onchange="updateTotalPrice()">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($facility->prices->where('is_there_a_quantity', '!=', 1)->isNotEmpty())
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-tags"></i>
                <span>Price Options</span>
            </div>
            <div class="section-content">
                <div class="time-input-group">
                    {{-- <label for="price_id" class="time-label">Select Price Option</label> --}}
                    <select name="price_id" id="price_id" class="client-type-select" onchange="updateTotalPrice()">
                        <option value="">Select Price</option>
                        @foreach ($facility->prices as $price)
                            @if (!$price->is_there_a_quantity)
                                <option value="{{ $price->id }}" data-value="{{ $price->value }}">
                                    {{ $price->name }} - ₱{{ number_format($price->value, 2) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="selected_price_value" name="selected_price" value="">
            </div>
        </div>
    @endif

    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price:  </strong>
        <span id="computed-total" class="total-price-value">₱ 0.00</span>
    </div>
</div>


{{-- Style for UI --}}
<style>
    .facility-booking-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 16px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
    .fc-capacity-badge {
        position: absolute !important;
        bottom: -10px !important;
        left: 50% !important;
        transform: translateX(-85%) !important;
        font-size: 9px !important;
        color: white !important;
        padding: 2px 6px !important;
        border-radius: 10px !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        z-index: 10 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }

    .fc-capacity-success {
        background: #198754 !important;
        background: linear-gradient(135deg, #198754, #157347) !important;
    }

    .fc-capacity-warning {
        background: #ffc107 !important;
        background: linear-gradient(135deg, #ffc107, #e0a800) !important;
        color: #000 !important;
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% { opacity: 0.9; }
        50% { opacity: 1; }
        100% { opacity: 0.9; }
    }

    .fc-status-booked {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        color: white !important;
        left: 2px !important;
        transform: translateX(-85%) !important;
        font-size: 9px !important;
        position: absolute !important;
        bottom: -10px !important;
        padding: 2px 6px !important;
        border-radius: 10px !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        z-index: 10 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        animation: pulse-booked 1.5s infinite ease-in-out;
    }

    @keyframes pulse-booked {
        0% { opacity: 0.9; }
        50% { opacity: 1; }
        100% { opacity: 0.9; }
    }

    .alert {
        border-radius: 6px;
        border: none;
        padding: 12px;
        margin-bottom: 16px;
        font-weight: 500;
    }

    .alert-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .capacity-info {
        margin-bottom: 16px;
    }

    .capacity-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        gap: 8px;
    }

    .capacity-card i {
        color: #3b82f6;
        font-size: 16px;
    }

    .capacity-text {
        font-weight: 600;
        color: #1e293b;
    }

    .capacity-value {
        background: #3b82f6;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
    }

    .booking-section {
        margin-bottom: 16px;
        background: #ffffff;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .section-header {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        gap: 8px;
    }

    .section-header i {
        color: #3b82f6;
        font-size: 14px;
    }

    .section-header span {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    .section-content {
        padding: 16px;
    }

    .date-range-display {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .date-item {
        flex: 1;
        min-width: 150px;
        color: #1e293b;
        font-size: 14px;
    }

    .selected-dates-display {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .date-selection-item {
        flex: 1;
        min-width: 150px;
        color: #1e293b;
        font-size: 14px;
    }

    .time-input-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 12px;
    }

    .time-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .time-input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.2s ease;
        background: #ffffff;
    }

    .time-input:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .time-input:read-only {
        background-color: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }

    .client-type-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
        background: #ffffff;
        transition: border-color 0.2s ease;
    }

    .client-type-select:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .total-price-section {
        background: #5a5b5d;
        border-radius: 6px;
        padding: 16px;
        margin-top: 16px;
    }

    .total-price-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-price-label {
        display: flex;
        align-items: center;
        color: #e2e8f0;
        font-size: 14px;
        font-weight: 600;
        gap: 8px;
    }

    .total-price-label i {
        color: #60a5fa;
        font-size: 16px;
    }

    .total-price-value {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 4px;
    }

    .bg-info {
        background-color: #3b82f6;
        color: white;
    }

    .bg-success {
        background-color: #10b981;
        color: white;
    }

    .quantity-input {
        width: 100%;
    }

    .selected-dates-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        height: 100%;
    }
    
    .fc-daygrid-day-number {
        font-size: 12px;
    }
    
    .availability-indicator .badge {
        font-size: 9px;
        padding: 2px 4px;
    }
    
    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .selected-start-date {
        background-color: rgba(25, 135, 84, 0.3) !important;
    }
    
    .selected-end-date {
        background-color: rgba(25, 135, 84, 0.3) !important;
    }
    
    .selected-range-date {
        background-color: rgba(25, 135, 84, 0.15) !important;
    }
    
    .fully-booked-date {
        background-color: rgba(220, 53, 69, 0.1) !important;
        position: relative;
    }
    
    .fully-booked-date::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #dc3545;
        transform: rotate(-5deg);
    }

    @media (max-width: 768px) {
        .facility-booking-container {
            padding: 12px;
        }
        
        .section-header {
            padding: 10px 12px;
        }
        
        .section-content {
            padding: 12px;
        }
        
        .date-range-display {
            flex-direction: column;
        }
        
        .selected-dates-display {
            flex-direction: column;
        }
        
        .total-price-content {
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .time-slot-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


{{-- Style for Calendar --}}
<style>
    #calendar {
        max-width: 100%;
        font-size: 14px;
        height: 600px;
    }

    .fc .fc-daygrid-day-frame {
        min-height: 70px;
        padding: 2px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .fc .fc-daygrid-day-number {
        font-size: 12px;
        padding: 2px;
        font-weight: bold;
        text-align: right;
    }

    .availability-indicator {
        position: relative;
        z-index: 5;
        text-align: center;
        margin: 2px 0;
        font-size: 10px;
    }

    .availability-badge {
        display: inline-block;
        width: 100%;
    }

    .availability-indicator .badge {
        font-size: 9px;
        padding: 2px 4px;
        font-weight: normal;
        display: inline-block;
        width: auto;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.2s ease;
    }

    .fc-daygrid-day:hover .availability-indicator .badge {
        transform: scale(1.05);
    }

    .fc .fc-daygrid-day-events {
        min-height: 40px;
        margin: 1px 0 0 0;
        overflow: visible !important;
    }

    .fc .fc-daygrid-event {
        margin: 1px;
        padding: 2px 3px;
        border-radius: 3px;
        font-size: 10px;
        line-height: 1.1;
    }

    .fc-event.fully-booked-event {
        font-size: 9px !important;
        padding: 2px 4px !important;
        white-space: nowrap !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: linear-gradient(135deg, #ff6b6b, #ee5a5a) !important;
        border: none !important;
        color: white !important;
        border-radius: 4px !important;
        box-shadow: 0 1px 3px rgba(238, 90, 90, 0.3) !important;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.2) !important;
    }

    .fc-event-title {
        font-size: 9px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block;
        text-align: center;
    }

    .fc-event-time {
        display: none !important;
    }

    .fc .fc-daygrid-more-link {
        font-size: 8px;
        padding: 0;
        margin-top: 1px;
    }

    .fc .fc-day.fully-booked-date,
    .fc .fc-daygrid-day.fully-booked-date,
    .fc-daygrid-day-frame.fully-booked-date {
        background: linear-gradient(135deg, #ff6b6b, #ee5a5a) !important;
        color: white !important;
        border: 1px solid #ff5252 !important;
        position: relative !important;
        opacity: 0.85 !important;
        transition: all 0.3s ease !important;
    }

    .fc .fc-day.fully-booked-date .fc-daygrid-day-number,
    .fc .fc-daygrid-day.fully-booked-date .fc-daygrid-day-number {
        color: white !important;
        font-weight: bold !important;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.3) !important;
    }

    .fc .fc-day.fully-booked-date:hover,
    .fc .fc-daygrid-day.fully-booked-date:hover {
        background: linear-gradient(135deg, #ff5252, #e53935) !important;
        cursor: not-allowed !important;
        opacity: 1 !important;
        transform: scale(0.98) !important;
        box-shadow: 0 2px 6px rgba(255, 82, 82, 0.3) !important;
    }

    .fc .fc-day.fully-booked-date::before {
        content: "🚫" !important;
        position: absolute !important;
        top: 1px !important;
        right: 1px !important;
        font-size: 10px !important;
        z-index: 15 !important;
        filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.3)) !important;
    }

    .fc .fc-day.fully-booked-date::after,
    .fc .fc-daygrid-day.fully-booked-date::after,
    .fc-daygrid-day-frame.fully-booked-date::after {
        content: "unavailable" !important;
        position: absolute !important;
        bottom: 1px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        font-size: 8px !important;
        color: white !important;
        background: rgba(0, 0, 0, 0.7) !important;
        padding: 1px 4px !important;
        border-radius: 8px !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        letter-spacing: 0.1px !important;
        line-height: 1 !important;
        z-index: 10 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }

    .fc .fc-day.selected-start-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-start-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb) !important;
        border: 1px solid #1976d2 !important;
        box-shadow: 0 1px 6px rgba(25, 118, 210, 0.3) !important;
        transition: all 0.3s ease !important;
    }

    .fc .fc-day.selected-end-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-end-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9) !important;
        border: 1px solid #388e3c !important;
        box-shadow: 0 1px 6px rgba(56, 142, 60, 0.3) !important;
        transition: all 0.3s ease !important;
    }

    .fc .fc-day.selected-range-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-range-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #f3e5f5, #e1bee7) !important;
        border: 1px solid #7b1fa2 !important;
        box-shadow: 0 1px 3px rgba(123, 31, 162, 0.2) !important;
        transition: all 0.3s ease !important;
    }

    .fc .fc-toolbar {
        padding: 8px 0;
    }

    .fc .fc-toolbar-title {
        font-size: 1.2em;
    }

    @keyframes pulseReserved {
        0% { opacity: 0.85; }
        50% { opacity: 1; }
        100% { opacity: 0.85; }
    }

    .fc .fc-day.fully-booked-date {
        animation: pulseReserved 3s infinite ease-in-out !important;
    }

    .fc .fc-day.fully-booked-date:hover::before {
        content: "This date is fully booked" !important;
        position: absolute !important;
        top: -30px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        background: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-size: 10px !important;
        white-space: nowrap !important;
        z-index: 1000 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
        font-weight: normal !important;
        letter-spacing: 0.2px !important;
    }

    .fc .fc-day.fully-booked-date:hover::after {
        content: "" !important;
        position: absolute !important;
        top: -6px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 5px solid transparent !important;
        border-right: 5px solid transparent !important;
        border-top: 5px solid rgba(0, 0, 0, 0.9) !important;
        z-index: 1000 !important;
    }

    .fc {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }

    .fc .fc-daygrid-day {
        transition: all 0.2s ease !important;
    }

    .fc .fc-daygrid-day:hover:not(.fully-booked-date) {
        background-color: #f8f9fa !important;
        cursor: pointer !important;
    }

    @media (max-width: 768px) {
        #calendar {
            font-size: 12px;
            height: 500px;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 60px;
        }

        .fc-event.fully-booked-event {
            font-size: 8px !important;
            padding: 1px 3px !important;
        }

        .availability-indicator .badge {
            font-size: 8px;
            padding: 1px 3px;
        }

        .fc .fc-toolbar {
            flex-direction: column;
            gap: 8px;
        }

        .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }

        .fc .fc-daygrid-day-number {
            font-size: 10px !important;
        }
    }
</style>


{{-- All of java --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dateFromInput = document.getElementById('date_from');
        var dateToInput = document.getElementById('date_to');
        var startDateDisplay = document.getElementById('start-date-display');
        var endDateDisplay = document.getElementById('end-date-display');
        var modalStartDate = document.getElementById('modal-start-date');
        var modalEndDate = document.getElementById('modal-end-date');
        var clearDatesBtn = document.getElementById('clear-dates');
        
        var availabilities = @json($facility->availabilities ?? []);
        var facilityAttributes = @json($facility->facilityAttributes ?? []);
        var isBasedOnDays = @json($isBasedOnDays);
        
        let selectedDates = [];
        let startDate = null;
        let endDate = null;
        
        function formatDate(dateInput) {
            if (!dateInput) return null;
            
            let dateStr;
            if (dateInput instanceof Date) {
                dateStr = dateInput.getFullYear() + '-' + 
                        String(dateInput.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(dateInput.getDate()).padStart(2, '0');
            } else if (typeof dateInput === 'string') {
                if (dateInput.includes('T')) {
                    dateStr = dateInput.split('T')[0];
                } else {
                    dateStr = dateInput;
                }
            }
            
            return dateStr;
        }

        function createLocalDate(dateStr) {
            if (!dateStr) return null;
            const parts = dateStr.split('-');
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }

        function isDateInRange(checkDate, startDate, endDate) {
            const check = formatDate(checkDate);
            const start = formatDate(startDate);
            const end = formatDate(endDate);
            
            if (!check || !start || !end) return false;
            
            return check >= start && check <= end;
        }

        function getRoomAvailabilityForDate(dateStr) {
            const selectedRoomId = document.getElementById('room_selection')?.value;
            if (!selectedRoomId) return { remaining: 0, isFullyBooked: true };
            
            const selectedRoom = facilityAttributes.find(attr => attr.id == selectedRoomId);
            const roomCapacity = selectedRoom ? selectedRoom.capacity : 0;
            
            if (!availabilities || availabilities.length === 0) {
                return {
                    remaining: roomCapacity,
                    isFullyBooked: false
                };
            }
            
            const formattedDate = formatDate(dateStr);
            
            let remainingCapacity = roomCapacity;
            let isFullyBooked = false;
            
            const matchingAvailabilities = availabilities
                .filter(avail => avail.facility_attribute_id == selectedRoomId)
                .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            
            for (const avail of matchingAvailabilities) {
                if (isDateInRange(formattedDate, avail.date_from, avail.date_to)) {
                    remainingCapacity = avail.remaining_capacity;
                    isFullyBooked = remainingCapacity <= 0;
                    break;
                }
            }
            
            return {
                remaining: remainingCapacity,
                isFullyBooked: isFullyBooked
            };
        }

        function getReservedDates() {
            const selectedRoomId = document.getElementById('room_selection')?.value;
            if (!selectedRoomId || !availabilities || availabilities.length === 0) {
                return [];
            }
            
            const reservedDates = [];
            
            availabilities.forEach(function(availability) {
                if (availability.facility_attribute_id == selectedRoomId && 
                    availability.remaining_capacity <= 0 &&
                    availability.date_from && availability.date_to) {
                    
                    const startDate = createLocalDate(availability.date_from);
                    const endDate = createLocalDate(availability.date_to);
                    
                    if (!startDate || !endDate) return;
                    
                    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                        const dateStr = formatDate(d);
                        if (dateStr && !reservedDates.includes(dateStr)) {
                            reservedDates.push(dateStr);
                        }
                    }
                }
            });
            
            return reservedDates;
        }

        function getDatesInRange(start, end) {
            const dates = [];
            const startDate = createLocalDate(start);
            const endDate = createLocalDate(end);
            
            if (!startDate || !endDate) return dates;
            
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                dates.push(formatDate(d));
            }
            
            return dates;
        }

        function showRoomSelectionAlert() {
            Swal.fire({
                icon: 'info',
                title: 'Unavailable',
                text: 'Please select a room number first to view the calendar and choose available dates.',
                confirmButtonColor: '#3085d6',
            });
        }

        const calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: new Date(),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                validRange: { start: formatDate(new Date()) },
                
                dateClick: function(info) {
                    const clickedDate = info.dateStr;
                    const selectedRoomId = document.getElementById('room_selection')?.value;
                    
                    if (!selectedRoomId) {
                        showRoomSelectionAlert();
                        return;
                    }
                    
                    const roomAvailability = getRoomAvailabilityForDate(clickedDate);
                    if (roomAvailability.isFullyBooked) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unavailable',
                            text: 'This date is fully reserved and unavailable for booking.',
                            confirmButtonColor: '#3085d6',
                        });
                        return;
                    }
                    
                    if (!startDate) {
                        startDate = clickedDate;
                        selectedDates = [clickedDate];
                    } else if (!endDate && clickedDate >= startDate) {
                        endDate = clickedDate;
                        selectedDates = getDatesInRange(startDate, endDate);
                        
                        const hasReservedDate = selectedDates.some(date => {
                            return getRoomAvailabilityForDate(date).isFullyBooked;
                        });
                        
                        if (hasReservedDate) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Unavailable Dates',
                                text: 'One or more dates in your selected range are reserved.',
                                confirmButtonColor: '#3085d6',
                            });
                            endDate = null;
                            return;
                        }
                    } else {
                        startDate = clickedDate;
                        endDate = null;
                        selectedDates = [clickedDate];
                    }
                    
                    updateInputs();
                    updateDateDisplay();
                    updateTotalPrice();
                    highlightDates();
                },
                
                dayCellClassNames: function(info) {
                    const dateStr = info.dateStr;
                    const classes = [];
                    const roomAvailability = getRoomAvailabilityForDate(dateStr);
                    
                    if (roomAvailability.isFullyBooked) {
                        classes.push('fully-booked-date');
                    } else if (selectedDates.includes(dateStr)) {
                        if (dateStr === startDate) classes.push('selected-start-date');
                        else if (dateStr === endDate) classes.push('selected-end-date');
                        else classes.push('selected-range-date');
                    }
                    
                    return classes;
                },
                
                dayCellContent: function(args) {
                    const dateStr = args.dateStr || formatDate(args.date);
                    const roomAvailability = getRoomAvailabilityForDate(dateStr);
                    const selectedRoomId = document.getElementById('room_selection')?.value;
                    const selectedRoom = facilityAttributes.find(attr => attr.id == selectedRoomId);
                    const roomCapacity = selectedRoom ? selectedRoom.capacity : 0;
                    
                    const container = document.createElement('div');
                    container.style.height = '100%';
                    container.style.display = 'flex';
                    container.style.flexDirection = 'column';
                    container.style.justifyContent = 'space-between';
                    
                    const dateNumberEl = document.createElement('div');
                    dateNumberEl.className = 'fc-daygrid-day-number';
                    dateNumberEl.textContent = args.date.getDate();
                    dateNumberEl.style.textAlign = 'right';
                    dateNumberEl.style.padding = '2px';
                    
                    const availabilityEl = document.createElement('div');
                    availabilityEl.className = 'availability-indicator';
                    availabilityEl.style.textAlign = 'center';
                    availabilityEl.style.margin = '2px 0';
                    availabilityEl.style.fontSize = '10px';
                    
                    if (!selectedRoomId) {
                        availabilityEl.innerHTML = '<span class="badge bg-secondary">Select room</span>';
                    } 
                    else if (roomAvailability.isFullyBooked) {
                        availabilityEl.innerHTML = '<span class="fc-status-booked">Booked</span>';
                    } 
                    else {
                        availabilityEl.innerHTML = `
                            <span class="fc-capacity-badge 
                                ${roomAvailability.remaining < 3 ? 'fc-capacity-warning' : 'fc-capacity-success'}">
                                ${roomAvailability.remaining}/${roomCapacity} left
                            </span>
                        `;
                    }
                    
                    container.appendChild(dateNumberEl);
                    container.appendChild(availabilityEl);
                    
                    return { domNodes: [container] };
                },
                
                events: function(fetchInfo, successCallback, failureCallback) {
                    const reservedDates = getReservedDates();
                    const events = reservedDates.map(date => ({}));
                    successCallback(events);
                }
            });
            
            window.calendar = calendar;
            calendar.render();

            $('#calendarModal').on('shown.bs.modal', function() {
                if (window.calendar) {
                    window.calendar.gotoDate(new Date()); 
                }
            });
            
            function updateDateDisplay() {
                const formattedStart = startDate ? new Date(startDate).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '';
                
                const formattedEnd = endDate ? new Date(endDate).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '';
                
                startDateDisplay.textContent = formattedStart;
                endDateDisplay.textContent = formattedEnd;
                modalStartDate.textContent = formattedStart;
                modalEndDate.textContent = formattedEnd;
                
                confirmDatesBtn.disabled = !startDate;
            }
            
            function updateInputs() {
                if (dateFromInput) dateFromInput.value = startDate || '';
                if (dateToInput) dateToInput.value = endDate || '';
            }
            
            function highlightDates() {
                calendar.refetchEvents();
                calendar.render();
            }
            
        
                
        
        }
    });

    function updateRoomInfo() {
        const select = document.getElementById('room_selection');
        const selectedOption = select.options[select.selectedIndex];
        const roomInfoDiv = document.getElementById('selected-room-info');
        
        if (select.value) {
            const roomName = selectedOption.getAttribute('data-room-name');
            const capacity = selectedOption.getAttribute('data-capacity');
            const sexRestriction = selectedOption.getAttribute('data-sex-restriction');
            
            document.getElementById('selected-room-name').textContent = roomName;
            document.getElementById('selected-capacity').textContent = capacity + ' person(s)';
            
            document.getElementById('hidden_room_name').value = roomName;
            document.getElementById('hidden_room_capacity').value = capacity;
            
            const sexBadge = document.getElementById('selected-sex-badge');
            const sexIcon = document.getElementById('selected-sex-icon');
            const sexText = document.getElementById('selected-sex-text');
            
            if (sexRestriction) {
                sexIcon.className = 'fa fa-' + (sexRestriction === 'male' ? 'mars' : 'venus') + ' me-1';
                sexText.textContent = sexRestriction.charAt(0).toUpperCase() + sexRestriction.slice(1);
                sexBadge.style.display = 'inline-block';
            } else {
                sexBadge.style.display = 'none';
            }
            
            roomInfoDiv.style.display = 'flex';
        } else {
            roomInfoDiv.style.display = 'none';
            document.getElementById('hidden_room_name').value = '';
            document.getElementById('hidden_room_capacity').value = '';
        }
        
        if (window.calendar) {
            window.calendar.refetchEvents();
            window.calendar.render();
        }
    }

    function updateTotalPrice() {
        let totalPrice = 0;
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            const priceText = input.closest('.form-floating').querySelector('.product-type').textContent;
            const priceValue = parseFloat(priceText.replace(/[^0-9.]/g, ''));

            if (!isNaN(priceValue)) {
                let itemTotal = priceValue * quantity;
                const dateFrom = document.getElementById('date_from');
                const dateTo = document.getElementById('date_to');

                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    const startDate = new Date(dateFrom.value);
                    const endDate = new Date(dateTo.value);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    itemTotal = itemTotal * diffDays;
                }
                totalPrice += itemTotal;
            }
        });
        
        const priceSelect = document.getElementById('price_id');
        if (priceSelect && priceSelect.value) {
            const selectedOption = priceSelect.options[priceSelect.selectedIndex];
            const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
            
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                const startDate = new Date(dateFrom.value);
                const endDate = new Date(dateTo.value);
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalPrice += selectedPrice * diffDays;
            } else {
                totalPrice += selectedPrice;
            }
        }
        
        const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('computed-total').textContent = formattedTotal;
        document.getElementById('total_price_input').value = totalPrice.toFixed(2);
    }

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateTotalPrice);
    });

    const priceSelect = document.getElementById('price_id');
    if (priceSelect) {
        priceSelect.addEventListener('change', function() {
            document.getElementById('selected_price_value').value = 
                this.options[this.selectedIndex].getAttribute('data-value');
            updateTotalPrice();
        });
    }
</script>

{{-- Script for validation --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reserveBtn = document.getElementById('reserve-btn');
        const noRoomsAlert = document.querySelector('.alert.alert-warning');
        const startDateDisplay = document.getElementById('start-date-display');
        const endDateDisplay = document.getElementById('end-date-display');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const roomSelect = document.getElementById('room_selection');
    
        const userType = "{{ auth()->user()->utype ?? 'USR' }}"; 
        
        reserveBtn.disabled = true;
        
        if (noRoomsAlert && noRoomsAlert.textContent.includes('No rooms with available capacity at the moment')) {
            reserveBtn.disabled = true;
            return;
        }

        const today = new Date();
        const minSelectableDate = new Date();
        minSelectableDate.setDate(today.getDate() + 2);
    
        let maxSelectableDate = null;
        if (userType === 'USR') {
            maxSelectableDate = new Date();
            maxSelectableDate.setMonth(today.getMonth() + 1); 
        }     
        const datedPriceScenario = @json($datedPriceWithQuantity || $datedPrice ? true : false);
        
        function resetDateSelections() {
            if (startDateDisplay) startDateDisplay.textContent = "";
            if (endDateDisplay) endDateDisplay.textContent = "";
            if (dateFromInput) dateFromInput.value = "";
            if (dateToInput) dateToInput.value = "";
            
            if (window.calendar) {
                window.selectedDates = [];
                window.startDate = null;
                window.endDate = null;
                window.calendar.refetchEvents();
                window.calendar.render();
            }
        }

        if (roomSelect) {
            roomSelect.addEventListener('change', function() {
                resetDateSelections();
        
                if (this.value === "") {
                    resetDateSelections();
                }
                validateFullScenario();
            });
        }
        
        if (datedPriceScenario) {
            const priceSelect = document.getElementById('price_id');
            const validateDatedPriceScenario = () => {
                reserveBtn.disabled = !(priceSelect && priceSelect.value);
            };
            if (priceSelect) priceSelect.addEventListener('change', validateDatedPriceScenario);
            validateDatedPriceScenario();
        } else {
            const priceSelect = document.getElementById('price_id');
            
            const validateFullScenario = () => {
                const roomValid = roomSelect ? roomSelect.value : true;
                const priceValid = priceSelect ? priceSelect.value : true;
                const datesValid = dateFromInput && dateToInput ? (dateFromInput.value && dateToInput.value) : true;
                reserveBtn.disabled = !(roomValid && priceValid && datesValid);
            };
            
            if (priceSelect) priceSelect.addEventListener('change', validateFullScenario);
            if (dateFromInput && dateToInput) {
                dateFromInput.addEventListener('change', validateFullScenario);
                dateToInput.addEventListener('change', validateFullScenario);
            }
            
            document.getElementById('confirm-dates')?.addEventListener('click', validateFullScenario);
            validateFullScenario();
            
            if (window.calendar) {
                const validRange = { start: minSelectableDate };
                if (maxSelectableDate) {
                    validRange.end = maxSelectableDate;
                }
                window.calendar.setOption('validRange', validRange);
            }
        }
    });
</script>