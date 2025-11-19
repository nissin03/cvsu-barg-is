document.addEventListener('DOMContentLoaded', function() {
    const hasDayBasedPricing = document.querySelector('[data-has-day-based-pricing]')?.dataset.hasDayBasedPricing === 'true';
    
    if (!hasDayBasedPricing) {
        initializeCalendar();
    }
});

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }
    
    const userType = calendarEl.dataset.userType;
    const userRole = calendarEl.dataset.userRole;
    const availabilities = JSON.parse(calendarEl.dataset.availabilities || '[]');
    const facilityCapacity = parseInt(calendarEl.dataset.facilityCapacity || '0');
    
    const today = new Date();
    const tomorrow = new Date(today);
    
    if (userType === 'ADM') {
        tomorrow.setDate(tomorrow.getDate() + 1);
    } else {
        tomorrow.setDate(tomorrow.getDate() + 7);
    }
    
    const tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    let maxDate = null;
    let maxDateFormatted = null;
    
    if (userType === 'USR') {
        maxDate = new Date(today);
        maxDate.setMonth(maxDate.getMonth() + 3);
        maxDateFormatted = maxDate.toISOString().split('T')[0];
    } else if (userType === 'ADM') {
        maxDate = new Date(today);
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        maxDateFormatted = maxDate.toISOString().split('T')[0];
    }
    
    window.calendarSelectedDates = [];
    window.calendarStartDate = null;
    window.calendarEndDate = null;
    
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
        validRange: { start: tomorrowFormatted },
        
        dateClick: function(info) {
            handleDateClick(info, userType, userRole, availabilities, facilityCapacity);
        },
        
        dayCellClassNames: function(info) {
            return getDayCellClassNames(info, userType, userRole, availabilities, facilityCapacity);
        },
        
        events: function(fetchInfo, successCallback, failureCallback) {
            const events = getCalendarEvents(availabilities);
            successCallback(events);
        }
    };

    if (userType === 'USR' && (userRole === 'student' || userRole === 'employee')) {
        calendarOptions.weekends = false;
    } else {
        calendarOptions.weekends = true;
    }

    if (maxDateFormatted) {
        calendarOptions.validRange.end = maxDateFormatted;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, calendarOptions);
    calendar.render();
    
    window.facilityCalendar = calendar;
    window.facilityCalendar.clickCount = 0;
    
    document.getElementById('calendarModal')?.addEventListener('shown.bs.modal', function () {
        calendar.updateSize();
    });
    
    document.getElementById('confirm-dates')?.addEventListener('click', function() {
        updateCalendarInputs();
        updateDateDisplay();
        if (typeof window.updateTotalPrice === 'function') {
            window.updateTotalPrice();
        }
        if (typeof window.validateReserveButton === 'function') {
            window.validateReserveButton();
        }
    });
    
    console.log('Calendar initialized successfully');
}

function handleDateClick(info, userType, userRole, availabilities, facilityCapacity) {
    const clickedDate = info.dateStr;
    const dayOfWeek = new Date(clickedDate).getDay();
    
    if (userType !== 'ADM' && userRole !== 'non-employee') {
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            Swal.fire({
                icon: 'error',
                title: 'Not Available',
                text: 'Weekend bookings are not currently available. Please select a date between Monday and Friday'
            });
            return;
        }
    }
    
    if (isDateFullyBooked(clickedDate, availabilities, facilityCapacity)) {
        Swal.fire({
            icon: 'error',
            title: 'Unavailable',
            text: 'This date is fully reserved and unavailable for booking.'
        });
        return;
    }
    
    window.facilityCalendar.clickCount++;
    
    if (window.facilityCalendar.clickCount % 2 === 1) {
        window.calendarStartDate = clickedDate;
        window.calendarSelectedDates = [clickedDate];
        window.calendarEndDate = null;
    } else {
        if (window.calendarStartDate) {
            const start = new Date(window.calendarStartDate);
            const end = new Date(clickedDate);
            
            if (end >= start) {
                const dateRange = getDatesInRange(window.calendarStartDate, clickedDate);
                
                if (userType !== 'ADM' && userRole !== 'non-employee') {
                    const hasWeekendDate = dateRange.some(date => {
                        const day = new Date(date).getDay();
                        return day === 0 || day === 6;
                    });
                    
                    if (hasWeekendDate) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Available',
                            text: 'Weekend bookings are not currently available. Please select a date between Monday and Friday'
                        });
                        window.facilityCalendar.clickCount--;
                        return;
                    }
                }
                
                const hasReservedDate = dateRange.some(date => isDateFullyBooked(date, availabilities, facilityCapacity));
                
                if (hasReservedDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unavailable',
                        text: 'One or more dates in your selected range are reserved.'
                    });
                    window.facilityCalendar.clickCount--;
                    return;
                }
                
                window.calendarEndDate = clickedDate;
                window.calendarSelectedDates = dateRange;
            } else {
                window.calendarStartDate = clickedDate;
                window.calendarSelectedDates = [clickedDate];
                window.calendarEndDate = null;
                window.facilityCalendar.clickCount = 1;
            }
        } else {
            window.calendarStartDate = clickedDate;
            window.calendarSelectedDates = [clickedDate];
            window.calendarEndDate = null;
            window.facilityCalendar.clickCount = 1;
        }
    }
    
    updateCalendarInputs();
    updateDateDisplay();
    window.facilityCalendar.render();
}

