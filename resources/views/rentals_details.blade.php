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
            'rentals.index' => ['Facility'],
            'rentals.details' => ['Facility', 'Facility Details'],
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
                                                src="{{ asset('uploads/facility/thumbnails/' . $facility->image) }}"
                                                alt="{{ $facility->name }}" height="204">
                                        </div>

                                        <!-- Loop through gallery images for thumbnails -->
                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('uploads/facility/thumbnails/' . trim($gimg)) }}"
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
                                                src="{{ asset('uploads/facility/' . $facility->image) }}"
                                                alt="{{ $facility->name }}">
                                            <a data-fancybox="gallery"
                                                href="{{ asset('uploads/facility/' . $facility->image) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $facility->name }}"></a>
                                        </div>

                                        <!-- Loop through gallery images for main images -->
                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img"
                                                    src="{{ asset('uploads/facility/' . trim($gimg)) }}"
                                                    alt="{{ $facility->name }}">
                                                <a data-fancybox="gallery"
                                                    href="{{ asset('uploads/facility/' . trim($gimg)) }}"
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
                    <h1 class="rental-single__name">{{ $facility->name }}</h1>

                    {{-- <div class="rental-single__desc">
                        <p>{{ $facility->description }}</p>
                    </div> --}}

                    
                    
                    
                    @if ($facility->facility_type === 'both')

                    @php
                        $individualPrices = $facility->prices->where('price_type', 'individual');
                        $wholePrice = $facility->prices->where('price_type', 'whole')->first();
                    @endphp
                
                    <div class="mb-3">
                        <label><strong>Usage Type:</strong></label><br>
                
                        @if ($individualPrices->isNotEmpty())
                            <input type="radio" id="individual" name="usage_type" value="individual" checked>
                            <label for="individual">Individual</label><br>
                        @endif
                
                        @if ($wholePrice)
                            <input type="radio" id="whole" name="usage_type" value="whole">
                            <label for="whole">Whole</label><br>
                        @endif
                    </div>
                
                    @if ($individualPrices->isNotEmpty())
                        <div id="individual-price" class="usage-price" style="display: block;">
                            @foreach($individualPrices as $individualPrice)
                                <div class="individual-price-entry">
                                    <label><strong>{{ $individualPrice->name }}:</strong> ₱{{ number_format($individualPrice->value, 2) }}</label><br>
                                    <div class="mb-3">
                                        <label for="individual_value_{{ $individualPrice->id }}">Enter Quantity for {{ $individualPrice->name }}:</label>
                                        <input type="number" class="form-control" id="individual_value_{{ $individualPrice->id }}" name="individual_value[{{ $individualPrice->id }}]" min="1" value="{{ old('individual_value.' . $individualPrice->id) }}" placeholder="Enter quantity or value">
                                    </div>
                                </div>
                            @endforeach
                        </div>
     
                    @endif
                
                    @if ($wholePrice)
                        <div id="whole-price" class="usage-price" style="display: none;">
                            <label><strong>Price: </strong>₱{{ number_format($wholePrice->value, 2) }}</label><br>
                        </div>
                   
                    @endif
                
                    <div class="rental-single__price">
                        <label><strong>Total Price: </strong>&#8369;<span id="total_price">0.00</span></label>
                    </div>
                
                @endif
                    

                 


                        <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                        style="padding: 15px 30px; font-size: 18px; margin-top: 20px;">
                        Reserve
                    </button>

                    <!-- </form> -->
                </div>
            </div>



            <div class="rental-single__details-tab">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <!-- Description Tab -->
                    <li class="nav-item" role="presentation">
                        <a class="nav-link nav-link_underscore active" id="tab-description-tab" data-bs-toggle="tab"
                            href="#tab-description" role="tab" aria-controls="tab-description" aria-selected="true">
                            Description
                        </a>
                    </li>
            
                    <!-- Rules Tab -->
                    <li class="nav-item" role="presentation">
                        <a class="nav-link nav-link_underscore" id="tab-rules-tab" data-bs-toggle="tab"
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

                        <form onsubmit="return validateForm()">
                            <!-- Your form inputs here -->
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                    
                    <!-- Form submission example -->
                  
                </div>
            </div>
            

                    
        </section>
    </main>

    <hr class="mt-5 text-secondary" />
@endsection

@push('scripts')
<script>
function validateForm() {
        const checkbox = document.getElementById('agreeToRules');
        if (!checkbox.checked) {
            checkbox.setCustomValidity('You must agree to the rules and regulations.');
            return false;
        }
        checkbox.setCustomValidity(''); // Clear the custom validation message
        return true;
    }

</script>

@if ($facility->facility_type == 'both')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const individualRadio = document.getElementById('individual');
        const wholeRadio = document.getElementById('whole');
        const individualPriceDiv = document.getElementById('individual-price');
        const wholePriceDiv = document.getElementById('whole-price');
        const totalPriceSpan = document.getElementById('total_price');

        function calculateTotalPrice() {
            let totalPrice = 0;

            if (individualRadio && individualRadio.checked) {
                const individualPriceEntries = document.querySelectorAll('.individual-price-entry');
                
                individualPriceEntries.forEach(entry => {
                    const quantityInput = entry.querySelector('input[type="number"]');
                    const priceLabel = entry.querySelector('label');
                    
                    if (quantityInput && priceLabel) {
                        const quantity = parseInt(quantityInput.value) || 0;
                        const priceText = priceLabel.textContent.split('₱')[1];
                        const price = parseFloat(priceText.replace(/,/g, '').trim());
                        
                        if (!isNaN(quantity) && !isNaN(price)) {
                            totalPrice += quantity * price;
                        }
                    }
                });
            } else if (wholeRadio && wholeRadio.checked) {
                const wholePriceLabel = document.querySelector('#whole-price label');
                if (wholePriceLabel) {
                    const priceText = wholePriceLabel.textContent.split('₱')[1];
                    totalPrice = parseFloat(priceText.replace(/,/g, '').trim());
                }
            }

            totalPriceSpan.textContent = isNaN(totalPrice) ? '0.00' : totalPrice.toFixed(2);
        }

        individualRadio?.addEventListener('change', function() {
            individualPriceDiv.style.display = 'block';
            wholePriceDiv.style.display = 'none';
            calculateTotalPrice();
        });

        wholeRadio?.addEventListener('change', function() {
            individualPriceDiv.style.display = 'none';
            wholePriceDiv.style.display = 'block';
            calculateTotalPrice();
        });

        const individualQuantityInputs = document.querySelectorAll('input[name^="individual_value"]');
        individualQuantityInputs.forEach(input => {
            input.addEventListener('input', calculateTotalPrice);
        });

        calculateTotalPrice();
    });
</script>
@endif
@endpush
