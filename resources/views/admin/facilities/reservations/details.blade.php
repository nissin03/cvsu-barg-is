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

        .tf-button-style-1 {
            width: max-content;
            height: 40px;
            padding: 10px 16px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: "Inter", sans-serif;
            font-size: 13px;
            font-weight: 600;
            line-height: 18px;
            border-radius: 8px;
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

        .tf-button-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .tf-button-info:hover {
            background-color: #fff;
            color: #17a2b8;
        }

        .tf-button-info:hover span {
            color: #17a2b8 !important;
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


        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bg-individual-green {
            background-color: oklch(49.6% 0.265 301.924);
        }

        .bg-whole-orange {
            background-color: oklch(66.6% 0.179 58.318);
        }

        .bg-whole-blue {
            background-color: oklch(58.8% 0.158 241.966);
        }

        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .date-range {
            margin-bottom: 2px;
        }

        .qualification-status-form {
            margin: 0;
        }

        .qualification-status-form .form-select {
            min-width: 120px;
        }

        .qualification-status-form .btn {
            white-space: nowrap;
        }

        .table-responsive {
            overflow-x: auto;
        }


        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Reservation Details</h3>
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
                        <div class="text-tiny">Reservation Details</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex justify-content-end">
                    <a class="btn btn-danger" href="{{ route('admin.facilities.reservations') }}">Back to Reservation</a>
                </div>
                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif

                <!-- Responsive table wrapper -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th class="table-header" style="width: 20%;">Reservation No</th>
                                <td style="width: 30%;">{{ $reservation->id ?? 'Not Applicable' }}</td>
                                <th class="table-header" style="width: 20%;">Status</th>
                                <td style="width: 30%;">
                                    <span
                                        class="badge status-badge
                                            @if ($reservation->status === 'completed') bg-success
                                            @elseif ($reservation->status === 'pending') bg-primary
                                            @elseif ($reservation->status === 'canceled') bg-danger
                                            @elseif ($reservation->status === 'reserved') bg-warning
                                            @else bg-secondary @endif">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Row 2: Facility Info -->
                            <tr>
                                <th class="table-header">Facility</th>
                                <td>{{ $reservation->availability->facility->name ?? 'Not Applicable' }}</td>
                                <th class="table-header">Type</th>
                                <td>
                                    <span
                                        class="badge status-badge
                            @if ($reservation->availability->facility->facility_type === 'individual') bg-individual-green
                            @elseif ($reservation->availability->facility->facility_type === 'whole_place') bg-whole-orange
                            @elseif ($reservation->availability->facility->facility_type === 'both') bg-warning
                            @else bg-secondary @endif">
                                        {{ ucfirst($reservation->availability->facility->facility_type ?? 'Not Applicable') }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Row 3: Date Info -->
                            <tr>
                                <th class="table-header">Reservation Date</th>
                                <td colspan="3">
                                    @php
                                        $groupedDates = [];

                                        if (
                                            $reservation->grouped_availabilities &&
                                            $reservation->grouped_availabilities->isNotEmpty()
                                        ) {
                                            $sortedAvailabilities = $reservation->grouped_availabilities->sortBy(
                                                'date_from',
                                            );
                                            $currentGroup = [];

                                            foreach ($sortedAvailabilities as $avail) {
                                                if (empty($currentGroup)) {
                                                    $currentGroup = [
                                                        'start' => $avail->date_from,
                                                        'end' => $avail->date_to,
                                                    ];
                                                } elseif (
                                                    \Carbon\Carbon::parse($currentGroup['end'])
                                                        ->addDay()
                                                        ->format('Y-m-d') === $avail->date_from
                                                ) {
                                                    $currentGroup['end'] = $avail->date_to;
                                                } else {
                                                    $groupedDates[] = $currentGroup;
                                                    $currentGroup = [
                                                        'start' => $avail->date_from,
                                                        'end' => $avail->date_to,
                                                    ];
                                                }
                                            }

                                            if (!empty($currentGroup)) {
                                                $groupedDates[] = $currentGroup;
                                            }
                                        } else {
                                            // fallback: single availability
                                            $groupedDates[] = [
                                                'start' => $reservation->availability->date_from,
                                                'end' => $reservation->availability->date_to,
                                            ];
                                        }
                                    @endphp

                                    @if (!empty($groupedDates))
                                        <div class="date-ranges">
                                            @foreach ($groupedDates as $range)
                                                @php
                                                    $startDate = \Carbon\Carbon::parse($range['start']);
                                                    $endDate = \Carbon\Carbon::parse($range['end']);
                                                @endphp

                                                <div class="date-range mb-1">
                                                    @if ($startDate->equalTo($endDate))
                                                        <span
                                                            class="badge bg-info">{{ $startDate->format('M j, Y') }}</span>
                                                    @else
                                                        @if ($startDate->format('M Y') === $endDate->format('M Y'))
                                                            <span class="badge bg-info">{{ $startDate->format('M j') }} -
                                                                {{ $endDate->format('j, Y') }}</span>
                                                        @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                            <span class="badge bg-info">{{ $startDate->format('M j') }} -
                                                                {{ $endDate->format('M j, Y') }}</span>
                                                        @else
                                                            <span class="badge bg-info">{{ $startDate->format('M j, Y') }}
                                                                - {{ $endDate->format('M j, Y') }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        Not Applicable
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="wg-box mt-5 table-responsive">
                <h5>User Information</h5>
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $reservation->user->name ?? 'Not Applicable' }}</td>
                            <th>Email</th>
                            <td>{{ $reservation->user->email ?? 'Not Applicable' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $reservation->user->phone_number ?? '—' }}</td>
                            <th>Role</th>
                            <td>{{ ucfirst($reservation->user->role ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td>{{ optional($reservation->user->college)->name ?? '—' }}</td>
                            <th>Course</th>
                            <td>{{ optional($reservation->user->course)->name ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @php
                $allAddons = $reservation->refundable_addon_transactions->merge(
                    $reservation->non_refundable_addon_transactions,
                );
            @endphp


            @if ($allAddons->isNotEmpty())
                {{-- User Add-ons Section --}}
                <div class="wg-box mt-5 table-responsive">
                    <h5>User Add-ons</h5>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Add-on Name</th>
                                <th>Billing Cycle</th>
                                <th>Date From</th>
                                <th>Date To</th>
                                <th>Quantity</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $groupedAddons = $reservation->grouped_addons ?? collect(); @endphp

                            @forelse ($groupedAddons as $row)
                                <tr>
                                    <td>{{ $row->addon_name }}</td>
                                    <td>{{ $row->billing_cycle_label }}</td>

                                    @if (!$row->is_contract)
                                        <td>{{ $row->date_from_fmt }}</td>
                                        <td>{{ $row->date_to_fmt }}</td>
                                    @else
                                        <td colspan="2" class="text-center text-muted">Contract-based</td>
                                    @endif

                                    <td>{{ $row->quantity ?? '—' }}</td>
                                    <td>{{ $row->days ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No add-ons assigned to this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if (isset($reservation->refundable_addon_payments) && $reservation->refundable_addon_payments->count() > 0)
                <div class="wg-box mt-5">
                    <h4 class="fw-bold mb-4">Refundable Addon Payments</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Add-on Name</th>
                                    <th style="width: 25%;">Price</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservation->refundable_addon_payments as $addonPayment)
                                    @php
                                        $currentDownpayment = $addonPayment->downpayment_amount ?? 0;
                                        $remainingBalance = $addonPayment->total - $currentDownpayment;
                                        $isLocked = in_array($addonPayment->status, ['forfeit', 'refunded']);
                                    @endphp
                                    <tr>
                                        {{-- Addon name --}}
                                        <td><strong>{{ $addonPayment->addon->name ?? 'N/A' }}</strong></td>

                                        {{-- Price --}}
                                        <td>&#8369;{{ number_format($addonPayment->total, 2) }}</td>

                                        {{-- Status --}}
                                        <td>
                                            <span
                                                class="badge status-badge
                                    @if ($addonPayment->status === 'paid') bg-success
                                    @elseif ($addonPayment->status === 'downpayment') bg-info
                                    @elseif ($addonPayment->status === 'unpaid') bg-warning text-dark
                                    @elseif ($addonPayment->status === 'forfeit') bg-danger
                                    @elseif ($addonPayment->status === 'refunded') bg-secondary
                                    @else bg-secondary @endif">
                                                {{ ucfirst($addonPayment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <!-- Update Status Section -->
                                            <div class="mt-6">
                                                <h6 class="fw-semibold mb-3 mt-2">Update Payment Status</h6>

                                                @if ($isLocked)
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-lock"></i>
                                                        Status is locked and cannot be changed.
                                                    </div>
                                                @endif

                                                <form
                                                    action="{{ route('admin.addon-payments.update', $addonPayment->id) }}"
                                                    method="POST" class="addon-payment-form"
                                                    data-addon-payment-id="{{ $addonPayment->id }}"
                                                    data-current-downpayment="{{ $currentDownpayment }}"
                                                    data-remaining-balance="{{ $remainingBalance }}"
                                                    data-total="{{ $addonPayment->total }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <!-- Status Dropdown -->
                                                    <div class="d-flex gap-3 align-items-center flex-wrap mb-4">
                                                        <select name="status" class="addon-status-select"
                                                            data-addon-payment-id="{{ $addonPayment->id }}"
                                                            data-current-status="{{ $addonPayment->status }}"
                                                            style="max-width: 200px;" {{ $isLocked ? 'disabled' : '' }}>

                                                            @if ($addonPayment->status === 'unpaid')
                                                                {{-- Unpaid can transition to: downpayment or paid --}}
                                                                <option value="unpaid" selected>Unpaid</option>
                                                                <option value="downpayment">Downpayment</option>
                                                                <option value="paid">Paid</option>
                                                            @elseif ($addonPayment->status === 'downpayment')
                                                                {{-- Downpayment can transition to: forfeit or paid --}}
                                                                <option value="downpayment" selected>Downpayment</option>
                                                                <option value="forfeit">Forfeit</option>
                                                                <option value="paid">Paid</option>
                                                            @elseif ($addonPayment->status === 'paid')
                                                                {{-- Paid can transition to: refunded or forfeit --}}
                                                                <option value="paid" selected>Paid</option>
                                                                <option value="refunded">Refunded</option>
                                                                <option value="forfeit">Forfeit</option>
                                                            @elseif ($addonPayment->status === 'forfeit')
                                                                {{-- Forfeit is locked --}}
                                                                <option value="forfeit" selected>Forfeit</option>
                                                            @elseif ($addonPayment->status === 'refunded')
                                                                {{-- Refunded is locked --}}
                                                                <option value="refunded" selected>Refunded</option>
                                                            @endif
                                                        </select>
                                                        <button type="submit" class="tf-button w208 addon-update-btn"
                                                            {{ $isLocked ? 'disabled' : '' }}>
                                                            Update Payment
                                                        </button>
                                                    </div>

                                                    <!-- Current Payment Info -->
                                                    @if ($currentDownpayment > 0)
                                                        <div class="alert alert-warning mb-3">
                                                            <i class="fas fa-info-circle"></i>
                                                            <strong>Already Paid:</strong>
                                                            &#8369;{{ number_format($currentDownpayment, 2) }}
                                                        </div>
                                                    @endif

                                                    <!-- Downpayment and Remaining Balance (Hidden for forfeit) -->
                                                    <div class="payment-details-section"
                                                        data-addon-payment-id="{{ $addonPayment->id }}"
                                                        style="{{ $addonPayment->status === 'forfeit' ? 'display: none;' : '' }}">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Payment
                                                                    Received</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">&#8369;</span>
                                                                    <input type="number" name="payment_received"
                                                                        class="form-control payment-received-input"
                                                                        data-addon-payment-id="{{ $addonPayment->id }}"
                                                                        data-total="{{ $addonPayment->total }}"
                                                                        data-current-downpayment="{{ $currentDownpayment }}"
                                                                        data-remaining-balance="{{ $remainingBalance }}"
                                                                        value="0" min="0" step="0.01"
                                                                        {{ $addonPayment->status !== 'downpayment' ? 'readonly' : '' }}>
                                                                </div>
                                                                <div class="invalid-feedback-custom"
                                                                    data-addon-payment-id="{{ $addonPayment->id }}"
                                                                    style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Remaining
                                                                    Balance</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">&#8369;</span>
                                                                    <input type="text"
                                                                        class="form-control remaining-balance-display remaining-balance-display-{{ $addonPayment->id }}"
                                                                        data-addon-payment-id="{{ $addonPayment->id }}"
                                                                        value="{{ number_format($remainingBalance, 2) }}"
                                                                        readonly>
                                                                </div>
                                                                <small class="text-muted">Current:
                                                                    &#8369;{{ number_format($remainingBalance, 2) }}</small>
                                                            </div>
                                                        </div>

                                                        <!-- Change Display (only shows when payment exceeds remaining balance) -->
                                                        <div class="change-display"
                                                            data-addon-payment-id="{{ $addonPayment->id }}"
                                                            style="display: none;">
                                                            <div
                                                                class="alert alert-success d-flex justify-content-between align-items-center">
                                                                <span class="fw-semibold">Change to Return:</span>
                                                                <span class="fs-5 fw-bold change-amount"
                                                                    data-addon-payment-id="{{ $addonPayment->id }}">
                                                                    &#8369;0.00
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Total Price Display -->
                                                    <div
                                                        class="alert alert-info d-flex justify-content-between align-items-center">
                                                        <span class="fw-semibold">Total Price:</span>
                                                        <span
                                                            class="fs-5 fw-bold">&#8369;{{ number_format($addonPayment->total, 2) }}</span>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- JavaScript for validation and change calculation --}}
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.payment-received-input').forEach(function(input) {
                            input.addEventListener('input', function() {
                                calculateChange(this);
                            });

                            input.addEventListener('blur', function() {
                                validatePaymentInput(this);
                            });
                        });

                        function calculateChange(input) {
                            const addonPaymentId = input.getAttribute('data-addon-payment-id');
                            const remainingBalance = parseFloat(input.getAttribute('data-remaining-balance')) || 0;
                            const paymentReceived = parseFloat(input.value) || 0;

                            const changeDisplay = document.querySelector('.change-display[data-addon-payment-id="' +
                                addonPaymentId + '"]');
                            const changeAmount = document.querySelector('.change-amount[data-addon-payment-id="' +
                                addonPaymentId + '"]');

                            if (paymentReceived > remainingBalance) {
                                const change = paymentReceived - remainingBalance;
                                if (changeDisplay && changeAmount) {
                                    changeAmount.textContent = '₱' + change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                    changeDisplay.style.display = 'block';
                                }
                            } else {
                                if (changeDisplay) {
                                    changeDisplay.style.display = 'none';
                                }
                            }
                            const remainingBalanceDisplay = document.querySelector('.remaining-balance-display-' +
                                addonPaymentId);
                            if (remainingBalanceDisplay) {
                                const newRemaining = Math.max(0, remainingBalance - paymentReceived);
                                remainingBalanceDisplay.value = newRemaining.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                            }
                        }

                        function validatePaymentInput(input) {
                            const addonPaymentId = input.getAttribute('data-addon-payment-id');
                            const paymentReceived = parseFloat(input.value) || 0;
                            const errorDiv = document.querySelector('.invalid-feedback-custom[data-addon-payment-id="' +
                                addonPaymentId + '"]');

                            let isValid = true;
                            let errorMessage = '';
                            if (paymentReceived < 0) {
                                isValid = false;
                                errorMessage = 'Payment amount cannot be negative';
                            }

                            if (!isValid) {
                                input.classList.add('is-invalid');
                                input.style.borderColor = '#dc3545';
                                if (errorDiv) {
                                    errorDiv.textContent = errorMessage;
                                    errorDiv.style.display = 'block';
                                }
                            } else {
                                input.classList.remove('is-invalid');
                                input.style.borderColor = '';
                                if (errorDiv) {
                                    errorDiv.style.display = 'none';
                                }
                            }

                            return isValid;
                        }

                        document.querySelectorAll('.addon-status-select').forEach(function(select) {
                            select.addEventListener('change', function() {
                                const addonPaymentId = this.getAttribute('data-addon-payment-id');
                                const selectedStatus = this.value;
                                const currentStatus = this.getAttribute('data-current-status');

                                const paymentDetails = document.querySelector(
                                    '.payment-details-section[data-addon-payment-id="' + addonPaymentId +
                                    '"]');

                                if (paymentDetails) {
                                    if (selectedStatus === 'downpayment') {
                                        paymentDetails.style.display = 'block';
                                    } else {
                                        paymentDetails.style.display = 'none';
                                    }
                                }

                                const paymentInput = document.querySelector(
                                    '.payment-received-input[data-addon-payment-id="' + addonPaymentId +
                                    '"]');

                                if (paymentInput) {
                                    if (selectedStatus === 'downpayment') {
                                        paymentInput.removeAttribute('readonly');
                                        if (currentStatus === 'unpaid') {
                                            paymentInput.value = '0';
                                        }
                                    } else {
                                        paymentInput.setAttribute('readonly', 'readonly');
                                        paymentInput.value = '0';
                                        paymentInput.classList.remove('is-invalid');
                                        paymentInput.style.borderColor = '';

                                        const errorDiv = document.querySelector(
                                            '.invalid-feedback-custom[data-addon-payment-id="' +
                                            addonPaymentId + '"]');
                                        if (errorDiv) {
                                            errorDiv.style.display = 'none';
                                        }

                                        const changeDisplay = document.querySelector(
                                            '.change-display[data-addon-payment-id="' + addonPaymentId +
                                            '"]');
                                        if (changeDisplay) {
                                            changeDisplay.style.display = 'none';
                                        }
                                    }
                                }
                            });
                        });

                        // Replace the entire addon payment form submit section with this updated version
                        document.querySelectorAll('.addon-payment-form').forEach(function(form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();

                                const statusSelect = this.querySelector('.addon-status-select');
                                const selectedStatus = statusSelect.value;
                                const currentStatus = statusSelect.getAttribute('data-current-status');
                                const paymentInput = this.querySelector('.payment-received-input');
                                const paymentReceived = parseFloat(paymentInput.value) || 0;
                                const currentDownpayment = parseFloat(this.getAttribute(
                                    'data-current-downpayment')) || 0;
                                const remainingBalance = parseFloat(this.getAttribute(
                                    'data-remaining-balance')) || 0;
                                const total = parseFloat(this.getAttribute('data-total'));
                                const formAction = this.action;

                                if (selectedStatus === 'downpayment') {
                                    const isValid = validatePaymentInput(paymentInput);

                                    if (!isValid) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Validation Error',
                                            text: 'Please fix the validation errors before submitting.',
                                            confirmButtonColor: '#d33'
                                        });
                                        return;
                                    }

                                    if (paymentReceived <= 0) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Invalid Payment',
                                            text: 'Please enter a payment amount greater than 0.',
                                            confirmButtonColor: '#3085d6'
                                        });
                                        paymentInput.focus();
                                        return;
                                    }
                                }

                                let confirmTitle = 'Update Addon Payment?';
                                let confirmText = 'Are you sure you want to update this addon payment?';
                                let confirmIcon = 'question';

                                if (selectedStatus === 'forfeit') {
                                    confirmTitle = 'Mark as Forfeit?';
                                    confirmText =
                                        'Are you sure you want to mark this as FORFEIT? This action will:\n\n' +
                                        '• Lock the addon payment status\n' +
                                        '• Automatically CANCEL the main reservation\n' +
                                        '• Update all related transactions to CANCELED\n\n' +
                                        'This action cannot be undone.';
                                    confirmIcon = 'warning';
                                } else if (selectedStatus === 'paid') {
                                    if (currentStatus === 'unpaid') {
                                        confirmTitle = 'Mark as Paid?';
                                        confirmText =
                                            'Are you sure you want to mark this as PAID (full payment)? This action will lock the payment status.';
                                    } else if (currentStatus === 'downpayment') {
                                        confirmTitle = 'Mark as Paid?';
                                        confirmText =
                                            'Are you sure you want to mark this as PAID? This action will lock the payment status.';
                                    }
                                    confirmIcon = 'success';
                                } else if (selectedStatus === 'refunded') {
                                    confirmTitle = 'Refund Payment?';
                                    confirmText =
                                        'Are you sure you want to REFUND this payment? This action will lock the payment status.';
                                    confirmIcon = 'warning';
                                } else if (selectedStatus === 'downpayment' && paymentReceived > 0) {
                                    const newTotalPaid = currentDownpayment + Math.min(paymentReceived,
                                        remainingBalance);
                                    const newRemaining = Math.max(0, remainingBalance - paymentReceived);
                                    const change = Math.max(0, paymentReceived - remainingBalance);

                                    confirmTitle = 'Process Payment?';
                                    confirmText = 'Payment Received: ₱' + paymentReceived.toFixed(2).replace(
                                            /\B(?=(\d{3})+(?!\d))/g, ',') + '\n' +
                                        'Total Paid: ₱' + newTotalPaid.toFixed(2).replace(
                                            /\B(?=(\d{3})+(?!\d))/g, ',') + '\n' +
                                        'New Remaining Balance: ₱' + newRemaining.toFixed(2).replace(
                                            /\B(?=(\d{3})+(?!\d))/g, ',');

                                    if (change > 0) {
                                        confirmText += '\n\nChange to Return: ₱' + change.toFixed(2).replace(
                                            /\B(?=(\d{3})+(?!\d))/g, ',');
                                    }

                                    confirmIcon = 'info';
                                }

                                Swal.fire({
                                    title: confirmTitle,
                                    text: confirmText,
                                    icon: confirmIcon,
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes, proceed',
                                    cancelButtonText: 'Cancel',
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        Swal.fire({
                                            title: 'Processing...',
                                            text: 'Please wait while we update the payment.',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            showConfirmButton: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        const formData = new FormData();
                                        formData.append('_method', 'PUT');
                                        formData.append('status', selectedStatus);
                                        if (selectedStatus === 'downpayment') {
                                            formData.append('payment_received', paymentReceived);
                                        }

                                        fetch(formAction, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector(
                                                        'meta[name="csrf-token"]').getAttribute(
                                                        'content'),
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                body: formData
                                            })
                                            .then(response => {
                                                if (!response.ok) {
                                                    return response.json().then(err => {
                                                        throw err;
                                                    });
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                if (data.success) {
                                                    if (data.reservation_status_changed && data
                                                        .new_reservation_status === 'canceled') {
                                                        // Update the UI immediately BEFORE showing the alert
                                                        updateReservationStatusUIOnForfeit();

                                                        if (data.qualification_ids && data
                                                            .qualification_ids.length > 0) {
                                                            data.qualification_ids.forEach(
                                                                qualId => {
                                                                    const event =
                                                                        new CustomEvent(
                                                                            'qualificationStatusChanged', {
                                                                                detail: {
                                                                                    qualificationId: qualId,
                                                                                    newStatus: 'canceled'
                                                                                }
                                                                            });
                                                                    document.dispatchEvent(
                                                                        event);
                                                                });
                                                        }
                                                        Swal.fire({
                                                            icon: 'success',
                                                            title: 'Success!',
                                                            html: `<div style="text-align: left;">
                                        <p><strong>${data.message}</strong></p>
                                        <hr>
                                        <p style="color: #dc3545; font-weight: 600;">
                                            <i class="fas fa-exclamation-circle"></i>
                                            Main Reservation Status Updated
                                        </p>
                                        <p>The reservation has been automatically canceled due to the forfeit status.</p>
                                        <p><small class="text-muted">The page will refresh to show all changes.</small></p>
                                    </div>`,
                                                            confirmButtonColor: '#3085d6',
                                                            confirmButtonText: 'Okay, Refresh Page'
                                                        }).then(() => {
                                                            window.location.reload();
                                                        });
                                                    } else {
                                                        Swal.fire({
                                                            icon: 'success',
                                                            title: 'Success!',
                                                            text: data.message,
                                                            confirmButtonColor: '#3085d6',
                                                            timer: 3000
                                                        }).then(() => {
                                                            window.location.reload();
                                                        });
                                                    }
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Update Failed',
                                                        text: data.message ||
                                                            'An error occurred while updating the payment.',
                                                        confirmButtonColor: '#d33'
                                                    });
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);

                                                if (error.errors) {
                                                    let errorMessages = Object.values(error.errors)
                                                        .flat().join('\n');
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Validation Error',
                                                        text: errorMessages,
                                                        confirmButtonColor: '#d33'
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: error.message ? 'Error' :
                                                            'Network Error',
                                                        text: error.message ||
                                                            'An error occurred while processing your request. Please try again.',
                                                        confirmButtonColor: '#d33'
                                                    });
                                                }
                                            });
                                    }
                                });
                            });
                        });

                        // NEW FUNCTION: Update reservation status UI when forfeit happens
                        function updateReservationStatusUIOnForfeit() {
                            console.log('Updating reservation status UI to canceled due to forfeit...');

                            // 1. Update the main reservation status badge (at the top of the page)
                            const mainStatusBadge = document.querySelector('.status-badge:not(.qualification-status-badge)');
                            if (mainStatusBadge) {
                                mainStatusBadge.textContent = 'Canceled';
                                mainStatusBadge.className = 'badge status-badge bg-danger';
                            }

                            // 2. Update the status dropdown
                            const statusSelect = document.getElementById('status');
                            if (statusSelect) {
                                statusSelect.innerHTML = '';
                                const option = document.createElement('option');
                                option.value = 'canceled';
                                option.textContent = 'Canceled (Final)';
                                option.selected = true;
                                statusSelect.appendChild(option);

                                // Disable the dropdown
                                statusSelect.disabled = true;
                                statusSelect.classList.add('disabled');
                                statusSelect.dataset.currentStatus = 'canceled';
                            }

                            // 3. Update the submit button
                            const submitButton = document.getElementById('submit-button');
                            if (submitButton) {
                                submitButton.disabled = true;
                                submitButton.classList.add('disabled');
                                submitButton.textContent = 'Cannot Change';
                            }

                            // 4. Update the alert message
                            const alertDiv = document.getElementById('reservation-status-alert');
                            if (alertDiv) {
                                alertDiv.className = 'alert alert-danger mb-3';
                                alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Reservation Canceled:</strong> This reservation was automatically canceled due to addon payment forfeit. Status is now locked and cannot be changed.
        `;
                            }

                            // 5. Add a visual indicator that changes were made
                            const updateReservationSection = document.querySelector('.wg-box.mt-5:has(#statusUpdateForm)');
                            if (updateReservationSection) {
                                updateReservationSection.style.border = '2px solid #dc3545';
                                updateReservationSection.style.backgroundColor = '#fff5f5';

                                // Add a flash effect
                                setTimeout(() => {
                                    updateReservationSection.style.transition = 'all 0.3s ease';
                                }, 100);
                            }

                            console.log('Reservation status UI updated successfully');
                        }
                    });
                </script>

                <style>
                    .addon-status-select,
                    .qualification-status-select {
                        padding: 8px 12px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        font-size: 14px;
                    }

                    .addon-status-select:disabled,
                    .addon-update-btn:disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                    }

                    .tf-button.w208 {
                        min-width: 208px;
                    }

                    .input-group-text {
                        background-color: #f8f9fa;
                        border-right: 0;
                    }

                    .input-group .form-control {
                        border-left: 0;
                    }

                    .input-group .form-control:focus {
                        border-color: #ced4da;
                        box-shadow: none;
                    }

                    .input-group .form-control[readonly] {
                        background-color: #e9ecef;
                        cursor: not-allowed;
                    }

                    .input-group .form-control.is-invalid {
                        border-color: #dc3545 !important;
                    }

                    .input-group .form-control.is-invalid:focus {
                        border-color: #dc3545 !important;
                        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                    }

                    .change-display {
                        margin-top: 1rem;
                    }
                </style>
            @endif


            <div class="wg-box mt-5 table-responsive">
                <h5>Transaction Details</h5>
                <table class="table table-striped table-bordered table-transaction">
                    <tbody>
                        <tr>
                            <th>Total Price</th>
                            <td>&#8369;{{ number_format($reservation->total_price, 2) }}</td>
                            <th>Last Updated By</th>
                            <td>{{ $reservation->updated_by ? $reservation->updatedBy->name : 'Not Applicable' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0)
                <div class="wg-box mt-5">
                    <h4 class="fw-bold mb-4">Qualification Details</h4>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">File</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservation->qualification_approvals as $qualification)
                                    <tr>
                                        <td>
                                            {{-- <span class="badge status-badge qualification-status-badge"
                                                data-qualification-id="{{ $qualification->id }}"
                                                data-current-qualification-status="{{ $qualification->status }}">
                                                {{ ucfirst($qualification->status) }}
                                            </span> --}}
                                            <span
                                                class="badge status-badge qualification-status-badge
                @if ($qualification->status === 'approved') bg-success
                @elseif ($qualification->status === 'pending') bg-primary
                @elseif ($qualification->status === 'canceled') bg-danger
                @else bg-secondary @endif"
                                                data-qualification-id="{{ $qualification->id }}"
                                                data-current-qualification-status="{{ $qualification->status }}">
                                                {{ ucfirst($qualification->status) }}
                                            </span>
                                        </td>

                                        <!-- File -->
                                        <td>
                                            @if ($qualification->hasQualificationFile())
                                                <a href="{{ $qualification->qualification_url }}" target="_blank"
                                                    class="btn btn-info btn-lg text-white">
                                                    <i class="icon-download"></i> View File
                                                </a>
                                            @else
                                                <span class="text-muted">No file uploaded</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <!-- Update Status Section -->
                                            <div class="mt-6">
                                                <h6 class="fw-semibold mb-3 mt-2">Update Status</h6>

                                                @if (in_array($qualification->status, ['approved', 'canceled']))
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-lock"></i>
                                                        Status cannot be changed.
                                                    </div>
                                                @else
                                                    <form
                                                        class="qualification-status-form d-flex gap-3 align-items-center flex-wrap"
                                                        data-qualification-id="{{ $qualification->id }}"
                                                        data-current-status="{{ $qualification->status }}">
                                                        @csrf
                                                        @method('PATCH')

                                                        <select name="status" class="qualification-status-select"
                                                            style="max-width: 200px;">
                                                            <option value="{{ $qualification->status }}" selected>
                                                                {{ ucfirst($qualification->status) }} (Current)
                                                            </option>
                                                            @if ($qualification->status === 'pending')
                                                                <option value="approved">Approved</option>
                                                                <option value="canceled">Canceled</option>
                                                            @endif
                                                        </select>

                                                        <button type="submit"
                                                            class="tf-button w208 qualification-update-btn">
                                                            Update Status
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @php
                $statusTransitions = [
                    'pending' => ['reserved', 'completed', 'canceled'],
                    'reserved' => ['completed', 'canceled'],
                    'completed' => ['canceled'],
                    'canceled' => [],
                ];
                $qualificationStatus = 'pending';
                $canUpdateReservation = false;
                $availableStatuses = [];

                if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0) {
                    $qualification = $reservation->qualification_approvals->first();
                    $qualificationStatus = $qualification->status;

                    if ($qualificationStatus === 'approved') {
                        $canUpdateReservation = true;
                        // Use the current reservation status to get valid transitions
                        $availableStatuses = $statusTransitions[$reservation->status] ?? [];
                    } elseif ($qualificationStatus === 'canceled') {
                        $canUpdateReservation = true;
                        $availableStatuses = ['canceled'];
                    }
                } else {
                    // No qualification required - allow normal transitions
                    $canUpdateReservation = true;
                    $availableStatuses = $statusTransitions[$reservation->status] ?? [];
                }

                if (in_array($reservation->status, ['completed', 'canceled'])) {
                    $availableStatuses = [];
                    $canUpdateReservation = false;
                }
            @endphp

            <div class="wg-box mt-5">
                <h5>Update Reservation Status</h5>
                {{--
                @if ($qualificationStatus === 'pending' && $reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-info mb-3" id="reservation-status-alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Action Required:</strong> User qualification must be approved before reservation status can
                        be updated.
                        <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
                    </div>
                @elseif(
                    $qualificationStatus === 'canceled' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-warning mb-3" id="reservation-status-alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> User qualification was canceled. Only cancellation is allowed.
                    </div>
                @elseif(
                    $qualificationStatus === 'approved' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-success mb-3" id="reservation-status-alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Qualification Approved:</strong>
                        @if ($reservation->status === 'pending')
                            Pending reservations can be changed to: Reserved, Completed, or Canceled.
                        @elseif($reservation->status === 'reserved')
                            Reserved reservations can be changed to: Completed or Canceled.
                        @elseif($reservation->status === 'completed')
                            Completed reservations can only be changed to: Canceled.
                        @else
                            Status transitions are now available.
                        @endif
                    </div>
                @elseif(in_array($reservation->status, ['completed', 'canceled']))
                    <div class="alert alert-secondary mb-3" id="reservation-status-alert">
                        <i class="fas fa-lock"></i>
                        <strong>Status Final:</strong> {{ ucfirst($reservation->status) }} reservations cannot be changed.
                    </div>
                @endif --}}

                @if ($reservation->status === 'canceled')
                    <div class="alert alert-danger mb-3" id="reservation-status-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Reservation Canceled:</strong> This reservation has been canceled and cannot be changed.
                        @if ($reservation->refundable_addon_payments && $reservation->refundable_addon_payments->contains('status', 'forfeit'))
                            <br><small class="text-muted">Reason: Addon payment was marked as forfeit.</small>
                        @endif
                    </div>
                @elseif ($reservation->status === 'completed')
                    <div class="alert alert-secondary mb-3" id="reservation-status-alert">
                        <i class="fas fa-lock"></i>
                        <strong>Status Final:</strong> Completed reservations can only be changed to Canceled.
                    </div>
                @elseif(
                    $qualificationStatus === 'pending' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-info mb-3" id="reservation-status-alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Action Required:</strong> User qualification must be approved before reservation status can
                        be updated.
                        <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
                    </div>
                @elseif(
                    $qualificationStatus === 'canceled' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-warning mb-3" id="reservation-status-alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> User qualification was canceled. Only cancellation is allowed.
                    </div>
                @elseif(
                    $qualificationStatus === 'approved' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-success mb-3" id="reservation-status-alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Qualification Approved:</strong>
                        @if ($reservation->status === 'pending')
                            Pending reservations can be changed to: Reserved, Completed, or Canceled.
                        @elseif($reservation->status === 'reserved')
                            Reserved reservations can be changed to: Completed or Canceled.
                        @elseif($reservation->status === 'completed')
                            Completed reservations can only be changed to: Canceled.
                        @else
                            Status transitions are now available.
                        @endif
                    </div>
                @endif


                <form id="statusUpdateForm" action="{{ route('admin.facilities.reservations.update', $reservation) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="initialStatus" value="{{ $reservation->status }}">
                    <input type="hidden" id="initialQualificationStatus" value="{{ $qualificationStatus }}">
                    <div class="row">
                        <div class="col-md-3 mt-2 mb-4">
                            <div class="select">
                                <select name="status" id="status" class=""
                                    data-current-status="{{ $reservation->status }}" @disabled(!$canUpdateReservation || empty($availableStatuses))>

                                    @if ($canUpdateReservation && count($availableStatuses) > 0)
                                        <!-- Current status as first option -->
                                        <option value="{{ $reservation->status }}" selected>
                                            {{ ucfirst($reservation->status) }} (Current)
                                        </option>

                                        <!-- Available transition statuses -->
                                        @foreach ($availableStatuses as $status)
                                            @if ($status !== $reservation->status)
                                                <option value="{{ $status }}">
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endif
                                        @endforeach
                                        {{-- @else
                                        <!-- Final status or restricted -->
                                        <option value="{{ $reservation->status }}" selected>
                                            @if (!$canUpdateReservation && $qualificationStatus === 'pending')
                                                Awaiting qualification approval
                                            @elseif (empty($availableStatuses))
                                                {{ ucfirst($reservation->status) }} (Final)
                                            @else
                                                No options available
                                            @endif
                                        </option>
                                    @endif --}}
                                    @else
                                        <!-- Final status or restricted -->
                                        <option value="{{ $reservation->status }}" selected>
                                            @if ($reservation->status === 'canceled')
                                                Canceled (Final)
                                            @elseif ($reservation->status === 'completed')
                                                {{ ucfirst($reservation->status) }} (Final)
                                            @elseif (!$canUpdateReservation && $qualificationStatus === 'pending')
                                                Awaiting qualification approval
                                            @elseif (empty($availableStatuses))
                                                {{ ucfirst($reservation->status) }} (Final)
                                            @else
                                                No options available
                                            @endif
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        {{-- <div class="col-md-3">
                            <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                @disabled(!$canUpdateReservation || empty($availableStatuses))>
                                @if (!$canUpdateReservation && $qualificationStatus === 'pending')
                                    Awaiting Approval
                                @elseif (empty($availableStatuses))
                                    Cannot Change
                                @else
                                    Update Status
                                @endif
                            </button>
                        </div> --}}
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                @disabled(!$canUpdateReservation || empty($availableStatuses))>
                                @if ($reservation->status === 'canceled')
                                    Cannot Change
                                @elseif (!$canUpdateReservation && $qualificationStatus === 'pending')
                                    Awaiting Approval
                                @elseif (empty($availableStatuses))
                                    Cannot Change
                                @else
                                    Update Status
                                @endif
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize status elements
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const initialQualificationStatus = document.getElementById('initialQualificationStatus')?.value ||
                'pending';

            // Initialize reservation status form based on current state
            initializeReservationStatusForm();

            // Initialize qualification status forms
            initializeQualificationForms();

            // Initialize addon payment forms
            initializeAddonPaymentForms();
        });

        function initializeReservationStatusForm() {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');

            if (!statusSelect || !submitButton) return;

            const currentReservationStatus = statusSelect.dataset.currentStatus;
            const initialQualificationStatus = document.getElementById('initialQualificationStatus')?.value || 'pending';

            // Get qualification approvals count
            const hasQualifications =
                {{ $reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0 ? 'true' : 'false' }};

            // Only disable if there are qualifications AND they are pending
            if (hasQualifications && initialQualificationStatus === 'pending') {
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
            }

            // Disable if reservation status is final
            if (['completed', 'canceled'].includes(currentReservationStatus)) {
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
            }
        }

        // Reservation status update form
        const statusUpdateForm = document.getElementById('statusUpdateForm');
        if (statusUpdateForm) {
            statusUpdateForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const submitButton = document.getElementById('submit-button');
                const statusSelect = document.getElementById('status');

                if (!submitButton || !statusSelect) {
                    console.error('Required form elements not found');
                    return;
                }

                if (submitButton.disabled) return;

                const originalButtonText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Updating...';

                fetch(form.action, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            status: statusSelect.value
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.error || 'Failed to update status');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        showAlert(form, 'success', data.message || 'Status updated successfully');

                        // Update the status badge
                        updateReservationStatusBadge(data.new_status);

                        // Update the form based on new status
                        statusSelect.dataset.currentStatus = data.new_status;
                        updateReservationDropdown(data.new_status, data.available_next_statuses, submitButton,
                            originalButtonText);
                    })
                    .catch(error => {
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                        showAlert(form, 'danger', error.message || 'Error updating status. Please try again.');
                    });
            });
        }

        // FIXED: Qualification status update forms initialization
        function initializeQualificationForms() {
            const qualificationForms = document.querySelectorAll('.qualification-status-form');

            if (qualificationForms.length > 0) {
                qualificationForms.forEach(form => {
                    // Remove any existing listeners to prevent duplicates
                    const newForm = form.cloneNode(true);
                    form.parentNode.replaceChild(newForm, form);

                    newForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const qualificationId = this.dataset.qualificationId;
                        const statusSelect = this.querySelector('.qualification-status-select');
                        const updateBtn = this.querySelector('.qualification-update-btn');

                        if (!statusSelect || !updateBtn) {
                            console.error('Required form elements not found');
                            return;
                        }

                        const newStatus = statusSelect.value;
                        const currentStatus = this.dataset.currentStatus;
                        const originalButtonText = updateBtn.innerHTML;

                        // Prevent update if status hasn't changed
                        if (newStatus === currentStatus) {
                            showAlert(this, 'info', 'Status is already ' + newStatus);
                            return;
                        }

                        statusSelect.disabled = true;
                        updateBtn.disabled = true;
                        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

                        console.log('Updating qualification status:', {
                            qualificationId,
                            currentStatus,
                            newStatus
                        });

                        fetch(`/admin/facilities/reservations/qualification/${qualificationId}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    status: newStatus
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw new Error(err.error ||
                                            'Network response was not ok');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Qualification update response:', data);

                                showAlert(this, 'success', data.message ||
                                    'Qualification approval status updated successfully');

                                // Update the qualification status badge
                                updateQualificationStatusBadge(qualificationId, data
                                    .qualification_status);

                                // Update the qualification form dropdown
                                statusSelect.value = data.qualification_status;
                                this.dataset.currentStatus = data.qualification_status;

                                // Update reservation form availability
                                updateReservationFormAvailability(
                                    data.qualification_status,
                                    data.current_reservation_status,
                                    data.available_reservation_statuses,
                                    data.can_update_reservation
                                );

                                // Disable qualification form if status is final
                                if (['approved', 'canceled'].includes(data.qualification_status)) {
                                    statusSelect.disabled = true;
                                    updateBtn.disabled = true;
                                    updateBtn.innerHTML = 'Status Updated';

                                    // Show lock message after a short delay
                                    setTimeout(() => {
                                        const lockDiv = document.createElement('div');
                                        lockDiv.className = 'alert alert-info mt-2';
                                        lockDiv.innerHTML =
                                            '<i class="fas fa-lock"></i> Status cannot be changed.';
                                        this.parentNode.replaceChild(lockDiv, this);
                                    }, 1000);
                                } else {
                                    statusSelect.disabled = false;
                                    updateBtn.disabled = false;
                                    updateBtn.innerHTML = originalButtonText;
                                }
                            })
                            .catch(error => {
                                console.error('Error updating qualification status:', error);
                                statusSelect.disabled = false;
                                updateBtn.disabled = false;
                                updateBtn.innerHTML = originalButtonText;
                                showAlert(this, 'danger', error.message ||
                                    'Error updating status. Please try again.');
                            });
                    });
                });
            }
        }

        function updateQualificationStatusBadge(qualificationId, newStatus) {
            const statusBadge = document.querySelector(`[data-qualification-id="${qualificationId}"]`);
            if (statusBadge) {
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusBadge.classList.remove('bg-success', 'bg-primary', 'bg-danger', 'bg-secondary', 'bg-warning',
                    'bg-info');
                const badgeClass = getQualificationStatusBadgeClass(newStatus);
                statusBadge.classList.add(badgeClass);
                statusBadge.dataset.currentQualificationStatus = newStatus;

                console.log('Updated qualification badge:', {
                    qualificationId: qualificationId,
                    newStatus: newStatus,
                    badgeClass: badgeClass
                });
            }
        }

        function updateReservationFormAvailability(qualificationStatus, currentReservationStatus,
            availableReservationStatuses, canUpdate) {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const alertDiv = document.getElementById('reservation-status-alert');

            if (!statusSelect || !submitButton) {
                console.error('Reservation form elements not found');
                return;
            }

            console.log('Updating reservation form availability:', {
                qualificationStatus,
                currentReservationStatus,
                availableReservationStatuses,
                canUpdate
            });

            // Don't change anything if reservation is already at final status
            if (['completed', 'canceled'].includes(currentReservationStatus)) {
                if (currentReservationStatus === 'canceled' && alertDiv) {
                    alertDiv.className = 'alert alert-danger mb-3';
                    alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <strong>Reservation Canceled:</strong> This reservation has been canceled and cannot be changed.
            `;
                }
                return;
            }

            if (qualificationStatus === 'approved') {
                // Enable reservation status form
                statusSelect.disabled = false;
                submitButton.disabled = false;
                statusSelect.classList.remove('disabled');
                submitButton.classList.remove('disabled');
                submitButton.textContent = 'Update Status';

                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-success mb-3';
                    alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <strong>Qualification Approved:</strong>
                ${getStatusTransitionMessage(currentReservationStatus)}
            `;
                }

                // Update dropdown options but keep current reservation status selected
                updateReservationDropdownOptions(currentReservationStatus, availableReservationStatuses, true);

            } else if (qualificationStatus === 'canceled') {
                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-warning mb-3';
                    alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Note:</strong> User qualification was canceled. Only cancellation is allowed.
            `;
                }

                if (currentReservationStatus !== 'canceled' && canUpdate) {
                    // Enable form but only show cancellation option
                    statusSelect.disabled = false;
                    submitButton.disabled = false;
                    statusSelect.classList.remove('disabled');
                    submitButton.classList.remove('disabled');
                    submitButton.textContent = 'Update Status';

                    updateReservationDropdownOptions(currentReservationStatus, ['canceled'], true);
                } else {
                    // Already canceled or cannot update
                    statusSelect.disabled = true;
                    submitButton.disabled = true;
                    statusSelect.classList.add('disabled');
                    submitButton.classList.add('disabled');
                }

            } else if (qualificationStatus === 'pending') {
                // Disable reservation status form while qualification is pending
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
                submitButton.textContent = 'Awaiting Approval';

                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-info mb-3';
                    alertDiv.innerHTML = `
                <i class="fas fa-info-circle"></i>
                <strong>Action Required:</strong> User qualification must be approved before reservation status can be updated.
                <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
            `;
                }
            }
        }

        function updateReservationDropdownOptions(currentStatus, availableStatuses, keepCurrentSelection = true) {
            const statusSelect = document.getElementById('status');
            if (!statusSelect) return;

            console.log('Updating dropdown options:', {
                currentStatus,
                availableStatuses,
                keepCurrentSelection
            });

            // Store the currently selected value if we want to keep it
            const currentSelection = keepCurrentSelection ? statusSelect.value : currentStatus;

            // Clear existing options
            statusSelect.innerHTML = '';

            // Always add current status as first option (selected)
            const currentOption = document.createElement('option');
            currentOption.value = currentStatus;
            currentOption.textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) + ' (Current)';
            currentOption.selected = (currentSelection === currentStatus);
            statusSelect.appendChild(currentOption);

            // Add available transition options
            if (availableStatuses && availableStatuses.length > 0) {
                availableStatuses.forEach(status => {
                    if (status !== currentStatus) {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        option.selected = (currentSelection === status);
                        statusSelect.appendChild(option);
                    }
                });
            }

            // If no options available, show as final status
            if (!availableStatuses || availableStatuses.length === 0) {
                currentOption.textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) + ' (Final)';
                statusSelect.disabled = true;
                const submitButton = document.getElementById('submit-button');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Cannot Change';
                }
            }
        }

        function updateReservationStatusBadge(newStatus) {
            const statusBadge = document.querySelector('.status-badge:not(.qualification-status-badge)');
            if (statusBadge) {
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusBadge.className = 'badge status-badge ' + getStatusBadgeClass(newStatus);
            }
        }

        function updateReservationDropdown(newStatus, availableNextStatuses, submitButton, originalButtonText) {
            const statusSelect = document.getElementById('status');

            statusSelect.innerHTML = '';

            if (newStatus === 'canceled' || newStatus === 'completed') {
                const option = document.createElement('option');
                option.value = newStatus;
                option.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Final)';
                option.selected = true;
                statusSelect.appendChild(option);
                statusSelect.disabled = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Cannot Change';
            } else if (availableNextStatuses && availableNextStatuses.length > 0) {
                // Add current status as first option
                const currentOption = document.createElement('option');
                currentOption.value = newStatus;
                currentOption.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Current)';
                currentOption.selected = true;
                statusSelect.appendChild(currentOption);

                // Add available next statuses
                availableNextStatuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusSelect.appendChild(option);
                });

                statusSelect.disabled = false;
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            } else {
                const option = document.createElement('option');
                option.value = newStatus;
                option.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Final)';
                option.selected = true;
                statusSelect.appendChild(option);
                statusSelect.disabled = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Cannot Change';
            }
        }

        function showAlert(parentElement, type, message) {
            // Remove existing alerts
            const existingAlerts = parentElement.querySelectorAll('.alert');
            existingAlerts.forEach(alert => {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger') || alert
                    .classList.contains('alert-info')) {
                    alert.remove();
                }
            });

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} mt-3`;
            alertDiv.textContent = message;
            parentElement.parentNode.insertBefore(alertDiv, parentElement.nextSibling);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'completed':
                    return 'bg-success';
                case 'pending':
                    return 'bg-primary';
                case 'canceled':
                    return 'bg-danger';
                case 'reserved':
                    return 'bg-warning';
                default:
                    return 'bg-secondary';
            }
        }

        function getQualificationStatusBadgeClass(status) {
            switch (status) {
                case 'approved':
                    return 'bg-success';
                case 'pending':
                    return 'bg-primary';
                case 'canceled':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        function getStatusTransitionMessage(currentStatus) {
            switch (currentStatus) {
                case 'pending':
                    return 'Pending reservations can be changed to: Reserved, Completed, or Canceled.';
                case 'reserved':
                    return 'Reserved reservations can be changed to: Completed or Canceled.';
                case 'completed':
                    return 'Completed reservations can only be changed to: Canceled.';
                default:
                    return 'Status transitions are now available.';
            }
        }

        // Initialize addon payment forms
        function initializeAddonPaymentForms() {
            // Handle payment received input - calculate change
            document.querySelectorAll('.payment-received-input').forEach(function(input) {
                input.addEventListener('input', function() {
                    calculateChange(this);
                });

                input.addEventListener('blur', function() {
                    validatePaymentInput(this);
                });
            });

            // Handle status dropdown changes
            document.querySelectorAll('.addon-status-select').forEach(function(select) {
                select.addEventListener('change', handleAddonStatusChange);
            });

            // Handle form submissions
            document.querySelectorAll('.addon-payment-form').forEach(function(form) {
                form.addEventListener('submit', handleAddonPaymentSubmit);
            });
        }

        function calculateChange(input) {
            const addonPaymentId = input.getAttribute('data-addon-payment-id');
            const remainingBalance = parseFloat(input.getAttribute('data-remaining-balance')) || 0;
            const paymentReceived = parseFloat(input.value) || 0;

            const changeDisplay = document.querySelector('.change-display[data-addon-payment-id="' + addonPaymentId + '"]');
            const changeAmount = document.querySelector('.change-amount[data-addon-payment-id="' + addonPaymentId + '"]');

            if (paymentReceived > remainingBalance) {
                const change = paymentReceived - remainingBalance;
                if (changeDisplay && changeAmount) {
                    changeAmount.textContent = '₱' + change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    changeDisplay.style.display = 'block';
                }
            } else {
                if (changeDisplay) {
                    changeDisplay.style.display = 'none';
                }
            }

            const remainingBalanceDisplay = document.querySelector('.remaining-balance-display-' + addonPaymentId);
            if (remainingBalanceDisplay) {
                const newRemaining = Math.max(0, remainingBalance - paymentReceived);
                remainingBalanceDisplay.value = newRemaining.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        }

        function validatePaymentInput(input) {
            const addonPaymentId = input.getAttribute('data-addon-payment-id');
            const paymentReceived = parseFloat(input.value) || 0;
            const errorDiv = document.querySelector('.invalid-feedback-custom[data-addon-payment-id="' + addonPaymentId +
                '"]');

            let isValid = true;
            let errorMessage = '';

            if (paymentReceived < 0) {
                isValid = false;
                errorMessage = 'Payment amount cannot be negative';
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                input.style.borderColor = '#dc3545';
                if (errorDiv) {
                    errorDiv.textContent = errorMessage;
                    errorDiv.style.display = 'block';
                }
            } else {
                input.classList.remove('is-invalid');
                input.style.borderColor = '';
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            }

            return isValid;
        }

        function handleAddonStatusChange(e) {
            const select = e.target;
            const addonPaymentId = select.getAttribute('data-addon-payment-id');
            const selectedStatus = select.value;
            const currentStatus = select.getAttribute('data-current-status');

            const paymentDetails = document.querySelector('.payment-details-section[data-addon-payment-id="' +
                addonPaymentId + '"]');

            if (paymentDetails) {
                if (selectedStatus === 'downpayment') {
                    paymentDetails.style.display = 'block';
                } else {
                    paymentDetails.style.display = 'none';
                }
            }

            const paymentInput = document.querySelector('.payment-received-input[data-addon-payment-id="' + addonPaymentId +
                '"]');

            if (paymentInput) {
                if (selectedStatus === 'downpayment') {
                    paymentInput.removeAttribute('readonly');
                    if (currentStatus === 'unpaid') {
                        paymentInput.value = '0';
                    }
                } else {
                    paymentInput.setAttribute('readonly', 'readonly');
                    paymentInput.value = '0';
                    paymentInput.classList.remove('is-invalid');
                    paymentInput.style.borderColor = '';

                    const errorDiv = document.querySelector('.invalid-feedback-custom[data-addon-payment-id="' +
                        addonPaymentId + '"]');
                    if (errorDiv) {
                        errorDiv.style.display = 'none';
                    }

                    const changeDisplay = document.querySelector('.change-display[data-addon-payment-id="' +
                        addonPaymentId + '"]');
                    if (changeDisplay) {
                        changeDisplay.style.display = 'none';
                    }
                }
            }
        }

        function handleAddonPaymentSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const statusSelect = form.querySelector('.addon-status-select');
            const selectedStatus = statusSelect.value;
            const currentStatus = statusSelect.getAttribute('data-current-status');
            const paymentInput = form.querySelector('.payment-received-input');
            const paymentReceived = parseFloat(paymentInput.value) || 0;
            const currentDownpayment = parseFloat(form.getAttribute('data-current-downpayment')) || 0;
            const remainingBalance = parseFloat(form.getAttribute('data-remaining-balance')) || 0;
            const total = parseFloat(form.getAttribute('data-total'));
            const formAction = form.action;

            if (selectedStatus === 'downpayment') {
                const isValid = validatePaymentInput(paymentInput);

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fix the validation errors before submitting.',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                if (paymentReceived <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Payment',
                        text: 'Please enter a payment amount greater than 0.',
                        confirmButtonColor: '#3085d6'
                    });
                    paymentInput.focus();
                    return;
                }
            }

            let confirmTitle = 'Update Addon Payment?';
            let confirmText = 'Are you sure you want to update this addon payment?';
            let confirmIcon = 'question';

            if (selectedStatus === 'forfeit') {
                confirmTitle = 'Mark as Forfeit?';
                confirmText = 'Are you sure you want to mark this as FORFEIT? This action will:\n\n' +
                    '• Lock the addon payment status\n' +
                    '• Automatically CANCEL the main reservation\n' +
                    '• Update all related transactions to CANCELED\n\n' +
                    'This action cannot be undone.';
                confirmIcon = 'warning';
            } else if (selectedStatus === 'paid') {
                if (currentStatus === 'unpaid') {
                    confirmTitle = 'Mark as Paid?';
                    confirmText =
                        'Are you sure you want to mark this as PAID (full payment)? This action will lock the payment status.';
                } else if (currentStatus === 'downpayment') {
                    confirmTitle = 'Mark as Paid?';
                    confirmText = 'Are you sure you want to mark this as PAID? This action will lock the payment status.';
                }
                confirmIcon = 'success';
            } else if (selectedStatus === 'refunded') {
                confirmTitle = 'Refund Payment?';
                confirmText = 'Are you sure you want to REFUND this payment? This action will lock the payment status.';
                confirmIcon = 'warning';
            } else if (selectedStatus === 'downpayment' && paymentReceived > 0) {
                const newTotalPaid = currentDownpayment + Math.min(paymentReceived, remainingBalance);
                const newRemaining = Math.max(0, remainingBalance - paymentReceived);
                const change = Math.max(0, paymentReceived - remainingBalance);

                confirmTitle = 'Process Payment?';
                confirmText = 'Payment Received: ₱' + paymentReceived.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') +
                    '\n' +
                    'Total Paid: ₱' + newTotalPaid.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '\n' +
                    'New Remaining Balance: ₱' + newRemaining.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                if (change > 0) {
                    confirmText += '\n\nChange to Return: ₱' + change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }

                confirmIcon = 'info';
            }

            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: confirmIcon,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we update the payment.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('status', selectedStatus);
                    if (selectedStatus === 'downpayment') {
                        formData.append('payment_received', paymentReceived);
                    }

                    fetch(formAction, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw err;
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                if (data.reservation_status_changed && data.new_reservation_status ===
                                    'canceled') {
                                    updateReservationStatusUIOnForfeit();

                                    if (data.qualification_ids && data.qualification_ids.length > 0) {
                                        data.qualification_ids.forEach(qualId => {
                                            const event = new CustomEvent(
                                                'qualificationStatusChanged', {
                                                    detail: {
                                                        qualificationId: qualId,
                                                        newStatus: 'canceled'
                                                    }
                                                });
                                            document.dispatchEvent(event);
                                        });
                                    }

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        html: `<div style="text-align: left;">
                                    <p><strong>${data.message}</strong></p>
                                    <hr>
                                    <p style="color: #dc3545; font-weight: 600;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Main Reservation Status Updated
                                    </p>
                                    <p>The reservation has been automatically canceled due to the forfeit status.</p>
                                    <p><small class="text-muted">The page will refresh to show all changes.</small></p>
                                </div>`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Okay, Refresh Page'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: data.message,
                                        confirmButtonColor: '#3085d6',
                                        timer: 3000
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Update Failed',
                                    text: data.message ||
                                        'An error occurred while updating the payment.',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            if (error.errors) {
                                let errorMessages = Object.values(error.errors).flat().join('\n');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: errorMessages,
                                    confirmButtonColor: '#d33'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: error.message ? 'Error' : 'Network Error',
                                    text: error.message ||
                                        'An error occurred while processing your request. Please try again.',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                }
            });
        }

        function updateReservationStatusUIOnForfeit() {
            console.log('Updating reservation status UI to canceled due to forfeit...');

            // 1. Update the main reservation status badge
            const mainStatusBadge = document.querySelector('.status-badge:not(.qualification-status-badge)');
            if (mainStatusBadge) {
                mainStatusBadge.textContent = 'Canceled';
                mainStatusBadge.className = 'badge status-badge bg-danger';
            }

            // 2. Update the status dropdown
            const statusSelect = document.getElementById('status');
            if (statusSelect) {
                statusSelect.innerHTML = '';
                const option = document.createElement('option');
                option.value = 'canceled';
                option.textContent = 'Canceled (Final)';
                option.selected = true;
                statusSelect.appendChild(option);

                statusSelect.disabled = true;
                statusSelect.classList.add('disabled');
                statusSelect.dataset.currentStatus = 'canceled';
            }

            // 3. Update the submit button
            const submitButton = document.getElementById('submit-button');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
                submitButton.textContent = 'Cannot Change';
            }

            // 4. Update the alert message
            const alertDiv = document.getElementById('reservation-status-alert');
            if (alertDiv) {
                alertDiv.className = 'alert alert-danger mb-3';
                alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Reservation Canceled:</strong> This reservation was automatically canceled due to addon payment forfeit. Status is now locked and cannot be changed.
        `;
            }

            // 5. Add visual indicator
            const updateReservationSection = document.querySelector('.wg-box.mt-5:has(#statusUpdateForm)');
            if (updateReservationSection) {
                updateReservationSection.style.border = '2px solid #dc3545';
                updateReservationSection.style.backgroundColor = '#fff5f5';

                setTimeout(() => {
                    updateReservationSection.style.transition = 'all 0.3s ease';
                }, 100);
            }

            console.log('Reservation status UI updated successfully');
        }

        // Listen for custom qualification status change events
        document.addEventListener('qualificationStatusChanged', function(e) {
            const qualificationId = e.detail.qualificationId;
            const newStatus = e.detail.newStatus;

            updateQualificationStatusBadge(qualificationId, newStatus);

            const form = document.querySelector(`form[data-qualification-id="${qualificationId}"]`);
            if (form) {
                const lockDiv = document.createElement('div');
                lockDiv.className = 'alert alert-info mt-2';
                lockDiv.innerHTML = '<i class="fas fa-lock"></i> Status cannot be changed.';
                form.parentNode.replaceChild(lockDiv, form);
            }
        });
    </script>
@endsection
