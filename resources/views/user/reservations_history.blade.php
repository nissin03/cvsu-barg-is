@extends('layouts.app')

@section('content')
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

        // Handle Reservation pages
        if ($currentRoute === 'user.reservations') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Reservation History'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="container" style="padding-top: 1em;">
        <div class="mb-4 pb-4"></div>
        <h3>Reservation History</h3>
        <div class="accordion" id="reservationHistoryAccordion">  
            @forelse ($availabilities as $availability)
                @php
                    $daysSinceReservation = \Carbon\Carbon::now()->diffInDays($availability->created_at);
                    $daysRemaining = 30 - $daysSinceReservation;
                @endphp

                @if($daysRemaining > 0)
                    <div class="alert alert-warning" role="alert">
                        <strong>Note:</strong> Reservation #{{ $availability->id }} will be removed from your history in {{ $daysRemaining }} days.
                    </div>
                @endif

                <div class="accordion-item mb-3 shadow-sm border rounded bg-white text-dark">
                    <h2 class="accordion-header" id="headingReservation{{ $availability->id }}">
                        <button
                            class="accordion-button d-flex justify-content-between align-items-center bg-success text-white"
                            type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapseReservation{{ $availability->id }}"
                            aria-expanded="false" 
                            aria-controls="collapseReservation{{ $availability->id }}">
                            <span>
                                <strong>Reservation #{{ $availability->id }}</strong>
                                <small class="text-white">Placed on {{ $availability->created_at->format('M d, Y') }}</small>
                            </span>
                            <span class="px-2">
                                @if ($availability->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($availability->status == 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @elseif($availability->status == 'reserved')
                                    <span class="badge bg-success">Reserved</span>
                                @elseif($availability->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @endif
                            </span>
                        </button>
                    </h2>
                    <div id="collapseReservation{{ $availability->id }}" class="accordion-collapse collapse"
                        aria-labelledby="headingReservation{{ $availability->id }}" 
                        data-bs-parent="#reservationHistoryAccordion">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <p><strong>Name: </strong> {{ $availability->facility->name }}</p>
                                    <p><strong>Status: </strong> {{ ucfirst($availability->status) }} </p>
                                    <p><strong>Payment Status: </strong> {{ ucfirst($availability->payment_status) }}</p>
                                </div>
                                <div>
                                    <p><strong>Total Price: </strong>₱{{ number_format($availability->total_price, 2) }}</p>
                                </div>
                            </div>

                            <!-- Reservation Footer Details -->
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                
                                <a href="{{ route('user.reservation_details',['availability_id' => $availability->id]) }}" class="btn btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No reservations found.</p>
            @endforelse
        </div>

        <div class="divider"></div>

        <div class="flex items-center justify-between flex-wrap gap-2 pagination-wrapper">
            
        </div>
    </div>  
@endsection

@push('styles')
<style>
    .accordion-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pagination-wrapper {
        margin-top: 20px;
    }
</style>
@endpush