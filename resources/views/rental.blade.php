@extends('layouts.app')
@section('content')
    <!-- Custom CSS for calendar modal and button -->
    <style>
        .close-btn {
            position: absolute;
            top: 15px;
            right: 22px;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--bs-green);
            padding: 0 5px;
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
        if ($currentRoute === 'rentals.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Facilities'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row g-4">
                    @foreach ($facilities as $facility)
                        <div class="col-md-6 d-flex justify-content-center">
                            <div class="card shadow-sm rental-info" style="width: 100%; max-width: 600px; transition: transform 0.3s ease; margin-top: 100px;">
                                <div class="facility-image">
                                    <a href="{{ route('rentals.details', ['rental_slug' => $facility->slug]) }}">
                                           <img src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}" class="card-img-top" style="height: 400px; object-fit: cover;">
                                    </a>
                                </div>  

                                <div class="card-body text-center">
                                    <h2 class="card-title">{{ $facility->name }}</h2>
                                    {{-- <p><strong>Description:</strong> {{ $facility->description }}</p>
                                    <p><strong>Rules & Regulations:</strong> {{ $facility->rules_and_regulations }}</p>
                                    <p><strong>Requirements:</strong> {{ $facility->requirements }}</p> --}}

                                    @if ($facility->featured)
                                        <p><strong>Status:</strong> Featured</p>
                                    @else
                                        <p><strong>Status:</strong> Regular</p>
                                    @endif

                                    <!-- Type Section -->
                                    <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                        <p><strong>Type:</strong></p>
                                        <p>{{ ucfirst($facility->facility_type) }}</p>
                                    </div>

                                    <!-- @if ($facility->sex_restriction)
                                        <div style="display: flex; flex-direction: row; justify-content: center; gap: 5px;">
                                            <p><strong>Sex Restriction:</strong></p>
                                            <p>{{ ucfirst($facility->sex_restriction) }}</p>
                                        </div>
                                    @endif -->

                        

                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
            <div class="d-flex justify-content-center w-100 mt-4">
                {{ $facilities->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </main>

    <form action="{{ route('rentals.index') }}" method="get" id="frmfilter">
        <input type="hidden" name="page" value="{{ $facilities->currentPage() }}">
    </form>
@endsection
