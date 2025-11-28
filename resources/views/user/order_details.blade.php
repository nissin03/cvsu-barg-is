@extends('layouts.app')

@section('content')
    <style>
        /* General Styles */
        .pt-90 {
            padding-top: 90px !important;
        }

        .my-account .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 13px;
        }

        .my-account .wg-box {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Order Summary Styles */
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .order-summary .left-section,
        .order-summary .right-section {
            width: 48%;
        }

        .order-summary .order-status {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .order-summary .order-info {
            font-size: 0.9rem;
            color: #555;
        }

        /* Table Styles */
        .table-custom {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            border: 1px solid #eaeaea;
            text-align: left;
            vertical-align: middle;
        }

        .table-custom th {
            background-color: #f5f5f5;
            font-weight: 600;
        }

        .product-info {
            display: flex;
            align-items: center;
        }

        .product-info img {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
        }

        .product-details {
            font-size: 0.9rem;
            color: #333;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            color: #fff;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        /* Buttons */
        .btn-custom {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }

        .btn-outline-primary {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .btn-success {
            background-color: #28a745;
            color: #fff;
            border: none;
        }

        .text-end {
            text-align: right;
        }

        /* Rebook Box Styles */
        .rebook-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .rebook-box .rebook-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .rebook-box .rebook-header i {
            font-size: 2rem;
            color: #ffc107;
            margin-right: 15px;
        }

        .rebook-box .rebook-info {
            flex: 1;
        }

        .rebook-box .rebook-timer {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #ffc107;
        }

        .rebook-box .rebook-timer i {
            color: #28a745;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .rebook-box .rebook-timer.expired i {
            color: #dc3545;
        }

        .rebook-box .rebook-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .order-summary .left-section,
            .order-summary .right-section {
                width: 100%;
                margin-bottom: 20px;
            }

            .rebook-box .rebook-header {
                flex-direction: column;
                text-align: center;
            }

            .rebook-box .rebook-actions {
                justify-content: center;
            }
        }
    </style>

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>

        <section class="my-account container">
            <h2 class="page-title">Order Details</h2>

            <div class="row">
                <!-- Left Side: Account Navigation -->
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <!-- Right Side: Order Details Content -->
                <div class="col-lg-10">
                    <!-- Order Summary Box -->
                    <div class="wg-box">
                        @if ($order->status === 'canceled' && $order->canceled_reason)
                            <div class="alert alert-danger mt-3" role="alert">
                                <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Order Canceled
                                </h6>
                                <p class="mb-1"><strong>Reason:</strong> {{ $order->canceled_reason }}</p>
                                @if ($order->canceled_date)
                                    <small class="text-muted">Canceled on:
                                        {{ \Carbon\Carbon::parse($order->canceled_date)->format('M d, Y h:i A') }}
                                    </small>
                                @endif
                                @if ($order->updated_by)
                                    <br>
                                    <small class="text-muted">Updated by:
                                        {{ $order->updatedBy->name ?? 'System' }}</small>
                                @endif
                            </div>
                        @endif

                        <div class="order-summary">
                            <!-- Left Section -->
                            <div class="left-section">
                                <div class="order-status">
                                    <p><strong>Status:</strong>
                                        @if ($order->status == 'pickedup')
                                            <span class="badge badge-success">Picked Up</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge badge-danger">Canceled</span>
                                        @else
                                            <span class="badge badge-warning">Reserved</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="order-info">
                                    <p><strong>Order Date:</strong>
                                        <span class="badge bg-primary">{{ $order->created_at->format('M d, Y H:i') }}</span>
                                    </p>

                                    <p><strong>Reservation Date:</strong>
                                        @if ($order->reservation_date)
                                            <span
                                                class="badge bg-success">{{ \Carbon\Carbon::parse($order->reservation_date)->format('M d, Y') }}</span>
                                        @else
                                            <span class="badge bg-secondary">Not yet reserved</span>
                                        @endif
                                    </p>

                                    <p><strong>Picked Up Date:</strong>
                                        @if ($order->picked_up_date)
                                            <span
                                                class="badge bg-success">{{ \Carbon\Carbon::parse($order->picked_up_date)->format('M d, Y') }}</span>
                                        @else
                                            <span class="badge bg-secondary">Not yet picked up</span>
                                        @endif
                                    </p>

                                    <p><strong>Canceled Date:</strong>
                                        @if ($order->canceled_date)
                                            <span
                                                class="badge bg-danger">{{ \Carbon\Carbon::parse($order->canceled_date)->format('M d, Y') }}</span>
                                        @else
                                            <span class="badge bg-secondary">Not canceled</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Right Section -->
                            <div class="right-section">
                                <div class="order-info">
                                    <p><strong>Name:</strong> {{ $order->user->name ?? '—' }}</p>
                                    <p><strong>Phone:</strong> {{ $order->user->phone_number ?? '—' }}</p>

                                    @if ($order->user->role === 'student')
                                        <p><strong>Year Level:</strong> {{ $order->user->year_level ?? '—' }}</p>
                                        <p><strong>College:</strong> {{ $order->user->college->name ?? '—' }}</p>
                                        <p><strong>Course:</strong> {{ $order->user->course->name ?? '—' }}</p>
                                    @endif
                                    <p><strong>Time Slot:</strong> {{ $order->time_slot ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- @if ($order->status === 'canceled' && $transaction->status === 'unpaid' && is_null($order->updated_by))
                        <!-- Rebook Box (Only show for canceled orders) -->
                        @if ($order->status === 'canceled' && $transaction->status === 'unpaid')
                            @php
                                $hoursSinceCreated = $order->created_at->diffInHours(now());
                                $canRebook = $hoursSinceCreated <= 24;
                                $hoursRemaining = max(0, 24 - $hoursSinceCreated);
                            @endphp

                            <div class="rebook-box">
                                <div class="rebook-header">
                                    <i class="fas fa-redo-alt"></i>
                                    <div class="rebook-info">
                                        <h5 class="mb-2">Re-book This Order</h5>
                                        <p class="mb-0 text-muted">
                                            Your order was automatically canceled because payment was not completed within
                                            24
                                            hours.
                                            You can re-book the same items if the order was placed less than 24 hours ago.
                                        </p>
                                    </div>
                                </div>

                                @if ($canRebook)
                                    <div class="rebook-timer">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <strong class="text-success">Re-booking Available</strong>
                                            <p class="mb-0 text-muted small">
                                                You have <strong>{{ $hoursRemaining }} hours</strong> remaining to re-book
                                                this
                                                order
                                            </p>
                                        </div>
                                    </div>

                                    <div class="rebook-actions">
                                        <form action="{{ route('user.order.rebook', $order->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-lg">
                                                <i class="fas fa-redo me-2"></i>
                                                Re-book This Order
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="rebook-timer expired">
                                        <i class="fas fa-times-circle"></i>
                                        <div>
                                            <strong class="text-danger">Re-booking No Longer Available</strong>
                                            <p class="mb-0 text-muted small">
                                                This order was placed more than 24 hours ago and can no longer be re-booked.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif --}}

                    @if ($order->status === 'canceled' && $transaction->status === 'unpaid' && is_null($order->updated_by))
                        @php
                            $hoursSinceCreated = $order->created_at->diffInHours(now());
                            $canRebook = $hoursSinceCreated <= 24;
                            $hoursRemaining = max(0, 24 - $hoursSinceCreated);
                        @endphp

                        <div class="rebook-box">
                            <div class="rebook-header">
                                <i class="fas fa-redo-alt"></i>
                                <div class="rebook-info">
                                    <h5 class="mb-2">Re-book This Order</h5>

                                    @if ($canRebook)
                                        <p class="mb-0 text-muted">
                                            Your order was automatically canceled because payment was not completed within
                                            24 hours.
                                            You may re-book this order while the 24-hour window is still open.
                                        </p>
                                    @else
                                        <p class="mb-0 text-muted">
                                            Your order was automatically canceled because payment was not completed within
                                            24 hours.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if ($canRebook)
                                <!-- Rebook Still Available -->
                                <div class="rebook-timer">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <strong class="text-success">Re-booking Available</strong>
                                        <p class="mb-0 text-muted small">
                                            You have <strong>{{ $hoursRemaining }} hour(s)</strong> remaining to re-book
                                            this order.
                                        </p>
                                    </div>
                                </div>

                                <div class="rebook-actions">
                                    <form action="{{ route('user.order.rebook', $order->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-lg">
                                            <i class="fas fa-redo me-2"></i>
                                            Re-book This Order
                                        </button>
                                    </form>
                                </div>
                            @else
                                <!-- Rebook Not Available -->
                                <div class="rebook-timer expired">
                                    <i class="fas fa-times-circle"></i>
                                    <div>
                                        <strong class="text-danger">Re-booking No Longer Available</strong>
                                        <p class="mb-0 text-muted small">
                                            This order was placed more than 24 hours ago and can no longer be re-booked.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Reserved Items Box -->
                    <div class="wg-box">
                        <h5>Reserved Items</h5>
                        @if ($orderItems->count() > 0)
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Variations</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orderItems as $item)
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                                        alt="{{ $item->product->name }}">
                                                    <div class="product-details">
                                                        <p class="py-2">{{ $item->product->name }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($item->options)
                                                    @php $options = json_decode($item->options, true); @endphp
                                                    @if (isset($options['variant_attributes']) && !empty($options['variant_attributes']))
                                                        @foreach ($options['variant_attributes'] as $attributeName => $value)
                                                            <p>{{ $attributeName }}: {{ $value }}</p>
                                                        @endforeach
                                                    @else
                                                        <p>No variations</p>
                                                    @endif
                                                @else
                                                    <p>No variations</p>
                                                @endif
                                            </td>
                                            <td>₱ {{ number_format($item->price, 2) }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₱ {{ number_format($item->price * $item->quantity, 2) }}</td>
                                            <td>
                                                <a href="{{ route('shop.product.details', ['product_slug' => $item->product->slug]) }}"
                                                    class="btn btn-custom btn-outline-primary" target="_blank">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div>
                                {{ $orderItems->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <h5 class="text-muted">No Items Found</h5>
                                <p class="text-muted">This order doesn't contain any items.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Transactions Box -->
                    <div class="wg-box">
                        <h5>Transactions</h5>
                        <table class="table-custom">
                            <tr>
                                <th>Total</th>
                                <td>₱ {{ number_format($order->total, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($transaction->status == 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Back to Orders Button -->
                    <div class="text-end">
                        <a class="btn btn-custom btn-success" href="{{ route('user.orders') }}">Back to Orders</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
