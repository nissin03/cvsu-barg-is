@php
    $filteredAttributes = $facility->facilityAttributes->filter(function($attribute) {
        if (auth()->user()->utype === 'ADM' || is_null($attribute->sex_restriction)) {
            return true;
        }
        return $attribute->sex_restriction === auth()->user()->sex;
    });
    
    $isBasedOnDays = $facility->prices->where('is_based_on_days', true)->count() > 0;
    
    $datedPriceWithQuantity = $facility->prices->first(function($price) {
        return $price->is_there_a_quantity && 
            $price->is_based_on_days && 
            ($price->date_from || $price->date_to);
    });
    
    $datedPrice = $facility->prices->first(function($price) {
        return !$price->is_there_a_quantity && 
            $price->is_based_on_days && 
            ($price->date_from || $price->date_to);
    });
@endphp

<link href="{{ asset('css/facility/individual.css') }}" rel="stylesheet">

<div class="facility-booking-container mb-4">
    <div class="booking-section">
        <div class="section-header">
            <i class="fa fa-door-open"></i>
            <span>Available Rooms</span>
        </div>
        <div class="section-content">
            @if($isBasedOnDays)
                @php
                    $hasAvailableRooms = false;
                    $availableRoom = $filteredAttributes->first(function($attribute) {
                        $availability = $attribute->availabilities->sortByDesc('id')->first();
                        $displayCapacity = $availability ? $availability->remaining_capacity : $attribute->capacity;
                        return $displayCapacity > 0;
                    });
                @endphp
                
                @if($availableRoom)
                    @php
                        $hasAvailableRooms = true;
                        $availability = $availableRoom->availabilities->sortByDesc('id')->first();
                        $displayCapacity = $availability ? $availability->remaining_capacity : $availableRoom->capacity;
                    @endphp
                    <div class="capacity-info mb-2">
                        <div class="capacity-card">
                            <i class="fa fa-door-open"></i>
                            <span class="capacity-text">{{ $availableRoom->room_name }}</span>
                            @if($availableRoom->sex_restriction)
                                <span class="badge bg-info ms-2">
                                    <i class="fa fa-{{ $availableRoom->sex_restriction === 'male' ? 'mars' : 'venus' }} me-1"></i>
                                    {{ ucfirst($availableRoom->sex_restriction) }} Only
                                </span>
                            @endif
                            <span class="capacity-value">{{ $displayCapacity }} person(s)</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="facility_attribute_id" value="{{ $availableRoom->id }}">
                    <input type="hidden" name="room_name" value="{{ $availableRoom->room_name }}">
                    <input type="hidden" name="room_capacity" value="{{ $displayCapacity }}">
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <div>No rooms with available capacity at the moment.</div>
                    </div>
                @endif
            @else
                @if($filteredAttributes->isNotEmpty())
                    <div class="time-input-group mb-3">
                        <label for="room_selection" class="time-label">Select Room</label>
                        <select name="facility_attribute_id" id="room_selection" class="client-type-select" onchange="updateRoomInfo()">
                            <option value="">Select a Room</option>
                            @foreach($filteredAttributes as $attribute)
                                <option value="{{ $attribute->id }}" 
                                        data-room-name="{{ $attribute->room_name }}" 
                                        data-capacity="{{ $attribute->capacity }}"
                                        data-sex-restriction="{{ $attribute->sex_restriction }}">
                                    {{ $attribute->room_name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="selected-room-info" class="capacity-info" style="display: none;">
                        <div class="capacity-card">
                            <i class="fa fa-door-open"></i>
                            <span class="capacity-text" id="selected-room-name"></span>
                            <span id="selected-sex-badge" class="badge bg-info ms-2" style="display: none;">
                                <i id="selected-sex-icon" class="me-1"></i>
                                <span id="selected-sex-text"></span> Only
                            </span>
                            <span id="selected-capacity" class="capacity-value"></span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="room_name" id="hidden_room_name" value="">
                    <input type="hidden" name="room_capacity" id="hidden_room_capacity" value="">
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <div>No rooms available for your profile.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0 || 
         $facility->prices->where('is_there_a_quantity', '!=', 1)->isNotEmpty())
        @if ($datedPriceWithQuantity || $datedPrice)
            <div class="booking-section">
                <div class="section-header">
                    <i class="fa fa-calendar-alt"></i>
                    <span>Available Date Range</span>
                </div>
                <div class="section-content">
                    <div class="date-range-display">
                        <div class="date-item">
                            <strong>From:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_from ?? $datedPrice->date_from)->format('F d, Y') }}
                        </div>
                        <div class="date-item">
                            <strong>To:</strong> {{ \Carbon\Carbon::parse($datedPriceWithQuantity->date_to ?? $datedPrice->date_to)->format('F d, Y') }}
                        </div>
                    </div>
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
                    
                    <div class="selected-dates-display mb-3">
                        <div class="date-selection-item">
                            <strong>Start Date:</strong> 
                            <span id="start-date-display"></span>
                        </div>
                        <div class="date-selection-item">
                            <strong>End Date:</strong> 
                            <span id="end-date-display"></span>
                        </div>
                    </div>
                    
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
                                                <div id="modal-start-date" class="fw-bold"></div>
                                            </div>
                                            <div class="mb-3">
                                                <small class="text-muted">End Date:</small>
                                                <div id="modal-end-date" class="fw-bold"></div>
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
    @endif

    @if ($facility->prices->where('is_there_a_quantity', true)->count() > 0)
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-calculator"></i>
                <span>Quantity Selection</span>
            </div>
            <div class="section-content">
                @foreach ($facility->prices as $price)
                    @if ($price->is_there_a_quantity)  
                        <input type="hidden" name="price_values[{{ $price->id }}]" value="{{ $price->value }}">
                        <input type="hidden" name="price_names[{{ $price->id }}]" value="{{ $price->name }}">
                        <div class="time-input-group mb-3">
                            <label for="internal_quantity-{{ $price->id }}" class="time-label">{{ $price->name }} (₱{{ number_format($price->value, 2) }})</label>
                            <input id="internal_quantity-{{ $price->id }}" type="number" 
                                class="time-input quantity-input"
                                name="internal_quantity[{{ $price->id }}]" value="{{ old('internal_quantity.' . $price->id) }}" min="0" step="1" onchange="updateTotalPrice()">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($facility->prices->where('is_there_a_quantity', '!=', 1)->isNotEmpty())
        <div class="booking-section">
            <div class="section-header">
                <i class="fa fa-tags"></i>
                <span>Price Options</span>
            </div>
            <div class="section-content">
                <div class="time-input-group">
                    {{-- <label for="price_id" class="time-label">Select Price Option</label> --}}
                    <select name="price_id" id="price_id" class="client-type-select" onchange="updateTotalPrice()">
                        <option value="">Select Price</option>
                        @foreach ($facility->prices as $price)
                            @if (!$price->is_there_a_quantity)
                                <option value="{{ $price->id }}" data-value="{{ $price->value }}">
                                    {{ $price->name }} - ₱{{ number_format($price->value, 2) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="selected_price_value" name="selected_price" value="">
            </div>
        </div>
    @endif

    <div id="total-price" class="total-price-section">
        <strong class="total-price-label">Total Price:  </strong>
        <span id="computed-total" class="total-price-value">₱ 0.00</span>
    </div>
</div>



{{-- All of java --}}
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
        var isBasedOnDays = @json($isBasedOnDays);
        
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
                        // availabilityEl.innerHTML = '<span class="badge bg-secondary">Select room</span>';
                    } 
                    else if (roomAvailability.isFullyBooked) {
                        availabilityEl.innerHTML = '<span class="fc-status-booked">Booked</span>';
                    } 
                    else {
                        availabilityEl.innerHTML = `
                            <span class="fc-capacity-badge 
                                ${roomAvailability.remaining < 3 ? 'fc-capacity-warning' : 'fc-capacity-success'}">
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
                }) : '';
                
                const formattedEnd = endDate ? new Date(endDate).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '';
                
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

{{-- Script for validation --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reserveBtn = document.getElementById('reserve-btn');
        const noRoomsAlert = document.querySelector('.alert.alert-warning');
        const startDateDisplay = document.getElementById('start-date-display');
        const endDateDisplay = document.getElementById('end-date-display');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        
        const roomSelect = document.getElementById('room_selection');
    
        const userType = "{{ auth()->user()->utype ?? 'USR' }}"; 
        
        reserveBtn.disabled = true;
        
        if (noRoomsAlert && noRoomsAlert.textContent.includes('No rooms with available capacity at the moment')) {
            reserveBtn.disabled = true;
            return;
        }

        const today = new Date();
        const minSelectableDate = new Date();
        minSelectableDate.setDate(today.getDate() + 3);
    
        let maxSelectableDate = null;
        if (userType === 'USR') {
            maxSelectableDate = new Date();
            maxSelectableDate.setMonth(today.getMonth() + 1); 
        }     
        const datedPriceScenario = @json($datedPriceWithQuantity || $datedPrice ? true : false);
        
        function resetDateSelections() {
            if (startDateDisplay) startDateDisplay.textContent = "";
            if (endDateDisplay) endDateDisplay.textContent = "";
            if (dateFromInput) dateFromInput.value = "";
            if (dateToInput) dateToInput.value = "";
            
            if (window.calendar) {
                window.selectedDates = [];
                window.startDate = null;
                window.endDate = null;
                window.calendar.refetchEvents();
                window.calendar.render();
            }
        }

        if (roomSelect) {
            roomSelect.addEventListener('change', function() {
                resetDateSelections();
        
                if (this.value === "") {
                    resetDateSelections();
                }
                validateFullScenario();
            });
        }
        
        if (datedPriceScenario) {
            const priceSelect = document.getElementById('price_id');
            const validateDatedPriceScenario = () => {
                reserveBtn.disabled = !(priceSelect && priceSelect.value);
            };
            if (priceSelect) priceSelect.addEventListener('change', validateDatedPriceScenario);
            validateDatedPriceScenario();
        } else {
            const priceSelect = document.getElementById('price_id');
            
            const validateFullScenario = () => {
                const roomValid = roomSelect ? roomSelect.value : true;
                const priceValid = priceSelect ? priceSelect.value : true;
                const datesValid = dateFromInput && dateToInput ? (dateFromInput.value && dateToInput.value) : true;
                reserveBtn.disabled = !(roomValid && priceValid && datesValid);
            };
            
            if (priceSelect) priceSelect.addEventListener('change', validateFullScenario);
            if (dateFromInput && dateToInput) {
                dateFromInput.addEventListener('change', validateFullScenario);
                dateToInput.addEventListener('change', validateFullScenario);
            }
            
            document.getElementById('confirm-dates')?.addEventListener('click', validateFullScenario);
            validateFullScenario();
            
            if (window.calendar) {
                const validRange = { start: minSelectableDate };
                if (maxSelectableDate) {
                    validRange.end = maxSelectableDate;
                }
                window.calendar.setOption('validRange', validRange);
            }
        }
    });
</script>