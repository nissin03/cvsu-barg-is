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

    // Customize breadcrumbs based on the current route
    $breadcrumbs[] = [
        'url' => null, 
        'label' => $currentRoute === 'user.rentals' ? 'Rentals' : ucwords(str_replace('.', ' ', $currentRoute))
    ];
@endphp

<x-header 
    backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" 
    title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" 
/>

    <main class="container" style="padding-top: 1em;">
        <section class="my-account container">
            <h2 class="page-title">Rentals</h2>

            <div class="row">
                <div class="col-lg-2">
                    @include('user.account__nav')
                </div>

                <div class="col-lg-10">
                <div class="accordion mt-5" id="facilityAccordion">
        @forelse ($availabilities as $availability)
            <div class="accordion-item mb-3 shadow-sm border rounded">
                <h2 class="accordion-header" id="headingReservation{{ $availability->id }}">
                    <button class="accordion-button d-flex justify-content-between align-items-center" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseReservation{{ $availability->id }}" 
                            aria-expanded="false" 
                            aria-controls="collapseReservation{{ $availability->id }}">
                        <span>
                            <strong>Reservation #{{ $availability->id }}</strong>
                            <small class="text-muted">Placed on {{ $availability->created_at->format('M d, Y') }}</small>
                        </span>
                        <span>
                            <span class="badge">
                                {{ ucfirst($availability->status) }}
                            </span>
                        </span>
                    </button>
                </h2>
                <div id="collapseReservation{{ $availability->id }}" 
                    class="accordion-collapse collapse" 
                    aria-labelledby="headingReservation{{ $availability->id }}" 
                    data-bs-parent="#facilityAccordion">
                    <div class="accordion-body">
                        <!-- Reservation Summary Section -->
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <p><strong>Name: </strong>{{ $availability->facility->name }}</p>
                                <p><strong>Status: </strong>{{ ucfirst($availability->status) }}</p>
                            </div>
                            <div>
                                <p><strong>Total Price: </strong>&#8369;{{ number_format($availability->total_price, 2) }}</p>
                            </div>
                        </div>

                        <!-- Reservation Footer Details -->
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('user.reservation_details', ['availability_id' => $availability->id]) }}" class="btn btn-outline-primary">View Details</a>
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
                    
                </div>
            </div>
        </div>
    </section>
</main>
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