@extends('layouts.app')

@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container py-4">
        <section class="my-account">
            <div class="mb-4">
                <h2 class="h4 fw-bold text-dark mb-1">My Orders</h2>
                <p class="text-muted small">Track your active reservations</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3">
                    @include('user.account__nav')
                </div>
                <div class="col-lg-9">
                    @forelse ($orders as $order)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom-0">
                                <div class="d-flex justify-content-end align-items-center w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark">Reserved</span>
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
                                            <span class="fw-bold text-primary">₱{{ number_format($order->total, 2) }}</span>
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
