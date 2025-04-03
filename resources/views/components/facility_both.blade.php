<div id="dynamic_prices_container">
    <div id="prices_display">
        <div data-price-type="individual">
            @foreach ($pricesWithAttributes->where('price_type', 'individual') as $price)
                <h4><strong>{{ $price->name }}: </strong>
                    <span class="product-type text-primary">&#8369;
                        {{ number_format($price->value, 2) }}
                    </span>
                </h4>
                <p>
                    @if ($price->is_based_on_days)
                        <span class="badge">Per Day</span>
                    @endif
                </p>
            @endforeach
        </div>

        <div data-price-type="whole">
            @foreach ($pricesWithAttributes->where('price_type', 'whole') as $price)
                <h4><strong>{{ $price->name }}: </strong>
                    <span class="product-type text-primary">&#8369;
                        {{ number_format($price->value, 2) }}
                    </span>
                </h4>
                <p>
                    @if ($price->is_based_on_days)
                        <span class="badge">Per Day</span>
                    @endif
                </p>
            @endforeach
        </div>
    </div>
</div>

<div>

    @foreach ($pricesWithoutAttributes as $price)
        <h4><strong>{{ $price->name }}: </strong>
            <span class="product-type text-primary">&#8369;
                {{ number_format($price->value, 2) }}</span>
        </h4>
        <p>
            @if ($price->is_based_on_days)
                <span class="badge">Per Day</span>
            @endif
        </p>
    @endforeach
</div>

<div>
    <!-- Rooms Display Based on Selection -->
    <div id="rooms_display" class="mt-4">
        <!-- Shared Rooms -->
        <div id="shared_rooms" style="display: none;">
            <h4>Shared Rooms</h4>
            <ul class="list-group">
                @foreach ($facility->facilityAttributes as $room)
                    @if ($room->remaining_capacity > 0)
                        <li class="list-group-item">
                            {{ $room->room_name }} - Capacity: {{ $room->remaining_capacity }}
                            out
                            of {{ $room->capacity }}
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <!-- Solo Rooms -->
        <div id="solo_rooms" style="display: none;">
            <h4>Solo Rooms</h4>
            <ul class="list-group">
                @foreach ($facility->facilityAttributes as $room)
                    @if ($room->remaining_capacity == $room->capacity)
                        <li class="list-group-item">
                            {{ $room->room_name }} - Capacity: {{ $room->capacity }} out of
                            {{ $room->capacity }}
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>



@if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity)
    <input type="hidden" name="price_type" id="price_type" value="">
    <input type="hidden" name="price_value" id="price_value" value="">
    <input type="hidden" name="price_id" id="price_id" value="">
    <input type="hidden" name="room_id" id="room_id" value="">
    <div class="reservation-section">
        <div class="my-2">
            <label for="calendar"><strong>Select Reservation Date:</strong></label>
            <div id="calendar"></div>

            <!-- Hidden input fields to store selected dates -->
            <input type="hidden" id="date_from" name="date_from"
                value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
            <input type="hidden" id="date_to" name="date_to"
                value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">

            <div id="selected-date" class="my-3">
                @if (isset($reservationData['date_from']))
                    <p class="select-date"><strong>Selected Date From:</strong>
                        {{ $reservationData['date_from'] }}</p>
                @endif
                @if (isset($reservationData['date_to']))
                    <p class="select-date"><strong>Selected Date To:</strong>
                        {{ $reservationData['date_to'] }}</p>
                @endif
            </div>
        </div>
    </div>
    <div style="margin-bottom: 15px;">
        <label><strong>Select Type:</strong></label>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="form-check form-check-inline">
                    <input type="radio" id="solo" name="reservation_type" value="solo"
                        class="form-check-input">
                    <label for="solo" class="form-check-label">Solo</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" id="shared" name="reservation_type" value="shared"
                        class="form-check-input">
                    <label for="shared" class="form-check-label">Shared</label>
                </div>
            </div>
            <div>
                <button type="button" id="reset_button" class="btn btn-secondary btn-sm">Reset</button>
            </div>
        </div>
    </div>

    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
        <div id="individual_inputs" style="display:none;">
            <label><strong>Set Quantity:</strong></label>
            <div class="form-floating mb-3">
                <input id="internal_quantity" type="number"
                    class="form-control form-control_gray @error('internal_quantity') is-invali10.d @enderror"
                    name="internal_quantity" value="{{ old('internal_quantity') }}">
                <label for="internal_quantity">
                    Enter Internal Quantity
                </label>
            </div>

            <div class="form-floating mb-3">
                <input id="external_quantity" type="number"
                    class="form-control form-control_gray @error('external_quantity') is-invalid @enderror"
                    name="external_quantity" value="{{ old('external_quantity') }}">
                <label for="external_quantity">
                    Enter External Quantity
                </label>
            </div>
        </div>
    @endif

    <div id="shared_inputs" style="display: none;">
        <div class="form-floating mb-3">
            <select id="shared_room_select" name="shared_room_select" class="form-select">
                <option value="" disabled selected>Select Shared Room</option>
                @foreach ($facility->facilityAttributes as $room)
                    @if ($room->remaining_capacity > 0)
                        <option value="{{ $room->id }}">
                            {{ $room->room_name }} - Capacity: {{ $room->remaining_capacity }}
                            out of {{ $room->capacity }}
                        </option>
                    @endif
                @endforeach
            </select>
            <label for="shared_room_select">Shared Rooms:</label>
        </div>
    </div>

    <div id="shared_type_dropdown" style="display: none;">
        <div class="form-floating mb-3">
            <select id="shared_price_select" name="shared_price_select" class="form-select">
                <option value="" disabled selected>Select Shared Type</option>
                @foreach ($facility->prices->where('price_type', 'individual') as $price)
                    <option value="{{ $price->id }}">{{ $price->name }}</option>
                @endforeach
            </select>
            <label for="shared_price_select">Shared Type:</label>
        </div>
    </div>

    <!-- Solo Room Select -->
    <div id="solo_dropdown" style="display: none;">
        <div class="form-floating mb-3">
            <select id="solo_room_select" name="solo_room_select" class="form-select">
                <option value="" disabled selected>Select Solo Room</option>
                @foreach ($facility->facilityAttributes as $room)
                    @if ($room->remaining_capacity == $room->capacity)
                        <option value="{{ $room->id }}">
                            {{ $room->room_name }} - Capacity: {{ $room->capacity }}
                            out of {{ $room->capacity }}
                        </option>
                    @endif
                @endforeach
            </select>
            <label for="solo_room_select">Solo Rooms:</label>
        </div>
    </div>

    <!-- Solo Price Select -->
    <div id="solo_type_dropdown" style="display: none;">
        <div class="form-floating mb-3">
            <select id="solo_price_select" name="solo_price_select" class="form-select">
                <option value="" disabled selected>Select Solo Type</option>
                @foreach ($facility->prices->where('price_type', 'whole') as $price)
                    <option value="{{ $price->id }}">{{ $price->name }}</option>
                @endforeach
            </select>
            <label for="solo_price_select">Solo Type:</label>
        </div>
    </div>
