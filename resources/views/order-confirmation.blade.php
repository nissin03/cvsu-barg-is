@extends('layouts.app')

@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5 d-flex justify-content-center">
        <section class="shop-checkout container col-md-8 col-lg-6">
            <h2 class="page-title text-center mb-4">Reservation Confirmed</h2>
            <div class="order-complete text-center">
                <div class="order-complete__message mb-4">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="40" fill="#B9A16B" />
                        <path
                            d="M52.9743 35.7612C52.9743 35.3426 52.8069 34.9241 52.5056 34.6228L50.2288 32.346C49.9275 32.0446 49.5089 31.8772 49.0904 31.8772C48.6719 31.8772 48.2533 32.0446 47.952 32.346L36.9699 43.3449L32.048 38.4062C31.7467 38.1049 31.3281 37.9375 30.9096 37.9375C30.4911 37.9375 30.0725 38.1049 29.7712 38.4062L27.4944 40.683C27.1931 40.9844 27.0257 41.4029 27.0257 41.8214C27.0257 42.24 27.1931 42.6585 27.4944 42.9598L33.5547 49.0201L35.8315 51.2969C36.1328 51.5982 36.5513 51.7656 36.9699 51.7656C37.3884 51.7656 37.8069 51.5982 38.1083 51.2969L40.385 49.0201L52.5056 36.8996C52.8069 36.5982 52.9743 36.1797 52.9743 35.7612Z"
                            fill="white" />
                    </svg>
                    <h3 class="mt-3">Your reservation is confirmed!</h3>
                    <p>Thank you, {{ Auth::user()->name }}! Please proceed to the cashier on your reserved date and timeslot
                        to pick up your order. Kindly prepare the exact amount.</p>
                </div>

                <div class="order-info text-start mb-4">
                    <div class="order-info__item d-flex">
                        <label class="me-3 fw-bold">Order Placed On:</label>
                        <span>{{ $order->created_at->setTimezone('Asia/Manila')->format('F j, Y, g:i a') }}</span>
                    </div>
                </div>

                <div class="checkout__totals-wrapper">
                    <div class="checkout__totals">
                        <h3 class="text-center mb-3">Order Details</h3>
                        <table class="table table-bordered checkout-cart-items">
                            <thead>
                                <tr>
                                    <th>PRODUCT</th>
                                    <th class="text-end">Item Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderItems as $item)
                                    <tr>
                                        <td>{{ $item->product->name }} x {{ $item->quantity }}</td>
                                        <td class="text-end">{{ $item->price }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="table table-bordered checkout-totals mt-3">
                            <tbody>
                                {{-- <tr>
                                <th>SUBTOTAL</th>
                                <td class="text-end">{{ $order->subtotal }}</td>
                            </tr> --}}
                                <tr>
                                    <th>TOTAL</th>
                                    <td class="text-end"><strong>{{ $order->total }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="reservationDate" name="reservation_date"
                                        value="{{ $order->reservation_date ? \Carbon\Carbon::parse($order->reservation_date)->format('F j, Y') : 'N/A' }}"
                                        readonly>
                                    <label for="reservationDate">Reservation Date</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="timeSlot" name="timeslot"
                                        value="{{ $order->time_slot ?? 'N/A' }}" readonly>
                                    <label for="timeSlot">Timeslot</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
