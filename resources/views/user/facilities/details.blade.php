@extends('layouts.app')
@section('content')
    <style>
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
            border-radius: 8px;
        }

        /* Card-like effect for thumbnails */
        .thumbnail-card {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            margin: 5px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        .thumbnail-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        /* Styling for the navigation buttons */
        .swiper-button-next,
        .swiper-button-prev {
            color: #000;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            padding: 10px;
        }

        /* Styling for the main image */
        .main-image img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        #calendar {
            width: 100%;
            max-width: 370px;
        }

        .fc .fc-toolbar {
            font-size: 12px;
        }

        .fc .fc-button {
            font-size: 12px;
            padding: 4px 8px;
        }

        .fc .fc-daygrid-day-number {
            font-size: 14px;
        }

        .fc .fc-daygrid-day {
            padding: 5px;
        }

        .fc .fc-daygrid {
            height: auto;
        }

        .fc-view-dayGridWeek-button {
            display: none;
        }

        .fc .fc-toolbar-title {
            font-size: 1em;
        }

        /* Responsive behavior */
        @media (max-width: 768px) {
            #calendar {
                max-width: 100%;
                padding: 0;
            }

            .fc .fc-button {
                font-size: 10px;
                padding: 2px 4px;
            }

            .fc .fc-daygrid-day-number {
                font-size: 12px;
            }
        }

        .price-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .price-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        #calendar {
            min-height: 350px;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover:not(:disabled) {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .text-primary {
            color: #4e73df !important;
        }
        
        .bg-primary {
            background-color: #4e73df !important;
        }
        
        .border {
            border-color: #e3e6f0 !important;
        }
        
        .calendar-container {
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        
        /* FullCalendar Styling */
        .fc-day-today {
            background-color: rgba(78, 115, 223, 0.1) !important;
        }
        
        .fc-highlight {
            background-color: rgba(78, 115, 223, 0.2) !important;
        }
        
        .btn:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }
        .fc-day-reserved {
            background-color: red !important;
            color: white !important;
        }

        /* Style for Rules and Regulation */
        .rental-single__details-tab {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            transition: all 0.3s ease;
            position: relative;
            margin-bottom: -1px;
        }

        .nav-tabs .nav-link:hover {
            color: #4e73df;
            background-color: rgba(78, 115, 223, 0.05);
        }

        .nav-tabs .nav-link.active {
            color: #4e73df;
            background-color: white;
            border-bottom: 3px solid #4e73df;
            font-weight: 600;
        }

        .hover-bg-light:hover {
            background-color: rgba(248, 249, 250, 0.8) !important;
        }

        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .text-gray-700 {
            color: #4a5568;
        }

        .form-check-input:invalid {
            border-color: #dc3545;
        }

        .form-check-input:invalid ~ .invalid-feedback {
            display: block;
        }

        .form-check-label a {
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .form-check-label a:hover {
            text-decoration: underline;
            color: #2e59d9;
        }

        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.2em;
            cursor: pointer;
        }

        .invalid-feedback {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            color: #dc3545;
            display: none;
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

                    <form action="{{ route('facility.reserve') }}" method="POST" style="margin: 0">
                        @csrf
                        <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                        <input type="hidden" name="total_price" id="total-price-field" value="0">
                        <input type="hidden" name="facility_type" value="{{ $facility->facility_type }}">
                        <input type="hidden" name="selected_price" id="selected_price">

                        @if($facility->facility_type === 'individual')
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
                        $facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
                        @include('components.facility_both_rooms')
                    @endif

                    @if (
                        $facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty()
                    )
                        @include('components.facility_both_building')
                    @endif

                       {{-- must be disabled for validation--}}
                         <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                            style="padding: 15px 30px; font-size: 18px">
                            Reserve
                        </button>
                    </form>
                </div>
        </section>

       <div class="rental-single__details-tab mt-5">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-justified border-0 mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-dark fw-medium px-4 py-3 rounded-top active" 
                    id="tab-description-tab" data-bs-toggle="tab" href="#tab-description" 
                    role="tab" aria-controls="tab-description" aria-selected="true">
                    <i class="fas fa-align-left me-2"></i>Description
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-dark fw-medium px-4 py-3 rounded-top" 
                    id="tab-rules-tab" data-bs-toggle="tab" href="#tab-rules" 
                    role="tab" aria-controls="tab-rules" aria-selected="false">
                    <i class="fas fa-clipboard-check me-2"></i>Rules & Regulations
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content bg-white rounded-3 shadow-sm p-4">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="tab-description" role="tabpanel" aria-labelledby="tab-description-tab">
                    <div class="rental-single__description text-gray-700 lh-lg">
                        {{ $facility->description }}
                    </div>
                </div>

                <!-- Rules Tab -->
                <div class="tab-pane fade" id="tab-rules" role="tabpanel" aria-labelledby="tab-rules-tab">
                    <div class="rental-single__rules mb-4">
                        <!-- Rules Header (Collapsible) -->
                        <div class="rules-header cursor-pointer" data-bs-toggle="collapse" data-bs-target="#rules-content" aria-expanded="false">
                            <div class="d-flex justify-content-between align-items-center p-4 bg-light rounded-3 shadow-sm">
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

                        <!-- Rules Content (Collapsible) -->
                        <div class="collapse" id="rules-content">
                            <div class="rules-container bg-white p-0 mt-3">
                                <div class="rules-content">
                                    @php
                                        $rules = array_filter(explode("\n", $facility->rules_and_regulations));
                                    @endphp

                                    <div class="rules-sections">
                                        @foreach ($rules as $index => $rule)
                                            @if (trim($rule) !== '')
                                                <div class="rule-item d-flex align-items-start p-3 mb-2 rounded-3 hover-bg-light">
                                                    <div class="rule-number me-3 pt-1">
                                                        <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                            style="width: 26px; height: 26px;">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </div>
                                                    <div class="rule-text text-gray-700 lh-base">
                                                        {!! trim($rule) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="rules-footer mt-4 pt-3 border-top text-center">
                                        <div class="d-inline-flex align-items-center bg-light px-3 py-2 rounded-pill">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            <small class="text-muted">Please read all rules carefully before making a reservation</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agreement Checkbox -->
                   <div class="form-check mt-4 ms-4 ps-0">
                        <div class="border-top pt-4">
                            <input class="form-check-input me-2" type="checkbox" id="agreeToRules" required>
                            <label class="form-check-label text-gray-700" for="agreeToRules">
                                I agree to the <a href="#tab-rules" data-bs-toggle="collapse" 
                                data-bs-target="#rules-content" class="text-primary fw-medium">Rules and Regulations</a>
                            </label>
                            <div class="invalid-feedback text-danger mt-2">
                                <i class="fas fa-exclamation-circle me-2"></i>You must agree to the rules and regulations before proceeding.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <hr class="mt-5 text-secondary" />
@endsection

@push('scripts')
    <script>
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

@if ($facility->facility_type === 'whole_place')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateFromInput = document.getElementById('date_from');
            var dateToInput = document.getElementById('date_to');
            var timeStartInput = document.getElementById('time_start');
            var timeEndInput = document.getElementById('time_end');
            var clientTypeDropdown = document.getElementById('client_type');
            var totalPriceElement = document.getElementById('total-price').querySelector('span');
            
            var hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
            var userType = @json(auth()->user()->utype ?? 'USR');
            
            var availabilities = @json($facility->availabilities ?? []);
            var facilityCapacity = @json($wholeAttr->whole_capacity ?? 0);

            var today = new Date();
            var tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 3);
            var tomorrowFormatted = tomorrow.toISOString().split('T')[0];

            function getMaxDate() {
                if (userType === 'ADM') return null;
                var maxDate = new Date();
                maxDate.setMonth(maxDate.getMonth() + 1);
                return maxDate.toISOString().split('T')[0];
            }

            timeStartInput.value = '07:00';
            calculateEndTime();

            function isDateFullyBooked(dateStr) {
                if (!availabilities || availabilities.length === 0) return false;
                
                const checkDate = new Date(dateStr);
                let totalBookedCapacity = 0;
                let hasMatchingAvailability = false;
                
                availabilities.forEach(function(availability) {
                    const availFromDate = new Date(availability.date_from);
                    const availToDate = new Date(availability.date_to);
                    
                    if (checkDate >= availFromDate && checkDate <= availToDate) {
                        hasMatchingAvailability = true;
                        
                        if (availability.remaining_capacity <= 0) {
                            totalBookedCapacity = facilityCapacity;
                            return;
                        }
                        
                        const bookedCapacity = facilityCapacity - availability.remaining_capacity;
                        totalBookedCapacity += bookedCapacity;
                    }
                });
                
                return hasMatchingAvailability && (totalBookedCapacity >= facilityCapacity);
            }

            function getReservedDates() {
                if (!availabilities || availabilities.length === 0) return [];
                
                const reservedDates = [];
                
                availabilities.forEach(function(availability) {
                    if (availability.remaining_capacity <= 0) {
                        const startDate = new Date(availability.date_from);
                        const endDate = new Date(availability.date_to);
                        
                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            const dateStr = d.toISOString().split('T')[0];
                            if (!reservedDates.includes(dateStr)) {
                                reservedDates.push(dateStr);
                            }
                        }
                    }
                });
                
                return reservedDates;
            }

            if (!hasDayBasedPricing) {
                const calendarEl = document.getElementById('calendar');
                if (calendarEl) {
                    let selectedDates = [];
                    let startDate = null;
                    let endDate = null;
                    
                    const calendarOptions = {
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
                        validRange: { start: tomorrowFormatted },
                        
                        dateClick: function(info) {
                            const clickedDate = info.dateStr;
                            const dateEl = info.dayEl;
                            
                            if (isDateFullyBooked(clickedDate)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Unavailable',
                                    text: 'This date is fully reserved and unavailable for booking.'
                                });
                                return;
                            }
                            
                            if (typeof calendar.clickCount === 'undefined') {
                                calendar.clickCount = 0;
                            }
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
                                        
                                        if (hasReservedDate) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Unavailable',
                                                text: 'One or more dates in your selected range are reserved.'
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
                            
                            updateInputs();
                            updateDateDisplay();
                            updateTotalPrice();
                            highlightDates();
                        },
                        
                        dayCellClassNames: function(info) {
                            let classes = [];
                            
                            if (isDateFullyBooked(info.dateStr)) {
                                classes.push('fully-booked-date');
                            }
                            
                            if (selectedDates.includes(info.dateStr) && !isDateFullyBooked(info.dateStr)) {
                                if (info.dateStr === startDate) {
                                    classes.push('selected-start-date');
                                } else if (info.dateStr === endDate) {
                                    classes.push('selected-end-date');
                                } else {
                                    classes.push('selected-range-date');
                                }
                            }
                            
                            return classes;
                        },
                        
                        events: function(fetchInfo, successCallback, failureCallback) {
                            const reservedDates = getReservedDates();
                            const events = reservedDates.map(date => ({
                                id: `reserved-${date}`,
                                title: 'booked',
                                start: date,
                                allDay: true,
                                backgroundColor: 'transparent',
                                borderColor: '#bd2130',
                                textColor: 'white',
                                classNames: ['fully-booked-event'],
                                display: 'block'
                            }));
                            
                            successCallback(events);
                        }
                    };

                    if (userType === 'USR') {
                        calendarOptions.validRange.end = getMaxDate();
                    }

                    const calendar = new FullCalendar.Calendar(calendarEl, calendarOptions);
                    calendar.render();
                    
                    function getDatesInRange(start, end) {
                        const dates = [];
                        const startDate = new Date(start);
                        const endDate = new Date(end);
                        
                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            dates.push(d.toISOString().split('T')[0]);
                        }
                        
                        return dates;
                    }
                    
                    function updateDateDisplay() {
                        const startDisplay = document.getElementById('start-date-display');
                        const endDisplay = document.getElementById('end-date-display');
                        const modalStart = document.getElementById('modal-start-date');
                        const modalEnd = document.getElementById('modal-end-date');
                        
                        if (startDate) {
                            const formattedStart = new Date(startDate).toLocaleDateString('en-US', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            startDisplay.textContent = formattedStart;
                            modalStart.textContent = formattedStart;
                        } else {
                            startDisplay.textContent = 'Click on calendar to select';
                            modalStart.textContent = 'Not selected';
                        }
                        
                        if (endDate) {
                            const formattedEnd = new Date(endDate).toLocaleDateString('en-US', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            endDisplay.textContent = formattedEnd;
                            modalEnd.textContent = formattedEnd;
                        } else {
                            endDisplay.textContent = 'Click on calendar to select';
                            modalEnd.textContent = 'Not selected';
                        }
                    }
                    
                    function updateInputs() {
                        dateFromInput.value = startDate || '';
                        dateToInput.value = endDate || '';
                    }
                    
                    function highlightDates() {
                        calendar.render();
                    }

                    document.getElementById('calendarModal').addEventListener('shown.bs.modal', function () {
                        calendar.updateSize();
                    });
                }
            }

            function calculateEndTime() {
                var st = timeStartInput.value;
                if (!st) return;
                var parts = st.split(':').map(Number),
                    h = (parts[0] + 8) % 24,
                    m = parts[1];
                timeEndInput.value = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
            }

            function updateTotalPrice() {
                var ctVal = clientTypeDropdown.value;
                var total = 0;
                
                if (hasDayBasedPricing) {
                    if (ctVal) {
                        total = parseFloat(ctVal);
                        totalPriceElement.textContent = '₱' + total.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    } else {
                        totalPriceElement.textContent = '₱0.00';
                    }
                } else {
                    var f = dateFromInput.value;
                    var t = dateToInput.value;

                    if (ctVal && f && t) {
                        var fromDate = new Date(f),
                            toDate = new Date(t),
                            diffMs = toDate - fromDate,
                            daysDiff = Math.floor(diffMs / (1000*60*60*24)) + 1;
                        
                        if (daysDiff > 0) {
                            total = daysDiff * parseFloat(ctVal);
                            totalPriceElement.textContent = '₱' + total.toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } else {
                            totalPriceElement.textContent = '₱0.00';
                        }
                    } else {
                        totalPriceElement.textContent = '₱0.00';
                    }
                }
                
                document.getElementById('total_price_input').value = total.toFixed(2);
            }

            timeStartInput.addEventListener('input', calculateEndTime);
            clientTypeDropdown.addEventListener('change', updateTotalPrice);
            
            if (!hasDayBasedPricing) {
                dateFromInput.addEventListener('change', updateTotalPrice);
                dateToInput.addEventListener('change', updateTotalPrice);
            }
        });

        document.getElementById('client_type').addEventListener('change', function() {
            var selectedPrice = this.value;  
            document.getElementById('selected_price').value = selectedPrice;  
            updateTotalPrice();  
        });
    </script>
