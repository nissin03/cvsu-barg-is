document.addEventListener('DOMContentLoaded', function() {
    var dateFromInput = document.getElementById('date_from');
    var dateToInput = document.getElementById('date_to');
    var startDateDisplay = document.getElementById('start-date-display');
    var endDateDisplay = document.getElementById('end-date-display');
    var modalStartDate = document.getElementById('modal-start-date');
    var modalEndDate = document.getElementById('modal-end-date');
    var clearDatesBtn = document.getElementById('clear-dates');
    
    var availabilities = window.availabilities || [];
    var facilityAttributes = window.facilityAttributes || [];
    var isBasedOnDays = window.isBasedOnDays || false;
    
    let selectedDates = [];
    let startDate = null;
    let endDate = null;
    
    window.selectedDates = selectedDates;
    window.startDate = startDate;
    window.endDate = endDate;

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
        
        const confirmDatesBtn = document.getElementById('confirm-dates');
        if (confirmDatesBtn) {
            confirmDatesBtn.disabled = !startDate;
        }
    }
    
    function updateInputs() {
        if (dateFromInput) dateFromInput.value = startDate || '';
        if (dateToInput) dateToInput.value = endDate || '';
    }
    
    function highlightDates() {
        if (window.calendar) {
            window.calendar.refetchEvents();
            window.calendar.render();
        }
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
                
                window.startDate = startDate;
                window.endDate = endDate;
                window.selectedDates = selectedDates;
                
                updateInputs();
                updateDateDisplay();
                if (window.updateTotalPrice) {
                    window.updateTotalPrice();
                }
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
    }

    window.resetDateSelections = function() {
        startDate = null;
        endDate = null;
        selectedDates = [];
        window.startDate = null;
        window.endDate = null;
        window.selectedDates = [];
        
        if (startDateDisplay) startDateDisplay.textContent = "";
        if (endDateDisplay) endDateDisplay.textContent = "";
        if (dateFromInput) dateFromInput.value = "";
        if (dateToInput) dateToInput.value = "";
        
        if (window.calendar) {
            window.calendar.refetchEvents();
            window.calendar.render();
        }
    };
});