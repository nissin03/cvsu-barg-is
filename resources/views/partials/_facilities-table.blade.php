@forelse ($facilities as $facility)
    <tr class="facility-row" data-href="{{ route('admin.facilities.edit', ['id' => $facility->id]) }}"
        style="cursor: pointer;">
        <td class="facility-cell">

            <img src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}" class="facility-img">

            <div class="facility-info position-relative">
                <a href="#" class="body-title-2 facility-name"
                    data-id="{{ $facility->id }}">{{ $facility->name }}</a>
                <div class="facility-tooltip tooltip" id="facility-tooltip-{{ $facility->id }}">
                    <strong>Rules:</strong>
                    <span class="badge {{ $facility->rules_and_regulations ? 'badge-success' : 'badge-danger' }}">
                        {{ $facility->rules_and_regulations ? 'Available' : 'N/A' }}
                    </span>
                    <br>
                    <strong>Requirements:</strong>
                    <span class="badge {{ $facility->requirements ? 'badge-success' : 'badge-danger' }}">
                        {{ $facility->requirements ? 'Available' : 'N/A' }}
                    </span>
                </div>
            </div>
        </td>
        <td>{{ $facility->facility_type ? $facility->facility_type : 'N/A' }}</td>
        <td>
            @php $prices = $facility->prices ?? collect(); @endphp
            @if ($prices->count())
                <span class="badge badge-info">
                    {{ $prices[0]->price_type === 'individual' ? 'Individual' : 'Whole' }}:
                    ₱{{ number_format($prices[0]->value, 2) }}
                </span>
                @if ($prices->count() > 1)
                    <span class="badge badge-secondary price-tooltip-trigger" data-id="{{ $facility->id }}">
                        +{{ $prices->count() - 1 }} more
                        <span class="price-tooltip tooltip">
                            @foreach ($prices->slice(1) as $price)
                                <div>{{ $price->price_type === 'individual' ? 'Individual' : 'Whole' }}:
                                    ₱{{ number_format($price->value, 2) }}</div>
                            @endforeach
                        </span>
                    </span>
                @endif
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>
            @php $attrs = $facility->facilityAttributes ?? collect(); @endphp
            @if ($attrs->count())
                <span class="badge badge-info">
                    {{ $attrs[0]->room_name }}@if ($attrs[0]->capacity)
                        <small>(Cap: {{ $attrs[0]->capacity }})</small>
                    @endif
                </span>
                @if ($attrs->count() > 1)
                    <span class="badge badge-secondary room-tooltip-trigger" data-id="{{ $facility->id }}">
                        +{{ $attrs->count() - 1 }} more
                        <span class="room-tooltip tooltip">
                            @foreach ($attrs->slice(1) as $attr)
                                <div>{{ $attr->room_name }}@if ($attr->capacity)
                                        <small>(Cap: {{ $attr->capacity }})</small>
                                    @endif
                                </div>
                            @endforeach
                        </span>
                    </span>
                @endif
            @endif
        </td>
        <td>
            <div class="list-icon-function">
                <a href="{{ route('admin.facilities.edit', ['id' => $facility->id]) }}">
                    <div class="item edit">
                        <i class="icon-edit-3"></i>
                    </div>
                </a>
                <form action="{{ route('admin.facilities.archive', ['id' => $facility->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="item text-warning archive">
                        <i class="icon-archive"></i>
                    </div>
                </form>
            </div>
        </td>
    </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No facilities found.</td>
        </tr>
    @endforelse

    <style>
        .facility-cell {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 250px;
        }

        .facility-img {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .facility-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
        }

        .badge-info {
            background-color: #17a2b8;
            color: #fff;
            margin-bottom: 2px;
            display: inline-block;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
            margin-left: 4px;
            cursor: pointer;
            position: relative;
        }

        .tooltip {
            display: none;
            position: absolute;
            left: 0;
            top: 120%;
            background-color: #333;
            color: #fff;
            padding: 8px;
            border-radius: 4px;
            z-index: 1000;
            font-size: 12px;
            min-width: 120px;
            max-width: 250px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            white-space: normal;
        }

        .badge-secondary:hover .price-tooltip,
        .badge-secondary:focus .price-tooltip,
        .badge-secondary:hover .room-tooltip,
        .badge-secondary:focus .room-tooltip {
            display: block;
        }

        .facility-name:hover+.facility-tooltip,
        .facility-name:focus+.facility-tooltip {
            display: block;
        }
    </style>
