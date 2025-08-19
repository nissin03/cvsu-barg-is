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
            @php
                $groupedDates = [];
        
                if ($reservation->grouped_availabilities && $reservation->grouped_availabilities->isNotEmpty()) {
                    $sortedAvailabilities = $reservation->grouped_availabilities->sortBy('date_from');
                    $currentGroup = [];
        
                    foreach ($sortedAvailabilities as $avail) {
                        if (empty($currentGroup)) {
                            $currentGroup = [
                                'start' => $avail->date_from,
                                'end' => $avail->date_to
                            ];
                        } elseif (\Carbon\Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                            $currentGroup['end'] = $avail->date_to;
                        } else {
                            $groupedDates[] = $currentGroup;
                            $currentGroup = [
                                'start' => $avail->date_from,
                                'end' => $avail->date_to
                            ];
                        }
                    }
        
                    if (!empty($currentGroup)) {
                        $groupedDates[] = $currentGroup;
                    }
                } else {
                    // fallback: single availability
                    $groupedDates[] = [
                        'start' => $reservation->availability->date_from,
                        'end' => $reservation->availability->date_to
                    ];
                }
            @endphp
        
            @if(!empty($groupedDates))
                <div class="date-ranges">
                    @foreach($groupedDates as $range)
                        @php
                            $startDate = \Carbon\Carbon::parse($range['start']);
                            $endDate = \Carbon\Carbon::parse($range['end']);
                        @endphp
        
                        <div class="date-range" style="margin-bottom: 2px;">
                            @if ($startDate->equalTo($endDate))
                                {{ $startDate->format('M j, Y') }}
                            @else
                                @if($startDate->format('M Y') === $endDate->format('M Y'))
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