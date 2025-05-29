@if ($reservations->count() > 0)
    @foreach ($reservations as $reservation)
        @php
            $facility = $reservation->availability->facility ?? null;
        @endphp
        <tr>
            <td class="text-start">
                <div class="name">
                    {{ $facility->name ?? '—' }}
                    <span
                        class="badge status-badge
                        @if ($facility?->facility_type === 'individual') bg-individual-green
                        @elseif ($facility?->facility_type === 'whole_place')
                           bg-whole-orange
                        @elseif ($facility?->facility_type === 'both')
                            bg-warning
                        @else
                            bg-secondary @endif">
                        {{ ucfirst($facility->facility_type ?? '—') }}
                    </span>

                    <div class="status-badge-container">
                        <span
                            class="badge status-badge
                                    @if ($reservation->status === 'completed') bg-success
                                    @elseif ($reservation->status === 'pending')
                                        bg-primary
                                    @elseif ($reservation->status === 'canceled')
                                        bg-danger
                                    @elseif ($reservation->status === 'reserved')
                                        bg-warning
                                    @else
                                        bg-secondary @endif">
                            {{ ucfirst($reservation->status) }}
                        </span>

                    </div>
                </div>
            </td>
            <td class="text-center reservation-date">
                <span>
                    {{ \Carbon\Carbon::parse($reservation->availability->date_from)->format('M d, Y') }}
                    -
                    {{ \Carbon\Carbon::parse($reservation->availability->date_to)->format('M d, Y') }}
                </span>
            </td>
            <!-- User -->
            <td class="text-center">
                {{ $reservation->user->name ?? '—' }}
            </td>
            <td class="text-center">
                &#8369;{{ number_format($reservation->total_price, 2) }}
            </td>
            {{-- <td class="text-center">
                <div class="action-buttons">
                    <select class="filter-select status-select" data-reservation-id="{{ $reservation->id }}">
                        <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="reserved" {{ $reservation->status === 'reserved' ? 'selected' : '' }}>Reserved
                        </option>
                        <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>
                            Completed</option>
                        <option value="canceled" {{ $reservation->status === 'canceled' ? 'selected' : '' }}>Canceled
                        </option>
                    </select>
                </div>
            </td> --}}

            <td class="text-center">
                <div class="action-buttons">
                    <select class="filter-select status-select" data-reservation-id="{{ $reservation->id }}"
                        @disabled(in_array($reservation->status, ['completed', 'canceled']))>
                        <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="reserved" {{ $reservation->status === 'reserved' ? 'selected' : '' }}>Reserved
                        </option>
                        <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>
                            Completed</option>
                        <option value="canceled" {{ $reservation->status === 'canceled' ? 'selected' : '' }}>Canceled
                        </option>
                    </select>
                </div>
            </td>

            <!-- View Action -->
            <td class="text-center">
                <a href="#">
                    <i class="icon-eye" title="View Details"></i>
                </a>
            </td>
        </tr>
    @endforeach
@else
    <tr id="no-results-message">
        <td colspan="8" class="text-center p-3">
            <div class="alert alert-info">No reservations found matching your filters. Try different criteria.</div>
        </td>
    </tr>
@endif
