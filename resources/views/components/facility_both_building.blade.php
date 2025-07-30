@php
    $hasAvailableRooms = false;
    $filteredAttributes = $facility->facilityAttributes->filter(function($attribute) {
        if (is_null($attribute->sex_restriction)) {
            return true;
        }
        return $attribute->sex_restriction === auth()->user()->sex;
    });
    $availableRoom = $filteredAttributes->first(function($attribute) {
        return $attribute->whole_capacity > 0;
    });
    $hasIndividualPrice = $facility->prices->contains('price_type', 'individual');
    $hasWholePrice = $facility->prices->contains('price_type', 'whole');
    $defaultShared = $availableRoom && $hasIndividualPrice;
    $defaultWhole = !$defaultShared && $hasWholePrice;
    $wholeAttr = $facility->facilityAttributes->first();
@endphp

<div class="facility-booking-container mb-4">
    <div class="mb-3">
        <h4 class="mb-2 booking-type-header fw-bold border-bottom pb-2">Booking Type:</h4>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="shared" value="shared" 
                @if($defaultShared) checked @endif
                @if(!$availableRoom || !$hasIndividualPrice) disabled @endif>
            <label class="form-check-label" for="shared">Shared</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="whole_place" value="whole" 
                @if($defaultWhole) checked @endif
                @if(!$hasWholePrice) disabled @endif>
            <label class="form-check-label" for="whole_place">Whole Place</label>
        </div>
    </div>

    <div id="shared-section" @if(!$defaultShared) style="display: none;" @endif>
        @if($availableRoom && $hasIndividualPrice)
            <div class="mb-3">
                @php
                    $hasAvailableRooms = true;
                    $assignedRoom = $filteredAttributes->first(function($attribute) {
                        return $attribute->whole_capacity > 0;
                    });
                @endphp
                
                @if($assignedRoom)
                    @php
                        $availability = $assignedRoom->availabilities->first();
                        $displayCapacity = $availability ? $availability->remaining_capacity : $assignedRoom->whole_capacity;
                    @endphp
                    
                    <div class="capacity-info">
                        <div class="capacity-card">
                            <i class="fa fa-users"></i>
                            <span class="capacity-text">Capacity:</span>
                            <span class="capacity-value">{{ $wholeAttr->whole_capacity }}</span>
                        </div>
                    </div>
                    <input type="hidden" name="shared_facility_name" value="{{ $facility->name }}">
                    <input type="hidden" name="shared_facility_capacity" value="{{ $assignedRoom->whole_capacity }}">
                @endif
            </div>

            @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                @php
                    $datedPriceWithQuantity = $facility->prices->first(function($price) {
                        return $price->is_there_a_quantity && 
                            $price->is_based_on_days && 
                            ($price->date_from || $price->date_to);
                    });
                @endphp
                @if ($datedPriceWithQuantity)
                    <div class="mb-3 p-2 bg-light rounded border-start border-info border-3">
                        <p class="mb-0">
                            <strong><i class="fa fa-calendar-day me-1"></i>Available Date Range:</strong>
                            {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from)->format('F d') }} - {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to)->format('F d, Y') }}
                        </p>
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
                            
                            <div class="selected-dates-display">
                                <div class="date-selection-item">
                                    <strong>Start Date:</strong> 
                                    <span id="shared-start-date-display"></span>
                                </div>
                                <div class="date-selection-item">
                                    <strong>End Date:</strong> 
                                    <span id="shared-end-date-display"></span>
                                </div>
                            </div>
                            
                            <div id="shared-error-message" class="error-message"></div>
                            
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#sharedCalendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="sharedCalendarModal" tabindex="-1" aria-labelledby="sharedCalendarModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sharedCalendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div id="shared-calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selected-dates-card p-3">
                                                    <h6 class="fw-bold mb-3">Selected Dates</h6>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Start Date:</small>
                                                        <div id="shared-modal-start-date" class="fw-bold">Not selected</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">End Date:</small>
                                                        <div id="shared-modal-end-date" class="fw-bold">Not selected</div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="shared-confirm-dates">
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
                <div class="mb-3">
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-user-tag"></i>
                            <span><strong>Client Type:</strong></span>
                        </div>
                        <div class="section-content">
                            <div class="selected-client-types-display mb-3" id="selected-client-types-display">
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#priceQuantityModal">
                                    <i class="fa fa-user-tag me-2"></i> Select Client Types and Quantities
                                </button>
                        </div>
                   </div>

                        <div class="modal fade" id="priceQuantityModal" tabindex="-1" aria-labelledby="priceQuantityModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="priceQuantityModalLabel">Select Client Types and Quantities</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Maximum capacity for this facility: <strong><span class="capacity-value">{{ $wholeAttr->whole_capacity }}</span> persons</strong>
                                        </div>
                                        <div class="price-quantity-section">
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
                                                            <label for="internal_quantity-{{ $price->id }}" class="form-label quantity-label">Number of Persons</label>
                                                            <input id="internal_quantity-{{ $price->id }}" 
                                                                type="number" 
                                                                class="form-control quantity-input @error('internal_quantity') is-invalid @enderror"
                                                                name="internal_quantity[{{ $price->id }}]" 
                                                                value="{{ old('internal_quantity.' . $price->id) }}" 
                                                                min="0" 
                                                                max="{{ $wholeAttr->whole_capacity }}"
                                                                step="1" 
                                                                oninput="validateQuantityInput(this)"
                                                                placeholder="Enter quantity">
                                                            @error('internal_quantity.' . $price->id)
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="capacity-summary mt-3 p-3 bg-light rounded">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Persons:</span>
                                                <strong id="total-persons">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Remaining Capacity:</span>
                                                <strong id="remaining-capacity">{{ $wholeAttr->whole_capacity }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="updateTotalPrice()">Save Changes</button>
                                    </div>
                                </div>
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
                    <div class="mb-3 p-2 bg-light rounded border-start border-info border-3">
                        <p class="mb-0">
                            <strong><i class="fa fa-calendar-day me-1"></i>Available Date Range:</strong>
                            {{ \Carbon\Carbon::parse($datedPrice->date_from)->format('F d') }} - {{ \Carbon\Carbon::parse($datedPrice->date_to)->format('F d, Y') }}
                        </p>
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
                            
                            <div class="selected-dates-display">
                                <div class="date-selection-item">
                                    <strong>Start Date:</strong> 
                                    <span id="shared-start-date-display">Click on calendar to select</span>
                                </div>
                                <div class="date-selection-item">
                                    <strong>End Date:</strong> 
                                    <span id="shared-end-date-display">Click on calendar to select</span>
                                </div>
                            </div>
                            
                            <div id="shared-error-message" class="error-message"></div>
                            
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#sharedCalendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="sharedCalendarModal" tabindex="-1" aria-labelledby="sharedCalendarModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sharedCalendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div id="shared-calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selected-dates-card p-3">
                                                    <h6 class="fw-bold mb-3">Selected Dates</h6>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Start Date:</small>
                                                        <div id="shared-modal-start-date" class="fw-bold">Not selected</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">End Date:</small>
                                                        <div id="shared-modal-end-date" class="fw-bold">Not selected</div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="shared-confirm-dates">
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
                <div class="mb-3">
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-user-tag"></i>
                            <span><strong>Client Type:</strong></span>
                        </div>
                        <div class="section-content">
                            <select name="price_id" id="price_id" class="form-select @error('price_id') is-invalid @enderror" onchange="updateTotalPrice()">
                                <option value="">Select Price</option>
                                @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                    @if (!$price->is_there_a_quantity)
                                        <option value="{{ $price->id }}" data-value="{{ $price->value }}">
                                            {{ $price->name }} - &#8369;{{ number_format($price->value, 2) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" id="selected_price_value" name="selected_price" value="">
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div id="whole-section" @if(!$defaultWhole) style="display: none;" @endif>
        @if($hasWholePrice)
         
                <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}">
                @if ($facility->prices->isNotEmpty())
                    @foreach ($facility->prices as $price)
                    @endforeach
                @else
                    <p class="alert alert-info">No prices available for this facility.</p>
                @endif
                
                <div class="capacity-info">
                    <div class="capacity-card">
                        <i class="fa fa-users"></i>
                        <span class="capacity-text">Capacity:</span>
                        <span class="capacity-value">{{ $wholeAttr->whole_capacity }}</span>
                    </div>
                </div>
                
                @php
                    $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);
                @endphp
                
                @if ($hasDayBasedPricing)
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold"><i class="fa fa-calendar me-2"></i>Date Scheduled:</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $firstPrice = $facility->prices->where('is_based_on_days', true)->first();
                            @endphp
                            
                            @if ($firstPrice && $firstPrice->date_from && $firstPrice->date_to)
                                <div class="alert alert-primary mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <i class="fa fa-calendar-alt me-1"></i>
                                            <strong>From:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_from)->format('M d, Y') }}<br>
                                            <strong>To:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_to)->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                            
                            <div class="selected-dates-display">
                                <div class="date-selection-item">
                                    <strong>Start Date:</strong> 
                                    <span id="whole-start-date-display"></span>
                                </div>
                                <div class="date-selection-item">
                                    <strong>End Date:</strong> 
                                    <span id="whole-end-date-display"></span>
                                </div>
                            </div>
                            
                            <div id="whole-error-message" class="error-message"></div>
                            
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
                                                        <div id="whole-modal-start-date" class="fw-bold">Not selected</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">End Date:</small>
                                                        <div id="whole-modal-end-date" class="fw-bold">Not selected</div>
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
                {{-- <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-clock"></i>
                        <span>Time Selection</span>
                    </div>
                    <div class="section-content">
                        <div id="whole-time-slot-container" class="time-slot-grid">
                            <div class="time-input-group">
                                <label for="whole_time_start" class="time-label">Start Time</label>
                                <input type="time" id="whole_time_start" name="whole_time_start" class="time-input">
                            </div>
                            <div class="time-input-group">
                                <label for="whole_time_end" class="time-label">End Time <small>(Max 8 hours)</small></label>
                                <input type="time" id="whole_time_end" name="whole_time_end" class="time-input" readonly>
                            </div>
                        </div>
                    </div>
                </div> --}}
                
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-user-tag"></i>
                        <span><strong>Client Type:</strong></span>
                    </div>
                    <div class="section-content">
                        <select id="whole_client_type" name="whole_client_type" class="client-type-select" onchange="updateWholeTotalPrice()">
                            <option value="" disabled selected>Select a client type</option>
                            @foreach ($facility->prices->where('price_type', 'whole') as $price)
                                <option value="{{ $price->value }}" data-name="{{ $price->name }}">
                                    {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
           
        @endif
    </div>

    <div id="total-price" class="total-price-section">
        <div class="total-price-content">
            <strong class="total-price-label">Total Price:</strong>
            <span id="computed-total" class="total-price-value">₱0.00</span>
        </div>
        <input type="hidden" name="total_price" id="total_price_input" value="0">
        <input type="hidden" name="whole_total_price" id="whole_total_price_input" value="0">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dateFromInput = document.getElementById('date_from');
        var dateToInput = document.getElementById('date_to');
        var wholeDateFromInput = document.getElementById('whole_date_from');
        var wholeDateToInput = document.getElementById('whole_date_to');
        var sharedDateFromInput = document.getElementById('date_from');
        var sharedDateToInput = document.getElementById('date_to');
        var wholeTimeStartInput = document.getElementById('whole_time_start');
        var wholeTimeEndInput = document.getElementById('whole_time_end');
        var wholeClientTypeDropdown = document.getElementById('whole_client_type');

        var today = new Date();
        var tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 2);
        var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        
        var userType = @json(auth()->user()->utype);
        var maxDate = null;
        var maxDateFormatted = null;
        
        if (userType === 'USR') {
            maxDate = new Date(today);
            maxDate.setMonth(maxDate.getMonth() + 1);
            maxDateFormatted = maxDate.toISOString().split('T')[0];
        }
        
        if (dateFromInput) {
            dateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) dateFromInput.max = maxDateFormatted;
        }
        if (dateToInput) {
            dateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) dateToInput.max = maxDateFormatted;
        }
        if (wholeDateFromInput) {
            wholeDateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) wholeDateFromInput.max = maxDateFormatted;
        }
        if (wholeDateToInput) {
            wholeDateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) wholeDateToInput.max = maxDateFormatted;
        }
        if (sharedDateFromInput) {
            sharedDateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) sharedDateFromInput.max = maxDateFormatted;
        }
        if (sharedDateToInput) {
            sharedDateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) sharedDateToInput.max = maxDateFormatted;
        }

        if (wholeTimeStartInput) {
            wholeTimeStartInput.value = '07:00';
            calculateWholeEndTime();
        }

        var hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
        var availabilities = @json($facility->availabilities ?? []);
        var facilityAttributes = @json($facility->facilityAttributes ?? []);
        var facilityCapacity = @json($wholeAttr->whole_capacity ?? 0);
        var wholeCalendarInitialized = false;
        var sharedCalendarInitialized = false;

        function getAvailabilityForDate(dateStr) {
            const checkDate = new Date(dateStr);
            const matchingAvailabilities = availabilities.filter(avail => {
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
            
            return matchingAvailabilities[0];
        }

        function isDateFullyBooked(dateStr) {
            const availability = getAvailabilityForDate(dateStr);
            if (!availability) return false;
            return availability.remaining_capacity <= 0;
        }

        function isDateOccupied(dateStr) {
            const availability = getAvailabilityForDate(dateStr);
            if (!availability) return false;
            const attribute = facilityAttributes.find(attr => attr.id === availability.facility_attribute_id);
            if (!attribute) return false;
            return attribute.whole_capacity !== availability.remaining_capacity;
        }

        function formatDateForDisplay(dateStr) {
            if (!dateStr) return 'Not selected';
            const date = new Date(dateStr + 'T00:00:00+08:00');
            return date.toLocaleDateString('en-PH', { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
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
                startDisplay.textContent = formatDateForDisplay(startDate);
            }
            if (endDisplay) {
                endDisplay.textContent = formatDateForDisplay(endDate);
            }
        }

        function calculateWholeEndTime() {
            if (!wholeTimeStartInput || !wholeTimeEndInput) return;
            var st = wholeTimeStartInput.value;
            if (!st) return;
            var parts = st.split(':').map(Number),
                h = (parts[0] + 8) % 24,
                m = parts[1];
            wholeTimeEndInput.value = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
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
                if (wholeClientTypeDropdown) wholeClientTypeDropdown.setAttribute('required', 'required');
            }
        }

        function initializeCalendar(section) {
            const isWhole = section === 'whole';
            const calendarEl = document.getElementById(isWhole ? 'whole-calendar' : 'shared-calendar');
            const modalId = isWhole ? 'wholeCalendarModal' : 'sharedCalendarModal';
            const modalEl = document.getElementById(modalId);
            
            if (!modalEl || !calendarEl) return;
            if ((isWhole && wholeCalendarInitialized) || (!isWhole && sharedCalendarInitialized)) return;
            
            modalEl.addEventListener('shown.bs.modal', function initCalendarOnShow() {
                modalEl.removeEventListener('shown.bs.modal', initCalendarOnShow);
                
                if ((isWhole && wholeCalendarInitialized) || (!isWhole && sharedCalendarInitialized)) {
                    const calendar = calendarEl._fullCalendar;
                    if (calendar) {
                        calendar.render();
                        return;
                    }
                }

                const dateFromInput = isWhole ? wholeDateFromInput : sharedDateFromInput;
                const dateToInput = isWhole ? wholeDateToInput : sharedDateToInput;
                const startDisplay = document.getElementById(isWhole ? 'whole-start-date-display' : 'shared-start-date-display');
                const endDisplay = document.getElementById(isWhole ? 'whole-end-date-display' : 'shared-end-date-display');
                const modalStartDisplay = document.getElementById(isWhole ? 'whole-modal-start-date' : 'shared-modal-start-date');
                const modalEndDisplay = document.getElementById(isWhole ? 'whole-modal-end-date' : 'shared-modal-end-date');
                const confirmButton = document.getElementById(isWhole ? 'whole-confirm-dates' : 'shared-confirm-dates');
                
                if (!hasDayBasedPricing && calendarEl && !calendarEl._fullCalendar) {
                    let selectedDates = [];
                    let startDate = null;
                    let endDate = null;
                    
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        timeZone: 'Asia/Manila',
                        locale: 'en',
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth'
                        },
                        selectable: true,
                        selectMirror: true,
                        dayMaxEvents: false,
                        weekends: true,
                        validRange: { 
                            start: tomorrowFormatted,
                            end: maxDateFormatted || undefined
                        },
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
                                
                                if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
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
                                        
                                        if (modalEndDisplay) modalEndDisplay.textContent = formatDateForDisplay(endDate);
                                    } else {
                                        startDate = clickedDate;
                                        selectedDates = [clickedDate];
                                        endDate = null;
                                        calendar.clickCount = 1;
                                        
                                        if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                        if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                                    }
                                } else {
                                    startDate = clickedDate;
                                    selectedDates = [clickedDate];
                                    endDate = null;
                                    calendar.clickCount = 1;
                                    
                                    if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                    if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                                }
                            }
                            if (dateFromInput) dateFromInput.value = startDate || '';
                            if (dateToInput) dateToInput.value = endDate || '';
                            
                            if (confirmButton) {
                                confirmButton.disabled = !(startDate && endDate);
                            }
                            
                            if (isWhole) {
                                updateWholeTotalPrice();
                            } else {
                                updateTotalPrice();
                            }
                            
                            calendar.render();
                        },
                        dayCellClassNames: function(info) {
                            let classes = [];
                            if (isDateFullyBooked(info.dateStr)) classes.push('fully-booked-date');
                            if (isDateOccupied(info.dateStr)) classes.push('occupied-date');
                            if (selectedDates.includes(info.dateStr) && !isDateFullyBooked(info.dateStr) && !isDateOccupied(info.dateStr)) {
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
                            
                            const availability = getAvailabilityForDate(dateStr);
                            const attribute = availability ? facilityAttributes.find(attr => attr.id === availability.facility_attribute_id) : null;
                            
                            if (!isWhole) {
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
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${facilityCapacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            } else {
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else if (attribute && availability.remaining_capacity !== attribute.whole_capacity) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-occupied';
                                        statusEl.textContent = 'Occupied';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${attribute ? attribute.whole_capacity : facilityCapacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${facilityCapacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            }
                        },
                        events: function(fetchInfo, successCallback, failureCallback) {
                            const events = availabilities.map(avail => ({
                                id: `avail-${avail.id}`,
                                start: avail.date_from,
                                end: avail.date_to ? new Date(new Date(avail.date_to).setDate(new Date(avail.date_to).getDate() + 1)) : null,
                                display: 'background',
                                backgroundColor: avail.remaining_capacity <= 0 ? 'rgba(220, 53, 69, 0.2)' : (avail.remaining_capacity < avail.whole_capacity ? 'rgba(253, 126, 20, 0.2)' : 'transparent'),
                                extendedProps: {
                                    remaining_capacity: avail.remaining_capacity
                                }
                            }));
                            successCallback(events);
                        }
                    });
                    calendar.render();
                    calendarEl._fullCalendar = calendar;
                    if (isWhole) wholeCalendarInitialized = true;
                    else sharedCalendarInitialized = true;
                }
            });
        }

        function updateTotalPrice() {
            let total = 0;
            const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (selectedBookingType && selectedBookingType.value === 'shared') {
                const priceDropdown = document.getElementById('price_id');
                const quantityInputs = document.querySelectorAll('.quantity-input');
                
                if (priceDropdown && priceDropdown.value) {
                    const selectedOption = priceDropdown.options[priceDropdown.selectedIndex];
                    const priceValue = parseFloat(selectedOption.dataset.value) || 0;
                    total += priceValue;
                    const selectedPriceInput = document.getElementById('selected_price_value');
                    if (selectedPriceInput) {
                        selectedPriceInput.value = priceValue;
                    }
                }
                
                quantityInputs.forEach(input => {
                    const quantity = parseInt(input.value) || 0;
                    const priceId = input.name.match(/\[(\d+)\]/)[1];
                    const priceValue = document.querySelector(`input[name="price_values[${priceId}]"]`);
                    if (priceValue && quantity > 0) {
                        total += parseFloat(priceValue.value) * quantity;
                    }
                });
                
                const dateFrom = document.getElementById('date_from');
                const dateTo = document.getElementById('date_to');
                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    const startDate = new Date(dateFrom.value);
                    const endDate = new Date(dateTo.value);
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
                    if (daysDiff > 1) {
                        total *= daysDiff;
                    }
                }
            }
            
            const computedTotal = document.getElementById('computed-total');
            const totalPriceInput = document.getElementById('total_price_input');
            if (computedTotal) {
                computedTotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (totalPriceInput) {
                totalPriceInput.value = total.toFixed(2);
            }
        }

        function updateWholeTotalPrice() {
            let total = 0;
            const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (selectedBookingType && selectedBookingType.value === 'whole') {
                const clientTypeDropdown = document.getElementById('whole_client_type');
                if (clientTypeDropdown && clientTypeDropdown.value) {
                    const priceValue = parseFloat(clientTypeDropdown.value) || 0;
                    total = priceValue;
                    const dateFrom = document.getElementById('whole_date_from');
                    const dateTo = document.getElementById('whole_date_to');
                    if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                        const startDate = new Date(dateFrom.value);
                        const endDate = new Date(dateTo.value);
                        const timeDiff = endDate - startDate;
                        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
                        if (daysDiff > 1) {
                            total *= daysDiff;
                        }
                    }
                }
            }
            
            const computedTotal = document.getElementById('computed-total');
            const wholeTotalPriceInput = document.getElementById('whole_total_price_input');
            if (computedTotal) {
                computedTotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (wholeTotalPriceInput) {
                wholeTotalPriceInput.value = total.toFixed(2);
            }
        }

        function updateClientTypeDisplay() {
            const container = document.getElementById('selected-client-types-display');
            if (!container) return;
            
            container.innerHTML = '';
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            let hasSelection = false;
            
            quantityInputs.forEach(input => {
                if (input.value && parseInt(input.value) > 0) {
                    hasSelection = true;
                    const priceId = input.name.match(/\[(.*?)\]/)[1];
                    const priceNameInput = document.querySelector(`input[name="price_names[${priceId}]"]`);
                    const priceValueInput = document.querySelector(`input[name="price_values[${priceId}]"]`);
                    
                    if (priceNameInput && priceValueInput) {
                        const priceName = priceNameInput.value;
                        const priceValue = parseFloat(priceValueInput.value);
                        
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'client-type-item';
                        itemDiv.innerHTML = `
                            <strong>${priceName}</strong>
                            <span>Price: ₱${priceValue.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span><br>
                            <span>Qty: ${parseInt(input.value).toLocaleString()}</span>
                        `;
                        
                        container.appendChild(itemDiv);
                    }
                }
            });
            
            if (hasSelection) {
                container.style.display = 'flex'; 
            } else {
                container.style.display = 'none';
            }
            
            updateTotalPrice();
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

        if (wholeTimeStartInput) {
            wholeTimeStartInput.addEventListener('change', function() {
                calculateWholeEndTime();
                updateWholeTotalPrice();
            });
        }

        if (wholeClientTypeDropdown) {
            wholeClientTypeDropdown.addEventListener('change', function() {
                updateWholeTotalPrice();
            });
        }

        const priceQuantityModal = document.getElementById('priceQuantityModal');
        if (priceQuantityModal) {
            priceQuantityModal.addEventListener('hidden.bs.modal', function () {
                updateClientTypeDisplay();
            });
        }

        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', function() {
                const startDate = document.getElementById('date_from').value;
                const endDate = document.getElementById('date_to').value;
                
                const startDisplay = document.getElementById('shared-start-date-display');
                const endDisplay = document.getElementById('shared-end-date-display');
                
                if (startDisplay) startDisplay.textContent = formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = formatDateForDisplay(endDate);
            });
        }

        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', function() {
                const startDate = document.getElementById('whole_date_from').value;
                const endDate = document.getElementById('whole_date_to').value;
                
                const startDisplay = document.getElementById('whole-start-date-display');
                const endDisplay = document.getElementById('whole-end-date-display');
                
                if (startDisplay) startDisplay.textContent = formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = formatDateForDisplay(endDate);
            });
        }

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
            });
        }

        updateClientTypeDisplay();
        updateTotalPrice();
    });
