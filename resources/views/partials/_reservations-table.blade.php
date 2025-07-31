@forelse ($reservations as $reservation)
    <tr>
        <td>
            <div class="name-cell">
                <span class="name-text">{{ $reservation->availability->facility->name }}</span>
                <small class="text-muted">Type:
                    {{ ucfirst($reservation->availability->facility->facility_type) }}</small>
            </div>
        </td>
        <td class="text-center reservation-date" data-time-slot="{{ $reservation->availability->time_slot }}">
            {{ \Carbon\Carbon::parse($reservation->availability->date_from)->format('M d, Y') }} -
            {{ \Carbon\Carbon::parse($reservation->availability->date_to)->format('M d, Y') }}
        </td>
        <td class="text-center">{{ $reservation->user->name }}</td>
        <td class="text-center">&#8369;{{ number_format($reservation->total_price, 2) }}</td>
        <td class="text-center">
            <div class="status-badge-container">
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
            </div>
        </td>
        <td class="text-center">
            <div class="action-buttons">
                <a href="{{ route('admin.facilities.reservations.show', $reservation->id) }}">
                    <i class="icon-eye" title="View Details"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">No reservations found</td>
    </tr>
@endforelse