@endif

@if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Oops! Something went wrong.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <input type="hidden" name="price_type" id="price_type" value="">
    <input type="hidden" name="price_id" id="price_id" value="">
    <input type="hidden" name="total_price" id="total_price_input" value="0">

    <div style="margin-bottom: 15px;">
        <label><strong>Select Type:</strong></label>
        <div style="display: flex; justify-content: space-between;">
            <div>
                <div>
                    <input type="radio" id="individual" name="reservation_type" value="individual">
                    <label for="individual">Individual</label>
                </div>
                <div>
                    <input type="radio" id="exclusive" name="reservation_type" value="exclusive">
                    <label for="exclusive">Exclusive</label>
                </div>
            </div>
            <div>
                <button type="button" id="reset_button" class="btn btn-shop btn-addtocart"
                    style="font-size: 10px;">Reset</button>
            </div>
        </div>
    </div>

    @if ($facility->prices->where('is_there_a_quantity', true)->where('price_type', 'individual')->count() > 0)
        <div id="individual_inputs" style="display: none;">
            <label><strong>Set Quantity:</strong></label>
            @foreach ($facility->prices->where('is_there_a_quantity', true)->where('price_type', 'individual') as $price)
                <div class="form-floating mb-3">
                    <input id="quantity_{{ $price->id }}" type="number" class="form-control quantity-input"
                        name="quantity[{{ $price->id }}]" value="{{ old('quantity.' . $price->id) }}"
                        min="0" data-price="{{ $price->value }}">
                    <label for="quantity_{{ $price->id }}">Enter Quantity for
                        {{ $price->name }}</label>
                </div>
            @endforeach
            {{-- <p><strong>Total Price:</strong> ₱<span id="grand_total">0.00</span></p> --}}
        </div>
    @endif


    <div id="exclusive_dropdown" style="display: none;">
        <div class="form-floating mb-3">
            <select id="exclusive_type" name="exclusive_type"
                class="form-select @error('exclusive_type') is-invalid @enderror">
                <option value="" disabled selected>Select Exclusive Type</option>
                @foreach ($facility->prices->where('price_type', 'whole') as $price)
                    <option value="{{ $price->id }}" data-price="{{ $price->value }}">
                        {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                    </option>
                @endforeach
            </select>
            <label for="exclusive_type">Exclusive Type:</label>

        </div>
    </div>
    <div class="reservation-section">
        <div class="my-2">
            <div id="calendar"></div>
            <input type="hidden" id="date_from" name="date_from"
                value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
            <input type="hidden" id="date_to" name="date_to"
                value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
            <div id="selected-date" class="my-3">
                @if (isset($date_from))
                    {{-- <p class="select-date"><strong>Selected Date:</strong> {{ $date_from }}</p> --}}
                    <p class="select-date"><strong>Selected Date:</strong>
                        {{ $reservationData['date_from'] }}</p>
                @endif
            </div>


        </div>
    </div>
@endif
<div id="total_price" style="font-weight: bold; "><strong>Total Price:</strong> &#8369; 0.00
</div>
