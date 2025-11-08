@php
    $section = $section ?? 'both';
    $ns = $section === 'whole' ? 'whole_addons' : 'shared_addons';

    $filteredAddons = $facility->addons->filter(function ($addon) use ($facility, $section) {
        $facilityMatch =
            $addon->facility_id === $facility->id &&
            ($addon->facility_attribute_id === null ||
                $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
            $addon->is_available == true &&
            $addon->is_refundable == false;
        if ($section === 'shared') {
            return $facilityMatch && ($addon->show === 'both' || $addon->show === 'shared');
        } elseif ($section === 'whole') {
            return $facilityMatch && ($addon->show === 'both' || $addon->show === 'whole');
        } else {
            return $facilityMatch && $addon->show === 'both';
        }
    });
    $refundableAddons = $facility->addons->filter(function ($addon) use ($facility, $section) {
        $facilityMatch =
            $addon->facility_id === $facility->id &&
            ($addon->facility_attribute_id === null ||
                $addon->facility_attribute_id == $facility->facility_attributes->pluck('id')->first()) &&
            $addon->is_available == true &&
            $addon->is_refundable == true &&
            $addon->price_type === 'flat_rate';
        if ($section === 'shared') {
            return $facilityMatch && ($addon->show === 'both' || $addon->show === 'shared');
        } elseif ($section === 'whole') {
            return $facilityMatch && ($addon->show === 'both' || $addon->show === 'whole');
        } else {
            return $facilityMatch && $addon->show === 'both';
        }
    });

    $perUnitAddons = $filteredAddons->filter(function ($addon) {
        return $addon->price_type === 'per_unit';
    });
    $perNightAddons = $filteredAddons->filter(function ($addon) {
        return $addon->price_type === 'per_night';
    });
    $perItemAddons = $filteredAddons->filter(function ($addon) {
        return $addon->price_type === 'per_item';
    });
    $flatRateAddons = $filteredAddons->filter(function ($addon) {
        return $addon->price_type === 'flat_rate' ||
            !in_array($addon->price_type, ['per_unit', 'per_night', 'per_item']);
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
            $unavailableDates[$addon->id] = array_filter($reservationData, function ($item) {
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
    #addonsModal-{{ $section }} .addon-card {
        border: 1px solid #e9ecef !important;
        border-radius: 12px !important;
        transition: all .3s ease;
        overflow: hidden;
        background: #ffffff;
        margin-bottom: 0 !important
    }

    #addonsModal-{{ $section }} .addon-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08)
    }

    #addonsModal-{{ $section }} .section-divider {
        border-bottom: 2px solid #e9ecef;
        margin: 2rem 0 1.5rem 0;
        padding-bottom: .5rem
    }

    #addonsModal-{{ $section }} .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem
    }

    #addonsModal-{{ $section }} .section-title i {
        color: #0044cc
    }

    #addonsModal-{{ $section }} .no-addons-message {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem
    }

    #addonsModal-{{ $section }} .accordion-button {
        background: #ffffff !important;
        border: 1px solid #e9ecef !important;
        padding: 1.25rem 1.5rem;
        font-weight: 500;
        color: #495057 !important;
        box-shadow: none !important;
        border-radius: 12px 12px 0 0 !important
    }

    #addonsModal-{{ $section }} .accordion-button:not(.collapsed) {
        background: #f8f9fa !important;
        color: #212529 !important;
        box-shadow: none !important;
        border-color: #dee2e6 !important
    }

    #addonsModal-{{ $section }} .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05) !important;
        border-color: #ced4da !important
    }

    #addonsModal-{{ $section }} .accordion-button::after {
        filter: brightness(.6)
    }

    #addonsModal-{{ $section }} .accordion-button:not(.collapsed)::after {
        filter: brightness(.4)
    }

    #addonsModal-{{ $section }} .accordion-body {
        padding: 1.5rem;
        background: #fafbfc;
        border-radius: 0 0 12px 12px
    }

    #addonsModal-{{ $section }} .addon-price-badge {
        background: #117a11 !important;
        color: #fff;
        font-size: .875rem;
        font-weight: 600;
        padding: .5rem 1rem;
        border-radius: 20px;
        border: none
    }

    #addonsModal-{{ $section }} .addon-price-badge.per-unit {
        background: #0044cc !important
    }

    #addonsModal-{{ $section }} .addon-price-badge.per-night {
        background: #6610f2 !important
    }

    #addonsModal-{{ $section }} .addon-price-badge.per-item {
        background: #20c997 !important
    }

    #addonsModal-{{ $section }} .addon-price-badge.flat-rate {
        background: #fd7e14 !important
    }

    #addonsModal-{{ $section }} .addon-description {
        font-size: .9rem;
        line-height: 1.5;
        color: #6c757d;
        margin-bottom: 1.5rem;
        font-style: italic
    }

    #addonsModal-{{ $section }} .form-control {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: .75rem 1rem;
        font-size: .9rem;
        transition: all .3s ease
    }

    #addonsModal-{{ $section }} .form-control:focus {
        border-color: #495057 !important;
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important
    }

    #addonsModal-{{ $section }} .quantity-label,
    #addonsModal-{{ $section }} .nights-label,
    #addonsModal-{{ $section }} .date-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: .5rem;
        font-size: .9rem
    }

    #addonsModal-{{ $section }} .form-check {
        padding: .75rem 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all .3s ease
    }

    #addonsModal-{{ $section }} .form-check:hover {
        background: #e9ecef
    }

    #addonsModal-{{ $section }} .form-check-input:checked {
        background-color: #495057 !important;
        border-color: #495057 !important
    }

    #addonsModal-{{ $section }} .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1) !important
    }

    #addonsModal-{{ $section }} .form-check-label {
        font-weight: 500;
        color: #495057;
        margin-left: .5rem
    }

    #addonsModal-{{ $section }} .modal-content {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important
    }

    #addonsModal-{{ $section }} .modal-header {
        background: #ffffff;
        color: #212529;
        border-radius: 16px 16px 0 0 !important;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem
    }

    #addonsModal-{{ $section }} .modal-title {
        font-weight: 600;
        font-size: 1.25rem
    }

    #addonsModal-{{ $section }} .btn-close {
        filter: brightness(0) invert(1) !important;
        opacity: .8
    }

    #addonsModal-{{ $section }} .btn-close:hover {
        opacity: 1
    }

    #addonsModal-{{ $section }} .modal-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto
    }

    #addonsModal-{{ $section }} .btn-primary {
        background-color: #0044cc !important
    }

    #addonsModal-{{ $section }} .quantity-control,
    #addonsModal-{{ $section }} .nights-control,
    #addonsModal-{{ $section }} .date-control {
        margin-top: 1rem
    }

    #addonsModal-{{ $section }} .form-control:disabled {
        background-color: #e9ecef !important;
        opacity: .6
    }

    #addonsModal-{{ $section }} .date-range-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem
    }

    #addonsModal-{{ $section }} .date-validation-message {
        font-size: .8rem;
        margin-top: .5rem;
        padding: .5rem;
        border-radius: 4px
    }

    #addonsModal-{{ $section }} .date-validation-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb
    }

    #addonsModal-{{ $section }} .date-validation-message.success {
        background-color: #d1edff;
        color: #0c5460;
        border: 1px solid #b8daff
    }

    #addonsModal-{{ $section }} .flatpickr-input {
        background-color: #fff !important
    }

    #addonsModal-{{ $section }} .flatpickr-disabled {
        background-color: #e9ecef !important;
        color: #6c757d !important
    }

    @media (max-width:768px) {
        #addonsModal-{{ $section }} .modal-dialog {
            margin: 1rem
        }

        #addonsModal-{{ $section }} .modal-body {
            padding: 1rem
        }

        #addonsModal-{{ $section }} .modal-footer {
            padding: 1rem
        }

        #addonsModal-{{ $section }} .accordion-button {
            padding: 1rem;
            font-size: .9rem
        }

        #addonsModal-{{ $section }} .accordion-body {
            padding: 1rem
        }

        #addonsModal-{{ $section }} .date-range-container {
            grid-template-columns: 1fr
        }
    }
