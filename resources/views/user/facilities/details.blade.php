@extends('layouts.app')
@section('content')
    <style>
        <link rel="stylesheet" href="path/to/swiper-bundle.min.css">

        .facilities-single__rules {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .rules-header {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rules-header:hover {
            transform: translateY(-1px);
        }

        .rules-container {
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .rules-container::-webkit-scrollbar {
            width: 6px;
        }

        .rules-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .rules-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .rules-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .rule-item {
            transition: all 0.2s ease;
        }

        .rule-item:hover {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
            margin: -8px;
            margin-bottom: 12px;
        }

        .rule-number .badge {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .rule-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #4a4a4a;
        }

        .rules-footer {
            font-size: 0.9rem;
        }

        .chevron-icon {
            transition: transform 0.3s ease;
        }

        .collapsed .chevron-icon {
            transform: rotate(-90deg);
        }

        .rules-toggle-text {
            font-size: 0.9rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .rules-container {
                max-height: 300px;
            }

            .rule-text {
                font-size: 0.9rem;
            }
        }

        /* Animation for collapse */
        .collapse {
            transition: all 0.35s ease;
        }

        .collapse:not(.show) {
            display: none;
        }

        .collapsing {
            height: 0;
            overflow: hidden;
            transition: height 0.35s ease;
        }

        /* Styling for the thumbnails */
        .thumbnail-img {
            cursor: pointer;
            width: 100%;
            height: auto;
            transition: transform 0.3s ease-in-out;
            border-radius: 8px;  /* Optional: Rounded corners */
        }

        /* Card-like effect for thumbnails */
        .thumbnail-card {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            margin: 5px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);  /* Light shadow */
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        .thumbnail-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);  /* Slightly stronger shadow on hover */
            transform: translateY(-5px); /* Lift the card slightly on hover */
        }

        /* Optional: Styling for the navigation buttons */
        .swiper-button-next, .swiper-button-prev {
            color: #000;  /* Navigation arrows color */
            background-color: rgba(255, 255, 255, 0.5);  /* Semi-transparent background for arrows */
            border-radius: 50%;
            padding: 10px;
        }

        /* Styling for the main image */
        .main-image img {
            width: 100%;  /* Ensures the main image fills the container */
            height: auto;
            border-radius: 5px;  /* Optional: Rounded corners for the main image */
        }
        p{
            margin:0;
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

        // Breadcrumbs logic
        $routesWithBreadcrumbs = [
            'facilities.index' => ['Rentals'],
            'facilities.details' => ['Rentals', 'Rental Details'],
            'about.index' => ['About Us'],
            'contact.index' => ['Contact Us'],
        ];

        if (isset($routesWithBreadcrumbs[$currentRoute])) {
            foreach ($routesWithBreadcrumbs[$currentRoute] as $label) {
                $breadcrumbs[] = ['url' => null, 'label' => $label];
            }
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />


        <main class="container my-5">
            <section class="facilities-single container">
                <div class="row">
                    <!-- Facility Gallery -->
                    <div class="col-lg-7">
                        <div class="facility-gallery">
                            <div class="gallery-wrapper">
                                <!-- Thumbnails -->
                             
                            
                                <div class="thumbnails">
                                    <div class="swiper-container thumbnail-swiper">
                                        <div class="swiper-wrapper">
                                            <!-- Main Image Thumbnail -->
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('storage/' . $facility->image) }}"
                                                    alt="{{ $facility->name }}" height="204">
                                                </div>
                                                
                                            <!-- Loop through gallery images for thumbnails -->
                                            @foreach (explode(',', $facility->images) as $gimg)
                                                <div class="swiper-slide">
                                                    <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('storage/' . trim($gimg)) }}"
                                                    alt="{{ $facility->name }}">
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
                                                <a data-fancybox="gallery"
                                                    href="{{ asset('storage/' . $facility->image) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $facility->name }}"></a>
                                            </div>
        
                                            <!-- Loop through gallery images for main images -->
                                            @foreach (explode(',', $facility->images) as $gimg)
                                                <div class="swiper-slide">
                                                    <img loading="lazy" class="h-auto main-img"
                                                        src="{{ asset('storage/' . trim($gimg)) }}"
                                                        alt="{{ $facility->name }}">
                                                    <a data-fancybox="gallery"
                                                        href="{{ asset('storage/' . trim($gimg)) }}"
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
                        <h1 class="facilities-single__name">{{ $facility->name }}</h1>

                        
                        
                        
                        @if ($facility->facility_type == 'whole_place')
                            <div class="mb-3">
                            @if ($facility->prices->isNotEmpty())
                                        
                                @foreach ($facility->prices as $price)
                                        <h4><strong>{{ $price->name }}: </strong> <span class="product-type text-primary">&#8369; {{ number_format($price->value, 2) }}</span> </h4>
                                        <p>@if ($price->is_based_on_days)
                                                <span class="badge">Per Day</span>
                                            @endif</p>
                                @endforeach
                            @else
                            <p>No prices available for this facility.</p>
                            @endif
                    
                                <div class="form-group">
                                    <label for="client_type" style="margin-bottom: 7px;"><strong>Client Type: </strong></label>
                                    <select id="client_type" class="form-control">
                                        <option value="internal" selected>Internal Clients</option>
                                        <option value="external">External Clients</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if ($facility->facility_type == 'individual')
                            <div class="mb-3">
                            @if ($facility->prices->isNotEmpty())                                       
                                @foreach ($facility->prices as $price)
                                        <h4><strong>{{ $price->name }}: </strong> <span class="product-type text-primary">&#8369; {{ number_format($price->value, 2) }}</span> </h4>
                                @endforeach
                            @else
                            <p>No prices available for this facility.</p>
                            @endif                           
                            @php
                                // Extract room numbers for the overall range
                                $roomNumbers = $facility->facilityAttributes->pluck('room_name')->filter()->map(function ($name) {
                                    return preg_replace('/[^0-9]/', '', $name); 
                                })->sort()->values();

                                // Extract sex restriction
                                $sexRestriction = $facility->facilityAttributes->pluck('sex_restriction')->filter()->first(); // Get the first non-null value
                            @endphp

                            @if ($roomNumbers->isNotEmpty())
                                @php
                                    $firstRoom = $roomNumbers->first();
                                    $lastRoom = $roomNumbers->last();
                                @endphp
                            @endif
                            @php
                                // Extract and process room details
                                $roomDetails = $facility->facilityAttributes
                                    ->filter(fn($attribute) => $attribute->room_name && $attribute->capacity) 
                                    ->map(fn($attribute) => [
                                        'room_number' => preg_replace('/[^0-9]/', '', $attribute->room_name), 
                                        'capacity' => $attribute->capacity,
                                    ])
                                    ->sortBy('room_number')
                                    ->values();

                                $groupedRooms = $roomDetails->groupBy('capacity'); 
                            @endphp
                            
                            @if ($groupedRooms->isNotEmpty())
                                <p><strong>Room Capacities:</strong></p>
                                <ul style="padding: 0">
                                    @foreach ($groupedRooms as $capacity => $rooms)
                                        @php
                                            // Generate room range for the group
                                            $roomNumbers = $rooms->pluck('room_number')->map(fn($num) => "R{$num}");
                                            $range = $roomNumbers->count() > 1 
                                                ? $roomNumbers->first() . '-' . $roomNumbers->last() 
                                                : $roomNumbers->first();
                                        @endphp
                                        <li >{{ $range }} - {{ $capacity }} People</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="alert alert-danger " role="alert" style="margin-top: ">
                                <p><strong>Note: </strong>Room Selection is not available in this facility.</p>
                            </div>
                            
                    
                            <div id="total-price" style="margin-top: 20px;">
                                <strong>Total Price: </strong><span>&#8369; {{ number_format($facility->prices->first()->value, 2) }}</span>
                            </div>
                        </div>
                         @endif    
                        
                        


                        @if ($facility->facility_type == 'both')
                            @foreach ($facility->facilityAttributes as $facilityAttribute)
                                @if (!empty($facilityAttribute->room_name))
                                    @foreach ($facility->prices as $price)
                                        <h4><strong>{{ $price->name }}: </strong> 
                                            <span class="product-type text-primary">&#8369; {{ number_format($price->value, 2) }}</span>
                                        </h4>
                                        <p>
                                            @if ($price->is_based_on_days)
                                                <span class="badge">Per Day</span>
                                            @endif
                                        </p>
                                    @endforeach
                                    
                                    <div style="margin-bottom: 15px;">
                                    <label><strong>Select Type:</strong></label>
                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <div>
                                                <input type="radio" id="exclusive" name="reservation_type" value="exclusive">
                                                <label for="exclusive">Solo</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="individual" name="reservation_type" value="individual">
                                                <label for="individual">Shared</label>
                                            </div>
                                        </div>
                                        <div>
                                            <!-- Reset Button -->
                                        <button type="button" id="reset_button" class="btn btn-shop btn-addtocart" style="font-size: 10px;">Reset</button>
                                        </div> 
                                    </div>             
                                </div>

                                <!-- Inputs for Individual -->
                                <div id="individual_inputs" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <input id="internal_quantity" type="number"
                                            class="form-control form-control_gray @error('internal_quantity') is-invalid @enderror"
                                            name="internal_quantity" value="{{ old('internal_quantity') }}">
                                        <label for="internal_quantity">Internal Quantity:</label>
                                        @error('internal_quantity')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input id="internal_fee" type="number"
                                            class="form-control form-control_gray @error('internal_fee') is-invalid @enderror"
                                            name="internal_fee" value="{{ old('internal_fee') }}">
                                        <label for="internal_fee">External Quantity:</label>
                                        @error('internal_fee')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror   
                                    </div>
                                </div>

                                <!-- Dropdown for Exclusive -->
                                <div id="exclusive_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="exclusive_type" name="exclusive_type"
                                            class="form-select @error('exclusive_type') is-invalid @enderror">
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="internal_exclusive">Solo (Internal) </option>
                                            <option value="external_exclusive">Solo (External) </option>
                                        </select>
                                        <label for="exclusive_type">Exclusive Type:</label>
                                        @error('exclusive_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div id="exclusive_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="exclusive_type" name="exclusive_type"
                                            class="form-select @error('exclusive_type') is-invalid @enderror">
                                            <option value="" disabled selected>Select Exclusive Type</option>
                                            <option value="internal_exclusive">Internal Exclusive</option>
                                            <option value="external_exclusive">External Exclusive</option>
                                        </select>
                                        <label for="exclusive_type">Exclusive Type:</label>
                                        @error('exclusive_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                    
                                @else
                                @foreach ($facility->prices as $price)
                                    <h4><strong>{{ $price->name }}: </strong> 
                                        <span class="product-type text-primary">&#8369; {{ number_format($price->value, 2) }}</span>
                                    </h4>
                                    <p>
                                        @if ($price->is_based_on_days)
                                            <span class="badge">Per Day</span>
                                        @endif
                                    </p>
                                @endforeach

                                <div style="margin-bottom: 15px;">
                                    <label><strong>Select Type:</strong></label>
                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <div>
                                                <input type="radio" id="individual" name="reservation_type" value="individual">
                                                <label for="individual">Individual</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="exclusive" name="reservation_type" value="exclusive">
                                                <label for="exclusive">Exclusive</label>
                                            </div>
                                        </div>
                                        <div>
                                            <!-- Reset Button -->
                                        <button type="button" id="reset_button" class="btn btn-shop btn-addtocart" style="font-size: 10px;">Reset</button>
                                        </div> 
                                    </div>             
                                </div>

                                <!-- Inputs for Individual -->
                                <div id="individual_inputs" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <input id="internal_quantity" type="number"
                                            class="form-control form-control_gray @error('internal_quantity') is-invalid @enderror"
                                            name="internal_quantity" value="{{ old('internal_quantity') }}">
                                        <label for="internal_quantity">Internal Quantity:</label>
                                        @error('internal_quantity')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input id="external_fee" type="number"
                                            class="form-control form-control_gray @error('external_fee') is-invalid @enderror"
                                            name="external_fee" value="{{ old('internal_fee') }}">
                                        <label for="external_fee">Extenrnal Quantity</label>
                                        @error('external_fee')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror   
                                    </div>
                                </div>

                                <!-- Dropdown for Exclusive -->
                                <div id="exclusive_dropdown" style="display: none;">
                                    <div class="form-floating mb-3">
                                        <select id="exclusive_type" name="exclusive_type"
                                            class="form-select @error('exclusive_type') is-invalid @enderror">
                                            <option value="" disabled selected>Select Exclusive Type</option>
                                            <option value="internal_exclusive">Internal Exclusive</option>
                                            <option value="external_exclusive">External Exclusive</option>
                                        </select>
                                        <label for="exclusive_type">Exclusive Type:</label>
                                        @error('exclusive_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif   
                        

                        <form action="{{ route('facility.reserve') }}" method="POST" style="margin: 0">
                            @csrf
                            <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                            <input type="hidden" name="total_price" id="total-price-field" value="{{ $individualPrice }}">
                            <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                                    style="padding: 15px 30px; font-size: 18px">
                                Reserve
                            </button>
                        </form>
                    </div>
                
                




            </section>
            <div class="rental-single__details-tab" style="margin-top: 30px;">
                    <ul class="nav nav-tabs" id="myTab" role="tablist" style="display:flex; justify-content: center; gap: 7px;">
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
                                                                <span class="badge bg-primary rounded-circle">{{ $index + 1 }}</span>
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
                                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
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
                                    I agree to the <a href="#tab-rules" data-bs-toggle="collapse" data-bs-target="#rules-content">Rules and Regulations</a>.
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
            document.querySelectorAll('.thumbnail-img').forEach((thumb) => thumb.classList.remove('active'));
            this.classList.add('active');
        });

        
    function validateForm() {
            const checkbox = document.getElementById('agreeToRules');
            if (!checkbox.checked) {
                checkbox.setCustomValidity('You must agree to the rules and regulations.');
                return false;
            }
            checkbox.setCustomValidity(''); // Clear the custom validation message
            return true;
        }

        });
        });
