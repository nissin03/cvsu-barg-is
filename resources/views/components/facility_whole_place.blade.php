<div class="mb-3">
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
            <!-- Display price options -->
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
    @else
        <p>No prices available for this facility.</p>
    @endif
    <div class="reservation-section">
        <div class="my-2">
            <label for="calendar"><strong>Select Reservation Date:</strong></label>
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

            @error('date_from')
                <span class="text-danger alert alert-warning " role="alert"> <strong> {{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="form-group">
        <label for="client_type" style="margin-bottom: 7px;"><strong>Client Type:
            </strong></label>
        <select id="client_type" class="form-control">
            <option value="" disabled selected>Select a client type</option>
            @foreach ($facility->prices as $price)
                <option value="{{ $price->value }}" data-name="{{ $price->name }}">
                    {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                </option>
            @endforeach
        </select>
    </div>

    <div id="total-price" style="margin-top: 20px;">
        <strong>Total Price: </strong><span>&#8369; 0.00</span>
    </div>
</div>
