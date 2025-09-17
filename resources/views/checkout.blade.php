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
                            <button type="submit" class="btn reservation-btn" id="placeReservationBtn" disabled>
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
        let selectedDate = null;

        function dateClick(info) {
            const clickedDate = info.date;
            const day = clickedDate.getDay();
            if (day < 1 || day > 4) {
                return;
            }
            if (isDateDisabled(clickedDate)) {
                return;
            }

            const calendarApi = info.view.calendar;
            if (clickedDate.getMonth() !== calendarApi.getDate().getMonth()) {
                calendarApi.gotoDate(clickedDate);
                return;
            }

            const dateStr = clickedDate.toLocaleDateString('en-CA');
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
                });

            $('.fc-day').removeClass('selected-date');
            setTimeout(() => {
                const selector = `.fc-day[data-date="${dateStr}"]`;
                $(selector).addClass('selected-date');
            }, 0);
            calendarApi.unselect();
        }

        function isDateDisabled(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const dateMidnight = new Date(date);
            dateMidnight.setHours(0, 0, 0, 0);

            if (dateMidnight < today) {
                return false;
            }

            let businessDaysCount = 0;
            let checkDate = new Date(today);

            while (businessDaysCount < 2) {
                const dayOfWeek = checkDate.getDay();
                if (dayOfWeek >= 1 && dayOfWeek <= 4) {
                    const checkDateMidnight = new Date(checkDate);
                    checkDateMidnight.setHours(0, 0, 0, 0);

                    if (checkDateMidnight.getTime() === dateMidnight.getTime()) {
                        return true;
                    }
                    businessDaysCount++;
                }
                checkDate.setDate(checkDate.getDate() + 1);
            }
            return false;
        }
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const today = new Date();
            const todayDate = today.toISOString().split('T')[0];
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);
            const nextMonthDate =
                `${nextMonth.getFullYear()}-${String(nextMonth.getMonth() + 1).padStart(2, '0')}-${String(nextMonth.getDate()).padStart(2, '0')}`;

            const todayMidnight = new Date(today);
            todayMidnight.setHours(0, 0, 0, 0);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                aspectRatio: window.innerWidth < 768 ? 1.0 : window.innerWidth < 992 ? 1.3 : 1.6,
                initialView: 'dayGridMonth',
                selectable: true,
                dateClick: dateClick,
                height: 'auto',
                contentHeight: 'auto',
                validRange: {
                    start: todayDate,
                    end: nextMonthDate,
                },
                selectAllow: function(selectInfo) {
                    const day = selectInfo.start.getDay();
                    return day >= 1 && day <= 4 && !isDateDisabled(selectInfo.start);
                },
                dayCellClassNames: function(arg) {
                    const day = arg.date.getDay();
                    const dateMidnight = new Date(arg.date);
                    dateMidnight.setHours(0, 0, 0, 0);

                    const dateStr = dateMidnight.toISOString().split('T')[0];
                    const isDisabled = dateMidnight < todayMidnight ||
                        day === 0 || day === 5 || day === 6 ||
                        isDateDisabled(arg.date);
                    if (isDisabled) {
                        return ['fc-disabled-day'];
                    }
                },
                windowResize: function(view) {
                    calendar.setOption('aspectRatio', window.innerWidth < 768 ? 1.0 :
                        window.innerWidth < 992 ? 1.3 : 1.6);
                }
            });

            calendar.render();

            $(document).on('click', '.time-btn', function() {
                $('.time-btn').removeClass('active');
                $(this).addClass('active');

                const time = $(this).data('time');
                const slots = window.slotAvailability?.[time] ?? 0;

                $('#selectedSlots').text(slots).toggleClass('text-danger', slots === 0);
                $('#slotDisplay').removeClass('d-none');
                $('#time_slot').val(time);

                const selectedISODate = $('#reservation_date').val();
                const formattedDate = new Date(selectedISODate).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: '2-digit'
                });
                $('#displayDate').text(formattedDate);
                $('#displayTime').text($('#time_slot').val() || '');
                toggleSubmitButton();
            });
        });

        function toggleSubmitButton() {
            const date = $('#reservation_date').val();
            const time = $('#time_slot').val();
            $('#placeReservationBtn').prop('disabled', !(date && time));
        }
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

        .fc {
            width: 100% !important;
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
        }
    </style>
@endpush
