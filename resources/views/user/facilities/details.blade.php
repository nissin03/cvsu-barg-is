@extends('layouts.app')
@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />
    <main class="container my-5">
        <section class="facilities-single container">
            <div class="row">
                <!-- Facility Gallery -->
                <div class="col-lg-7">
                    <div class="facility-gallery">
                        <div class="gallery-wrapper">
                            <div class="thumbnails">
                                <div class="swiper-container thumbnail-swiper">
                                    <div class="swiper-wrapper">
                                        <!-- Main Image Thumbnail -->
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="thumbnail-img"
                                                src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}"
                                                height="204">
                                        </div>

                                        <!-- Loop through gallery images for thumbnails -->
                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('storage/' . trim($gimg)) }}" alt="{{ $facility->name }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- Main Image -->
                            <div class="main-image">
                                <div class="swiper-container main-swiper">
                                    <div class="swiper-wrapper">
                                        <!-- Main Image -->
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="h-auto main-img"
                                                src="{{ asset('storage/' . $facility->image) }}"
                                                alt="{{ $facility->name }}">
                                            <a data-fancybox="gallery" href="{{ asset('storage/' . $facility->image) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $facility->name }}"></a>
                                        </div>

                                        <!-- Loop through gallery images for main images -->
                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img"
                                                    src="{{ asset('storage/' . trim($gimg)) }}"
                                                    alt="{{ $facility->name }}">
                                                <a data-fancybox="gallery" href="{{ asset('storage/' . trim($gimg)) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $facility->name }}"></a>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Navigation buttons -->
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental Info -->
                <div class="col-lg-5">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @elseif (Session::has('error'))
                        <p class="alert alert-danger">{{ Session::get('error') }}</p>
                    @endif
                    <h1 class="facilities-single__name">{{ $facility->name }}</h1>

                    <form action="{{ route('facility.reserve') }}" method="POST" style="margin: 0" id="reservation-form">
                        @csrf
                        <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                        <input type="hidden" name="total_price" id="total-price-field" value="0">
                        <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">

                        @if ($facility->facility_type === 'individual')
                            @include('components.facility_individual')
                        @endif

                        @if ($facility->facility_type == 'whole_place')
                            @include('components.facility_whole_place')
                        @endif


                        @if ($facility->facility_type == 'both')
                            <div id="dynamic_prices_container">
                                <div id="prices_display">
                                    <div data-price-type="individual">
                                        @foreach ($pricesWithAttributes->where('price_type', 'individual') as $price)
                                            <h4><strong>{{ $price->name }}: </strong>
                                                <span class="product-type text-primary">&#8369;
                                                    {{ number_format($price->value, 2) }}
                                                </span>
                                            </h4>
                                            <p>
                                                @if ($price->is_based_on_days)
                                                    <span class="badge">Per Day</span>
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>

                                    <div data-price-type="whole">
                                        @foreach ($pricesWithAttributes->where('price_type', 'whole') as $price)
                                            <h4><strong>{{ $price->name }}: </strong>
                                                <span class="product-type text-primary">&#8369;
                                                    {{ number_format($price->value, 2) }}
                                                </span>
                                            </h4>
                                            <p>
                                                @if ($price->is_based_on_days)
                                                    <span class="badge">Per Day</span>
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>

                                @foreach ($pricesWithoutAttributes as $price)
                                    <h4><strong>{{ $price->name }}: </strong>
                                        <span class="product-type text-primary">&#8369;
                                            {{ number_format($price->value, 2) }}</span>
                                    </h4>
                                    <p>
                                        @if ($price->is_based_on_days)
                                            <span class="badge">Per Day</span>
                                        @endif
                                    </p>
                                @endforeach
                            </div>

                            <div>
                                <!-- Rooms Display Based on Selection -->
                                <div id="rooms_display" class="mt-4">
                                    <!-- Shared Rooms -->
                                    <div id="shared_rooms" style="display: none;">
                                        <h4>Shared Rooms</h4>
                                        <ul class="list-group">
                                            @foreach ($facility->facilityAttributes as $room)
                                                @if ($room->remaining_capacity > 0)
                                                    <li class="list-group-item">
                                                        {{ $room->room_name }} - Capacity: {{ $room->remaining_capacity }}
                                                        out
                                                        of {{ $room->capacity }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Solo Rooms -->
                                    <div id="solo_rooms" style="display: none;">
                                        <h4>Solo Rooms</h4>
                                        <ul class="list-group">
                                            @foreach ($facility->facilityAttributes as $room)
                                                @if ($room->remaining_capacity == $room->capacity)
                                                    <li class="list-group-item">
                                                        {{ $room->room_name }} - Capacity: {{ $room->capacity }} out of
                                                        {{ $room->capacity }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>



                            @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity)
                                <input type="hidden" name="price_type" id="price_type" value="">
                                <input type="hidden" name="price_value" id="price_value" value="">
                                <input type="hidden" name="price_id" id="price_id" value="">
                                <input type="hidden" name="room_id" id="room_id" value="">
                                <div class="reservation-section">
                                    <div class="my-2">
                                        <label for="calendar"><strong>Select Reservation Date:</strong></label>
                                        <div id="calendar"></div>

                                        <!-- Hidden input fields to store selected dates -->
                                        <input type="hidden" id="date_from" name="date_from"
                                            value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
                                        <input type="hidden" id="date_to" name="date_to"
                                            value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">

                                        <div id="selected-date" class="my-3">
                                            @if (isset($reservationData['date_from']))
                                                <p class="select-date"><strong>Selected Date From:</strong>
                                                    {{ $reservationData['date_from'] }}</p>
                                            @endif
                                            @if (isset($reservationData['date_to']))
                                                <p class="select-date"><strong>Selected Date To:</strong>
                                                    {{ $reservationData['date_to'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <label><strong>Select Type:</strong></label>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" id="solo" name="reservation_type"
                                                    value="solo" class="form-check-input">
                                                <label for="solo" class="form-check-label">Solo</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" id="shared" name="reservation_type"
                                                    value="shared" class="form-check-input">
                                                <label for="shared" class="form-check-label">Shared</label>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" id="reset_button"
                                                class="btn btn-secondary btn-sm">Reset</button>
                                        </div>
                                    </div>
                                </div>

                                @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                                    <div id="individual_inputs" style="display:none;">
                                        <label><strong>Set Quantity:</strong></label>
                                        <div class="form-floating mb-3">
                                            <input id="internal_quantity" type="number"
                                                class="form-control form-control_gray @error('internal_quantity') is-invali10.d @enderror"
                                                name="internal_quantity" value="{{ old('internal_quantity') }}">
                                            <label for="internal_quantity">
                                                Enter Internal Quantity
                                            </label>
                                        </div>

                                        <div class="form-floating mb-3">
                                            <input id="external_quantity" type="number"
                                                class="form-control form-control_gray @error('external_quantity') is-invalid @enderror"
                                                name="external_quantity" value="{{ old('external_quantity') }}">
                                            <label for="external_quantity">
                                                Enter External Quantity
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <div id="shared_inputs" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="shared_room_select" name="shared_room_select" class="form-select">
                                            <option value="" disabled selected>Select Shared Room</option>
                                            @foreach ($facility->facilityAttributes as $room)
                                                @if ($room->remaining_capacity > 0)
                                                    <option value="{{ $room->id }}">
                                                        {{ $room->room_name }} - Capacity: {{ $room->remaining_capacity }}
                                                        out of {{ $room->capacity }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <label for="shared_room_select">Shared Rooms:</label>
                                    </div>
                                </div>

                                <div id="shared_type_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="shared_price_select" name="shared_price_select" class="form-select">
                                            <option value="" disabled selected>Select Shared Type</option>
                                            @foreach ($facility->prices->where('price_type', 'individual') as $price)
                                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="shared_price_select">Shared Type:</label>
                                    </div>
                                </div>

                                <!-- Solo Room Select -->
                                <div id="solo_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="solo_room_select" name="solo_room_select" class="form-select">
                                            <option value="" disabled selected>Select Solo Room</option>
                                            @foreach ($facility->facilityAttributes as $room)
                                                @if ($room->remaining_capacity == $room->capacity)
                                                    <option value="{{ $room->id }}">
                                                        {{ $room->room_name }} - Capacity: {{ $room->capacity }}
                                                        out of {{ $room->capacity }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <label for="solo_room_select">Solo Rooms:</label>
                                    </div>
                                </div>

                                <!-- Solo Price Select -->
                                <div id="solo_type_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="solo_price_select" name="solo_price_select" class="form-select">
                                            <option value="" disabled selected>Select Solo Type</option>
                                            @foreach ($facility->prices->where('price_type', 'whole') as $price)
                                                <option value="{{ $price->id }}">{{ $price->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="solo_price_select">Solo Type:</label>
                                    </div>
                                </div>
                            @endif

                            @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>Oops! Something went wrong.</strong>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <input type="hidden" name="price_type" id="price_type" value="">
                                <input type="hidden" name="price_id" id="price_id" value="">
                                <input type="hidden" name="total_price" id="total_price_input" value="0">

                                <div style="margin-bottom: 15px;">
                                    <label><strong>Select Type:</strong></label>
                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <div>
                                                <input type="radio" id="individual" name="reservation_type"
                                                    value="individual">
                                                <label for="individual">Individual</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="exclusive" name="reservation_type"
                                                    value="exclusive">
                                                <label for="exclusive">Exclusive</label>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" id="reset_button" class="btn btn-shop btn-addtocart"
                                                style="font-size: 10px;">Reset</button>
                                        </div>
                                    </div>
                                </div>

                                @if ($facility->prices->where('is_there_a_quantity', true)->where('price_type', 'individual')->count() > 0)
                                    <div id="individual_inputs" style="display: none;">
                                        <label><strong>Set Quantity:</strong></label>
                                        @foreach ($facility->prices->where('is_there_a_quantity', true)->where('price_type', 'individual') as $price)
                                            <div class="form-floating mb-3">
                                                <input id="quantity_{{ $price->id }}" type="number"
                                                    class="form-control quantity-input"
                                                    name="quantity[{{ $price->id }}]"
                                                    value="{{ old('quantity.' . $price->id) }}" min="0"
                                                    data-price="{{ $price->value }}">
                                                <label for="quantity_{{ $price->id }}">Enter Quantity for
                                                    {{ $price->name }}</label>
                                            </div>
                                        @endforeach
                                        {{-- <p><strong>Total Price:</strong> ₱<span id="grand_total">0.00</span></p> --}}
                                    </div>
                                @endif


                                <div id="exclusive_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="exclusive_type" name="exclusive_type"
                                            class="form-select @error('exclusive_type') is-invalid @enderror">
                                            <option value="" disabled selected>Select Exclusive Type</option>
                                            @foreach ($facility->prices->where('price_type', 'whole') as $price)
                                                <option value="{{ $price->id }}" data-price="{{ $price->value }}">
                                                    {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="exclusive_type">Exclusive Type:</label>

                                    </div>
                                </div>
                                <div class="reservation-section">
                                    <div class="my-2">
                                        <div id="calendar"></div>
                                        <input type="hidden" id="date_from" name="date_from"
                                            value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
                                        <input type="hidden" id="date_to" name="date_to"
                                            value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
                                        <div id="selected-date" class="my-3">
                                            @if (isset($date_from))
                                                {{-- <p class="select-date"><strong>Selected Date:</strong> {{ $date_from }}</p> --}}
                                                <p class="select-date"><strong>Selected Date:</strong>
                                                    {{ $reservationData['date_from'] }}</p>
                                            @endif
                                        </div>


                                    </div>
                                </div>
                            @endif
                            <div id="total_price" style="font-weight: bold; "><strong>Total Price:</strong> &#8369; 0.00
                            </div>
                        @endif

                        <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                            style="padding: 15px 30px; font-size: 18px">
                            Reserve
                        </button>
                    </form>


                </div>
        </section>
        <div class="rental-single__details-tab" style="margin-top: 30px;">
            <ul class="nav nav-tabs" id="myTab" role="tablist"
                style="display:flex; justify-content: center; gap: 7px;">
                <!-- Description Tab -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link nav-link_underscore active p-2" id="tab-description-tab" data-bs-toggle="tab"
                        href="#tab-description" role="tab" aria-controls="tab-description" aria-selected="true">
                        Description
                    </a>
                </li>

                <!-- Rules Tab -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link nav-link_underscore p-2" id="tab-rules-tab" data-bs-toggle="tab"
                        href="#tab-rules" role="tab" aria-controls="tab-rules" aria-selected="false">
                        Rules and Regulation
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Description Tab Content -->
                <div class="tab-pane fade show active" id="tab-description" role="tabpanel"
                    aria-labelledby="tab-description-tab">
                    <div class="rental-single__description">
                        {{ $facility->description }}
                    </div>
                </div>

                <!-- Rules and Regulations Tab Content -->
                <div class="tab-pane fade" id="tab-rules" role="tabpanel" aria-labelledby="tab-rules-tab">
                    <div class="rental-single__rules mb-4">
                        <div class="rules-header" data-bs-toggle="collapse" data-bs-target="#rules-content"
                            aria-expanded="false">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    Rules and Regulations
                                </h5>
                                <div class="d-flex align-items-center">
                                    <span class="rules-toggle-text me-2">View Details</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="chevron-icon">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="collapse" id="rules-content">
                            <div class="rules-container bg-white p-4 border rounded-bottom">
                                <div class="rules-content">
                                    @php
                                        $rules = explode("\n", $facility->rules_and_regulations);
                                    @endphp

                                    <div class="rules-sections">
                                        @foreach ($rules as $index => $rule)
                                            @if (trim($rule) !== '')
                                                <div class="rule-item d-flex align-items-start mb-3">
                                                    <div class="rule-number me-3">
                                                        <span
                                                            class="badge bg-primary rounded-circle">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="rule-text">
                                                        {!! trim($rule) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="rules-footer mt-4 pt-3 border-top">
                                        <div class="d-flex align-items-center text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="me-2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="16" x2="12" y2="12">
                                                </line>
                                                <line x1="12" y1="8" x2="12.01" y2="8">
                                                </line>
                                            </svg>
                                            <small>Please read all rules carefully before making a reservation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agree to Rules Checkbox -->
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="agreeToRules" required>
                        <label class="form-check-label" for="agreeToRules">
                            I agree to the <a href="#tab-rules" data-bs-toggle="collapse"
                                data-bs-target="#rules-content">Rules and Regulations</a>.
                        </label>
                        <div class="invalid-feedback">
                            You must agree to the rules and regulations before proceeding.
                        </div>
                    </div>
                </div>

                <!-- Form submission example -->

            </div>
        </div>
    </main>


    <hr class="mt-5 text-secondary" />
@endsection
@push('scripts')


    <script>
        //IMAGE//
        document.addEventListener('DOMContentLoaded', function() {

            const mainSwiper = new Swiper('.main-swiper', {
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                loop: true,
                on: {
                    slideChange: function() {
                        const activeIndex = this.realIndex;
                        document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                            thumbnail.classList.toggle('active', index === activeIndex);
                        });
                    }
                }
            });

            const thumbnailSwiper = new Swiper('.thumbnail-swiper', {
                direction: 'vertical',
                slidesPerView: 'auto',
                spaceBetween: 10,
            });

            document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', function() {
                    mainSwiper.slideToLoop(index);
                    document.querySelectorAll('.thumbnail-img').forEach((thumb) => thumb.classList
                        .remove('active'));
                    this.classList.add('active');
                });


                function validateForm() {
                    const checkbox = document.getElementById('agreeToRules');
                    if (!checkbox.checked) {
                        checkbox.setCustomValidity('You must agree to the rules and regulations.');
                        return false;
                    }
                    checkbox.setCustomValidity('');
                    return true;
                }

            });

        });
    </script>

    @if ($facility->facility_type == 'individual')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const hasQuantityPricing = @json($facility->prices->where('is_there_a_quantity', true)->isNotEmpty());
                const reserveBtn = document.getElementById('reserve-btn');

                function toggleReserveButton() {
                    let hasValidInput = false;

                    document.querySelectorAll('.quantity-input').forEach(input => {
                        const qty = parseInt(input.value || 0);
                        if (qty > 0) {
                            hasValidInput = true;
                        }
                    });

                    reserveBtn.disabled = !hasValidInput;
                }

                if (hasQuantityPricing) {
                    function computeDynamicTotal() {
                        let total = 0;

                        document.querySelectorAll('.quantity-input').forEach(input => {
                            const qty = parseInt(input.value || 0);
                            const price = parseFloat(input.dataset.price || 0);
                            total += qty * price;
                        });

                        document.getElementById('total-price-field').value = total.toFixed(2);
                        document.getElementById('computed-total').innerHTML = '₱' + total.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                        toggleReserveButton();
                    }

                    document.querySelectorAll('.quantity-input').forEach(input => {
                        input.addEventListener('input', computeDynamicTotal);
                    });

                    computeDynamicTotal();

                } else {
                    const individualPrice = @json($individualPrice);
                    document.getElementById('computed-total').textContent = `₱${individualPrice.toLocaleString(undefined, {
                        minimumFractionDigits: 2
                        })}`;
                    document.getElementById('total-price-field').value = individualPrice.toFixed(2);
                    reserveBtn.disabled = false;
                }
            });
        </script>
    @endif

    @if ($facility->facility_type == 'both')
        @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->capacity)
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const soloRadio = document.getElementById('solo');
                    const sharedRadio = document.getElementById('shared');
                    const resetButton = document.getElementById('reset_button');
                    const dateFromEl = document.getElementById('date_from');
                    const dateToEl = document.getElementById('date_to');
                    const pricesDisplay = document.getElementById('prices_display');
                    const submitButton = document.getElementById('submit-button');
                    const selectedDateEl = document.getElementById('selected-date');
                    const sharedInputs = document.getElementById('shared_inputs');
                    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                        const individualInputs = document.getElementById('individual_inputs');
                    @endif

                    const soloTypeDropdown = document.getElementById('solo_type_dropdown');

                    const totalPriceEl = document.getElementById('total_price');
                    const sharedTypeDropdown = document.getElementById('shared_type_dropdown');
                    const soloDropdown = document.getElementById('solo_dropdown');
                    const roomsDisplay = document.getElementById('rooms_display');
                    const sharedRooms = document.getElementById('shared_rooms');
                    const soloRooms = document.getElementById('solo_rooms');
                    const calendarEl = document.getElementById('calendar');


                    const sharedRoomSelect = document.getElementById('shared_room_select');
                    const sharedPriceSelect = document.getElementById('shared_price_select');

                    const soloRoomSelect = document.getElementById('solo_room_select');
                    const soloPriceSelect = document.getElementById('solo_price_select');

                    const prices = @json($pricesWithAttributes);
                    const facilityId = "{{ $facility->id }}";

                    // This function calls your "calculatePrice" route via AJAX
                    function ajaxCalculatePrice() {
                        const dateFrom = dateFromEl.value;
                        const dateTo = dateToEl.value;


                        if (!dateFrom || !dateTo) {
                            totalPriceEl.innerText = '';
                            return;
                        }

                        let priceType = null;
                        if (soloRadio.checked) {
                            priceType = 'whole';
                        } else if (sharedRadio.checked) {
                            priceType = 'individual';
                        } else {
                            totalPriceEl.innerText = '';
                            return;
                        }

                        // Decide which <select> to read for the Price ID
                        let priceId = null;
                        if (priceType === 'whole' && soloPriceSelect) {
                            priceId = soloPriceSelect.value;
                        } else if (priceType === 'individual' && sharedPriceSelect) {
                            priceId = sharedPriceSelect.value;
                        }

                        let roomId = null;
                        // if (priceType === 'whole') {
                        //     roomId = document.getElementById('solo_room_select').value;
                        // } else if (priceType === 'individual') {
                        //     roomId = document.getElementById('shared_room_select').value;
                        // }
                        if (priceType === 'whole' && soloRoomSelect) {
                            roomId = soloRoomSelect.value;
                        } else if (priceType === 'individual' && sharedRoomSelect) {
                            roomId = sharedRoomSelect.value;
                        }

                        if (!priceId || !roomId) {
                            totalPriceEl.innerText = '';
                            return;
                        }
                        fetch("{{ route('facilities.calculatePrice') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    facility_id: facilityId,
                                    date_from: dateFrom,
                                    date_to: dateTo,
                                    price_type: priceType,
                                    price_id: priceId,
                                    room_id: roomId,
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    totalPriceEl.innerText =
                                        'Total Price: ₱' + parseFloat(data.total_price).toFixed(2);

                                    document.getElementById('price_type').value = priceType;
                                    document.getElementById('price_id').value = priceId;
                                    document.getElementById('room_id').value = roomId;
                                    document.getElementById('total_price').value = data.total_price;
                                } else {
                                    console.error(data.message || 'Calculation failed.');
                                }
                            })
                            .catch(error => console.error('Error calculating price:', error));
                    }

                    soloRadio.addEventListener('change', ajaxCalculatePrice);
                    sharedRadio.addEventListener('change', ajaxCalculatePrice);

                    if (sharedRoomSelect) {
                        sharedRoomSelect.addEventListener('change', ajaxCalculatePrice);
                    }
                    if (sharedPriceSelect) {
                        sharedPriceSelect.addEventListener('change', ajaxCalculatePrice);
                    }
                    if (soloRoomSelect) {
                        soloRoomSelect.addEventListener('change', ajaxCalculatePrice);
                    }
                    if (soloPriceSelect) {
                        soloPriceSelect.addEventListener('change', ajaxCalculatePrice);
                    }

                    dateFromEl.addEventListener('change', ajaxCalculatePrice);
                    dateToEl.addEventListener('change', ajaxCalculatePrice);

                    // store current selection so we can restore them
                    let sharedTypeValue = '';
                    let soloTypeValue = '';

                    // Show/hide logic for prices
                    function updatePrices(type) {
                        pricesDisplay.innerHTML = ''; // Clear previous
                        const filteredPrices = prices.filter(p => p.price_type === type);

                        if (filteredPrices.length > 0) {
                            filteredPrices.forEach(p => {
                                const priceHTML = `
                    <h4><strong>${p.name}:</strong>
                        <span class="product-type text-primary">&#8369;
                            ${parseFloat(p.value).toFixed(2)}
                        </span>
                    </h4>
                    <p>${p.is_based_on_days ? '<span class="badge">Per Day</span>' : ''}</p>
                `;
                                pricesDisplay.innerHTML += priceHTML;
                            });
                        } else {
                            pricesDisplay.innerHTML = '<p>No prices available for this selection.</p>';
                        }
                    }

                    function hideAll() {
                        sharedInputs.style.display = 'none';
                        @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                            individualInputs.style.display = 'none';
                        @endif
                        soloDropdown.style.display = 'none';
                        soloTypeDropdown.style.display = 'none';
                        sharedTypeDropdown.style.display = 'none';
                        roomsDisplay.style.display = 'none';
                        sharedRooms.style.display = 'none';
                        soloRooms.style.display = 'none';
                    }

                    // When user clicks the "Solo" radio
                    soloRadio.addEventListener('change', function() {
                        if (this.checked) {

                            if (sharedPriceSelect) {
                                sharedTypeValue = sharedPriceSelect.value;
                            }

                            if (soloPriceSelect) {
                                soloPriceSelect.value = soloTypeValue;
                            }

                            updatePrices('whole');
                            hideAll();


                            soloDropdown.style.display = 'block';
                            soloTypeDropdown.style.display = 'block';
                            roomsDisplay.style.display = 'block';
                            soloRooms.style.display = 'block';
                        }
                    });


                    sharedRadio.addEventListener('change', function() {
                        if (this.checked) {

                            if (soloPriceSelect) {
                                soloTypeValue = soloPriceSelect.value;
                            }

                            if (sharedPriceSelect) {
                                sharedPriceSelect.value = sharedTypeValue;
                            }

                            updatePrices('individual');
                            hideAll();


                            sharedInputs.style.display = 'block';
                            sharedTypeDropdown.style.display = 'block';
                            @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                                individualInputs.style.display = 'block';
                            @endif
                            roomsDisplay.style.display = 'block';
                            sharedRooms.style.display = 'block';
                        }
                    });

                    // Reset button
                    resetButton.addEventListener('click', function() {
                        document.getElementById('date_from').value = '';
                        document.getElementById('date_to').value = '';
                        selectedDateEl.innerHTML = '';
                        totalPriceEl.innerText = '';

                        if (soloRoomSelect) soloRoomSelect.value = '';
                        if (soloPriceSelect) soloPriceSelect.value = '';
                        if (sharedRoomSelect) sharedRoomSelect.value = '';
                        if (sharedPriceSelect) sharedPriceSelect.value = '';

                        calendar.unselect();

                        if (submitButton) {
                            submitButton.disabled = true;
                        }

                        calendar.getEvents().forEach(function(event) {
                            if (event.display === 'background') {
                                event.remove();
                            }
                        });

                        soloRadio.checked = false;
                        sharedRadio.checked = false;
                        sharedTypeValue = '';
                        soloTypeValue = '';

                        hideAll();
                        pricesDisplay.innerHTML = '<p>Please select a type to view prices.</p>';
                    });

                    // Setup FullCalendar
                    var today = new Date();
                    var startDate = new Date();
                    startDate.setDate(today.getDate() + 3);

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        selectable: true,
                        selectMirror: true,
                        timeZone: 'local',
                        select: function(info) {
                            var selectedDate = info.start;
                            var minDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate
                                .getDate());

                            if (selectedDate >= minDate) {
                                var year = selectedDate.getFullYear();
                                var month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                                var day = String(selectedDate.getDate()).padStart(2, '0');
                                var formattedDate = `${year}-${month}-${day}`;

                                if (!dateFromEl.value) {

                                    dateFromEl.value = formattedDate;
                                    selectedDateEl.innerHTML =
                                        '<p class="select-date"><strong>Selected Date From:</strong> ' +
                                        formattedDate + '</p>';
                                } else if (!dateToEl.value && formattedDate >= dateFromEl.value) {

                                    dateToEl.value = formattedDate;
                                    selectedDateEl.innerHTML +=
                                        '<p class="select-date"><strong>Selected Date To:</strong> ' +
                                        formattedDate + '</p>';

                                    if (submitButton) {
                                        submitButton.disabled = false;
                                    }
                                    ajaxCalculatePrice();
                                } else {
                                    alert('Date To cannot be before Date From.');
                                    calendar.unselect();
                                }


                                calendar.getEvents().forEach(function(event) {
                                    if (event.display === 'background') {
                                        event.remove();
                                    }
                                });


                                var dateFrom = dateFromEl.value;
                                var dateTo = dateToEl.value;

                                if (dateFrom) {
                                    var dateFromObj = new Date(dateFrom);
                                    calendar.addEvent({
                                        title: 'Selected Date From',
                                        start: dateFromObj,
                                        allDay: true,
                                        display: 'background',
                                        backgroundColor: '#B0E0E6'
                                    });
                                }
                                if (dateTo) {
                                    var dateToObj = new Date(dateTo);
                                    calendar.addEvent({
                                        title: 'Selected Date To',
                                        start: dateToObj,
                                        allDay: true,
                                        display: 'background',
                                        backgroundColor: '#FFB6C1'
                                    });
                                }
                            } else {
                                alert('Please select a date starting from ' + minDate.toISOString().split('T')[
                                    0]);
                                calendar.unselect();
                            }
                        },
                        validRange: {
                            start: startDate.toISOString().split('T')[0],
                        },
                        headerToolbar: {
                            left: 'prev,next',
                            center: 'title',
                            right: ''
                        },
                        dateClick: function(info) {
                            // no-op
                        },
                    });

                    calendar.render();

                    // Ensure the submit button is disabled initially
                    if (submitButton) {
                        submitButton.disabled = true;
                    }
                });
            </script>
        @endif
        @if ($facility->facilityAttributes->first() && $facility->facilityAttributes->first()->whole_capacity)
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const reservationTypeRadios = document.querySelectorAll('input[name="reservation_type"]');
                    const individualInputs = document.getElementById('individual_inputs');
                    const exclusiveDropdown = document.getElementById('exclusive_dropdown');
                    const resetButton = document.getElementById('reset_button');
                    const totalPriceElement = document.getElementById('total_price');
                    const totalPriceInput = document.getElementById('total_price_input');
                    const priceTypeInput = document.getElementById('price_type');
                    const priceIdInput = document.getElementById('price_id');

                    setTimeout(() => {
                        let alertBox = document.querySelector('.alert');
                        if (alertBox) {
                            alertBox.classList.remove('show');
                            alertBox.classList.add('fade');
                            setTimeout(() => alertBox.remove(), 500);
                        }
                    }, 5000);


                    const updateTotalPrice = (total, type, priceId) => {
                        totalPriceElement.innerHTML = `<strong>Total Price:</strong> &#8369; ${total.toFixed(2)}`;
                        totalPriceInput.value = total.toFixed(2);
                        priceTypeInput.value = type;
                        priceIdInput.value = priceId;
                    };
                    const calculateGrandTotal = () => {
                        let grandTotal = 0;
                        let selectedPriceId = null;

                        document.querySelectorAll('.quantity-input').forEach(input => {
                            const price = parseFloat(input.dataset.price);
                            const quantity = parseFloat(input.value) || 0;
                            if (quantity > 0) {
                                grandTotal += price * quantity;
                                selectedPriceId = input.id.replace('quantity_', '');
                            }
                        });

                        updateTotalPrice(grandTotal, 'individual', selectedPriceId);
                    };

                    reservationTypeRadios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (this.value === 'individual') {
                                individualInputs.style.display = 'block';
                                exclusiveDropdown.style.display = 'none';
                                calculateGrandTotal();
                            } else {
                                individualInputs.style.display = 'none';
                                exclusiveDropdown.style.display = 'block';
                                updateTotalPrice(0, 'exclusive', ''); // Reset when switching types
                            }
                        });
                    });

                    const exclusiveTypeDropdown = document.getElementById('exclusive_type');
                    if (exclusiveTypeDropdown) {
                        exclusiveTypeDropdown.addEventListener('change', function() {
                            const selectedOption = exclusiveTypeDropdown.options[exclusiveTypeDropdown
                                .selectedIndex];
                            const price = parseFloat(selectedOption.dataset.price || 0);
                            const priceId = selectedOption.value;
                            updateTotalPrice(price, 'whole', priceId);
                        });
                    }


                    document.querySelectorAll('.quantity-input').forEach(input => {
                        input.addEventListener('input', calculateGrandTotal);
                    });
                    resetButton.addEventListener('click', function() {
                        reservationTypeRadios.forEach(radio => (radio.checked = false));
                        individualInputs.style.display = 'none';
                        exclusiveDropdown.style.display = 'none';
                        document.querySelectorAll('.quantity-input').forEach(input => {
                            input.value = '';
                        });
                        if (exclusiveTypeDropdown) {
                            exclusiveTypeDropdown.value = '';
                        }
                        updateTotalPrice(0, '', '');
                    });
                });



                document.addEventListener('DOMContentLoaded', function() {
                    const individualRadio = document.getElementById('individual');
                    const exclusiveRadio = document.getElementById('exclusive');
                    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                        const individualInputs = document.getElementById('individual_inputs');
                    @endif
                    const exclusiveDropdown = document.getElementById('exclusive_dropdown');
                    const resetButton = document.getElementById('reset_button');
                    const totalPriceEl = document.getElementById('total_price');
                    const pricesDisplay = document.getElementById('prices_display');

                    // Get all price elements
                    const individualPrices = pricesDisplay.querySelectorAll('[data-price-type="individual"]');
                    const wholePrices = pricesDisplay.querySelectorAll('[data-price-type="whole"]');

                    if (!individualRadio || !exclusiveRadio || !resetButton || !totalPriceEl || !pricesDisplay) {
                        console.error("One or more elements are missing. Check your HTML element IDs.");
                        return;
                    }

                    // Function to hide all sections
                    function hideAll() {
                        @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                            individualInputs.style.display = 'none';
                        @endif
                        exclusiveDropdown.style.display = 'none';
                        console.log("Hiding all sections.");
                    }

                    // Function to show/hide prices based on type
                    function togglePrices(showIndividual) {
                        const individualPrices = pricesDisplay.querySelectorAll('[data-price-type="individual"]');
                        const wholePrices = pricesDisplay.querySelectorAll('[data-price-type="whole"]');

                        individualPrices.forEach(price => {
                            price.style.display = showIndividual ? 'block' : 'none';
                        });

                        wholePrices.forEach(price => {
                            price.style.display = showIndividual ? 'none' : 'block';
                        });
                    }

                    // Reset total price
                    function resetTotalPrice() {
                        totalPriceEl.innerHTML = `<strong>Total Price:</strong> &#8369; 0.00`;
                    }

                    // Event listener for Individual radio button
                    individualRadio.addEventListener('change', function() {
                        if (individualRadio.checked) {
                            hideAll();
                            @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
                                individualInputs.style.display = 'block';
                            @endif
                            togglePrices(true); // Show individual prices
                            console.log("Showing individual inputs and prices.");
                            resetTotalPrice();
                        }
                    });

                    // Event listener for Exclusive radio button
                    exclusiveRadio.addEventListener('change', function() {
                        if (exclusiveRadio.checked) {
                            hideAll();
                            exclusiveDropdown.style.display = 'block';
                            togglePrices(false); // Show whole/exclusive prices
                            console.log("Showing exclusive dropdown and prices.");
                            resetTotalPrice();
                        }
                    });

                    // Event listener for Reset button
                    resetButton.addEventListener('click', function() {
                        hideAll();
                        individualRadio.checked = false;
                        exclusiveRadio.checked = false;
                        // Show all prices on reset
                        individualPrices.forEach(price => price.style.display = 'block');
                        wholePrices.forEach(price => price.style.display = 'block');
                        resetTotalPrice();
                        console.log("Reset button clicked. All options reset.");
                    });

                    // Initialize by hiding all sections
                    hideAll();

                    // Event listener for dropdown change to update total price
                    const exclusiveTypeDropdown = document.getElementById('exclusive_type');
                    if (exclusiveTypeDropdown) {
                        exclusiveTypeDropdown.addEventListener('change', function() {
                            const selectedOption = exclusiveTypeDropdown.options[exclusiveTypeDropdown
                                .selectedIndex];
                            const price = parseFloat(selectedOption.dataset.price || 0);
                            totalPriceEl.innerHTML = `<strong>Total Price:</strong> &#8369; ${price.toFixed(2)}`;
                            console.log("Updated total price: ", price);
                        });
                    }

                    // FullCalendar setup
                    const calendarEl = document.getElementById('calendar');
                    const submitButton = document.getElementById('submit-button');
                    const selectedDateEl = document.getElementById('selected-date');

                    if (calendarEl) {
                        const today = new Date();
                        const startDate = new Date();
                        startDate.setDate(today.getDate() + 4);

                        const calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            selectable: true,
                            selectMirror: true,
                            timeZone: 'local',
                            select: function(info) {
                                const selectedDate = info.start;
                                const selected = new Date(selectedDate.getFullYear(), selectedDate.getMonth(),
                                    selectedDate.getDate());
                                const minDate = new Date(startDate.getFullYear(), startDate.getMonth(),
                                    startDate.getDate());

                                if (selected >= minDate) {
                                    const year = selected.getFullYear();
                                    const month = String(selected.getMonth() + 1).padStart(2, '0');
                                    const day = String(selected.getDate()).padStart(2, '0');
                                    const formattedDate = `${year}-${month}-${day}`;

                                    document.getElementById('date_from').value = formattedDate;
                                    document.getElementById('date_to').value = formattedDate;

                                    if (selectedDateEl) {
                                        selectedDateEl.innerText = 'Selected Date: ' + formattedDate;
                                    }

                                    if (submitButton) {
                                        submitButton.disabled = false;
                                    }

                                    calendar.getEvents().forEach(function(event) {
                                        if (event.display === 'background') {
                                            event.remove();
                                        }
                                    });

                                    calendar.addEvent({
                                        title: 'Selected',
                                        start: selectedDate,
                                        allDay: true,
                                        display: 'background',
                                        backgroundColor: '#B0E0E6'
                                    });
                                } else {
                                    alert('Please select a date starting from ' + minDate.toISOString().split(
                                        'T')[0]);
                                    calendar.unselect();
                                }
                            },
                            validRange: {
                                start: startDate.toISOString().split('T')[0],
                            },
                            headerToolbar: {
                                left: 'prev,next',
                                center: 'title',
                                right: ''
                            },
                            dateClick: function(info) {
                                // Do nothing
                            },
                        });

                        calendar.render();
                    }
                });
            </script>
        @endif
    @endif

    @if ($facility->facility_type === 'whole_place')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const reservationEvents = @json($reservations);
                const pendingDates = reservationEvents.map(event => event.start);

                const calendarEl = document.getElementById('calendar');
                const submitButton = document.getElementById('submit-button');
                const selectedDateEl = document.getElementById('selected-date');
                const reserveBtn = document.getElementById('reserve-btn');
                const clientTypeDropdown = document.getElementById('client_type');
                const totalPriceElement = document.getElementById('total-price').querySelector('span');
                const reservationForm = document.getElementById('reservation-form');
                const totalPriceField = document.getElementById('total-price-field');


                const errorContainer = document.createElement('div');
                errorContainer.id = 'form-errors';
                errorContainer.style.color = 'red';
                reservationForm.insertBefore(errorContainer, reserveBtn);


                let today = new Date();
                // let startDate = new Date();
                let startDate = new Date(today);
                // startDate.setDate(today.getDate() + 4);
                startDate.setDate(today.getDate() + 4);

                let maxMonthDate = new Date(today.getFullYear(), today.getMonth() + 4, 0)
                const formatDate = (date) => date.toISOString().split('T')[0];
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    selectable: true,
                    selectMirror: true,
                    timeZone: 'local',

                    events: reservationEvents,

                    eventContent: function(arg) {
                        return {
                            html: `<div style="color: white; background: ${arg.event.backgroundColor}; padding: 2px; border-radius: 3px; font-size: 11px;">${arg.event.title}</div>`
                        };
                    },

                    select: function(info) {
                        const selectedDateStr = info.startStr;

                        if (pendingDates.includes(selectedDateStr)) {
                            alert(
                                "There's a pending reservation for this date. Please select another date."
                            );
                            calendar.unselect();
                            return;
                        }

                        const selected = new Date(selectedDateStr);
                        const minDate = new Date(startDate);

                        if (selected >= minDate) {
                            const year = selected.getFullYear();
                            const month = String(selected.getMonth() + 1).padStart(2, '0');
                            const day = String(selected.getDate()).padStart(2, '0');
                            const formattedDate = `${year}-${month}-${day}`;

                            document.getElementById('date_from').value = formattedDate;
                            document.getElementById('date_to').value = formattedDate;

                            if (selectedDateEl) {
                                selectedDateEl.innerText = 'Selected Date: ' + formattedDate;
                            }

                            if (submitButton) {
                                submitButton.disabled = false;
                            }
                            calendar.getEvents().forEach(function(event) {
                                if (event.display === 'background') {
                                    event.remove();
                                }
                            });

                            calendar.addEvent({
                                title: 'Selected',
                                start: selectedDateStr,
                                allDay: true,
                                display: 'background',
                                backgroundColor: '#B0E0E6'
                            });
                        } else {
                            alert('Please select a date starting from ' + minDate.toISOString().split('T')[
                                0]);
                            calendar.unselect();
                        }
                    },

                    validRange: {
                        // start: startDate.toISOString().split('T')[0],
                        start: formatDate(startDate),
                        end: formatDate(maxMonthDate),
                    },

                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: ''
                    },

                    datesSet: function(view) {
                        const currentDate = view.start;

                        const earliestMonth = new Date(today.getFullYear(), today.getMonth(),
                            1); // 1st of this month
                        const latestMonth = new Date(today.getFullYear(), today.getMonth() + 2,
                            1); // 1st of last allowed month

                        const prevButton = document.querySelector('.fc-prev-button');
                        const nextButton = document.querySelector('.fc-next-button');

                        // Disable prev if current view is at or before start
                        if (currentDate <= earliestMonth) {
                            prevButton.setAttribute('disabled', 'disabled');
                        } else {
                            prevButton.removeAttribute('disabled');
                        }

                        // Disable next if current view is at or after end
                        if (currentDate >= latestMonth) {
                            nextButton.setAttribute('disabled', 'disabled');
                        } else {
                            nextButton.removeAttribute('disabled');
                        }
                    },
                });

                calendar.render();

                if (submitButton) {
                    submitButton.disabled = true;
                }

                totalPriceElement.textContent = '₱0.00';

                clientTypeDropdown.addEventListener('change', function() {
                    const selectedOption = clientTypeDropdown.options[clientTypeDropdown.selectedIndex];
                    const priceValue = selectedOption.value;
                    totalPriceElement.textContent = `₱` + `${parseFloat(priceValue).toFixed(2)}`;

                    totalPriceField.value = priceValue;
                    document.getElementById('total-price').innerHTML =
                        '<strong>Total Price: </strong>&#8369; ' + new Intl.NumberFormat().format(priceValue);
                });

                reservationForm.addEventListener('submit', function(e) {
                    const selectedClientType = clientTypeDropdown.value;
                    const rawPrice = totalPriceElement.textContent.replace(/[^\d.]/g, '');
                    const parsedPrice = parseFloat(rawPrice) || 0;
                    const dateFrom = document.getElementById('date_from').value;
                    const dateTo = document.getElementById('date_to').value;

                    let hasError = false;
                    let errorMessages = [];

                    if (!selectedClientType || parsedPrice <= 0) {
                        e.preventDefault();
                        clientTypeDropdown.classList.add('is-invalid');
                        alert('Please select a valid client type before submitting the reservation.');
                    } else {
                        clientTypeDropdown.classList.remove('is-invalid');
                    }

                    if (!dateFrom || !dateTo) {
                        errorMessages.push('Please select a valid reservation date.');
                        hasError = true;
                    }

                    if (hasError) {
                        e.preventDefault();
                        errorContainer.innerHTML = errorMessages.map(msg => `<div>${msg}</div>`).join('');
                    } else {
                        errorContainer.innerHTML = '';
                    }

                });
            });
        </script>
    @endif




@endpush
