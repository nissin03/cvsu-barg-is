@extends('layouts.admin')

@section('content')
    <style>
        .table-transaction>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fff !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .body-title {
            font-size: 16px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Order Details</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders') }}">
                            <div class="text-tiny">Orders</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Order Details</div>
                    </li>
                </ul>
            </div>

            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
                <div id="paymentToast" class="toast align-items-center text-bg-success border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body" id="paymentToastBody">Payment completed successfully!</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="wg-box">
                        <div class="d-flex align-items-center justify-content-between gap10 flex-wrap">
                            <h5>User Details</h5>
                            <div id="pdf-download-container" style="display: none;">
                                <a id="download-receipt-btn" href="{{ route('admin.order-receipt.pdf', $order->id) }}"
                                    target="_blank" class="btn btn-outline-danger mt-3">
                                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                                </a>
                            </div>

                        </div>
                        <div class="my-account__address-item">
                            <div class="my-account__address-item__detail col-md-6">
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Name:</p>
                                    <p class="body-text mb-2">
                                        {{ $order->user->name }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Year
                                        Level:</p>
                                    <p class="body-text mb-2">
                                        {{ $order->user->year_level }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Department:
                                    <p class="body-text mb-2">
                                        {{ $order->user->department }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Course:
                                    <p class="body-text mb-2">
                                        {{ $order->user->course }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Mobile:
                                    <p class="body-text mb-2">
                                        {{ $order->user->phone_number }}</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-capitalize mt-5">Reservation Details</h5>
                        <div class="my-account__address-item">
                            <div class="my-account__address-item__detail">
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">
                                        Reservation
                                        Date:</p>
                                    <p class="body-text mb-2">
                                        {{ \Carbon\Carbon::parse($order->reservation_date)->format('F d, Y') }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Time
                                        Slot:</p>
                                    <p class="body-text mb-2">
                                        {{ \Carbon\Carbon::parse($order->time_slot)->format('h:i A') }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Status:</p>
                                    <span
                                        class="badge status-badge
                                    {{ $order->status === 'canceled' ? 'bg-danger' : ($order->status === 'pickedup' ? 'bg-success' : 'bg-warning') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <form action="{{ route('admin.order.status.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                @php
                                    $isDisabled = in_array($order->status, ['canceled', 'pickedup']);
                                @endphp
                                <input type="hidden" name="order_id" value="{{ $order->id }}" />
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="select">
                                            <select name="order_status" id="order_status" onchange="checkStatus()"
                                                {{ session('disabled') ? 'disabled' : '' }}>
                                                <option value="canceled"
                                                    {{ $order->status === 'canceled' ? 'selected' : '' }}>
                                                    Canceled
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                            {{ $isDisabled ? 'disabled' : '' }}>
                                            Update Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="wg-box">
                        <h5 class="mb-2">Ordered Items</h5>
                        @foreach ($orderItems as $item)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                    alt="Product Image" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <div class="text-capitalize body-title">{{ $item->product->name }}</div>
                                    <div class="body-text">Price: &#8369;{{ $item->price }}</div>
                                    <div class="body-text">Qty: {{ $item->quantity }}</div>
                                    <div class="divider my-1"></div>
                                    <div class="d-flex justify-content-start align-items-center gap-2 body-text mb-4">
                                        <span>{{ number_format($item->product->price, 2) }}</span>
                                        <span>x</span>
                                        <span>{{ $item->quantity }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="divider my-1"></div>
                        @endforeach
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-capitalize body-title">Total</span>
                            <span class="text-capitalize body-title">&#8369;{{ number_format($order->total, 2) }}</span>
                        </div>
                        <div class="divider my-3"></div>

                        <form method="POST" id="payment-form" class="gap-2"
                            action="{{ route('admin.order.complete-payment', $order->id) }}">
                            @csrf
                            <div class="mb-5">
                                <label for="amount_paid" class="body-text mb-2">Amount Paid</label>
                                <input type="text" name="amount_paid" id="amount_paid" class="form-control"
                                inputmode="numeric" pattern="[0-9,]*"
                                @error('amount_paid') is-invalid @enderror {{ $isDisabled ? 'disabled' : '' }}>
                            </div>
                            @error('amount_paid')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
                                <span class="text-capitalize body-title">Change:</span>
                                <span class="text-capitalize body-title"><span
                                        id="change_display">0.00</span></span>
                            </div>

                            <div class="divider my-3"></div>

                            <div class="d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-success" {{ $isDisabled ? 'disabled' : '' }}>
                                    Complete Payment
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>



    </div>
@endsection

@push('scripts')
    <script>
        const amountInput = document.getElementById('amount_paid');
        const changeDisplay = document.getElementById('change_display');
        const total = parseFloat(@json($order->total));

        amountInput.addEventListener('input', function(e) {
            const numericValue = parseFloat(this.value.replace(/,/g, '')) || 0;
            if (!isNaN(numericValue)) {
                this.value = numericValue.toLocaleString('en-US');
            }
            const change = numericValue - total;
            changeDisplay.textContent = change >= 0 ? 
                '₱' + change.toLocaleString('en-US', {minimumFractionDigits: 2}) : 
                '₱0.00';
            if (numericValue >= total) {
                amountInput.classList.remove('is-invalid');
                const feedback = amountInput.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });

        $('#payment-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    toastr.clear();
                    toastr.success(response.message, 'Success');
                    $('#amount_paid').prop('disabled', true);
                    $('button[type="submit"]').prop('disabled', true);
                    $('#pdf-download-container').show();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.amount_paid) {
                            $('#amount_paid').addClass('is-invalid');
                            $('#amount_paid').next('.invalid-feedback').remove();
                            $('<div class="invalid-feedback">' + errors.amount_paid[0] + '</div>')
                                .insertAfter('#amount_paid');
                        }
                    } else {
                        toastr.clear();
                        toastr.error('Something went wrong. Please try again.', 'Error');
                    }
                }
            });
        });


        $('#order_status').on('change', function() {
            const disabledStates = ['canceled', 'pickedup'];
            const selected = $(this).val();
            const shouldDisable = disabledStates.includes(selected);

            $('#amount_paid').prop('disabled', shouldDisable);
            $('button[type="submit"]').prop('disabled', shouldDisable);
        });
    </script>
@endpush
