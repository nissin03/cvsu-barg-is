// Initialization file for facility booking system
// This file should be loaded first and sets up global data and configurations

// Initialize global facility data object
window.facilityData = {
    userType: 'USR', // Will be populated from PHP
    availabilities: [], // Will be populated from PHP
    facilityAttributes: [], // Will be populated from PHP
    hasQuantityPrices: false, // Will be populated from PHP
    hasNonQuantityPrices: false, // Will be populated from PHP
    isBasedOnDays: false // Will be populated from PHP
};

// Function to initialize facility data from PHP variables
function initializeFacilityData(phpData) {
    window.facilityData = {
        userType: phpData.userType || 'USR',
        availabilities: phpData.availabilities || [],
        facilityAttributes: phpData.facilityAttributes || [],
        hasQuantityPrices: phpData.hasQuantityPrices || false,
        hasNonQuantityPrices: phpData.hasNonQuantityPrices || false,
        isBasedOnDays: phpData.isBasedOnDays || false,
        facility: phpData.facility || null
    };
}

// Check if all required scripts are loaded
function checkScriptDependencies() {
    const requiredGlobals = [
        'FullCalendar',
        'Swal'
    ];
    
    const missing = requiredGlobals.filter(global => typeof window[global] === 'undefined');
    
    if (missing.length > 0) {
        console.warn('Missing required dependencies:', missing);
        return false;
    }
    
    return true;
}

// Initialize the booking system
function initializeBookingSystem() {
    // Check dependencies
    if (!checkScriptDependencies()) {
        console.error('Cannot initialize booking system - missing dependencies');
        return;
    }
    
    // Log initialization
    console.log('Initializing facility booking system...');
    
    // Dispatch custom event when initialization is complete
    document.dispatchEvent(new CustomEvent('facilityBookingReady', {
        detail: {
            facilityData: window.facilityData
        }
    }));
    
    console.log('Facility booking system initialized successfully');
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure all scripts are loaded
    setTimeout(initializeBookingSystem, 100);
});

// Export initialization function for manual use
window.initializeFacilityData = initializeFacilityData;
window.initializeBookingSystem = initializeBookingSystem;