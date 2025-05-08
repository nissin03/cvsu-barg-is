@if ($orders->count() > 0)
    @foreach ($orders as $order)
        <tr>
            <td class="text-start">
                <div class="name">
                    {{ $order->name }}
                    <div class="status-badge-container">
                        <span
                            class="badge status-badge
                        {{ $order->status == 'pickedup' ? 'bg-success' : ($order->status == 'canceled' ? 'bg-danger' : 'bg-warning') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </td>

            <!-- Reservation Date with Tooltip -->
            <td class="text-center reservation-date" data-time-slot="{{ $order->time_slot }}">
                <span class="reservation-date">
                    {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}
                </span>
            </td>

            <td class="text-center">
                {{ $order->orderItems->count() }}
                {{ $order->orderItems->count() == 1 ? 'item' : 'items' }}
            </td>
            <td class="text-center">&#8369;{{ $order->total }}</td>

            <td>{{ $order->created_at->format('M d, Y') }}</td>
            <td class="text-center">{{ $order->picked_up_date }}</td>
            <td class="text-center">
                <a href="{{ route('admin.order.details', ['order_id' => $order->id]) }}">
                    <i class="icon-eye" title="View Details"></i>
                </a>
            </td>
        </tr>
    @endforeach
@else
    <tr id="no-results-message">
        <td colspan="7" class="text-center p-3">
            <div class="alert alert-info">No orders found matching your filters. Try different criteria.</div>
        </td>
    </tr>
@endif
