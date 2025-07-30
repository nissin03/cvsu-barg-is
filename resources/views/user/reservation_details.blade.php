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

        /* Reservation Summary Styles */
        .reservation-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .reservation-summary .left-section,
        .reservation-summary .right-section {
            width: 48%;
        }

        .reservation-summary .reservation-status {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .reservation-summary .reservation-info {
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

        .text-end {
            text-align: right;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .reservation-summary .left-section,
            .reservation-summary .right-section {
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        $breadcrumbs[] = ['url' => null, 'label' => 'Reservation Details'];
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container pt-90">
        <section class="my-account">
            <h2 class="page-title">Reservation Details</h2>

            <div class="wg-box">
                <div class="reservation-summary">
                    <!-- Left Section -->
                    <div class="left-section">
                        <div class="reservation-status">
                            @if ($payment->status == 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($payment->status == 'canceled')
                                <span class="badge badge-danger">Canceled</span>
                            @elseif($payment->status == 'reserved')
                                <span class="badge badge-warning">Reserved</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </div>
                        <div class="reservation-info">
                            <p><strong>Reservation No:</strong> {{ $payment->id }}</p>
                            <p><strong>Reservation Date:</strong> {{ $payment->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Date From:</strong>
                                {{ \Carbon\Carbon::parse($payment->availability->date_from)->format('M d, Y') }}</p>
                            <p><strong>Date To:</strong>
                                {{ \Carbon\Carbon::parse($payment->availability->date_to)->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="right-section">
                        <div class="reservation-info">
                            <p><strong>Facility:</strong> {{ $payment->availability->facility->name }}</p>
                            <p><strong>Total Price:</strong> &#8369;{{ number_format($payment->total_price, 2) }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a class="btn btn-custom btn-primary" href="{{ route('user.reservations') }}">Back to Reservations</a>
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="wg-box">
                <h5>Reservation Details</h5>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Room</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->transactionReservations as $reservation)
                            <tr>
                                <td>{{ $payment->availability->facility->name }}</td>
                                <td>{{ $reservation->facilityAttribute ? $reservation->facilityAttribute->room_name : 'N/A' }}
                                </td>
                                <td>{{ $reservation->quantity }}</td>
                                <td>&#8369;{{ number_format($reservation->price->value, 2) }}</td>
                                <td>&#8369;{{ number_format($reservation->price->value * $reservation->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Payment Details -->
            <div class="wg-box">
                <h5>Payment Details</h5>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment->paymentDetails as $detail)
                            <tr>
                                <td>{{ $detail->facility->name }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>&#8369;{{ number_format($detail->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Approval History -->
            <div class="wg-box">
                <h5>Approval History</h5>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Updated By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($payment->updated_by)
                            <tr>
                                <td>
                                    @if ($payment->status == 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($payment->status == 'canceled')
                                        <span class="badge badge-danger">Canceled</span>
                                    @elseif($payment->status == 'reserved')
                                        <span class="badge badge-warning">Reserved</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $payment->updatedBy->name }}</td>
                                <td>{{ $payment->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
