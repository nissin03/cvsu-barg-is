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
            <div class="row">
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

                        @if ($facility->facility_type === 'individual')
                            <input type="hidden" name="facility_attribute_id" value="{{ $availableRoom->id ?? '' }}">
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
                                        // Split the rules into sections based on numbered rules
                                        $rulesSections = preg_split('/(?=\d+\.)/', $facility->rules_and_regulations);
                                        $rulesSections = array_filter(array_map('trim', $rulesSections));
                                    @endphp

                                    <div class="rules-sections">
                                        @foreach ($rulesSections as $section)
                                            @if (trim($section) !== '')
                                                @php
                                                    // Extract the rule number and content
                                                    $lines = explode("\n", $section);
                                                    $firstLine = trim($lines[0]);
                                                    $remainingLines = array_slice($lines, 1);

                                                    // Check if the line starts with a number followed by a dot
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
                                            <small class="text-muted">Please read all rules carefully before making a
                                                reservation</small>
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
        // image modal and swipers
        document.addEventListener('DOMContentLoaded', function() {
            const addonDescModal = document.getElementById('addonDescModal');

            addonDescModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const addonName = button.getAttribute('data-addon-name');
                const addonDescription = button.getAttribute('data-addon-description');

                document.getElementById('addonModalName').textContent = addonName;
                document.getElementById('addonModalDescription').textContent = addonDescription;
            });

            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const imageModalTitle = document.getElementById('imageModalTitle');
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

            imageModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const imageSrc = trigger.getAttribute('data-image-src');
                const imageAlt = trigger.getAttribute('data-image-alt');

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



    {{-- @if ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dateFromInput = document.getElementById('date_from');
        var dateToInput = document.getElementById('date_to');
        var wholeDateFromInput = document.getElementById('whole_date_from');
        var wholeDateToInput = document.getElementById('whole_date_to');
        var sharedDateFromInput = document.getElementById('date_from');
        var sharedDateToInput = document.getElementById('date_to');
        var wholeTimeStartInput = document.getElementById('whole_time_start');
        var wholeTimeEndInput = document.getElementById('whole_time_end');
        var wholeClientTypeDropdown = document.getElementById('whole_client_type');

        var today = new Date();
        var tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 7);
        var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        
        var userType = @json(auth()->user()->utype);
        var maxDate = null;
        var maxDateFormatted = null;
        
        if (userType === 'USR') {
            maxDate = new Date(today);
            maxDate.setMonth(maxDate.getMonth() + 3);
            maxDateFormatted = maxDate.toISOString().split('T')[0];
        }
        
        if (dateFromInput) {
            dateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) dateFromInput.max = maxDateFormatted;
        }
        if (dateToInput) {
            dateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) dateToInput.max = maxDateFormatted;
        }
        if (wholeDateFromInput) {
            wholeDateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) wholeDateFromInput.max = maxDateFormatted;
        }
        if (wholeDateToInput) {
            wholeDateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) wholeDateToInput.max = maxDateFormatted;
        }
        if (sharedDateFromInput) {
            sharedDateFromInput.min = tomorrowFormatted;
            if (maxDateFormatted) sharedDateFromInput.max = maxDateFormatted;
        }
        if (sharedDateToInput) {
            sharedDateToInput.min = tomorrowFormatted;
            if (maxDateFormatted) sharedDateToInput.max = maxDateFormatted;
        }

        if (wholeTimeStartInput) {
            wholeTimeStartInput.value = '07:00';
            calculateWholeEndTime();
        }

        var hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
        var availabilities = @json($facility->availabilities ?? []);
        var facilityAttributes = @json($facility->facilityAttributes ?? []);
        var facilityCapacity = @json($wholeAttr->whole_capacity ?? 0);
        var wholeCalendarInitialized = false;
        var sharedCalendarInitialized = false;

        function getAvailabilityForDate(dateStr) {
            const checkDate = new Date(dateStr);
            const matchingAvailabilities = availabilities.filter(avail => {
                const availFrom = avail.date_from ? new Date(avail.date_from) : null;
                const availTo = avail.date_to ? new Date(avail.date_to) : null;
                
                if (avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString()) {
                    return true;
                }
                if (availFrom && availTo && checkDate >= availFrom && checkDate <= availTo) {
                    return true;
                }
                return false;
            });
            
            return matchingAvailabilities[0];
        }

        function getMinCapacityForDateRange(startDate, endDate) {
            const dates = getDatesInRange(startDate, endDate);
            let minCapacity = facilityCapacity;
            
            dates.forEach(dateStr => {
                const availability = getAvailabilityForDate(dateStr);
                if (availability) {
                    if (availability.remaining_capacity < minCapacity) {
                        minCapacity = availability.remaining_capacity;
                    }
                }
            });
            
            return minCapacity;
        }

        function isDateFullyBooked(dateStr) {
            const availability = getAvailabilityForDate(dateStr);
            if (!availability) return false;
            return availability.remaining_capacity <= 0;
        }

        function isDateOccupied(dateStr) {
            const availability = getAvailabilityForDate(dateStr);
            if (!availability) return false;
            const attribute = facilityAttributes.find(attr => attr.id === availability.facility_attribute_id);
            if (!attribute) return false;
            return attribute.whole_capacity !== availability.remaining_capacity;
        }

        function formatDateForDisplay(dateStr) {
            if (!dateStr) return 'Not selected';
            const date = new Date(dateStr + 'T00:00:00+08:00');
            return date.toLocaleDateString('en-PH', { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        function getDatesInRange(start, end) {
            const dates = [];
            const startDate = new Date(start);
            const endDate = new Date(end);
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                dates.push(d.toISOString().split('T')[0]);
            }
            return dates;
        }

        function updateDateDisplay(startDisplay, endDisplay, startDate, endDate) {
            if (startDisplay) {
                startDisplay.textContent = formatDateForDisplay(startDate);
            }
            if (endDisplay) {
                endDisplay.textContent = formatDateForDisplay(endDate);
            }
        }

        function calculateWholeEndTime() {
            if (!wholeTimeStartInput || !wholeTimeEndInput) return;
            var st = wholeTimeStartInput.value;
            if (!st) return;
            var parts = st.split(':').map(Number),
                h = (parts[0] + 8) % 24,
                m = parts[1];
            wholeTimeEndInput.value = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
        }

        function updateRequiredFields(activeSection) {
            document.querySelectorAll('#shared-section [required], #whole-section [required]').forEach(field => {
                field.removeAttribute('required');
            });
            if (activeSection === 'shared') {
                const sharedFields = document.querySelectorAll('#shared-section input[type="date"], #shared-section select');
                sharedFields.forEach(field => {
                    if (field.style.display !== 'none' && !field.closest('[style*="display: none"]')) {
                        field.setAttribute('required', 'required');
                    }
                });
            } else if (activeSection === 'whole') {
                if (wholeDateFromInput) wholeDateFromInput.setAttribute('required', 'required');
                if (wholeDateToInput) wholeDateToInput.setAttribute('required', 'required');
                if (wholeClientTypeDropdown) wholeClientTypeDropdown.setAttribute('required', 'required');
            }
        }

        function initializeCalendar(section) {
            const isWhole = section === 'whole';
            const calendarEl = document.getElementById(isWhole ? 'whole-calendar' : 'shared-calendar');
            const modalId = isWhole ? 'wholeCalendarModal' : 'sharedCalendarModal';
            const modalEl = document.getElementById(modalId);
            
            if (!modalEl || !calendarEl) return;
            if ((isWhole && wholeCalendarInitialized) || (!isWhole && sharedCalendarInitialized)) return;
            
            modalEl.addEventListener('shown.bs.modal', function initCalendarOnShow() {
                modalEl.removeEventListener('shown.bs.modal', initCalendarOnShow);
                
                if ((isWhole && wholeCalendarInitialized) || (!isWhole && sharedCalendarInitialized)) {
                    const calendar = calendarEl._fullCalendar;
                    if (calendar) {
                        calendar.render();
                        return;
                    }
                }

                const dateFromInput = isWhole ? wholeDateFromInput : sharedDateFromInput;
                const dateToInput = isWhole ? wholeDateToInput : sharedDateToInput;
                const startDisplay = document.getElementById(isWhole ? 'whole-start-date-display' : 'shared-start-date-display');
                const endDisplay = document.getElementById(isWhole ? 'whole-end-date-display' : 'shared-end-date-display');
                const modalStartDisplay = document.getElementById(isWhole ? 'whole-modal-start-date' : 'shared-modal-start-date');
                const modalEndDisplay = document.getElementById(isWhole ? 'whole-modal-end-date' : 'shared-modal-end-date');
                const confirmButton = document.getElementById(isWhole ? 'whole-confirm-dates' : 'shared-confirm-dates');
                
                if (!hasDayBasedPricing && calendarEl && !calendarEl._fullCalendar) {
                    let selectedDates = [];
                    let startDate = null;
                    let endDate = null;
                    
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        timeZone: 'Asia/Manila',
                        locale: 'en',
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth'
                        },
                        selectable: true,
                        selectMirror: true,
                        dayMaxEvents: false,
                        weekends: true,
                        validRange: { 
                            start: tomorrowFormatted,
                            end: maxDateFormatted || undefined
                        },
                        moreLinkClick: function(info) { return false; },
                        dateClick: function(info) {
                            const clickedDate = info.dateStr;
                            if (isDateFullyBooked(clickedDate)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Not Available',
                                    text: 'This date is fully reserved and unavailable for booking.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (isWhole && isDateOccupied(clickedDate)) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Partially Occupied',
                                    text: 'This date is partially occupied. Please select dates that are completely available.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (typeof calendar.clickCount === 'undefined') calendar.clickCount = 0;
                            calendar.clickCount++;
                            if (calendar.clickCount % 2 === 1) {
                                startDate = clickedDate;
                                selectedDates = [clickedDate];
                                endDate = null;
                                
                                if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                            } else {
                                if (startDate) {
                                    const start = new Date(startDate);
                                    const end = new Date(clickedDate);
                                    if (end >= start) {
                                        const dateRange = getDatesInRange(startDate, clickedDate);
                                        const hasReservedDate = dateRange.some(date => isDateFullyBooked(date));
                                        const hasOccupiedDate = isWhole ? dateRange.some(date => isDateOccupied(date)) : false;
                                        if (hasReservedDate) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Reserved Dates',
                                                text: 'One or more dates in your selected range are fully reserved.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        if (hasOccupiedDate) {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Occupied Dates',
                                                text: 'One or more dates in your selected range are partially occupied. Please select a different range.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        endDate = clickedDate;
                                        selectedDates = dateRange;
                                        
                                        if (modalEndDisplay) modalEndDisplay.textContent = formatDateForDisplay(endDate);
                                    } else {
                                        startDate = clickedDate;
                                        selectedDates = [clickedDate];
                                        endDate = null;
                                        calendar.clickCount = 1;
                                        
                                        if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                        if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                                    }
                                } else {
                                    startDate = clickedDate;
                                    selectedDates = [clickedDate];
                                    endDate = null;
                                    calendar.clickCount = 1;
                                    
                                    if (modalStartDisplay) modalStartDisplay.textContent = formatDateForDisplay(startDate);
                                    if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                                }
                            }
                            if (dateFromInput) dateFromInput.value = startDate || '';
                            if (dateToInput) dateToInput.value = endDate || '';
                            
                            if (confirmButton) {
                                confirmButton.disabled = !(startDate && endDate);
                            }
                            
                            if (isWhole) {
                                updateWholeTotalPrice();
                            } else {
                                updateTotalPrice();
                            }
                            
                            calendar.render();
                        },
                        dayCellClassNames: function(info) {
                            let classes = [];
                            if (isDateFullyBooked(info.dateStr)) classes.push('fully-booked-date');
                            if (isDateOccupied(info.dateStr)) classes.push('occupied-date');
                            if (selectedDates.includes(info.dateStr) && !isDateFullyBooked(info.dateStr) && !isDateOccupied(info.dateStr)) {
                                if (info.dateStr === startDate) classes.push('selected-start-date');
                                else if (info.dateStr === endDate) classes.push('selected-end-date');
                                else classes.push('selected-range-date');
                            }
                            return classes;
                        },
                        dayCellContent: function(args) {
                            const dateStr = args.date.toISOString().split('T')[0];
                            const dayNumberEl = document.createElement('div');
                            dayNumberEl.className = 'fc-daygrid-day-number';
                            dayNumberEl.textContent = args.dayNumberText;
                            
                            const availability = getAvailabilityForDate(dateStr);
                            const attribute = availability ? facilityAttributes.find(attr => attr.id === availability.facility_attribute_id) : null;
                            
                            if (!isWhole) {
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity';
                                        const capacityClass = availability.remaining_capacity < 3 ? 'fc-capacity-warning' : 'fc-capacity-success';
                                        capacityEl.classList.add(capacityClass);
                                        capacityEl.textContent = `${availability.remaining_capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${facilityCapacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            } else {
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else if (attribute && availability.remaining_capacity !== attribute.whole_capacity) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-occupied';
                                        statusEl.textContent = 'Occupied';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${attribute ? attribute.whole_capacity : facilityCapacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${facilityCapacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            }
                        },
                        events: function(fetchInfo, successCallback, failureCallback) {
                            const events = availabilities.map(avail => ({
                                id: `avail-${avail.id}`,
                                start: avail.date_from,
                                end: avail.date_to ? new Date(new Date(avail.date_to).setDate(new Date(avail.date_to).getDate() + 1)) : null,
                                display: 'background',
                                backgroundColor: avail.remaining_capacity <= 0 ? 'rgba(220, 53, 69, 0.2)' : (avail.remaining_capacity < avail.whole_capacity ? 'rgba(253, 126, 20, 0.2)' : 'transparent'),
                                extendedProps: {
                                    remaining_capacity: avail.remaining_capacity
                                }
                            }));
                            successCallback(events);
                        }
                    });
                    calendar.render();
                    calendarEl._fullCalendar = calendar;
                    if (isWhole) wholeCalendarInitialized = true;
                    else sharedCalendarInitialized = true;
                }
            });
        }

        function updateModalCapacityDisplay() {
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            const modalCapacityElement = document.querySelector('#priceQuantityModal .capacity-value');
            
            if (dateFrom && dateTo && dateFrom.value && dateTo.value && modalCapacityElement) {
                const minCapacity = getMinCapacityForDateRange(dateFrom.value, dateTo.value);
                modalCapacityElement.textContent = minCapacity;
                
                const quantityInputs = document.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.max = minCapacity;
                });
            } else if (modalCapacityElement) {
                modalCapacityElement.textContent = facilityCapacity;
                
                const quantityInputs = document.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.max = facilityCapacity;
                });
            }
        }

        function validateQuantityInput(input) {
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            let maxCapacity = facilityCapacity;
            if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                maxCapacity = getMinCapacityForDateRange(dateFrom.value, dateTo.value);
            }
            
            const quantity = parseInt(input.value) || 0;
            
            if (quantity > maxCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Capacity Exceeded',
                    text: `The quantity exceeds the maximum capacity of ${maxCapacity} persons.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    input.value = '';
                    input.focus();
                });
                return false;
            }
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            let totalQuantity = 0;
            
            quantityInputs.forEach(qInput => {
                const qty = parseInt(qInput.value) || 0;
                totalQuantity += qty;
            });
            
            if (totalQuantity > maxCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Total Capacity Exceeded',
                    text: `The total number of persons (${totalQuantity}) exceeds the maximum capacity of ${maxCapacity} persons.`,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    input.value = '';
                    input.focus();
                });
                return false;
            }
            
            return true;
        }

        function updateTotalPrice() {
            let total = 0;
            const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (selectedBookingType && selectedBookingType.value === 'shared') {
                const priceDropdown = document.getElementById('price_id');
                const quantityInputs = document.querySelectorAll('.quantity-input');
                
                if (priceDropdown && priceDropdown.value) {
                    const selectedOption = priceDropdown.options[priceDropdown.selectedIndex];
                    const priceValue = parseFloat(selectedOption.dataset.value) || 0;
                    total += priceValue;
                    const selectedPriceInput = document.getElementById('selected_price_value');
                    if (selectedPriceInput) {
                        selectedPriceInput.value = priceValue;
                    }
                }
                
                quantityInputs.forEach(input => {
                    const quantity = parseInt(input.value) || 0;
                    const priceId = input.name.match(/\[(\d+)\]/)[1];
                    const priceValue = document.querySelector(`input[name="price_values[${priceId}]"]`);
                    if (priceValue && quantity > 0) {
                        total += parseFloat(priceValue.value) * quantity;
                    }
                });
                
                const dateFrom = document.getElementById('date_from');
                const dateTo = document.getElementById('date_to');
                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    const startDate = new Date(dateFrom.value);
                    const endDate = new Date(dateTo.value);
                    const timeDiff = endDate - startDate;
                    const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
                    if (daysDiff > 1) {
                        total *= daysDiff;
                    }
                }
            }
            
            const computedTotal = document.getElementById('computed-total');
            const totalPriceInput = document.getElementById('total_price_input');
            if (computedTotal) {
                computedTotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (totalPriceInput) {
                totalPriceInput.value = total.toFixed(2);
            }
        }

        function updateWholeTotalPrice() {
            let total = 0;
            const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (selectedBookingType && selectedBookingType.value === 'whole') {
                const clientTypeDropdown = document.getElementById('whole_client_type');
                if (clientTypeDropdown && clientTypeDropdown.value) {
                    const priceValue = parseFloat(clientTypeDropdown.value) || 0;
                    total = priceValue;
                    const dateFrom = document.getElementById('whole_date_from');
                    const dateTo = document.getElementById('whole_date_to');
                    if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                        const startDate = new Date(dateFrom.value);
                        const endDate = new Date(dateTo.value);
                        const timeDiff = endDate - startDate;
                        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
                        if (daysDiff > 1) {
                            total *= daysDiff;
                        }
                    }
                }
            }
            
            const computedTotal = document.getElementById('computed-total');
            const wholeTotalPriceInput = document.getElementById('whole_total_price_input');
            if (computedTotal) {
                computedTotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (wholeTotalPriceInput) {
                wholeTotalPriceInput.value = total.toFixed(2);
            }
        }

        function updateClientTypeDisplay() {
            const container = document.getElementById('selected-client-types-display');
            if (!container) return;
            
            container.innerHTML = '';
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            let hasSelection = false;
            
            quantityInputs.forEach(input => {
                if (input.value && parseInt(input.value) > 0) {
                    hasSelection = true;
                    const priceId = input.name.match(/\[(.*?)\]/)[1];
                    const priceNameInput = document.querySelector(`input[name="price_names[${priceId}]"]`);
                    const priceValueInput = document.querySelector(`input[name="price_values[${priceId}]"]`);
                    
                    if (priceNameInput && priceValueInput) {
                        const priceName = priceNameInput.value;
                        const priceValue = parseFloat(priceValueInput.value);
                        
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'client-type-item';
                        itemDiv.innerHTML = `
                            <strong>${priceName}</strong>
                            <span>Price: ₱${priceValue.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span><br>
                            <span>Qty: ${parseInt(input.value).toLocaleString()}</span>
                        `;
                        
                        container.appendChild(itemDiv);
                    }
                }
            });
            
            if (hasSelection) {
                container.style.display = 'flex'; 
            } else {
                container.style.display = 'none';
            }
            
            updateTotalPrice();
        }

        document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'shared') {
                    document.getElementById('shared-section').style.display = 'block';
                    document.getElementById('whole-section').style.display = 'none';
                    updateRequiredFields('shared');
                    if (!sharedCalendarInitialized) initializeCalendar('shared');
                } else {
                    document.getElementById('shared-section').style.display = 'none';
                    document.getElementById('whole-section').style.display = 'block';
                    updateRequiredFields('whole');
                    if (!wholeCalendarInitialized) initializeCalendar('whole');
                }
                updateTotalPrice();
            });
        });

        if (wholeTimeStartInput) {
            wholeTimeStartInput.addEventListener('change', function() {
                calculateWholeEndTime();
                updateWholeTotalPrice();
            });
        }

        if (wholeClientTypeDropdown) {
            wholeClientTypeDropdown.addEventListener('change', function() {
                updateWholeTotalPrice();
            });
        }

        const priceQuantityModal = document.getElementById('priceQuantityModal');
        if (priceQuantityModal) {
            priceQuantityModal.addEventListener('hidden.bs.modal', function () {
                updateClientTypeDisplay();
            });
        }

        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', function() {
                validateQuantityInput(this);
            });
        });

        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', function() {
                const startDate = document.getElementById('date_from').value;
                const endDate = document.getElementById('date_to').value;
                
                const startDisplay = document.getElementById('shared-start-date-display');
                const endDisplay = document.getElementById('shared-end-date-display');
                
                if (startDisplay) startDisplay.textContent = formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = formatDateForDisplay(endDate);
                
                updateModalCapacityDisplay();
            });
        }

        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', function() {
                const startDate = document.getElementById('whole_date_from').value;
                const endDate = document.getElementById('whole_date_to').value;
                
                const startDisplay = document.getElementById('whole-start-date-display');
                const endDisplay = document.getElementById('whole-end-date-display');
                
                if (startDisplay) startDisplay.textContent = formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = formatDateForDisplay(endDate);
            });
        }

        const defaultBookingType = document.querySelector('input[name="booking_type"]:checked');
        if (defaultBookingType) {
            const activeSection = defaultBookingType.value;
            updateRequiredFields(activeSection);
            if (activeSection === 'whole' && !wholeCalendarInitialized) {
                initializeCalendar('whole');
            } else if (activeSection === 'shared' && !sharedCalendarInitialized) {
                initializeCalendar('shared');
            }
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
            });
        }

        updateClientTypeDisplay();
        updateTotalPrice();
    });
