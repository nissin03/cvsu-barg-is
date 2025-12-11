@php
    $hasAvailableRooms = false;

    $filteredAttributes = $facility->facilityAttributes->filter(function ($attribute) {
        // Show to admin regardless of restriction
        if (auth()->check() && auth()->user()->utype === 'ADM') {
            return true;
        }

        // Show to users if no restriction, 'all' restriction, or matches user's sex
    if (is_null($attribute->sex_restriction) || $attribute->sex_restriction === 'all') {
        return true;
    }

    return auth()->check() && $attribute->sex_restriction === auth()->user()->sex;
});

$availableRoom = $filteredAttributes->first(function ($attribute) {
    return $attribute->capacity > 0;
});

$hasIndividualPrice = $facility->prices->contains('price_type', 'individual');
$hasWholePrice = $facility->prices->contains('price_type', 'whole');

$defaultShared = $availableRoom && $hasIndividualPrice;
$defaultWhole = !$defaultShared && $hasWholePrice;

$allRooms = $facility->facilityAttributes;

$availableRooms = $allRooms->filter(function ($room) {
    $availability = $room->availabilities->first();

    $isAvailable = !$availability || $availability->remaining_capacity >= $room->capacity;

    $sexAllowed = true;
    if ($room->sex_restriction && auth()->check() && auth()->user()->utype === 'USR') {
        // Allow if restriction is 'all' or matches user's sex
            $sexAllowed =
                $room->sex_restriction === 'all' || (auth()->check() && $room->sex_restriction === auth()->user()->sex);
        }

        return $isAvailable && $sexAllowed;
    });

    $hasAvailableWholeRooms = $availableRooms->isNotEmpty();
@endphp

<link href="{{ asset('css/facility/both_rooms.css') }}" rel="stylesheet">

