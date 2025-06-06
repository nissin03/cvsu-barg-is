<input type="hidden" name="total_price" id="total-price-field" value="{{ $individualPrice }}">
<div class="mb-3">
    {{-- Prices Display --}}
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
            <h4>
                <strong>{{ $price->name }}: </strong>
                <span class="product-type text-primary">&#8369;
                    {{ number_format($price->value, 2) }}
                </span>
            </h4>
        @endforeach
    @else
        <p>No prices available for this facility.</p>
    @endif

    {{-- Prepare Room Data --}}
    @php
        $rooms = $facility->facilityAttributes
            ->filter(fn($room) => $room->room_name && $room->capacity)
            ->map(
                fn($room) => [
                    'name' => $room->room_name,
                    'capacity' => $room->capacity,
                ],
            )
            ->sortBy('capacity')
            ->values();

        $roomDetails = $facility->facilityAttributes
            ->filter(fn($attribute) => $attribute->room_name && $attribute->capacity)
            ->map(
                fn($attribute) => [
                    'room_name' => $attribute->room_name,
                    'capacity' => $attribute->capacity,
                ],
            )
            ->sortBy('room_name')
            ->values();

        $groupedRooms = $roomDetails->groupBy('capacity');
    @endphp

    {{-- Grouped Room Display --}}
    @if ($groupedRooms->isNotEmpty())
        <p><strong>Room Capacities:</strong></p>
        <ul style="padding: 0">
            @foreach ($groupedRooms as $capacity => $rooms)
                @php
                    $roomNames = $rooms->pluck('room_name');
                    $range =
                        $roomNames->count() > 1
                            ? $roomNames->first() . ' - ' . $roomNames->last()
                            : $roomNames->first();
                @endphp
                <li>{{ $range }} - {{ $capacity }} People</li>
            @endforeach
        </ul>
    @endif

    {{-- Quantity Input Fields --}}
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

    {{-- Room Notice --}}
    <div class="alert alert-warning " role="alert">
        <p><strong>Note: </strong>Rooms selection is not available in this facility.</p>
    </div>

    {{-- Hidden Date Inputs --}}
    <input type="hidden" name="date_from" value="{{ $price->date_from }}">
    <input type="hidden" name="date_to" value="{{ $price->date_to }}">

    {{-- Total Price --}}
    <div id="total-price" style="margin-top: 20px;">
        <strong>Total Price: </strong>
        <span id="computed-total">&#8369;{{ number_format($individualPrice, 2) }}</span>
    </div>
</div>

{{-- JavaScript --}}
<script>
    document.querySelectorAll('.quantity-input').forEach(input => {
        const rooms = @json($rooms);
        input.addEventListener('input', function() {
            const quantity = parseInt(this.value || 0);
            if (isNaN(quantity) || quantity <= 0) return;

            const totalCapacity = rooms.reduce((sum, room) => sum + room.capacity, 0);

            if (quantity > totalCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Over capacity!',
                    text: `Only ${totalCapacity} people can be accommodated in total.`,
                });
                this.value = totalCapacity;
                return;
            }

            computeDynamicTotal();
        });
    });
</script>
