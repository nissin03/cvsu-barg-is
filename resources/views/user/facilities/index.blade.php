@extends('layouts.app')
@section('content')
    <style>
        p {
            margin: 2px 0 2px 0;
        }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        if ($currentRoute === 'shop.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
        } elseif ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
            $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
        } elseif ($currentRoute === 'about.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
        } elseif ($currentRoute === 'rentals.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Rentals'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        @if (session('message'))
            <div class="alert alert-info">
                {{ session('message') }}
            </div>
        @endif


        @foreach ($facilities as $facility)
            <div class="product-item d-flex justify-content-between"
                style="align-items: center; flex-direction: row; padding: 15px; margin-bottom: 20px;"
                onclick="window.location.href='{{ route('user.facilities.details', ['slug' => $facility->slug]) }}'">
                <div class="image" style="width: 30%;">
                    <img src="{{ asset('storage/' . $facility->image) }}" alt=""
                        style="border-radius: 5px; width: 100%; height: 250px;">
                </div>
                <div class="rental-info" style="width: 70%">
                    <h1 style="margin-left: 1.2rem">{{ $facility->name }}
                        <hr>
                    </h1>
                    <div style="margin-left: 50px;">
                        <div class="product-single__description"
                            style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; ">
                            <p>{{ $facility->description }}</p>
                        </div>
                        @if ($facility->prices->isNotEmpty())
                            @foreach ($facility->prices as $price)
                                <p><strong>{{ $price->name }}: </strong> <span class="product-type text-primary">&#8369;
                                        {{ number_format($price->value, 2) }}</span></p>
                            @endforeach
                        @else
                            <p>No prices available for this facility.</p>
                        @endif

                        @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)
                            <p><strong>Whole Capacity: </strong>
                                {{ $facility->facilityAttributes->first()->whole_capacity }}</p>
                        @endif

                        @if ($facility->facility_type == 'individual')
                            @php
                                // Extract room numbers for the overall range
                                $roomNumbers = $facility->facilityAttributes
                                    ->pluck('room_name')
                                    ->filter()
                                    ->map(function ($name) {
                                        return preg_replace('/[^0-9]/', '', $name); // Extract numeric part
                                    })
                                    ->sort()
                                    ->values();

                                // Extract sex restriction
                                $sexRestriction = $facility->facilityAttributes
                                    ->pluck('sex_restriction')
                                    ->filter()
                                    ->first(); // Get the first non-null value
                            @endphp

                            @if ($roomNumbers->isNotEmpty())
                                @php
                                    $firstRoom = $roomNumbers->first();
                                    $lastRoom = $roomNumbers->last();
                                @endphp
                                <p><strong>Room(s) Name: </strong>Room
                                    {{ $firstRoom }}{{ $firstRoom != $lastRoom ? ' - ' . $lastRoom : '' }}</p>
                            @endif
                            @php
                                // Extract and process room details
                                $roomDetails = $facility->facilityAttributes
                                    ->filter(fn($attribute) => $attribute->room_name && $attribute->capacity) // Ensure room_name and capacity exist
                                    ->map(
                                        fn($attribute) => [
                                            'room_number' => preg_replace('/[^0-9]/', '', $attribute->room_name), // Extract numeric part of room name
                                            'capacity' => $attribute->capacity,
                                        ],
                                    )
                                    ->sortBy('room_number') // Sort by room number
                                    ->values();

                                $groupedRooms = $roomDetails->groupBy('capacity'); // Group rooms by capacity
                            @endphp

                            @if ($groupedRooms->isNotEmpty())
                                <p><strong>Room Capacities:</strong></p>
                                <ul>
                                    @foreach ($groupedRooms as $capacity => $rooms)
                                        @php
                                            // Generate room range for the group
                                            $roomNumbers = $rooms->pluck('room_number')->map(fn($num) => "R{$num}");
                                            $range =
                                                $roomNumbers->count() > 1
                                                    ? $roomNumbers->first() . '-' . $roomNumbers->last()
                                                    : $roomNumbers->first();
                                        @endphp
                                        <li>{{ $range }} - {{ $capacity }} People</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($sexRestriction)
                                <p><strong>Sex Restriction: </strong>{{ ucfirst($sexRestriction) }}</p>
                            @endif
                        @endif

                        @if ($facility->facility_type == 'both')
                            @php

                                $roomNumbers = $facility->facilityAttributes
                                    ->pluck('room_name')
                                    ->filter()
                                    ->map(function ($name) {
                                        return preg_replace('/[^0-9]/', '', $name);
                                    })
                                    ->sort()
                                    ->values();

                                $sexRestriction = $facility->facilityAttributes
                                    ->pluck('sex_restriction')
                                    ->filter()
                                    ->first();
                            @endphp

                            @if ($roomNumbers->isNotEmpty())
                                @php
                                    $firstRoom = $roomNumbers->first();
                                    $lastRoom = $roomNumbers->last();
                                @endphp
                                <p><strong>Room(s) Name: </strong>Room
                                    {{ $firstRoom }}{{ $firstRoom != $lastRoom ? ' - ' . $lastRoom : '' }}</p>
                            @endif
                            @php
                                // Extract and process room details
                                $roomDetails = $facility->facilityAttributes
                                    ->filter(fn($attribute) => $attribute->room_name && $attribute->capacity) // Ensure room_name and capacity exist
                                    ->map(
                                        fn($attribute) => [
                                            'room_number' => preg_replace('/[^0-9]/', '', $attribute->room_name), // Extract numeric part of room name
                                            'capacity' => $attribute->capacity,
                                        ],
                                    )
                                    ->sortBy('room_number') // Sort by room number
                                    ->values();

                                $groupedRooms = $roomDetails->groupBy('capacity'); // Group rooms by capacity
                            @endphp

                            @if ($groupedRooms->isNotEmpty())
                                <p><strong>Room Capacities:</strong></p>
                                <ul>
                                    @foreach ($groupedRooms as $capacity => $rooms)
                                        @php
                                            // Generate room range for the group
                                            $roomNumbers = $rooms->pluck('room_number')->map(fn($num) => "R{$num}");
                                            $range =
                                                $roomNumbers->count() > 1
                                                    ? $roomNumbers->first() . '-' . $roomNumbers->last()
                                                    : $roomNumbers->first();
                                        @endphp
                                        <li>{{ $range }} - {{ $capacity }} People</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($sexRestriction)
                                <p><strong>Sex Restriction: </strong>{{ ucfirst($sexRestriction) }}</p>
                            @endif
                        @endif

                    </div>
                    <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                        style="padding: 15px 30px; font-size: 18px; margin-top: 15px; margin-left: 50px;">
                        Show Available Dates
                    </button>
                </div>
            </div>
        @endforeach
    </main>




@endsection

@push('scripts')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Reservation in Progress',
                text: "{{ session('success') }}",
            });
        @endif
        @if (session('error'))
            Swal.fire([
                icon: 'error',
                title: 'Oops...'
                text: "{{ session('error') }}",
            ]);
        @endif
    </script>
@endpush