</script>


@if ($facility->facility_type == 'both')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const individualRadio = document.getElementById('individual');
        const exclusiveRadio = document.getElementById('exclusive');
        const individualInputs = document.getElementById('individual_inputs');
        const exclusiveDropdown = document.getElementById('exclusive_dropdown');
        const resetButton = document.getElementById('reset_button');

        // Initially hide all elements and ensure no radio button is selected
        individualInputs.style.display = 'none';
        exclusiveDropdown.style.display = 'none';
        individualRadio.checked = false;
        exclusiveRadio.checked = false;

        // Event listener for Individual
        individualRadio.addEventListener('change', function () {
            if (this.checked) {
                individualInputs.style.display = 'block';
                exclusiveDropdown.style.display = 'none';
            }
        });

        // Event listener for Exclusive
        exclusiveRadio.addEventListener('change', function () {
            if (this.checked) {
                individualInputs.style.display = 'none';
                exclusiveDropdown.style.display = 'block';
            }
        });

        // Event listener for Reset Button
        resetButton.addEventListener('click', function () {
            // Reset radio buttons
            individualRadio.checked = false;
            exclusiveRadio.checked = false;

            // Hide all conditional sections
            individualInputs.style.display = 'none';
            exclusiveDropdown.style.display = 'none';
        });
    });
