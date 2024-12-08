@extends('layouts.app')
@section('content')
    <style>
        .rental-single__rules {
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
            'rentals.index' => ['Rentals'],
            'rentals.details' => ['Rentals', 'Rental Details'],
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
        <section class="rental-single container">
            <div class="row">
                <!-- Rental Gallery -->
                <div class="col-lg-7">
                    <div class="rental-gallery">
                        <div class="gallery-wrapper">
                            <!-- Thumbnails -->
                            <div class="thumbnails">
                                <div class="swiper-container thumbnail-swiper">
                                    <div class="swiper-wrapper">
                                        <!-- Main Image Thumbnail -->
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="thumbnail-img"
                                                src="{{ asset('uploads/rentals/thumbnails/' . $rental->image) }}"
                                                alt="{{ $rental->name }}" height="204">
                                        </div>

                                        <!-- Loop through gallery images for thumbnails -->
                                        @foreach (explode(',', $rental->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('uploads/rentals/thumbnails/' . trim($gimg)) }}"
                                                    alt="{{ $rental->name }}">
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
                                                src="{{ asset('uploads/rentals/' . $rental->image) }}"
                                                alt="{{ $rental->name }}">
                                            <a data-fancybox="gallery"
                                                href="{{ asset('uploads/rentals/' . $rental->image) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $rental->name }}"></a>
                                        </div>

                                        <!-- Loop through gallery images for main images -->
                                        @foreach (explode(',', $rental->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img"
                                                    src="{{ asset('uploads/rentals/' . trim($gimg)) }}"
                                                    alt="{{ $rental->name }}">
                                                <a data-fancybox="gallery"
                                                    href="{{ asset('uploads/rentals/' . trim($gimg)) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $rental->name }}"></a>
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
                    <h1 class="rental-single__name">{{ $rental->name }}</h1>

                    <div class="rental-single__desc">
                        <p>{{ $rental->description }}</p>
                    </div>

                    <!-- Collapsible Rules and Regulations Section -->
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
                                        $rules = explode("\n", $rental->rules_and_regulations);
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

                    @if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory']))
                        @if ($dormitoryRoom)
                            <p><strong>Capacity:</strong>
                                {{ $dormitoryRoom ? $dormitoryRoom->room_capacity : 'Not available' }} people
                            </p>
                        @else
                            <p><strong>Capacity: </strong>Not available</p>
                        @endif
                        <div class="rental-single__price" data-bed-price="{{ $rental->price }}">
                            <label><strong>Bed Price: </strong>&#8369;{{ number_format($rental->price, 2) }}</label>
                        </div>
                    @endif


                    @if (in_array($rental->name, ['Male Dormitory', 'Female Dormitory']))
                        @if ($dormitoryRoom)
                            <p><strong>Capacity:</strong>
                                {{ $dormitoryRoom ? $dormitoryRoom->room_capacity : 'Not available' }} people
                            </p>
                        @else
                            <p><strong>Capacity: </strong>Not available</p>
                        @endif

                        <div class="rental-single__price" data-bed-price="{{ $rental->price }}">
                            <label><strong>Bed Price: </strong>&#8369;{{ number_format($rental->price, 2) }}</label>
                        </div>

                        <!-- Total Price for Male and Female Dormitories -->
                        <div>
                            <p><strong>Total Price: </strong><span
                                    id="total_price_display_{{ $rental->name }}">₱{{ number_format($rental->price, 2) }}</span>
                            </p>
                        </div>
                    @endif

                    <!-- If rental name is 'International House II' -->
                @if ($rental->name == 'International House II')
                    <div class="mb-3">
                        <label><strong>Reservation Type:</strong></label><br>
                        <input type="radio" id="solo" name="reservation_type" value="solo" 
                            {{ old('reservation_type') == 'solo' ? 'checked' : '' }}>
                        <label for="solo">Solo</label><br>
                        <input type="radio" id="shared" name="reservation_type" value="shared" 
                            {{ old('reservation_type') == 'shared' ? 'checked' : '' }}>
                        <label for="shared">Shared</label><br>
                    </div>

                    <div class="rental-single__price" id="ih2-price" data-bed-price="{{ $rental->price }}">
                        <label><strong>Bed Price: </strong>&#8369;{{ number_format($rental->price, 2) }}</label>
                    </div>

                    <p><strong>Capacity:</strong> 
                        {{ $dormitoryRoom ? $dormitoryRoom->room_capacity . ' people' : 'Not available' }}
                    </p>

                    <div>
                        <p class="my-2"><strong>Total Price: </strong><span id="total_price_display">₱0.00</span></p>
                    </div>
                @endif



                    @if ($rental->name == 'Swimming Pool')
                        <div class="mb-3">

                            <p><strong>Capacity: </strong>{{ $rental->capacity }} people</p>
                            <label><strong>Usage Type:</strong></label><br>
                            <input type="radio" id="individual_group" name="usage_type" value="individual_group"
                                checked>
                            <label for="individual_group">Individual Group</label><br>
                            <input type="radio" id="exclusive_use" name="usage_type" value="exclusive_use">
                            <label for="exclusive_use">Exclusive Use</label><br>
                        </div>

                        <div class="rental-single__price" data-internal-price="{{ $rental->internal_price }}"
                            data-external-price="{{ $rental->external_price }}" data-capacity="{{ $rental->capacity }}">
                            <label><strong>Internal Clients Price:
                                </strong>&#8369;{{ number_format($rental->internal_price, 2) }}</label><br>
                            <label><strong>External Clients Price:
                                </strong>&#8369;{{ number_format($rental->external_price, 2) }}</label><br>
                        </div>

                        <div id="quantity-error" class="text-danger mb-3" style="display: none;">
                            Please select at least 1 internal or external quantity.
                        </div>

                        <div class="mb-3">
                            <label for="internal_quantity">Select Internal Quantity:</label>
                            <input type="number" class="form-control" id="internal_quantity" name="internal_quantity"
                                min="0" value="{{ old('internal_quantity', 0) }}">
                        </div>

                        <div class="mb-3">
                            <label for="external_quantity">Select External Quantity:</label>
                            <input type="number" class="form-control" id="external_quantity" name="external_quantity"
                                min="0" value="{{ old('external_quantity', 0) }}">
                        </div>

                        <div class="rental-single__price">
                            <label><strong>Total Price: </strong>&#8369; <span id="total_price">₱0.00</span></label>
                        </div>
                    @else
                        @if (in_array($rental->name, ['International Convention Center', 'Rolle Hall']))
                            <div class="rental-single__price" data-internal-price="{{ $rental->internal_price }}"
                                data-external-price="{{ $rental->external_price }}">
                                <label><strong>Internal Clients Price: </strong>&#8369;
                                    {{ number_format($rental->internal_price, 2) }}</label><br>
                                <label><strong>External Clients Price: </strong>&#8369;
                                    {{ number_format($rental->external_price, 2) }}</label><br>
                            </div>

                            <div class="mb-3">
                                <label for="price_type">Select Price Type:</label>
                                <select class="form-control" id="price_type" name="price_type"
                                    onchange="calculateTotalPriceBasedOnType()">
                                    <option>Select</option>
                                    <option value="internal">Internal Price</option>
                                    <option value="external">External Price</option>
                                </select>
                                <span class="text-danger" id="price-type-error" style="display:none;">Choose another
                                    option.</span>
                            </div>

                            <div class="rental-single__price">
                                <label><strong>Total Price: </strong>&#8369; <span id="total_price">0.00</span></label>
                            </div>
                        @endif
                    @endif

                    <form method="GET" action="{{ route('rentals.checkout', ['rental_id' => $rental->id]) }}">
                        @csrf

                        @if ($rental->name === 'Swimming Pool')
                            <input type="hidden" id="hidden_internal_quantity" name="internal_quantity"
                                value="0" />
                            <input type="hidden" id="hidden_external_quantity" name="external_quantity"
                                value="0" />
                            <input type="hidden" id="hidden_pool_quantity" name="pool_quantity" value="0" />
                            <input type="hidden" id="hidden_usage_type" name="usage_type" value="individual_group" />
                        @endif
                        <input type="hidden" name="total_price" id="calculated-total-price" value="0" />
                        @if ($rental->name === 'International House II')
                            <input type="hidden" name="total_price_ih2" id="total_price_ih2"
                                value="{{ old('total_price_ih2', 0) }}">
                        @endif
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
        document.addEventListener("DOMContentLoaded", function() {
            // Form submit event
            document.querySelector('form').addEventListener('submit', function() {
                updateTotalPrice(); // Ensure total price is updated before form submission
            });

            // Toggle visibility of the rules section
            const rulesHeader = document.querySelector('.rules-header');
            const toggleText = document.querySelector('.rules-toggle-text');
            const rulesContent = document.getElementById('rules-content');

            if (rulesContent) {
                rulesContent.addEventListener('show.bs.collapse', function() {
                    toggleText.textContent = 'Hide Details';
                    rulesHeader.classList.remove('collapsed');
                });

                rulesContent.addEventListener('hide.bs.collapse', function() {
                    toggleText.textContent = 'View Details';
                    rulesHeader.classList.add('collapsed');
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Smooth scroll to rules section
            document.querySelectorAll('a[href="#rules-content"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('#rules-content').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Add keyboard navigation to rules header
            if (rulesHeader) {
                rulesHeader.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            }

            // Rental data initialization
            const rentalDetails = document.querySelector('.rental-single__price');
            const internalPrice = parseFloat(rentalDetails.getAttribute('data-internal-price')) || 0;
            const externalPrice = parseFloat(rentalDetails.getAttribute('data-external-price')) || 0;
            const capacity = parseInt(rentalDetails.getAttribute('data-capacity')) || 0;
            const bedPrice = parseFloat(rentalDetails.getAttribute('data-bed-price')) || 0;
            const errorElementPool = document.getElementById('quantity-error');
            const reserveButton = document.getElementById('reserve-btn');
            const totalPriceInput = document.getElementById('total_price_input');

            function updateTotalPrice() {
                // Only run this function for International House II
                const soloRadioButton = document.getElementById('solo');
                const sharedRadioButton = document.getElementById('shared');

                // Check if these radio buttons exist (only for International House II)
                if (!soloRadioButton || !sharedRadioButton) return;

                const totalPriceDisplay = document.getElementById('total_price_display');
                const totalPriceIH2Input = document.getElementById('total_price_ih2');
                const roomCapacity = {{ $dormitoryRoom ? $dormitoryRoom->room_capacity : 0 }};
                const rentalPrice = parseFloat(@json($rental->price)) || 0;

                let totalPrice = 0;
                // Check selected radio button and calculate total price accordingly
                if (soloRadioButton.checked) {
                    totalPrice = roomCapacity * rentalPrice;
                } else if (sharedRadioButton.checked) {
                    totalPrice = rentalPrice;
                }

                totalPrice = parseFloat(totalPrice) || 0; // Ensure total price is a number

                // Update hidden inputs and total price display
                if (totalPriceIH2Input) {
                    totalPriceIH2Input.value = totalPrice.toFixed(2);
                }

                if (totalPriceDisplay) {
                    totalPriceDisplay.textContent = '₱' + totalPrice.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }

            // Event listeners for radio button changes (only for International House II)
            const soloRadioButton = document.getElementById('solo');
            const sharedRadioButton = document.getElementById('shared');

            if (soloRadioButton) {
                soloRadioButton.addEventListener('change', updateTotalPrice);
            }

            if (sharedRadioButton) {
                sharedRadioButton.addEventListener('change', updateTotalPrice);
            }

            // Initialize total price on page load for International House II
            if (soloRadioButton && sharedRadioButton) {
                updateTotalPrice();
            }

            // Calculate total based on internal and external quantities
            window.calculateTotal = function() {
                const internalQuantityInput = document.getElementById('internal_quantity');
                const externalQuantityInput = document.getElementById('external_quantity');
                const totalPriceElement = document.getElementById('total_price');
                const totalPriceHiddenInput = document.getElementById('calculated-total-price');

                const internalQuantity = parseInt(internalQuantityInput.value || 0);
                const externalQuantity = parseInt(externalQuantityInput.value || 0);

                const totalQuantity = internalQuantity + externalQuantity;
                const totalPrice = (internalQuantity * internalPrice) + (externalQuantity * externalPrice);

                // Update hidden inputs
                document.getElementById('hidden_internal_quantity').value = internalQuantity;
                document.getElementById('hidden_external_quantity').value = externalQuantity;
                document.getElementById('hidden_pool_quantity').value = totalQuantity;

                // Error handling and updating total price
                if (totalQuantity === 0) {
                    errorElementPool.style.display = "block";
                    reserveButton.disabled = true;
                    totalPriceElement.textContent = "₱0.00";
                    totalPriceHiddenInput.value = "0";
                    return;
                } else if (totalQuantity > capacity) {
                    alert('The total quantity exceeds the rental capacity.');
                    internalQuantityInput.value = '';
                    externalQuantityInput.value = '';
                    errorElementPool.style.display = "none";
                    reserveButton.disabled = true;
                    totalPriceElement.textContent = "₱0.00";
                    totalPriceHiddenInput.value = "0";
                    return;
                }

                // Enable the reserve button and show the total price
                errorElementPool.style.display = "none";
                reserveButton.disabled = false;
                totalPriceElement.textContent = totalPrice.toFixed(2);
                totalPriceHiddenInput.value = totalPrice.toFixed(2);
            };

            // Event listeners for quantity input changes
            document.getElementById('internal_quantity').addEventListener('input', window.calculateTotal);
            document.getElementById('external_quantity').addEventListener('input', window.calculateTotal);

            // Calculate total price based on price type
            window.calculateTotalPriceBasedOnType = function() {
                const priceTypeSelect = document.getElementById('price_type');
                const selectedOption = priceTypeSelect.value;
                const totalPriceElement = document.getElementById('total_price');
                const totalPriceHiddenInput = document.getElementById('calculated-total-price');
                const errorElement = document.getElementById('price-type-error');

                if (selectedOption === "Select") {
                    errorElement.style.display = "inline";
                    totalPriceElement.textContent = "₱0.00";
                    totalPriceHiddenInput.value = "0";
                    document.getElementById('reserve-btn').disabled = true;
                    return;
                }

                errorElement.style.display = "none";
                document.getElementById('reserve-btn').disabled = false;

                const selectedPrice = selectedOption === "internal" ? internalPrice : externalPrice;
                totalPriceElement.textContent = selectedPrice.toFixed(2);
                totalPriceHiddenInput.value = selectedPrice.toFixed(2);
            };

            // Event listener for price type dropdown
            const priceTypeDropdown = document.getElementById('price_type');
            if (priceTypeDropdown) {
                priceTypeDropdown.addEventListener('change', window.calculateTotalPriceBasedOnType);
            }

            // Form submission event with validation
            const form = document.querySelector('form[action*="rentals/checkout"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    updateTotalPrice(); // Ensure total price is updated

                    const internalQuantity = parseInt(document.getElementById('hidden_internal_quantity')
                        .value || 0);
                    const externalQuantity = parseInt(document.getElementById('hidden_external_quantity')
                        .value || 0);

                    // Validate at least one quantity is selected
                    if (internalQuantity === 0 && externalQuantity === 0) {
                        e.preventDefault();
                        alert('Please select at least one internal or external quantity.');
                        return false;
                    }

                    // Debugging: Check form submission data
                    console.log('Submitting form with values:', {
                        internalQuantity,
                        externalQuantity,
                        poolQuantity: document.getElementById('hidden_pool_quantity').value,
                        totalPrice: document.getElementById('calculated-total-price').value
                    });
                });
            }

            // Initialize Swipers for the gallery
            const mainSwiper = new Swiper('.main-swiper', {
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
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
                slidesPerView: 4,
                spaceBetween: 10,
                loop: true,
                slideToClickedSlide: true
            });
        });
    </script>
@endpush
