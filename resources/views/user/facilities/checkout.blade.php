@extends('layouts.app')
@section('content')
    <style>
        #submit-button {
            --bs-btn-disabled-color: #6c757d;
            --bs-btn-disabled-bg: #e9ecef;
            --bs-btn-hover-color: #6c757d;
            --bs-btn-hover-bg: #e9ecef;
            --bs-btn-hover-border-color: transparent;


            cursor: not-allowed !important;
            pointer-events: none;
        }

        #submit-button:disabled {
            opacity: 0.65;
        }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        // Initialize breadcrumbs array with the Home link
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

        // Handle different pages
        if ($currentRoute === 'rentals.checkout') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-8 col-lg-6">
            <form name="checkout-form" action="{{ route('user.facilities.placeReservation') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">


                @if ($facility->facility_type === 'individual')
                    <input type="hidden" name="facility_attribute_id"
                        value="{{ $facilityAttribute->id ?? $reservationData['facility_attribute_id'] }}">
                    <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                    <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                @elseif($facility->facility_type === 'whole_place')
                    <div class="my-2">
                        <input type="hidden" id="date_from" name="date_from"
                            value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
                        <input type="hidden" id="date_to" name="date_to"
                            value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
                        <div id="selected-date" class="my-3">
                            @if (isset($date_from))
                                <p class="select-date"><strong>Selected Date:</strong> {{ $reservationData['date_from'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @elseif($facility->facility_type === 'both')
                    <div class="my-2">
                        <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">
                        @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity)
                            <input type="hidden" name="facility_attribute_id"
                                value="{{ $facilityAttribute->id ?? $reservationData['facility_attribute_id'] }}">
                        @endif
                        <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                        <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">

                        @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)
                            @foreach ($reservationData['quantity'] as $key => $value)
                                <input type="hidden" name="quantity[{{ $key }}]" value="{{ $value }}">
                            @endforeach
                        @endif

                    </div>
                @endif
                <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Requirements</h5>
                    @if ($facility && $facility->requirements)
                        @php
                            $fileExtension = strtolower(pathinfo($facility->requirements, PATHINFO_EXTENSION));
                            $isImageReq = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                        @endphp
                        @if (!$isImageReq)
                            <p>
                                <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Document
                                </a>
                            </p>
                        @else
                            <p>
                                <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Image
                                </a>
                            </p>
                        @endif
                    @else
                        <p>No Requirements document uploaded.</p>
                    @endif
                    @error('requirements')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="qualification">Qualification Document (PDF/DOC) <small>(Optional)</small></label>
                    {{-- <input type="file" id="qualification" name="qualification" class="form-control"
                        accept=".pdf,.doc,.docx" required> --}}
                    <input type="file" name="qualification" class="form-control accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        onchange="if(this.files[0].size > 10485760) { alert('File must be less than 10MB'); this.value=''; }" />


                    @error('qualification')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="order-summary mb-4">
                    <h3 class="text-center">Reservation Details</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($facility->facility_type === 'individual')
                                <tr>
                                    <td><strong>{{ $roomName }}</strong></td>
                                    <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Date From</strong></td>
                                    <td class="text-end"><strong>{{ $date_from }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Date To</strong></td>
                                    <td class="text-end"><strong>{{ $date_to }}</strong></td>
                                </tr>
                            @elseif ($facility->facility_type === 'both')
                                <tr>
                                    <td><strong>{{ $roomName }}</strong></td>
                                    <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Date From</strong></td>
                                    <td class="text-end"><strong>{{ $date_from }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Date To</strong></td>
                                    <td class="text-end"><strong>{{ $date_to }}</strong></td>
                                </tr>

                                @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)
                                    <tr>
                                        <td><strong>Quantity</strong></td>
                                        <td class="text-end">
                                            <strong>
                                                @if (isset($reservationData['quantity']))
                                                    @if (is_array($reservationData['quantity']))
                                                        @foreach ($reservationData['quantity'] as $priceId => $qty)
                                                            {{ $qty }} (Price ID: {{ $priceId }})<br>
                                                        @endforeach
                                                    @else
                                                        {{ $reservationData['quantity'] }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </strong>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            {{-- @if (($reservationData['total_quantity'] ?? 0) > 0) --}}
                            @if ($hasQuantity)
                                <tr>
                                    <td><strong>Total Quantity</strong></td>
                                    <td class="text-end"><strong>{{ $reservationData['total_quantity'] ?? 'N/A' }}</strong>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Total Price</strong></td>
                                <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {{-- <button type="submit" class="btn btn-warning w-100">Place Reservation</button> --}}
                <button type="submit" class="btn btn-warning w-100" onclick="this.disabled=true; this.form.submit();">Place
                    Reservation</button>

            </form>

        </div>
    </div>
@endsection
