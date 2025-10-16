@php
    $filteredAddons = $facility->addons->filter(function($addon) use ($facility) {
        return $addon->facility_id === $facility->id &&
               ($addon->facility_attribute_id === null || $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
               $addon->is_available == true &&
               $addon->is_refundable == false &&
               $addon->show === 'both';
    });

    $refundableAddons = $facility->addons->filter(function($addon) use ($facility) {
        return $addon->facility_id === $facility->id &&
               ($addon->facility_attribute_id === null || $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
               $addon->is_available == true &&
               $addon->is_refundable == true &&
               $addon->price_type === 'flat_rate' &&
               $addon->show === 'both';
    });

    $perUnitAddons = $filteredAddons->filter(function($addon) {
        return $addon->price_type === 'per_unit';
    });
    
    $perNightAddons = $filteredAddons->filter(function($addon) {
        return $addon->price_type === 'per_night';
    });
    
    $perItemAddons = $filteredAddons->filter(function($addon) {
        return $addon->price_type === 'per_item';
    });
    
    $flatRateAddons = $filteredAddons->filter(function($addon) {
        return $addon->price_type === 'flat_rate' || (!in_array($addon->price_type, ['per_unit', 'per_night', 'per_item']));
    });

    $unavailableDates = [];
    $perItemQuantityData = [];
    
    foreach ($filteredAddons as $addon) {
        if ($addon->price_type === 'per_item') {
            $reservationData = \DB::table('addons_reservations')
                ->where('addon_id', $addon->id)
                ->whereNotNull('date_from')
                ->whereNotNull('date_to')
                ->select('date_from', 'date_to', 'remaining_quantity')
                ->get()
                ->toArray();
            
            $perItemQuantityData[$addon->id] = $reservationData;
            
            $unavailableDates[$addon->id] = array_filter($reservationData, function($item) {
                return $item->remaining_quantity === 0;
            });
        } else {
            $unavailableDates[$addon->id] = \DB::table('addons_reservations')
                ->where('addon_id', $addon->id)
                ->where('remaining_capacity', 0)
                ->whereNotNull('date_from')
                ->whereNotNull('date_to')
                ->select('date_from', 'date_to')
                ->get()
                ->toArray();
        }
    }
    $perContractRemainingCapacity = [];
    foreach ($filteredAddons as $addon) {
        if ($addon->billing_cycle === 'per_contract') {
            $row = \DB::table('addons_reservations')
                ->where('addon_id', $addon->id)
                ->whereNull('date_from')
                ->whereNull('date_to')
                ->orderByDesc('id')
                ->first();
            $perContractRemainingCapacity[$addon->id] = $row ? (int) ($row->remaining_capacity ?? 0) : null;
        }
    }
@endphp

<style>
    /* Your existing CSS styles remain the same */
    #addonsModal .addon-card {
        border: 1px solid #e9ecef !important;
        border-radius: 12px !important;
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
        margin-bottom: 0 !important;
    }
    #addonsModal .addon-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    #addonsModal .section-divider {
        border-bottom: 2px solid #e9ecef;
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.5rem;
    }
    #addonsModal .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    #addonsModal .section-title i {
        color: #0044cc;
    }
    #addonsModal .no-addons-message {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    #addonsModal .accordion-button {
        background: #ffffff !important;
        border: 1px solid #e9ecef !important;
        padding: 1.25rem 1.5rem;
        font-weight: 500;
        color: #495057 !important;
        box-shadow: none !important;
        border-radius: 12px 12px 0 0 !important;
    }
    #addonsModal .accordion-button:not(.collapsed) {
        background: #f8f9fa !important;
        color: #212529 !important;
        box-shadow: none !important;
        border-color: #dee2e6 !important;
    }
    #addonsModal .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05) !important;
        border-color: #ced4da !important;
    }
    #addonsModal .accordion-button::after {
        filter: brightness(0.6);
    }
    #addonsModal .accordion-button:not(.collapsed)::after {
        filter: brightness(0.4);
    }
    #addonsModal .accordion-body {
        padding: 1.5rem;
        background: #fafbfc;
        border-radius: 0 0 12px 12px;
    }
    #addonsModal .addon-price-badge {
        background: #117a11 !important;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: none;
    }
    #addonsModal .addon-price-badge.per-unit {
        background: #0044cc !important;
    }
    #addonsModal .addon-price-badge.per-night {
        background: #6610f2 !important;
    }
    #addonsModal .addon-price-badge.per-item {
        background: #20c997 !important;
    }
    #addonsModal .addon-price-badge.flat-rate {
        background: #fd7e14 !important;
    }
    #addonsModal .addon-description {
        font-size: 0.9rem;
        line-height: 1.5;
        color: #6c757d;
        margin-bottom: 1.5rem;
        font-style: italic;
    }
    #addonsModal .form-control {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    #addonsModal .form-control:focus {
        border-color: #495057 !important;
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important;
    }
    #addonsModal .quantity-label, 
    #addonsModal .nights-label,
    #addonsModal .date-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    #addonsModal .form-check {
        padding: 0.75rem 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    #addonsModal .form-check:hover {
        background: #e9ecef;
    }
    #addonsModal .form-check-input:checked {
        background-color: #495057 !important;
        border-color: #495057 !important;
    }
    #addonsModal .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important;
    }
    #addonsModal .form-check-label {
        font-weight: 500;
        color: #495057;
        margin-left: 0.5rem;
    }
    #addonsModal .modal-content {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    }
    #addonsModal .modal-header {
        background: #ffffff;
        color: #212529;
        border-radius: 16px 16px 0 0 !important;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }
    #addonsModal .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }
    #addonsModal .btn-close {
        filter: brightness(0) invert(1) !important;
        opacity: 0.8;
    }
    #addonsModal .btn-close:hover {
        opacity: 1;
    }
    #addonsModal .modal-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto;
    }
    #addonsModal .btn-primary {
        background-color: #0044cc !important;
    }
    #addonsModal .quantity-control, 
    #addonsModal .nights-control,
    #addonsModal .date-control {
        margin-top: 1rem;
    }
    #addonsModal .form-control:disabled {
        background-color: #e9ecef !important;
        opacity: 0.6;
    }
    #addonsModal .date-range-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    #addonsModal .date-validation-message {
        font-size: 0.8rem;
        margin-top: 0.5rem;
        padding: 0.5rem;
        border-radius: 4px;
    }
    #addonsModal .date-validation-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    #addonsModal .date-validation-message.success {
        background-color: #d1edff;
        color: #0c5460;
        border: 1px solid #b8daff;
    }
    
    /* Flatpickr custom styles */
    #addonsModal .flatpickr-input {
        background-color: white !important;
    }
    #addonsModal .flatpickr-disabled {
        background-color: #e9ecef !important;
        color: #6c757d !important;
    }
    
    @media (max-width: 768px) {
        #addonsModal .modal-dialog {
            margin: 1rem;
        }
        #addonsModal .modal-body {
            padding: 1rem;
        }
        #addonsModal .modal-footer {
            padding: 1rem;
        }
        #addonsModal .accordion-button {
            padding: 1rem;
            font-size: 0.9rem;
        }
        #addonsModal .accordion-body {
            padding: 1rem;
        }
        #addonsModal .date-range-container {
            grid-template-columns: 1fr;
        }
    }
