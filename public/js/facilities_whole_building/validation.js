// js/facilities_whole_building/validation.js
document.addEventListener('DOMContentLoaded', function() {
    initializeValidation();
});

function initializeValidation() {
    const reserveBtn = document.getElementById('reserve-btn');
    const clientTypeDropdown = document.getElementById('client_type');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const hasDayBasedPricing = document.querySelector('[data-has-day-based-pricing]').dataset.hasDayBasedPricing === 'true';
    
    // Initialize global function
    window.validateReserveButton = validateReserveButton;
    
    // Set initial state
    if (reserveBtn) {
        reserveBtn.disabled = true;
    }
    
    // Add event listeners
    if (clientTypeDropdown) {
        clientTypeDropdown.addEventListener('change', validateReserveButton);
    }
    
    if (!hasDayBasedPricing) {
        if (dateFromInput) {
            dateFromInput.addEventListener('change', validateReserveButton);
        }
        
        if (dateToInput) {
            dateToInput.addEventListener('change', validateReserveButton);
            
            // Set up observers for dynamic changes
            const observer = new MutationObserver(validateReserveButton);
            if (dateFromInput) {
                observer.observe(dateFromInput, { attributes: true, attributeFilter: ['value'] });
            }
            if (dateToInput) {
                observer.observe(dateToInput, { attributes: true, attributeFilter: ['value'] });
            }
            
            // Periodic check for changes
            let lastDateFrom = '';
            let lastDateTo = '';
            
            const checkDateChanges = () => {
                if ((dateFromInput && dateFromInput.value !== lastDateFrom) || 
                    (dateToInput && dateToInput.value !== lastDateTo)) {
                    lastDateFrom = dateFromInput ? dateFromInput.value : '';
                    lastDateTo = dateToInput ? dateToInput.value : '';
                    validateReserveButton();
                }
            };
            
            setInterval(checkDateChanges, 500);
        }
    }
    
    // Initial validation
    validateReserveButton();
    setTimeout(validateReserveButton, 1000);
}

function validateReserveButton() {
    const reserveBtn = document.getElementById('reserve-btn');
    const clientTypeDropdown = document.getElementById('client_type');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    const hasDayBasedPricing = document.querySelector('[data-has-day-based-pricing]').dataset.hasDayBasedPricing === 'true';
    
    if (!reserveBtn) return;
    
    if (hasDayBasedPricing) {
        reserveBtn.disabled = !(clientTypeDropdown && clientTypeDropdown.value);
    } else {
        const hasClientType = clientTypeDropdown && clientTypeDropdown.value;
        const hasDateFrom = dateFromInput && dateFromInput.value;
        const hasDateTo = dateToInput && dateToInput.value;
        
        reserveBtn.disabled = !(hasClientType && hasDateFrom && hasDateTo);
    }
}