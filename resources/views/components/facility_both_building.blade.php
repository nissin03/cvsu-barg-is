@php
    $hasAvailableRooms = false;
    $filteredAttributes = $facility->facilityAttributes->filter(function ($attribute) {
        if (is_null($attribute->sex_restriction)) {
            return true;
        }
        return auth()->check() && $attribute->sex_restriction === auth()->user()->sex;
    });
    $availableRoom = $filteredAttributes->first(function ($attribute) {
        return $attribute->whole_capacity > 0;
    });
    $hasIndividualPrice = $facility->prices->contains('price_type', 'individual');
    $hasWholePrice = $facility->prices->contains('price_type', 'whole');
    $defaultShared = $availableRoom && $hasIndividualPrice;
    $defaultWhole = !$defaultShared && $hasWholePrice;
    $wholeAttr = $facility->facilityAttributes->first();

    $addons = $facility->addons->filter(function ($addon) {
        return $addon->is_available && $addon->show === 'both';
    });
@endphp

<link href="{{ asset('css/facility/both_building.css') }}" rel="stylesheet">
<style>

</style>

<script>
    window.facilityData = {
        facility: @json($facility),
        availabilities: @json($facility->availabilities ?? []),
        facilityAttributes: @json($facility->facilityAttributes ?? []),
        wholeAttr: @json($wholeAttr),
        authUserType: @json(auth()->user()->utype)
    };
</script>

