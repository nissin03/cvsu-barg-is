<input type="hidden" name="total_price" id="total-price-field" value="{{ $individualPrice }}">
<div class="mb-3">
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
            <h4><strong>{{ $price->name }}: </strong> <span class="product-type text-primary">&#8369;
                    {{ number_format($price->value, 2) }}</span> </h4>
        @endforeach
    @else
        <p>No prices available for this facility.</p>
    @endif


    @if ($roomNumbers->isNotEmpty())
        @php
            $firstRoom = $roomNumbers->first();
            $lastRoom = $roomNumbers->last();
        @endphp
    @endif
    @php
        // Extract and process room details
        $roomDetails = $facility->facilityAttributes
            ->filter(fn($attribute) => $attribute->room_name && $attribute->capacity)
            ->map(
                fn($attribute) => [
                    'room_number' => preg_replace('/[^0-9]/', '', $attribute->room_name),
                    'capacity' => $attribute->capacity,
                ],
            )
            ->sortBy('room_number')
            ->values();

        $groupedRooms = $roomDetails->groupBy('capacity');
    @endphp

    @if ($groupedRooms->isNotEmpty())
        <p><strong>Room Capacities:</strong></p>
        <ul style="padding: 0">
            @foreach ($groupedRooms as $capacity => $rooms)
                @php
                    // Generate room range for the group
                    $roomNumbers = $rooms->pluck('room_number')->map(fn($num) => "R{$num}");
                    $range =
                        $roomNumbers->count() > 1
                            ? $roomNumbers->first() . '-' . $roomNumbers->last()
                            : $roomNumbers->first();
                @endphp
                <li>{{ $range }} - {{ $capacity }} People</li>
            @endforeach
        </ul>
    @endif

    @if ($facility->prices->where('is_there_a_quantity', true)->isNotEmpty())
        <div id="dynamic_quantity_inputs" class="mb-3">
            <label class="mb-2"><strong>Set Quantity:</strong></label>

            @foreach ($facility->prices->where('is_there_a_quantity', true) as $price)
                <div class="form-floating mb-3">
                    <input type="number" name="quantities[{{ $price->id }}]" id="quantity_{{ $price->id }}"
                        class="form-control form-control_gray quantity-input" data-price="{{ $price->value }}"
                        value="{{ old('quantities.' . $price->id) }}">
                    <label for="quantity_{{ $price->id }}">Enter Quantity for {{ $price->name }}</label>
                </div>
            @endforeach
        </div>
    @endif


    <div class="alert alert-warning " role="alert" style="margin-top: ">
        <p><strong>Note: </strong>Rooms selection is not available in this facility.</p>
    </div>

    <input type="hidden" name="date_from" value="{{ $price->date_from }}">
    <input type="hidden" name="date_to" value="{{ $price->date_to }}">


    <div id="total-price" style="margin-top: 20px;">
        <strong>Total Price: </strong><span id="computed-total">&#8369;{{ number_format($individualPrice, 2) }}</span>
    </div>

</div>
