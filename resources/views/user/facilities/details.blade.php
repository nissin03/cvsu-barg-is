@extends('layouts.app')
@section('content')


<link href="{{ asset('css/facility/details.css') }}" rel="stylesheet">

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
                   {{-- <div class="form-check mt-4 ms-4 ps-0">
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
                    </div> --}}
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
            let dateFromInput = document.getElementById('date_from');
            let dateToInput = document.getElementById('date_to');
            let timeStartInput = document.getElementById('time_start');
            let timeEndInput = document.getElementById('time_end');
            let clientTypeDropdown = document.getElementById('client_type');
            let totalPriceElement = document.getElementById('total-price').querySelector('span');
            
            let hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
            let userType = @json(auth()->check() ? auth()->user()->utype ?? 'USR' : 'USR');
            
            let availabilities = @json($facility->availabilities ?? []);
            let facilityCapacity = @json($wholeAttr->whole_capacity ?? 0);

            let today = new Date();
            let tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 3);
            let tomorrowFormatted = tomorrow.toISOString().split('T')[0];

            function getMaxDate() {
                if (userType === 'ADM') return null;
                let maxDate = new Date();
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


@if ($facility->facility_type === 'both' && $facility->facilityAttributes->whereNull('room_name')->whereNull('capacity')->isNotEmpty())
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
        tomorrow.setDate(tomorrow.getDate() + 3);
        var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        
        var userType = @json(auth()->check() ? auth()->user()->utype : 'USR');
        var maxDate = null;
        var maxDateFormatted = null;
        
        if (userType === 'USR') {
            maxDate = new Date(today);
            maxDate.setMonth(maxDate.getMonth() + 1);
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

        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', function() {
                const startDate = document.getElementById('date_from').value;
                const endDate = document.getElementById('date_to').value;
                
                const startDisplay = document.getElementById('shared-start-date-display');
                const endDisplay = document.getElementById('shared-end-date-display');
                
                if (startDisplay) startDisplay.textContent = formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = formatDateForDisplay(endDate);
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
@endif

@if ( $facility->facility_type === 'both' && $facility->facilityAttributes->whereNotNull('room_name')->whereNotNull('capacity')->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateFromInput = document.getElementById('date_from');
    var dateToInput = document.getElementById('date_to');
    var wholeDateFromInput = document.getElementById('whole_date_from');
    var wholeDateToInput = document.getElementById('whole_date_to');
    var sharedDateFromInput = document.getElementById('date_from');
    var sharedDateToInput = document.getElementById('date_to');

    var today = new Date();
    var tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 3);
    var tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    var userType = @json(auth()->check() ? auth()->user()->utype ?? 'USR' : 'USR');
    
    var maxDate = null;
    var maxDateFormatted = null;
    
    if (userType === 'USR') {
        maxDate = new Date(tomorrow);
        maxDate.setMonth(maxDate.getMonth() + 1);
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
</script>
@endif







    



@endpush
