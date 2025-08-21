<link href="{{ asset('css/facility/whole_place.css') }}" rel="stylesheet"> 

<div class="facility-booking-container mb-4">
  <input type="hidden" name="facility_attribute_id" value="{{ $wholeAttr?->id ?? '' }}">
    @if ($facility->prices->isNotEmpty())
        @foreach ($facility->prices as $price)
        @endforeach
    @else
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <div>No prices available for this facility.</div>
        </div>
    @endif
    
    <div class="capacity-info">
        <div class="capacity-card">
            <i class="fa fa-users"></i>
            <span class="capacity-text">Capacity:</span>
            <span class="capacity-value">{{ $wholeAttr->whole_capacity }}</span>
        </div>
    </div>
    
    @php
        $hasDayBasedPricing = $facility->prices->contains('is_based_on_days', true);
    @endphp
    
    @if ($hasDayBasedPricing)
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calendar-alt"></i>
                <span>Date Scheduled:</span>
            </div>
            <div class="section-content">
                @php
                    $firstPrice = $facility->prices->where('is_based_on_days', true)->first();
                @endphp
                
                @if ($firstPrice && $firstPrice->date_from && $firstPrice->date_to)
                    <div class="date-range-display">
                        <div class="date-item">
                            <strong>From:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_from)->format('M d, Y') }}
                        </div>
                        <div class="date-item">
                            <strong>To:</strong> {{ \Carbon\Carbon::parse($firstPrice->date_to)->format('M d, Y') }}
                        </div>
                    </div>
                    <input type="hidden" id="date_from" name="date_from" value="{{ $firstPrice->date_from }}">
                    <input type="hidden" id="date_to" name="date_to" value="{{ $firstPrice->date_to }}">
                @endif
            </div>
        </div>
    @else
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calendar-alt"></i>
                <span>Select Dates</span>
            </div>
            <div class="section-content">
                <input type="hidden" id="date_from" name="date_from" required>
                <input type="hidden" id="date_to" name="date_to" required>
                
                <div class="selected-dates-display">
                    <div class="date-selection-item">
                        <strong>Start Date:</strong> 
                        <span id="start-date-display"></span>
                    </div>
                    <div class="date-selection-item">
                        <strong>End Date:</strong> 
                        <span id="end-date-display"></span>
                    </div>
                </div>
                
                <div id="error-message" class="error-message"></div>
                
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#calendarModal">
                    <i class="fa fa-calendar me-2"></i> Open Calendar
                </button>
            </div>
        </div>
        
        <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarModalLabel">Select Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8">
                                    <div id="calendar" style="max-width: 100%; margin: 0 auto;"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="selected-dates-card p-3">
                                        <h6 class="fw-bold mb-3">Selected Dates</h6>
                                        <div class="mb-2">
                                            <small class="text-muted">Start Date:</small>
                                            <div id="modal-start-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">End Date:</small>
                                            <div id="modal-end-date" class="fw-bold">Not selected</div>
                                        </div>
                                        <div class="d-grid gap-2">
                                           <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Confirm" id="confirm-dates">
                                                    Confirm Selection
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-clock"></i>
            <span>Time Selection</span>
        </div>
        <div class="section-content">
            <div id="time-slot-container" class="time-slot-grid">
                <div class="time-input-group">
                    <label for="time_start" class="time-label">Start Time</label>
                    <select id="time_start" name="time_start" class="form-select time-select">
                        @for($hour = 7; $hour <= 23; $hour++)
                            @php
                                $displayHour = $hour > 12 ? $hour - 12 : $hour;
                                $ampm = $hour >= 12 ? 'PM' : 'AM';
                                if ($hour === 12) $displayHour = 12;
                                if ($hour === 0) $displayHour = 12;
                                $value = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                $display = $displayHour . ':00 ' . $ampm;
                            @endphp
                            <option value="{{ $value }}">{{ $display }}</option>
                        @endfor
                    </select>
                </div>
                <div class="time-input-group">
                    <label for="time_end" class="time-label">End Time</label>
                    <select id="time_end" name="time_end" class="form-select time-select" disabled>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-user-tag"></i>
            <span><strong>Client Type:</strong></span>
        </div>
        <div class="section-content">
            <select id="client_type" class="client-type-select">
                <option value="" disabled selected>Select a client type</option>
                @foreach ($facility->prices as $price)
                    <option value="{{ $price->value }}" data-name="{{ $price->name }}">
                        {{ $price->name }} (₱{{ number_format($price->value, 2) }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    
    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price: </strong>
        <span class="total-price-value">₱ 0.00</span>
    </div>

    <input type="hidden" name="total_price" id="total_price_input" value="0">
</div>

@if ($facility->facility_type === 'whole_place')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateFromInput = document.getElementById('date_from');
            var dateToInput = document.getElementById('date_to');
            var timeStartSelect = document.getElementById('time_start');
            var timeEndSelect = document.getElementById('time_end');
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

            function formatTimeTo12Hour(time) {
                var hour = parseInt(time.split(':')[0]);
                var minutes = time.split(':')[1];
                var ampm = hour >= 12 ? 'PM' : 'AM';
                var displayHour = hour % 12;
                if (displayHour === 0) displayHour = 12;
                return displayHour + ':' + minutes + ' ' + ampm;
            }

            function updateEndTimeOptions() {
                var startTime = timeStartSelect.value;
                if (!startTime) return;
                
                timeEndSelect.innerHTML = '';
                
                var startHour = parseInt(startTime.split(':')[0]);
                var maxHour = Math.min(startHour + 8, 24);
                
                for (var hour = startHour + 1; hour <= maxHour; hour++) {
                    var option = document.createElement('option');
                    var value = (hour === 24 ? '00' : String(hour).padStart(2, '0')) + ':00';
                    var displayHour = hour > 12 ? hour - 12 : hour;
                    if (hour === 12) displayHour = 12;
                    if (hour === 0) displayHour = 12;
                    var ampm = hour >= 12 ? 'PM' : 'AM';
                    option.value = value;
                    option.textContent = displayHour + ':00 ' + ampm;
                    timeEndSelect.appendChild(option);
                }
                
                timeEndSelect.disabled = false;
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

            timeStartSelect.addEventListener('change', function() {
                updateEndTimeOptions();
                updateTotalPrice();
            });
            
            timeEndSelect.addEventListener('change', updateTotalPrice);
            clientTypeDropdown.addEventListener('change', updateTotalPrice);
            
            if (!hasDayBasedPricing) {
                dateFromInput.addEventListener('change', updateTotalPrice);
                dateToInput.addEventListener('change', updateTotalPrice);
            }
            
            updateEndTimeOptions();
        });

        document.getElementById('client_type').addEventListener('change', function() {
            var selectedPrice = this.value;  
            document.getElementById('selected_price').value = selectedPrice;  
            updateTotalPrice();  
        });
    </script>
@endif

{{-- Validation --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reserveBtn = document.getElementById('reserve-btn');
    const clientTypeDropdown = document.getElementById('client_type');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const hasDayBasedPricing = @json($facility->prices->contains('is_based_on_days', true));
    
    reserveBtn.disabled = true;
    
    function validateReserveButton() {
        if (hasDayBasedPricing) {
            reserveBtn.disabled = !clientTypeDropdown.value;
        } else {
            const hasClientType = clientTypeDropdown.value;
            const hasDateFrom = dateFromInput.value;
            const hasDateTo = dateToInput.value;
            
            reserveBtn.disabled = !(hasClientType && hasDateFrom && hasDateTo);
        }
    }
    
    clientTypeDropdown.addEventListener('change', validateReserveButton);
    
    if (!hasDayBasedPricing) {
        dateFromInput.addEventListener('change', validateReserveButton);
        dateToInput.addEventListener('change', validateReserveButton);
        
        const observer = new MutationObserver(validateReserveButton);
        observer.observe(dateFromInput, { attributes: true, attributeFilter: ['value'] });
        observer.observe(dateToInput, { attributes: true, attributeFilter: ['value'] });
        
        let lastDateFrom = '';
        let lastDateTo = '';
        
        const checkDateChanges = () => {
            if (dateFromInput.value !== lastDateFrom || dateToInput.value !== lastDateTo) {
                lastDateFrom = dateFromInput.value;
                lastDateTo = dateToInput.value;
                validateReserveButton();
            }
        };
        
        setInterval(checkDateChanges, 500);
    }
    
    window.validateReserveButton = validateReserveButton;
    
    validateReserveButton();
    setTimeout(validateReserveButton, 1000);
});
</script>



