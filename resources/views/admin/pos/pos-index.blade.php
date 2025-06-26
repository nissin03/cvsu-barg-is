@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <h3>Point of Sale (POS)</h3>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('admin.pos') }}" class="mb-3">
                <input type="text" name="order-user-search" placeholder="Search user..." value="{{ $search }}"
                    class="form-control" />
            </form>

            <div class="tf-section-2-2" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">

                {{-- LEFT PANEL: Order List --}}
                <div class="wg-box overflow-auto" style="max-height: 85vh;">
                    <h5>Users with Orders</h5>
                    @forelse ($orders as $order)
                        <a href="{{ route('admin.pos', $order->id) }}" class="text-decoration-none">
                            <div
                                class="border p-3 mb-2 {{ $selectedOrder && $order->id === $selectedOrder->id ? 'bg-primary text-white' : 'bg-light' }}">
                                <strong>{{ $order->name }}</strong>
                                <div>{{ $order->reservation_date }} | {{ $order->time_slot }}</div>
                                <div>Ordered on: {{ $order->created_at->format('M d, Y') }}</div>
                                <div>Status:
                                    <span
                                        class="badge bg-{{ $order->status == 'pickedup' ? 'success' : ($order->status == 'canceled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>

                                {{-- Show latest transaction (optional) --}}
                                @if ($order->transaction)
                                    <div class="mt-1 text-sm">
                                        Txn: ₱{{ number_format($order->transaction->amount ?? 0, 2) }}
                                        ({{ ucfirst($order->transaction->status ?? 'N/A') }})
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p>No users with orders found.</p>
                    @endforelse
                </div>

                {{-- RIGHT PANEL: POS Summary --}}
                <div class="wg-box">
                    @if ($selectedOrder)
                        <h5>POS: {{ $selectedOrder->name }}</h5>

                        @foreach ($orderItems as $item)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                    alt="Product Image" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <strong>{{ $item->product->name }}</strong>
                                    <div>Qty: {{ $item->quantity }}</div>
                                    <div>Subtotal: ₱{{ number_format($item->price * $item->quantity, 2) }}</div>
                                </div>
                            </div>
                        @endforeach

                        <hr>
                        <p><strong>Total:</strong> ₱{{ number_format($selectedOrder->total, 2) }}</p>

                        <form method="POST" action="#">
                            @csrf
                            <div class="mb-3">
                                <label for="amount_paid">Amount Paid</label>
                                <input type="number" name="amount_paid" id="amount_paid" class="form-control"
                                    step="0.01" required oninput="calculateChange()">
                            </div>
                            <p><strong>Change:</strong> ₱<span id="change_display">0.00</span></p>
                            <button type="submit" class="btn btn-success">Complete Payment</button>
                        </form>

                        <script>
                            function calculateChange() {
                                const total = {{ $selectedOrder->total }};
                                const paid = parseFloat(document.getElementById('amount_paid').value) || 0;
                                const change = paid - total;
                                document.getElementById('change_display').innerText = change.toFixed(2);
                            }
                        </script>
                    @else
                        <p>Please search and select a user to view POS.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
