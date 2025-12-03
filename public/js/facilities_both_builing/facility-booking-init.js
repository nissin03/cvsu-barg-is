document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.facilityData === 'undefined') {
        console.error('Facility data not found. Please ensure facility data is loaded before this script.');
        return;
    }

    const { facility, availabilities, facilityAttributes, wholeAttr, authUserType } = window.facilityData;

    window.authUserType = authUserType;
    
    window.calendarValidation = new CalendarValidation(facility, availabilities, facilityAttributes, wholeAttr);
    window.clientTypeHandler = new ClientTypeHandler(facility, wholeAttr?.whole_capacity || 0);
    window.priceComputation = new PriceComputation();
    window.formValidation = new FormValidation(facility, wholeAttr?.whole_capacity || 0);

    window.priceComputation.makeGlobalFunctions();

    const wholeTimeStartInput = document.getElementById('whole_time_start');
    const wholeTimeEndInput = document.getElementById('whole_time_end');

    function calculateWholeEndTime() {
        if (!wholeTimeStartInput || !wholeTimeEndInput) return;
        const st = wholeTimeStartInput.value;
        if (!st) return;
        const parts = st.split(':').map(Number);
        const h = (parts[0] + 8) % 24;
        const m = parts[1];
        wholeTimeEndInput.value = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
    }

    if (wholeTimeStartInput) {
        wholeTimeStartInput.value = '07:00';
        calculateWholeEndTime();
        
        wholeTimeStartInput.addEventListener('change', function() {
            calculateWholeEndTime();
            window.priceComputation.updateWholeTotalPrice();
        });
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
            const wholeDateFromInput = document.getElementById('whole_date_from');
            const wholeDateToInput = document.getElementById('whole_date_to');
            const wholeClientTypeDropdown = document.getElementById('whole_client_type');
            
            if (wholeDateFromInput) wholeDateFromInput.setAttribute('required', 'required');
            if (wholeDateToInput) wholeDateToInput.setAttribute('required', 'required');
            if (wholeClientTypeDropdown) wholeClientTypeDropdown.setAttribute('required', 'required');
        }
    }

    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'shared') {
                document.getElementById('shared-section').style.display = 'block';
                document.getElementById('whole-section').style.display = 'none';
                updateRequiredFields('shared');
                window.calendarValidation.initializeCalendar('shared');
            } else {
                document.getElementById('shared-section').style.display = 'none';
                document.getElementById('whole-section').style.display = 'block';
                updateRequiredFields('whole');
                window.calendarValidation.initializeCalendar('whole');
            }
            window.priceComputation.updateTotalPrice();
        });
    });

    window.calendarValidation.setupConfirmButtons();

    const defaultBookingType = document.querySelector('input[name="booking_type"]:checked');
    if (defaultBookingType) {
        const activeSection = defaultBookingType.value;
        updateRequiredFields(activeSection);
        window.calendarValidation.initializeCalendar(activeSection);
    }

    window.clientTypeHandler.initializeDisplay();

    window.validateQuantityInput = function(input) {
        return window.clientTypeHandler.validateQuantityInput(input);
    };

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
        });
    }
});