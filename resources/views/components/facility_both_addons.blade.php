@php
    $filteredAddons = $facility->addons->filter(function($addon) use ($facility) {
        return $addon->facility_id === $facility->id &&
               ($addon->facility_attribute_id === null || $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
               $addon->is_available == true &&
               $addon->is_refundable == false &&
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
    #{{ $section }}AddonsModal .addon-card {
        border: 1px solid #e9ecef !important;
        border-radius: 12px !important;
        transition: all 0.3s ease;
        overflow: hidden;
        background: #ffffff;
        margin-bottom: 0 !important;
    }

    #{{ $section }}AddonsModal .addon-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    #{{ $section }}AddonsModal .section-divider {
        border-bottom: 2px solid #e9ecef;
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.5rem;
    }

    #{{ $section }}AddonsModal .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #{{ $section }}AddonsModal .section-title i {
        color: #0044cc;
    }

    #{{ $section }}AddonsModal .no-addons-message {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    #{{ $section }}AddonsModal .accordion-button {
        background: #ffffff !important;
        border: 1px solid #e9ecef !important;
        padding: 1.25rem 1.5rem;
        font-weight: 500;
        color: #495057 !important;
        box-shadow: none !important;
        border-radius: 12px 12px 0 0 !important;
    }

    #{{ $section }}AddonsModal .accordion-button:not(.collapsed) {
        background: #f8f9fa !important;
        color: #212529 !important;
        box-shadow: none !important;
        border-color: #dee2e6 !important;
    }

    #{{ $section }}AddonsModal .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05) !important;
        border-color: #ced4da !important;
    }

    #{{ $section }}AddonsModal .accordion-button::after {
        filter: brightness(0.6);
    }

    #{{ $section }}AddonsModal .accordion-button:not(.collapsed)::after {
        filter: brightness(0.4);
    }

    #{{ $section }}AddonsModal .accordion-body {
        padding: 1.5rem;
        background: #fafbfc;
        border-radius: 0 0 12px 12px;
    }

    #{{ $section }}AddonsModal .addon-price-badge {
        background: #117a11 !important;
        color: white;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: none;
    }

    #{{ $section }}AddonsModal .addon-price-badge.per-unit {
        background: #0044cc !important;
    }

    #{{ $section }}AddonsModal .addon-price-badge.per-night {
        background: #6610f2 !important;
    }

    #{{ $section }}AddonsModal .addon-price-badge.per-item {
        background: #20c997 !important;
    }

    #{{ $section }}AddonsModal .addon-price-badge.flat-rate {
        background: #fd7e14 !important;
    }

    #{{ $section }}AddonsModal .addon-description {
        font-size: 0.9rem;
        line-height: 1.5;
        color: #6c757d;
        margin-bottom: 1.5rem;
        font-style: italic;
    }

    #{{ $section }}AddonsModal .form-control {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    #{{ $section }}AddonsModal .form-control:focus {
        border-color: #495057 !important;
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important;
    }

    #{{ $section }}AddonsModal .quantity-label, 
    #{{ $section }}AddonsModal .nights-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    #{{ $section }}AddonsModal .form-check {
        padding: 0.75rem 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    #{{ $section }}AddonsModal .form-check:hover {
        background: #e9ecef;
    }

    #{{ $section }}AddonsModal .form-check-input:checked {
        background-color: #495057 !important;
        border-color: #495057 !important;
    }

    #{{ $section }}AddonsModal .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important;
    }

    #{{ $section }}AddonsModal .form-check-label {
        font-weight: 500;
        color: #495057;
        margin-left: 0.5rem;
    }

    #{{ $section }}AddonsModal .modal-content {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    }

    #{{ $section }}AddonsModal .modal-header {
        background: #ffffff;
        color: #212529;
        border-radius: 16px 16px 0 0 !important;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem;
    }

    #{{ $section }}AddonsModal .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    #{{ $section }}AddonsModal .btn-close {
        filter: brightness(0) invert(1) !important;
        opacity: 0.8;
    }

    #{{ $section }}AddonsModal .btn-close:hover {
        opacity: 1;
    }

    #{{ $section }}AddonsModal .modal-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    #{{ $section }}AddonsModal .btn-primary {
        background-color: #0044cc !important;
    }

    #{{ $section }}AddonsModal .quantity-control, 
    #{{ $section }}AddonsModal .nights-control {
        margin-top: 1rem;
    }

    #{{ $section }}AddonsModal .form-control:disabled {
        background-color: #e9ecef !important;
        opacity: 0.6;
    }

    @media (max-width: 768px) {
        #{{ $section }}AddonsModal .modal-dialog {
            margin: 1rem;
        }
        
        #{{ $section }}AddonsModal .modal-body {
            padding: 1rem;
        }
        
        #{{ $section }}AddonsModal .modal-footer {
            padding: 1rem;
        }
        
        #{{ $section }}AddonsModal .accordion-button {
            padding: 1rem;
            font-size: 0.9rem;
        }
        
        #{{ $section }}AddonsModal .accordion-body {
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
            <div class="selected-addons-display mb-3 d-none" id="{{ $section }}-selected-addons-display">
            </div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#{{ $section }}AddonsModal">
                    <i class="fa fa-plus me-2"></i> Select Add-ons
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="{{ $section }}AddonsModal" tabindex="-1" aria-labelledby="{{ $section }}AddonsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $section }}AddonsModalLabel">
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
                        <div class="accordion" id="{{ $section }}PerUnitAddonsAccordion">
                            @foreach ($perUnitAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ $section }}PerUnitHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#{{ $section }}PerUnitCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $section }}PerUnitCollapse{{ $addon->id }}">
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
                                        <div id="{{ $section }}PerUnitCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="{{ $section }}PerUnitHeading{{ $addon->id }}" 
                                             data-bs-parent="#{{ $section }}PerUnitAddonsAccordion">
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
                                                        <label for="{{ $section }}_addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>Quantity
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="{{ $section }}_addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="{{ $section }}_addon_quantity[{{ $addon->id }}]" 
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
                                                            id="{{ $section }}_addon_checkbox-{{ $addon->id }}"
                                                            name="{{ $section }}_addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="{{ $section }}_addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
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
                        <div class="accordion" id="{{ $section }}PerNightAddonsAccordion">
                            @foreach ($perNightAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ $section }}PerNightHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#{{ $section }}PerNightCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $section }}PerNightCollapse{{ $addon->id }}">
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
                                        <div id="{{ $section }}PerNightCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="{{ $section }}PerNightHeading{{ $addon->id }}" 
                                             data-bs-parent="#{{ $section }}PerNightAddonsAccordion">
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
                                                        <label for="{{ $section }}_addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>Quantity
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="{{ $section }}_addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="{{ $section }}_addon_quantity[{{ $addon->id }}]" 
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
                                                            id="{{ $section }}_addon_checkbox-{{ $addon->id }}"
                                                            name="{{ $section }}_addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="{{ $section }}_addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
                                                @endif

                                                <div class="nights-control">
                                                    <label for="{{ $section }}_addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                        <i class="fa fa-moon me-1"></i>How many nights
                                                        <small class="text-muted">(Select dates first)</small>
                                                    </label>
                                                    <input id="{{ $section }}_addon_nights-{{ $addon->id }}" 
                                                        type="number" 
                                                        class="form-control nights-input"
                                                        name="{{ $section }}_addon_nights[{{ $addon->id }}]" 
                                                        value="1" 
                                                        min="1" 
                                                        step="1" 
                                                        data-addon-id="{{ $addon->id }}"
                                                        placeholder="Enter number of nights"
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
                        <div class="accordion" id="{{ $section }}PerItemAddonsAccordion">
                            @foreach ($perItemAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ $section }}PerItemHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#{{ $section }}PerItemCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $section }}PerItemCollapse{{ $addon->id }}">
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
                                        <div id="{{ $section }}PerItemCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="{{ $section }}PerItemHeading{{ $addon->id }}" 
                                             data-bs-parent="#{{ $section }}PerItemAddonsAccordion">
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
                                                        <label for="{{ $section }}_addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>How many items
                                                            @if($addon->quantity)
                                                                <small class="text-muted">(Max: {{ $addon->quantity }} available)</small>
                                                            @endif
                                                        </label>
                                                        <input id="{{ $section }}_addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="{{ $section }}_addon_quantity[{{ $addon->id }}]" 
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
                                                            id="{{ $section }}_addon_checkbox-{{ $addon->id }}"
                                                            name="{{ $section }}_addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="{{ $section }}_addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this item
                                                        </label>
                                                    </div>
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
                        <div class="accordion" id="{{ $section }}FlatRateAddonsAccordion">
                            @foreach ($flatRateAddons as $index => $addon)
                                <div class="addon-card mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="{{ $section }}FlatRateHeading{{ $addon->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#{{ $section }}FlatRateCollapse{{ $addon->id }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="{{ $section }}FlatRateCollapse{{ $addon->id }}">
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
                                        <div id="{{ $section }}FlatRateCollapse{{ $addon->id }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="{{ $section }}FlatRateHeading{{ $addon->id }}" 
                                             data-bs-parent="#{{ $section }}FlatRateAddonsAccordion">
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
                                                        <label for="{{ $section }}_addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                            <i class="fa fa-sort-numeric-up me-1"></i>Quantity
                                                            @if($addon->capacity)
                                                                <small class="text-muted">(Max: {{ $addon->capacity }})</small>
                                                            @endif
                                                        </label>
                                                        <input id="{{ $section }}_addon_quantity-{{ $addon->id }}" 
                                                            type="number" 
                                                            class="form-control quantity-input addon-quantity"
                                                            name="{{ $section }}_addon_quantity[{{ $addon->id }}]" 
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
                                                            id="{{ $section }}_addon_checkbox-{{ $addon->id }}"
                                                            name="{{ $section }}_addon_checkbox[{{ $addon->id }}]" 
                                                            value="1"
                                                            data-addon-id="{{ $addon->id }}">
                                                        <label class="form-check-label" for="{{ $section }}_addon_checkbox-{{ $addon->id }}">
                                                            <i class="fa fa-check me-1"></i>Include this addon
                                                        </label>
                                                    </div>
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
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var addonsData = @json($filteredAddons->keyBy('id')->toArray());
        
        function calculateDaysDiff(startDate, endDate) {
            if (!startDate || !endDate) return 0;
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = end - start;
            return Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
        }
        
        function getSelectedDateRange(section = null) {
            let startDate, endDate;
            
            if (section) {
                if (section === 'shared') {
                    startDate = document.getElementById('date_from')?.value;
                    endDate = document.getElementById('date_to')?.value;
                } else if (section === 'whole') {
                    startDate = document.getElementById('whole_date_from')?.value;
                    endDate = document.getElementById('whole_date_to')?.value;
                }
            } else {
                const activeBookingType = document.querySelector('input[name="booking_type"]:checked');
                if (!activeBookingType) return { startDate: null, endDate: null, daysDiff: 0 };
                
                if (activeBookingType.value === 'shared') {
                    startDate = document.getElementById('date_from')?.value;
                    endDate = document.getElementById('date_to')?.value;
                } else if (activeBookingType.value === 'whole') {
                    startDate = document.getElementById('whole_date_from')?.value;
                    endDate = document.getElementById('whole_date_to')?.value;
                }
            }
            
            const daysDiff = calculateDaysDiff(startDate, endDate);
            return { startDate, endDate, daysDiff };
        }
        
        function hasSelectedAddons() {
            let hasSelected = false;
            
            document.querySelectorAll('#{{ $section }}AddonsModal .addon-checkbox:checked').forEach(() => {
                hasSelected = true;
            });
            
            document.querySelectorAll('#{{ $section }}AddonsModal .addon-quantity').forEach(input => {
                const quantity = parseInt(input.value) || 0;
                if (quantity > 0) {
                    hasSelected = true;
                }
            });
            
            return hasSelected;
        }
        
        function updateSelectedAddonsDisplay() {
            const selectedAddonsDisplay = document.getElementById('{{ $section }}-selected-addons-display');
            if (!selectedAddonsDisplay) return;
            
            let selectedAddons = [];
            
            document.querySelectorAll('#{{ $section }}AddonsModal .addon-checkbox:checked').forEach(checkbox => {
                const addonId = checkbox.getAttribute('data-addon-id');
                const addon = addonsData[addonId];
                if (addon) {
                    let displayText = addon.name;
                    let totalPrice = parseFloat(addon.base_price);
                    
                    if (addon.price_type === 'per_night') {
                        const nightsInput = document.querySelector(`#{{ $section }}AddonsModal input[name="{{ $section }}_addon_nights[${addonId}]"]`);
                        const nights = nightsInput ? parseInt(nightsInput.value) || 1 : 1;
                        totalPrice = totalPrice * nights;
                        displayText += ` (${nights} ${nights === 1 ? 'night' : 'nights'})`;
                    }
                    
                    selectedAddons.push({
                        name: displayText,
                        price: totalPrice
                    });
                }
            });
            
            document.querySelectorAll('#{{ $section }}AddonsModal .addon-quantity').forEach(input => {
                const quantity = parseInt(input.value) || 0;
                if (quantity > 0) {
                    const addonId = input.getAttribute('data-addon-id');
                    const addon = addonsData[addonId];
                    if (addon) {
                        let displayText = `${addon.name} (${quantity})`;
                        let totalPrice = parseFloat(addon.base_price);
                        
                        if (addon.price_type === 'per_unit') {
                            totalPrice = totalPrice * quantity;
                        } else if (addon.price_type === 'per_night') {
                            const nightsInput = document.querySelector(`#{{ $section }}AddonsModal input[name="{{ $section }}_addon_nights[${addonId}]"]`);
                            const nights = nightsInput ? parseInt(nightsInput.value) || 1 : 1;
                            totalPrice = totalPrice * quantity * nights;
                            displayText = `${addon.name} (${quantity} × ${nights} ${nights === 1 ? 'night' : 'nights'})`;
                        } else if (addon.price_type === 'per_item') {
                            totalPrice = totalPrice * quantity;
                            displayText = `${addon.name} (${quantity} ${quantity === 1 ? 'item' : 'items'})`;
                        } else {
                            totalPrice = totalPrice * quantity;
                        }
                        
                        selectedAddons.push({
                            name: displayText,
                            price: totalPrice
                        });
                    }
                }
            });
            
            const parentContainer = selectedAddonsDisplay.parentNode;
            
            if (selectedAddons.length === 0) {
                const existingRows = parentContainer.querySelectorAll('.row');
                existingRows.forEach(row => {
                    if (row.querySelector('.client-type-item')) {
                        row.remove();
                    }
                });
            } else {
                const existingRows = parentContainer.querySelectorAll('.row');
                existingRows.forEach(row => {
                    if (row.querySelector('.client-type-item')) {
                        row.remove();
                    }
                });
                
                const rowContainer = document.createElement('div');
                rowContainer.className = 'row';
                
                selectedAddons.forEach((addon, index) => {
                    const addonParts = addon.name.match(/^(.+?)(?:\s*\((.+)\))?$/);
                    const addonName = addonParts ? addonParts[1] : addon.name;
                    const quantityInfo = addonParts && addonParts[2] ? addonParts[2] : null;
                    
                    const colElement = document.createElement('div');
                    colElement.className = 'col-md-6';
                    
                    const addonElement = document.createElement('div');
                    addonElement.className = 'client-type-item';
                    addonElement.style.cssText = 'margin-bottom: 15px; padding: 12px; min-height: 80px; display: flex; flex-direction: column; justify-content: space-between;';
                    addonElement.innerHTML = `
                        <div style="font-weight: 600; margin-bottom: 4px; color: #333;">Name: ${addonName}</div>
                        <div style="font-weight: 500; margin-bottom: 4px; color: #0066cc;">Price: ₱${addon.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        <div style="font-size: 0.9em; color: #666;">${quantityInfo ? `Qty: ${quantityInfo}` : '&nbsp;'}</div>
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
            
            if (inputValue > availableQuantity && hasSelectedAddons()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantity Limit Exceeded',
                    text: `The quantity for "${addon.name}" cannot exceed ${availableQuantity} items. You entered ${inputValue}.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    input.value = '';
                    input.focus();
                    updateSelectedAddonsDisplay();
                });
                return false;
            }
            
            return true;
        }
        
        function validatePerNightAddon(nightsInput, addonId) {
            const addon = addonsData[addonId];
            if (!addon || addon.price_type !== 'per_night') return true;
            
            const { startDate, endDate, daysDiff } = getSelectedDateRange('{{ $section }}');
            const nightsValue = parseInt(nightsInput.value) || 0;
            
            if (nightsValue > daysDiff && hasSelectedAddons()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Number of Nights',
                    text: `The number of day/nights for "${addon.name}" (${nightsValue}) day/nights period cannot exceed the (${daysDiff} day/nights period). Please enter a value equal to or less than ${daysDiff}.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    nightsInput.value = daysDiff > 0 ? daysDiff : 1;
                    nightsInput.focus();
                    updateSelectedAddonsDisplay();
                });
                return false;
            }
            
            return true;
        }
        
        function updatePerNightControls() {
            const { startDate, endDate, daysDiff } = getSelectedDateRange('{{ $section }}');
            const hasValidDateRange = startDate && endDate && daysDiff > 0;
            
            Object.keys(addonsData).forEach(addonId => {
                const addon = addonsData[addonId];
                if (addon.price_type === 'per_night') {
                    const nightsInput = document.querySelector(`#{{ $section }}AddonsModal input[name="{{ $section }}_addon_nights[${addonId}]"]`);
                    const checkbox = document.querySelector(`#{{ $section }}AddonsModal input[name="{{ $section }}_addon_checkbox[${addonId}]"]`);
                    const quantityInput = document.querySelector(`#{{ $section }}AddonsModal input[name="{{ $section }}_addon_quantity[${addonId}]"]`);
                    
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
                }
            });
            updateSelectedAddonsDisplay();
        }
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('addon-checkbox') && e.target.closest('#{{ $section }}AddonsModal')) {
                updateSelectedAddonsDisplay();
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('addon-quantity') && e.target.closest('#{{ $section }}AddonsModal')) {
                const addonId = e.target.getAttribute('data-addon-id');
                if (addonId) {
                    if (validatePerItemQuantity(e.target, addonId)) {
                        updateSelectedAddonsDisplay();
                    }
                }
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('nights-input') && e.target.closest('#{{ $section }}AddonsModal')) {
                const addonId = e.target.getAttribute('data-addon-id');
                if (addonId) {
                    if (validatePerNightAddon(e.target, addonId)) {
                        updateSelectedAddonsDisplay();
                    }
                }
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.type === 'date') {
                if (('{{ $section }}' === 'shared' && (e.target.id === 'date_from' || e.target.id === 'date_to')) ||
                    ('{{ $section }}' === 'whole' && (e.target.id === 'whole_date_from' || e.target.id === 'whole_date_to'))) {
                    setTimeout(updatePerNightControls, 100);
                }
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.name === 'booking_type') {
                setTimeout(updatePerNightControls, 100);
            }
        });
        
        const confirmBtnId = '{{ $section }}' === 'shared' ? 'shared-confirm-dates' : 'whole-confirm-dates';
        const confirmBtn = document.getElementById(confirmBtnId);
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                setTimeout(updatePerNightControls, 500);
            });
        }
        
        document.addEventListener('show.bs.modal', function(e) {
            if (e.target.id === '{{ $section }}AddonsModal') {
                setTimeout(updatePerNightControls, 300);
            }
        });
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const target = mutation.target;
                    if ((target.id === 'date_from' || target.id === 'date_to' || 
                        target.id === 'whole_date_from' || target.id === 'whole_date_to') &&
                        (('{{ $section }}' === 'shared' && (target.id === 'date_from' || target.id === 'date_to')) ||
                        ('{{ $section }}' === 'whole' && (target.id === 'whole_date_from' || target.id === 'whole_date_to')))) {
                        setTimeout(updatePerNightControls, 100);
                    }
                }
            });
        });
        
        const dateInputs = ['date_from', 'date_to', 'whole_date_from', 'whole_date_to'];
        dateInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                observer.observe(input, { attributes: true, attributeFilter: ['value'] });
                input.addEventListener('input', function() {
                    if (('{{ $section }}' === 'shared' && (this.id === 'date_from' || this.id === 'date_to')) ||
                        ('{{ $section }}' === 'whole' && (this.id === 'whole_date_from' || this.id === 'whole_date_to'))) {
                        setTimeout(updatePerNightControls, 100);
                    }
                });
            }
        });
        
        updatePerNightControls();
        
        document.addEventListener('submit', function(e) {
            if (!hasSelectedAddons()) {
                return;
            }
            
            let hasErrors = false;
            
            document.querySelectorAll('#{{ $section }}AddonsModal .addon-quantity').forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                if (addonId && input.value && parseInt(input.value) > 0) {
                    if (!validatePerItemQuantity(input, addonId)) {
                        hasErrors = true;
                    }
                }
            });
            
            document.querySelectorAll('#{{ $section }}AddonsModal .nights-input').forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                if (addonId && input.value && parseInt(input.value) > 0) {
                    if (!validatePerNightAddon(input, addonId)) {
                        hasErrors = true;
                    }
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Errors',
                    text: 'Please fix the highlighted errors before submitting.',
                    confirmButtonColor: '#3085d6',
                });
            }
        });
    });
</script>
@endpush