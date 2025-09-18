@php
    $filteredAddons = $facility->addons->filter(function($addon) use ($facility) {
        return $addon->facility_id === $facility->id &&
               ($addon->facility_attribute_id === null || $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
               $addon->is_available == true &&
               $addon->is_refundable == false &&
               $addon->show === 'both';
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
            <div class="selected-addons-display mb-3" id="{{ $section }}-selected-addons-display">
                <div class="no-addons-message text-muted">
                    No add-ons selected
                </div>
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
                <div class="accordion" id="{{ $section }}AddonsAccordion">
                    @foreach ($filteredAddons as $index => $addon)
                        <div class="addon-card mb-3">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="{{ $section }}Heading{{ $addon->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#{{ $section }}Collapse{{ $addon->id }}" 
                                            aria-expanded="false" 
                                            aria-controls="{{ $section }}Collapse{{ $addon->id }}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <strong>{{ $addon->name }}</strong>
                                            </div>
                                            <div>
                                                <span class="addon-price-badge">
                                                    @if($addon->price_type === 'per_unit')
                                                        ₱{{ number_format($addon->base_price, 2) }} per unit
                                                    @elseif($addon->price_type === 'per_night')
                                                        ₱{{ number_format($addon->base_price, 2) }} per night
                                                    @else
                                                        ₱{{ number_format($addon->base_price, 2) }} fixed rate
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="{{ $section }}Collapse{{ $addon->id }}" 
                                     class="accordion-collapse collapse" 
                                     aria-labelledby="{{ $section }}Heading{{ $addon->id }}" 
                                     data-bs-parent="#{{ $section }}AddonsAccordion">
                                    <div class="accordion-body">
                                        @if($addon->description)
                                            <p class="addon-description">{{ $addon->description }}</p>
                                        @endif
                                        
                                        <input type="hidden" name="addon_values[{{ $addon->id }}]" value="{{ $addon->base_price }}">
                                        <input type="hidden" name="addon_names[{{ $addon->id }}]" value="{{ $addon->name }}">
                                        <input type="hidden" name="addon_types[{{ $addon->id }}]" value="{{ $addon->price_type }}">
                                        
                                        @if($addon->is_based_on_quantity)
                                            <div class="quantity-control">
                                                <label for="{{ $section }}_addon_quantity-{{ $addon->id }}" class="form-label quantity-label">
                                                    <i class="fa fa-sort-numeric-up me-1"></i>Quantity
                                                </label>
                                                <input id="{{ $section }}_addon_quantity-{{ $addon->id }}" 
                                                    type="number" 
                                                    class="form-control quantity-input addon-quantity"
                                                    name="{{ $section }}_addon_quantity[{{ $addon->id }}]" 
                                                    value="0" 
                                                    min="0" 
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

                                        @if($addon->price_type === 'per_night')
                                            <div class="nights-control">
                                                <label for="{{ $section }}_addon_nights-{{ $addon->id }}" class="form-label nights-label">
                                                    <i class="fa fa-moon me-1"></i>How many nights
                                                </label>
                                                <input id="{{ $section }}_addon_nights-{{ $addon->id }}" 
                                                    type="number" 
                                                    class="form-control nights-input"
                                                    name="{{ $section }}_addon_nights[{{ $addon->id }}]" 
                                                    value="1" 
                                                    min="1" 
                                                    step="1" 
                                                    data-addon-id="{{ $addon->id }}"
                                                    placeholder="Enter number of nights">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save Changes</button>
                </div>
        </div>
    </div>
</div>
@endif