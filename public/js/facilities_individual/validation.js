document.addEventListener('DOMContentLoaded', function() {
    const reserveBtn = document.getElementById('reserve-btn');
    const noRoomsAlert = document.querySelector('.alert.alert-warning');
    const startDateDisplay = document.getElementById('start-date-display');
    const endDateDisplay = document.getElementById('end-date-display');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const roomSelect = document.getElementById('room_selection');
    
    const userType = (typeof window.userType !== 'undefined') ? window.userType : 'USR';
    
    reserveBtn.disabled = true;
    
    if (noRoomsAlert && noRoomsAlert.textContent.includes('No rooms with available capacity at the moment')) {
        reserveBtn.disabled = true;
        return;
    }

    const today = new Date();
    const minSelectableDate = new Date();
    minSelectableDate.setDate(today.getDate() + 7);

    let maxSelectableDate = null;
    if (userType === 'USR') {
        maxSelectableDate = new Date(); 
        maxSelectableDate.setMonth(today.getMonth() + 3); 
    }     
    const datedPriceScenario = (typeof window.datedPriceScenario !== 'undefined') ? window.datedPriceScenario : false;
    const isBasedOnDays = (typeof window.isBasedOnDays !== 'undefined') ? window.isBasedOnDays : false;
    
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            if (window.resetDateSelections) {
                window.resetDateSelections();
            }
    
            if (this.value === "") {
                if (window.resetDateSelections) {
                    window.resetDateSelections();
                }
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
            let roomValid = true;
            let priceValid = true;
            let datesValid = true;
            
            if (isBasedOnDays === false) {
                roomValid = roomSelect ? (roomSelect.value !== "" && roomSelect.value !== null) : true;
                datesValid = dateFromInput && dateToInput ? (dateFromInput.value !== "" && dateToInput.value !== "") : true;
                priceValid = priceSelect ? (priceSelect.value !== "" && priceSelect.value !== null) : true;
                
                reserveBtn.disabled = !(roomValid && datesValid && priceValid);
            } else {
                roomValid = roomSelect ? roomSelect.value : true;
                priceValid = priceSelect ? priceSelect.value : true;
                datesValid = dateFromInput && dateToInput ? (dateFromInput.value && dateToInput.value) : true;
                
                reserveBtn.disabled = !(roomValid && priceValid && datesValid);
            }
        };
        
        window.validateFullScenario = validateFullScenario;
        
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