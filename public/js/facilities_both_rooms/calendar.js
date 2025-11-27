document.addEventListener('DOMContentLoaded', function() {
    var dateFromInput = document.getElementById('date_from');
    var dateToInput = document.getElementById('date_to');
    var wholeDateFromInput = document.getElementById('whole_date_from');
    var wholeDateToInput = document.getElementById('whole_date_to');
    var sharedDateFromInput = document.getElementById('date_from');
    var sharedDateToInput = document.getElementById('date_to');

    var today = new Date();
    var tomorrow = new Date(today);
    
    var userType = window.userType || 'USR';
    
    if (userType === 'ADM') {
        tomorrow.setDate(tomorrow.getDate() + 1);
    } else {
        tomorrow.setDate(tomorrow.getDate() + 7);
    }
    
    var tomorrowFormatted = formatDateToPH(tomorrow);
    
    var maxDate = null;
    var maxDateFormatted = null;
    
    if (userType === 'USR') {
        maxDate = new Date(today);
        maxDate.setMonth(maxDate.getMonth() + 3);
        maxDateFormatted = formatDateToPH(maxDate);
    } else if (userType === 'ADM') {
        maxDate = new Date(today);
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        maxDateFormatted = formatDateToPH(maxDate);
    }
    
    if (dateFromInput) dateFromInput.min = tomorrowFormatted;
    if (dateToInput) dateToInput.min = tomorrowFormatted;
    if (wholeDateFromInput) wholeDateFromInput.min = tomorrowFormatted;
    if (wholeDateToInput) wholeDateToInput.min = tomorrowFormatted;
    if (sharedDateFromInput) sharedDateFromInput.min = tomorrowFormatted;
    if (sharedDateToInput) sharedDateToInput.min = tomorrowFormatted;
    
    if (maxDateFormatted) {
        if (dateFromInput) dateFromInput.max = maxDateFormatted;
        if (dateToInput) dateToInput.max = maxDateFormatted;
        if (wholeDateFromInput) wholeDateFromInput.max = maxDateFormatted;
        if (wholeDateToInput) wholeDateToInput.max = maxDateFormatted;
        if (sharedDateFromInput) sharedDateFromInput.max = maxDateFormatted;
        if (sharedDateToInput) sharedDateToInput.max = maxDateFormatted;
    }

    var availabilities = window.availabilities || [];
    var facilityAttributes = window.facilityAttributes || [];
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
        
        if (window.updateTotalPrice) {
            window.updateTotalPrice();
        }
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
        
        if (window.updateTotalPrice) {
            window.updateTotalPrice();
        }
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
                if (maxDateFormatted) {
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
                        if (window.updateTotalPrice) {
                            window.updateTotalPrice();
                        }
                    }
                });
            });
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
            if (window.updateTotalPrice) {
                window.updateTotalPrice();
            }
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

    function formatDateToPH(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    window.initializeCalendar = initializeCalendar;
    window.getDatesInRange = getDatesInRange;
});