@endif


@if ($facility->facility_type === 'individual')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateFromInput = document.getElementById('date_from');
            var dateToInput = document.getElementById('date_to');
            var startDateDisplay = document.getElementById('start-date-display');
            var endDateDisplay = document.getElementById('end-date-display');
            var modalStartDate = document.getElementById('modal-start-date');
            var modalEndDate = document.getElementById('modal-end-date');
            var clearDatesBtn = document.getElementById('clear-dates');
            
            var availabilities = @json($facility->availabilities ?? []);
            var facilityAttributes = @json($facility->facilityAttributes ?? []);
            var isBasedOnDays = @json($facility->prices->where('is_based_on_days', true)->count() > 0);
            
            let selectedDates = [];
            let startDate = null;
            let endDate = null;
            
            function formatDate(dateInput) {
                if (!dateInput) return null;
                
                let dateStr;
                if (dateInput instanceof Date) {
                    dateStr = dateInput.getFullYear() + '-' + 
                            String(dateInput.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(dateInput.getDate()).padStart(2, '0');
                } else if (typeof dateInput === 'string') {
                    if (dateInput.includes('T')) {
                        dateStr = dateInput.split('T')[0];
                    } else {
                        dateStr = dateInput;
                    }
                }
                
                return dateStr;
            }

            function createLocalDate(dateStr) {
                if (!dateStr) return null;
                const parts = dateStr.split('-');
                return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            }

            function isDateInRange(checkDate, startDate, endDate) {
                const check = formatDate(checkDate);
                const start = formatDate(startDate);
                const end = formatDate(endDate);
                
                if (!check || !start || !end) return false;
                
                return check >= start && check <= end;
            }

            function getRoomAvailabilityForDate(dateStr) {
                const selectedRoomId = document.getElementById('room_selection')?.value;
                if (!selectedRoomId) return { remaining: 0, isFullyBooked: true };
                
                const selectedRoom = facilityAttributes.find(attr => attr.id == selectedRoomId);
                const roomCapacity = selectedRoom ? selectedRoom.capacity : 0;
                
                if (!availabilities || availabilities.length === 0) {
                    return {
                        remaining: roomCapacity,
                        isFullyBooked: false
                    };
                }
                
                const formattedDate = formatDate(dateStr);
                
                let remainingCapacity = roomCapacity;
                let isFullyBooked = false;
                
                const matchingAvailabilities = availabilities
                    .filter(avail => avail.facility_attribute_id == selectedRoomId)
                    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                
                for (const avail of matchingAvailabilities) {
                    if (isDateInRange(formattedDate, avail.date_from, avail.date_to)) {
                        remainingCapacity = avail.remaining_capacity;
                        isFullyBooked = remainingCapacity <= 0;
                        break;
                    }
                }
                
                return {
                    remaining: remainingCapacity,
                    isFullyBooked: isFullyBooked
                };
            }

            function getReservedDates() {
                const selectedRoomId = document.getElementById('room_selection')?.value;
                if (!selectedRoomId || !availabilities || availabilities.length === 0) {
                    return [];
                }
                
                const reservedDates = [];
                
                availabilities.forEach(function(availability) {
                    if (availability.facility_attribute_id == selectedRoomId && 
                        availability.remaining_capacity <= 0 &&
                        availability.date_from && availability.date_to) {
                        
                        const startDate = createLocalDate(availability.date_from);
                        const endDate = createLocalDate(availability.date_to);
                        
                        if (!startDate || !endDate) return;
                        
                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            const dateStr = formatDate(d);
                            if (dateStr && !reservedDates.includes(dateStr)) {
                                reservedDates.push(dateStr);
                            }
                        }
                    }
                });
                
                return reservedDates;
            }

            function getDatesInRange(start, end) {
                const dates = [];
                const startDate = createLocalDate(start);
                const endDate = createLocalDate(end);
                
                if (!startDate || !endDate) return dates;
                
                for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                    dates.push(formatDate(d));
                }
                
                return dates;
            }

            function showRoomSelectionAlert() {
                Swal.fire({
                    icon: 'info',
                    title: 'Unavailable',
                    text: 'Please select a room number first to view the calendar and choose available dates.',
                    confirmButtonColor: '#3085d6',
                });
            }

            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    initialDate: new Date(),
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    selectable: true,
                    selectMirror: true,
                    dayMaxEvents: true,
                    weekends: true,
                    validRange: { start: formatDate(new Date()) },
                    
                    dateClick: function(info) {
                        const clickedDate = info.dateStr;
                        const selectedRoomId = document.getElementById('room_selection')?.value;
                        
                        if (!selectedRoomId) {
                            showRoomSelectionAlert();
                            return;
                        }
                        
                        const roomAvailability = getRoomAvailabilityForDate(clickedDate);
                        if (roomAvailability.isFullyBooked) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Unavailable',
                                text: 'This date is fully reserved and unavailable for booking.',
                                confirmButtonColor: '#3085d6',
                            });
                            return;
                        }
                        
                        if (!startDate) {
                            startDate = clickedDate;
                            selectedDates = [clickedDate];
                        } else if (!endDate && clickedDate >= startDate) {
                            endDate = clickedDate;
                            selectedDates = getDatesInRange(startDate, endDate);
                            
                            const hasReservedDate = selectedDates.some(date => {
                                return getRoomAvailabilityForDate(date).isFullyBooked;
                            });
                            
                            if (hasReservedDate) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Unavailable Dates',
                                    text: 'One or more dates in your selected range are reserved.',
                                    confirmButtonColor: '#3085d6',
                                });
                                endDate = null;
                                return;
                            }
                        } else {
                            startDate = clickedDate;
                            endDate = null;
                            selectedDates = [clickedDate];
                        }
                        
                        updateInputs();
                        updateDateDisplay();
                        updateTotalPrice();
                        highlightDates();
                    },
                    
                    dayCellClassNames: function(info) {
                        const dateStr = info.dateStr;
                        const classes = [];
                        const roomAvailability = getRoomAvailabilityForDate(dateStr);
                        
                        if (roomAvailability.isFullyBooked) {
                            classes.push('fully-booked-date');
                        } else if (selectedDates.includes(dateStr)) {
                            if (dateStr === startDate) classes.push('selected-start-date');
                            else if (dateStr === endDate) classes.push('selected-end-date');
                            else classes.push('selected-range-date');
                        }
                        
                        return classes;
                    },
                    
                    dayCellContent: function(args) {
                        const dateStr = args.dateStr || formatDate(args.date);
                        const roomAvailability = getRoomAvailabilityForDate(dateStr);
                        const selectedRoomId = document.getElementById('room_selection')?.value;
                        const selectedRoom = facilityAttributes.find(attr => attr.id == selectedRoomId);
                        const roomCapacity = selectedRoom ? selectedRoom.capacity : 0;
                        
                        const container = document.createElement('div');
                        container.style.height = '100%';
                        container.style.display = 'flex';
                        container.style.flexDirection = 'column';
                        container.style.justifyContent = 'space-between';
                        
                        const dateNumberEl = document.createElement('div');
                        dateNumberEl.className = 'fc-daygrid-day-number';
                        dateNumberEl.textContent = args.date.getDate();
                        dateNumberEl.style.textAlign = 'right';
                        dateNumberEl.style.padding = '2px';
                        
                        const availabilityEl = document.createElement('div');
                        availabilityEl.className = 'availability-indicator';
                        availabilityEl.style.textAlign = 'center';
                        availabilityEl.style.margin = '2px 0';
                        availabilityEl.style.fontSize = '10px';
                        
                        if (!selectedRoomId) {
                            availabilityEl.innerHTML = '<span class="badge bg-secondary">Select room</span>';
                        } 
                        else if (roomAvailability.isFullyBooked) {
                            availabilityEl.innerHTML = '<span class="badge bg-danger">Booked</span>';
                        } 
                        else {
                            availabilityEl.innerHTML = `
                                <span class="badge bg-${roomAvailability.remaining < 3 ? 'warning' : 'success'}">
                                    ${roomAvailability.remaining}/${roomCapacity} left
                                </span>
                            `;
                        }
                        
                        container.appendChild(dateNumberEl);
                        container.appendChild(availabilityEl);
                        
                        return { domNodes: [container] };
                    },
                    
                    events: function(fetchInfo, successCallback, failureCallback) {
                        const reservedDates = getReservedDates();
                        const events = reservedDates.map(date => ({}));
                        successCallback(events);
                    }
                });
                
                window.calendar = calendar;
                calendar.render();

                $('#calendarModal').on('shown.bs.modal', function() {
                    if (window.calendar) {
                        window.calendar.gotoDate(new Date()); 
                    }
                });
                
                function updateDateDisplay() {
                    const formattedStart = startDate ? new Date(startDate).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : 'Not selected';
                    
                    const formattedEnd = endDate ? new Date(endDate).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : 'Not selected';
                    
                    startDateDisplay.textContent = formattedStart;
                    endDateDisplay.textContent = formattedEnd;
                    modalStartDate.textContent = formattedStart;
                    modalEndDate.textContent = formattedEnd;
                    
                    confirmDatesBtn.disabled = !startDate;
                }
                
                function updateInputs() {
                    if (dateFromInput) dateFromInput.value = startDate || '';
                    if (dateToInput) dateToInput.value = endDate || '';
                }
                
                function highlightDates() {
                    calendar.refetchEvents();
                    calendar.render();
                }
                
            
                    
            
            }
        });

        function updateRoomInfo() {
            const select = document.getElementById('room_selection');
            const selectedOption = select.options[select.selectedIndex];
            const roomInfoDiv = document.getElementById('selected-room-info');
            
            if (select.value) {
                const roomName = selectedOption.getAttribute('data-room-name');
                const capacity = selectedOption.getAttribute('data-capacity');
                const sexRestriction = selectedOption.getAttribute('data-sex-restriction');
                
                document.getElementById('selected-room-name').textContent = roomName;
                document.getElementById('selected-capacity').textContent = capacity + ' person(s)';
                
                document.getElementById('hidden_room_name').value = roomName;
                document.getElementById('hidden_room_capacity').value = capacity;
                
                const sexBadge = document.getElementById('selected-sex-badge');
                const sexIcon = document.getElementById('selected-sex-icon');
                const sexText = document.getElementById('selected-sex-text');
                
                if (sexRestriction) {
                    sexIcon.className = 'fa fa-' + (sexRestriction === 'male' ? 'mars' : 'venus') + ' me-1';
                    sexText.textContent = sexRestriction.charAt(0).toUpperCase() + sexRestriction.slice(1);
                    sexBadge.style.display = 'inline-block';
                } else {
                    sexBadge.style.display = 'none';
                }
                
                roomInfoDiv.style.display = 'flex';
            } else {
                roomInfoDiv.style.display = 'none';
                document.getElementById('hidden_room_name').value = '';
                document.getElementById('hidden_room_capacity').value = '';
            }
            
            if (window.calendar) {
                window.calendar.refetchEvents();
                window.calendar.render();
            }
        }

        function updateTotalPrice() {
            let totalPrice = 0;
            
            document.querySelectorAll('.quantity-input').forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const priceText = input.closest('.form-floating').querySelector('.product-type').textContent;
                const priceValue = parseFloat(priceText.replace(/[^0-9.]/g, ''));

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
            
            const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            document.getElementById('computed-total').textContent = formattedTotal;
            document.getElementById('total_price_input').value = totalPrice.toFixed(2);
        }

        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', updateTotalPrice);
        });

        const priceSelect = document.getElementById('price_id');
        if (priceSelect) {
            priceSelect.addEventListener('change', function() {
                document.getElementById('selected_price_value').value = 
                    this.options[this.selectedIndex].getAttribute('data-value');
                updateTotalPrice();
            });
        }
    </script>
@endif







    



@endpush
