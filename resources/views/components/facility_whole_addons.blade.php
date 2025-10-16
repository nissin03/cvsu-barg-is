@php
    $filteredAddons = $facility->addons->filter(function($addon) use ($facility) {
        return $addon->facility_id === $facility->id &&
               ($addon->facility_attribute_id === null || $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
               $addon->is_available == true &&
               $addon->is_refundable == false &&
               $addon->show === 'both';
    });

       // Add filtering for refundable addons
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
@endphp



<style>
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
    #addonsModal .nights-label {
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
    #addonsModal .nights-control {
        margin-top: 1rem;
    }
    #addonsModal .form-control:disabled {
        background-color: #e9ecef !important;
        opacity: 0.6;
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
                                                            <small class="text-muted">(Max: {{ $addon->capacity }} per person/unit)</small>
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
                                                <div class="nights-control">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                        <small class="text-muted">(Select dates first)</small>
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="1" 
                                                        min="1" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Enter number of days"
                                                        disabled>
                                                </div>
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
                                                <div class="nights-control">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                        <small class="text-muted">(Select dates first)</small>
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="1" 
                                                        min="1" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Enter number of days"
                                                        disabled>
                                                </div>
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
                                                <input type="hidden" name="addon_quantity[{{ $addon->id }}]" value="{{ $addon->quantity ?? 0 }}">
                                                <input type="hidden" name="addon_is_quantity_based[{{ $addon->id }}]" value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                
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
                                                            class="form-control quantity-input addon-quantity"
                                                            name="addon_quantity[{{ $addon->id }}]" 
                                                            value="0" 
                                                            min="0" 
                                                            max="{{ $addon->quantity ?? 999 }}"
                                                            step="1" 
                                                            data-addon-id="{{ $addon->id }}"
                                                            placeholder="Enter number of items">
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
                                                            <i class="fa fa-check me-1"></i>Include this item
                                                        </label>
                                                    </div>
                                                @endif
                                                <div class="nights-control">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                        <small class="text-muted">(Select dates first)</small>
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="1" 
                                                        min="1" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Enter number of days"
                                                        disabled>
                                                </div>
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
                                                <div class="nights-control">
                                                    <label for="addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many days
                                                        <small class="text-muted">(Select dates first)</small>
                                                    </label>
                                                    <input id="addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="addon_nights[{{ $addon->id }}]" 
                                                        value="1" 
                                                        min="1" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Enter number of days"
                                                        disabled>
                                                </div>
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
    setTimeout(function() {
        initializeAddonsScript();
    }, 200);
});

