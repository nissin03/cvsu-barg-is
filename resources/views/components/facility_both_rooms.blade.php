{{-- both rooms working --}}

@php
    $hasAvailableRooms = false;
    
    $filteredAttributes = $facility->facilityAttributes->filter(function($attribute) {
        if (is_null($attribute->sex_restriction)) {
            return true;
        }
        
        return $attribute->sex_restriction === auth()->user()->sex;
    });
    
    $availableRoom = $filteredAttributes->first(function($attribute) {
        return $attribute->capacity > 0;
    });
    
    $hasIndividualPrice = $facility->prices->contains('price_type', 'individual');
    $hasWholePrice = $facility->prices->contains('price_type', 'whole');
    
    $defaultShared = $availableRoom && $hasIndividualPrice;
    $defaultWhole = !$defaultShared && $hasWholePrice;
    
    $allRooms = $facility->facilityAttributes;
    
    $availableRooms = $allRooms->filter(function($room) {
        $availability = $room->availabilities->first();
        
        $isAvailable = !$availability || 
                    ($availability->remaining_capacity >= $room->capacity);
        
        $sexAllowed = true;
        if ($room->sex_restriction) {
            $sexAllowed = $room->sex_restriction === auth()->user()->sex;
        }
        
        return $isAvailable && $sexAllowed;
    });
    
    $hasAvailableWholeRooms = $availableRooms->isNotEmpty();
@endphp

