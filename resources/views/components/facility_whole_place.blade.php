<link href="{{ asset('css/facility/whole_place.css') }}" rel="stylesheet">

<div class="facility-booking-container mb-4">
    <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}"
        data-has-day-based-pricing="{{ $facility->prices->contains('is_based_on_days', true) ? 'true' : 'false' }}">
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
        @endforeach
    @else
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <div>No prices available for this facility.</div>
        </div>
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
        $userRole = auth()->user()->role ?? 'student';
        $userType = auth()->user()->utype ?? 'USR';
        $tomorrowFormatted = \Carbon\Carbon::tomorrow()->format('Y-m-d');
    @endphp

    @if ($hasDayBasedPricing)
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calendar-alt"></i>
                <span>Date Scheduled:</span>
            </div>
            <div class="section-content">
                @php
                    $firstPrice = $facility->prices->where('is_based_on_days', true)->first();
                @endphp

                @if ($firstPrice && $firstPrice->date_from && $firstPrice->date_to)
                    <div class="date-range-display">
                        <div class="date-item">
                            <strong>From:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_from)->format('M d, Y') }}
                        </div>
                        <div class="date-item">
                            <strong>To:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_to)->format('M d, Y') }}
                        </div>
                    </div>
                    <input type="hidden" id="date_from" name="date_from" value="{{ $firstPrice->date_from }}">
                    <input type="hidden" id="date_to" name="date_to" value="{{ $firstPrice->date_to }}">
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
                <input type="hidden" id="date_from" name="date_from" required>
                <input type="hidden" id="date_to" name="date_to" required>

                <div class="selected-dates-display">
                    <div class="date-selection-item">
                        <strong>Start Date:</strong>
                        <span id="start-date-display"></span>
                    </div>
                    <div class="date-selection-item">
                        <strong>End Date:</strong>
                        <span id="end-date-display"></span>
                    </div>
                </div>

                <div id="error-message" class="error-message"></div>

                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                    data-bs-target="#calendarModal">
                    <i class="fa fa-calendar me-2"></i> Open Calendar
                </button>
            </div>
        </div>

        <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8">
                                    <div id="calendar" data-user-type="{{ $userType }}"
                                        data-user-role="{{ $userRole }}"
                                        data-availabilities="{{ json_encode($facility->availabilities ?? []) }}"
                                        data-facility-capacity="{{ $wholeAttr->whole_capacity ?? 0 }}"
                                        data-tomorrow-formatted="{{ $tomorrowFormatted }}"
                                        style="max-width: 100%; margin: 0 auto;"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="selected-dates-card p-3">
                                        <h6 class="fw-bold mb-3">Selected Dates</h6>
                                        <div class="mb-2">
                                            <small class="text-muted">Start Date:</small>
                                            <div id="modal-start-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">End Date:</small>
                                            <div id="modal-end-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                                aria-label="Confirm" id="confirm-dates">
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
            <i class="fa fa-clock"></i>
            <span>Time Selection</span>
        </div>
        <div class="section-content">
            <div id="time-slot-container" class="time-slot-grid">
                <div class="time-input-group">
                    <label for="time_start" class="time-label">Start Time</label>
                    <select id="time_start" name="time_start" class="form-select time-select"
                        data-user-type="{{ $userType }}" data-user-role="{{ $userRole }}">

                    </select>
                </div>
                <div class="time-input-group">
                    <label for="time_end" class="time-label">End Time</label>
                    <select id="time_end" name="time_end" class="form-select time-select" disabled>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- facility_whole_place.blade.php --}}
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-user-tag"></i>
            <span><strong>Client Type:</strong></span>
        </div>
        <div class="section-content">
            <select id="client_type" name="client_type" class="client-type-select">
                <option value="" disabled selected>Select a client type</option>
                @foreach ($facility->prices as $price)
                    <option value="{{ $price->value }}" data-name="{{ $price->name }}"
                        data-discount="{{ $price->is_this_a_discount ? '1' : '0' }}">
                        {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                    </option>
                @endforeach
            </select>
            <p id="discount-note" class="text-danger mt-2" style="display:none;">
                <i class="fas fa-info-circle me-1"></i>
                This requires a proof of id for verification
            </p>
        </div>
    </div>

    @include('components.facility_whole_addons')

    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price: </strong>
        <span class="total-price-value">₱ 0.00</span>
    </div>

    <input type="hidden" name="total_price" id="total_price_input" value="0">
</div>

<!-- Include JavaScript files -->
<script src="{{ asset('js/facilities_whole_building/priceComputation.js') }}"></script>
<script src="{{ asset('js/facilities_whole_building/validation.js') }}"></script>
<script src="{{ asset('js/facilities_whole_building/clientType.js') }}"></script>
<script src="{{ asset('js/facilities_whole_building/timeSelection.js') }}"></script>
<script src="{{ asset('js/facilities_whole_building/calendar.js') }}"></script>
