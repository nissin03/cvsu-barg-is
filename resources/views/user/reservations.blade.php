@extends('layouts.app')

@section('content')
    <style>
        .table> :not(caption)>tr>th {
            padding: 0.625rem 1.5rem .625rem !important;
            background-color: #b3b892 !important;
        }

        .table>tr>td {
            padding: 0.625rem 1.5rem .625rem !important;
        }

        .table-bordered> :not(caption)>tr>th,
        .table-bordered> :not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        .table> :not(caption)>tr>td {
            padding: .8rem 1rem !important;
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

        .reservation-items .reservation-item {
            padding: 0.5rem 0;
        }

        .reservation-items .reservation-item .text-muted {
            font-size: 0.9rem;
        }

        .btn-outline-primary {
            font-size: 0.875rem;
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
        $breadcrumbs[] = ['url' => null, 'label' => 'My Reservations'];
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container" style="padding-top: 1em;">
        <section class="my-account container">
            <h2 class="page-title">My Reservations</h2>

            <div class="row">
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <div class="col-lg-10">
                    <div class="accordion mt-5" id="facilityAccordion">
                        @forelse ($payments as $payment)
                            <div class="accordion-item mb-3 shadow-sm border rounded">
                                <h2 class="accordion-header" id="headingReservation{{ $payment->id }}">
                                    <button class="accordion-button d-flex justify-content-between align-items-center"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseReservation{{ $payment->id }}" aria-expanded="false"
                                        aria-controls="collapseReservation{{ $payment->id }}">
                                        <span>
                                            <strong>Reservation #{{ $payment->id }}</strong>
                                            <small class="text-muted">Placed on
                                                {{ $payment->created_at->format('M d, Y') }}</small>
                                        </span>
                                        <span>
                                            @if ($payment->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($payment->status == 'canceled')
                                                <span class="badge bg-danger">Canceled</span>
                                            @elseif($payment->status == 'reserved')
                                                <span class="badge bg-warning">Reserved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapseReservation{{ $payment->id }}" class="accordion-collapse collapse"
                                    aria-labelledby="headingReservation{{ $payment->id }}"
                                    data-bs-parent="#facilityAccordion">
                                    <div class="accordion-body">
                                        <!-- Reservation Summary Section -->
                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <p><strong>Facility: </strong>{{ $payment->availability->facility->name }}
                                                </p>
                                                <p><strong>Status: </strong>{{ ucfirst($payment->status) }}</p>
                                                <p><strong>Date:
                                                    </strong>{{ \Carbon\Carbon::parse($payment->availability->date_from)->format('M d, Y') }}
                                                    to
                                                    {{ \Carbon\Carbon::parse($payment->availability->date_to)->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p><strong>Total Price:
                                                    </strong>&#8369;{{ number_format($payment->total_price, 2) }}</p>
                                            </div>
                                        </div>

                                        <!-- Reservation Footer Details -->
                                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                            <p class="text-muted">
                                                <strong>Last Updated:</strong>
                                                {{ $payment->updated_at->format('M d, Y H:i') }}
                                                @if ($payment->updated_by)
                                                    by {{ $payment->updatedBy->name }}
                                                @endif
                                            </p>
                                            <a href="{{ route('user.reservation_details', ['payment_id' => $payment->id]) }}"
                                                class="btn btn-outline-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p>No reservations found.</p>
                        @endforelse
                    </div>

                    <div class="divider"></div>

                    <div class="d-flex justify-content-between flex-wrap gap-2 pagination-wrapper">
                        {{ $payments->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('styles')
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 20px;
            text-align: left;
        }

        .table td {
            white-space: nowrap;
        }
    </style>
@endpush