</style>

@if ($filteredAddons->count() > 0)
    <div class="mb-3">
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-plus-circle"></i>
                <span><strong>Add-ons:</strong></span>
            </div>
            <div class="section-content">
                <div class="selected-addons-display mb-3 d-none" id="selected-addons-display-{{ $section }}"></div>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal"
                        data-bs-target="#addonsModal-{{ $section }}">
                        <i class="fa fa-plus me-2"></i> Select Add-ons
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addonsModal-{{ $section }}" tabindex="-1"
        aria-labelledby="addonsModalLabel-{{ $section }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addonsModalLabel-{{ $section }}">
                        <i class="fa fa-plus-circle me-2"></i>Select Add-ons
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    @if ($perUnitAddons->count() > 0)
                        <div class="addon-section">
                            <h6 class="section-title"><i class="fa fa-calculator"></i> Per Unit Add-ons</h6>
                            <div class="accordion" id="perUnitAddonsAccordion-{{ $section }}">
                                @foreach ($perUnitAddons as $index => $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header"
                                                id="perUnitHeading{{ $addon->id }}-{{ $section }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perUnitCollapse{{ $addon->id }}-{{ $section }}"
                                                    aria-expanded="false"
                                                    aria-controls="perUnitCollapse{{ $addon->id }}-{{ $section }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge per-unit">₱{{ number_format($addon->base_price, 2) }}
                                                                per unit</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perUnitCollapse{{ $addon->id }}-{{ $section }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perUnitHeading{{ $addon->id }}-{{ $section }}"
                                                data-bs-parent="#perUnitAddonsAccordion-{{ $section }}">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif

                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_values][{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_names][{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_types][{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_capacity][{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_is_quantity_based][{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_billing_cycle][{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">

                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label
                                                                for="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label quantity-label">How many
                                                                {{ $addon->name }}</label>
                                                            <input
                                                                id="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                type="number" class="form-control addon-quantity"
                                                                name="{{ $ns }}[addon_quantity][{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->capacity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_checkbox][{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}-{{ $section }}">Include
                                                                this addon</label>
                                                        </div>
                                                    @endif

                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label"><i
                                                                    class="fa fa-calendar me-1"></i>Select Date
                                                                Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}-{{ $section }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_from][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_to][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_selected_dates][{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}-{{ $section }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label
                                                                for="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input
                                                                id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                type="number" class="form-control nights-input"
                                                                name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color: #f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden"
                                                            id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                            name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                            value="1">
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

                    @if ($perNightAddons->count() > 0)
                        <div class="addon-section">
                            <h6 class="section-title"><i class="fa fa-moon"></i> Per Night / Per Day Add-ons</h6>
                            <div class="accordion" id="perNightAddonsAccordion-{{ $section }}">
                                @foreach ($perNightAddons as $index => $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header"
                                                id="perNightHeading{{ $addon->id }}-{{ $section }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perNightCollapse{{ $addon->id }}-{{ $section }}"
                                                    aria-expanded="false"
                                                    aria-controls="perNightCollapse{{ $addon->id }}-{{ $section }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge per-night">₱{{ number_format($addon->base_price, 2) }}
                                                                per night</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perNightCollapse{{ $addon->id }}-{{ $section }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perNightHeading{{ $addon->id }}-{{ $section }}"
                                                data-bs-parent="#perNightAddonsAccordion-{{ $section }}">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif

                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_values][{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_names][{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_types][{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_capacity][{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_is_quantity_based][{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_billing_cycle][{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">

                                                    @if (!$addon->is_based_on_quantity)
                                                        <input type="hidden"
                                                            id="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                            name="{{ $ns }}[addon_quantity][{{ $addon->id }}]"
                                                            value="0" data-addon-id="{{ $addon->id }}"
                                                            data-price-type="per_night"
                                                            data-section="{{ $section }}">
                                                    @endif

                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label
                                                                for="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label quantity-label">How many
                                                                {{ $addon->name }}</label>
                                                            <input
                                                                id="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                type="number"
                                                                class="form-control addon-quantity per-night-quantity"
                                                                name="{{ $ns }}[addon_quantity][{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                @if (!is_null($addon->quantity)) max="{{ $addon->quantity }}" @endif
                                                                step="1" data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night"
                                                                data-section="{{ $section }}"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input addon-checkbox per-night-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_checkbox][{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night"
                                                                data-section="{{ $section }}">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}-{{ $section }}">Include
                                                                this addon</label>
                                                        </div>
                                                    @endif

                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label"><i
                                                                    class="fa fa-calendar me-1"></i>Select Date
                                                                Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}-{{ $section }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_from][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_to][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_selected_dates][{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}-{{ $section }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label
                                                                for="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input
                                                                id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                type="number"
                                                                class="form-control nights-input per-night-nights"
                                                                name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night"
                                                                data-section="{{ $section }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color: #f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden"
                                                            id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                            name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                            value="1">
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

                    @if ($perItemAddons->count() > 0)
                        <div class="addon-section">
                            <h6 class="section-title"><i class="fa fa-cubes"></i> Per Item Add-ons</h6>
                            <div class="accordion" id="perItemAddonsAccordion-{{ $section }}">
                                @foreach ($perItemAddons as $index => $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header"
                                                id="perItemHeading{{ $addon->id }}-{{ $section }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perItemCollapse{{ $addon->id }}-{{ $section }}"
                                                    aria-expanded="false"
                                                    aria-controls="perItemCollapse{{ $addon->id }}-{{ $section }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong>
                                                            @if ($addon->quantity)
                                                                <small class="text-muted">({{ $addon->quantity }}
                                                                    available)</small>
                                                            @endif
                                                        </div>
                                                        <div><span
                                                                class="addon-price-badge per-item">₱{{ number_format($addon->base_price, 2) }}
                                                                per item</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perItemCollapse{{ $addon->id }}-{{ $section }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perItemHeading{{ $addon->id }}-{{ $section }}"
                                                data-bs-parent="#perItemAddonsAccordion-{{ $section }}">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif

                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_values][{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_names][{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_types][{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_capacity][{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_is_quantity_based][{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_billing_cycle][{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">

                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label
                                                                for="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label quantity-label">How many
                                                                items</label>
                                                            <input
                                                                id="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                type="number"
                                                                class="form-control addon-quantity per-item-quantity"
                                                                name="{{ $ns }}[addon_quantity][{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->quantity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-billing-cycle="{{ $addon->billing_cycle }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Enter number of items" disabled>
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_checkbox][{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-billing-cycle="{{ $addon->billing_cycle }}"
                                                                data-section="{{ $section }}" disabled>
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}-{{ $section }}">Include
                                                                this item</label>
                                                        </div>
                                                    @endif

                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label"><i
                                                                    class="fa fa-calendar me-1"></i>Select Date
                                                                Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}-{{ $section }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_from][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_to][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_selected_dates][{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}-{{ $section }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label
                                                                for="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input
                                                                id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                type="number" class="form-control nights-input"
                                                                name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color: #f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden"
                                                            id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                            name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                            value="1">
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

                    @if ($flatRateAddons->count() > 0)
                        <div class="addon-section">
                            <h6 class="section-title"><i class="fa fa-tag"></i> Fixed Price Add-ons</h6>
                            <div class="accordion" id="flatRateAddonsAccordion-{{ $section }}">
                                @foreach ($flatRateAddons as $index => $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header"
                                                id="flatRateHeading{{ $addon->id }}-{{ $section }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#flatRateCollapse{{ $addon->id }}-{{ $section }}"
                                                    aria-expanded="false"
                                                    aria-controls="flatRateCollapse{{ $addon->id }}-{{ $section }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge flat-rate">₱{{ number_format($addon->base_price, 2) }}
                                                                flat rate</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="flatRateCollapse{{ $addon->id }}-{{ $section }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="flatRateHeading{{ $addon->id }}-{{ $section }}"
                                                data-bs-parent="#flatRateAddonsAccordion-{{ $section }}">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif

                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_values][{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_names][{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_types][{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_capacity][{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_is_quantity_based][{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="{{ $ns }}[addon_billing_cycle][{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">

                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label
                                                                for="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label quantity-label">Quantity</label>
                                                            <input
                                                                id="addon_quantity-{{ $addon->id }}-{{ $section }}"
                                                                type="number" class="form-control addon-quantity"
                                                                name="{{ $ns }}[addon_quantity][{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->capacity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_checkbox][{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}-{{ $section }}">Include
                                                                this addon</label>
                                                        </div>
                                                    @endif

                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label"><i
                                                                    class="fa fa-calendar me-1"></i>Select Date
                                                                Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}-{{ $section }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_from][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_date_to][{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}-{{ $section }}"
                                                                name="{{ $ns }}[addon_selected_dates][{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}-{{ $section }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label
                                                                for="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input
                                                                id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                                type="number" class="form-control nights-input"
                                                                name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-section="{{ $section }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color: #f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden"
                                                            id="addon_nights-{{ $addon->id }}-{{ $section }}"
                                                            name="{{ $ns }}[addon_nights][{{ $addon->id }}]"
                                                            value="1">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (
                        $perUnitAddons->count() == 0 &&
                            $perNightAddons->count() == 0 &&
                            $perItemAddons->count() == 0 &&
                            $flatRateAddons->count() == 0)
                        <div class="no-addons-message">
                            <i class="fa fa-info-circle me-2"></i>
                            No add-ons are currently available for this facility.
                        </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                        onclick="saveAddonsChanges('{{ $section }}')">Save Changes</button>
                </div>
            </div>

            @if ($refundableAddons->count() > 0)
                @foreach ($refundableAddons as $refundableAddon)
                    <input type="hidden" name="{{ $ns }}[refundable_addon_ids][]"
                        value="{{ $refundableAddon->id }}">
                    <input type="hidden"
                        name="{{ $ns }}[refundable_addon_names][{{ $refundableAddon->id }}]"
                        value="{{ $refundableAddon->name }}">
                    <input type="hidden"
                        name="{{ $ns }}[refundable_addon_prices][{{ $refundableAddon->id }}]"
                        value="{{ $refundableAddon->base_price }}">
                @endforeach
            @endif

        </div>
    </div>
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addonCalendars = window.addonCalendars || {};
            window.addonSelectedDates = window.addonSelectedDates || {};
            window.addonUnavailableDates = @json($unavailableDates);
            window.perItemQuantityData = @json($perItemQuantityData);
            window.perContractRemainingCapacity = @json($perContractRemainingCapacity);
            initializeAddonsScript('{{ $section }}');
            observeFacilityDates('{{ $section }}');
            observeBookingTypeChanges();
        });

        function sectionGroup(section) {
            return section === 'whole' ? 'whole_addons' : 'shared_addons';
        }

        function nsName(base, section, addonId) {
            return `${sectionGroup(section)}[${base}][${addonId}]`;
        }

        function gi(base, section, addonId) {
            return document.querySelector(`input[name="${nsName(base, section, addonId)}"]`);
        }

        function observeBookingTypeChanges() {
            const bookingTypeRadios = document.querySelectorAll('input[name="booking_type"]');
            bookingTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const selectedSection = this.value;
                    resetAllAddons(selectedSection);
                });
            });
        }

        function resetAllAddons(selectedSection) {
            const sections = ['shared', 'whole'];
            sections.forEach(section => {
                if (section !== selectedSection) {
                    resetSectionAddons(section);
                }
            });
        }

        function resetSectionAddons(section) {
            document.querySelectorAll(`.addon-checkbox[data-section="${section}"]`).forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll(`.addon-quantity[data-section="${section}"]`).forEach(input => {
                input.value = 0;
            });
            document.querySelectorAll(`.nights-input[data-section="${section}"]`).forEach(input => {
                input.value = 0;
            });
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            Object.keys(addonsData).forEach(addonId => {
                const key = `${addonId}-${section}`;
                if (window.addonSelectedDates[key]) {
                    window.addonSelectedDates[key] = {
                        dateFrom: '',
                        dateTo: '',
                        selectedDates: []
                    };
                }
                const calendar = window.addonCalendars[key];
                if (calendar) {
                    calendar.removeAllEvents();
                    const {
                        facilityStart,
                        facilityEnd
                    } = getFacilityDates(section);
                    const unavailableDates = window.addonUnavailableDates || {};
                    const perItemQuantityData = window.perItemQuantityData || {};
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
                                const datesInRange = getDatesBetween(reservation.date_from, reservation
                                    .date_to);
                                datesInRange.forEach(date => {
                                    quantityEvents.push({
                                        title: `${reservation.remaining_quantity} available`,
                                        start: date,
                                        color: '#ffc107'
                                    });
                                });
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
                    calendar.addEventSource([...unavailableEvents, ...quantityEvents]);
                }
                updateSelectedDatesDisplay(addonId, window.addonSelectedDates[key] || {
                    dateFrom: '',
                    dateTo: '',
                    selectedDates: []
                }, section);
                updatePerItemInputs(addonId, false, section);
            });
            if (window.savedAddonsState && window.savedAddonsState[section]) {
                window.savedAddonsState[section] = {
                    checkboxes: {},
                    quantities: {},
                    nights: {},
                    dateFrom: {},
                    dateTo: {}
                };
            }
            const selectedAddonsDisplay = document.getElementById(`selected-addons-display-${section}`);
            if (selectedAddonsDisplay) {
                selectedAddonsDisplay.classList.add('d-none');
                const parentContainer = selectedAddonsDisplay.parentNode;
                const existingRows = parentContainer.querySelectorAll('.row');
                existingRows.forEach(row => {
                    if (row.querySelector('.client-type')) {
                        row.remove();
                    }
                });
            }
        }

        function observeFacilityDates(section) {
            const dateFromInputs = document.querySelectorAll('input[name="date_from"], #date_from, #whole_date_from');
            const dateToInputs = document.querySelectorAll('input[name="date_to"], #date_to, #whole_date_to');
            [...dateFromInputs, ...dateToInputs].forEach(input => {
                if (input) {
                    input.addEventListener('change', function() {
                        resetSectionAddons(section);
                        setTimeout(() => {
                            initializeAllCalendars(section);
                            updateAddonControls(section);
                            if (typeof updateTotalPrice === 'function') updateTotalPrice();
                        }, 100);
                    });
                }
            });
            const confirmBtns = document.querySelectorAll('#confirm-dates, #whole-confirm-dates');
            confirmBtns.forEach(confirmBtn => {
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        resetSectionAddons(section);
                        setTimeout(() => {
                            initializeAllCalendars(section);
                            updateAddonControls(section);
                            if (typeof updateTotalPrice === 'function') updateTotalPrice();
                        }, 300);
                    });
                }
            });
        }

        function getFacilityDates(section) {
            let facilityStart = '';
            let facilityEnd = '';
            if (section === 'shared') {
                const hiddenDateFrom = document.querySelector('input[name="date_from"]');
                const hiddenDateTo = document.querySelector('input[name="date_to"]');
                if (hiddenDateFrom && hiddenDateFrom.value) facilityStart = hiddenDateFrom.value;
                if (hiddenDateTo && hiddenDateTo.value) facilityEnd = hiddenDateTo.value;
                if (!facilityStart || !facilityEnd) {
                    const startDateDisplay = document.getElementById('shared-start-date-display');
                    const endDateDisplay = document.getElementById('shared-end-date-display');
                    if (startDateDisplay && startDateDisplay.textContent && endDateDisplay && endDateDisplay.textContent) {
                        facilityStart = formatDateFromDisplay(startDateDisplay.textcontent || startDateDisplay.textContent);
                        facilityEnd = formatDateFromDisplay(endDateDisplay.textcontent || endDateDisplay.textContent);
                    }
                }
            } else if (section === 'whole') {
                const hiddenDateFrom = document.querySelector('input[name="whole_date_from"]');
                const hiddenDateTo = document.querySelector('input[name="whole_date_to"]');
                if (hiddenDateFrom && hiddenDateFrom.value) facilityStart = hiddenDateFrom.value;
                if (hiddenDateTo && hiddenDateTo.value) facilityEnd = hiddenDateTo.value;
                if (!facilityStart || !facilityEnd) {
                    const startDateDisplay = document.getElementById('start-date-display');
                    const endDateDisplay = document.getElementById('end-date-display');
                    if (startDateDisplay && startDateDisplay.textContent && endDateDisplay && endDateDisplay.textContent) {
                        facilityStart = formatDateFromDisplay(startDateDisplay.textcontent || startDateDisplay.textContent);
                        facilityEnd = formatDateFromDisplay(endDateDisplay.textcontent || endDateDisplay.textContent);
                    }
                }
            }
            return {
                facilityStart,
                facilityEnd
            };
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

        function initializeAllCalendars(section) {
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
                const {
                    facilityStart,
                    facilityEnd
                } = getFacilityDates(section);
                allAddons.forEach(addonId => {
                    const calendarEl = document.getElementById(`addon_calendar-${addonId}-${section}`);
                    if (!calendarEl) return;
                    if (!facilityStart || !facilityEnd) {
                        calendarEl.innerHTML =
                            '<div class="alert alert-warning">Please select facility dates first</div>';
                        return;
                    }
                    if (window.addonCalendars[`${addonId}-${section}`]) {
                        window.addonCalendars[`${addonId}-${section}`].destroy();
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
                                const datesInRange = getDatesBetween(reservation.date_from,
                                    reservation.date_to);
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
                    if (!window.addonSelectedDates[`${addonId}-${section}`]) {
                        window.addonSelectedDates[`${addonId}-${section}`] = {
                            dateFrom: '',
                            dateTo: '',
                            selectedDates: []
                        };
                    }
                    let selectedState = window.addonSelectedDates[`${addonId}-${section}`];
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
                            end: new Date(new Date(facilityEnd).getTime() + 86400000).toISOString()
                                .split('T')[0]
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
                                const availableQuantity = getAvailableQuantityForDate(addonId,
                                    clickedDate, addon.quantity || 0);
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
                                selectedState.selectedDates = getDatesBetween(selectedState
                                    .dateFrom, selectedState.dateTo);
                                const allDatesAvailable = selectedState.selectedDates.every(
                                    date => {
                                        if (unavailableEvents.some(event => {
                                                const eventStart = new Date(event
                                                    .start);
                                                const eventEnd = new Date(event.end);
                                                const currentDate = new Date(date);
                                                return currentDate >= eventStart &&
                                                    currentDate <= eventEnd;
                                            })) return false;
                                        if (isPerItem && addon.billing_cycle ===
                                            'per_day') {
                                            const availableQuantity =
                                                getAvailableQuantityForDate(addonId, date,
                                                    addon.quantity || 0);
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
                            window.addonSelectedDates[`${addonId}-${section}`] = selectedState;
                            updateSelectedDatesDisplay(addonId, selectedState, section);
                            if (isPerItem && addon.billing_cycle === 'per_day') {
                                updatePerItemInputs(addonId, selectedState.selectedDates
                                    .length > 0, section);
                            }
                            const updatedSelectedEvents = selectedState.selectedDates.map(
                                date => ({
                                    start: date,
                                    backgroundColor: '#28a745',
                                    borderColor: '#28a745',
                                    display: 'background'
                                }));
                            calendar.removeAllEvents();
                            calendar.addEventSource([...unavailableEvents, ...quantityEvents,
                                ...updatedSelectedEvents
                            ]);
                            updateNightsInput(addonId, selectedState.selectedDates.length,
                                section);
                        }
                    });
                    calendar.render();
                    window.addonCalendars[`${addonId}-${section}`] = calendar;
                    setTimeout(() => {
                        calendar.updateSize();
                    }, 300);
                    updateSelectedDatesDisplay(addonId, selectedState, section);
                    updateNightsInput(addonId, selectedState.selectedDates.length, section);
                    if (isPerItem && addon.billing_cycle === 'per_day') {
                        updatePerItemInputs(addonId, selectedState.selectedDates.length > 0, section);
                    }
                });
            });
        }

        function updatePerItemInputs(addonId, hasSelectedDates, section) {
            const quantityInput = gi('addon_quantity', section, addonId);
            const checkboxInput = gi('addon_checkbox', section, addonId);
            if (quantityInput) {
                if (quantityInput.classList.contains('per-item-quantity')) {
                    quantityInput.disabled = !hasSelectedDates;
                    if (!hasSelectedDates) quantityInput.value = 0;
                }
            }
            if (checkboxInput) {
                if (checkboxInput.getAttribute('data-billing-cycle') === 'per_day') {
                    checkboxInput.disabled = !hasSelectedDates;
                    if (!hasSelectedDates) checkboxInput.checked = false;
                }
            }
        }

        function getAvailableQuantityForDate(addonId, isoDate, defaultDailyCap) {
            const rows = window.perItemQuantityData[addonId] || [];
            const date = new Date(isoDate);
            let cap = defaultDailyCap;
            rows.forEach(r => {
                if (!r.date_from || !r.date_to) return;
                const from = new Date(r.date_from);
                const to = new Date(r.date_to);
                if (date >= from && date <= to) {
                    if (typeof r.remaining_quantity === 'number') cap = Math.min(cap, r.remaining_quantity);
                }
            });
            return cap;
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

        function updateSelectedDatesDisplay(addonId, selectedState, section) {
            const dateFromInput = document.getElementById(`addon_date_from-${addonId}-${section}`);
            const dateToInput = document.getElementById(`addon_date_to-${addonId}-${section}`);
            const selectedDatesInput = document.getElementById(`addon_selected_dates-${addonId}-${section}`);
            if (selectedState.dateFrom && !selectedState.dateTo) selectedState.dateTo = selectedState.dateFrom;
            let dates = [];
            if (selectedState.dateFrom && selectedState.dateTo) dates = getDatesBetween(selectedState.dateFrom,
                selectedState.dateTo);
            if (dateFromInput) dateFromInput.value = selectedState.dateFrom || '';
            if (dateToInput) dateToInput.value = selectedState.dateTo || (selectedState.dateFrom || '');
            if (selectedDatesInput) selectedDatesInput.value = dates.length ? JSON.stringify(dates) : '';
            const displayEl = document.querySelector(`#selected_dates_display-${addonId}-${section} .selected-dates-text`);
            const daysCountEl = document.querySelector(`#selected_dates_display-${addonId}-${section} .days-count-text`);
            if (displayEl && daysCountEl) {
                if (selectedState.dateFrom && selectedState.dateTo) {
                    displayEl.textContent = `${selectedState.dateFrom} to ${selectedState.dateTo}`;
                    daysCountEl.textContent = `${dates.length} days`;
                } else if (selectedState.dateFrom) {
                    displayEl.textContent = `${selectedState.dateFrom} (select end date)`;
                    daysCountEl.textContent = '1 day';
                } else {
                    displayEl.textContent = 'None';
                    daysCountEl.textContent = '0 days';
                }
            }
            const nights = dates.length ? dates.length : (selectedState.dateFrom ? 1 : 0);
            updateNightsInput(addonId, nights, section);
        }

        function updateNightsInput(addonId, daysCount, section) {
            const nightsInput = gi('addon_nights', section, addonId);
            if (nightsInput) nightsInput.value = daysCount;
        }

        function saveAddonsChanges(section) {
            saveCurrentTempState(section);
            updateSelectedAddonsDisplay(section);
        }

        function saveCurrentTempState(section) {
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            if (!window.savedAddonsState) window.savedAddonsState = {};
            if (!window.savedAddonsState[section]) {
                window.savedAddonsState[section] = {
                    checkboxes: {},
                    quantities: {},
                    nights: {},
                    dateFrom: {},
                    dateTo: {}
                };
            }
            const savedState = window.savedAddonsState[section];
            document.querySelectorAll(`.addon-checkbox[data-section="${section}"]`).forEach(checkbox => {
                const addonId = checkbox.getAttribute('data-addon-id');
                const addon = addonsData[addonId];
                if (!checkbox.disabled && validateCheckboxRequired(addonId, addon, section)) savedState.checkboxes[
                    addonId] = checkbox.checked;
                else savedState.checkboxes[addonId] = false;
            });
            document.querySelectorAll(`.addon-quantity[data-section="${section}"]`).forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                const addon = addonsData[addonId];
                if (!input.disabled && validateCheckboxRequired(addonId, addon, section)) savedState.quantities[
                    addonId] = parseInt(input.value) || 0;
                else savedState.quantities[addonId] = 0;
            });
            document.querySelectorAll(`.nights-input[data-section="${section}"]`).forEach(input => {
                const addonId = input.getAttribute('data-addon-id');
                if (!input.disabled) savedState.nights[addonId] = parseInt(input.value) || 1;
            });
            Object.keys(window.addonSelectedDates).forEach(key => {
                if (key.endsWith(`-${section}`)) {
                    const addonId = key.replace(`-${section}`, '');
                    const selectedState = window.addonSelectedDates[key];
                    savedState.dateFrom[addonId] = selectedState.dateFrom;
                    savedState.dateTo[addonId] = selectedState.dateTo;
                }
            });
        }

        function validateCheckboxRequired(addonId, addon, section) {
            if (!addon || addon.billing_cycle !== 'per_contract') return true;
            const checkbox = gi('addon_checkbox', section, addonId);
            const quantityInput = gi('addon_quantity', section, addonId);
            if (checkbox && !addon.is_based_on_quantity) return checkbox.checked;
            if (quantityInput && addon.is_based_on_quantity) return parseInt(quantityInput.value) > 0;
            return true;
        }

        function updateSelectedAddonsDisplay(section) {
            const selectedAddonsDisplay = document.getElementById(`selected-addons-display-${section}`);
            if (!selectedAddonsDisplay) return;
            if (!window.savedAddonsState || !window.savedAddonsState[section]) return;
            const savedState = window.savedAddonsState[section];
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            let selectedAddons = [];
            for (let addonId in savedState.checkboxes) {
                if (savedState.checkboxes[addonId]) {
                    const addon = addonsData[addonId];
                    if (addon) {
                        let totalPrice = parseFloat(addon.base_price);
                        let days = savedState.nights[addonId] || 1;
                        let dateFrom = savedState.dateFrom[addonId] || '';
                        let dateTo = savedState.dateTo[addonId] || '';
                        if (addon.price_type === 'per_night') totalPrice = calculatePerNightPrice(addonId, addon.base_price,
                            0, days);
                        else if (addon.billing_cycle === 'per_day' && (addon.price_type === 'per_unit' || addon
                                .price_type === 'flat_rate' || addon.price_type === 'per_item')) totalPrice = totalPrice *
                            days;
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
            for (let addonId in savedState.quantities) {
                const quantity = savedState.quantities[addonId];
                if (quantity > 0) {
                    const addon = addonsData[addonId];
                    if (addon) {
                        let totalPrice = parseFloat(addon.base_price);
                        let days = savedState.nights[addonId] || 1;
                        let dateFrom = savedState.dateFrom[addonId] || '';
                        let dateTo = savedState.dateTo[addonId] || '';
                        if (addon.price_type === 'per_night') totalPrice = calculatePerNightPrice(addonId, addon.base_price,
                            quantity, days);
                        else if (addon.billing_cycle === 'per_day') totalPrice = totalPrice * quantity * days;
                        else totalPrice = totalPrice * quantity;
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
                if (row.querySelector('.client-type')) row.remove();
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
                    let daysHtml = addon.days && addon.billing_cycle === 'per_day' && addon.price_type !==
                        'per_item' ? `Days: ${addon.days}` : '';
                    let dateRangeHtml = '';
                    if (addon.dateFrom && addon.dateTo && addon.billing_cycle === 'per_day') {
                        dateRangeHtml =
                            `<div style="font-size: 0.9em; color: #059669; margin-bottom: 2px;">Dates: ${addon.dateFrom} to ${addon.dateTo}</div>`;
                    } else if (addon.dateFrom && addon.billing_cycle === 'per_day') {
                        dateRangeHtml =
                            `<div style="font-size: 0.9em; color: #059669; margin-bottom: 2px;">Date: ${addon.dateFrom}</div>`;
                    }
                    addonElement.innerHTML = `
                <div style="font-weight: 600; margin-bottom: 4px; color: #111827;">Name: ${addon.name}</div>
                <div style="font-weight: 500; margin-bottom: 4px; color: #0066cc;">Price: ₱${addon.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                ${dateRangeHtml}
                <div style="font-size: 0.9em; color: #374151; margin-bottom: 2px;">${quantityHtml}</div>
                ${daysHtml ? `<div style="font-size: 0.9em; color: #374151;">${daysHtml}</div>` : ''}
            `;
                    colElement.appendChild(addonElement);
                    rowContainer.appendChild(colElement);
                });
                parentContainer.insertBefore(rowContainer, selectedAddonsDisplay);
                selectedAddonsDisplay.classList.remove('d-none');
            } else {
                selectedAddonsDisplay.classList.add('d-none');
            }
        }

        function initializeAddonsScript(section) {
            const modalElement = document.getElementById(`addonsModal-${section}`);
            if (modalElement) {
                modalElement.addEventListener('show.bs.modal', function() {
                    setTimeout(() => {
                        initializeAllCalendars(section);
                        updateAddonControls(section);
                    }, 100);
                });
                modalElement.addEventListener('shown.bs.modal', function() {
                    setTimeout(() => {
                        Object.keys(window.addonCalendars).forEach(key => {
                            if (key.endsWith(`-${section}`) && window.addonCalendars[key]) window
                                .addonCalendars[key].updateSize();
                        });
                    }, 100);
                });
            }
            const accordions = document.querySelectorAll(`.accordion-collapse`);
            accordions.forEach(accordion => {
                if (accordion.id.includes(`-${section}`)) {
                    accordion.addEventListener('shown.bs.collapse', function() {
                        const addonId = this.id.replace(`perUnitCollapse`, '').replace(`perNightCollapse`,
                            '').replace(`perItemCollapse`, '').replace(`flatRateCollapse`, '').replace(
                            `-${section}`, '');
                        const calendar = window.addonCalendars[`${addonId}-${section}`];
                        if (calendar) setTimeout(() => {
                            calendar.updateSize();
                        }, 150);
                    });
                }
            });
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('addon-quantity')) {
                    const addonId = e.target.getAttribute('data-addon-id');
                    const section = e.target.getAttribute('data-section');
                    if (addonId && section) {
                        setTimeout(() => validatePerItemQuantity(e.target, addonId, section), 100);
                        setTimeout(() => validatePerUnitPerContractQuantity(e.target, addonId, section), 110);
                    }
                }
                if (e.target.classList.contains('per-night-quantity')) {
                    const addonId = e.target.getAttribute('data-addon-id');
                    const section = e.target.getAttribute('data-section');
                    if (addonId && section) setTimeout(() => validatePerNightQuantity(e.target, addonId, section),
                        50);
                }
            });
            updateAddonControls(section);
        }

        function validatePerItemQuantity(input, addonId, section) {
            const addonData = @json($filteredAddons->keyBy('id')->toArray());
            const addon = addonData[addonId];
            if (!addon || addon.price_type !== 'per_item') return true;
            const entered = parseInt(input.value) || 0;
            if (addon.billing_cycle === 'per_contract' && !!addon.is_based_on_days) {
                const cap = parseInt(addon.quantity || 0);
                if (entered > cap) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Limit Exceeded',
                        text: `Maximum allowed is ${cap} for this add-on.`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        input.value = Math.max(0, cap);
                        input.focus();
                    });
                    return false;
                }
                return true;
            }
            if (addon.billing_cycle === 'per_contract' && !addon.is_based_on_days) {
                const cap = parseInt(addon.quantity || 0);
                if (cap > 0 && entered > cap) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Limit Exceeded',
                        text: `Only ${cap} items available for this add-on.`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        input.value = Math.max(0, cap);
                        input.focus();
                    });
                    return false;
                }
                return true;
            }
            if (addon.billing_cycle !== 'per_day') return true;
            const state = window.addonSelectedDates[`${addonId}-${section}`];
            if (!state || state.selectedDates.length === 0) {
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
            const defaultDailyCap = parseInt(addon.quantity || 0);
            const minCapAcrossDays = getMinRemainingAcrossSelectedDates(addonId, state.selectedDates, defaultDailyCap);
            if (entered > minCapAcrossDays) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantity Limit Exceeded',
                    text: `Maximum allowed across your selected dates is ${minCapAcrossDays}.`,
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    input.value = Math.max(0, minCapAcrossDays);
                    input.focus();
                });
                return false;
            }
            return true;
        }

        function getMinRemainingAcrossSelectedDates(addonId, isoDates, defaultDailyCap) {
            let minCap = Number.isFinite(defaultDailyCap) ? defaultDailyCap : 0;
            isoDates.forEach(d => {
                const dayCap = getAvailableQuantityForDate(addonId, d, defaultDailyCap);
                minCap = Math.min(minCap, dayCap);
            });
            return minCap;
        }

        function validatePerNightQuantity(input, addonId, section) {
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

        function validatePerUnitPerContractQuantity(input, addonId, section) {
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            const addon = addonsData[addonId];
            if (!addon) return true;
            if (addon.price_type !== 'per_unit') return true;
            if (addon.billing_cycle !== 'per_contract') return true;
            const inputValue = parseInt(input.value) || 0;
            const hardCap = (addon.capacity != null) ? parseInt(addon.capacity) : null;
            const contractCap = (window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] !=
                null) ? parseInt(window.perContractRemainingCapacity[addonId]) : null;
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
            if (addon.is_based_on_quantity && quantity > 0) total = total * quantity;
            return total;
        }

        function updateAddonControls(section) {
            const {
                facilityStart,
                facilityEnd
            } = getFacilityDates(section);
            const hasValidDateRange = facilityStart && facilityEnd;
            document.querySelectorAll(`.addon-checkbox[data-section="${section}"]`).forEach(checkbox => {
                const isPerDay = (checkbox.getAttribute('data-billing-cycle') === 'per_day');
                if (isPerDay) {
                    checkbox.disabled = !hasValidDateRange;
                    if (!hasValidDateRange) checkbox.checked = false;
                }
            });
            document.querySelectorAll(`.addon-quantity[data-section="${section}"]`).forEach(input => {
                const isPerDay = (input.getAttribute('data-billing-cycle') === 'per_day');
                if (isPerDay) {
                    input.disabled = !hasValidDateRange;
                    if (!hasValidDateRange) input.value = 0;
                }
            });
            document.querySelectorAll(`.nights-input[data-section="${section}"]`).forEach(input => {
                const isPerNight = (input.getAttribute('data-price-type') === 'per_night');
                if (isPerNight) {
                    input.disabled = !hasValidDateRange;
                    if (!hasValidDateRange) input.value = 1;
                }
            });
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            Object.keys(addonsData).forEach(id => {
                lockPerContractControlsByCapacity(id, section);
                lockPerItemPerContractByQuantity(id, section);
            });
        }

        function lockPerContractControlsByCapacity(addonId, section) {
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            const addon = addonsData[addonId];
            if (!addon || addon.billing_cycle !== 'per_contract') return;

            if (addon.price_type === 'flat_rate') return;

            const rem = window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] != null ?
                parseInt(window.perContractRemainingCapacity[addonId]) :
                null;

            const checkbox = gi('addon_checkbox', section, addonId);
            const qtyInput = gi('addon_quantity', section, addonId);
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


        function lockPerItemPerContractByQuantity(addonId, section) {
            const addonsData = @json($filteredAddons->keyBy('id')->toArray());
            const addon = addonsData[addonId];
            if (!addon) return;
            if (addon.price_type !== 'per_item' || addon.billing_cycle !== 'per_contract') return;
            const qtyAvailable = parseInt(addon.quantity ?? 0);
            const checkbox = gi('addon_checkbox', section, addonId);
            const qtyInput = gi('addon_quantity', section, addonId);
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
                    if ((parseInt(qtyInput.value) || 0) > qtyAvailable) qtyInput.value = qtyAvailable;
                    qtyInput.title = '';
                } else {
                    qtyInput.disabled = true;
                    qtyInput.value = 0;
                    qtyInput.title = 'No items available';
                }
            }
        }
    </script>
@endpush