function initializeAddonsScript() {
    let addonsData;
    try {
        addonsData = @json($filteredAddons->keyBy('id')->toArray());
        if (!addonsData || Object.keys(addonsData).length === 0) {
            return;
        }
    } catch (e) {
        return;
    }
    
    let savedAddonsState = {
        checkboxes: {},
        quantities: {},
        nights: {}
    };
    
    let tempAddonsState = {
        checkboxes: {},
        quantities: {},
        nights: {}
    };
    
    let lastDateRange = {
        startDate: null,
        endDate: null,
        daysDiff: 0
    };
    
    window.savedAddonsState = savedAddonsState;
    
    function calculateDaysDiff(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        const start = new Date(startDate);
        const end = new Date(endDate);
        const timeDiff = end - start;
        return Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
    }
    
    function getSelectedDateRange() {
        let startDate = document.getElementById('date_from')?.value;
        let endDate = document.getElementById('date_to')?.value;
        
        const daysDiff = calculateDaysDiff(startDate, endDate);
        return { startDate, endDate, daysDiff };
    }
    
    function clearAllAddonsState() {
        savedAddonsState = {
            checkboxes: {},
            quantities: {},
            nights: {}
        };
        tempAddonsState = {
            checkboxes: {},
            quantities: {},
            nights: {}
        };
        window.savedAddonsState = savedAddonsState;
        
        document.querySelectorAll('#addonsModal .addon-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        document.querySelectorAll('#addonsModal .addon-quantity').forEach(input => {
            input.value = 0;
        });
        
        document.querySelectorAll('#addonsModal .nights-input').forEach(input => {
            input.value = 1;
        });
        
        updateSelectedAddonsDisplay();
        triggerAddonsUpdate();
    }
    
    function checkDateRangeChange() {
        const { startDate, endDate, daysDiff } = getSelectedDateRange();
        
        if (lastDateRange.startDate !== startDate || lastDateRange.endDate !== endDate) {
            if (lastDateRange.startDate !== null || lastDateRange.endDate !== null) {
                clearAllAddonsState();
            }
            
            lastDateRange = { startDate, endDate, daysDiff };
        }
    }
    
    function hasSelectedAddons(useTemp = false) {
        const state = useTemp ? tempAddonsState : savedAddonsState;
        
        for (let addonId in state.checkboxes) {
            if (state.checkboxes[addonId]) {
                return true;
            }
        }
        
        for (let addonId in state.quantities) {
            if (parseInt(state.quantities[addonId]) > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    function saveCurrentTempState() {
        document.querySelectorAll('#addonsModal .addon-checkbox').forEach(checkbox => {
            const addonId = checkbox.getAttribute('data-addon-id');
            tempAddonsState.checkboxes[addonId] = checkbox.checked;
        });
        
        document.querySelectorAll('#addonsModal .addon-quantity').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            tempAddonsState.quantities[addonId] = parseInt(input.value) || 0;
        });
        
        document.querySelectorAll('#addonsModal .nights-input').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            tempAddonsState.nights[addonId] = parseInt(input.value) || 1;
        });
    }
    
    function loadStateToForm(state) {
        document.querySelectorAll('#addonsModal .addon-checkbox').forEach(checkbox => {
            const addonId = checkbox.getAttribute('data-addon-id');
            checkbox.checked = state.checkboxes[addonId] || false;
        });
        
        document.querySelectorAll('#addonsModal .addon-quantity').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            input.value = state.quantities[addonId] || 0;
        });
        
        document.querySelectorAll('#addonsModal .nights-input').forEach(input => {
            const addonId = input.getAttribute('data-addon-id');
            input.value = state.nights[addonId] || 1;
        });
    }
    
    function commitTempState() {
        savedAddonsState.checkboxes = { ...tempAddonsState.checkboxes };
        savedAddonsState.quantities = { ...tempAddonsState.quantities };
        savedAddonsState.nights = { ...tempAddonsState.nights };
    }
    
    function discardTempState() {
        tempAddonsState.checkboxes = { ...savedAddonsState.checkboxes };
        tempAddonsState.quantities = { ...savedAddonsState.quantities };
        tempAddonsState.nights = { ...savedAddonsState.nights };
    }
    
    function updateSelectedAddonsDisplay() {
        const selectedAddonsDisplay = document.getElementById('selected-addons-display');
        if (!selectedAddonsDisplay) return;
        
        let selectedAddons = [];
        
        for (let addonId in savedAddonsState.checkboxes) {
            if (savedAddonsState.checkboxes[addonId]) {
                const addon = addonsData[addonId];
                if (addon) {
                    let displayText = addon.name;
                    let totalPrice = parseFloat(addon.base_price);
                    let days = savedAddonsState.nights[addonId] || 1;
                    
                    if (addon.price_type === 'per_night' || addon.price_type === 'per_unit' || addon.price_type === 'per_item' || addon.price_type === 'flat_rate') {
                        totalPrice = totalPrice * days;
                    }
                    
                    selectedAddons.push({
                        name: displayText,
                        price: totalPrice,
                        days: days
                    });
                }
            }
        }
        
        for (let addonId in savedAddonsState.quantities) {
            const quantity = savedAddonsState.quantities[addonId];
            if (quantity > 0) {
                const addon = addonsData[addonId];
                if (addon) {
                    let displayText = addon.name;
                    let totalPrice = parseFloat(addon.base_price);
                    let days = savedAddonsState.nights[addonId] || 1;
                    
                    if (addon.price_type === 'per_unit') {
                        totalPrice = totalPrice * quantity * days;
                    } else if (addon.price_type === 'per_night') {
                        totalPrice = totalPrice * quantity * days;
                    } else if (addon.price_type === 'per_item') {
                        totalPrice = totalPrice * quantity * days;
                    } else {
                        totalPrice = totalPrice * quantity * days;
                    }
                    
                    selectedAddons.push({
                        name: displayText,
                        price: totalPrice,
                        quantity: quantity,
                        days: days
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
            
            selectedAddons.forEach((addon, index) => {
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
                let daysHtml = `Days: ${addon.days}`;
                
                addonElement.innerHTML = `
                    <div style="font-weight: 600; margin-bottom: 4px; color: #111827;">
                        Name: ${addon.name}
                    </div>
                    <div style="font-weight: 500; margin-bottom: 4px; color: #0066cc;">
                        Price: ₱${addon.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                    <div style="font-size: 0.9em; color: #374151; margin-bottom: 2px;">
                        ${quantityHtml}
                    </div>
                    <div style="font-size: 0.9em; color: #374151;">
                        ${daysHtml}
                    </div>
                `;
                
                colElement.appendChild(addonElement);
                rowContainer.appendChild(colElement);
            });
            
            parentContainer.insertBefore(rowContainer, selectedAddonsDisplay);
        }
    }
    
    function validatePerItemQuantity(input, addonId) {
        const addon = addonsData[addonId];
        if (!addon || addon.price_type !== 'per_item') return true;
        
        const inputValue = parseInt(input.value) || 0;
        const availableQuantity = parseInt(addon.quantity) || 0;
        
        if (inputValue > availableQuantity && inputValue > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantity Limit Exceeded',
                    text: `The quantity for "${addon.name}" cannot exceed ${availableQuantity} items. You entered ${inputValue}.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    input.value = availableQuantity;
                    input.focus();
                });
            } else {
                alert(`The quantity for "${addon.name}" cannot exceed ${availableQuantity} items. You entered ${inputValue}.`);
                input.value = availableQuantity;
                input.focus();
            }
            return false;
        }
        
        return true;
    }
    
    function validateDaysInput(nightsInput, addonId) {
        const addon = addonsData[addonId];
        if (!addon) return true;
        
        const { startDate, endDate, daysDiff } = getSelectedDateRange();
        const nightsValue = parseInt(nightsInput.value) || 0;
        
        if (nightsValue > daysDiff && nightsValue > 0 && daysDiff > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Number of Days',
                    text: `The number of days for "${addon.name}" (${nightsValue}) cannot exceed the selected period (${daysDiff} days). Please enter a value equal to or less than ${daysDiff}.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    nightsInput.value = daysDiff > 0 ? daysDiff : 1;
                    nightsInput.focus();
                });
            } else {
                alert(`The number of days for "${addon.name}" (${nightsValue}) cannot exceed the selected period (${daysDiff} days).`);
                nightsInput.value = daysDiff > 0 ? daysDiff : 1;
                nightsInput.focus();
            }
            return false;
        }
        
        return true;
    }
    
    function updateDaysControls() {
        checkDateRangeChange();
        
        const { startDate, endDate, daysDiff } = getSelectedDateRange();
        const hasValidDateRange = startDate && endDate && daysDiff > 0;
        
        Object.keys(addonsData).forEach(addonId => {
            const addon = addonsData[addonId];
            const nightsInput = document.querySelector(`#addonsModal input[name="addon_nights[${addonId}]"]`);
            const checkbox = document.querySelector(`#addonsModal input[name="addon_checkbox[${addonId}]"]`);
            const quantityInput = document.querySelector(`#addonsModal input[name="addon_quantity[${addonId}]"]`);
            
            if (nightsInput) {
                nightsInput.disabled = !hasValidDateRange;
                if (hasValidDateRange) {
                    nightsInput.max = daysDiff;
                    if (parseInt(nightsInput.value) > daysDiff) {
                        nightsInput.value = daysDiff;
                    }
                } else {
                    nightsInput.value = 1;
                }
            }
            
            if (checkbox) {
                checkbox.disabled = !hasValidDateRange;
                if (!hasValidDateRange) {
                    checkbox.checked = false;
                }
            }
            
            if (quantityInput && addon.is_based_on_quantity) {
                quantityInput.disabled = !hasValidDateRange;
                if (!hasValidDateRange) {
                    quantityInput.value = 0;
                }
            }
        });
    }
    
    function triggerAddonsUpdate() {
        const event = new CustomEvent('addonsUpdated');
        document.dispatchEvent(event);
    }
    
    function setupEventListeners() {
        const modalElement = document.getElementById('addonsModal');
        if (!modalElement) return;
        
        modalElement.addEventListener('show.bs.modal', function(e) {
            discardTempState();
            loadStateToForm(savedAddonsState);
            setTimeout(updateDaysControls, 300);
        });
        
        const saveBtn = document.querySelector('#addonsModal .btn-primary[data-bs-dismiss="modal"]');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                saveCurrentTempState();
                commitTempState();
                updateSelectedAddonsDisplay();
                triggerAddonsUpdate();
            });
        }
        
        const cancelBtn = document.querySelector('#addonsModal .btn-secondary[data-bs-dismiss="modal"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                discardTempState();
            });
        }
        
        modalElement.addEventListener('hidden.bs.modal', function(e) {
            discardTempState();
            loadStateToForm(savedAddonsState);
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('addon-quantity') && e.target.closest('#addonsModal')) {
                const addonId = e.target.getAttribute('data-addon-id');
                if (addonId) {
                    setTimeout(() => validatePerItemQuantity(e.target, addonId), 100);
                }
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('nights-input') && e.target.closest('#addonsModal')) {
                const addonId = e.target.getAttribute('data-addon-id');
                if (addonId) {
                    setTimeout(() => validateDaysInput(e.target, addonId), 100);
                }
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.type === 'date') {
                if (e.target.id === 'date_from' || e.target.id === 'date_to') {
                    setTimeout(() => {
                        checkDateRangeChange();
                        updateDaysControls();
                    }, 100);
                }
            }
        });
        
        const confirmBtn = document.getElementById('confirm-dates');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                setTimeout(() => {
                    checkDateRangeChange();
                    updateDaysControls();
                }, 500);
            });
        }
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const target = mutation.target;
                    if ((target.id === 'date_from' || target.id === 'date_to')) {
                        setTimeout(() => {
                            checkDateRangeChange();
                            updateDaysControls();
                        }, 100);
                    }
                }
            });
        });
        
        const dateInputs = ['date_from', 'date_to'];
        dateInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                observer.observe(input, { attributes: true, attributeFilter: ['value'] });
                input.addEventListener('input', function() {
                    if (this.id === 'date_from' || this.id === 'date_to') {
                        setTimeout(() => {
                            checkDateRangeChange();
                            updateDaysControls();
                        }, 100);
                    }
                });
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('addon-quantity') || 
                e.target.classList.contains('nights-input') ||
                e.target.classList.contains('addon-checkbox')) {
                triggerAddonsUpdate();
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('addon-checkbox')) {
                triggerAddonsUpdate();
            }
        });
        
        document.addEventListener('submit', function(e) {
            if (!hasSelectedAddons()) {
                return;
            }
            
            let hasErrors = false;
            
            document.querySelectorAll('#addonsModal .addon-quantity').forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                if (addonId && input.value && parseInt(input.value) > 0) {
                    if (!validatePerItemQuantity(input, addonId)) {
                        hasErrors = true;
                    }
                }
            });
            
            document.querySelectorAll('#addonsModal .nights-input').forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                if (addonId && input.value && parseInt(input.value) > 0) {
                    if (!validateDaysInput(input, addonId)) {
                        hasErrors = true;
                    }
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
            }
        });
    }
    
    setupEventListeners();
    updateDaysControls();
    
    const { startDate, endDate, daysDiff } = getSelectedDateRange();
    lastDateRange = { startDate, endDate, daysDiff };
}
</script>
@endpush