<div class="facility-booking-container mb-4">
    <div class="mb-3">
        <h4 class="mb-2 booking-type-header fw-bold border-bottom pb-2">Booking Type:</h4>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="shared" value="shared"
                @if ($defaultShared) checked @endif @if (!$availableRoom || !$hasIndividualPrice) disabled @endif>
            <label class="form-check-label" for="shared">Shared</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="whole_place" value="whole"
                @if ($defaultWhole) checked @endif @if (!$hasWholePrice) disabled @endif>
            <label class="form-check-label" for="whole_place">Whole Place</label>
        </div>
    </div>

    <div id="shared-section" @if (!$defaultShared) style="display: none;" @endif>
        @if ($availableRoom && $hasIndividualPrice)
            <div class="mb-3">
                @php
                    $hasAvailableRooms = true;
                    $assignedRoom = $filteredAttributes->first(function ($attribute) {
                        return $attribute->whole_capacity > 0;
                    });
                @endphp

                @if ($assignedRoom)
                    @php
                        $availability = $assignedRoom->availabilities->first();
                        $displayCapacity = $availability
                            ? $availability->remaining_capacity
                            : $assignedRoom->whole_capacity;
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
                    $datedPriceWithQuantity = $facility->prices->first(function ($price) {
                        return $price->is_there_a_quantity &&
                            $price->is_based_on_days &&
                            ($price->date_from || $price->date_to);
                    });
                @endphp

                @if ($datedPriceWithQuantity)
                    <div class="mb-3 p-2 bg-light rounded border-start border-info border-3">
                        <p class="mb-0">
                            <strong><i class="fa fa-calendar-day me-1"></i>Available Date Range:</strong>
                            {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from)->format('F d') }} -
                            {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to)->format('F d, Y') }}
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

                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#sharedCalendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="sharedCalendarModal" tabindex="-1"
                        aria-labelledby="sharedCalendarModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sharedCalendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div id="shared-calendar" style="max-width: 100%; margin: 0 auto;">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selected-dates-card p-3">
                                                    <h6 class="fw-bold mb-3">Selected Dates</h6>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Start Date:</small>
                                                        <div id="shared-modal-start-date" class="fw-bold">Not selected
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">End Date:</small>
                                                        <div id="shared-modal-end-date" class="fw-bold">Not selected
                                                        </div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-primary"
                                                            data-bs-dismiss="modal" aria-label="Confirm"
                                                            id="shared-confirm-dates">
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
                                <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal"
                                    data-bs-target="#priceQuantityModal">
                                    <i class="fa fa-user-tag me-2"></i> Select Client Types and Quantities
                                </button>
                            </div>
                        </div>

                        <div class="modal fade" id="priceQuantityModal" tabindex="-1"
                            aria-labelledby="priceQuantityModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="priceQuantityModalLabel">Select Client Types and
                                            Quantities</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Maximum capacity for this facility: <strong><span
                                                    class="capacity-value">{{ $wholeAttr->whole_capacity }}</span>
                                                persons</strong>
                                        </div>
                                        <div class="price-quantity-section">
                                            @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                                @if ($price->is_there_a_quantity)
                                                    <input type="hidden" name="price_values[{{ $price->id }}]"
                                                        value="{{ $price->value }}">
                                                    <input type="hidden" name="price_names[{{ $price->id }}]"
                                                        value="{{ $price->name }}">
                                                    <input type="hidden"
                                                        name="price_is_discount[{{ $price->id }}]"
                                                        data-is-discount="{{ $price->is_this_a_discount ? '1' : '0' }}"
                                                        value="{{ $price->is_this_a_discount ? '1' : '0' }}">

                                                    <div class="price-quantity-card mb-4">
                                                        <div
                                                            class="price-header d-flex justify-content-between align-items-center mb-3">
                                                            <h5 class="price-title m-0">
                                                                {{ $price->name }}
                                                                @if ($price->is_this_a_discount)
                                                                    <span class="badge bg-warning text-dark ms-2">
                                                                        <i class="fa fa-info-circle"></i> Requires
                                                                        Proof
                                                                    </span>
                                                                @endif
                                                            </h5>
                                                            <span
                                                                class="price-value badge bg-primary-light text-primary fs-5 fw-bold">
                                                                ₱{{ number_format($price->value, 2) }}
                                                            </span>
                                                        </div>
                                                        <div class="quantity-control">
                                                            <label for="internal_quantity-{{ $price->id }}"
                                                                class="form-label quantity-label">Number of
                                                                Persons</label>
                                                            <input id="internal_quantity-{{ $price->id }}"
                                                                type="number"
                                                                class="form-control quantity-input @error('internal_quantity') is-invalid @enderror"
                                                                name="internal_quantity[{{ $price->id }}]"
                                                                value="{{ old('internal_quantity.' . $price->id) }}"
                                                                min="0" max="{{ $wholeAttr->whole_capacity }}"
                                                                step="1" oninput="validateQuantityInput(this)"
                                                                data-price-id="{{ $price->id }}"
                                                                data-is-discount="{{ $price->is_this_a_discount ? '1' : '0' }}"
                                                                placeholder="Enter quantity">
                                                            @error('internal_quantity.' . $price->id)
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        @if ($price->is_this_a_discount)
                                                            <p class="text-danger mt-2 mb-0 small">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                This requires a proof of id for verification
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        {{-- <p id="quantity-discount-note" class="text-danger mt-2"
                                            style="display:none;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            One or more selected prices require valid discount proof.
                                        </p> --}}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                            onclick="updateTotalPrice()">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($facility->prices->where('is_there_a_quantity', '!=', 1)->where('price_type', 'individual')->isNotEmpty())
                @php
                    $datedPrice = $facility->prices->first(function ($price) {
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
                            {{ \Carbon\Carbon::parse($datedPrice->date_from)->format('F d') }} -
                            {{ \Carbon\Carbon::parse($datedPrice->date_to)->format('F d, Y') }}
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

                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#sharedCalendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="sharedCalendarModal" tabindex="-1"
                        aria-labelledby="sharedCalendarModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="sharedCalendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div id="shared-calendar" style="max-width: 100%; margin: 0 auto;">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="selected-dates-card p-3">
                                                    <h6 class="fw-bold mb-3">Selected Dates</h6>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Start Date:</small>
                                                        <div id="shared-modal-start-date" class="fw-bold">Not selected
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <small class="text-muted">End Date:</small>
                                                        <div id="shared-modal-end-date" class="fw-bold">Not selected
                                                        </div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-primary"
                                                            data-bs-dismiss="modal" aria-label="Confirm"
                                                            id="shared-confirm-dates">
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

            @include('components.facility_both_addons', ['section' => 'shared', 'facility' => $facility])
        @endif
    </div>

    <div id="whole-section" @if (!$defaultWhole) style="display: none;" @endif>
        @if ($hasWholePrice)
            <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}">

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
                                        <strong>From:</strong>
                                        {{ \Carbon\Carbon::parse($firstPrice->date_from)->format('M d, Y') }}<br>
                                        <strong>To:</strong>
                                        {{ \Carbon\Carbon::parse($firstPrice->date_to)->format('M d, Y') }}
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

                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#wholeCalendarModal">
                            <i class="fa fa-calendar me-2"></i> Open Calendar
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="wholeCalendarModal" tabindex="-1"
                    aria-labelledby="wholeCalendarModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="wholeCalendarModalLabel">Select Dates</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                                                    <div id="whole-modal-start-date" class="fw-bold">Not selected
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">End Date:</small>
                                                    <div id="whole-modal-end-date" class="fw-bold">Not selected</div>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-dismiss="modal" aria-label="Confirm"
                                                        id="whole-confirm-dates">
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

            {{-- facility_both_building.blade.php --}}
            {{-- bookType === whole --}}
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-user-tag"></i>
                    <span><strong>Client Type:</strong></span>
                </div>
                <div class="section-content">
                    <select id="whole_client_type" name="whole_client_type" class="client-type-select"
                        onchange="updateWholeTotalPrice()">
                        <option value="" disabled selected>Select a client type</option>
                        @foreach ($facility->prices->where('price_type', 'whole') as $price)
                            <option value="{{ $price->value }}" data-name="{{ $price->name }}"
                                data-discount="{{ $price->is_this_a_discount ? '1' : '0' }}">
                                {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <p id="discount-note" class="text-danger mt-2" style="display: none">
                        <i class="fas fa-info-circle me-1"></i>
                        This requires a proof of id for verification
                    </p>
                </div>
            </div>

            @include('components.facility_both_addons', ['section' => 'whole', 'facility' => $facility])
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

<script src="{{ asset('js/facilities_both_builing/calendar-validation.js') }}"></script>
<script src="{{ asset('js/facilities_both_builing/client-type-handler.js') }}"></script>
<script src="{{ asset('js/facilities_both_builing/price-computation.js') }}"></script>
<script src="{{ asset('js/facilities_both_builing/form-validation.js') }}"></script>
<script src="{{ asset('js/facilities_both_builing/facility-booking-init.js') }}"></script>
