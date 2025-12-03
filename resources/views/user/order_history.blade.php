@extends('layouts.app')

@section('content')
    <style>
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

        .accordion-item {
            background-color: #fff;
            border-radius: 5px;
        }

        .accordion-button {
            background-color: #f8f9fa;
        }

        .accordion-body {
            padding: 1rem;
        }

        .order-items .order-item {
            padding: 0.5rem 0;
        }

        .order-items .order-item .text-muted {
            font-size: 0.9rem;
        }

        .btn-outline-primary {
            font-size: 0.875rem;
        }

        .bg-success {
            background-color: #40c710 !important;
        }

        .bg-danger {
            background-color: #f44032 !important;
        }

        .bg-warning {
            background-color: #f5d700 !important;
        }

        .alert {
            margin-bottom: 1rem;
        }

        /* Empty State Styles */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .btn-custom {
            padding: 12px 24px;
            font-size: 1rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container py-4">
        <section class="my-account">
            <div class="mb-4">
                <h2 class="h4 fw-bold text-dark mb-1">My Order History</h2>
                <p class="text-muted small">Track your past reservations</p>
            </div>


            <div class="row g-4">
                <div class="col-lg-3">
                    @include('user.account__nav')
                </div>
                <div class="col-lg-9">
                    @forelse ($orders as $order)
                        @php
                            $expiryDate = $order->created_at->copy()->addDays(30);
                            $daysRemaining = now()->diffInDays($expiryDate, false);
                            $weeksRemaining = ceil($daysRemaining / 7);
                            $showNotice = $daysRemaining >= 0 && $daysRemaining <= 30;
                            $noticeClass = '';
                            $noticeMessage = '';

                            if ($daysRemaining < 0) {
                                $showNotice = false;
                            } elseif ($daysRemaining <= 3) {
                                $noticeClass = 'alert-danger';
                                $noticeMessage =
                                    'Order #{{ $order->id }} will be removed from your history in ' .
                                    ($daysRemaining == 0
                                        ? 'less than 24 hours'
                                        : $daysRemaining . ' day' . ($daysRemaining == 1 ? '' : 's'));
                            } elseif ($daysRemaining <= 7) {
                                $noticeClass = 'alert-warning';
                                $noticeMessage =
                                    'Order #{{ $order->id }} will be removed from your history in ' .
                                    ($daysRemaining == 7 ? '1 week' : $daysRemaining . ' days');
                            } elseif ($daysRemaining <= 14) {
                                $noticeClass = 'alert-info';
                                $noticeMessage =
                                    'Order #{{ $order->id }} will be automatically archived in ' .
                                    ($weeksRemaining == 2
                                        ? '2 weeks'
                                        : ($weeksRemaining == 1
                                            ? '1 week'
                                            : $daysRemaining . ' days'));
                            } elseif ($daysRemaining <= 21) {
                                $noticeClass = 'alert-light';
                                $noticeMessage =
                                    'Order #{{ $order->id }} will be archived in approximately ' .
                                    $weeksRemaining .
                                    ' week' .
                                    ($weeksRemaining == 1 ? '' : 's');
                            } else {
                                $showNotice = false;
                            }
                        @endphp

                        @if ($showNotice)
                            <div class="alert {{ $noticeClass }} alert-dismissible fade show" role="alert">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Notice:</strong> {{ $noticeMessage }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom-0">
                                <div class="d-flex justify-content-end align-items-center w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($order->status === 'pickedup')
                                            <span class="badge bg-success">Picked Up</span>
                                        @elseif ($order->status === 'canceled')
                                            <span class="badge bg-danger">Canceled</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 mb-4">
                                    <!-- Customer Info -->
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold text-muted mb-3">Customer</h6>
                                        <p class="mb-1"><span class="fw-medium">{{ $order->user->name }}</span></p>
                                        <p class="text-muted small mb-0">📞 {{ $order->user->phone_number }}</p>
                                    </div>

                                    <!-- Order Summary -->
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold text-muted mb-3">Summary</h6>
                                        <p class="mb-1">
                                            <span class="text-muted small">Total:</span>
                                            <span
                                                class="fw-bold text-primary">₱{{ number_format($order->total, 2) }}</span>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            {{ $order->orderItems->count() }} item(s)
                                        </p>
                                    </div>
                                </div>

                                <!-- Order Items Accordion -->
                                <div class="accordion mb-4" id="orderAccordion{{ $order->id }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $order->id }}">
                                            <button class="accordion-button collapsed fw-medium" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $order->id }}">
                                                View Items ({{ $order->orderItems->count() }})
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $order->id }}" class="accordion-collapse collapse"
                                            data-bs-parent="#orderAccordion{{ $order->id }}">
                                            <div class="accordion-body p-0">
                                                @foreach ($order->orderItems as $item)
                                                    <div
                                                        class="d-flex justify-content-between align-items-start p-3 @if (!$loop->last) border-bottom @endif">
                                                        <div class="d-flex">
                                                            <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                                                alt="{{ $item->product->name }}"
                                                                class="rounded border me-3"
                                                                style="width: 50px; height: 50px; object-fit: cover;">
                                                            <div>
                                                                <h6 class="fw-medium mb-1">{{ $item->product->name }}</h6>
                                                                <small class="text-muted d-block">Qty:
                                                                    {{ $item->quantity }}</small>
                                                                @if ($item->options)
                                                                    @php $options = json_decode($item->options, true); @endphp
                                                                    @foreach ($options['variant_attributes'] ?? [] as $attr => $val)
                                                                        <small
                                                                            class="text-muted d-block">{{ $attr }}:
                                                                            {{ $val }}</small>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <span
                                                                class="fw-bold">₱{{ number_format($item->price * $item->quantity, 2) }}</span>
                                                            <small
                                                                class="d-block text-muted">₱{{ number_format($item->price, 2) }}
                                                                each</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light p-2 rounded mb-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Order placed {{ $order->created_at->diffForHumans() }}
                                    </small>
                                    <small class="text-muted">
                                        @if ($daysRemaining > 0)
                                            Expires: {{ $expiryDate->format('M d, Y') }}
                                        @else
                                            <span class="text-danger">Expired</span>
                                        @endif
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Reservation:
                                        <span class="{{ $order->reservation_date ? 'text-success' : 'text-muted' }}">
                                            {{ $order->reservation_date ? \Carbon\Carbon::parse($order->reservation_date)->format('F d, Y') : 'Not scheduled' }}
                                        </span>
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('user.order.details', $order->id) }}"
                                            class="btn btn-outline-primary btn">
                                            <i class="bi bi-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Empty State -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-bag display-1 text-muted mb-3"></i>
                                <h5 class="fw-bold text-dark">No Orders Yet</h5>
                                <p class="text-muted">You haven’t placed any reservations yet.</p>
                                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                                    <i class="bi bi-shop me-2"></i> Browse Products
                                </a>
                            </div>
                        </div>
                    @endforelse

                    @if ($orders->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection

<style>
    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    .accordion-item {
        transition: all 0.3s ease;
    }

    .accordion-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .order-item:last-child {
        border-bottom: none !important;
    }
</style>
