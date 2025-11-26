@extends('layouts.app')
@section('content')

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

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
            $breadcrumbs[] = ['url' => null, 'label' => 'Facility Details'];
        }
    @endphp

    <link href="{{ asset('css/facility/details.css') }}" rel="stylesheet">

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        <section class="facilities-single container">
            <div class="row facility-main-row">
                <div class="col-lg-7">
                    <div class="facility-gallery">
                        <div class="gallery-wrapper">
                            <div class="thumbnails">
                                <div class="swiper-container thumbnail-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="thumbnail-img"
                                                src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}"
                                                height="204">
                                        </div>

                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('storage/' . trim($gimg)) }}" alt="{{ $facility->name }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="main-image">
                                <div class="swiper-container main-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="h-auto main-img image-clickable"
                                                src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}"
                                                data-bs-toggle="modal" data-bs-target="#imageModal"
                                                data-image-src="{{ asset('storage/' . $facility->image) }}"
                                                data-image-alt="{{ $facility->name }}" style="cursor: pointer;">
                                            <a data-fancybox="gallery" href="{{ asset('storage/' . $facility->image) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $facility->name }}" style="display: none;"></a>
                                        </div>

                                        @foreach (explode(',', $facility->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img image-clickable"
                                                    src="{{ asset('storage/' . trim($gimg)) }}"
                                                    alt="{{ $facility->name }}" data-bs-toggle="modal"
                                                    data-bs-target="#imageModal"
                                                    data-image-src="{{ asset('storage/' . trim($gimg)) }}"
                                                    data-image-alt="{{ $facility->name }}" style="cursor: pointer;">
                                                <a data-fancybox="gallery" href="{{ asset('storage/' . trim($gimg)) }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $facility->name }}" style="display: none;"></a>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $refundableAddons = $facility->addons->filter(function ($addon) {
                            return $addon->price_type === 'flat_rate' &&
                                $addon->is_refundable == 1 &&
                                $addon->show === 'both' &&
                                $addon->is_available == 1;
                        });
                    @endphp

                    @if ($refundableAddons && $refundableAddons->count() > 0)
                        <div class="addons-section mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="addons-title mb-0 fw-semibold" style="font-size: 1.4rem;">
                                    <i class="fas fa-plus-circle me-2" style="color: #1864ab; font-size: 1.2rem;"></i>
                                    Refundable Fee
                                </h4>
                                <span class="badge rounded-pill px-3 py-1"
                                    style="background-color: #0aa130; font-size: 0.9rem;">
                                    {{ $refundableAddons->count() }} available
                                </span>
                            </div>

                            <div class="addons-list">
                                @foreach ($refundableAddons as $addon)
                                    <div class="addon-item border rounded-3 p-3 mb-3 bg-white shadow-sm">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="addon-info flex-grow-1">
                                                <h6 class="addon-name mb-1 fw-bold" style="font-size: 1.1rem;">
                                                    {{ $addon->name }}</h6>
                                                <div class="addon-price text-success mb-2"
                                                    style="font-size: 1.3rem; font-weight: 600;">
                                                    ₱{{ number_format($addon->base_price, 2) }}
                                                </div>
                                                <button class="btn btn-sm"
                                                    style="background-color: #3b82f6; color: white; border-color: #3b82f6; font-size: 0.9rem; padding: 6px 12px;"
                                                    data-bs-toggle="modal" data-bs-target="#addonDescModal"
                                                    data-addon-name="{{ $addon->name }}"
                                                    data-addon-description="{{ $addon->description }}">
                                                    <i class="fas fa-info-circle me-1"></i>View Description
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-5">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @elseif (Session::has('error'))
                        <p class="alert alert-danger">{{ Session::get('error') }}</p>
                    @endif
                    <h1 class="facilities-single__name">{{ $facility->name }}</h1>

                    <form action="{{ route('facility.reserve') }}" method="POST" style="margin: 0">
                        @csrf
                        <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                        <input type="hidden" name="total_price" id="total-price-field" value="0">
                        <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">
                        <input type="hidden" name="selected_price" id="selected_price">

                        <<<<<<< HEAD @if (isset($discounts) && $discounts->count() > 0)
                            <div class="mb-3">
                                <label for="discount_id" class="form-label fw-semibold">Discount (optional)</label>
                                <select name="discount_id" id="discount_id" class="form-select">
                                    <option value="">-- No discount --</option>
                                    @foreach ($discounts as $discount)
                                        <option value="{{ $discount->id }}" data-percent="{{ $discount->percent }}"
                                            data-applies-to="{{ $discount->applies_to }}"
                                            data-requires-proof="{{ $discount->requires_proof ? '1' : '0' }}">
                                            {{ $discount->name }}
                                            ({{ rtrim(rtrim(number_format($discount->percent, 2, '.', ''), '0'), '.') }}%)
                                            @if ($discount->applies_to === 'venue_only')
                                                - Venue Only
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            =======
                            >>>>>>> 56b920b (Discount Admin and User Side)
                            @if ($facility->facility_type === 'individual')
                                <input type="hidden" name="facility_attribute_id"
                                    value="{{ $availableRoom->id ?? '' }}">
                            @elseif($facility->facility_type == 'whole_place')
                                <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}">
                            @endif

                            @if ($facility->facility_type == 'whole_place')
                                @include('components.facility_whole_place')
                            @endif

                            @if ($facility->facility_type === 'individual')
                                @include('components.facility_individual')
                            @endif

                            @if (
                                $facility->facility_type === 'both' &&
                                    $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
                                @include('components.facility_both_rooms')
                            @endif

                            @if (
                                $facility->facility_type === 'both' &&
                                    $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
                                @include('components.facility_both_building')
                            @endif

                            <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                                style="padding: 15px 30px; font-size: 18px">
                                Reserve
                            </button>
                    </form>
                </div>
            </div>
        </section>

        <div class="rental-single__details-tab mt-5">
            <ul class="nav nav-tabs nav-justified border-0 mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-dark fw-medium px-4 py-3 rounded-top active" id="tab-description-tab"
                        data-bs-toggle="tab" href="#tab-description" role="tab" aria-controls="tab-description"
                        aria-selected="true">
                        <i class="fas fa-align-left me-2"></i>Description
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-dark fw-medium px-4 py-3 rounded-top" id="tab-rules-tab" data-bs-toggle="tab"
                        href="#tab-rules" role="tab" aria-controls="tab-rules" aria-selected="false">
                        <i class="fas fa-clipboard-check me-2"></i>Rules & Regulations
                    </a>
                </li>
            </ul>

            <div class="tab-content bg-white rounded-3 shadow-sm p-4">
                <div class="tab-pane fade show active" id="tab-description" role="tabpanel"
                    aria-labelledby="tab-description-tab">
                    <div class="rental-single__description text-gray-700 lh-lg">
                        {{ $facility->description }}
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-rules" role="tabpanel" aria-labelledby="tab-rules-tab">
                    <div class="rental-single__rules mb-4">
                        <div class="rules-header cursor-pointer" data-bs-toggle="collapse"
                            data-bs-target="#rules-content" aria-expanded="false">
                            <div
                                class="d-flex justify-content-between align-items-center p-4 bg-light rounded-3 shadow-sm">
                                <h5 class="mb-0 d-flex align-items-center text-primary">
                                    <i class="fas fa-file-alt fs-5 me-3"></i>
                                    Rules and Regulations
                                </h5>
                                <div class="d-flex align-items-center">
                                    <span class="rules-toggle-text text-muted me-2 small">View Details</span>
                                    <i class="fas fa-chevron-down chevron-icon text-muted small"></i>
                                </div>
                            </div>
                        </div>

                        <div class="collapse" id="rules-content">
                            <div class="rules-container bg-white p-0 mt-3">
                                <div class="rules-content">
                                    @php
                                        $rulesSections = preg_split('/(?=\d+\.)/', $facility->rules_and_regulations);
                                        $rulesSections = array_filter(array_map('trim', $rulesSections));
                                    @endphp

                                    <div class="rules-sections">
                                        @foreach ($rulesSections as $section)
                                            @if (trim($section) !== '')
                                                @php
                                                    $lines = explode("\n", $section);
                                                    $firstLine = trim($lines[0]);
                                                    $remainingLines = array_slice($lines, 1);
                                                    $hasNumber = preg_match('/^\d+\./', $firstLine);
                                                    $displayText = $hasNumber
                                                        ? substr($firstLine, strpos($firstLine, '.') + 1)
                                                        : $firstLine;
                                                @endphp

                                                <div class="rule-section mb-4">
                                                    <div
                                                        class="rule-main d-flex align-items-start p-3 mb-2 rounded-3 bg-light">
                                                        <div class="rule-number me-3 pt-1">
                                                            @if ($hasNumber)
                                                                <span
                                                                    class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 26px; height: 26px;">
                                                                    {{ substr($firstLine, 0, strpos($firstLine, '.')) }}
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 26px; height: 26px; visibility: hidden;">
                                                                    &nbsp;
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="rule-text text-gray-700 lh-base fw-medium">
                                                            {{ $displayText }}
                                                        </div>
                                                    </div>

                                                    @if (!empty($remainingLines))
                                                        <div class="sub-rules ms-5">
                                                            @foreach ($remainingLines as $subRule)
                                                                @if (trim($subRule) !== '')
                                                                    <div
                                                                        class="sub-rule-item d-flex align-items-start p-2 ps-4 mb-1">
                                                                        <div class="sub-rule-marker me-3 pt-1">
                                                                            <span class="text-muted">•</span>
                                                                        </div>
                                                                        <div class="sub-rule-text text-gray-600 lh-base">
                                                                            {{ trim($subRule) }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="rules-footer mt-4 pt-3 border-top text-center">
                                        <div class="d-inline-flex align-items-center bg-light px-3 py-2 rounded-pill">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            <small class="text-muted">Please read all rules carefully before
                                                making a reservation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="imageModalLabel">
                        <i class="fas fa-image me-2"></i>
                        <span id="imageModalTitle">{{ $facility->name }}</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex align-items-center justify-content-center p-0">
                    <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                        <img id="modalImage" src="" alt="" class="img-fluid"
                            style="max-height: 90vh; max-width: 100%; object-fit: contain;">

                        <button type="button" id="modalPrevBtn"
                            class="btn btn-light position-absolute start-0 top-50 translate-middle-y ms-3 rounded-circle"
                            style="width: 50px; height: 50px; opacity: 0.8;">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" id="modalNextBtn"
                            class="btn btn-light position-absolute end-0 top-50 translate-middle-y me-3 rounded-circle"
                            style="width: 50px; height: 50px; opacity: 0.8;">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <div class="text-white-50 small">
                        <span id="imageCounter">1 of 1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addonDescModal" tabindex="-1" aria-labelledby="addonDescModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #1864ab;">
                    <h5 class="modal-title" id="addonDescModalLabel">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="addonModalName">Add-on Details</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="addon-description-content">
                        <p id="addonModalDescription" class="text-gray-700 lh-lg mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <button type="button" class="btn"
                        style="background-color: #3b82f6; color: white; border-color: #3b82f6;"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <hr class="mt-5 text-secondary" />
@endsection


@push('scripts')
    <script>
        function validateForm() {
            const checkbox = document.getElementById('agreeToRules');
            if (!checkbox) {
                return true;
            }
            if (!checkbox.checked) {
                checkbox.setCustomValidity('You must agree to the rules and regulations.');
                return false;
            }
            checkbox.setCustomValidity('');
            return true;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const clientTypeSelect = document.getElementById("client_type");
            const wholeClientTypeSelect = document.getElementById("whole_client_type");
            const priceIdSelect = document.getElementById("price_id");
            const wholePriceIdSelect = document.getElementById("whole_price_id");

            function handleDiscountNote(selectElement, noteId) {
                if (!selectElement) return;

                const discountNote = document.getElementById(noteId || "discount-note");
                if (!discountNote) return;

                selectElement.addEventListener("change", function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const isDiscount = selectedOption.getAttribute("data-discount") === "1";

                    if (isDiscount) {
                        discountNote.style.display = "block";
                    } else {
                        discountNote.style.display = "none";
                    }
                });
                if (selectElement.value) {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const isDiscount = selectedOption.getAttribute("data-discount") === "1";
                    discountNote.style.display = isDiscount ? "block" : "none";
                }
            }
            handleDiscountNote(clientTypeSelect, "discount-note");
            handleDiscountNote(wholeClientTypeSelect, "discount-note");
            handleDiscountNote(priceIdSelect, "discount-note");
            handleDiscountNote(wholePriceIdSelect, "discount-note");
        });
    </script>

    <script>
        // image modal and swipers
        document.addEventListener('DOMContentLoaded', function() {
            const addonDescModal = document.getElementById('addonDescModal');
            const discountSelect = document.getElementById('discount_id');

            if (discountSelect) {
                discountSelect.addEventListener('change', function() {
                    if (typeof updateTotalPrice === 'function') {
                        updateTotalPrice();
                    }
                });
            }

            if (addonDescModal) {
                addonDescModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const addonName = button.getAttribute('data-addon-name');
                    const addonDescription = button.getAttribute('data-addon-description');

                    document.getElementById('addonModalName').textContent = addonName;
                    document.getElementById('addonModalDescription').textContent = addonDescription;
                });
            }

            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const imageCounter = document.getElementById('imageCounter');
            const modalPrevBtn = document.getElementById('modalPrevBtn');
            const modalNextBtn = document.getElementById('modalNextBtn');

            let allImages = [];
            let currentImageIndex = 0;

            const mainImage = "{{ asset('storage/' . $facility->image) }}";
            allImages.push({
                src: mainImage,
                alt: "{{ $facility->name }}"
            });

            @foreach (explode(',', $facility->images) as $gimg)
                allImages.push({
                    src: "{{ asset('storage/' . trim($gimg)) }}",
                    alt: "{{ $facility->name }}"
                });
            @endforeach

            function updateModalImage(index) {
                if (allImages[index]) {
                    modalImage.src = allImages[index].src;
                    modalImage.alt = allImages[index].alt;
                    imageCounter.textContent = `${index + 1} of ${allImages.length}`;
                    currentImageIndex = index;

                    modalPrevBtn.style.display = allImages.length > 1 ? 'block' : 'none';
                    modalNextBtn.style.display = allImages.length > 1 ? 'block' : 'none';
                }
            }

            function showPrevImage() {
                const newIndex = currentImageIndex > 0 ? currentImageIndex - 1 : allImages.length - 1;
                updateModalImage(newIndex);
            }

            function showNextImage() {
                const newIndex = currentImageIndex < allImages.length - 1 ? currentImageIndex + 1 : 0;
                updateModalImage(newIndex);
            }

            if (imageModal) {
                imageModal.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    const imageSrc = trigger.getAttribute('data-image-src');
                    const clickedIndex = allImages.findIndex(img => img.src === imageSrc);
                    updateModalImage(clickedIndex >= 0 ? clickedIndex : 0);
                });

                modalPrevBtn.addEventListener('click', showPrevImage);
                modalNextBtn.addEventListener('click', showNextImage);

                imageModal.addEventListener('keydown', function(event) {
                    if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        showPrevImage();
                    } else if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        showNextImage();
                    }
                });
            }

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
                breakpoints: {
                    0: {
                        direction: 'horizontal',
                        slidesPerView: 4,
                        spaceBetween: 8,
                    },
                    576: {
                        direction: 'horizontal',
                        slidesPerView: 5,
                        spaceBetween: 10,
                    },
                    768: {
                        direction: 'vertical',
                        slidesPerView: 'auto',
                        spaceBetween: 10,
                    },
                },
            });

            document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', function() {
                    mainSwiper.slideToLoop(index);
                    document.querySelectorAll('.thumbnail-img').forEach((thumb) =>
                        thumb.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            const firstThumb = document.querySelector('.thumbnail-img');
            if (firstThumb) {
                firstThumb.classList.add('active');
            }

            window.validateForm = validateForm;
        });
    </script>
@endpush
