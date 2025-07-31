<div class="container">
    <div class="header">
        @php
            $isPdf = request()->routeIs('admin.order-receipt.pdf');
        @endphp
        <table class="header-table">
            <tr>
                <td class="logo-left">
                    <img src="{{ $isPdf ? public_path('images/logo/cvsu-logo.png') : asset('images/logo/cvsu-logo.png') }}"
                        alt="CVSU Logo" />
                </td>
                <td class="center-cell">
                    <div class="university-name">
                        Republic of the Philippines<br />
                        CAVITE STATE UNIVERSITY<br />
                        Don Severino delas Alas Campus
                    </div>
                    <div class="subtext">
                        Indang, Cavite<br />
                        <a href="http://www.cvsu.edu.ph" target="_blank">www.cvsu.edu.ph</a>
                    </div>
                </td>
                <td class="logo-right">
                    <img src="{{ $isPdf ? public_path('images/logo/BaRG-logo.png') : asset('images/logo/BaRG-logo.png') }}"
                        alt="CVSU Logo" />
                </td>
            </tr>
        </table>
    </div>

    <div class="receipt-container">
        <div class="receipt-info">
            <div>
                <span class="label">Order ID:</span>
                <span>{{ $order->id }}</span>
            </div>
            <div>
                <span class="label">Date:</span>
                <span>{{ $order->created_at->format('F d, Y - h:i A') }}</span>
            </div>
            <div>
                <span class="label">Customer:</span>
                <span>{{ $order->user->name }}</span>
            </div>
            <div>
                <span class="label">Email:</span>
                <span>{{ $order->user->email }}</span>
            </div>
        </div>

        <div class="items-section">
            <div class="item-header">
                <span>Item</span>
                <span>Quantity × Price</span>
            </div>
            @foreach ($orderItems as $item)
                <div class="item">
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} × {{ $item->product->price }}</span>
                        <span>{{ $item->total_price }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="totals-section">
            <div class="total-line grand-total">
                <span>TOTAL</span>
                <span>₱{{ $order->total }}</span>
            </div>
        </div>

        <div class="payment-section">
            <div class="total-line">
                <span>Amount Paid:</span>
                <span>₱{{ number_format($transaction->amount_paid,2) }}</span>
            </div>
            <div class="total-line">
                <span>Change:</span>
                <span>₱{{ number_format($transaction->change, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <div class="thank-you">THANK YOU FOR YOUR PURCHASE!</div>
            <div>Keep this receipt for your records</div>
            <div class="generated-time">
                Generated: {{ $order->created_at->format('F d, Y - h:i A') }}
            </div>
        </div>
    </div>
</div>