<div class="facility-booking-container mb-4">
     <div class="mb-3">
        <h4 class="mb-2 booking-type-header fw-bold border-bottom pb-2">Booking Type:</h4>
        <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="booking_type" id="shared" value="shared" 
                    @if($defaultShared) checked @endif
                    @if(!$availableRoom) @endif>
                <label class="form-check-label" for="shared">Shared</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="booking_type" id="whole_place" value="whole" 
                    @if($defaultWhole) checked @endif
                    @if(!$hasWholePrice) @endif>
                <label class="form-check-label" for="whole_place">Whole Place</label>
            </div>
        </div>

    <div id="shared-section" @if(!$defaultShared) style="display: none;" @endif>
        @if($availableRoom && $hasIndividualPrice)
            @php
                $hasIndividualPriceBasedOnDays = $facility->prices
                    ->where('price_type', 'individual')
                    ->where('is_based_on_days', true)
                    ->isNotEmpty();
            @endphp

            @if($hasIndividualPriceBasedOnDays)
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-door-open"></i>
                        <span>Assigned Room</span>
                    </div>
                    <div class="section-content">
                        @php
                            $hasAvailableRooms = true;
                            $assignedRoom = $filteredAttributes->first(function($attribute) {
                                return $attribute->capacity > 0;
                            });
                        @endphp
                        
                        @if($assignedRoom)
                            <div class="capacity-info mb-2">
                                <div class="capacity-card">
                                    <i class="fa fa-door-open"></i>
                                    <span class="capacity-text">{{ $assignedRoom->room_name }}</span>
                                    @if($assignedRoom->sex_restriction)
                                        <span class="badge bg-info ms-2">
                                            <i class="fa fa-{{ $assignedRoom->sex_restriction === 'male' ? 'mars' : 'venus' }} me-1"></i>
                                            {{ ucfirst($assignedRoom->sex_restriction) }} Only
                                        </span>
                                    @endif
                                    <span class="capacity-value">{{ $assignedRoom->capacity }} person(s)</span>
                                </div>
                            </div>
                            <input type="hidden" name="shared_room_name" value="{{ $assignedRoom->room_name }}">
                            <input type="hidden" name="shared_room_capacity" value="{{ $assignedRoom->capacity }}">
                        @endif
                    </div>
                </div>
            @else
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-door-open"></i>
                        <span>Select Room</span>
                    </div>
                    <div class="section-content">
                        <div class="time-input-group mb-3">
                            <label for="shared_selected_room" class="time-label">Select Room</label>
                            <select name="shared_selected_room" id="shared_selected_room" class="client-type-select" required>
                                <option value="">Select a Room</option>
                                @foreach ($filteredAttributes as $room)
                                    @if($room->capacity > 0)
                                        <option value="{{ $room->id }}" 
                                            data-capacity="{{ $room->capacity }}"
                                            data-room-name="{{ $room->room_name }}">
                                            {{ $room->room_name }} (Capacity: {{ $room->capacity }} person(s))
                                            @if($room->sex_restriction)
                                                - {{ ucfirst($room->sex_restriction) }} only
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" id="shared_selected_room_name" name="shared_room_name" value="">
                            <input type="hidden" id="shared_selected_room_capacity" name="shared_room_capacity" value="">
                        </div>
                    </div>
                </div>
            @endif

            @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                @php
                    $datedPriceWithQuantity = $facility->prices->first(function($price) {
                        return $price->is_there_a_quantity && 
                            $price->is_based_on_days && 
                            ($price->date_from || $price->date_to);
                    });
                @endphp
                @if ($datedPriceWithQuantity)
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-calendar-alt"></i>
                            <span>Available Date Range</span>
                        </div>
                        <div class="section-content">
                            <div class="date-range-display">
                                <div class="date-item">
                                    <strong>From:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from)->format('F d, Y') }}
                                </div>
                                <div class="date-item">
                                    <strong>To:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to)->format('F d, Y') }}
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
                                    <span id="shared-start-date-display"></span>
                                </div>
                                <div class="date-selection-item">
                                    <strong>End Date:</strong> 
                                    <span id="shared-end-date-display"></span>
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
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-calculator"></i>
                            <span>Client Type:</span>
                        </div>
                        <div class="section-content">
                            <!-- Display area for selected client types -->
                            <div class="selected-client-types" id="selected-client-types"></div>
                            
                            <!-- Button to trigger the modal -->
                            <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#clientTypeModal">
                                <i class="fa fa-user-tag me-2"></i> Select Client Types
                            </button>
                        </div>
                    </div>

                    <!-- Modal for selecting client types -->
                    <div class="modal fade" id="clientTypeModal" tabindex="-1" aria-labelledby="clientTypeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="clientTypeModalLabel">
                                        <i class="fas fa-user-tag me-2"></i> Select Client Types
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                   <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Maximum capacity for this facility: <strong><span class="capacity-value"></span></strong>
                                    </div>
                                    @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                        @if ($price->is_there_a_quantity)  
                                            <input type="hidden" name="price_values[{{ $price->id }}]" value="{{ $price->value }}">
                                            <input type="hidden" name="price_names[{{ $price->id }}]" value="{{ $price->name }}">
                                            <div class="price-quantity-card mb-4">
                                                <div class="price-header d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="price-title m-0">{{ $price->name }}</h5>
                                                    <span class="price-value badge bg-primary-light text-primary fs-5 fw-bold">₱{{ number_format($price->value, 2) }}</span>
                                                </div>
                                                <div class="quantity-control">
                                                    <div class="input-group">
                                                        <span class="input-group-text quantity-label">Quantity</span>
                                                        <input type="number" 
                                                            class="form-control quantity-input"
                                                            name="internal_quantity[{{ $price->id }}]"
                                                            value="{{ old('internal_quantity.' . $price->id) }}"
                                                            min="0"
                                                            oninput="updateTotalPrice()"
                                                            placeholder="Enter Quantity">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="updateClientTypeDisplay()">Save Selections</button>
                                </div>
                            </div>
                        </div>
                    </div>

            @endif
            @if ($facility->prices->where('is_there_a_quantity', '!=', 1)->where('price_type', 'individual')->isNotEmpty())
                @php
                    $datedPrice = $facility->prices->first(function($price) {
                        return !$price->is_there_a_quantity && 
                            $price->is_based_on_days && 
                            ($price->date_from || $price->date_to) &&
                            $price->price_type == 'individual';
                    });
                @endphp
                @if ($datedPrice)
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-calendar-alt"></i>
                            <span>Available Date Range</span>
                        </div>
                        <div class="section-content">
                            <div class="date-range-display">
                                <div class="date-item">
                                    <strong>From:</strong> {{ \Carbon\Carbon::parse($datedPrice->date_from)->format('F d, Y') }}
                                </div>
                                <div class="date-item">
                                    <strong>To:</strong> {{ \Carbon\Carbon::parse($datedPrice->date_to)->format('F d, Y') }}
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
                                    <span id="shared-start-date-display"></span>
                                </div>
                                <div class="date-selection-item">
                                    <strong>End Date:</strong> 
                                    <span id="shared-end-date-display"></span>
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
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-tags"></i>
                        <span>Price Options</span>
                    </div>
                    <div class="section-content">
                        <div class="time-input-group">
                            <select name="price_id" id="price_id" class="client-type-select" onchange="updateTotalPrice()">
                                <option value="">Select Price</option>
                                @foreach ($facility->prices->where('price_type', 'individual') as $price)
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
        @endif
    </div>

    <div id="whole-section" @if(!$defaultWhole) style="display: none;" @endif>
        @if($hasWholePrice)
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-door-open"></i>
                    <span>Available Rooms</span>
                </div>
                <div class="section-content">
                    <div class="time-input-group mb-3">
                        <label for="selected_room" class="time-label">Select Room</label>
                        <select name="selected_room" id="selected_room" class="client-type-select" required>
                            <option value="">Select a Room</option>
                            @foreach ($allRooms as $room)
                                @if(!$room->sex_restriction || $room->sex_restriction === auth()->user()->sex)
                                    <option value="{{ $room->id }}" 
                                        data-capacity="{{ $room->capacity }}"
                                        data-room-name="{{ $room->room_name }}">
                                        {{ $room->room_name }} (Capacity: {{ $room->capacity }})
                                        @if($room->sex_restriction)
                                            - {{ ucfirst($room->sex_restriction) }} only
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" id="selected_room_name" name="room_name" value="">
                        <input type="hidden" id="selected_room_capacity" name="room_capacity" value="">
                    </div>
                </div>
            </div>

            @php
                $datedWholePrice = $facility->prices->first(function($price) {
                    return $price->price_type == 'whole' && 
                        $price->is_based_on_days && 
                        ($price->date_from || $price->date_to);
                });
            @endphp

            @if ($datedWholePrice)
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-calendar-alt"></i>
                        <span>Available Date Range</span>
                    </div>
                    <div class="section-content">
                        <div class="date-range-display">
                            <div class="date-item">
                                <strong>From:</strong> {{ \Carbon\Carbon::parse($datedWholePrice->date_from)->format('F d, Y') }}
                            </div>
                            <div class="date-item">
                                <strong>To:</strong> {{ \Carbon\Carbon::parse($datedWholePrice->date_to)->format('F d, Y') }}
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
                        <input type="hidden" id="whole_date_from" name="whole_date_from" required>
                        <input type="hidden" id="whole_date_to" name="whole_date_to" required>
                        
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
                        
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#wholeCalendarModal">
                            <i class="fa fa-calendar me-2"></i> Open Calendar
                        </button>
                    </div>
                </div>
                
                <div class="modal fade" id="wholeCalendarModal" tabindex="-1" aria-labelledby="wholeCalendarModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="wholeCalendarModalLabel">Select Dates</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div id="whole-calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="selected-dates-card p-3">
                                                <h6 class="fw-bold mb-3">Selected Dates</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Start Date:</small>
                                                    <div id="whole-modal-start-date" class="fw-bold"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">End Date:</small>
                                                    <div id="whole-modal-end-date" class="fw-bold"></div>
                                                </div>
                                                <div class="d-grid gap-2">
                                                   <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="whole-confirm-dates">
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

            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-tags"></i>
                    <span>Price Options</span>
                </div>
                <div class="section-content">
                    <div class="time-input-group">
                        <select name="whole_price_id" id="whole_price_id" class="client-type-select" onchange="updateTotalPrice()">
                            <option value="">Select Price</option>
                            @foreach ($facility->prices->where('price_type', 'whole') as $price)
                                <option value="{{ $price->id }}" data-value="{{ $price->value }}">
                                    {{ $price->name }} - ₱{{ number_format($price->value, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Hidden field to store the selected price value (optional) -->
                    <input type="hidden" id="selected_whole_price_value" name="selected_whole_price" value="">
                </div>
            </div>
        @endif
    </div>

    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price:  </strong>
        <span id="computed-total" class="total-price-value">₱ 0.00</span>
    </div>
</div>

<style>
    .booking-type-header{
        color: #3b82f6;
    }
    .facility-booking-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 16px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
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

    /* Calendar specific styles */
    .fc-day-capacity {
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

    .fc-day-status {
        position: absolute !important;
        bottom: -10px !important;
        left: 50% !important;
        transform: translateX(-70%) !important;
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

    .fc-status-booked {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        color: white !important;
        /* left: 1px !important; */
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
    }

    .fc-status-occupied {
        background: #fd7e14 !important;
        background: linear-gradient(135deg, #fd7e14, #e36209) !important;
        color: white !important;
        /* left: 1px !important;   */
        transform: translateX(-75%) !important;  
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
    }

    @keyframes pulse-warning {
        0% { opacity: 0.9; }
        50% { opacity: 1; }
        100% { opacity: 0.9; }
    }

    /* Modal Styles */
        .selected-client-types {
        display: none;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
    }
    
    .client-type-item {
        flex: 1;
        min-width: 120px;
        max-width: calc(50% - 6px);
        color: #1e293b;
        font-size: 14px;
        background: #f8fafc;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .client-type-item strong {
        display: block;
        margin-bottom: 4px;
    }
    
    @media (max-width: 576px) {
        .selected-client-types {
            gap: 8px;
        }
        
        .client-type-item {
            font-size: 13px;
            padding: 8px;
            min-width: 100px;
            max-width: calc(50% - 4px);
        }
    }

    
    /* Modal Structure */
    .modal .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: translate(0, -50px);
    }
    
    .modal.show .modal-dialog {
        transform: none;
    }
    
    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
    }
    
    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding-top: 1rem;
    }
    
    /* Price Quantity Cards */
    .price-quantity-card {
        background: #f8fafc;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .price-quantity-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .price-header {
        padding-bottom: 8px;
        border-bottom: 1px dashed #e5e7eb;
    }
    
    .price-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .price-value {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .badge.bg-primary-light {
        background-color: #eff6ff;
        color: #3b82f6;
    }
    
    /* Quantity Input */
    .quantity-control {
        margin-top: 12px;
    }
    
    .quantity-label {
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
        background-color: #f8fafc;
        border: 1px solid #e5e7eb;
    }
    
    .quantity-input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .quantity-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .price-quantity-card {
            padding: 12px;
        }
        
        .price-title {
            font-size: 15px;
        }
        
        .price-value {
            font-size: 13px;
        }
    }
</style>

<script>
   document.addEventListener('DOMContentLoaded', function() {
    var dateFromInput = document.getElementById('date_from');
    var dateToInput = document.getElementById('date_to');
    var wholeDateFromInput = document.getElementById('whole_date_from');
    var wholeDateToInput = document.getElementById('whole_date_to');
    var sharedDateFromInput = document.getElementById('date_from');
    var sharedDateToInput = document.getElementById('date_to');

    var today = new Date();
    var tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    if (dateFromInput) dateFromInput.min = tomorrowFormatted;
    if (dateToInput) dateToInput.min = tomorrowFormatted;
    if (wholeDateFromInput) wholeDateFromInput.min = tomorrowFormatted;
    if (wholeDateToInput) wholeDateToInput.min = tomorrowFormatted;
    if (sharedDateFromInput) sharedDateFromInput.min = tomorrowFormatted;
    if (sharedDateToInput) sharedDateToInput.min = tomorrowFormatted;

    var availabilities = @json($facility->availabilities ?? []);
    var facilityAttributes = @json($facility->facilityAttributes ?? []);
    var wholeCalendarInitialized = false;
    var sharedCalendarInitialized = false;
    var currentSelectedRoom = null;
    var currentSelectedWholeRoom = null;

    document.getElementById('shared_selected_room')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        currentSelectedRoom = selectedOption.value ? facilityAttributes.find(attr => attr.id == selectedOption.value) : null;
        
        if (selectedOption.value) {
            document.getElementById('shared_selected_room_name').value = selectedOption.getAttribute('data-room-name');
            document.getElementById('shared_selected_room_capacity').value = selectedOption.getAttribute('data-capacity');
        } else {
            document.getElementById('shared_selected_room_name').value = '';
            document.getElementById('shared_selected_room_capacity').value = '';
        }
        
        if (sharedCalendarInitialized) {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl && calendarEl._fullCalendar) {
                const calendar = calendarEl._fullCalendar;
                calendar.render();
                calendar.refetchEvents();
            }
        }
        
        updateTotalPrice();
    });

    document.getElementById('selected_room')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        currentSelectedWholeRoom = selectedOption.value ? facilityAttributes.find(attr => attr.id == selectedOption.value) : null;
        
        if (selectedOption.value) {
            document.getElementById('selected_room_name').value = selectedOption.getAttribute('data-room-name');
            document.getElementById('selected_room_capacity').value = selectedOption.getAttribute('data-capacity');
        } else {
            document.getElementById('selected_room_name').value = '';
            document.getElementById('selected_room_capacity').value = '';
        }
        
        if (wholeCalendarInitialized) {
            const calendarEl = document.getElementById('whole-calendar');
            if (calendarEl && calendarEl._fullCalendar) {
                const calendar = calendarEl._fullCalendar;
                calendar.render();
                calendar.refetchEvents();
            }
        }
        
        updateTotalPrice();
    });

    function getAvailabilityForDate(dateStr) {
        if (!currentSelectedRoom) return null;
        
        const checkDate = new Date(dateStr);
        const matchingAvailabilities = availabilities.filter(avail => {
            // Only consider availabilities for the currently selected room
            if (avail.facility_attribute_id !== currentSelectedRoom.id) return false;
            
            const availFrom = avail.date_from ? new Date(avail.date_from) : null;
            const availTo = avail.date_to ? new Date(avail.date_to) : null;
            
            if (avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString()) {
                return true;
            }
            if (availFrom && availTo && checkDate >= availFrom && checkDate <= availTo) {
                return true;
            }
            return false;
        });
        
        // Return the most recent availability (assuming availabilities are sorted by date)
        return matchingAvailabilities[0];
    }

    function isDateFullyBooked(dateStr) {
        const availability = getAvailabilityForDate(dateStr);
        if (!availability) return false;
        return availability.remaining_capacity <= 0;
    }

    function isDateOccupied(dateStr) {
        if (!currentSelectedWholeRoom) return false;
        const availability = availabilities.find(avail => {
            const availFrom = avail.date_from ? new Date(avail.date_from) : null;
            const availTo = avail.date_to ? new Date(avail.date_to) : null;
            const checkDate = new Date(dateStr);
            
            const dateInRange = availFrom && availTo && checkDate >= availFrom && checkDate <= availTo;
            const singleDate = avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString();
            
            return (dateInRange || singleDate) && 
                   avail.facility_attribute_id === currentSelectedWholeRoom.id && 
                   avail.remaining_capacity < currentSelectedWholeRoom.capacity;
        });
        return !!availability;
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'Not selected';
        const date = new Date(dateStr + 'T00:00:00+08:00');
        return date.toLocaleDateString('en-PH', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    function updateModalDateDisplay(startDisplay, endDisplay, startDate, endDate) {
        if (startDisplay) {
            startDisplay.textContent = formatDate(startDate);
        }
        if (endDisplay) {
            endDisplay.textContent = formatDate(endDate);
        }
    }

    function initializeCalendar(section) {
        const isWhole = section === 'whole';
        const modalId = isWhole ? 'wholeCalendarModal' : 'calendarModal';
        const calendarElId = isWhole ? 'whole-calendar' : 'calendar';
        const dateFromInput = isWhole ? wholeDateFromInput : sharedDateFromInput;
        const dateToInput = isWhole ? wholeDateToInput : sharedDateToInput;
        const startDisplay = document.getElementById(isWhole ? 'start-date-display' : 'shared-start-date-display');
        const endDisplay = document.getElementById(isWhole ? 'end-date-display' : 'shared-end-date-display');
        const confirmBtnId = isWhole ? 'whole-confirm-dates' : 'confirm-dates';
        const modalStartDisplay = document.getElementById(isWhole ? 'whole-modal-start-date' : 'modal-start-date');
        const modalEndDisplay = document.getElementById(isWhole ? 'whole-modal-end-date' : 'modal-end-date');
        
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                const calendarEl = document.getElementById(calendarElId);
                if (calendarEl && !calendarEl._fullCalendar) {
                    let selectedDates = [];
                    let startDate = dateFromInput?.value || null;
                    let endDate = dateToInput?.value || null;
                    
                    if (startDate && endDate) {
                        selectedDates = getDatesInRange(startDate, endDate);
                    }
                    
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        timeZone: 'Asia/Manila',
                        locale: 'en',
                        initialView: 'dayGridMonth',
                        initialDate: new Date(),
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth'
                        },
                        selectable: true,
                        selectMirror: true,
                        dayMaxEvents: false,
                        weekends: true,
                        validRange: { start: tomorrowFormatted },
                        moreLinkClick: function(info) { return false; },
                        dateClick: function(info) {
                            const clickedDate = info.dateStr;
                            if (isDateFullyBooked(clickedDate)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Not Available',
                                    text: 'This date is fully reserved and unavailable for booking.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (isWhole && isDateOccupied(clickedDate)) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Partially Occupied',
                                    text: 'This date is partially occupied. Please select dates that are completely available.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (typeof calendar.clickCount === 'undefined') calendar.clickCount = 0;
                            calendar.clickCount++;
                            if (calendar.clickCount % 2 === 1) {
                                startDate = clickedDate;
                                selectedDates = [clickedDate];
                                endDate = null;
                            } else {
                                if (startDate) {
                                    const start = new Date(startDate);
                                    const end = new Date(clickedDate);
                                    if (end >= start) {
                                        const dateRange = getDatesInRange(startDate, clickedDate);
                                        const hasReservedDate = dateRange.some(date => isDateFullyBooked(date));
                                        const hasOccupiedDate = isWhole ? dateRange.some(date => isDateOccupied(date)) : false;
                                        if (hasReservedDate) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Reserved Dates',
                                                text: 'One or more dates in your selected range are fully reserved.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        if (hasOccupiedDate) {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Occupied Dates',
                                                text: 'One or more dates in your selected range are partially occupied. Please select a different range.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        endDate = clickedDate;
                                        selectedDates = dateRange;
                                    } else {
                                        startDate = clickedDate;
                                        selectedDates = [clickedDate];
                                        endDate = null;
                                        calendar.clickCount = 1;
                                    }
                                } else {
                                    startDate = clickedDate;
                                    selectedDates = [clickedDate];
                                    endDate = null;
                                    calendar.clickCount = 1;
                                }
                            }
                            updateModalDateDisplay(modalStartDisplay, modalEndDisplay, startDate, endDate);
                            calendar.render();
                        },
                        dayCellClassNames: function(info) {
                            let classes = [];
                            if (isDateFullyBooked(info.dateStr)) classes.push('fully-booked-date');
                            if (isWhole && isDateOccupied(info.dateStr)) classes.push('occupied-date');
                            if (selectedDates.includes(info.dateStr) && !isDateFullyBooked(info.dateStr) && !(isWhole && isDateOccupied(info.dateStr))) {
                                if (info.dateStr === startDate) classes.push('selected-start-date');
                                else if (info.dateStr === endDate) classes.push('selected-end-date');
                                else classes.push('selected-range-date');
                            }
                            return classes;
                        },
                        dayCellContent: function(args) {
                            const dateStr = args.date.toISOString().split('T')[0];
                            const dayNumberEl = document.createElement('div');
                            dayNumberEl.className = 'fc-daygrid-day-number';
                            dayNumberEl.textContent = args.dayNumberText;
                            
                            if (!isWhole) {
                                // Shared section logic
                                if (!currentSelectedRoom) {
                                    return { domNodes: [dayNumberEl] };
                                }
                                
                                const availability = getAvailabilityForDate(dateStr);
                                
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity';
                                        const capacityClass = availability.remaining_capacity < 3 ? 'fc-capacity-warning' : 'fc-capacity-success';
                                        capacityEl.classList.add(capacityClass);
                                        capacityEl.textContent = `${availability.remaining_capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    // No availability record means full capacity is available
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${currentSelectedRoom.capacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            } else {
                                const availability = availabilities.find(avail => {
                                    const availFrom = avail.date_from ? new Date(avail.date_from) : null;
                                    const availTo = avail.date_to ? new Date(avail.date_to) : null;
                                    const checkDate = new Date(dateStr);
                                    
                                    const dateInRange = availFrom && availTo && checkDate >= availFrom && checkDate <= availTo;
                                    const singleDate = avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString();
                                    
                                    return (dateInRange || singleDate) && avail.facility_attribute_id === currentSelectedWholeRoom?.id;
                                });
                                
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else if (availability.remaining_capacity < currentSelectedWholeRoom.capacity) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-occupied';
                                        statusEl.textContent = 'Occupied';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${currentSelectedWholeRoom.capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    if (currentSelectedWholeRoom) {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${currentSelectedWholeRoom.capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                    return { domNodes: [dayNumberEl] };
                                }
                            }
                        },
                        events: function(fetchInfo, successCallback, failureCallback) {
                            const events = availabilities.map(avail => ({
                                id: `avail-${avail.id}`,
                                start: avail.date_from,
                                end: avail.date_to ? new Date(new Date(avail.date_to).setDate(new Date(avail.date_to).getDate() + 1)) : null,
                                display: 'background',
                                backgroundColor: avail.remaining_capacity <= 0 ? 'rgba(220, 53, 69, 0.2)' : (avail.remaining_capacity < (facilityAttributes.find(attr => attr.id === avail.facility_attribute_id)?.capacity || 0) ? 'rgba(253, 126, 20, 0.2)' : 'transparent'),
                                extendedProps: {
                                    remaining_capacity: avail.remaining_capacity
                                }
                            }));
                            successCallback(events);
                        }
                    });
                    
                    calendar.render();
                    calendarEl._fullCalendar = calendar;
                    
                    if (isWhole) {
                        wholeCalendarInitialized = true;
                    } else {
                        sharedCalendarInitialized = true;
                    }
                    
                    if (startDate && endDate) {
                        updateModalDateDisplay(modalStartDisplay, modalEndDisplay, startDate, endDate);
                    }
                    
                    document.getElementById(confirmBtnId)?.addEventListener('click', function() {
                        if (startDate && endDate) {
                            dateFromInput.value = startDate;
                            dateToInput.value = endDate;
                            updateDateDisplay(startDisplay, endDisplay, startDate, endDate);
                            updateTotalPrice();
                        }
                    });
                } else if (calendarEl && calendarEl._fullCalendar) {
                    const calendar = calendarEl._fullCalendar;
                    calendar.gotoDate(new Date());
                    calendar.render();
                }
            });
        }
    }

    function getDatesInRange(start, end) {
        const dates = [];
        const startDate = new Date(start);
        const endDate = new Date(end);
        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            dates.push(d.toISOString().split('T')[0]);
        }
        return dates;
    }

    function updateDateDisplay(startDisplay, endDisplay, startDate, endDate) {
        if (startDisplay) {
            startDisplay.textContent = formatDate(startDate);
        }
        if (endDisplay) {
            endDisplay.textContent = formatDate(endDate);
        }
    }

    function updateRequiredFields(activeSection) {
        document.querySelectorAll('#shared-section [required], #whole-section [required]').forEach(field => {
            field.removeAttribute('required');
        });
        if (activeSection === 'shared') {
            const sharedFields = document.querySelectorAll('#shared-section input[type="date"], #shared-section select');
            sharedFields.forEach(field => {
                if (field.style.display !== 'none' && !field.closest('[style*="display: none"]')) {
                    field.setAttribute('required', 'required');
                }
            });
        } else if (activeSection === 'whole') {
            if (wholeDateFromInput) wholeDateFromInput.setAttribute('required', 'required');
            if (wholeDateToInput) wholeDateToInput.setAttribute('required', 'required');
        }
    }

    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'shared') {
                document.getElementById('shared-section').style.display = 'block';
                document.getElementById('whole-section').style.display = 'none';
                updateRequiredFields('shared');
                if (!sharedCalendarInitialized) initializeCalendar('shared');
            } else {
                document.getElementById('shared-section').style.display = 'none';
                document.getElementById('whole-section').style.display = 'block';
                updateRequiredFields('whole');
                if (!wholeCalendarInitialized) initializeCalendar('whole');
            }
            updateTotalPrice();
        });
    });

    const defaultBookingType = document.querySelector('input[name="booking_type"]:checked');
    if (defaultBookingType) {
        const activeSection = defaultBookingType.value;
        updateRequiredFields(activeSection);
        if (activeSection === 'whole' && !wholeCalendarInitialized) {
            initializeCalendar('whole');
        } else if (activeSection === 'shared' && !sharedCalendarInitialized) {
            initializeCalendar('shared');
        }
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const activeBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (activeBookingType) {
                if (activeBookingType.value === 'whole') {
                    if (!validateWholeForm()) {
                        e.preventDefault();
                        return false;
                    }
                } else if (activeBookingType.value === 'shared') {
                    if (!validateSharedForm()) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
    }
});

function updateTotalPrice() {
    let totalPrice = 0;
    const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
    
    if (bookingType === 'shared') {
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            const priceId = input.name.match(/\[(\d+)\]/)[1];
            const priceValue = parseFloat(document.querySelector(`input[name="price_values[${priceId}]"]`).value) || 0;

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
            document.getElementById('selected_price_value').value = selectedPrice;
            
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
    } else if (bookingType === 'whole') {
        const priceSelect = document.getElementById('whole_price_id');
        if (priceSelect && priceSelect.value) {
            const selectedOption = priceSelect.options[priceSelect.selectedIndex];
            const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
            
            document.getElementById('selected_whole_price_value').value = selectedPrice;
            
            const dateFrom = document.getElementById('whole_date_from');
            const dateTo = document.getElementById('whole_date_to');
            
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
    }
    
    const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    document.getElementById('computed-total').textContent = formattedTotal;
    
    const totalPriceField = document.getElementById('total-price-field');
    if (totalPriceField) {
        totalPriceField.value = totalPrice.toFixed(2);
    }
}

document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('input', updateTotalPrice);
});



 function updateClientTypeDisplay() {
        const container = document.getElementById('selected-client-types');
        if (!container) return;
        
        container.innerHTML = '';
        let hasSelection = false;
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                hasSelection = true;
                const priceId = input.name.match(/\[(\d+)\]/)[1];
                const priceName = document.querySelector(`input[name="price_names[${priceId}]"]`).value;
                const priceValue = parseFloat(document.querySelector(`input[name="price_values[${priceId}]"]`).value);
                const total = (quantity * priceValue).toFixed(2);
                
                const item = document.createElement('div');
                item.className = 'client-type-item';
                item.innerHTML = `
                    <strong class="fw-bold">${priceName}</strong>
                    <span>Price:₱${total}</span><br>
                    <span>Qty: ${quantity}</span>
                `;
                container.appendChild(item);
            }
        });
        
        if (hasSelection) {
            container.style.display = 'flex'; 
        } else {
            container.style.display = 'none';
        }
        updateTotalPrice();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateClientTypeDisplay();
    });
</script>



{{-- Validation --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to get the minimum remaining capacity across selected dates
    function getMinRemainingCapacity(selectedDates) {
        let minCapacity = Infinity;
        
        // This would need to be populated with actual availability data from your backend
        const availabilities = @json($facility->availabilities ?? []);
        const selectedRoomId = document.getElementById('shared_selected_room')?.value;
        
        selectedDates.forEach(date => {
            const availability = availabilities.find(avail => 
                avail.facility_attribute_id == selectedRoomId &&
                new Date(date) >= new Date(avail.date_from) &&
                new Date(date) <= new Date(avail.date_to)
            );
            
            if (availability) {
                minCapacity = Math.min(minCapacity, availability.remaining_capacity);
            } else {
                // If no availability record, use the room's full capacity
                const roomSelect = document.getElementById('shared_selected_room');
                const roomCapacity = roomSelect ? 
                    parseInt(roomSelect.options[roomSelect.selectedIndex]?.getAttribute('data-capacity')) : 0;
                minCapacity = Math.min(minCapacity, roomCapacity);
            }
        });
        
        return minCapacity === Infinity ? 0 : minCapacity;
    }

    // Function to validate quantities against capacity
    function validateQuantitiesAgainstCapacity() {
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        
        if (bookingType === 'shared') {
            let maxCapacity;
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            
            if (dateFrom && dateTo) {
                // Date range selected - check capacity for each date
                const selectedDates = getDatesInRange(dateFrom, dateTo);
                maxCapacity = getMinRemainingCapacity(selectedDates);
            } else {
                // No dates selected - use room capacity
                const roomSelect = document.getElementById('shared_selected_room');
                const assignedRoom = document.querySelector('.capacity-card');
                
                if (roomSelect && roomSelect.value) {
                    maxCapacity = parseInt(roomSelect.options[roomSelect.selectedIndex].getAttribute('data-capacity')) || 0;
                } else if (assignedRoom) {
                    const capacityText = assignedRoom.querySelector('.capacity-value').textContent;
                    maxCapacity = parseInt(capacityText) || 0;
                }
            }
            
            // Update capacity display
            const capacityValueElement = document.querySelector('.alert-info .capacity-value');
            if (capacityValueElement) {
                capacityValueElement.textContent = maxCapacity;
            }
            
            // Validate quantities
            let totalQuantity = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                totalQuantity += parseInt(input.value) || 0;
            });
            
            if (totalQuantity > maxCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Capacity Exceeded',
                    html: `The total number of guests (${totalQuantity}) exceeds the maximum capacity of ${maxCapacity} for the selected dates.<br><br>Please adjust your selections.`,
                    confirmButtonColor: '#3085d6',
                });
                
                // Reset quantities to zero
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.value = '';
                });
                
                updateClientTypeDisplay();
                updateTotalPrice();
                return false;
            }
        }
        return true;
    }

    // Helper function to get dates in range
    function getDatesInRange(startDate, endDate) {
        const dates = [];
        const currentDate = new Date(startDate);
        const end = new Date(endDate);
        
        while (currentDate <= end) {
            dates.push(new Date(currentDate).toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        return dates;
    }

    // Add event listeners
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', validateQuantitiesAgainstCapacity);
    });
    
    document.getElementById('shared_selected_room')?.addEventListener('change', validateQuantitiesAgainstCapacity);
    
    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', validateQuantitiesAgainstCapacity);
    });
    
    // Also validate when dates are selected
    document.getElementById('confirm-dates')?.addEventListener('click', function() {
        setTimeout(validateQuantitiesAgainstCapacity, 100);
    });
});
</script>