</script>

{{-- UI STYLES --}}
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

    .alert-primary {
        background-color: #e7f5ff;
        color: #1864ab;
    }

    .alert-info {
        background-color: #e7f5ff;
        color: #1864ab;
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

    .selected-dates-display {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .selected-client-types-display {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    
    .client-type-item {
        flex: 1;
        min-width: 150px;
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

    .date-selection-item {
        flex: 1;
        min-width: 150px;
        color: #1e293b;
        font-size: 14px;    
    }

    .error-message {
        color: #ef4444;
        font-size: 14px;
        margin-bottom: 16px;
        padding: 8px;
        background: #fef2f2;
        border-radius: 4px;
        border: 1px solid #fecaca;
        display: none;
    }

    .time-slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .time-input-group {
        display: flex;
        flex-direction: column;
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
        display: block; 
    }

    .total-price-label {
        display: block;
        color: #e2e8f0;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    /* .total-price-label i {
        color: #60a5fa;
        font-size: 16px;
    } */

    .total-price-value {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }

    .computed-total{
         font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }

    .modal .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: translate(0, -50px);
    }

    .modal.show .modal-dialog {
        transform: none;
    }

    .price-quantity-section {
        background: #ffffff;
        border-radius: 8px;
        padding: 16px;
    }

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

    #priceQuantityModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .price-quantity-modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding-top: 1rem;
    }

    .badge.bg-primary-light {
        background-color: #eff6ff;
        color: #3b82f6;
    }

    .quantity-label {
        font-size: 14px;
        font-weight: 500;
        color: #4b5563;
        margin-bottom: 8px;
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

    .invalid-feedback {
        font-size: 13px;
        margin-top: 6px;
    }

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


    .selected-dates-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .selected-dates-card h6 {
        color: #1a1a1a;
        font-size: 1rem;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 12px;
        margin-bottom: 16px;
    }

    .selected-dates-card small.text-muted {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-bottom: 4px;
    }

    .selected-dates-card .fw-bold {
        font-size: 0.9rem;
        color: #1a1a1a;
        margin-bottom: 12px;
        word-break: break-word;
    }

    #shared-confirm-dates,
    #whole-confirm-dates {
        background-color: #3b82f6;
        border: none;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
        margin-top: auto;
    }

    #shared-confirm-dates:hover,
    #whole-confirm-dates:hover {
        background-color: #2563eb;
    }

    #shared-confirm-dates:disabled,
    #whole-confirm-dates:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
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
        
        .selected-dates-display {
            flex-direction: column;
        }
        
        .modal .modal-dialog {
            margin: 0.5rem auto;
        }
        
        .modal .modal-content {
            height: 90vh;
        }
        
        .modal .row {
            flex-direction: column;
            height: 100%;
        }
        
        .modal #shared-calendar,
        .modal #whole-calendar {
            height: 60%;
        }
        
        .selected-dates-card {
            height: auto;
            margin-top: 16px;
        }
    }

    @media (max-width: 480px) {
        .time-slot-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

{{-- Calendar Styles --}}
<style>
    .fc .fc-day.fully-booked-date,
    .fc .fc-daygrid-day.fully-booked-date,
    .fc-daygrid-day-frame.fully-booked-date {
        background: linear-gradient(135deg, #ff6b6b, #ee5a5a) !important;
        color: white !important;
        border: 2px solid #ff5252 !important;
        position: relative !important;
        opacity: 0.85 !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.fully-booked-date .fc-daygrid-day-number,
    .fc .fc-daygrid-day.fully-booked-date .fc-daygrid-day-number {
        color: white !important;
        font-weight: bold !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
    }
    
    .fc .fc-day.fully-booked-date:hover,
    .fc .fc-daygrid-day.fully-booked-date:hover {
        background: linear-gradient(135deg, #ff5252, #e53935) !important;
        cursor: not-allowed !important;
        opacity: 1 !important;
        transform: scale(0.98) !important;
        box-shadow: 0 4px 8px rgba(255, 82, 82, 0.3) !important;
    }
    
    .fc .fc-day.fully-booked-date::before {
        content: "🚫" !important;
        position: absolute !important;
        top: 2px !important;
        right: 2px !important;
        font-size: 12px !important;
        z-index: 15 !important;
        filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.3)) !important;
    }
    
    .fc .fc-day.fully-booked-date::after,
    .fc .fc-daygrid-day.fully-booked-date::after,
    .fc-daygrid-day-frame.fully-booked-date::after {
        content: "booked" !important;
        position: absolute !important;
        bottom: -10px !important;
        left: 50% !important;
        transform: translateX(-70%) !important;
        font-size: 9px !important;
        color: white !important;
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
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
    
    .fc .fc-day.selected-start-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-start-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb) !important;
        border: 2px solid #1976d2 !important;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.selected-end-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-end-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9) !important;
        border: 2px solid #388e3c !important;
        box-shadow: 0 2px 8px rgba(56, 142, 60, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc .fc-day.selected-range-date:not(.fully-booked-date),
    .fc .fc-daygrid-day.selected-range-date:not(.fully-booked-date) {
        background: linear-gradient(135deg, #f3e5f5, #e1bee7) !important;
        border: 1px solid #7b1fa2 !important;
        box-shadow: 0 1px 4px rgba(123, 31, 162, 0.2) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc-event.fully-booked-event {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        color: white !important;
        font-size: 9px !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        padding: 2px 6px !important;
        text-align: center !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        transition: all 0.3s ease !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
        pointer-events: none !important;
        white-space: nowrap !important;
    }
    
    .fc-event.fully-booked-event:hover {
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4) !important;
    }
    
    .fc-event.fully-booked-event .fc-event-title {
        font-size: 9px !important;
        text-transform: lowercase !important;
        font-weight: 500 !important;
        line-height: 1 !important;
        letter-spacing: 0.2px !important;
        color: white !important;
    }
    
    .fc .fc-daygrid-event.fully-booked-event {
        font-size: 9px !important;
        text-transform: lowercase !important;
        background: linear-gradient(135deg, #dc3545, #c82333) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        color: white !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
    }
    
    .fc .fc-daygrid-event.fully-booked-event .fc-event-title,
    .fc .fc-daygrid-event.fully-booked-event .fc-event-main,
    .fc .fc-daygrid-event.fully-booked-event .fc-event-main-frame {
        font-size: 9px !important;
        text-transform: lowercase !important;
        color: white !important;
        font-weight: 500 !important;
        letter-spacing: 0.2px !important;
        line-height: 1 !important;
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
    
    @keyframes pulseReserved {
        0% { opacity: 0.85; }
        50% { opacity: 1; }
        100% { opacity: 0.85; }
    }
    
    .fc .fc-day.fully-booked-date {
        animation: pulseReserved 3s infinite ease-in-out !important;
        position: relative !important;
    }
    
    .fc .fc-day.fully-booked-date:hover::before {
        content: "This date is fully booked" !important;
        position: absolute !important;
        top: -35px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        background: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 6px 10px !important;
        border-radius: 6px !important;
        font-size: 11px !important;
        white-space: nowrap !important;
        z-index: 1000 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
        font-weight: normal !important;
        letter-spacing: 0.3px !important;
    }
    
    .fc .fc-day.fully-booked-date:hover::after {
        content: "" !important;
        position: absolute !important;
        top: -8px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 6px solid transparent !important;
        border-right: 6px solid transparent !important;
        border-top: 6px solid rgba(0, 0, 0, 0.9) !important;
        z-index: 1000 !important;
    }
    
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
        left: 50% !important;
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


    .fc-status-occupied {
        background: #fd7e14 !important;
        background: linear-gradient(135deg, #fd7e14, #e36209) !important;
        color: white !important;
        right: 2px !important;  
        transform: none !important;  
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
</style>


{{-- Validation --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reserveBtn = document.getElementById('reserve-btn');
        const bookingTypeRadios = document.querySelectorAll('input[name="booking_type"]');
        const sharedSection = document.getElementById('shared-section');
        const wholeSection = document.getElementById('whole-section');
        
        reserveBtn.disabled = true;
        reserveBtn.style.opacity = '0.5';
        reserveBtn.style.cursor = 'not-allowed';
        
        bookingTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'shared') {
                    sharedSection.style.display = 'block';
                    wholeSection.style.display = 'none';
                } else if (this.value === 'whole') {
                    sharedSection.style.display = 'none';
                    wholeSection.style.display = 'block';
                }
                validateForm();
            });
        });
        
        function validateSharedSection() {
            const isSharedSelected = document.querySelector('input[name="booking_type"]:checked')?.value === 'shared';
            if (!isSharedSelected) return false;
            
            let isValid = true;
            
            const dateBasedPricing = {{ $facility->prices->contains('is_based_on_days', true) ? 'true' : 'false' }};
            
            if (!dateBasedPricing) {
                const dateFrom = document.getElementById('date_from')?.value;
                const dateTo = document.getElementById('date_to')?.value;
                
                if (!dateFrom || !dateTo) {
                    isValid = false;
                }
            }
            
            const hasQuantityPricing = {{ $facility->prices->where('is_there_a_quantity', true)->count() > 0 ? 'true' : 'false' }};
            const maxCapacity = parseInt(document.querySelector('.capacity-value')?.textContent) || 0;
            
            if (hasQuantityPricing) {
                const quantityInputs = document.querySelectorAll('input[name^="internal_quantity"]');
                let hasValidQuantity = false;
                let totalQuantity = 0;
                
                quantityInputs.forEach(input => {
                    const value = parseInt(input.value) || 0;
                    if (value > 0) {
                        hasValidQuantity = true;
                        totalQuantity += value;
                    }
                });
                
                if (!hasValidQuantity) {
                    isValid = false;
                }
                
                // Check if total quantity exceeds capacity
                if (totalQuantity > maxCapacity) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Capacity Exceeded',
                        text: `The total number of persons (${totalQuantity}) exceeds the facility's maximum capacity of ${maxCapacity}. Please adjust your quantities.`,
                        confirmButtonColor: '#3085d6',
                    });
                }
            } else {
                const priceSelect = document.getElementById('price_id');
                if (priceSelect && !priceSelect.value) {
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        function validateWholeSection() {
            const isWholeSelected = document.querySelector('input[name="booking_type"]:checked')?.value === 'whole';
            if (!isWholeSelected) return false;
            
            let isValid = true;
            
            const dateBasedPricing = {{ $facility->prices->contains('is_based_on_days', true) ? 'true' : 'false' }};
            
            if (!dateBasedPricing) {
                const wholeDateFrom = document.getElementById('whole_date_from')?.value;
                const wholeDateTo = document.getElementById('whole_date_to')?.value;
                
                if (!wholeDateFrom || !wholeDateTo) {
                    isValid = false;
                }
            }
            
            const wholeClientType = document.getElementById('whole_client_type');
            if (wholeClientType && !wholeClientType.value) {
                isValid = false;
            }
            
            return isValid;
        }
        
        function validateForm() {
            const selectedBookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
            let isFormValid = false;
            
            if (selectedBookingType === 'shared') {
                isFormValid = validateSharedSection();
            } else if (selectedBookingType === 'whole') {
                isFormValid = validateWholeSection();
            }
            
            if (isFormValid) {
                reserveBtn.disabled = false;
                reserveBtn.style.opacity = '1';
                reserveBtn.style.cursor = 'pointer';
            } else {
                reserveBtn.disabled = true;
                reserveBtn.style.opacity = '0.5';
                reserveBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Combined quantity validation function
        function validateQuantityInput(input) {
            const maxCapacity = parseInt(document.querySelector('.capacity-value')?.textContent) || 0;
            const quantityInputs = document.querySelectorAll('input[name^="internal_quantity"], .quantity-input');
            let totalQuantity = 0;
            
            quantityInputs.forEach(qtyInput => {
                totalQuantity += parseInt(qtyInput.value) || 0;
            });
            
            // Update the summary display if elements exist
            const totalPersonsEl = document.getElementById('total-persons');
            const remainingCapacityEl = document.getElementById('remaining-capacity');
            if (totalPersonsEl) totalPersonsEl.textContent = totalQuantity;
            if (remainingCapacityEl) remainingCapacityEl.textContent = Math.max(0, maxCapacity - totalQuantity);
            
            if (totalQuantity > maxCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Capacity Exceeded',
                    text: `The total number of persons (${totalQuantity}) exceeds the facility's maximum capacity of ${maxCapacity}. Please adjust your quantities.`,
                    confirmButtonColor: '#3085d6',
                });
                
                // Reset the input that caused the overflow
                const remaining = maxCapacity - (totalQuantity - (parseInt(input.value) || 0));
                input.value = Math.max(0, remaining);
                
                // Recalculate totals after adjustment
                totalQuantity = 0;
                quantityInputs.forEach(qtyInput => {
                    totalQuantity += parseInt(qtyInput.value) || 0;
                });
                if (totalPersonsEl) totalPersonsEl.textContent = totalQuantity;
                if (remainingCapacityEl) remainingCapacityEl.textContent = Math.max(0, maxCapacity - totalQuantity);
            }
            
            // Update total price if function exists
            if (typeof updateTotalPrice === 'function') {
                updateTotalPrice();
            }
            
            validateForm();
        }
        
        // Make validateQuantityInput available globally
        window.validateQuantityInput = validateQuantityInput;
        
        // Add input validation for quantity fields
        const quantityInputs = document.querySelectorAll('input[name^="internal_quantity"], .quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', function() {
                validateQuantityInput(this);
            });
            
            input.addEventListener('change', validateForm);
        });
        
        // Date input event listeners
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        if (dateFromInput) dateFromInput.addEventListener('change', validateForm);
        if (dateToInput) dateToInput.addEventListener('change', validateForm);
        
        const wholeDateFromInput = document.getElementById('whole_date_from');
        const wholeDateToInput = document.getElementById('whole_date_to');
        if (wholeDateFromInput) wholeDateFromInput.addEventListener('change', validateForm);
        if (wholeDateToInput) wholeDateToInput.addEventListener('change', validateForm);
        
        // Other form element event listeners
        const priceSelect = document.getElementById('price_id');
        if (priceSelect) priceSelect.addEventListener('change', validateForm);
        
        const wholeClientTypeSelect = document.getElementById('whole_client_type');
        if (wholeClientTypeSelect) wholeClientTypeSelect.addEventListener('change', validateForm);
        
        // Confirm button event listeners
        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', function() {
                setTimeout(validateForm, 100);
            });
        }
        
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', function() {
                setTimeout(validateForm, 100);
            });
        }
        
        // Override existing price update functions to include validation
        const originalUpdateTotalPrice = window.updateTotalPrice;
        window.updateTotalPrice = function() {
            if (originalUpdateTotalPrice) {
                originalUpdateTotalPrice();
            }
            validateForm();
        };
        
        const originalUpdateWholeTotalPrice = window.updateWholeTotalPrice;
        window.updateWholeTotalPrice = function() {
            if (originalUpdateWholeTotalPrice) {
                originalUpdateWholeTotalPrice();
            }
            validateForm();
        };
        
        // Modal save button event listener
        const modalSaveBtn = document.querySelector('#priceQuantityModal .btn-primary[data-bs-dismiss="modal"]');
        if (modalSaveBtn) {
            modalSaveBtn.addEventListener('click', function() {
                setTimeout(validateForm, 100);
            });
        }
        
        // Initial validation
        validateForm();
    });
</script>