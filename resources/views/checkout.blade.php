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
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endpush
