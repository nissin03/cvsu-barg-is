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
                        <div class="facilities-single__desc">
                            <p>{{ $facility->description }}</p>
                        </div>
        
                        <!-- Collapsible Rules and Regulations Section -->
                        <div class="facilities-single__rules mb-4">
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
                                <label for="client_type"><strong>Client Type: </strong></label>
                                <p></p>
                                <select id="client_type" class="form-control">
                                    <option value="internal" selected>Internal Clients</option>
                                    <option value="external">External Clients</option>
                                </select>
                            </div>
                        </div>
                        @endif
                        
                        <form method="GET" action="{{ route('rentals.checkout', ['rental_id' => $facility->id]) }}">
                            <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                                style="padding: 15px 30px; font-size: 18px; margin-top: 20px;">
                                Reserve
                            </button>
                        </form>
                    </div>
                </div>
            </section>
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
    });

//     // Initialize the first thumbnail as active
//     document.querySelector('.thumbnail-img').classList.add('active');
//     const qtyInputs = document.querySelectorAll('.qty-control__number');
//     const decreaseBtns = document.querySelectorAll('.qty-control__reduce');
//     const increaseBtns = document.querySelectorAll('.qty-control__increase');
//     const availableQuantitySpan = document.getElementById('available-quantity');
//     let maxAvailableQty = parseInt(availableQuantitySpan.textContent) || 0;
// });

// document.addEventListener('DOMContentLoaded', function () {
//         // Get the facility's pricing and capacity dynamically
//         const internalPrice = parseFloat('{{ $facility->internal_price }}');
//         const externalPrice = parseFloat('{{ $facility->external_price }}');
//         const capacity = parseInt('{{ $facility->capacity }}');
        
//         const internalQuantityInput = document.getElementById('internal_quantity');
//         const externalQuantityInput = document.getElementById('external_quantity');
//         const totalPriceElement = document.getElementById('total_price');
//         const usageTypeRadios = document.querySelectorAll('input[name="usage_type"]');

//         // Function to calculate total price
//         function calculateTotalPrice() {
//             let internalQuantity = parseInt(internalQuantityInput.value) || 0;
//             let externalQuantity = parseInt(externalQuantityInput.value) || 0;
//             let totalPrice = 0;

//             // Log the quantities and usage type
//             console.log('Internal Quantity:', internalQuantity);
//             console.log('External Quantity:', externalQuantity);

//             const selectedUsageType = Array.from(usageTypeRadios).find(radio => radio.checked).value;
//             console.log('Selected Usage Type:', selectedUsageType);

//             // Price calculation logic remains the same
//             if (selectedUsageType === 'individual_group') {
//                 totalPrice = (internalQuantity * internalPrice) + (externalQuantity * externalPrice);
//             } else if (selectedUsageType === 'exclusive_use') {
//                 totalPrice = (internalQuantity + externalQuantity) * (internalPrice + externalPrice);
//             }

//             // Update total price display
//             totalPriceElement.textContent = `₱${totalPrice.toFixed(2)}`;
//         }

//         // Event listener for quantity inputs and usage type selection
//         internalQuantityInput.addEventListener('input', calculateTotalPrice);
//         externalQuantityInput.addEventListener('input', calculateTotalPrice);
//         usageTypeRadios.forEach(radio => {
//             radio.addEventListener('change', calculateTotalPrice);
//         });

//         // Initialize total price calculation on page load
//         calculateTotalPrice();
    });
    </script>
@endpush