</script>
@endif --}}


    @if (
        $facility->facility_type === 'both' &&
            $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
        {{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        var dateFromInput = document.getElementById('date_from');
        var dateToInput = document.getElementById('date_to');
        var wholeDateFromInput = document.getElementById('whole_date_from');
        var wholeDateToInput = document.getElementById('whole_date_to');
        var sharedDateFromInput = document.getElementById('date_from');
        var sharedDateToInput = document.getElementById('date_to');

        var today = new Date();
        var tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 7);
        var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        
        var userType = @json(auth()->user()->utype ?? 'USR');
        
        var maxDate = null;
        var maxDateFormatted = null;
        
        if (userType === 'USR') {
            maxDate = new Date(tomorrow);
            maxDate.setMonth(maxDate.getMonth() + 3);
            maxDateFormatted = maxDate.toISOString().split('T')[0];
        }
        
        if (dateFromInput) dateFromInput.min = tomorrowFormatted;
        if (dateToInput) dateToInput.min = tomorrowFormatted;
        if (wholeDateFromInput) wholeDateFromInput.min = tomorrowFormatted;
        if (wholeDateToInput) wholeDateToInput.min = tomorrowFormatted;
        if (sharedDateFromInput) sharedDateFromInput.min = tomorrowFormatted;
        if (sharedDateToInput) sharedDateToInput.min = tomorrowFormatted;
        
        if (userType === 'USR' && maxDateFormatted) {
            if (dateFromInput) dateFromInput.max = maxDateFormatted;
            if (dateToInput) dateToInput.max = maxDateFormatted;
            if (wholeDateFromInput) wholeDateFromInput.max = maxDateFormatted;
            if (wholeDateToInput) wholeDateToInput.max = maxDateFormatted;
            if (sharedDateFromInput) sharedDateFromInput.max = maxDateFormatted;
            if (sharedDateToInput) sharedDateToInput.max = maxDateFormatted;
        }

        var availabilities = @json($facility->availabilities ?? []);
        var facilityAttributes = @json($facility->facilityAttributes ?? []);
        var wholeCalendarInitialized = false;
        var sharedCalendarInitialized = false;
        var currentSelectedRoom = null;
        var currentSelectedWholeRoom = null;

        document.getElementById('shared_selected_room')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            currentSelectedRoom = selectedOption.value ? facilityAttributes.find(attr => attr.id == selectedOption.value) : null;
            
            if (selectedOption.value) {
                document.getElementById('shared_selected_room_name').value = selectedOption.getAttribute('data-room-name');
                document.getElementById('shared_selected_room_capacity').value = selectedOption.getAttribute('data-capacity');
            } else {
                document.getElementById('shared_selected_room_name').value = '';
                document.getElementById('shared_selected_room_capacity').value = '';
            }
            
            if (sharedCalendarInitialized) {
                const calendarEl = document.getElementById('calendar');
                if (calendarEl && calendarEl._fullCalendar) {
                    const calendar = calendarEl._fullCalendar;
                    calendar.render();
                    calendar.refetchEvents();
                }
            }
            
            updateTotalPrice();
        });

        document.getElementById('selected_room')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            currentSelectedWholeRoom = selectedOption.value ? facilityAttributes.find(attr => attr.id == selectedOption.value) : null;
            
            if (selectedOption.value) {
                document.getElementById('selected_room_name').value = selectedOption.getAttribute('data-room-name');
                document.getElementById('selected_room_capacity').value = selectedOption.getAttribute('data-capacity');
            } else {
                document.getElementById('selected_room_name').value = '';
                document.getElementById('selected_room_capacity').value = '';
            }
            
            if (wholeCalendarInitialized) {
                const calendarEl = document.getElementById('whole-calendar');
                if (calendarEl && calendarEl._fullCalendar) {
                    const calendar = calendarEl._fullCalendar;
                    calendar.render();
                    calendar.refetchEvents();
                }
            }
            
            updateTotalPrice();
        });

        function getAvailabilityForDate(dateStr) {
            if (!currentSelectedRoom) return null;
            const checkDate = new Date(dateStr);
            const matchingAvailabilities = availabilities.filter(avail => {
                if (avail.facility_attribute_id !== currentSelectedRoom.id) return false;
                const availFrom = avail.date_from ? new Date(avail.date_from) : null;
                const availTo = avail.date_to ? new Date(avail.date_to) : null;
                if (avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString()) {
                    return true;
                }
                if (availFrom && availTo && checkDate >= availFrom && checkDate <= availTo) {
                    return true;
                }
                return false;
            });
            return matchingAvailabilities[0];
        }

        function isDateFullyBooked(dateStr) {
            const availability = getAvailabilityForDate(dateStr);
            if (!availability) return false;
            return availability.remaining_capacity <= 0;
        }

        function isDateOccupied(dateStr) {
            if (!currentSelectedWholeRoom) return false;
            const availability = availabilities.find(avail => {
                const availFrom = avail.date_from ? new Date(avail.date_from) : null;
                const availTo = avail.date_to ? new Date(avail.date_to) : null;
                const checkDate = new Date(dateStr);
                const dateInRange = availFrom && availTo && checkDate >= availFrom && checkDate <= availTo;
                const singleDate = avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString();
                return (dateInRange || singleDate) && 
                    avail.facility_attribute_id === currentSelectedWholeRoom.id && 
                    avail.remaining_capacity < currentSelectedWholeRoom.capacity;
            });
            return !!availability;
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'Not selected';
            const date = new Date(dateStr + 'T00:00:00+08:00');
            return date.toLocaleDateString('en-PH', { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        function updateModalDateDisplay(startDisplay, endDisplay, startDate, endDate) {
            if (startDisplay) {
                startDisplay.textContent = formatDate(startDate);
            }
            if (endDisplay) {
                endDisplay.textContent = formatDate(endDate);
            }
        }

        function initializeCalendar(section) {
            const isWhole = section === 'whole';
            const modalId = isWhole ? 'wholeCalendarModal' : 'calendarModal';
            const calendarElId = isWhole ? 'whole-calendar' : 'calendar';
            const dateFromInput = isWhole ? wholeDateFromInput : sharedDateFromInput;
            const dateToInput = isWhole ? wholeDateToInput : sharedDateToInput;
            const startDisplay = document.getElementById(isWhole ? 'start-date-display' : 'shared-start-date-display');
            const endDisplay = document.getElementById(isWhole ? 'end-date-display' : 'shared-end-date-display');
            const confirmBtnId = isWhole ? 'whole-confirm-dates' : 'confirm-dates';
            const modalStartDisplay = document.getElementById(isWhole ? 'whole-modal-start-date' : 'modal-start-date');
            const modalEndDisplay = document.getElementById(isWhole ? 'whole-modal-end-date' : 'modal-end-date');
            
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    const calendarEl = document.getElementById(calendarElId);
                    if (calendarEl._fullCalendar) {
                        calendarEl._fullCalendar.destroy();
                    }

                    let selectedDates = [];
                    let startDate = dateFromInput?.value || null;
                    let endDate = dateToInput?.value || null;
                    
                    if (startDate && endDate) {
                        selectedDates = getDatesInRange(startDate, endDate);
                    }
                    
                    let validRange = { start: tomorrowFormatted };
                    if (userType === 'USR' && maxDateFormatted) {
                        validRange.end = maxDateFormatted;
                    }
                    
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        timeZone: 'Asia/Manila',
                        locale: 'en',
                        initialView: 'dayGridMonth',
                        initialDate: new Date(),
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth'
                        },
                        selectable: true,
                        selectMirror: true,
                        dayMaxEvents: false,
                        weekends: true,
                        validRange: validRange,
                        moreLinkClick: function(info) { return false; },
                        dateClick: function(info) {
                            if (!currentSelectedRoom && !isWhole) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Unavailable',
                                    text: 'Please select a room number first to view the calendar and choose available dates.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            
                            if (isWhole && !currentSelectedWholeRoom) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Unavailable',
                                    text: 'Please select a room first to view the calendar and choose available dates.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }

                            const clickedDate = info.dateStr;
                            if (isDateFullyBooked(clickedDate)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Not Available',
                                    text: 'This date is fully reserved and unavailable for booking.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (isWhole && isDateOccupied(clickedDate)) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Partially Occupied',
                                    text: 'This date is partially occupied. Please select dates that are completely available.',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }
                            if (typeof calendar.clickCount === 'undefined') calendar.clickCount = 0;
                            calendar.clickCount++;
                            if (calendar.clickCount % 2 === 1) {
                                startDate = clickedDate;
                                selectedDates = [clickedDate];
                                endDate = null;
                            } else {
                                if (startDate) {
                                    const start = new Date(startDate);
                                    const end = new Date(clickedDate);
                                    if (end >= start) {
                                        const dateRange = getDatesInRange(startDate, clickedDate);
                                        const hasReservedDate = dateRange.some(date => isDateFullyBooked(date));
                                        const hasOccupiedDate = isWhole ? dateRange.some(date => isDateOccupied(date)) : false;
                                        if (hasReservedDate) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Reserved Dates',
                                                text: 'One or more dates in your selected range are fully reserved.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        if (hasOccupiedDate) {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Occupied Dates',
                                                text: 'One or more dates in your selected range are partially occupied. Please select a different range.',
                                                confirmButtonColor: '#3085d6',
                                            });
                                            calendar.clickCount--;
                                            return;
                                        }
                                        endDate = clickedDate;
                                        selectedDates = dateRange;
                                    } else {
                                        startDate = clickedDate;
                                        selectedDates = [clickedDate];
                                        endDate = null;
                                        calendar.clickCount = 1;
                                    }
                                } else {
                                    startDate = clickedDate;
                                    selectedDates = [clickedDate];
                                    endDate = null;
                                    calendar.clickCount = 1;
                                }
                            }
                            updateModalDateDisplay(modalStartDisplay, modalEndDisplay, startDate, endDate);
                            calendar.render();
                        },
                        dayCellClassNames: function(info) {
                            let classes = [];
                            const noRoomSelected = (!currentSelectedRoom && !isWhole) || (isWhole && !currentSelectedWholeRoom);
                            if (noRoomSelected) {
                                classes.push('disabled-date');
                            }
                            if (isDateFullyBooked(info.dateStr)) classes.push('fully-booked-date');
                            if (isWhole && isDateOccupied(info.dateStr)) classes.push('occupied-date');
                            if (selectedDates.includes(info.dateStr) && !isDateFullyBooked(info.dateStr) && !(isWhole && isDateOccupied(info.dateStr))) {
                                if (info.dateStr === startDate) classes.push('selected-start-date');
                                else if (info.dateStr === endDate) classes.push('selected-end-date');
                                else classes.push('selected-range-date');
                            }
                            return classes;
                        },
                        dayCellContent: function(args) {
                            const dateStr = args.date.toISOString().split('T')[0];
                            const dayNumberEl = document.createElement('div');
                            dayNumberEl.className = 'fc-daygrid-day-number';
                            dayNumberEl.textContent = args.dayNumberText;
                            
                            if (!isWhole) {
                                if (!currentSelectedRoom) {
                                    return { domNodes: [dayNumberEl] };
                                }
                                const availability = getAvailabilityForDate(dateStr);
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity';
                                        const capacityClass = availability.remaining_capacity < 3 ? 'fc-capacity-warning' : 'fc-capacity-success';
                                        capacityEl.classList.add(capacityClass);
                                        capacityEl.textContent = `${availability.remaining_capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    const capacityEl = document.createElement('div');
                                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                    capacityEl.textContent = `${currentSelectedRoom.capacity} left`;
                                    return { domNodes: [dayNumberEl, capacityEl] };
                                }
                            } else {
                                const availability = availabilities.find(avail => {
                                    const availFrom = avail.date_from ? new Date(avail.date_from) : null;
                                    const availTo = avail.date_to ? new Date(avail.date_to) : null;
                                    const checkDate = new Date(dateStr);
                                    const dateInRange = availFrom && availTo && checkDate >= availFrom && checkDate <= availTo;
                                    const singleDate = avail.date_from && !avail.date_to && new Date(avail.date_from).toDateString() === checkDate.toDateString();
                                    return (dateInRange || singleDate) && avail.facility_attribute_id === currentSelectedWholeRoom?.id;
                                });
                                if (availability) {
                                    if (availability.remaining_capacity <= 0) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-booked';
                                        statusEl.textContent = 'Booked';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else if (availability.remaining_capacity < currentSelectedWholeRoom.capacity) {
                                        const statusEl = document.createElement('div');
                                        statusEl.className = 'fc-day-status fc-status-occupied';
                                        statusEl.textContent = 'Occupied';
                                        return { domNodes: [dayNumberEl, statusEl] };
                                    } else {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${currentSelectedWholeRoom.capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                } else {
                                    if (currentSelectedWholeRoom) {
                                        const capacityEl = document.createElement('div');
                                        capacityEl.className = 'fc-day-capacity fc-capacity-success';
                                        capacityEl.textContent = `${currentSelectedWholeRoom.capacity} left`;
                                        return { domNodes: [dayNumberEl, capacityEl] };
                                    }
                                    return { domNodes: [dayNumberEl] };
                                }
                            }
                        },
                        events: function(fetchInfo, successCallback, failureCallback) {
                            const events = availabilities.map(avail => ({
                                id: `avail-${avail.id}`,
                                start: avail.date_from,
                                end: avail.date_to ? new Date(new Date(avail.date_to).setDate(new Date(avail.date_to).getDate() + 1)) : null,
                                display: 'background',
                                backgroundColor: avail.remaining_capacity <= 0 ? 'rgba(220, 53, 69, 0.2)' : (avail.remaining_capacity < (facilityAttributes.find(attr => attr.id === avail.facility_attribute_id)?.capacity || 0) ? 'rgba(253, 126, 20, 0.2)' : 'transparent'),
                                extendedProps: {
                                    remaining_capacity: avail.remaining_capacity
                                }
                            }));
                            successCallback(events);
                        }
                    });
                    
                    calendar.render();
                    calendarEl._fullCalendar = calendar;
                    
                    if (isWhole) {
                        wholeCalendarInitialized = true;
                    } else {
                        sharedCalendarInitialized = true;
                    }
                    
                    if (startDate && endDate) {
                        updateModalDateDisplay(modalStartDisplay, modalEndDisplay, startDate, endDate);
                    }
                    
                    document.getElementById(confirmBtnId)?.addEventListener('click', function() {
                        if (startDate && endDate) {
                            dateFromInput.value = startDate;
                            dateToInput.value = endDate;
                            updateDateDisplay(startDisplay, endDisplay, startDate, endDate);
                            updateTotalPrice();
                        }
                    });
                });
            }
        }

        function getDatesInRange(start, end) {
            const dates = [];
            const startDate = new Date(start);
            const endDate = new Date(end);
            for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                dates.push(d.toISOString().split('T')[0]);
            }
            return dates;
        }

        function updateDateDisplay(startDisplay, endDisplay, startDate, endDate) {
            if (startDisplay) {
                startDisplay.textContent = formatDate(startDate);
            }
            if (endDisplay) {
                endDisplay.textContent = formatDate(endDate);
            }
        }

        function updateRequiredFields(activeSection) {
            document.querySelectorAll('#shared-section [required], #whole-section [required]').forEach(field => {
                field.removeAttribute('required');
            });
            if (activeSection === 'shared') {
                const sharedFields = document.querySelectorAll('#shared-section input[type="date"], #shared-section select');
                sharedFields.forEach(field => {
                    if (field.style.display !== 'none' && !field.closest('[style*="display: none"]')) {
                        field.setAttribute('required', 'required');
                    }
                });
            } else if (activeSection === 'whole') {
                if (wholeDateFromInput) wholeDateFromInput.setAttribute('required', 'required');
                if (wholeDateToInput) wholeDateToInput.setAttribute('required', 'required');
            }
        }

        document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'shared') {
                    document.getElementById('shared-section').style.display = 'block';
                    document.getElementById('whole-section').style.display = 'none';
                    updateRequiredFields('shared');
                    if (!sharedCalendarInitialized) initializeCalendar('shared');
                } else {
                    document.getElementById('shared-section').style.display = 'none';
                    document.getElementById('whole-section').style.display = 'block';
                    updateRequiredFields('whole');
                    if (!wholeCalendarInitialized) initializeCalendar('whole');
                }
                updateTotalPrice();
            });
        });

        const defaultBookingType = document.querySelector('input[name="booking_type"]:checked');
        if (defaultBookingType) {
            const activeSection = defaultBookingType.value;
            updateRequiredFields(activeSection);
            if (activeSection === 'whole' && !wholeCalendarInitialized) {
                initializeCalendar('whole');
            } else if (activeSection === 'shared' && !sharedCalendarInitialized) {
                initializeCalendar('shared');
            }
        }

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const activeBookingType = document.querySelector('input[name="booking_type"]:checked');
                if (activeBookingType) {
                    if (activeBookingType.value === 'whole') {
                        if (!validateWholeForm()) {
                            e.preventDefault();
                            return false;
                        }
                    } else if (activeBookingType.value === 'shared') {
                        if (!validateSharedForm()) {
                            e.preventDefault();
                            return false;
                        }
                    }
                }
            });
        }
    });

    function updateTotalPrice() {
        let totalPrice = 0;
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        
        if (bookingType === 'shared') {
            document.querySelectorAll('.quantity-input').forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const priceId = input.name.match(/\[(\d+)\]/)[1];
                const priceValue = parseFloat(document.querySelector(`input[name="price_values[${priceId}]"]`).value) || 0;

                if (!isNaN(priceValue)) {
                    let itemTotal = priceValue * quantity;
                    const dateFrom = document.getElementById('date_from');
                    const dateTo = document.getElementById('date_to');

                    if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                        const startDate = new Date(dateFrom.value);
                        const endDate = new Date(dateTo.value);
                        const diffTime = Math.abs(endDate - startDate);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        itemTotal = itemTotal * diffDays;
                    }
                    totalPrice += itemTotal;
                }
            });
            
            const priceSelect = document.getElementById('price_id');
            if (priceSelect && priceSelect.value) {
                const selectedOption = priceSelect.options[priceSelect.selectedIndex];
                const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
                document.getElementById('selected_price_value').value = selectedPrice;
                
                const dateFrom = document.getElementById('date_from');
                const dateTo = document.getElementById('date_to');
                
                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    const startDate = new Date(dateFrom.value);
                    const endDate = new Date(dateTo.value);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    totalPrice += selectedPrice * diffDays;
                } else {
                    totalPrice += selectedPrice;
                }
            }
        } else if (bookingType === 'whole') {
            const priceSelect = document.getElementById('whole_price_id');
            if (priceSelect && priceSelect.value) {
                const selectedOption = priceSelect.options[priceSelect.selectedIndex];
                const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
                
                document.getElementById('selected_whole_price_value').value = selectedPrice;
                
                const dateFrom = document.getElementById('whole_date_from');
                const dateTo = document.getElementById('whole_date_to');
                
                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    const startDate = new Date(dateFrom.value);
                    const endDate = new Date(dateTo.value);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    totalPrice += selectedPrice * diffDays;
                } else {
                    totalPrice += selectedPrice;
                }
            }
        }
        
        const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('computed-total').textContent = formattedTotal;
        
        const totalPriceField = document.getElementById('total-price-field');
        if (totalPriceField) {
            totalPriceField.value = totalPrice.toFixed(2);
        }
    }

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', updateTotalPrice);
    });

    function updateClientTypeDisplay() {
        const container = document.getElementById('selected-client-types');
        if (!container) return;
        
        container.innerHTML = '';
        let hasSelection = false;
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                hasSelection = true;
                const priceId = input.name.match(/\[(\d+)\]/)[1];
                const priceName = document.querySelector(`input[name="price_names[${priceId}]"]`).value;
                const priceValue = parseFloat(document.querySelector(`input[name="price_values[${priceId}]"]`).value);
                const total = (quantity * priceValue).toFixed(2);
                
                const item = document.createElement('div');
                item.className = 'client-type-item';
                item.innerHTML = `
                    <strong class="fw-bold">${priceName}</strong>
                    <span>Price:₱${total}</span><br>
                    <span>Qty: ${quantity}</span>
                `;
                container.appendChild(item);
            }
        });
        
        if (hasSelection) {
            container.style.display = 'flex'; 
        } else {
            container.style.display = 'none';
        }
        updateTotalPrice();
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateClientTypeDisplay();
    });
</script> --}}

        {{-- Validation --}}
        {{-- <script>
document.addEventListener('DOMContentLoaded', function() {
    const reserveBtn = document.getElementById('reserve-btn');
    reserveBtn.disabled = true;

    function checkFormValidity() {
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        let isValid = true;

        if (bookingType === 'shared') {
            const hasQuantityPrices = @json($facility->prices->where('price_type', 'individual')->where('is_there_a_quantity', true)->isNotEmpty());
            const hasNonQuantityPrices = @json($facility->prices->where('price_type', 'individual')->where('is_there_a_quantity', false)->isNotEmpty());
            const isBasedOnDays = @json($facility->prices->where('price_type', 'individual')->first()?->is_based_on_days ?? false);

            if (isBasedOnDays) {
                const dateFrom = document.getElementById('date_from')?.value;
                const dateTo = document.getElementById('date_to')?.value;
                if (!dateFrom || !dateTo) isValid = false;
            }

            const roomSelect = document.getElementById('shared_selected_room');
            if (roomSelect && roomSelect.value === "") isValid = false;

            if (hasQuantityPrices) {
                let hasQuantity = false;
                document.querySelectorAll('.quantity-input').forEach(input => {
                    if (parseInt(input.value) > 0) hasQuantity = true;
                });
                if (!hasQuantity) isValid = false;
            }

            if (hasNonQuantityPrices) {
                const priceSelect = document.getElementById('price_id');
                if (!priceSelect || priceSelect.value === "") isValid = false;
            }
        } 
        else if (bookingType === 'whole') {
            const dateFrom = document.getElementById('whole_date_from')?.value;
            const dateTo = document.getElementById('whole_date_to')?.value;
            const roomSelect = document.getElementById('selected_room');
            const priceSelect = document.getElementById('whole_price_id');

            if (!dateFrom || !dateTo) isValid = false;
            if (!roomSelect || roomSelect.value === "") isValid = false;
            if (!priceSelect || priceSelect.value === "") isValid = false;
        }

        if (isValid) {
            isValid = validateQuantitiesAgainstCapacity();
        }

        reserveBtn.disabled = !isValid;
    }

    function getMinRemainingCapacity(selectedDates) {
        let minCapacity = Infinity;
        const availabilities = @json($facility->availabilities ?? []);
        const selectedRoomId = document.getElementById('shared_selected_room')?.value;
        
        selectedDates.forEach(date => {
            const availability = availabilities.find(avail => 
                avail.facility_attribute_id == selectedRoomId &&
                new Date(date) >= new Date(avail.date_from) &&
                new Date(date) <= new Date(avail.date_to)
            );
            
            if (availability) {
                minCapacity = Math.min(minCapacity, availability.remaining_capacity);
            } else {
                const roomSelect = document.getElementById('shared_selected_room');
                const roomCapacity = roomSelect ? 
                    parseInt(roomSelect.options[roomSelect.selectedIndex]?.getAttribute('data-capacity')) : 0;
                minCapacity = Math.min(minCapacity, roomCapacity);
            }
        });
        
        return minCapacity === Infinity ? 0 : minCapacity;
    }

    function validateQuantitiesAgainstCapacity() {
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        
        if (bookingType === 'shared') {
            let maxCapacity;
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            
            if (dateFrom && dateTo) {
                const selectedDates = getDatesInRange(dateFrom, dateTo);
                maxCapacity = getMinRemainingCapacity(selectedDates);
            } else {
                const roomSelect = document.getElementById('shared_selected_room');
                const assignedRoom = document.querySelector('.capacity-card');
                
                if (roomSelect && roomSelect.value) {
                    maxCapacity = parseInt(roomSelect.options[roomSelect.selectedIndex].getAttribute('data-capacity')) || 0;
                } else if (assignedRoom) {
                    const capacityText = assignedRoom.querySelector('.capacity-value').textContent;
                    maxCapacity = parseInt(capacityText) || 0;
                }
            }
            
            const capacityValueElement = document.querySelector('.alert-info .capacity-value');
            if (capacityValueElement) {
                capacityValueElement.textContent = maxCapacity;
            }
            
            let totalQuantity = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                totalQuantity += parseInt(input.value) || 0;
            });
            
            if (totalQuantity > maxCapacity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Capacity Exceeded',
                    html: `The total number of guests (${totalQuantity}) exceeds the maximum capacity of ${maxCapacity} for the selected dates.<br><br>Please adjust your selections.`,
                    confirmButtonColor: '#3085d6',
                });
                
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.value = '';
                });
                
                updateClientTypeDisplay();
                updateTotalPrice();
                return false;
            }
        }
        return true;
    }

    function getDatesInRange(startDate, endDate) {
        const dates = [];
        const currentDate = new Date(startDate);
        const end = new Date(endDate);
        
        while (currentDate <= end) {
            dates.push(new Date(currentDate).toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        return dates;
    }

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        });
    });
    
    document.getElementById('shared_selected_room')?.addEventListener('change', function() {
        validateQuantitiesAgainstCapacity();
        checkFormValidity();
    });
    
    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        });
    });
    
    document.getElementById('confirm-dates')?.addEventListener('click', function() {
        setTimeout(function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        }, 100);
    });

    document.getElementById('price_id')?.addEventListener('change', checkFormValidity);
    document.getElementById('whole_price_id')?.addEventListener('change', checkFormValidity);
    document.getElementById('selected_room')?.addEventListener('change', checkFormValidity);
    document.getElementById('whole-confirm-dates')?.addEventListener('click', function() {
        setTimeout(checkFormValidity, 100);
    });

    // Initial check
    checkFormValidity();
});
</script> --}}
    @endif
@endpush
