@extends('layouts.app')
@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />
    <div class="container py-5">

        <div class="reservation-card">
            <div class="card-header text-center">
                <i class="fas fa-calendar-check reservation-icon"></i>
                <h2 class="reservation-title">Reservation Details</h2>
            </div>

            <form name="checkout-form" action="{{ route('cart.place.an.order') }}" method="POST">
                @csrf
                <input type="hidden" name="reservation_date" id="reservation_date">
                <input type="hidden" name="time_slot" id="time_slot">

                <!-- Mobile-first responsive layout -->
                <div class="row g-4">
                    <!-- Calendar Section - Full width on mobile, 8 cols on desktop -->
                    <div class="col-12 col-lg-8">
                        <div class="calendar-section mb-4">
                            <div class="section-header">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Select Date & Time</span>
                            </div>
                            <div class="calendar-wrapper">
                                <div id='calendar' class="calendar-container"></div>
                            </div>
                            <div class="selected-info text-center mt-3">
                                <i class="fas fa-info-circle"></i>
                                <span class="text-muted">Selected: <strong id="displayDate">None</strong> at <strong
                                        id="displayTime">None</strong></span>
                            </div>
                        </div>

                        <!-- Time Slot Section - Centered layout -->
                        <div id="timeSlotContainer" class="d-none">
                            <div class="d-flex justify-content-center mt-3 d-none" id="slotDisplay">
                                <div class="slots-indicator">
                                    <i class="fas fa-users"></i>
                                    <span>Available Slots: <span id="selectedSlots"
                                            class="text-success fw-bold">50</span></span>
                                </div>
                            </div>
                            <div class="time-slots-container mt-3">
                                <div class="time-slots-grid">
                                    @foreach ($timeSlots as $time)
                                        <button class="btn btn-outline-primary time-btn" type="button"
                                            data-time="{{ $time }}">
                                            <i class="fas fa-clock"></i>
                                            <span class="time-text">{{ $time }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Place Reservation Button - Centered -->
                        <div class="d-flex justify-content-center mt-4">
                            <button type="button" class="btn reservation-btn" id="placeReservationBtn" disabled>
                                <i class="fas fa-check-circle"></i>
                                Place Reservation
                            </button>
                        </div>
                    </div>

                    <!-- User Info and Order Summary - Full width on mobile, 4 cols on desktop -->
                    <div class="col-12 col-lg-4">
                        <div class="user-order-container">
                            @include('partials._user-info', ['user' => $user])

                            <div class="order-summary mb-4">
                                <div class="section-header">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Your Order</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table checkout-cart-item order-table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <i class="fas fa-box"></i>
                                                    PRODUCT
                                                </th>
                                                <th class="text-end">
                                                    <i class="fa-solid fa-tag"></i>
                                                    PRICE
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (Cart::instance('cart')->content() as $item)
                                                <tr>
                                                    <td class="product-name">{{ $item->name }} x {{ $item->qty }}</td>
                                                    <td class="text-end price-cell">{{ $item->price }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="total-row">
                                                <td>
                                                    <strong>
                                                        <i class="fas fa-calculator"></i>
                                                        TOTAL
                                                    </strong>
                                                </td>
                                                <td class="text-end">
                                                    <strong>{{ Cart::instance('cart')->total() }}</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
    <script>
        // Replace the existing holiday fetch and calendar initialization code with this:

        let selectedDate = null;
        let philHolidays = [];

        // Wait for holidays API before initializing calendar
        async function initializeCalendar() {
            try {
                // Fetch holidays first
                const response = await fetch(
                    `https://date.nager.at/api/v3/PublicHolidays/${new Date().getFullYear()}/PH`);
                const data = await response.json();
                philHolidays = data.map(h => ({
                    date: h.date,
                    name: h.localName || h.name
                }));
            } catch (err) {
                console.error("Holiday API error:", err);
                // Continue with empty holidays array if API fails
                philHolidays = [];
            }

            // Now initialize the calendar
            const calendarEl = document.getElementById('calendar');
            const today = new Date();

            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 3);
            const minDateStr = minDate.getFullYear() + '-' +
                String(minDate.getMonth() + 1).padStart(2, '0') + '-' +
                String(minDate.getDate()).padStart(2, '0');

            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
            const nextMonthStr = nextMonth.getFullYear() + '-' +
                String(nextMonth.getMonth() + 1).padStart(2, '0') + '-' +
                String(nextMonth.getDate()).padStart(2, '0');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                aspectRatio: window.innerWidth < 768 ? 1.0 : window.innerWidth < 992 ? 1.3 : 1.6,
                initialView: 'dayGridMonth',
                selectable: true,
                dateClick: dateClick,
                height: 'auto',
                contentHeight: 'auto',
                validRange: {
                    start: minDateStr,
                    end: nextMonthStr,
                },
                selectAllow: function(selectInfo) {
                    return !isDateDisabled(selectInfo.start);
                },
                dayCellClassNames: function(arg) {
                    const dateStr = arg.date.toISOString().split("T")[0];
                    let classes = [];

                    if (philHolidays.some(h => h.date === dateStr)) {
                        classes.push('fc-holiday');
                    }
                    if (isDateDisabled(arg.date)) {
                        classes.push('fc-disabled-day');
                    }
                    return classes;
                },
                dayCellDidMount: function(info) {
                    const dateStr = info.date.toISOString().split("T")[0];

                    const holiday = philHolidays.find(h => h.date === dateStr);
                    if (holiday) {
                        const holidayDiv = document.createElement("div");
                        holidayDiv.classList.add("holiday-label");
                        holidayDiv.textContent = holiday.name;
                        info.el.appendChild(holidayDiv);
                    }
                },
                windowResize: function(view) {
                    calendar.setOption('aspectRatio', window.innerWidth < 768 ? 1.0 :
                        window.innerWidth < 992 ? 1.3 : 1.6);
                }
            });

            calendar.render();

            // Handle time slot selection (keep your existing handler)
            $(document).on('click', '.time-btn', function() {
                if ($(this).prop('disabled')) {
                    return;
                }

                $('.time-btn').removeClass('active');
                $(this).addClass('active');

                const time = $(this).data('time');
                const slots = window.slotAvailability?.[time] ?? 0;

                $('#selectedSlots').text(slots).toggleClass('text-danger', slots === 0);
                $('#slotDisplay').removeClass('d-none');
                $('#time_slot').val(time);

                const selectedISODate = $('#reservation_date').val();
                if (selectedISODate) {
                    const formattedDate = new Date(selectedISODate + 'T00:00:00').toLocaleDateString(
                        'en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: '2-digit'
                        });
                    $('#displayDate').text(formattedDate);
                }
                $('#displayTime').text(time);

                toggleSubmitButton();
            });
        }

        // Simple function to check if a date is Monday-Thursday (1-4)
        function isValidDay(date) {
            const day = date.getDay();
            return day >= 1 && day <= 4;
        }

        // Check if date is too early (less than 3 days from today)
        function isTooEarly(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 3);

            const checkDate = new Date(date);
            checkDate.setHours(0, 0, 0, 0);

            return checkDate < minDate;
        }

        // Check if date should be disabled
        function isDateDisabled(date) {
            const dateStr = date.toISOString().split("T")[0];
            const isHoliday = philHolidays.some(h => h.date === dateStr);
            return isHoliday || isTooEarly(date) || !isValidDay(date);
        }

        function dateClick(info) {
            const clickedDate = info.date;

            if (isDateDisabled(clickedDate)) {
                return;
            }

            const calendarApi = info.view.calendar;

            if (clickedDate.getMonth() !== calendarApi.getDate().getMonth()) {
                calendarApi.gotoDate(clickedDate);
                return;
            }

            const year = clickedDate.getFullYear();
            const month = String(clickedDate.getMonth() + 1).padStart(2, '0');
            const day = String(clickedDate.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;

            const isSameDate = selectedDate === dateStr;
            selectedDate = dateStr;

            if (isSameDate) {
                return;
            }

            $('#reservation_date').val(dateStr);
            $('#timeSlotContainer').removeClass('d-none');

            if (!isSameDate) {
                $('#time_slot').val('');
                $('#selectedSlots').text('0').removeClass('text-danger');
            }

            fetch(`/api/slots?date=${dateStr}`)
                .then(res => res.json())
                .then(data => {
                    window.slotAvailability = data;
                    const currentTime = $('#time_slot').val();

                    $('.time-btn').each(function() {
                        const time = $(this).data('time');
                        const available = data[time] ?? 0;
                        const isActive = time === currentTime && available > 0;

                        $(this)
                            .prop('disabled', available === 0)
                            .toggleClass('disabled', available === 0)
                            .toggleClass('active', isActive)
                            .attr('title', `${available} slots left`);
                    });

                    const selectedSlotCount = window.slotAvailability?.[currentTime] ?? 0;
                    $('#selectedSlots').text(selectedSlotCount).toggleClass('text-danger', selectedSlotCount === 0);
                    $('#slotDisplay').toggleClass('d-none', selectedSlotCount === 0);
                    toggleSubmitButton();
                })
                .catch(error => {
                    console.error('Error fetching slots:', error);
                });

            $('.fc-day').removeClass('selected-date');
            setTimeout(() => {
                const selector = `.fc-day[data-date="${dateStr}"]`;
                $(selector).addClass('selected-date');
            }, 0);

            calendarApi.unselect();
        }

        function toggleSubmitButton() {
            const date = $('#reservation_date').val();
            const time = $('#time_slot').val();
            $('#placeReservationBtn').prop('disabled', !(date && time));
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Place reservation button handler
            const form = document.querySelector('form[name="checkout-form"]');
            const placeBtn = document.getElementById('placeReservationBtn');

            placeBtn.addEventListener('click', function() {
                if (placeBtn.disabled) {
                    return;
                }
                Swal.fire({
                    title: 'Confirm Reservation',
                    html: `
                <p class="mb-0 text-danger">
                    <strong>NOTE:</strong> This order will be <strong>automatically canceled</strong> if payment is not made
                    within 24 hours based on your <strong>reservation date</strong>.
                </p>
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, place my reservation',
                    cancelButtonText: 'Review my order',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Initialize calendar with holidays loaded
            initializeCalendar();
        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <style>
        /* Responsive Styles */
        .calendar-wrapper {
            width: 100%;
        }

        .calendar-container {
            width: 100%;
            min-height: 400px;
        }

        .fc-daygrid-day {
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .fc-daygrid-day:hover:not(.fc-disabled-day) {
            background-color: rgba(13, 110, 253, 0.05) !important;
            cursor: pointer;
        }

        .fc-daygrid-day-frame {
            padding: 4px;
            min-height: 60px;
        }


        .fc {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            width: 100%;
        }

        .fc-holiday {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%) !important;
            position: relative;
            border: 1px solid #ffcccc !important;
        }

        .fc-holiday:hover {
            background: linear-gradient(135deg, #ffe5e5 0%, #ffd5d5 100%) !important;
        }


        .holiday-label {
            position: absolute;
            bottom: 2px;
            left: 2px;
            right: 2px;
            font-size: 9px;
            color: #dc3545;
            font-weight: 600;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
            padding: 2px 4px;
            text-align: center;
            line-height: 1.2;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(220, 53, 69, 0.2);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .fc-view-harness {
            width: 100% !important;
        }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            max-width: 400px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .time-btn {
            padding: 12px 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60px;
            font-size: 0.9rem;
        }

        .time-btn i {
            margin-bottom: 4px;
            font-size: 0.8rem;
        }

        .time-text {
            font-weight: 500;
        }

        .reservation-btn {
            min-width: 200px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
        }

        .selected-info {
            background: rgba(13, 110, 253, 0.1);
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
        }

        .slots-indicator {
            background: rgba(25, 135, 84, 0.1);
            border-radius: 8px;
            padding: 8px 16px;
        }

        /* Disabled day styling */
        .fc-disabled-day {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
            position: relative;
        }

        .fc-disabled-day::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(45deg,
                    transparent,
                    transparent 10px,
                    rgba(0, 0, 0, 0.02) 10px,
                    rgba(0, 0, 0, 0.02) 20px);
            pointer-events: none;
        }

        .fc-disabled-day:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            transform: none !important;
        }

        .selected-date {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            color: white !important;
            transform: scale(1.02);
        }

        .selected-date .fc-daygrid-day-number {
            color: white !important;
            font-weight: 700 !important;
        }

        .fc-daygrid-day-number {
            padding: 6px;
            font-weight: 600;
            color: #2c3e50;
        }

        /* Today's Date Highlight */
        .fc-day-today {
            background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%) !important;
            border: 2px solid #ffc107 !important;
        }

        .fc-day-today .fc-daygrid-day-number {
            color: #f57c00;
            font-weight: 700;
        }


        /* Mobile Responsive Styles */
        @media (max-width: 767.98px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .reservation-card {
                margin: 0 -5px;
                padding: 20px 15px;
            }

            .calendar-container {
                margin-bottom: 20px;
                min-height: 320px;
            }

            .time-slots-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                max-width: 100%;
                padding: 0;
            }

            .time-btn {
                min-height: 55px;
                font-size: 0.85rem;
                padding: 10px 6px;
            }

            .selected-info {
                font-size: 0.9rem;
                text-align: center;
            }

            .section-header {
                font-size: 1rem;
                margin-bottom: 15px;
            }

            .user-order-container {
                margin-top: 30px;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .product-name {
                max-width: 200px;
                word-wrap: break-word;
            }

            .price-cell {
                white-space: nowrap;
            }

            .holiday-label {
                font-size: 8px;
                padding: 1px 3px;
                bottom: 1px;
                left: 1px;
                right: 1px;
            }

            .fc-daygrid-day-frame {
                min-height: 50px;
                padding: 2px;
            }

            .fc-toolbar-title {
                font-size: 1.2rem !important;
            }

            .fc-button {
                padding: 6px 12px !important;
                font-size: 0.875rem !important;
            }

            .fc-daygrid-day-number {
                padding: 4px;
                font-size: 0.875rem;
            }
        }

        .calendar-loading {
            position: relative;
            opacity: 0.5;
            pointer-events: none;
        }

        .calendar-loading::after {
            content: 'Loading holidays...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-weight: 600;
            color: #495057;
            z-index: 1000;
        }
    </style>
@endpush
