// js/facilities_whole_building/clientType.js
document.addEventListener('DOMContentLoaded', function() {
    initializeClientType();
});

function initializeClientType() {
    const clientTypeDropdown = document.getElementById('client_type');
    
    if (clientTypeDropdown) {
        clientTypeDropdown.addEventListener('change', function() {
            const selectedPrice = this.value;  
            document.getElementById('selected_price').value = selectedPrice;  
            window.updateTotalPrice();  
            window.validateReserveButton();
        });
    }
}