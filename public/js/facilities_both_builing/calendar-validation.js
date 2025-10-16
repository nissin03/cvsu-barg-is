class CalendarValidation {
    constructor(facility, availabilities, facilityAttributes, wholeAttr) {
        this.facility = facility;
        this.availabilities = availabilities || [];
        this.facilityAttributes = facilityAttributes || [];
        this.facilityCapacity = wholeAttr?.whole_capacity || 0;
        this.wholeCalendarInitialized = false;
        this.sharedCalendarInitialized = false;
        this.hasDayBasedPricing = facility.prices.some(price => price.is_based_on_days);
        
        this.today = new Date();
        this.tomorrow = new Date(this.today);
        this.tomorrow.setDate(this.tomorrow.getDate() + 7);
        this.tomorrowFormatted = this.tomorrow.toISOString().split('T')[0];
        
        const userType = window.authUserType;
        this.maxDate = null;
        this.maxDateFormatted = null;
        
        if (userType === 'USR') {
            this.maxDate = new Date(this.today);
            this.maxDate.setMonth(this.maxDate.getMonth() + 3);
            this.maxDateFormatted = this.maxDate.toISOString().split('T')[0];
        }
        
        this.initializeDateInputs();
    }

    initializeDateInputs() {
        const dateInputs = [
            'date_from', 'date_to', 'whole_date_from', 'whole_date_to'
        ];
        
        dateInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.min = this.tomorrowFormatted;
                if (this.maxDateFormatted) input.max = this.maxDateFormatted;
            }
        });
    }

    getAvailabilityForDate(dateStr) {
        const checkDate = new Date(dateStr);
        const matchingAvailabilities = this.availabilities.filter(avail => {
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

    getMinCapacityForDateRange(startDate, endDate) {
        const dates = this.getDatesInRange(startDate, endDate);
        let minCapacity = this.facilityCapacity;
        
        dates.forEach(dateStr => {
            const availability = this.getAvailabilityForDate(dateStr);
            if (availability) {
                if (availability.remaining_capacity < minCapacity) {
                    minCapacity = availability.remaining_capacity;
                }
            }
        });
        
        return minCapacity;
    }

    isDateFullyBooked(dateStr) {
        const availability = this.getAvailabilityForDate(dateStr);
        if (!availability) return false;
        return availability.remaining_capacity <= 0;
    }

    isDateOccupied(dateStr) {
        const availability = this.getAvailabilityForDate(dateStr);
        if (!availability) return false;
        const attribute = this.facilityAttributes.find(attr => attr.id === availability.facility_attribute_id);
        if (!attribute) return false;
        return attribute.whole_capacity !== availability.remaining_capacity;
    }

    formatDateForDisplay(dateStr) {
        if (!dateStr) return 'Not selected';
        const date = new Date(dateStr + 'T00:00:00+08:00');
        return date.toLocaleDateString('en-PH', { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    getDatesInRange(start, end) {
        const dates = [];
        const startDate = new Date(start);
        const endDate = new Date(end);
        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            dates.push(d.toISOString().split('T')[0]);
        }
        return dates;
    }

    updateDateDisplay(startDisplay, endDisplay, startDate, endDate) {
        if (startDisplay) {
            startDisplay.textContent = this.formatDateForDisplay(startDate);
        }
        if (endDisplay) {
            endDisplay.textContent = this.formatDateForDisplay(endDate);
        }
    }

    initializeCalendar(section) {
        const isWhole = section === 'whole';
        const calendarEl = document.getElementById(isWhole ? 'whole-calendar' : 'shared-calendar');
        const modalId = isWhole ? 'wholeCalendarModal' : 'sharedCalendarModal';
        const modalEl = document.getElementById(modalId);
        
        if (!modalEl || !calendarEl) return;
        if ((isWhole && this.wholeCalendarInitialized) || (!isWhole && this.sharedCalendarInitialized)) return;
        
        modalEl.addEventListener('shown.bs.modal', () => this.initCalendarOnShow(section));
    }

    initCalendarOnShow(section) {
        const isWhole = section === 'whole';
        const calendarEl = document.getElementById(isWhole ? 'whole-calendar' : 'shared-calendar');
        
        if ((isWhole && this.wholeCalendarInitialized) || (!isWhole && this.sharedCalendarInitialized)) {
            const calendar = calendarEl._fullCalendar;
            if (calendar) {
                calendar.render();
                return;
            }
        }

        const dateFromInput = document.getElementById(isWhole ? 'whole_date_from' : 'date_from');
        const dateToInput = document.getElementById(isWhole ? 'whole_date_to' : 'date_to');
        const startDisplay = document.getElementById(isWhole ? 'whole-start-date-display' : 'shared-start-date-display');
        const endDisplay = document.getElementById(isWhole ? 'whole-end-date-display' : 'shared-end-date-display');
        const modalStartDisplay = document.getElementById(isWhole ? 'whole-modal-start-date' : 'shared-modal-start-date');
        const modalEndDisplay = document.getElementById(isWhole ? 'whole-modal-end-date' : 'shared-modal-end-date');
        const confirmButton = document.getElementById(isWhole ? 'whole-confirm-dates' : 'shared-confirm-dates');
        
        if (!this.hasDayBasedPricing && calendarEl && !calendarEl._fullCalendar) {
            const calendarState = {
                selectedDates: [],
                startDate: null,
                endDate: null,
                clickCount: 0
            };
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'Asia/Manila',
                locale: 'en',
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
                validRange: { 
                    start: this.tomorrowFormatted,
                    end: this.maxDateFormatted || undefined
                },
                moreLinkClick: function(info) { return false; },
                dateClick: (info) => this.handleDateClick(info, calendar, isWhole, calendarState, dateFromInput, dateToInput, modalStartDisplay, modalEndDisplay, confirmButton),
                dayCellClassNames: (info) => this.getDayCellClasses(info, calendarState),
                dayCellContent: (args) => this.getDayCellContent(args, isWhole),
                events: (fetchInfo, successCallback, failureCallback) => this.getCalendarEvents(successCallback)
            });
            
            calendar.render();
            calendarEl._fullCalendar = calendar;
            calendarEl._calendarState = calendarState;
            if (isWhole) this.wholeCalendarInitialized = true;
            else this.sharedCalendarInitialized = true;
        }
    }

    handleDateClick(info, calendar, isWhole, calendarState, dateFromInput, dateToInput, modalStartDisplay, modalEndDisplay, confirmButton) {
        const clickedDate = info.dateStr;
        
        if (this.isDateFullyBooked(clickedDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Not Available',
                text: 'This date is fully reserved and unavailable for booking.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }
        
        if (isWhole && this.isDateOccupied(clickedDate)) {
            Swal.fire({
                icon: 'warning',
                title: 'Partially Occupied',
                text: 'This date is partially occupied. Please select dates that are completely available.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }
        
        calendarState.clickCount++;
        
        if (calendarState.clickCount % 2 === 1) {
            calendarState.startDate = clickedDate;
            calendarState.selectedDates = [clickedDate];
            calendarState.endDate = null;
            
            if (modalStartDisplay) modalStartDisplay.textContent = this.formatDateForDisplay(calendarState.startDate);
            if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
        } else {
            if (calendarState.startDate) {
                const start = new Date(calendarState.startDate);
                const end = new Date(clickedDate);
                
                if (end >= start) {
                    const dateRange = this.getDatesInRange(calendarState.startDate, clickedDate);
                    const hasReservedDate = dateRange.some(date => this.isDateFullyBooked(date));
                    const hasOccupiedDate = isWhole ? dateRange.some(date => this.isDateOccupied(date)) : false;
                    
                    if (hasReservedDate) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Reserved Dates',
                            text: 'One or more dates in your selected range are fully reserved.',
                            confirmButtonColor: '#3085d6',
                        });
                        calendarState.clickCount--;
                        return;
                    }
                    
                    if (hasOccupiedDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Occupied Dates',
                            text: 'One or more dates in your selected range are partially occupied. Please select a different range.',
                            confirmButtonColor: '#3085d6',
                        });
                        calendarState.clickCount--;
                        return;
                    }
                    
                    calendarState.endDate = clickedDate;
                    calendarState.selectedDates = dateRange;
                    
                    if (modalEndDisplay) modalEndDisplay.textContent = this.formatDateForDisplay(calendarState.endDate);
                } else {
                    calendarState.startDate = clickedDate;
                    calendarState.selectedDates = [clickedDate];
                    calendarState.endDate = null;
                    calendarState.clickCount = 1;
                    
                    if (modalStartDisplay) modalStartDisplay.textContent = this.formatDateForDisplay(calendarState.startDate);
                    if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
                }
            } else {
                calendarState.startDate = clickedDate;
                calendarState.selectedDates = [clickedDate];
                calendarState.endDate = null;
                calendarState.clickCount = 1;
                
                if (modalStartDisplay) modalStartDisplay.textContent = this.formatDateForDisplay(calendarState.startDate);
                if (modalEndDisplay) modalEndDisplay.textContent = 'Not selected';
            }
        }
        
        if (dateFromInput) dateFromInput.value = calendarState.startDate || '';
        if (dateToInput) dateToInput.value = calendarState.endDate || '';
        
        if (confirmButton) {
            confirmButton.disabled = !(calendarState.startDate && calendarState.endDate);
        }
        
        if (isWhole && window.priceComputation) {
            window.priceComputation.updateWholeTotalPrice();
        } else if (window.priceComputation) {
            window.priceComputation.updateTotalPrice();
        }
        
        calendar.render();
    }

    getDayCellClasses(info, calendarState) {
        let classes = [];
        if (this.isDateFullyBooked(info.dateStr)) classes.push('fully-booked-date');
        if (this.isDateOccupied(info.dateStr)) classes.push('occupied-date');
        if (calendarState.selectedDates.includes(info.dateStr) && !this.isDateFullyBooked(info.dateStr) && !this.isDateOccupied(info.dateStr)) {
            if (info.dateStr === calendarState.startDate) classes.push('selected-start-date');
            else if (info.dateStr === calendarState.endDate) classes.push('selected-end-date');
            else classes.push('selected-range-date');
        }
        return classes;
    }

    getDayCellContent(args, isWhole) {
        const dateStr = args.date.toISOString().split('T')[0];
        const dayNumberEl = document.createElement('div');
        dayNumberEl.className = 'fc-daygrid-day-number';
        dayNumberEl.textContent = args.dayNumberText;
        
        const availability = this.getAvailabilityForDate(dateStr);
        const attribute = availability ? this.facilityAttributes.find(attr => attr.id === availability.facility_attribute_id) : null;
        
        if (!isWhole) {
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
                capacityEl.textContent = `${this.facilityCapacity} left`;
                return { domNodes: [dayNumberEl, capacityEl] };
            }
        } else {
            if (availability) {
                if (availability.remaining_capacity <= 0) {
                    const statusEl = document.createElement('div');
                    statusEl.className = 'fc-day-status fc-status-booked';
                    statusEl.textContent = 'Booked';
                    return { domNodes: [dayNumberEl, statusEl] };
                } else if (attribute && availability.remaining_capacity !== attribute.whole_capacity) {
                    const statusEl = document.createElement('div');
                    statusEl.className = 'fc-day-status fc-status-occupied';
                    statusEl.textContent = 'Occupied';
                    return { domNodes: [dayNumberEl, statusEl] };
                } else {
                    const capacityEl = document.createElement('div');
                    capacityEl.className = 'fc-day-capacity fc-capacity-success';
                    capacityEl.textContent = `${attribute ? attribute.whole_capacity : this.facilityCapacity} left`;
                    return { domNodes: [dayNumberEl, capacityEl] };
                }
            } else {
                const capacityEl = document.createElement('div');
                capacityEl.className = 'fc-day-capacity fc-capacity-success';
                capacityEl.textContent = `${this.facilityCapacity} left`;
                return { domNodes: [dayNumberEl, capacityEl] };
            }
        }
    }

    getCalendarEvents(successCallback) {
        const events = this.availabilities.map(avail => ({
            id: `avail-${avail.id}`,
            start: avail.date_from,
            end: avail.date_to ? new Date(new Date(avail.date_to).setDate(new Date(avail.date_to).getDate() + 1)) : null,
            display: 'background',
            backgroundColor: avail.remaining_capacity <= 0 ? 'rgba(220, 53, 69, 0.2)' : (avail.remaining_capacity < avail.whole_capacity ? 'rgba(253, 126, 20, 0.2)' : 'transparent'),
            extendedProps: {
                remaining_capacity: avail.remaining_capacity
            }
        }));
        successCallback(events);
    }

    updateModalCapacityDisplay() {
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        const modalCapacityElement = document.querySelector('#priceQuantityModal .capacity-value');
        
        if (dateFrom && dateTo && dateFrom.value && dateTo.value && modalCapacityElement) {
            const minCapacity = this.getMinCapacityForDateRange(dateFrom.value, dateTo.value);
            modalCapacityElement.textContent = minCapacity;
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.max = minCapacity;
            });
        } else if (modalCapacityElement) {
            modalCapacityElement.textContent = this.facilityCapacity;
            
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.max = this.facilityCapacity;
            });
        }
    }

    setupConfirmButtons() {
        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', () => {
                const startDate = document.getElementById('date_from').value;
                const endDate = document.getElementById('date_to').value;
                
                const startDisplay = document.getElementById('shared-start-date-display');
                const endDisplay = document.getElementById('shared-end-date-display');
                
                if (startDisplay) startDisplay.textContent = this.formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = this.formatDateForDisplay(endDate);
                
                this.updateModalCapacityDisplay();
            });
        }

        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', () => {
                const startDate = document.getElementById('whole_date_from').value;
                const endDate = document.getElementById('whole_date_to').value;
                
                const startDisplay = document.getElementById('whole-start-date-display');
                const endDisplay = document.getElementById('whole-end-date-display');
                
                if (startDisplay) startDisplay.textContent = this.formatDateForDisplay(startDate);
                if (endDisplay) endDisplay.textContent = this.formatDateForDisplay(endDate);
            });
        }
    }
}