function getDayCellClassNames(info, userType, userRole, availabilities, facilityCapacity) {
    let classes = [];
    const dayOfWeek = info.date.getDay();
    const dateStr = info.dateStr;
    
    if (userType !== 'ADM' && userRole !== 'non-employee' && (dayOfWeek === 0 || dayOfWeek === 6)) {
        classes.push('disabled-date');
    }
    
    if (isDateFullyBooked(dateStr, availabilities, facilityCapacity)) {
        classes.push('fully-booked-date');
    }
    
    if (window.calendarSelectedDates && window.calendarSelectedDates.includes(dateStr) && 
        !isDateFullyBooked(dateStr, availabilities, facilityCapacity)) {
        if (dateStr === window.calendarStartDate) {
            classes.push('selected-start-date');
        } else if (dateStr === window.calendarEndDate) {
            classes.push('selected-end-date');
        } else {
            classes.push('selected-range-date');
        }
    }
    
    return classes;
}

function getCalendarEvents(availabilities) {
    const reservedDates = getReservedDates(availabilities);
    const events = reservedDates.map(date => {
        const availability = availabilities.find(avail => avail.date_from === date || avail.date_to === date);
        const title = availability && availability.remaining_capacity <= 0 ? 'Booked' : '';
        
        if (title === 'Booked') {
            return {
                id: `reserved-${date}`,
                title: 'Booked',
                start: date,
                allDay: true,
                backgroundColor: '#dc3545',
                borderColor: '#dc3545',
                textColor: 'white',
                classNames: ['fully-booked-event']
            };
        }

        return {
            id: `reserved-${date}`,
            title: '',
            start: date,
            allDay: true,
            backgroundColor: '#f8d7da',
            borderColor: '#f8d7da',
            textColor: 'black',
            classNames: ['available-event']
        };
    });

    return events;
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

function isDateFullyBooked(dateStr, availabilities, facilityCapacity) {
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

function getReservedDates(availabilities) {
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

function updateCalendarInputs() {
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput) {
        dateFromInput.value = window.calendarStartDate || '';
    }
    if (dateToInput) {
        dateToInput.value = window.calendarEndDate || '';
    }
}

function updateDateDisplay() {
    const startDisplay = document.getElementById('start-date-display');
    const endDisplay = document.getElementById('end-date-display');
    const modalStart = document.getElementById('modal-start-date');
    const modalEnd = document.getElementById('modal-end-date');
    
    if (window.calendarStartDate) {
        const formattedStart = new Date(window.calendarStartDate).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        if (startDisplay) startDisplay.textContent = formattedStart;
        if (modalStart) modalStart.textContent = formattedStart;
    } else {
        if (startDisplay) startDisplay.textContent = 'Click on calendar to select';
        if (modalStart) modalStart.textContent = 'Not selected';
    }
    
    if (window.calendarEndDate) {
        const formattedEnd = new Date(window.calendarEndDate).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        if (endDisplay) endDisplay.textContent = formattedEnd;
        if (modalEnd) modalEnd.textContent = formattedEnd;
    } else {
        if (endDisplay) endDisplay.textContent = 'Click on calendar to select';
        if (modalEnd) modalEnd.textContent = 'Not selected';
    }
}
