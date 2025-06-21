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

                <div class="row">
                    <div class="col-md-7 col-lg-7">
                        <div class="calendar-section mb-4">
                            <div class="section-header">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Select Date & Time</span>
                            </div>
                            <div id='calendar'></div>
                            <div class="selected-info">
                                <i class="fas fa-info-circle"></i>
                                <span class="text-muted">Selected: <strong id="displayDate"></strong> at <strong
                                        id="displayTime"></strong></span>
                            </div>
                        </div>

                        <div id="timeSlotContainer" class="d-none">
                            <div class="d-flex justify-content-center mt-3 d-none" id="slotDisplay">
                                <div class="slots-indicator">
                                    <i class="fas fa-users"></i>
                                    <span>Available Slots: <span id="selectedSlots"
                                            class="text-success fw-bold">50</span></span>
                                </div>
                            </div>
                            <div class="time-slots-container">
                                <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                    @foreach ($timeSlots as $time)
                                        <button class="btn btn-sm time-btn" type="button" data-time="{{ $time }}">
                                            <i class="fas fa-clock"></i>
                                            {{ $time }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn reservation-btn w-100" id="placeReservationBtn" disabled>
                            <i class="fas fa-check-circle"></i>
                            Place Reservation
                        </button>
                    </div>
                    <div class="col-md-5 col-lg-5">
                        @include('partials._user-info', ['user' => $user])
                        <div class="order-summary mb-4">
                            <div class="section-header">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Your Order</span>
                            </div>
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
                                            <td>{{ $item->name }} x {{ $item->qty }}</td>
                                            <td class="text-end">{{ $item->price }}</td>
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

        const today = new Date();
        const todayDate = today.toISOString().split('T')[0];

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                aspectRatio: 1.5,
                initialView: 'dayGridMonth',
                selectable: true,
                dateClick: dateClick,
                validRange: {
                    start: todayDate,
                },
                selectAllow: function(selectInfo) {
                    const day = selectInfo.start.getDay();
                    return day >= 1 && day <= 4;
                },
                dayCellClassNames: function(arg) {
                    const day = arg.date.getDay();
                    if (arg.date < today.setHours(0, 0, 0, 0) || day === 0 || day === 5 || day === 6) {
                        return ['fc-disabled-day'];
                    }
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
    <style>
        :root {
            --primary-green: #2d6a4f;
            --secondary-green: #40916c;
            --light-green: #52b788;
            --pale-green: #74c69d;
            --mint-green: #95d5b2;
            --light-mint: #b7e4c7;
            --pale-mint: #d8f3dc;
        }

        .reservation-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(45, 106, 79, 0.1);
            padding: 2rem;
            border: 1px solid var(--light-mint);
            position: relative;
            overflow: hidden;
        }

        .reservation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), var(--secondary-green), var(--light-green));
        }

        .card-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--pale-mint);
        }

        .reservation-icon {
            font-size: 3rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
            display: block;
        }

        .reservation-title {
            color: var(--primary-green);
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--pale-mint), var(--light-mint));
            border-radius: 12px;
            border-left: 4px solid var(--primary-green);
        }

        .section-header i {
            font-size: 1.25rem;
            color: var(--primary-green);
        }

        .section-header span {
            font-weight: 600;
            color: var(--primary-green);
            font-size: 1.1rem;
        }

        .user-info-section {
            background: #f8fffe;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid var(--light-mint);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .info-item:hover {
            background-color: var(--pale-mint);
        }

        .info-item i {
            color: var(--secondary-green);
            width: 20px;
            text-align: center;
        }

        .calendar-section {
            background: #f8fffe;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid var(--light-mint);
        }

        .selected-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.75rem;
            background: var(--pale-mint);
            border-radius: 10px;
        }

        .selected-info i {
            color: var(--primary-green);
        }

        .slots-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--pale-mint), var(--light-mint));
            border-radius: 25px;
            border: 1px solid var(--light-green);
        }

        .slots-indicator i {
            color: var(--primary-green);
        }

        .time-slots-container {
            background: #f8fffe;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .time-btn {
            background: linear-gradient(135deg, var(--pale-mint), var(--light-mint));
            border: 2px solid var(--light-green);
            color: var(--primary-green);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            justify-content: center;
        }

        .time-btn:hover {
            background: linear-gradient(135deg, var(--light-green), var(--secondary-green));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 106, 79, 0.3);
        }

        .time-btn.active {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border-color: var(--primary-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 106, 79, 0.4);
        }

        .time-btn.disabled {
            background: #e9ecef;
            color: #6c757d;
            border-color: #dee2e6;
            opacity: 0.6;
            cursor: not-allowed;
        }

        .time-btn.disabled:hover {
            background: #e9ecef;
            color: #6c757d;
            transform: none;
            box-shadow: none;
        }

        .order-summary {
            background: #f8fffe;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid var(--light-mint);
        }

        .order-table {
            margin-bottom: 0;
        }

        .order-table thead th {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .order-table thead th i {
            margin-right: 0.5rem;
        }

        .order-table tbody tr {
            border-bottom: 1px solid var(--light-mint);
        }

        .order-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        .total-row {
            background: var(--pale-mint);
            font-size: 1.1rem;
        }

        .total-row td {
            padding: 1rem;
            border-top: 2px solid var(--light-green);
        }

        .reservation-btn {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 5px 15px rgba(45, 106, 79, 0.3);
        }

        .reservation-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--secondary-green), var(--light-green));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 106, 79, 0.4);
        }

        .reservation-btn:disabled {
            background: #6c757d;
            color: #dee2e6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .fc-disabled-day {
            background-color: #f8d7da !important;
            pointer-events: none;
            opacity: 0.4;
        }

        .selected-date {
            background-color: var(--pale-mint) !important;
            border: 2px solid var(--primary-green) !important;
        }

        /* FullCalendar Customization */
        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: var(--light-mint);
        }

        .fc-button-primary {
            background: var(--primary-green);
            border-color: var(--primary-green);
        }

        .fc-button-primary:hover {
            background: var(--secondary-green);
            border-color: var(--secondary-green);
        }

        .fc-today {
            background-color: var(--pale-mint) !important;
        }

        .fc-day:not(.fc-disabled-day):hover {
            background-color: var(--light-mint);
            cursor: pointer;
        }

        .fc-disabled-day:hover {
            cursor: not-allowed !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .reservation-card {
                padding: 1.5rem;
                margin: 1rem;
            }

            .reservation-icon {
                font-size: 2.5rem;
            }

            .reservation-title {
                font-size: 1.5rem;
            }

            .time-btn {
                min-width: 100px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .section-header {
                padding: 0.75rem;
            }
        }
    </style>
@endpush
