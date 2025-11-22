@extends('layouts.app')

@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="Canceled Orders" :breadcrumbs="$breadcrumbs" />

    <main class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>

        <section class="my-account container">
            <h2 class="page-title">Canceled Orders</h2>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <!-- Content -->
                <div class="col-lg-10">
                    @if ($canceledOrders->count() > 0)
                        <!-- Information Message -->
                        <div class="alert alert-warning mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h5 class="alert-heading mb-2">Unpaid Reservations Voided</h5>
                                    <p class="mb-2">
                                        Your reserved items were automatically canceled because payment was not completed
                                        within 24 hours of placing the order.
                                    </p>
                                    <p class="mb-0">
                                        <strong>Note:</strong> You can re-book the same items within 24 hours of the
                                        original order placement by clicking the "Re-book Reservation" button below.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @forelse ($canceledOrders as $order)
                        <div class="accordion mt-4" id="canceledOrderAccordion">
                            <div class="accordion-item mb-3 shadow-sm border rounded">
                                <h2 class="accordion-header" id="headingCanceled{{ $order->id }}">
                                    <button class="accordion-button d-flex justify-content-between align-items-center"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseCanceled{{ $order->id }}" aria-expanded="true"
                                        aria-controls="collapseCanceled{{ $order->id }}">
                                        <span>
                                            <strong>Order #{{ $order->id }}</strong>
                                            <small class="text-muted">
                                                Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                                            </small>
                                        </span>
                                        <span>
                                            <span class="badge bg-danger">Canceled</span>
                                        </span>
                                    </button>
                                </h2>

                                <div id="collapseCanceled{{ $order->id }}"
                                    class="accordion-collapse collapse @if ($loop->first) show @endif"
                                    aria-labelledby="headingCanceled{{ $order->id }}"
                                    data-bs-parent="#canceledOrderAccordion">
                                    <div class="accordion-body">
                                        <!-- Order Info -->
                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <p><strong>Name:</strong> {{ $order->name }}</p>
                                                <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
                                            </div>
                                            <div>
                                                <p><strong>Total:</strong> ₱{{ number_format($order->total, 2) }}</p>
                                                <p><strong>Items:</strong> {{ $order->orderItems->count() }}</p>
                                            </div>
                                        </div>

                                        <!-- Items -->
                                        <div class="order-items">
                                            @foreach ($order->orderItems as $item)
                                                <div class="order-item d-flex justify-content-between py-2 border-bottom">
                                                    <div>
                                                        <strong>{{ $item->product->name }}</strong>
                                                        <p class="text-muted">Qty: {{ $item->quantity }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted">₱{{ number_format($item->price, 2) }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Footer: Re-book Action -->
                                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">
                                                    <strong>Canceled At:</strong>
                                                    {{ $order->canceled_date->format('M d, Y h:i A') }}
                                                </p>
                                                @php
                                                    $hoursSinceCreated = $order->created_at->diffInHours(now());
                                                    $canRebook = $hoursSinceCreated <= 24;
                                                @endphp
                                                @if ($canRebook)
                                                    <small class="text-success">
                                                        <i class="fas fa-clock"></i>
                                                        Re-booking available for {{ 24 - $hoursSinceCreated }} more hours
                                                    </small>
                                                @else
                                                    <small class="text-danger">
                                                        <i class="fas fa-times-circle"></i>
                                                        Re-booking no longer available (expired)
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="text-center">
                                                @if ($canRebook)
                                                    <form action="{{ route('user.order.rebook', $order->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-lg">
                                                            <i class="fas fa-redo me-2"></i>
                                                            Re-book Reservation
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                                                        <i class="fas fa-clock me-2"></i>
                                                        Re-booking Expired
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="wg-box mt-5">
                            <div class="empty-state text-center">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                                <h4>No Canceled Orders</h4>
                                <p class="text-muted">Great! You don't have any unpaid orders that were automatically
                                    canceled.</p>
                                <p class="text-muted">All your reservations have been properly completed or are still
                                    active.</p>
                                <a href="{{ route('shop.index') }}" class="btn btn-custom btn-outline-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Browse Products
                                </a>
                            </div>
                        </div>
                    @endforelse

                    <div class="divider"></div>

                    <!-- Pagination -->
                    @if ($canceledOrders->hasPages())
                        <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                            {{ $canceledOrders->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection
