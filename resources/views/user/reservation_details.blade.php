@extends('layouts.app')

@section('content')

@php
    $user = auth()->user();
    $currentRoute = request()->route()->getName();

    // Determine the base home route based on user type
    $homeRoute = match ($user?->utype ?? 'guest') {
        'USR' => route('user.index'),
        'ADM' => route('admin.index'),
        default => route('home.index'),
    };

    $breadcrumbs = [['url' => $homeRoute, 'label' => __('Home')]];

    // Generate Breadcrumbs based on the current route
    switch ($currentRoute) {
        case 'user.rental.details':
            $breadcrumbs[] = ['url' => null, 'label' => __('Rental Details')];
            break;
        default:
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
            break;
    }
@endphp

<x-header 
    backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" 
    title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" 
/>

<main class="container pt-90">
    <section class="my-account">
        <h2 class="page-title"></h2>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @isset($availability)
        <div class="wg-box">
            <div class="reservation-summary">
                <!-- Left Section -->
                <div class="left-section">
                    <div class="reservation-status">
                        <!-- Reservation Status Badge -->
                        <span class="badge badge-{{ $availability->status === 'completed' ? 'success' : ($availability->status === 'canceled' ? 'danger' : ($availability->status === 'reserved' ? 'warning' : 'secondary')) }}">
                            {{ ucfirst($availability->status) }}
                        </span>
                        @if($availability->status === 'completed')
                            <span class="badge badge-success">{{ __('Payment Completed') }}</span>
                        @elseif($availability->status === 'canceled')
                            <span class="badge badge-danger">{{ __('Payment Canceled') }}</span>
                        @endif
                        
                    </div>
                </div>

                <!-- Right Section -->
                <div class="right-section">
                    <div class="reservation-info">
                        <p><strong>Name:</strong> {{ ($availability->user?->name) }}</p>
                        <p><strong>Phone:</strong> {{ ($availability->user?->phone_number ?? __('N/A')) }}</p>
                        <p><strong>Email:</strong> {{ ($availability->user?->email ?? __('N/A')) }}</p>

                        <p><strong>{{ __('Total Price') }}:</strong> 
                            &#8369; {{ number_format($availability->total_price, 2) }}
                        </p>
                        <p><strong>{{ __('Payment Status') }}:</strong> {{ ucfirst($availability->payment_status) }}</p>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a class="btn btn-custom btn-danger" href="{{ $user?->utype === 'USR' ? route('user.reservations') : route('home.index') }}">
                    {{ __('Back to Rentals') }}
                </a>
            </div>         
        </div>
        <div class="alert alert-danger">
            {{ __('No reservation data available.') }}
        </div>
        @endisset
       
    </section>
</main>
@endsection

@push('styles')
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

    .badge-secondary {
        background-color: #6c757d;
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
@endpush