</style>

@if($filteredAddons->count() > 0)
<div class="mb-3">
    <div class="booking-section">
        <div class="section-header">        
            <i class="fa fa-plus-circle"></i>
            <span><strong>Add-ons:</strong></span>
        </div>
        <div class="section-content">
            <div class="selected-addons-display mb-3 d-none" id="selected-addons-display">
            </div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#addonsModal">
                    <i class="fa fa-plus me-2"></i> Select Add-ons
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addonsModalLabel">
                    <i class="fa fa-plus-circle me-2"></i>Select Add-ons
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                @if($perUnitAddons->count() > 0)
                    <div class="addon-section">
                        <h6 class="section-title">
                            <i class="fa fa-calculator"></i>
                            Per Unit Add-ons
                        </h6>
                        <div class="accordion" id="perUnitAddonsAccordion">
                            @foreach ($perUnitAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="perUnitHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#perUnitCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="perUnitCollapse{{ $addon->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>{{ $addon->name }}</strong>
                                                        @if($addon->capacity)
                                                            {{-- <small class="text-muted">(Max: {{ $addon->capacity }} per person/unit)</small> --}}
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="addon-price-badge per-unit">
                                                            ₱{{ number_format($addon->base_price, 2) }} per unit
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="perUnitCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="perUnitHeading{{ $addon->id }}" 
                                             data-bs-parent="#perUnitAddonsAccordion">
                                            <div class="accordion-body">
                                                @if($addon->description)
                                                    <p class="addon-description">{{ $addon->description }}</p>
                                                @endif
                                                
                                                <input type="hidden" name="addon_values[{{ $addon->id }}]" value="{{ $addon->base_price }}">
                                                <input type="hidden" name="addon_names[{{ $addon->id }}]" value="{{ $addon->name }}">
                                                <input type="hidden" name="addon_types[{{ $addon->id }}]" value="{{ $addon->price_type }}">
                                                <input type="hidden" name="addon_capacity[{{ $addon->id }}]" value="{{ $addon->capacity ?? 0 }}">
                                                <input type="hidden" name="addon_is_quantity_based[{{ $addon->id }}]" value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                <input type="hidden" name="addon_billing_cycle[{{ $addon->id }}]" value="{{ $addon->billing_cycle }}">
                                                <input type="hidden" id="addon_selected_dates-{{ $addon->id }}" name="addon_selected_dates[{{ $addon->id }}]" value="">
                                                
                                                @if($addon->is_based_on_quantity)
                                                    <div class="quantity-control">
                                                        <label for="addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>How many {{ $addon->name }}
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="addon_quantity[{{ $addon->id }}]" 
                                                            value="0" 
                                                            min="0" 
                                                            max="{{ $addon->capacity ?? 999 }}"
                                                            step="1" 
                                                            data-addon-id="{{ $addon->id }}"
                                                            placeholder="Enter quantity">
                                                    </div>
                                                @else
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox" 
                                                            type="checkbox" 
                                                            id="addon_checkbox-{{ $addon->id }}"
                                                            name="addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
                                                @endif
                                                
                                                @if($addon->billing_cycle === 'per_day')
                                                <div class="calendar-control mt-3">
                                                    <label class="form-label">
                                                        <i class="fa fa-calendar me-1"></i>Select Date Range
                                                    </label>
                                                    <div id="addon_calendar-{{ $addon->id }}" class="addon-calendar"></div>
                                                    
                                                    <input type="hidden" id="addon_date_from-{{ $addon->id }}" name="addon_date_from[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_date_to-{{ $addon->id }}" name="addon_date_to[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_selected_dates-{{ $addon->id }}" name="addon_selected_dates[{{ $addon->id }}]" value="">
                                                    
                                                    <div id="selected_dates_display-{{ $addon->id }}" class="mt-2 p-2 bg-light rounded">
                                                        <small>
                                                            <strong>Selected range:</strong> 
                                                            <span class="selected-dates-text">None</span>
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <strong>Days count:</strong> 
                                                            <span class="days-count-text">0 days</span>
                                                        </small>
                                                    </div>
                                                </div>  

                                                <div class="nights-control mt-3">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-calendar-day me-1"></i>How many days
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="0" 
                                                        min="0" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Number of days will auto-update"
                                                        readonly
                                                        style="background-color: #f8f9fa;">
                                                </div>
                                                @else
                                                <input type="hidden" id="addon_nights-{{ $addon->id }}" name="addon_nights[{{ $addon->id }}]" value="1">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="section-divider"></div>
                    </div>
                @endif

                @if($perNightAddons->count() > 0)
                    <div class="addon-section">
                        <h6 class="section-title">
                            <i class="fa fa-moon"></i>
                            Per Night / Per Day Add-ons
                        </h6>
                        <div class="accordion" id="perNightAddonsAccordion">
                            @foreach ($perNightAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="perNightHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#perNightCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="perNightCollapse{{ $addon->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>{{ $addon->name }}</strong>
                                                        @if($addon->capacity)
                                                            <small class="text-muted">(Max: {{ $addon->capacity }} per person/unit)</small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="addon-price-badge per-night">
                                                            ₱{{ number_format($addon->base_price, 2) }} per night
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="perNightCollapse{{ $addon->id }}" 
                                            class="accordion-collapse collapse" 
                                            aria-labelledby="perNightHeading{{ $addon->id }}" 
                                            data-bs-parent="#perNightAddonsAccordion">
                                            <div class="accordion-body">
                                                @if($addon->description)
                                                    <p class="addon-description">{{ $addon->description }}</p>
                                                @endif
                                                
                                                <input type="hidden" name="addon_values[{{ $addon->id }}]" value="{{ $addon->base_price }}">
                                                <input type="hidden" name="addon_names[{{ $addon->id }}]" value="{{ $addon->name }}">
                                                <input type="hidden" name="addon_types[{{ $addon->id }}]" value="{{ $addon->price_type }}">
                                                <input type="hidden" name="addon_capacity[{{ $addon->id }}]" value="{{ $addon->capacity ?? 0 }}">
                                                <input type="hidden" name="addon_is_quantity_based[{{ $addon->id }}]" value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                <input type="hidden" name="addon_billing_cycle[{{ $addon->id }}]" value="{{ $addon->billing_cycle }}">
                                                
                                                {{-- Add a hidden quantity input with default value 1 for non-quantity-based addons --}}
                                                @if(!$addon->is_based_on_quantity)
                                                    <input type="hidden" 
                                                        id="addon_quantity-{{ $addon->id }}" 
                                                        name="addon_quantity[{{ $addon->id }}]" 
                                                        value="0"
                                                        data-addon-id="{{ $addon->id }}"
                                                        data-price-type="per_night">
                                                @endif
                                                
                                                @if($addon->is_based_on_quantity)
                                                    <div class="quantity-control">
                                                        <label for="addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>How many {{ $addon->name }}
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity per-night-quantity"
                                                            name="addon_quantity[{{ $addon->id }}]" 
                                                            value="0" 
                                                            min="0" 
                                                            @if(!is_null($addon->quantity)) max="{{ $addon->quantity }}" @endif
                                                            step="1" 
                                                            data-addon-id="{{ $addon->id }}"
                                                            data-price-type="per_night"
                                                            placeholder="Enter quantity">
                                                    </div>
                                                @else
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox per-night-checkbox" 
                                                            type="checkbox" 
                                                            id="addon_checkbox-{{ $addon->id }}"
                                                            name="addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}"
                                                            data-price-type="per_night">
                                                        <label class="form-check-label" for="addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
                                                @endif

                                                @if($addon->billing_cycle === 'per_day')
                                                <div class="calendar-control mt-3">
                                                    <label class="form-label">
                                                        <i class="fa fa-calendar me-1"></i>Select Date Range
                                                    </label>
                                                    <div id="addon_calendar-{{ $addon->id }}" class="addon-calendar"></div>
                                                    
                                                    <input type="hidden" id="addon_date_from-{{ $addon->id }}" name="addon_date_from[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_date_to-{{ $addon->id }}" name="addon_date_to[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_selected_dates-{{ $addon->id }}" name="addon_selected_dates[{{ $addon->id }}]" value="">
                                                    
                                                    <div id="selected_dates_display-{{ $addon->id }}" class="mt-2 p-2 bg-light rounded">
                                                        <small>
                                                            <strong>Selected range:</strong> 
                                                            <span class="selected-dates-text">None</span>
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <strong>Days count:</strong> 
                                                            <span class="days-count-text">0 days</span>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="nights-control mt-3">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input per-night-nights"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="0" 
                                                        min="0" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        data-price-type="per_night"
                                                        placeholder="Number of days will auto-update"
                                                        readonly
                                                        style="background-color: #f8f9fa;">
                                                </div>
                                                @else
                                                <input type="hidden" id="addon_nights-{{ $addon->id }}" name="addon_nights[{{ $addon->id }}]" value="1">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="section-divider"></div>
                    </div>
                @endif

                @if($perItemAddons->count() > 0)
                    <div class="addon-section">
                        <h6 class="section-title">
                            <i class="fa fa-cubes"></i>
                            Per Item Add-ons
                        </h6>
                        <div class="accordion" id="perItemAddonsAccordion">
                            @foreach ($perItemAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="perItemHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#perItemCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="perItemCollapse{{ $addon->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>{{ $addon->name }}</strong>
                                                        @if($addon->quantity)
                                                            <small class="text-muted">({{ $addon->quantity }} available)</small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="addon-price-badge per-item">
                                                            ₱{{ number_format($addon->base_price, 2) }} per item
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="perItemCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="perItemHeading{{ $addon->id }}" 
                                             data-bs-parent="#perItemAddonsAccordion">
                                            <div class="accordion-body">
                                                @if($addon->description)
                                                    <p class="addon-description">{{ $addon->description }}</p>
                                                @endif
                                                
                                                <input type="hidden" name="addon_values[{{ $addon->id }}]" value="{{ $addon->base_price }}">
                                                <input type="hidden" name="addon_names[{{ $addon->id }}]" value="{{ $addon->name }}">
                                                <input type="hidden" name="addon_types[{{ $addon->id }}]" value="{{ $addon->price_type }}">
                                                <input type="hidden" name="addon_capacity[{{ $addon->id }}]" value="{{ $addon->capacity ?? 0 }}">
                                                <input type="hidden" name="addon_is_quantity_based[{{ $addon->id }}]" value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                <input type="hidden" name="addon_billing_cycle[{{ $addon->id }}]" value="{{ $addon->billing_cycle }}">
                                                
                                                @if($addon->is_based_on_quantity)
                                                    <div class="quantity-control">
                                                        <label for="addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>How many items
                                                            @if($addon->quantity)
                                                                <small class="text-muted">(Max: {{ $addon->quantity }} available)</small>
                                                            @endif
                                                        </label>
                                                        <input id="addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity per-item-quantity"
                                                            name="addon_quantity[{{ $addon->id }}]" 
                                                            value="0" 
                                                            min="0" 
                                                            max="{{ $addon->quantity ?? 999 }}"
                                                            step="1" 
                                                            data-addon-id="{{ $addon->id }}"
                                                            data-billing-cycle="{{ $addon->billing_cycle }}"
                                                            placeholder="Enter number of items"
                                                            disabled>
                                                    </div>
                                                @else
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox" 
                                                            type="checkbox" 
                                                            id="addon_checkbox-{{ $addon->id }}"
                                                            name="addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}"
                                                            data-billing-cycle="{{ $addon->billing_cycle }}"
                                                            disabled>
                                                        <label class="form-check-label" for="addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this item
                                                        </label>
                                                    </div>
                                                @endif

                                                @if($addon->billing_cycle === 'per_day')
                                                <div class="calendar-control mt-3">
                                                    <label class="form-label">
                                                        <i class="fa fa-calendar me-1"></i>Select Date Range
                                                    </label>
                                                    <div id="addon_calendar-{{ $addon->id }}" class="addon-calendar"></div>
                                                    
                                                    <input type="hidden" id="addon_date_from-{{ $addon->id }}" name="addon_date_from[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_date_to-{{ $addon->id }}" name="addon_date_to[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_selected_dates-{{ $addon->id }}" name="addon_selected_dates[{{ $addon->id }}]" value="">
                                                    
                                                    <div id="selected_dates_display-{{ $addon->id }}" class="mt-2 p-2 bg-light rounded">
                                                        <small>
                                                            <strong>Selected range:</strong> 
                                                            <span class="selected-dates-text">None</span>
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <strong>Days count:</strong> 
                                                            <span class="days-count-text">0 days</span>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="nights-control mt-3">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-calendar-day me-1"></i>How many days
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="0" 
                                                        min="0" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Number of days will auto-update"
                                                        readonly
                                                        style="background-color: #f8f9fa;">
                                                </div>
                                                @else
                                                <input type="hidden" id="addon_nights-{{ $addon->id }}" name="addon_nights[{{ $addon->id }}]" value="1">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="section-divider"></div>
                    </div>
                @endif

                @if($flatRateAddons->count() > 0)
                    <div class="addon-section">
                        <h6 class="section-title">
                            <i class="fa fa-tag"></i>
                            Fixed Price Add-ons
                        </h6>
                        <div class="accordion" id="flatRateAddonsAccordion">
                            @foreach ($flatRateAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flatRateHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#flatRateCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="flatRateCollapse{{ $addon->id }}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>{{ $addon->name }}</strong>
                                                        @if($addon->capacity)
                                                            <small class="text-muted">(Max: {{ $addon->capacity }} units)</small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="addon-price-badge flat-rate">
                                                            ₱{{ number_format($addon->base_price, 2) }} flat rate
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="flatRateCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="flatRateHeading{{ $addon->id }}" 
                                             data-bs-parent="#flatRateAddonsAccordion">
                                            <div class="accordion-body">
                                                @if($addon->description)
                                                    <p class="addon-description">{{ $addon->description }}</p>
                                                @endif
                                                
                                                <input type="hidden" name="addon_values[{{ $addon->id }}]" value="{{ $addon->base_price }}">
                                                <input type="hidden" name="addon_names[{{ $addon->id }}]" value="{{ $addon->name }}">
                                                <input type="hidden" name="addon_types[{{ $addon->id }}]" value="{{ $addon->price_type }}">
                                                <input type="hidden" name="addon_capacity[{{ $addon->id }}]" value="{{ $addon->capacity ?? 0 }}">
                                                <input type="hidden" name="addon_is_quantity_based[{{ $addon->id }}]" value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                <input type="hidden" name="addon_billing_cycle[{{ $addon->id }}]" value="{{ $addon->billing_cycle }}">
                                                
                                                @if($addon->is_based_on_quantity)
                                                    <div class="quantity-control">
                                                        <label for="addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>Quantity
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="addon_quantity[{{ $addon->id }}]" 
                                                            value="0" 
                                                            min="0" 
                                                            max="{{ $addon->capacity ?? 999 }}"
                                                            step="1" 
                                                            data-addon-id="{{ $addon->id }}"
                                                            placeholder="Enter quantity">
                                                    </div>
                                                @else
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox" 
                                                            type="checkbox" 
                                                            id="addon_checkbox-{{ $addon->id }}"
                                                            name="addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
                                                @endif

                                                @if($addon->billing_cycle === 'per_day')
                                                <div class="calendar-control mt-3">
                                                    <label class="form-label">
                                                        <i class="fa fa-calendar me-1"></i>Select Date Range
                                                    </label>
                                                    <div id="addon_calendar-{{ $addon->id }}" class="addon-calendar"></div>
                                                    
                                                    <input type="hidden" id="addon_date_from-{{ $addon->id }}" name="addon_date_from[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_date_to-{{ $addon->id }}" name="addon_date_to[{{ $addon->id }}]" value="">
                                                    <input type="hidden" id="addon_selected_dates-{{ $addon->id }}" name="addon_selected_dates[{{ $addon->id }}]" value="">
                                                    
                                                    <div id="selected_dates_display-{{ $addon->id }}" class="mt-2 p-2 bg-light rounded">
                                                        <small>
                                                            <strong>Selected range:</strong> 
                                                            <span class="selected-dates-text">None</span>
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <strong>Days count:</strong> 
                                                            <span class="days-count-text">0 days</span>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="nights-control mt-3">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="0" 
                                                        min="0" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Number of days will auto-update"
                                                        readonly
                                                        style="background-color: #f8f9fa;">
                                                </div>
                                                @else
                                                <input type="hidden" id="addon_nights-{{ $addon->id }}" name="addon_nights[{{ $addon->id }}]" value="1">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($perUnitAddons->count() == 0 && $perNightAddons->count() == 0 && $perItemAddons->count() == 0 && $flatRateAddons->count() == 0)
                    <div class="no-addons-message">
                        <i class="fa fa-info-circle me-2"></i>
                        No add-ons are currently available for this facility.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save Changes</button>
            </div>
        </div>
        
        @if($refundableAddons->count() > 0)
            @foreach($refundableAddons as $refundableAddon)
                <input type="hidden" name="refundable_addon_ids[]" value="{{ $refundableAddon->id }}">
                <input type="hidden" name="refundable_addon_names[{{ $refundableAddon->id }}]" value="{{ $refundableAddon->name }}">
                <input type="hidden" name="refundable_addon_prices[{{ $refundableAddon->id }}]" value="{{ $refundableAddon->base_price }}">
            @endforeach
        @endif

    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.addonCalendars = {};
    window.addonSelectedDates = {};
    window.addonUnavailableDates = @json($unavailableDates);
    window.perItemQuantityData = @json($perItemQuantityData);
    window.perContractRemainingCapacity = @json($perContractRemainingCapacity);
    initializeAddonsScript();
    observeFacilityDates();
});
function observeFacilityDates() {
    const dateFromInputs = document.querySelectorAll('input[name="date_from"], #date_from');
    const dateToInputs = document.querySelectorAll('input[name="date_to"], #date_to');
    [...dateFromInputs, ...dateToInputs].forEach(input => {
        if (input) {
            input.addEventListener('change', function() {
                setTimeout(() => {
                    initializeAllCalendars();
                    updateAddonControls();
                }, 100);
            });
        }
    });
    const confirmBtn = document.getElementById('confirm-dates');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            setTimeout(() => {
                initializeAllCalendars();
                updateAddonControls();
            }, 300);
        });
    }
}
function getFacilityDates() {
    let facilityStart = '';
    let facilityEnd = '';
    const hiddenDateFrom = document.querySelector('input[name="date_from"]');
    const hiddenDateTo = document.querySelector('input[name="date_to"]');
    if (hiddenDateFrom && hiddenDateFrom.value) {
        facilityStart = hiddenDateFrom.value;
    }
    if (hiddenDateTo && hiddenDateTo.value) {
        facilityEnd = hiddenDateTo.value;
    }
    if (!facilityStart || !facilityEnd) {
        const startDateDisplay = document.getElementById('start-date-display');
        const endDateDisplay = document.getElementById('end-date-display');
        if (startDateDisplay && startDateDisplay.textContent && endDateDisplay && endDateDisplay.textContent) {
            facilityStart = formatDateFromDisplay(startDateDisplay.textContent);
            facilityEnd = formatDateFromDisplay(endDateDisplay.textContent);
        }
    }
    return { facilityStart, facilityEnd };
}
function formatDateFromDisplay(dateString) {
    if (!dateString || dateString === 'Not selected') return '';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
    } catch (e) {
        return '';
    }
}
function waitForFullCalendar(callback, maxAttempts = 50) {
    let attempts = 0;
    const checkInterval = setInterval(() => {
        attempts++;
        if (typeof FullCalendar !== 'undefined' && typeof FullCalendar.Calendar !== 'undefined') {
            clearInterval(checkInterval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(checkInterval);
        }
    }, 100);
}
function initializeAllCalendars() {
    waitForFullCalendar(() => {
        const allAddons = [
            ...@json($perUnitAddons->pluck('id')->toArray()),
            ...@json($perNightAddons->pluck('id')->toArray()),
            ...@json($perItemAddons->pluck('id')->toArray()),
            ...@json($flatRateAddons->pluck('id')->toArray())
        ];
        const unavailableDates = window.addonUnavailableDates || {};
        const perItemQuantityData = window.perItemQuantityData || {};
        const addonsData = @json($filteredAddons->keyBy('id')->toArray());
        const { facilityStart, facilityEnd } = getFacilityDates();
        allAddons.forEach(addonId => {
            const calendarEl = document.getElementById(`addon_calendar-${addonId}`);
            if (!calendarEl) return;
            if (!facilityStart || !facilityEnd) {
                calendarEl.innerHTML = '<div class="alert alert-warning">Please select facility dates first</div>';
                return;
            }
            if (window.addonCalendars[addonId]) {
                window.addonCalendars[addonId].destroy();
            }
            calendarEl.innerHTML = '';
            const addon = addonsData[addonId];
            const isPerItem = addon && addon.price_type === 'per_item';
            let unavailableEvents = [];
            let quantityEvents = [];
            if (isPerItem && addon.billing_cycle === 'per_day') {
                const reservationData = perItemQuantityData[addonId] || [];
                reservationData.forEach(reservation => {
                    if (reservation.remaining_quantity === 0) {
                        unavailableEvents.push({
                            start: reservation.date_from,
                            end: reservation.date_to,
                            color: '#dc3545'
                        });
                    } else {
                        const datesInRange = getDatesBetween(reservation.date_from, reservation.date_to);
                        datesInRange.forEach(date => {
                            quantityEvents.push({
                                title: `${reservation.remaining_quantity} available`,
                                start: date,
                                color: '#ffc107'
                            });
                        });
                    }
                });
                const allDatesInRange = getDatesBetween(facilityStart, facilityEnd);
                allDatesInRange.forEach(date => {
                    const hasReservation = reservationData.some(res => {
                        const resStart = new Date(res.date_from);
                        const resEnd = new Date(res.date_to);
                        const currentDate = new Date(date);
                        return currentDate >= resStart && currentDate <= resEnd;
                    });
                    if (!hasReservation) {
                        const defaultQuantity = addon.quantity || 0;
                        if (defaultQuantity > 0) {
                            quantityEvents.push({
                                title: `${defaultQuantity} available`,
                                start: date,
                                color: '#28a745'
                            });
                        }
                    }
                });
            } else {
                unavailableEvents = (unavailableDates[addonId] || []).map(date => ({
                    start: date.date_from,
                    end: date.date_to,
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    display: 'background'
                }));
            }
            if (!window.addonSelectedDates[addonId]) {
                window.addonSelectedDates[addonId] = {
                    dateFrom: '',
                    dateTo: '',
                    selectedDates: []
                };
            }
            let selectedState = window.addonSelectedDates[addonId];
            let clickCount = 0;
            const selectedEvents = selectedState.selectedDates.map(date => ({
                start: date,
                backgroundColor: '#28a745',
                borderColor: '#28a745',
                display: 'background'
            }));
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: facilityStart,
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                validRange: {
                    start: facilityStart,
                    end: new Date(new Date(facilityEnd).getTime() + 86400000).toISOString().split('T')[0]
                },
                events: [...unavailableEvents, ...quantityEvents, ...selectedEvents],
                datesSet: function() {
                    setTimeout(() => {
                        calendar.updateSize();
                    }, 50);
                },
                dateClick: function(info) {
                    const clickedDate = info.dateStr;
                    const clickedDateTime = new Date(clickedDate).getTime();
                    const startTime = new Date(facilityStart).getTime();
                    const endTime = new Date(facilityEnd).getTime();
                    if (clickedDateTime < startTime || clickedDateTime > endTime) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Date Outside Range',
                            text: 'Please select dates within your facility booking period.',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                    const isUnavailable = unavailableEvents.some(event => {
                        const eventStart = new Date(event.start);
                        const eventEnd = new Date(event.end);
                        const clicked = new Date(clickedDate);
                        return clicked >= eventStart && clicked <= eventEnd;
                    });
                    if (isUnavailable) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Date Not Available',
                            text: 'The selected dates are not available.',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                    if (isPerItem && addon.billing_cycle === 'per_day') {
                        const availableQuantity = getAvailableQuantityForDate(addonId, clickedDate, addon.quantity || 0);
                        if (availableQuantity === 0) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Date Not Available',
                                text: 'No items available for the selected date.',
                                confirmButtonColor: '#3085d6'
                            });
                            return;
                        }
                    }
                    clickCount++;
                    if (clickCount === 1) {
                        selectedState.dateFrom = clickedDate;
                        selectedState.dateTo = '';
                        selectedState.selectedDates = [clickedDate];
                    } else if (clickCount === 2) {
                        selectedState.dateTo = clickedDate;
                        const fromDate = new Date(selectedState.dateFrom);
                        const toDate = new Date(selectedState.dateTo);
                        if (fromDate > toDate) {
                            const temp = selectedState.dateFrom;
                            selectedState.dateFrom = selectedState.dateTo;
                            selectedState.dateTo = temp;
                        }
                        selectedState.selectedDates = getDatesBetween(selectedState.dateFrom, selectedState.dateTo);
                        const allDatesAvailable = selectedState.selectedDates.every(date => {
                            if (unavailableEvents.some(event => {
                                const eventStart = new Date(event.start);
                                const eventEnd = new Date(event.end);
                                const currentDate = new Date(date);
                                return currentDate >= eventStart && currentDate <= eventEnd;
                            })) {
                                return false;
                            }
                            if (isPerItem && addon.billing_cycle === 'per_day') {
                                const availableQuantity = getAvailableQuantityForDate(addonId, date, addon.quantity || 0);
                                return availableQuantity > 0;
                            }
                            return true;
                        });
                        if (!allDatesAvailable) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Date Range Not Available',
                                text: 'Some selected dates are not available.',
                                confirmButtonColor: '#3085d6'
                            });
                            selectedState.dateFrom = '';
                            selectedState.dateTo = '';
                            selectedState.selectedDates = [];
                            clickCount = 0;
                        } else {
                            clickCount = 0;
                        }
                    }
                    if (clickCount > 2) {
                        clickCount = 1;
                        selectedState.dateFrom = clickedDate;
                        selectedState.dateTo = '';
                        selectedState.selectedDates = [clickedDate];
                    }
                    window.addonSelectedDates[addonId] = selectedState;
                    updateSelectedDatesDisplay(addonId, selectedState);
                    if (isPerItem && addon.billing_cycle === 'per_day') {
                        updatePerItemInputs(addonId, selectedState.selectedDates.length > 0);
                    }
                    const updatedSelectedEvents = selectedState.selectedDates.map(date => ({
                        start: date,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        display: 'background'
                    }));
                    calendar.removeAllEvents();
                    calendar.addEventSource([...unavailableEvents, ...quantityEvents, ...updatedSelectedEvents]);
                    updateNightsInput(addonId, selectedState.selectedDates.length);
                }
            });
            calendar.render();
            window.addonCalendars[addonId] = calendar;
            setTimeout(() => {
                calendar.updateSize();
            }, 300);
            updateSelectedDatesDisplay(addonId, selectedState);
            updateNightsInput(addonId, selectedState.selectedDates.length);
            if (isPerItem && addon.billing_cycle === 'per_day') {
                updatePerItemInputs(addonId, selectedState.selectedDates.length > 0);
            }
        });
    });
}
function updatePerItemInputs(addonId, hasSelectedDates) {
    const quantityInput = document.querySelector(`input[name="addon_quantity[${addonId}]"].per-item-quantity`);
    const checkboxInput = document.querySelector(`input[name="addon_checkbox[${addonId}]"][data-billing-cycle="per_day"]`);
    if (quantityInput) {
        quantityInput.disabled = !hasSelectedDates;
        if (!hasSelectedDates) {
            quantityInput.value = 0;
        }
    }
    if (checkboxInput) {
        checkboxInput.disabled = !hasSelectedDates;
        if (!hasSelectedDates) {
            checkboxInput.checked = false;
        }
    }
}
function getAvailableQuantityForDate(addonId, date, defaultQuantity) {
    const reservationData = window.perItemQuantityData[addonId] || [];
    let availableQuantity = defaultQuantity;
    reservationData.forEach(reservation => {
        const resStart = new Date(reservation.date_from);
        const resEnd = new Date(reservation.date_to);
        const checkDate = new Date(date);
        if (checkDate >= resStart && checkDate <= resEnd) {
            if (reservation.remaining_quantity < availableQuantity) {
                availableQuantity = reservation.remaining_quantity;
            }
        }
    });
    return availableQuantity;
}
function getDatesBetween(startDate, endDate) {
    const dates = [];
    const start = new Date(startDate);
    const end = new Date(endDate);
    const current = new Date(start);
    while (current <= end) {
        dates.push(current.toISOString().split('T')[0]);
        current.setDate(current.getDate() + 1);
    }
    return dates;
}
function updateSelectedDatesDisplay(addonId, selectedState) {
    const displayEl = document.querySelector(`#selected_dates_display-${addonId} .selected-dates-text`);
    const daysCountEl = document.querySelector(`#selected_dates_display-${addonId} .days-count-text`);
    const dateFromInput = document.getElementById(`addon_date_from-${addonId}`);
    const dateToInput = document.getElementById(`addon_date_to-${addonId}`);
    const selectedDatesInput = document.getElementById(`addon_selected_dates-${addonId}`);
    if (!displayEl || !daysCountEl) return;
    const daysCount = selectedState.selectedDates.length;
    if (selectedState.dateFrom && !selectedState.dateTo) {
        selectedState.dateTo = selectedState.dateFrom;
    }
    if (selectedState.dateFrom && selectedState.dateTo) {
        displayEl.textContent = `${selectedState.dateFrom} to ${selectedState.dateTo}`;
        daysCountEl.textContent = `${daysCount} days`;
        if (dateFromInput) dateFromInput.value = selectedState.dateFrom;
        if (dateToInput) dateToInput.value = selectedState.dateTo;
        if (selectedDatesInput) selectedDatesInput.value = JSON.stringify(selectedState.selectedDates);
    } else if (selectedState.dateFrom) {
        displayEl.textContent = `${selectedState.dateFrom} (select end date)`;
        daysCountEl.textContent = '1 day';
        if (dateFromInput) dateFromInput.value = selectedState.dateFrom;
        if (dateToInput) dateToInput.value = selectedState.dateFrom;
        if (selectedDatesInput) selectedDatesInput.value = JSON.stringify([selectedState.dateFrom]);
    } else {
        displayEl.textContent = 'None';
        daysCountEl.textContent = '0 days';
        if (dateFromInput) dateFromInput.value = '';
        if (dateToInput) dateToInput.value = '';
        if (selectedDatesInput) selectedDatesInput.value = '';
    }
    updateNightsInput(addonId, daysCount);
}
function updateNightsInput(addonId, daysCount) {
    const nightsInput = document.querySelector(`input[name="addon_nights[${addonId}]"]`);
    if (nightsInput) {
        nightsInput.value = daysCount;
    }
}
function lockPerContractControlsByCapacity(addonId) {
    const addonsData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonsData[addonId];
    if (!addon || addon.billing_cycle !== 'per_contract') return;
    const rem = window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] != null ? parseInt(window.perContractRemainingCapacity[addonId]) : null;
    const checkbox = document.querySelector(`input[name="addon_checkbox[${addonId}]"]`);
    const qtyInput = document.querySelector(`input[name="addon_quantity[${addonId}]"]`);
    const outOfCapacity = (rem === 0);
    if (checkbox) {
        checkbox.disabled = outOfCapacity || checkbox.disabled;
        if (outOfCapacity) checkbox.checked = false;
        checkbox.setAttribute('title', outOfCapacity ? 'No remaining capacity' : '');
    }
    if (qtyInput) {
        qtyInput.disabled = outOfCapacity || qtyInput.disabled;
        if (outOfCapacity) qtyInput.value = 0;
        qtyInput.setAttribute('title', outOfCapacity ? 'No remaining capacity' : '');
    }
}
function lockPerItemPerContractByQuantity(addonId) {
    const addonsData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonsData[addonId];
    if (!addon) return;
    if (addon.price_type !== 'per_item' || addon.billing_cycle !== 'per_contract') return;
    const qtyAvailable = parseInt(addon.quantity ?? 0);
    const checkbox = document.querySelector(`input[name="addon_checkbox[${addonId}]"]`);
    const qtyInput  = document.querySelector(`input[name="addon_quantity[${addonId}]"]`);
    if (checkbox && !addon.is_based_on_quantity) {
        if (qtyAvailable > 0) {
            checkbox.disabled = false;
            checkbox.removeAttribute('disabled');
            checkbox.title = '';
        } else {
            checkbox.disabled = true;
            checkbox.checked = false;
            checkbox.title = 'No items available';
        }
    }
    if (qtyInput && addon.is_based_on_quantity) {
        if (qtyAvailable > 0) {
            qtyInput.disabled = false;
            qtyInput.removeAttribute('disabled');
            qtyInput.max = qtyAvailable;
            if ((parseInt(qtyInput.value)||0) > qtyAvailable) {
                qtyInput.value = qtyAvailable;
            }
            qtyInput.title = '';
        } else {
            qtyInput.disabled = true;
            qtyInput.value = 0;
            qtyInput.title = 'No items available';
        }
    }
}
function updateAddonControls() {
    const { facilityStart, facilityEnd } = getFacilityDates();
    const hasValidDateRange = facilityStart && facilityEnd;
    document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
        const isPerDay = (checkbox.getAttribute('data-billing-cycle') === 'per_day');
        if (isPerDay) {
            checkbox.disabled = !hasValidDateRange;
            if (!hasValidDateRange) checkbox.checked = false;
        }
    });
    document.querySelectorAll('.addon-quantity').forEach(input => {
        const isPerDay = (input.getAttribute('data-billing-cycle') === 'per_day');
        if (isPerDay) {
            input.disabled = !hasValidDateRange;
            if (!hasValidDateRange) input.value = 0;
        }
    });
    document.querySelectorAll('.nights-input').forEach(input => {
        const isPerNight = (input.getAttribute('data-price-type') === 'per_night');
        if (isPerNight) {
            input.disabled = !hasValidDateRange;
            if (!hasValidDateRange) input.value = 1;
        }
    });
    const addonsData = @json($filteredAddons->keyBy('id')->toArray());
    Object.keys(addonsData).forEach(id => {
        lockPerContractControlsByCapacity(id);
        lockPerItemPerContractByQuantity(id);
    });
}
function validatePerItemQuantity(input, addonId) {
    const addonData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonData[addonId];
    if (!addon || addon.price_type !== 'per_item') return true;
    const inputValue = parseInt(input.value) || 0;
    if (addon.billing_cycle === 'per_contract') {
        if (addon.quantity !== null && typeof addon.quantity !== 'undefined') {
            const limit = parseInt(addon.quantity);
            if (inputValue > limit) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantity Limit Exceeded',
                    text: `Only ${limit} items available. You entered ${inputValue}.`,
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    input.value = limit;
                    input.focus();
                });
                return false;
            }
        }
        return true;
    }
    if (addon.billing_cycle !== 'per_day') return true;
    const selectedState = window.addonSelectedDates[addonId];
    if (!selectedState || selectedState.selectedDates.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Select Dates First',
            text: 'Please select dates before entering quantity.',
            confirmButtonColor: '#3085d6'
        }).then(() => {
            input.value = 0;
            input.focus();
        });
        return false;
    }
    const perItemQuantityData = window.perItemQuantityData[addonId] || [];
    let minAvailableQuantity = addon.quantity || 0;
    selectedState.selectedDates.forEach(date => {
        let dateAvailableQuantity = addon.quantity || 0;
        perItemQuantityData.forEach(reservation => {
            const resStart = new Date(reservation.date_from);
            const resEnd = new Date(reservation.date_to);
            const checkDate = new Date(date);
            if (checkDate >= resStart && checkDate <= resEnd) {
                if (reservation.remaining_quantity < dateAvailableQuantity) {
                    dateAvailableQuantity = reservation.remaining_quantity;
                }
            }
        });
        if (dateAvailableQuantity < minAvailableQuantity) {
            minAvailableQuantity = dateAvailableQuantity;
        }
    });
    if (inputValue > minAvailableQuantity) {
        Swal.fire({
            icon: 'error',
            title: 'Quantity Limit Exceeded',
            text: `Only ${minAvailableQuantity} items available for the selected dates. You entered ${inputValue}.`,
            confirmButtonColor: '#3085d6'
        }).then(() => {
            input.value = minAvailableQuantity;
            input.focus();
        });
        return false;
    }
    return true;
}
function validatePerNightQuantity(input, addonId) {
    const addonData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonData[addonId];
    if (!addon || addon.price_type !== 'per_night') return true;
    if (addon.quantity === null || typeof addon.quantity === 'undefined') return true;
    const inputValue = parseInt(input.value) || 0;
    const limit = parseInt(addon.quantity);
    if (inputValue > limit) {
        Swal.fire({
            icon: 'error',
            title: 'Quantity Limit Exceeded',
            text: `Only ${limit} available. You entered ${inputValue}.`,
            confirmButtonColor: '#3085d6'
        }).then(() => {
            input.value = limit;
            input.focus();
        });
        return false;
    }
    return true;
}
function validatePerUnitPerContractQuantity(input, addonId) {
    const addonsData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonsData[addonId];
    if (!addon) return true;
    if (addon.price_type !== 'per_unit') return true;
    if (addon.billing_cycle !== 'per_contract') return true;
    const inputValue = parseInt(input.value) || 0;
    const hardCap = (addon.capacity != null) ? parseInt(addon.capacity) : null;
    const contractCap = (window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] != null) ? parseInt(window.perContractRemainingCapacity[addonId]) : null;
    const limits = [hardCap, contractCap].filter(v => v != null);
    const limit = limits.length ? Math.min(...limits) : null;
    if (limit != null && inputValue > limit) {
        Swal.fire({
            icon: 'error',
            title: 'Quantity Limit Exceeded',
            text: `Only ${limit} units available for this add-on.`,
            confirmButtonColor: '#3085d6'
        }).then(() => {
            input.value = Math.max(0, limit);
            input.focus();
        });
        return false;
    }
    return true;
}
function calculatePerNightPrice(addonId, basePrice, quantity, nights) {
    const addonData = @json($filteredAddons->keyBy('id')->toArray());
    const addon = addonData[addonId];
    if (!addon || addon.price_type !== 'per_night') return 0;
    let total = basePrice * nights;
    if (addon.is_based_on_quantity && quantity > 0) {
        total = total * quantity;
    }
    return total;
}
function initializeAddonsScript() {
    let addonsData;
    try {
        addonsData = @json($filteredAddons->keyBy('id')->toArray());
        if (!addonsData) return;
    } catch (e) {
        return;
    }
    let savedAddonsState = {
        checkboxes: {},
        quantities: {},
        nights: {},
        dateFrom: {},
        dateTo: {}
    };
    window.savedAddonsState = savedAddonsState;
    function validateCheckboxRequired(addonId) {
        const addonData = @json($filteredAddons->keyBy('id')->toArray());
        const addon = addonData[addonId];
        if (!addon || addon.billing_cycle !== 'per_contract') {
            return true;
        }
        const checkbox = document.querySelector(`input[name="addon_checkbox[${addonId}]"]`);
        const quantityInput = document.querySelector(`input[name="addon_quantity[${addonId}]"]`);
        if (checkbox && !addon.is_based_on_quantity) {
            return checkbox.checked;
        }
        if (quantityInput && addon.is_based_on_quantity) {
            return parseInt(quantityInput.value) > 0;
        }
        return true;
    }
    function saveCurrentTempState() {
        const addonsData = @json($filteredAddons->keyBy('id')->toArray());
        document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
            const addonId = checkbox.getAttribute('data-addon-id');
            const addon = addonsData[addonId];
            if (!checkbox.disabled && validateCheckboxRequired(addonId)) {
                savedAddonsState.checkboxes[addonId] = checkbox.checked;
            } else {
                savedAddonsState.checkboxes[addonId] = false;
            }
        });
        document.querySelectorAll('.addon-quantity').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            const addon = addonsData[addonId];
            if (!input.disabled && validateCheckboxRequired(addonId)) {
                savedAddonsState.quantities[addonId] = parseInt(input.value) || 0;
            } else {
                savedAddonsState.quantities[addonId] = 0;
            }
        });
        document.querySelectorAll('.nights-input').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            if (!input.disabled) {
                savedAddonsState.nights[addonId] = parseInt(input.value) || 1;
            }
        });
        Object.keys(window.addonSelectedDates).forEach(addonId => {
            const selectedState = window.addonSelectedDates[addonId];
            savedAddonsState.dateFrom[addonId] = selectedState.dateFrom;
            savedAddonsState.dateTo[addonId] = selectedState.dateTo;
        });
    }
    function updateSelectedAddonsDisplay() {
        const selectedAddonsDisplay = document.getElementById('selected-addons-display');
        if (!selectedAddonsDisplay) return;
        let selectedAddons = [];
        for (let addonId in savedAddonsState.checkboxes) {
            if (savedAddonsState.checkboxes[addonId]) {
                const addon = addonsData[addonId];
                if (addon) {
                    let totalPrice = parseFloat(addon.base_price);
                    let days = savedAddonsState.nights[addonId] || 1;
                    let dateFrom = savedAddonsState.dateFrom[addonId] || '';
                    let dateTo = savedAddonsState.dateTo[addonId] || '';
                    if (addon.price_type === 'per_night') {
                        totalPrice = calculatePerNightPrice(addonId, addon.base_price, 0, days);
                    } else if (addon.billing_cycle === 'per_day' && (addon.price_type === 'per_unit' || addon.price_type === 'flat_rate' || addon.price_type === 'per_item')) {
                        totalPrice = totalPrice * days;
                    }
                    selectedAddons.push({
                        name: addon.name,
                        price: totalPrice,
                        days: days,
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                        addonId: addonId,
                        price_type: addon.price_type,
                        billing_cycle: addon.billing_cycle
                    });
                }
            }
        }
        for (let addonId in savedAddonsState.quantities) {
            const quantity = savedAddonsState.quantities[addonId];
            if (quantity > 0) {
                const addon = addonsData[addonId];
                if (addon) {
                    let totalPrice = parseFloat(addon.base_price);
                    let days = savedAddonsState.nights[addonId] || 1;
                    let dateFrom = savedAddonsState.dateFrom[addonId] || '';
                    let dateTo = savedAddonsState.dateTo[addonId] || '';
                    if (addon.price_type === 'per_night') {
                        totalPrice = calculatePerNightPrice(addonId, addon.base_price, quantity, days);
                    } else if (addon.billing_cycle === 'per_day') {
                        if (addon.price_type === 'per_unit') {
                            totalPrice = totalPrice * quantity * days;
                        } else if (addon.price_type === 'per_item') {
                            totalPrice = totalPrice * quantity * days;
                        } else {
                            totalPrice = totalPrice * quantity * days;
                        }
                    } else {
                        if (addon.price_type === 'per_unit') {
                            totalPrice = totalPrice * quantity;
                        } else if (addon.price_type === 'per_item') {
                            totalPrice = totalPrice * quantity;
                        } else {
                            totalPrice = totalPrice * quantity;
                        }
                    }
                    selectedAddons.push({
                        name: addon.name,
                        price: totalPrice,
                        quantity: quantity,
                        days: days,
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                        addonId: addonId,
                        price_type: addon.price_type,
                        billing_cycle: addon.billing_cycle
                    });
                }
            }
        }
        const parentContainer = selectedAddonsDisplay.parentNode;
        const existingRows = parentContainer.querySelectorAll('.row');
        existingRows.forEach(row => {
            if (row.querySelector('.client-type')) {
                row.remove();
            }
        });
        if (selectedAddons.length > 0) {
            const rowContainer = document.createElement('div');
            rowContainer.className = 'row';
            selectedAddons.forEach((addon) => {
                const colElement = document.createElement('div');
                colElement.className = 'col-md-6';
                const addonElement = document.createElement('div');
                addonElement.className = 'client-type';
                addonElement.style.cssText = `
                        margin-bottom: 15px;
                        padding: 15px;
                        min-height: 100px;
                        display: flex;
                        flex-direction: column;
                        justify-content: space-between;
                        border-radius: 8px;
                        background: #f3f4f6;
                        border: 1px solid #e5e7eb;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                    `;
                let quantityHtml = addon.quantity ? `Qty: ${addon.quantity}` : '&nbsp;';
                let daysHtml = addon.days && addon.billing_cycle === 'per_day' && addon.price_type !== 'per_item' ? `Days: ${addon.days}` : '';
                let dateRangeHtml = '';
                if (addon.dateFrom && addon.dateTo && addon.billing_cycle === 'per_day') {
                    dateRangeHtml = `<div style="font-size: 0.9em; color: #059669; margin-bottom: 2px;">
                            Dates: ${addon.dateFrom} to ${addon.dateTo}
                        </div>`;
                } else if (addon.dateFrom && addon.billing_cycle === 'per_day') {
                    dateRangeHtml = `<div style="font-size: 0.9em; color: #059669; margin-bottom: 2px;">
                            Date: ${addon.dateFrom}
                        </div>`;
                }
                addonElement.innerHTML = `
                        <div style="font-weight: 600; margin-bottom: 4px; color: #111827;">
                            Name: ${addon.name}
                        </div>
                        <div style="font-weight: 500; margin-bottom: 4px; color: #0066cc;">
                            Price: ₱${addon.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </div>
                        ${dateRangeHtml}
                        <div style="font-size: 0.9em; color: #374151; margin-bottom: 2px;">
                            ${quantityHtml}
                        </div>
                        ${daysHtml ? `<div style="font-size: 0.9em; color: #374151;">${daysHtml}</div>` : ''}
                    `;
                colElement.appendChild(addonElement);
                rowContainer.appendChild(colElement);
            });
            parentContainer.insertBefore(rowContainer, selectedAddonsDisplay);
        }
    }
    const modalElement = document.getElementById('addonsModal');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            setTimeout(() => {
                initializeAllCalendars();
                updateAddonControls();
            }, 100);
        });
        modalElement.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                Object.values(window.addonCalendars).forEach(calendar => {
                    if (calendar) {
                        calendar.updateSize();
                    }
                });
            }, 100);
        });
        const saveBtn = document.querySelector('#addonsModal .btn-primary[data-bs-dismiss="modal"]');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                saveCurrentTempState();
                updateSelectedAddonsDisplay();
            });
        }
    }
    const accordions = document.querySelectorAll('.accordion-collapse');
    accordions.forEach(accordion => {
        accordion.addEventListener('shown.bs.collapse', function() {
            const addonId = this.id.replace('perUnitCollapse', '').replace('perNightCollapse', '').replace('perItemCollapse', '').replace('flatRateCollapse', '');
            const calendar = window.addonCalendars[addonId];
            if (calendar) {
                setTimeout(() => {
                    calendar.updateSize();
                }, 150);
            }
        });
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('addon-quantity')) {
            const addonId = e.target.getAttribute('data-addon-id');
            if (addonId) {
                setTimeout(() => validatePerItemQuantity(e.target, addonId), 100);
                setTimeout(() => validatePerUnitPerContractQuantity(e.target, addonId), 110);
            }
        }
        if (e.target.classList.contains('per-night-quantity')) {
            const addonId = e.target.getAttribute('data-addon-id');
            if (addonId) {
                setTimeout(() => validatePerNightQuantity(e.target, addonId), 50);
            }
        }
    });
    updateAddonControls();
}
</script>




@endpush