<div class="facility-booking-container mb-4">
    <div class="mb-3">
        <h4 class="mb-2 booking-type-header fw-bold border-bottom pb-2">Booking Type:</h4>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="shared" value="shared"
                @if ($defaultShared) checked @endif @if (!$availableRoom)  @endif>
            <label class="form-check-label" for="shared">Shared</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="booking_type" id="whole_place" value="whole"
                @if ($defaultWhole) checked @endif @if (!$hasWholePrice)  @endif>
            <label class="form-check-label" for="whole_place">Whole Place</label>
        </div>
    </div>

    <div id="shared-section" @if (!$defaultShared) style="display: none;" @endif>
        @if ($availableRoom && $hasIndividualPrice)
            @php
                $hasIndividualPriceBasedOnDays = $facility->prices
                    ->where('price_type', 'individual')
                    ->where('is_based_on_days', true)
                    ->isNotEmpty();
            @endphp

            @if ($hasIndividualPriceBasedOnDays)
                <div class="booking-section">
                    <div class="section-header">
                        <i class="fa fa-door-open"></i>
                        <span>Assigned Room</span>
                    </div>
                    <div class="section-content">
                        @php
                            $hasAvailableRooms = true;
                            $assignedRoom = $filteredAttributes->first(function ($attribute) {
                                return $attribute->capacity > 0;
                            });
                        @endphp

                        @if ($assignedRoom)
                            <div class="capacity-info mb-2">
                                <div class="capacity-card">
                                    <i class="fa fa-door-open"></i>
                                    <span class="capacity-text">{{ $assignedRoom->room_name }}</span>
                                    @if ($assignedRoom->sex_restriction && $assignedRoom->sex_restriction !== 'all')
                                        <span class="badge bg-info ms-2">
                                            <i
                                                class="fa fa-{{ $assignedRoom->sex_restriction === 'male' ? 'mars' : 'venus' }} me-1"></i>
                                            {{ ucfirst($assignedRoom->sex_restriction) }} Only
                                        </span>
                                    @elseif($assignedRoom->sex_restriction === 'all')
                                        <span class="badge bg-success ms-2">
                                            <i class="fa fa-venus-mars me-1"></i>
                                            All Sex
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
                            <select name="shared_selected_room" id="shared_selected_room" class="client-type-select"
                                required>
                                <option value="">Select a Room</option>
                                @foreach ($filteredAttributes as $room)
                                    @if ($room->capacity > 0)
                                        <option value="{{ $room->id }}" data-capacity="{{ $room->capacity }}"
                                            data-room-name="{{ $room->room_name }}">
                                            {{ $room->room_name }} (Capacity: {{ $room->capacity }} person(s))
                                            @if ($room->sex_restriction && $room->sex_restriction !== 'all')
                                                - {{ ucfirst($room->sex_restriction) }} only
                                            @elseif($room->sex_restriction === 'all')
                                                - All Sex
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" id="shared_selected_room_name" name="shared_room_name" value="">
                            <input type="hidden" id="shared_selected_room_capacity" name="shared_room_capacity"
                                value="">
                        </div>
                    </div>
                </div>
            @endif

            @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                @php
                    $datedPriceWithQuantity = $facility->prices->first(function ($price) {
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
                                    <strong>From:</strong>
                                    {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from)->format('F d, Y') }}
                                </div>
                                <div class="date-item">
                                    <strong>To:</strong>
                                    {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to)->format('F d, Y') }}
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

                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#calendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
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
                                                    <div class="mb-3">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Start Date:</small>
                                                            <div id="modal-start-date" class="fw-bold"></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <small class="text-muted">End Date:</small>
                                                            <div id="modal-end-date" class="fw-bold"></div>
                                                        </div>
                                                        <div class="legend small" aria-label="Legend">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="box bg-danger">
                                                                </div>
                                                                <span class="text-muted">Fully Booked</span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="box bg-success">
                                                                </div>
                                                                <span class="text-muted">Available</span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="box bg-warning">
                                                                </div>
                                                                <span class="text-muted">Some Reserve the
                                                                    facility</span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="box bg-secondary">
                                                                </div>
                                                                <span class="text-muted">Unavailable</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-primary"
                                                            data-bs-dismiss="modal" aria-label="Confirm"
                                                            id="confirm-dates">
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
                        <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal"
                            data-bs-target="#clientTypeModal">
                            <i class="fa fa-user-tag me-2"></i> Select Client Types
                        </button>
                    </div>
                </div>

                <!-- Modal for selecting client types -->
                <div class="modal fade" id="clientTypeModal" tabindex="-1" aria-labelledby="clientTypeModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="clientTypeModalLabel">
                                    <i class="fas fa-user-tag me-2"></i> Select Client Types
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Maximum capacity for this facility: <strong><span
                                            class="capacity-value"></span></strong>
                                </div>
                                @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                    @if ($price->is_there_a_quantity)
                                        <input type="hidden" name="price_values[{{ $price->id }}]"
                                            value="{{ $price->value }}">
                                        <input type="hidden" name="price_names[{{ $price->id }}]"
                                            value="{{ $price->name }}">
                                        <input type="hidden" name="price_is_discount[{{ $price->id }}]"
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
                                                <div class="input-group">
                                                    <span class="input-group-text quantity-label">Quantity</span>
                                                    <input type="number" class="form-control quantity-input"
                                                        name="internal_quantity[{{ $price->id }}]"
                                                        value="{{ old('internal_quantity.' . $price->id) }}"
                                                        min="0" oninput="updateTotalPrice()"
                                                        data-price-id="{{ $price->id }}"
                                                        data-is-discount="{{ $price->is_this_a_discount ? '1' : '0' }}"
                                                        placeholder="Enter Quantity">
                                                </div>
                                            </div>
                                            @if ($price->is_this_a_discount)
                                                <p class="text-danger mt-2 small">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    This requires a proof of id for verification
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                    onclick="updateClientTypeDisplay()">Save Changes</button>
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
                    <div class="booking-section">
                        <div class="section-header">
                            <i class="fa fa-calendar-alt"></i>
                            <span>Available Date Range</span>
                        </div>
                        <div class="section-content">
                            <div class="date-range-display">
                                <div class="date-item">
                                    <strong>From:</strong>
                                    {{ \Carbon\Carbon::parse($datedPrice->date_from)->format('F d, Y') }}
                                </div>
                                <div class="date-item">
                                    <strong>To:</strong>
                                    {{ \Carbon\Carbon::parse($datedPrice->date_to)->format('F d, Y') }}
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

                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#calendarModal">
                                <i class="fa fa-calendar me-2"></i> Open Calendar
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
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
                                                        <button type="button" class="btn btn-primary"
                                                            data-bs-dismiss="modal" aria-label="Confirm"
                                                            id="confirm-dates">
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
                        <i class="fa fa-tags"></i>
                        <span>Price Options</span>
                    </div>
                    <div class="section-content">
                        <div class="time-input-group">
                            <select name="price_id" id="price_id" class="client-type-select"
                                onchange="updateTotalPrice()">
                                <option value="">Select Price</option>
                                @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                    @if (!$price->is_there_a_quantity)
                                        <option value="{{ $price->id }}" data-value="{{ $price->value }}"
                                            data-discount="{{ $price->is_this_a_discount }}">
                                            {{ $price->name }} - ₱{{ number_format($price->value, 2) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <p id="discount-note" class="text-danger mt-2" style="display:none;">
                            This price requires a valid discount proof.
                        </p>

                        <input type="hidden" id="selected_price_value" name="selected_price" value="">
                    </div>
                </div> --}}
            @endif
        @endif
        @include('components.facility_both_addons_rooms', ['section' => 'shared', 'facility' => $facility])
    </div>

    <div id="whole-section" @if (!$defaultWhole) style="display: none;" @endif>
        @if ($hasWholePrice)
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
                                @if (auth()->check() &&
                                        (auth()->user()->utype === 'ADM' ||
                                            !$room->sex_restriction ||
                                            $room->sex_restriction === 'all' ||
                                            $room->sex_restriction === auth()->user()->sex))
                                    <option value="{{ $room->id }}" data-capacity="{{ $room->capacity }}"
                                        data-room-name="{{ $room->room_name }}">
                                        {{ $room->room_name }} (Capacity: {{ $room->capacity }})
                                        @if ($room->sex_restriction && $room->sex_restriction !== 'all')
                                            - {{ ucfirst($room->sex_restriction) }} only
                                        @elseif($room->sex_restriction === 'all')
                                            - All Sex
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
                $datedWholePrice = $facility->prices->first(function ($price) {
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
                                <strong>From:</strong>
                                {{ \Carbon\Carbon::parse($datedWholePrice->date_from)->format('F d, Y') }}
                            </div>
                            <div class="date-item">
                                <strong>To:</strong>
                                {{ \Carbon\Carbon::parse($datedWholePrice->date_to)->format('F d, Y') }}
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
                                                    <div id="whole-modal-start-date" class="fw-bold"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">End Date:</small>
                                                    <div id="whole-modal-end-date" class="fw-bold"></div>
                                                </div>
                                                <hr class="divider">
                                                <div class="legend small" aria-label="Legend">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="box bg-danger">
                                                        </div>
                                                        <span class="text-muted">Fully Booked</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="box bg-success">
                                                        </div>
                                                        <span class="text-muted">Available</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="box bg-orange">
                                                        </div>
                                                        <span class="text-muted">Occupied</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="box bg-secondary">
                                                        </div>
                                                        <span class="text-muted">Unavailable</span>
                                                    </div>
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

            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-tags"></i>
                    <span>Price Options</span>
                </div>
                <div class="section-content">
                    <div class="time-input-group">
                        <select name="whole_price_id" id="whole_price_id" class="client-type-select"
                            onchange="updateTotalPrice()">
                            <option value="">Select Price</option>
                            @foreach ($facility->prices->where('price_type', 'whole') as $price)
                                <option value="{{ $price->id }}" data-value="{{ $price->value }}"
                                    data-discount="{{ $price->is_this_a_discount ? '1' : '0' }}">
                                    {{ $price->name }} - ₱{{ number_format($price->value, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <p id="discount-note" class="text-danger mt-2" style="display:none;">
                            <i class="fas fa-info-circle me-1"></i>
                            This requires a proof of id for verification
                        </p>
                    </div>

                    <input type="hidden" id="selected_whole_price_value" name="selected_whole_price"
                        value="">
                </div>
            </div>
        @endif
        @include('components.facility_both_addons_rooms', ['section' => 'whole', 'facility' => $facility])
    </div>

    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price: </strong>
        <span id="computed-total" class="total-price-value">₱ 0.00</span>
    </div>
</div>

{{-- JavaScript includes --}}
<script>
    // Global variables for JavaScript modules
    window.userType = @json(auth()->user()->utype ?? 'USR');
    window.availabilities = @json($facility->availabilities ?? []);
    window.facilityAttributes = @json($facility->facilityAttributes ?? []);
    window.hasQuantityPrices = @json($facility->prices->where('price_type', 'individual')->where('is_there_a_quantity', true)->isNotEmpty());
    window.hasNonQuantityPrices = @json($facility->prices->where('price_type', 'individual')->where('is_there_a_quantity', false)->isNotEmpty());
    window.isBasedOnDays = @json($facility->prices->where('price_type', 'individual')->first()?->is_based_on_days ?? false);
</script>

{{-- Include JavaScript modules --}}
<script src="{{ asset('js/facilities_both_rooms/calendar.js') }}"></script>
<script src="{{ asset('js/facilities_both_rooms/client-types.js') }}"></script>
<script src="{{ asset('js/facilities_both_rooms/price-computation.js') }}"></script>
<script src="{{ asset('js/facilities_both_rooms/validation.js') }}"></script>
