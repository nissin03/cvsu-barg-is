@if ($orders->count() > 0)
    @foreach ($orders as $order)
        <tr class="order-row" data-href="{{ route('admin.order.details', ['order_id' => $order->id]) }}"
            style="cursor: pointer;">

            <td class="text-start">
                <div class="name">
                    {{ $order->user->name }}
                    @if ($order->transaction && $order->transaction->status === 'paid')
                        <span class="badge bg-success ms-2">Paid</span>
                    @else
                        <span class="badge bg-secondary ms-2">Unpaid</span>
                    @endif
                    <div class="status-badge-container">
                        <span
                            class="badge status-badge
                        {{ $order->status == 'pickedup' ? 'bg-success' : ($order->status == 'canceled' ? 'bg-danger' : 'bg-warning') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </td>

            <td class="text-center reservation-date" data-time-slot="{{ $order->time_slot }}">
                <div>
                    {{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}
                </div>

                <div class="mt-1">
                    @if ($order->time_slot)
                        <span class="badge bg-secondary text-white">
                            {{ $order->time_slot }}
                        </span>
                    @else
                        <span class="badge bg-light text-muted">
                            No time slot
                        </span>
                    @endif
                </div>
            </td>


            <td class="text-center">
                {{ $order->orderItems->count() }}
                {{ $order->orderItems->count() == 1 ? 'item' : 'items' }}
            </td>
            <td class="text-center">&#8369;{{ $order->total }}</td>

            <td>{{ $order->created_at->format('M d, Y') }}</td>
        </tr>
    @endforeach
@else
    <tr id="no-results-message">
        <td colspan="6" class="text-center p-3">
            <div class="alert alert-info">No orders found matching your filters. Try different criteria.</div>
        </td>
    </tr>
@endif
