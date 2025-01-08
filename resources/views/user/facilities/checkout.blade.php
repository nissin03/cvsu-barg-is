@extends('layouts.app')
@section('content')
    <style>
        #submit-button {
            --bs-btn-disabled-color: #6c757d;
            --bs-btn-disabled-bg: #e9ecef;
            --bs-btn-hover-color: #6c757d;
            --bs-btn-hover-bg: #e9ecef;
            --bs-btn-hover-border-color: transparent;


            cursor: not-allowed !important;
            pointer-events: none;
        }

        #submit-button:disabled {
            opacity: 0.65;
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

        // Handle different pages
        if ($currentRoute === 'rentals.checkout') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-8 col-lg-6">
            <form name="checkout-form" action="{{ route('user.facilities.placeReservation') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="facility_id" value="{{ $reservationData['facility_id'] }}">


                @if ($facility->facility_type === 'individual')
                    <input type="hidden" name="facility_attribute_id"
                        value="{{ $facilityAttribute->id ?? $reservationData['facility_attribute_id'] }}">
                    <input type="hidden" name="date_from" value="{{ $reservationData['date_from'] }}">
                    <input type="hidden" name="date_to" value="{{ $reservationData['date_to'] }}">
                @elseif($facility->facility_type === 'whole_place')
                    <div class="my-2">
                        {{-- <label for="calendar"><strong>Select Reservation Date:</strong></label>
                        <div id="calendar"></div> --}}
                        {{-- <input type="hidden" id="date_from" name="date_from" value="{{ old('date_from') }}">
                        <input type="hidden" id="date_to" name="date_to" value="{{ old('date_to') }}"> --}}
                        <input type="hidden" id="date_from" name="date_from" value="{{ old('date_from', $reservationData['date_from'] ?? '') }}">
                        <input type="hidden" id="date_to" name="date_to" value="{{ old('date_to', $reservationData['date_to'] ?? '') }}">
                        <div id="selected-date" class="my-3">
                            @if (isset($date_from))
                                {{-- <p class="select-date"><strong>Selected Date:</strong> {{ $date_from }}</p> --}}
                                <p class="select-date"><strong>Selected Date:</strong> {{ $reservationData['date_from'] }}</p>
                            @endif
                        </div>
                        @error('date_from')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('date_to')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <input type="hidden" name="total_price" value="{{ $reservationData['total_price'] }}">
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Requirements</h5>
                    @if ($facility && $facility->requirements)
                        @php
                            $fileExtension = strtolower(pathinfo($facility->requirements, PATHINFO_EXTENSION));
                            $isImageReq = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                        @endphp
                        @if (!$isImageReq)
                            <p>
                                <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Document
                                </a>
                            </p>
                        @else
                            <p>
                                <a href="{{ asset('/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Image
                                </a>
                            </p>
                        @endif
                    @else
                        <p>No Requirements document uploaded.</p>
                    @endif
                    @error('requirements')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="qualification">Qualification Document (PDF/DOC) <small>(Optional)</small></label>
                    <input type="file" id="qualification" name="qualification" class="form-control"
                        accept=".pdf,.doc,.docx" required>

                    @error('qualification')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="order-summary mb-4">
                    <h3 class="text-center">Reservation Details</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($facility->facility_type === 'individual')
                                <tr>
                                    <td><strong>{{ $roomName }}</strong></td>
                                    <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Date From</strong></td>
                                    <td class="text-end"><strong>{{ $date_from }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Date To</strong></td>
                                    <td class="text-end"><strong>{{ $date_to }}</strong></td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Total Price</strong></td>
                                <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- <button type="submit" class="btn btn-warning w-100">Place Reservation</button> --}}

                <button type="submit" class="btn btn-warning w-100">Place Reservation</button>

            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

    <script>
       document.addEventListener('DOMContentLoaded', function() {
        @if ($facility->facility_type === 'whole_place')
        var calendarEl = document.getElementById('calendar');
        var submitButton = document.getElementById('submit-button');
        var selectedDateEl = document.getElementById('selected-date');

        // Calculate the start date (3 days from today)
        var today = new Date();
        var startDate = new Date();
        startDate.setDate(today.getDate() + 3);

        // Initialize FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            selectMirror: true,
            timeZone: 'local', 
            select: function(info) {
                // Ensure the selected date is valid
                var selectedDate = info.start;

                // Compare dates (ignoring time)
                var selected = new Date(selectedDate.getFullYear(), selectedDate.getMonth(),
                    selectedDate.getDate());
                var minDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate
                    .getDate());

                if (selected >= minDate) {
                    var year = selected.getFullYear();
                    var month = String(selected.getMonth() + 1).padStart(2, '0'); // Months are zero-based
                    var day = String(selected.getDate()).padStart(2, '0');
                    var formattedDate = `${year}-${month}-${day}`;

                    // var formattedDate = selected.toISOString().split('T')[0];    
                    document.getElementById('date_from').value = formattedDate;
                    document.getElementById('date_to').value = formattedDate; // Automatically set date_to

                    // Update the displayed selected date
                    if (selectedDateEl) {
                        selectedDateEl.innerText = 'Selected Date: ' + formattedDate;
                    }

                    // Enable the submit button
                    if (submitButton) {
                        submitButton.disabled = false;
                    }

                    // Remove existing background events
                    calendar.getEvents().forEach(function(event) {
                        if (event.display === 'background') {
                            event.remove();
                        }
                    });

                    // Add a new background event to highlight the selected date
                    calendar.addEvent({
                        title: 'Selected',
                        start: selectedDate,
                        allDay: true,
                        display: 'background',
                        backgroundColor: '#B0E0E6' // Light blue background
                    });
                } else {
                    alert('Please select a date starting from ' + minDate.toISOString().split(
                        'T')[0]);
                    calendar.unselect();
                }
            },
            validRange: {
                start: startDate.toISOString().split('T')[0], // 3 days from today
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek,dayGridDay'
            },
            // Disable dateClick to prevent individual date selection
            dateClick: function(info) {
                // Do nothing
            },
        });

        calendar.render();

        // Ensure the submit button is disabled initially
        if (submitButton) {
            submitButton.disabled = true;
        }
        @endif
    });
    </script>
@endpush
