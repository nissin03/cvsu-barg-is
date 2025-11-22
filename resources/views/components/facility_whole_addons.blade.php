    @php
        $addonsCollection = collect($facility->addons ?? []);
        $facilityId = (int) ($facility->id ?? 0);
        $facilityAttrId =
            (int) ($wholeAttr->id ?? (collect($facility->facility_attributes)->pluck('id')->first() ?? 0));

        $filteredAddons = $addonsCollection->filter(function ($addon) use ($facilityId, $facilityAttrId) {
            return (int) ($addon->facility_id ?? 0) === $facilityId &&
                (is_null($addon->facility_attribute_id) || (int) $addon->facility_attribute_id === $facilityAttrId) &&
                (bool) ($addon->is_available ?? false) === true &&
                (bool) ($addon->is_refundable ?? false) === false &&
                ($addon->show ?? 'both') === 'both';
        });

        $refundableAddons = $addonsCollection->filter(function ($addon) use ($facilityId, $facilityAttrId) {
            return (int) ($addon->facility_id ?? 0) === $facilityId &&
                (is_null($addon->facility_attribute_id) || (int) $addon->facility_attribute_id === $facilityAttrId) &&
                (bool) ($addon->is_available ?? false) === true &&
                (bool) ($addon->is_refundable ?? false) === true &&
                ($addon->price_type ?? null) === 'flat_rate' &&
                ($addon->show ?? 'both') === 'both';
        });

        $perUnitAddons = $filteredAddons->where('price_type', 'per_unit')->values();
        $perNightAddons = $filteredAddons->where('price_type', 'per_night')->values();
        $perItemAddons = $filteredAddons->where('price_type', 'per_item')->values();
        $flatRateAddons = $filteredAddons
            ->filter(
                fn($a) => ($a->price_type ?? null) === 'flat_rate' ||
                    !in_array($a->price_type ?? '', ['per_unit', 'per_night', 'per_item']),
            )
            ->values();

        $unavailableDates = [];
        $perItemQuantityData = [];
        foreach ($filteredAddons as $addon) {
            if (($addon->price_type ?? null) === 'per_item') {
                $reservationData = \DB::table('addons_reservations')
                    ->where('addon_id', $addon->id)
                    ->whereNotNull('date_from')
                    ->whereNotNull('date_to')
                    ->select('date_from', 'date_to', 'remaining_quantity')
                    ->get()
                    ->toArray();
                $perItemQuantityData[$addon->id] = $reservationData;
                $unavailableDates[$addon->id] = array_filter(
                    $reservationData,
                    fn($item) => (int) ($item->remaining_quantity ?? 0) === 0,
                );
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
            if (($addon->billing_cycle ?? null) === 'per_contract') {
                $row = \DB::table('addons_reservations')
                    ->where('addon_id', $addon->id)
                    ->whereNull('date_from')
                    ->whereNull('date_to')
                    ->orderByDesc('id')
                    ->first();
                $perContractRemainingCapacity[$addon->id] = $row ? (int) ($row->remaining_capacity ?? 0) : null;
            }
        }
        $hasAnyAddons =
            $perUnitAddons->count() ||
            $perNightAddons->count() ||
            $perItemAddons->count() ||
            $flatRateAddons->count() ||
            $refundableAddons->count();
    @endphp

    <style>
        .small-text-event .fc-event-title {
            font-size: 11px !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
        }

        .rounded-event {
            border-radius: 10px !important;
        }

        .centered-text .fc-event-title {
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
        }

        #addonsModal .addon-card {
            border: 1px solid #e9ecef !important;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #ffffff;
            margin-bottom: 0 !important;
        }

        #addonsModal .addon-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
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
    @if ($hasAnyAddons)
        <div class="mb-3">
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-plus-circle"></i>
                    <span><strong>Add-ons:</strong></span>
                </div>
                <div class="section-content">
                    <div class="selected-addons-display mb-3 d-none" id="selected-addons-display"></div>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal"
                            data-bs-target="#addonsModal">
                            <i class="fa fa-plus me-2"></i> Select Add-ons
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    @if ($perUnitAddons->count() > 0)
                        <div class="addon-section">
                            <h6 class="section-title"><i class="fa fa-calculator"></i> Per Unit Add-ons</h6>
                            <div class="accordion" id="perUnitAddonsAccordion">
                                @foreach ($perUnitAddons as $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="perUnitHeading{{ $addon->id }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perUnitCollapse{{ $addon->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="perUnitCollapse{{ $addon->id }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge per-unit">₱{{ number_format($addon->base_price, 2) }}
                                                                per unit</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perUnitCollapse{{ $addon->id }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perUnitHeading{{ $addon->id }}"
                                                data-bs-parent="#perUnitAddonsAccordion">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif
                                                    <input type="hidden" name="addon_values[{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden" name="addon_names[{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden" name="addon_types[{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden" name="addon_capacity[{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="addon_is_quantity_based[{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="addon_billing_cycle[{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">
                                                    <input type="hidden" id="addon_selected_dates-{{ $addon->id }}"
                                                        name="addon_selected_dates[{{ $addon->id }}]"
                                                        value="">
                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label for="addon_quantity-{{ $addon->id }}"
                                                                class="form-label quantity-label">How many
                                                                {{ $addon->name }}</label>
                                                            <input id="addon_quantity-{{ $addon->id }}"
                                                                type="number"
                                                                class="form-control quantity-input addon-quantity"
                                                                name="addon_quantity[{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->capacity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox" id="addon_checkbox-{{ $addon->id }}"
                                                                name="addon_checkbox[{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}">Include this
                                                                addon</label>
                                                        </div>
                                                    @endif
                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label">Select Date Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}"
                                                                name="addon_date_from[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}"
                                                                name="addon_date_to[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}"
                                                                name="addon_selected_dates[{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label for="addon_nights-{{ $addon->id }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input id="addon_nights-{{ $addon->id }}"
                                                                type="number" class="form-control nights-input"
                                                                name="addon_nights[{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color:#f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden" id="addon_nights-{{ $addon->id }}"
                                                            name="addon_nights[{{ $addon->id }}]" value="1">
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
                            <div class="accordion" id="perNightAddonsAccordion">
                                @foreach ($perNightAddons as $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="perNightHeading{{ $addon->id }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perNightCollapse{{ $addon->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="perNightCollapse{{ $addon->id }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge per-night">₱{{ number_format($addon->base_price, 2) }}
                                                                per night</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perNightCollapse{{ $addon->id }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perNightHeading{{ $addon->id }}"
                                                data-bs-parent="#perNightAddonsAccordion">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif
                                                    <input type="hidden" name="addon_values[{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden" name="addon_names[{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden" name="addon_types[{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden" name="addon_capacity[{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="addon_is_quantity_based[{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="addon_billing_cycle[{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">
                                                    @if (!$addon->is_based_on_quantity)
                                                        <input type="hidden" id="addon_quantity-{{ $addon->id }}"
                                                            name="addon_quantity[{{ $addon->id }}]" value="0"
                                                            data-addon-id="{{ $addon->id }}"
                                                            data-price-type="per_night">
                                                    @endif
                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label for="addon_quantity-{{ $addon->id }}"
                                                                class="form-label quantity-label">How many
                                                                {{ $addon->name }}</label>
                                                            <input id="addon_quantity-{{ $addon->id }}"
                                                                type="number"
                                                                class="form-control quantity-input addon-quantity per-night-quantity"
                                                                name="addon_quantity[{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                @if (!is_null($addon->quantity)) max="{{ $addon->quantity }}" @endif
                                                                step="1" data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input addon-checkbox per-night-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}"
                                                                name="addon_checkbox[{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}">Include this
                                                                addon</label>
                                                        </div>
                                                    @endif
                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label">Select Date Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}"
                                                                name="addon_date_from[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}"
                                                                name="addon_date_to[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}"
                                                                name="addon_selected_dates[{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label for="addon_nights-{{ $addon->id }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input id="addon_nights-{{ $addon->id }}"
                                                                type="number"
                                                                class="form-control nights-input per-night-nights"
                                                                name="addon_nights[{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-price-type="per_night"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color:#f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden" id="addon_nights-{{ $addon->id }}"
                                                            name="addon_nights[{{ $addon->id }}]" value="1">
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
                            <div class="accordion" id="perItemAddonsAccordion">
                                @foreach ($perItemAddons as $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="perItemHeading{{ $addon->id }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#perItemCollapse{{ $addon->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="perItemCollapse{{ $addon->id }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge per-item">₱{{ number_format($addon->base_price, 2) }}
                                                                per item</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="perItemCollapse{{ $addon->id }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="perItemHeading{{ $addon->id }}"
                                                data-bs-parent="#perItemAddonsAccordion">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif
                                                    <input type="hidden" name="addon_values[{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden" name="addon_names[{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden" name="addon_types[{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden" name="addon_capacity[{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="addon_is_quantity_based[{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="addon_billing_cycle[{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">
                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label for="addon_quantity-{{ $addon->id }}"
                                                                class="form-label quantity-label">How many
                                                                items</label>
                                                            <input id="addon_quantity-{{ $addon->id }}"
                                                                type="number"
                                                                class="form-control quantity-input addon-quantity per-item-quantity"
                                                                name="addon_quantity[{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->quantity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                data-billing-cycle="{{ $addon->billing_cycle }}"
                                                                placeholder="Enter number of items" disabled>
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}"
                                                                name="addon_checkbox[{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}"
                                                                data-billing-cycle="{{ $addon->billing_cycle }}"
                                                                disabled>
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}">Include this
                                                                item</label>
                                                        </div>
                                                    @endif
                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label">Select Date Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}"
                                                                name="addon_date_from[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}"
                                                                name="addon_date_to[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}"
                                                                name="addon_selected_dates[{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label for="addon_nights-{{ $addon->id }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input id="addon_nights-{{ $addon->id }}"
                                                                type="number" class="form-control nights-input"
                                                                name="addon_nights[{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color:#f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden" id="addon_nights-{{ $addon->id }}"
                                                            name="addon_nights[{{ $addon->id }}]" value="1">
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
                            <div class="accordion" id="flatRateAddonsAccordion">
                                @foreach ($flatRateAddons as $addon)
                                    <div class="addon-card mb-3">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="flatRateHeading{{ $addon->id }}">
                                                <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#flatRateCollapse{{ $addon->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="flatRateCollapse{{ $addon->id }}">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <div><strong>{{ $addon->name }}</strong></div>
                                                        <div><span
                                                                class="addon-price-badge flat-rate">₱{{ number_format($addon->base_price, 2) }}
                                                                flat rate</span></div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="flatRateCollapse{{ $addon->id }}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="flatRateHeading{{ $addon->id }}"
                                                data-bs-parent="#flatRateAddonsAccordion">
                                                <div class="accordion-body">
                                                    @if ($addon->description)
                                                        <p class="addon-description">{{ $addon->description }}</p>
                                                    @endif
                                                    <input type="hidden" name="addon_values[{{ $addon->id }}]"
                                                        value="{{ $addon->base_price }}">
                                                    <input type="hidden" name="addon_names[{{ $addon->id }}]"
                                                        value="{{ $addon->name }}">
                                                    <input type="hidden" name="addon_types[{{ $addon->id }}]"
                                                        value="{{ $addon->price_type }}">
                                                    <input type="hidden" name="addon_capacity[{{ $addon->id }}]"
                                                        value="{{ $addon->capacity ?? 0 }}">
                                                    <input type="hidden"
                                                        name="addon_is_quantity_based[{{ $addon->id }}]"
                                                        value="{{ $addon->is_based_on_quantity ? 1 : 0 }}">
                                                    <input type="hidden"
                                                        name="addon_billing_cycle[{{ $addon->id }}]"
                                                        value="{{ $addon->billing_cycle }}">
                                                    @if ($addon->is_based_on_quantity)
                                                        <div class="quantity-control">
                                                            <label for="addon_quantity-{{ $addon->id }}"
                                                                class="form-label quantity-label">Quantity</label>
                                                            <input id="addon_quantity-{{ $addon->id }}"
                                                                type="number"
                                                                class="form-control quantity-input addon-quantity"
                                                                name="addon_quantity[{{ $addon->id }}]"
                                                                value="0" min="0"
                                                                max="{{ $addon->capacity ?? 999 }}" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                placeholder="Enter quantity">
                                                        </div>
                                                    @else
                                                        <div class="form-check">
                                                            <input class="form-check-input addon-checkbox"
                                                                type="checkbox"
                                                                id="addon_checkbox-{{ $addon->id }}"
                                                                name="addon_checkbox[{{ $addon->id }}]"
                                                                value="1" data-addon-id="{{ $addon->id }}">
                                                            <label class="form-check-label"
                                                                for="addon_checkbox-{{ $addon->id }}">Include this
                                                                addon</label>
                                                        </div>
                                                    @endif
                                                    @if ($addon->billing_cycle === 'per_day')
                                                        <div class="calendar-control mt-3">
                                                            <label class="form-label">Select Date Range</label>
                                                            <div id="addon_calendar-{{ $addon->id }}"
                                                                class="addon-calendar"></div>
                                                            <input type="hidden"
                                                                id="addon_date_from-{{ $addon->id }}"
                                                                name="addon_date_from[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_date_to-{{ $addon->id }}"
                                                                name="addon_date_to[{{ $addon->id }}]"
                                                                value="">
                                                            <input type="hidden"
                                                                id="addon_selected_dates-{{ $addon->id }}"
                                                                name="addon_selected_dates[{{ $addon->id }}]"
                                                                value="">
                                                            <div id="selected_dates_display-{{ $addon->id }}"
                                                                class="mt-2 p-2 bg-light rounded">
                                                                <small><strong>Selected range:</strong> <span
                                                                        class="selected-dates-text">None</span></small><br>
                                                                <small><strong>Days count:</strong> <span
                                                                        class="days-count-text">0 days</span></small>
                                                            </div>
                                                        </div>
                                                        <div class="nights-control mt-3">
                                                            <label for="addon_nights-{{ $addon->id }}"
                                                                class="form-label nights-label">How many days</label>
                                                            <input id="addon_nights-{{ $addon->id }}"
                                                                type="number" class="form-control nights-input"
                                                                name="addon_nights[{{ $addon->id }}]"
                                                                value="0" min="0" step="1"
                                                                data-addon-id="{{ $addon->id }}"
                                                                placeholder="Number of days will auto-update" readonly
                                                                style="background-color:#f8f9fa;">
                                                        </div>
                                                    @else
                                                        <input type="hidden" id="addon_nights-{{ $addon->id }}"
                                                            name="addon_nights[{{ $addon->id }}]" value="1">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!$hasAnyAddons)
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

                @if ($refundableAddons->count() > 0)
                    @foreach ($refundableAddons as $refundableAddon)
                        <input type="hidden" name="refundable_addon_ids[]" value="{{ $refundableAddon->id }}">
                        <input type="hidden" name="refundable_addon_names[{{ $refundableAddon->id }}]"
                            value="{{ $refundableAddon->name }}">
                        <input type="hidden" name="refundable_addon_prices[{{ $refundableAddon->id }}]"
                            value="{{ $refundableAddon->base_price }}">
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <script>
        window.__HAS_ADDONS__ = {{ $hasAnyAddons ? 'true' : 'false' }};
    </script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!window.__HAS_ADDONS__) return;
                window.addonCalendars = {};
                window.addonSelectedDates = {};
                window.addonUnavailableDates = @json($unavailableDates);
                window.perItemQuantityData = @json($perItemQuantityData);
                window.perContractRemainingCapacity = @json($perContractRemainingCapacity);
                initializeAddonsScript();
                observeFacilityDates();
            });

            function observeFacilityDates() {
                const dateFromInputs = document.querySelectorAll('input[name="date_from"],#date_from');
                const dateToInputs = document.querySelectorAll('input[name="date_to"],#date_to');
                [...dateFromInputs, ...dateToInputs].forEach(input => {
                    if (input) {
                        input.addEventListener('change', () => {
                            setTimeout(() => {
                                initializeAllCalendars();
                                updateAddonControls();
                            }, 100)
                        });
                    }
                });
                const confirmBtn = document.getElementById('confirm-dates');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', () => {
                        setTimeout(() => {
                            initializeAllCalendars();
                            updateAddonControls();
                        }, 300)
                    });
                }
            }

            function getFacilityDates() {
                let facilityStart = '',
                    facilityEnd = '';
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
                return {
                    facilityStart,
                    facilityEnd
                };
            }

            function formatDateFromDisplay(dateString) {
                if (!dateString || dateString === 'Not selected') return '';
                try {
                    const d = new Date(dateString);
                    if (isNaN(d.getTime())) return '';
                    return d.toISOString().split('T')[0];
                } catch (e) {
                    return '';
                }
            }

            function waitForFullCalendar(cb, maxAttempts = 50) {
                let a = 0;
                const i = setInterval(() => {
                    a++;
                    if (typeof FullCalendar !== 'undefined' && typeof FullCalendar.Calendar !== 'undefined') {
                        clearInterval(i);
                        cb();
                    } else if (a >= maxAttempts) {
                        clearInterval(i);
                    }
                }, 100);
            }

            function initializeAllCalendars() {
                waitForFullCalendar(() => {
                    const allAddons = [...@json($perUnitAddons->pluck('id')->toArray()), ...@json($perNightAddons->pluck('id')->toArray()), ...
                        @json($perItemAddons->pluck('id')->toArray()), ...@json($flatRateAddons->pluck('id')->toArray())
                    ];
                    const unavailableDates = window.addonUnavailableDates || {};
                    const perItemQuantityData = window.perItemQuantityData || {};
                    const addonsData = @json($filteredAddons->keyBy('id')->toArray());
                    const {
                        facilityStart,
                        facilityEnd
                    } = getFacilityDates();
                    allAddons.forEach(addonId => {
                        const calendarEl = document.getElementById(`addon_calendar-${addonId}`);
                        if (!calendarEl) return;
                        if (!facilityStart || !facilityEnd) {
                            calendarEl.innerHTML =
                                '<div class="alert alert-warning">Please select facility dates first</div>';
                            return;
                        }
                        if (window.addonCalendars[addonId]) {
                            window.addonCalendars[addonId].destroy();
                        }
                        calendarEl.innerHTML = '';
                        const addon = addonsData[addonId];
                        const isPerItem = addon && addon.price_type === 'per_item';
                        let unavailableEvents = [],
                            quantityEvents = [];
                        const addonUnavailable = unavailableDates[addonId] || [];
                        addonUnavailable.forEach(dt => {
                            const unavailStart = new Date(dt.date_from);
                            const unavailEnd = new Date(dt.date_to);
                            const facilityStartDate = new Date(facilityStart);
                            const facilityEndDate = new Date(facilityEnd);
                            if (unavailStart <= facilityEndDate && unavailEnd >= facilityStartDate) {
                                const adjustedStart = unavailStart < facilityStartDate ?
                                    facilityStartDate : unavailStart;
                                const adjustedEnd = unavailEnd > facilityEndDate ? facilityEndDate :
                                    unavailEnd;
                                unavailableEvents.push({
                                    start: adjustedStart.toISOString().split('T')[0],
                                    end: new Date(adjustedEnd.getTime() + 86400000)
                                        .toISOString().split('T')[0],
                                    backgroundColor: '#dc3545',
                                    borderColor: '#dc3545',
                                    display: 'background'
                                });
                            }
                        });
                        if (isPerItem && addon.billing_cycle === 'per_day') {
                            const reservationData = perItemQuantityData[addonId] || [];
                            reservationData.forEach(r => {
                                const resStart = new Date(r.date_from);
                                const resEnd = new Date(r.date_to);
                                const facilityStartDate = new Date(facilityStart);
                                const facilityEndDate = new Date(facilityEnd);
                                if (resStart <= facilityEndDate && resEnd >= facilityStartDate) {
                                    if (r.remaining_quantity === 0) {
                                        getDatesBetween(
                                            resStart < facilityStartDate ? facilityStart : r
                                            .date_from,
                                            resEnd > facilityEndDate ? facilityEnd : r.date_to
                                        ).forEach(d => {
                                            quantityEvents.push({
                                                title: "unavailable",
                                                start: d,
                                                color: '#dc3545',
                                                textColor: '#ffffff',
                                                classNames: ['small-text-event',
                                                    'rounded-event', 'centered-text'
                                                ],
                                                display: 'auto'
                                            });
                                        });
                                    } else {
                                        getDatesBetween(
                                            resStart < facilityStartDate ? facilityStart : r
                                            .date_from,
                                            resEnd > facilityEndDate ? facilityEnd : r.date_to
                                        ).forEach(d => {
                                            quantityEvents.push({
                                                title: `${r.remaining_quantity} available`,
                                                start: d,
                                                color: '#28a745',
                                                textColor: '#ffffff',
                                                classNames: ['small-text-event',
                                                    'rounded-event', 'centered-text'
                                                ],
                                                display: 'auto'
                                            });
                                        });
                                    }
                                }
                            });
                            getDatesBetween(facilityStart, facilityEnd).forEach(d => {
                                const hasReservation = reservationData.some(res => {
                                    const resStart = new Date(res.date_from);
                                    const resEnd = new Date(res.date_to);
                                    const currentDate = new Date(d);
                                    return currentDate >= resStart && currentDate <= resEnd;
                                });
                                if (!hasReservation) {
                                    const defaultQty = addon.quantity || 0;
                                    if (defaultQty > 0) {
                                        quantityEvents.push({
                                            title: `${defaultQty} available`,
                                            start: d,
                                            color: '#28a745',
                                            textColor: '#ffffff',
                                            classNames: ['small-text-event', 'rounded-event',
                                                'centered-text'
                                            ],
                                            display: 'auto'
                                        });
                                    } else {
                                        quantityEvents.push({
                                            title: "unavailable",
                                            start: d,
                                            color: '#dc3545',
                                            textColor: '#ffffff',
                                            classNames: ['small-text-event', 'rounded-event',
                                                'centered-text'
                                            ],
                                            display: 'auto'
                                        });
                                    }
                                }
                            });
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
                        const selectedEvents = selectedState.selectedDates.filter(date => {
                            const dateObj = new Date(date);
                            const facilityStartObj = new Date(facilityStart);
                            const facilityEndObj = new Date(facilityEnd);
                            return dateObj >= facilityStartObj && dateObj <= facilityEndObj;
                        }).map(d => ({
                            start: d,
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
                            events: isPerItem ? [...quantityEvents, ...selectedEvents] : [...
                                unavailableEvents, ...quantityEvents, ...selectedEvents
                            ],
                            datesSet: function() {
                                setTimeout(() => {
                                    calendar.updateSize();
                                }, 50);
                            },
                            dateClick: function(info) {
                                const clickedDate = info.dateStr,
                                    clickedDateObj = new Date(clickedDate),
                                    facilityStartObj = new Date(facilityStart),
                                    facilityEndObj = new Date(facilityEnd);
                                if (clickedDateObj < facilityStartObj || clickedDateObj >
                                    facilityEndObj) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Date Outside Range',
                                        text: 'Please select dates within your facility booking period.',
                                        confirmButtonColor: '#3085d6'
                                    });
                                    return;
                                }
                                const isUnavailable = unavailableEvents.some(ev => {
                                    const eventStart = new Date(ev.start),
                                        eventEnd = new Date(ev.end);
                                    return clickedDateObj >= eventStart && clickedDateObj <
                                        eventEnd;
                                });
                                if (isUnavailable) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Date Not Available',
                                        text: 'The selected date is not available for this addon.',
                                        confirmButtonColor: '#3085d6'
                                    });
                                    return;
                                }
                                if (isPerItem && addon.billing_cycle === 'per_day') {
                                    const aq = getAvailableQuantityForDate(addonId, clickedDate,
                                        addon.quantity || 0);
                                    if (aq === 0) {
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
                                    const fd = new Date(selectedState.dateFrom),
                                        td = new Date(selectedState.dateTo);
                                    if (fd > td) {
                                        const tmp = selectedState.dateFrom;
                                        selectedState.dateFrom = selectedState.dateTo;
                                        selectedState.dateTo = tmp;
                                    }
                                    selectedState.selectedDates = getDatesBetween(selectedState
                                        .dateFrom, selectedState.dateTo);
                                    const ok = selectedState.selectedDates.every(d => {
                                        const dateObj = new Date(d);
                                        if (dateObj < facilityStartObj || dateObj >
                                            facilityEndObj) return false;
                                        if (unavailableEvents.some(ev => {
                                                const eventStart = new Date(ev.start),
                                                    eventEnd = new Date(ev.end);
                                                return dateObj >= eventStart &&
                                                    dateObj < eventEnd;
                                            })) return false;
                                        if (isPerItem && addon.billing_cycle ===
                                            'per_day') {
                                            return getAvailableQuantityForDate(addonId, d,
                                                addon.quantity || 0) > 0;
                                        }
                                        return true;
                                    });
                                    if (!ok) {
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
                                    updatePerItemInputs(addonId, selectedState.selectedDates
                                        .length > 0);
                                }
                                const upd = selectedState.selectedDates.map(d => ({
                                    start: d,
                                    backgroundColor: '#28a745',
                                    borderColor: '#28a745',
                                    display: 'background'
                                }));
                                calendar.removeAllEvents();
                                calendar.addEventSource(isPerItem ? [...quantityEvents, ...upd] : [
                                    ...unavailableEvents, ...quantityEvents, ...upd
                                ]);
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

            function getAvailableQuantityForDate(addonId, date, defQ) {
                const data = window.perItemQuantityData[addonId] || [];
                let avail = defQ;
                data.forEach(r => {
                    const rs = new Date(r.date_from),
                        re = new Date(r.date_to),
                        cd = new Date(date);
                    if (cd >= rs && cd <= re) {
                        if (r.remaining_quantity < avail) {
                            avail = r.remaining_quantity;
                        }
                    }
                });
                return avail;
            }

            function getDatesBetween(s, e) {
                const out = [];
                const st = new Date(s),
                    en = new Date(e),
                    cur = new Date(st);
                while (cur <= en) {
                    out.push(cur.toISOString().split('T')[0]);
                    cur.setDate(cur.getDate() + 1);
                }
                return out;
            }

            function updateSelectedDatesDisplay(addonId, state) {
                const disp = document.querySelector(`#selected_dates_display-${addonId} .selected-dates-text`);
                const days = document.querySelector(`#selected_dates_display-${addonId} .days-count-text`);
                const df = document.getElementById(`addon_date_from-${addonId}`);
                const dt = document.getElementById(`addon_date_to-${addonId}`);
                const sd = document.getElementById(`addon_selected_dates-${addonId}`);
                if (!disp || !days) return;
                const count = state.selectedDates.length;
                if (state.dateFrom && !state.dateTo) {
                    state.dateTo = state.dateFrom;
                }
                if (state.dateFrom && state.dateTo) {
                    disp.textContent = `${state.dateFrom} to ${state.dateTo}`;
                    days.textContent = `${count} days`;
                    if (df) df.value = state.dateFrom;
                    if (dt) dt.value = state.dateTo;
                    if (sd) sd.value = JSON.stringify(state.selectedDates);
                } else if (state.dateFrom) {
                    disp.textContent = `${state.dateFrom} (select end date)`;
                    days.textContent = '1 day';
                    if (df) df.value = state.dateFrom;
                    if (dt) dt.value = state.dateFrom;
                    if (sd) sd.value = JSON.stringify([state.dateFrom]);
                } else {
                    disp.textContent = 'None';
                    days.textContent = '0 days';
                    if (df) df.value = '';
                    if (dt) dt.value = '';
                    if (sd) sd.value = '';
                }
                updateNightsInput(addonId, count);
            }

            function updateNightsInput(addonId, count) {
                const input = document.querySelector(`input[name="addon_nights[${addonId}]"]`);
                if (input) input.value = count;
            }

            function updatePerItemInputs(addonId, hasDates) {
                const q = document.querySelector(`input[name="addon_quantity[${addonId}]"].per-item-quantity`);
                const c = document.querySelector(`input[name="addon_checkbox[${addonId}]"][data-billing-cycle="per_day"]`);
                if (q) {
                    q.disabled = !hasDates;
                    if (!hasDates) q.value = 0;
                }
                if (c) {
                    c.disabled = !hasDates;
                    if (!hasDates) c.checked = false;
                }
            }

            function lockPerContractControlsByCapacity(addonId) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon || addon.billing_cycle !== 'per_contract') return;

                if (addon.price_type === 'flat_rate') return;

                const rem = (window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] != null) ?
                    parseInt(window.perContractRemainingCapacity[addonId]) : null;

                const cb = document.querySelector(`input[name="addon_checkbox[${addonId}]"]`);
                const q = document.querySelector(`input[name="addon_quantity[${addonId}]"]`);
                const out = rem === 0;

                if (cb) {
                    cb.disabled = out || cb.disabled;
                    if (out) cb.checked = false;
                    cb.title = out ? 'No remaining capacity' : '';
                }
                if (q) {
                    q.disabled = out || q.disabled;
                    if (out) q.value = 0;
                    q.title = out ? 'No remaining capacity' : '';
                }
            }

            function lockPerItemPerContractByQuantity(addonId) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon) return;
                if (addon.price_type !== 'per_item' || addon.billing_cycle !== 'per_contract') return;
                const qty = parseInt(addon.quantity ?? 0);
                const cb = document.querySelector(`input[name="addon_checkbox[${addonId}]"]`);
                const q = document.querySelector(`input[name="addon_quantity[${addonId}]"]`);
                if (cb && !addon.is_based_on_quantity) {
                    if (qty > 0) {
                        cb.disabled = false;
                        cb.removeAttribute('disabled');
                        cb.title = '';
                    } else {
                        cb.disabled = true;
                        cb.checked = false;
                        cb.title = 'No items available';
                    }
                }
                if (q && addon.is_based_on_quantity) {
                    if (qty > 0) {
                        q.disabled = false;
                        q.removeAttribute('disabled');
                        q.max = qty;
                        if ((parseInt(q.value) || 0) > qty) {
                            q.value = qty;
                        }
                        q.title = '';
                    } else {
                        q.disabled = true;
                        q.value = 0;
                        q.title = 'No items available';
                    }
                }
            }

            function updateAddonControls() {
                const {
                    facilityStart,
                    facilityEnd
                } = getFacilityDates();
                const ok = facilityStart && facilityEnd;
                document.querySelectorAll('.addon-checkbox').forEach(cb => {
                    const pd = (cb.getAttribute('data-billing-cycle') === 'per_day');
                    if (pd) {
                        cb.disabled = !ok;
                        if (!ok) cb.checked = false;
                    }
                });
                document.querySelectorAll('.addon-quantity').forEach(i => {
                    const pd = (i.getAttribute('data-billing-cycle') === 'per_day');
                    if (pd) {
                        i.disabled = !ok;
                        if (!ok) i.value = 0;
                    }
                });
                document.querySelectorAll('.nights-input').forEach(i => {
                    const pn = (i.getAttribute('data-price-type') === 'per_night');
                    if (pn) {
                        i.disabled = !ok;
                        if (!ok) i.value = 1;
                    }
                });
                const data = @json($filteredAddons->keyBy('id')->toArray());
                Object.keys(data).forEach(id => {
                    lockPerContractControlsByCapacity(id);
                    lockPerItemPerContractByQuantity(id);
                });
            }

            function validatePerItemQuantity(input, addonId) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon || addon.price_type !== 'per_item') return true;
                const val = parseInt(input.value) || 0;
                if (addon.billing_cycle === 'per_contract') {
                    if (addon.quantity !== null && typeof addon.quantity !== 'undefined') {
                        const lim = parseInt(addon.quantity);
                        if (val > lim) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Quantity Limit Exceeded',
                                text: `Only ${lim} items available. You entered ${val}.`,
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                input.value = lim;
                                input.focus();
                            });
                            return false;
                        }
                    }
                    return true;
                }
                if (addon.billing_cycle !== 'per_day') return true;
                const st = window.addonSelectedDates[addonId];
                if (!st || st.selectedDates.length === 0) {
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
                const dataQ = window.perItemQuantityData[addonId] || [];
                let minAvail = addon.quantity || 0;
                st.selectedDates.forEach(d => {
                    let dateAvail = addon.quantity || 0;
                    dataQ.forEach(r => {
                        const rs = new Date(r.date_from),
                            re = new Date(r.date_to),
                            cd = new Date(d);
                        if (cd >= rs && cd <= re) {
                            if (r.remaining_quantity < dateAvail) {
                                dateAvail = r.remaining_quantity;
                            }
                        }
                    });
                    if (dateAvail < minAvail) {
                        minAvail = dateAvail;
                    }
                });
                if (val > minAvail) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Limit Exceeded',
                        text: `Only ${minAvail} items available for the selected dates. You entered ${val}.`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        input.value = minAvail;
                        input.focus();
                    });
                    return false;
                }
                return true;
            }

            function validatePerNightQuantity(input, addonId) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon || addon.price_type !== 'per_night') return true;
                if (addon.quantity === null || typeof addon.quantity === 'undefined') return true;
                const val = parseInt(input.value) || 0;
                const lim = parseInt(addon.quantity);
                if (val > lim) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Limit Exceeded',
                        text: `Only ${lim} available. You entered ${val}.`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        input.value = lim;
                        input.focus();
                    });
                    return false;
                }
                return true;
            }

            function validatePerUnitPerContractQuantity(input, addonId) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon) return true;
                if (addon.price_type !== 'per_unit') return true;
                if (addon.billing_cycle !== 'per_contract') return true;
                const val = parseInt(input.value) || 0;
                const hardCap = (addon.capacity != null) ? parseInt(addon.capacity) : null;
                const contractCap = (window.perContractRemainingCapacity && window.perContractRemainingCapacity[addonId] !=
                    null) ? parseInt(window.perContractRemainingCapacity[addonId]) : null;
                const limits = [hardCap, contractCap].filter(v => v != null);
                const lim = limits.length ? Math.min(...limits) : null;
                if (lim != null && val > lim) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Quantity Limit Exceeded',
                        text: `Only ${lim} units available for this add-on.`,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        input.value = Math.max(0, lim);
                        input.focus();
                    });
                    return false;
                }
                return true;
            }

            function calculatePerNightPrice(addonId, base, qty, nights) {
                const data = @json($filteredAddons->keyBy('id')->toArray());
                const addon = data[addonId];
                if (!addon || addon.price_type !== 'per_night') return 0;
                let total = base * nights;
                if (addon.is_based_on_quantity && qty > 0) {
                    total = total * qty;
                }
                return total;
            }

            function initializeAddonsScript() {
                let data;
                try {
                    data = @json($filteredAddons->keyBy('id')->toArray());
                    if (!data || Object.keys(data).length === 0) {
                        return;
                    }
                } catch (e) {
                    return;
                }
                let saved = {
                    checkboxes: {},
                    quantities: {},
                    nights: {},
                    dateFrom: {},
                    dateTo: {}
                };
                window.savedAddonsState = saved;

                function validateCheckboxRequired(id) {
                    const d = @json($filteredAddons->keyBy('id')->toArray());
                    const a = d[id];

                    if (a && a.price_type === 'flat_rate' && a.billing_cycle === 'per_contract') return true;

                    if (!a || a.billing_cycle !== 'per_contract') return true;

                    const cb = document.querySelector(`input[name="addon_checkbox[${id}]"]`);
                    const q = document.querySelector(`input[name="addon_quantity[${id}]"]`);
                    if (cb && !a.is_based_on_quantity) return cb.checked;
                    if (q && a.is_based_on_quantity) return (parseInt(q.value) > 0);
                    return true;
                }

                function saveState() {
                    const d = @json($filteredAddons->keyBy('id')->toArray());
                    document.querySelectorAll('.addon-checkbox').forEach(cb => {
                        const id = cb.getAttribute('data-addon-id');
                        const a = d[id];
                        if (!cb.disabled && validateCheckboxRequired(id)) {
                            saved.checkboxes[id] = cb.checked;
                        } else {
                            saved.checkboxes[id] = false;
                        }
                    });
                    document.querySelectorAll('.addon-quantity').forEach(i => {
                        const id = i.getAttribute('data-addon-id');
                        const a = d[id];
                        if (!i.disabled && validateCheckboxRequired(id)) {
                            saved.quantities[id] = parseInt(i.value) || 0;
                        } else {
                            saved.quantities[id] = 0;
                        }
                    });
                    document.querySelectorAll('.nights-input').forEach(i => {
                        const id = i.getAttribute('data-addon-id');
                        if (!i.disabled) {
                            saved.nights[id] = parseInt(i.value) || 1;
                        }
                    });
                    Object.keys(window.addonSelectedDates).forEach(id => {
                        const st = window.addonSelectedDates[id];
                        saved.dateFrom[id] = st.dateFrom;
                        saved.dateTo[id] = st.dateTo;
                    });
                }

                function renderSelection() {
                    const box = document.getElementById('selected-addons-display');
                    if (!box) return;
                    let rows = [];
                    for (let id in saved.checkboxes) {
                        if (saved.checkboxes[id]) {
                            const a = data[id];
                            if (a) {
                                let total = parseFloat(a.base_price),
                                    days = saved.nights[id] || 1,
                                    df = saved.dateFrom[id] || '',
                                    dt = saved.dateTo[id] || '';
                                if (a.price_type === 'per_night') {
                                    total = calculatePerNightPrice(id, a.base_price, 0, days);
                                } else if (a.billing_cycle === 'per_day' && (['per_unit', 'flat_rate', 'per_item'].includes(a
                                        .price_type))) {
                                    total = total * days;
                                }
                                rows.push({
                                    name: a.name,
                                    price: total,
                                    days,
                                    df,
                                    dt,
                                    id,
                                    pt: a.price_type,
                                    bc: a.billing_cycle
                                });
                            }
                        }
                    }
                    for (let id in saved.quantities) {
                        const q = saved.quantities[id];
                        if (q > 0) {
                            const a = data[id];
                            if (a) {
                                let total = parseFloat(a.base_price),
                                    days = saved.nights[id] || 1,
                                    df = saved.dateFrom[id] || '',
                                    dt = saved.dateTo[id] || '';
                                if (a.price_type === 'per_night') {
                                    total = calculatePerNightPrice(id, a.base_price, q, days);
                                } else if (a.billing_cycle === 'per_day') {
                                    total = total * q * days;
                                } else {
                                    total = total * q;
                                }
                                rows.push({
                                    name: a.name,
                                    price: total,
                                    quantity: q,
                                    days,
                                    df,
                                    dt,
                                    id,
                                    pt: a.price_type,
                                    bc: a.billing_cycle
                                });
                            }
                        }
                    }
                    const parent = box.parentNode;
                    const existing = parent.querySelectorAll('.row');
                    existing.forEach(r => {
                        if (r.querySelector('.client-type')) {
                            r.remove();
                        }
                    });
                    if (rows.length > 0) {
                        const row = document.createElement('div');
                        row.className = 'row';
                        rows.forEach(a => {
                            const col = document.createElement('div');
                            col.className = 'col-md-6';
                            const el = document.createElement('div');
                            el.className = 'client-type';
                            el.style.cssText =
                                'margin-bottom:15px;padding:15px;min-height:100px;display:flex;flex-direction:column;justify-content:space-between;border-radius:8px;background:#f3f4f6;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.08)';
                            let qty = a.quantity ? `Qty: ${a.quantity}` : '&nbsp;';
                            let days = a.days && a.bc === 'per_day' && a.pt !== 'per_item' ? `Days: ${a.days}` : '';
                            let range = '';
                            if (a.df && a.dt && a.bc === 'per_day') {
                                range =
                                    `<div style="font-size:0.9em;color:#059669;margin-bottom:2px;">Dates: ${a.df} to ${a.dt}</div>`;
                            } else if (a.df && a.bc === 'per_day') {
                                range =
                                    `<div style="font-size:0.9em;color:#059669;margin-bottom:2px;">Date: ${a.df}</div>`;
                            }
                            el.innerHTML =
                                `<div style="font-weight:600;margin-bottom:4px;color:#111827;">Name: ${a.name}</div><div style="font-weight:500;margin-bottom:4px;color:#0066cc;">Price: ₱${a.price.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</div>${range}<div style="font-size:0.9em;color:#374151;margin-bottom:2px;">${qty}</div>${days?`<div style="font-size:0.9em;color:#374151;">${days}</div>`:''}`;
                            col.appendChild(el);
                            row.appendChild(col);
                        });
                        parent.insertBefore(row, box);
                    }
                }
                const modal = document.getElementById('addonsModal');
                if (modal) {
                    modal.addEventListener('show.bs.modal', () => {
                        setTimeout(() => {
                            initializeAllCalendars();
                            updateAddonControls();
                        }, 100);
                    });
                    modal.addEventListener('shown.bs.modal', () => {
                        setTimeout(() => {
                            Object.values(window.addonCalendars).forEach(c => {
                                if (c) {
                                    c.updateSize();
                                }
                            });
                        }, 100);
                    });
                    const saveBtn = document.querySelector('#addonsModal .btn-primary[data-bs-dismiss="modal"]');
                    if (saveBtn) {
                        saveBtn.addEventListener('click', () => {
                            saveState();
                            renderSelection();
                        });
                    }
                }
                const acc = document.querySelectorAll('.accordion-collapse');
                acc.forEach(a => {
                    a.addEventListener('shown.bs.collapse', function() {
                        const id = this.id.replace('perUnitCollapse', '').replace('perNightCollapse', '')
                            .replace('perItemCollapse', '').replace('flatRateCollapse', '');
                        const c = window.addonCalendars[id];
                        if (c) {
                            setTimeout(() => {
                                c.updateSize();
                            }, 150);
                        }
                    });
                });
                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('addon-quantity')) {
                        const id = e.target.getAttribute('data-addon-id');
                        if (id) {
                            setTimeout(() => validatePerItemQuantity(e.target, id), 100);
                            setTimeout(() => validatePerUnitPerContractQuantity(e.target, id), 110);
                        }
                    }
                    if (e.target.classList.contains('per-night-quantity')) {
                        const id = e.target.getAttribute('data-addon-id');
                        if (id) {
                            setTimeout(() => validatePerNightQuantity(e.target, id), 50);
                        }
                    }
                });
                updateAddonControls();
            }
        </script>
    @endpush
