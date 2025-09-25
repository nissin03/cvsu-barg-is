@forelse ($reservations as $reservation)
    <tr class="reservation-row" data-href="{{ route('admin.facilities.reservations.show', $reservation->id) }}"
        style="cursor: pointer;">
        <td class="text-start">
            <div class="name-cell">
                {{ $reservation->availability->facility->name }}
                <div class="status-badge-container">
                    <span>
                        <span
                            class="badge status-badge
                        @if ($reservation->status === 'completed') bg-success
                        @elseif ($reservation->status === 'pending') bg-warning
                        @elseif ($reservation->status === 'canceled') bg-danger
                        @elseif ($reservation->status === 'reserved') bg-primary
                        @else bg-secondary @endif">
                            {{ ucfirst($reservation->status) }}
                        </span>
                        @if ($reservation->updated_by)
                            <small class="d-block text-muted mt-1">
                                Updated by: {{ $reservation->updatedBy->name }}
                            </small>
                        @endif
                    </span>
                </div>
            </div>

        </td>
        <td class="text-center reservation-date" data-time-slot="{{ $reservation->availability->time_slot }}">
            @if (!empty($reservation->grouped_dates))
                <div class="date-ranges">
                    @foreach ($reservation->grouped_dates as $range)
                        @php
                            $startDate = \Carbon\Carbon::parse($range['start']);
                            $endDate = \Carbon\Carbon::parse($range['end']);
                        @endphp

                        <div class="date-range mb-1">
                            @if ($startDate->equalTo($endDate))
                                {{ $startDate->format('M j, Y') }}
                            @else
                                @if ($startDate->format('M Y') === $endDate->format('M Y'))
                                    {{ $startDate->format('M j') }} - {{ $endDate->format('j, Y') }}
                                @elseif($startDate->format('Y') === $endDate->format('Y'))
                                    {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                                @else
                                    {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                --
            @endif
        </td>
        <td class="text-center">{{ $reservation->user->name }}</td>
        <td class="text-center">&#8369;{{ number_format($reservation->total_price, 2) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">No reservations found</td>
    </tr>
@endforelse