</script>
@endif



@if($facility->facility_type === 'whole_place')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wholePriceDiv = document.getElementById('whole-price');
        const totalPriceSpan = document.getElementById('total_price');
        const wholeRadio = document.getElementById('whole');
        
        function calculateTotalPrice() {
            let totalPrice = 0;

            if (wholeRadio && wholeRadio.checked) {
                const wholePriceLabel = document.querySelector('#whole-price label');
                if (wholePriceLabel) {
                    const priceText = wholePriceLabel.textContent.split('₱')[1];
                    totalPrice = parseFloat(priceText.replace(/,/g, '').trim());
                }
            }

            totalPriceSpan.textContent = isNaN(totalPrice) ? '0.00' : totalPrice.toFixed(2);
        }

        // Show or hide the whole price div when the radio button is clicked
        wholeRadio?.addEventListener('change', function() {
            if (wholeRadio.checked) {
                wholePriceDiv.style.display = 'block';
            } else {
                wholePriceDiv.style.display = 'none';
            }
            calculateTotalPrice();
        });

        calculateTotalPrice(); // Initial calculation on load
    });
</script>
@endif


@if($facility->facility_type === 'individual')
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const individualPriceDiv = document.getElementById('individual-price');
        //     const individualRadio = document.getElementById('individual');
        //     const totalPriceSpan = document.getElementById('total_price');

        //     // Function to calculate the total price (no quantity)
        //     function calculateTotalPrice() {
        //         let totalPrice = 0;

        //         if (individualRadio && individualRadio.checked) {
        //             const individualPriceEntries = document.querySelectorAll('.individual-price-entry');
                    
        //             individualPriceEntries.forEach(entry => {
        //                 const priceLabel = entry.querySelector('label');
                        
        //                 if (priceLabel) {
        //                     const priceText = priceLabel.textContent.split('₱')[1];
        //                     const price = parseFloat(priceText.replace(/,/g, '').trim());
                            
        //                     if (!isNaN(price)) {
        //                         totalPrice = price; // Direct price without quantity
        //                     }
        //                 }
        //             });
        //         }

        //         totalPriceSpan.textContent = isNaN(totalPrice) ? '0.00' : totalPrice.toFixed(2);
        //     }

        //     // Event listener for when the individual radio button changes
        //     individualRadio?.addEventListener('change', function() {
        //         individualPriceDiv.style.display = 'block';  // Show individual price section
        //         calculateTotalPrice();
        //     });

        //     // If 'individual' is selected on page load, ensure it's shown and calculate total
        //     if (individualRadio && individualRadio.checked) {
        //         individualPriceDiv.style.display = 'block';
        //         calculateTotalPrice();
        //     }
        // });
    </script>
@endif



@endpush
