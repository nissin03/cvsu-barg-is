@extends('layouts.admin')

@section('content')
    <style>
        .table-transaction>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fff !important;
        }

        .tf-button {
            width: max-content;
            height: 50px;
            padding: 15px 22px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 600;
            line-height: 20px;
            border-radius: 12px;
            background-size: 100%;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .tf-button-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .tf-button-success:hover {
            background-color: #fff;
            color: #28a745;
        }

        .tf-button-success:hover span {
            color: #28a745 !important;
        }

        .tf-button-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .tf-button-danger:hover {
            background-color: #fff;
            color: #dc3545;
        }

        .tf-button-danger:hover span {
            color: #dc3545 !important;
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

        .small-label {
            font-size: 1.50rem;
        }

        .small-textarea {
            font-size: 2rem;
            padding: 0.25rem;
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

            <!-- Cancellation Reason Modal -->
            <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelReasonModalLabel">Provide Cancellation Reason</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('admin.order.status.update') }}" method="POST" id="cancel-order-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="order_id" value="{{ $order->id }}" />
                            <input type="hidden" name="order_status" value="canceled" />
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="canceled_reason" class="form-label small-label">
                                        Reason for Cancellation <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="canceled_reason" id="canceled_reason" class="form-control small-textarea" rows="4"
                                        placeholder="Please provide a reason for canceling this order..." required maxlength="500"></textarea>
                                    <div class="form-text" style="font-size:1rem;">Maximum 500 characters</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left column -->
                <div class="col-md-7">
                    <div class="wg-box">
                        <div class="d-flex align-items-center justify-content-between gap10 flex-wrap">
                            <div id="pdf-download-container" @if ($order->status !== 'pickedup') style="display:none;" @endif>
                                <a id="download-receipt-btn" href="{{ route('admin.order-receipt.pdf', $order->id) }}"
                                    target="_blank" class="btn btn-lg btn-outline-danger mt-3">
                                    <i class="fas fa-file-pdf me-1"></i> Print Receipt
                                </a>
                            </div>
                        </div>

                        <div class="my-account__address-item">
                            <div class="my-account__address-item__detail">
                                {{-- User Details --}}
                                <h5 class="mb-4">User Details</h5>
                                <div class="row g-3">
                                    <div class="col-12 d-flex">
                                        <p class="fw-bold me-2">Name:</p>
                                        <p class="flex-grow-1 mb-0">{{ optional($order->user)->name ?? '--' }}</p>
                                    </div>

                                    @if (optional($order->user)->role === 'student')
                                        <div class="col-12 d-flex">
                                            <p class="fw-bold me-2">Year Level:</p>
                                            <p class="flex-grow-1 mb-0">{{ optional($order->user)->year_level ?? '--' }}</p>
                                        </div>
                                        <div class="col-12 d-flex">
                                            <p class="fw-bold me-2">College:</p>
                                            <p class="flex-grow-1 mb-0">
                                                {{ optional(optional($order->user)->college)->name ?? '--' }}</p>
                                        </div>
                                        <div class="col-12 d-flex">
                                            <p class="fw-bold me-2">Course:</p>
                                            <p class="flex-grow-1 mb-0">
                                                {{ optional(optional($order->user)->course)->name ?? '--' }}</p>
                                        </div>
                                    @endif

                                    <div class="col-12 d-flex">
                                        <p class="fw-bold me-2">Mobile:</p>
                                        <p class="flex-grow-1 mb-0">{{ optional($order->user)->phone_number ?? '--' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-capitalize mt-5">Reservation Details</h5>
                        <div class="my-account__address-item">
                            <div class="my-account__address-item__detail">
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">
                                        Reservation Date:
                                    </p>
                                    <p class="body-text mb-2">
                                        {{ \Carbon\Carbon::parse($order->reservation_date)->format('F d, Y') }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Time Slot:
                                    </p>
                                    <p class="body-text mb-2">
                                        {{ \Carbon\Carbon::parse($order->time_slot)->format('h:i A') }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-5">
                                    <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">Status:
                                    </p>
                                    <span
                                        class="badge status-badge
                                    {{ $order->status === 'canceled' ? 'bg-danger' : ($order->status === 'pickedup' ? 'bg-success' : 'bg-warning') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>

                                @if ($order->updatedBy)
                                    <div class="d-flex align-items-center gap-2 mb-5">
                                        <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">
                                            Updated by:
                                        </p>
                                        <span class="body-text mb-2">
                                            {{ $order->updatedBy->name }}
                                        </span>
                                    </div>
                                @endif

                                @if ($order->status === 'canceled' && $order->canceled_reason)
                                    <div class="d-flex align-items-start gap-2 mb-4">
                                        <p class="text-capitalize body-title mb-2" style="color: var(--Body-Text);">
                                            Cancellation Reason:
                                        </p>
                                        <div class="flex-grow-1">
                                            <p class="body-text mb-1 text-muted fst-italic">
                                                {{ $order->canceled_reason }}
                                            </p>
                                            @if ($order->canceled_date)
                                                <p class="text-muted d-block">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    {{ $order->canceled_date->timezone(config('app.timezone'))->format('F d, Y h:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5">
                            @php
                                $isDisabled = in_array($order->status, ['canceled', 'pickedup']);
                            @endphp

                            @if (!$isDisabled)
                                <div class="row">
                                    <div class="col-md-3">
                                        <button type="button" class="tf-button tf-button-danger" data-bs-toggle="modal"
                                            data-bs-target="#cancelReasonModal">
                                            Cancel Order
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info fs-5">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This order has already been {{ $order->status }}. No further actions can be performed.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="col-md-5">
                    <div class="wg-box">
                        <h5 class="mb-2">Ordered Items</h5>

                        @foreach ($order->orderItems as $item)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                    alt="Product Image" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <div class="text-capitalize body-title">{{ $item->product->name }}</div>
                                    <div class="body-text">Price: &#8369;{{ $item->price }}</div>
                                    <div class="body-text">Qty: {{ $item->quantity }}</div>
                                    <div class="divider my-1"></div>
                                    <div class="d-flex justify-content-start align-items-center gap-2 body-text mb-4">
                                        <span>{{ number_format($item->price, 2) }}</span>
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

                        {{-- PAYMENT PANEL --}}
                        @if ($order->status === 'reserved')
                            <form method="POST" id="payment-form" class="gap-2"
                                action="{{ route('admin.order.complete-payment', $order->id) }}">
                                @csrf
                                <div class="mb-5">
                                    <label for="amount_paid" class="body-text mb-2">Amount Paid</label>
                                    <input type="text" name="amount_paid" id="amount_paid"
                                        class="form-control @error('amount_paid') is-invalid @enderror"
                                        inputmode="numeric" pattern="[0-9,]*">
                                    @error('amount_paid')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
                                    <span class="text-capitalize body-title">Change:</span>
                                    <span class="text-capitalize body-title"><span id="change_display">0.00</span></span>
                                </div>

                                <div class="divider my-3"></div>
                                <div class="d-flex align-items-end gap-2">
                                    <button type="submit" class="tf-button tf-button-success">
                                        Complete Payment
                                    </button>
                                </div>
                            </form>
                        @elseif($order->status === 'pickedup' && $transaction)
                            <div class="alert alert-success p-4">
                                <h5 class="mb-3 fw-bold">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Payment Completed
                                </h5>
                                <div class="d-flex justify-content-between mb-3 fs-6">
                                    <p class="fw-semibold">Amount Paid:</p>
                                    <p class="fw-bold">₱{{ number_format($transaction->amount_paid, 2) }}</p>
                                </div>
                                <div class="d-flex justify-content-between mb-3 fs-6">
                                    <p class="fw-semibold">Order Total:</p>
                                    <p class="fw-bold">₱{{ number_format($order->total, 2) }}</p>
                                </div>
                                <div class="d-flex justify-content-between mb-3 fs-6">
                                    <p class="fw-semibold">Change:</p>
                                    <p class="fw-bold">₱{{ number_format($transaction->change, 2) }}</p>
                                </div>
                                @if ($order->picked_up_date)
                                    <p class="text-muted d-block mt-2 fs-5">
                                        Completed on:
                                        {{ \Carbon\Carbon::parse($order->picked_up_date)->format('F d, Y h:i A') }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                Payment cannot be processed for {{ $order->status }} orders.
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const amountInput = document.getElementById('amount_paid');
            const changeDisplay = document.getElementById('change_display');
            const total = parseFloat(@json($order->total));

            console.log('Total:', total); // Debug log
            console.log('Amount Input:', amountInput); // Debug log
            console.log('Change Display:', changeDisplay); // Debug log

            // Single event listener for amount input
            if (amountInput && changeDisplay) {
                amountInput.addEventListener('input', function(e) {
                    // Get raw value and remove commas
                    let rawValue = this.value.replace(/,/g, '');

                    // Convert to number
                    const numericValue = parseFloat(rawValue) || 0;

                    console.log('Input value:', numericValue); // Debug log

                    // Format with thousand separators
                    if (numericValue > 0) {
                        this.value = numericValue.toLocaleString('en-US');
                    }

                    // Calculate change
                    const change = numericValue - total;
                    console.log('Change:', change); // Debug log

                    changeDisplay.textContent = change >= 0 ?
                        '₱' + change.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) :
                        '₱0.00';

                    // Remove validation error if amount is sufficient
                    if (numericValue >= total) {
                        $(amountInput).removeClass('is-invalid');
                        const feedback = amountInput.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.remove();
                        }
                    }
                });

                // Trigger on page load if there's a value
                if (amountInput.value) {
                    amountInput.dispatchEvent(new Event('input'));
                }
            } else {
                console.error('Amount input or change display not found!');
            }

            // Payment form submission
            $('#payment-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');

                // Disable button to prevent double submission
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        toastr.clear();
                        toastr.success(response.message, 'Success');

                        // Show PDF download if container exists
                        if ($('#pdf-download-container').length) {
                            $('#pdf-download-container').show();
                        }

                        // Reload after delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        // Re-enable button on error
                        submitBtn.prop('disabled', false).html('Complete Payment');

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.amount_paid) {
                                $('#amount_paid').addClass('is-invalid');
                                $('#amount_paid').next('.invalid-feedback').remove();
                                $('<div class="invalid-feedback">' + errors.amount_paid[0] +
                                        '</div>')
                                    .insertAfter('#amount_paid');
                            }
                        } else {
                            toastr.clear();
                            toastr.error('Something went wrong. Please try again.', 'Error');
                        }
                    }
                });
            });

            // Cancel order form submission
            $('#cancel-order-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#cancelReasonModal').modal('hide');
                        toastr.success('Order cancelled successfully', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.canceled_reason) {
                                $('#canceled_reason').addClass('is-invalid');
                                $('#canceled_reason').next('.invalid-feedback').remove();
                                $('<div class="invalid-feedback">' + errors.canceled_reason[0] +
                                        '</div>')
                                    .insertAfter('#canceled_reason');
                            }
                        } else {
                            toastr.error('Something went wrong. Please try again.', 'Error');
                        }
                    }
                });
            });

            // Cancel reason character limit
            $('#canceled_reason').on('input', function() {
                const maxLength = 255;
                const currentLength = $(this).val().length;

                if (currentLength > maxLength) {
                    $(this).val($(this).val().substring(0, maxLength));
                }
            });
        });
    </script>
@endpush
