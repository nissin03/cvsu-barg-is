// Client types management and display functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize client type display on page load
    updateClientTypeDisplay();

    // Add event listeners to quantity inputs
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            if (window.validateQuantitiesAgainstCapacity) {
                window.validateQuantitiesAgainstCapacity();
            }
            if (window.checkFormValidity) {
                window.checkFormValidity();
            }
            updateClientTypeDisplay();
        });
    });
});

// Update client type display function
function updateClientTypeDisplay() {
    const container = document.getElementById('selected-client-types');
    if (!container) return;
    
    container.innerHTML = '';
    let hasSelection = false;
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        const quantity = parseInt(input.value) || 0;
        if (quantity > 0) {
            hasSelection = true;
            const priceId = input.name.match(/\[(\d+)\]/)[1];
            const priceName = document.querySelector(`input[name="price_names[${priceId}]"]`).value;
            const priceValue = parseFloat(document.querySelector(`input[name="price_values[${priceId}]"]`).value);
            const total = (quantity * priceValue).toFixed(2);
            
            const item = document.createElement('div');
            item.className = 'client-type-item';
            item.innerHTML = `
                <strong class="fw-bold">${priceName}</strong>
                <span>Price:₱${total}</span><br>
                <span>Qty: ${quantity}</span>
            `;
            container.appendChild(item);
        }
    });
    
    if (hasSelection) {
        container.style.display = 'flex'; 
    } else {
        container.style.display = 'none';
    }
    
    if (window.updateTotalPrice) {
        window.updateTotalPrice();
    }
}

// Get minimum remaining capacity for selected dates
function getMinRemainingCapacity(selectedDates) {
    let minCapacity = Infinity;
    const availabilities = window.availabilities || [];
    const selectedRoomId = document.getElementById('shared_selected_room')?.value;
    
    selectedDates.forEach(date => {
        const availability = availabilities.find(avail => 
            avail.facility_attribute_id == selectedRoomId &&
            new Date(date) >= new Date(avail.date_from) &&
            new Date(date) <= new Date(avail.date_to)
        );
        
        if (availability) {
            minCapacity = Math.min(minCapacity, availability.remaining_capacity);
        } else {
            const roomSelect = document.getElementById('shared_selected_room');
            const roomCapacity = roomSelect ? 
                parseInt(roomSelect.options[roomSelect.selectedIndex]?.getAttribute('data-capacity')) : 0;
            minCapacity = Math.min(minCapacity, roomCapacity);
        }
    });
    
    return minCapacity === Infinity ? 0 : minCapacity;
}

// Validate quantities against room capacity
function validateQuantitiesAgainstCapacity() {
    const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
    
    if (bookingType === 'shared') {
        let maxCapacity;
        const dateFrom = document.getElementById('date_from')?.value;
        const dateTo = document.getElementById('date_to')?.value;
        
        if (dateFrom && dateTo) {
            const selectedDates = window.getDatesInRange ? window.getDatesInRange(dateFrom, dateTo) : [];
            maxCapacity = getMinRemainingCapacity(selectedDates);
        } else {
            const roomSelect = document.getElementById('shared_selected_room');
            const assignedRoom = document.querySelector('.capacity-card');
            
            if (roomSelect && roomSelect.value) {
                maxCapacity = parseInt(roomSelect.options[roomSelect.selectedIndex].getAttribute('data-capacity')) || 0;
            } else if (assignedRoom) {
                const capacityText = assignedRoom.querySelector('.capacity-value').textContent;
                maxCapacity = parseInt(capacityText) || 0;
            }
        }
        
        const capacityValueElement = document.querySelector('.alert-info .capacity-value');
        if (capacityValueElement) {
            capacityValueElement.textContent = maxCapacity;
        }
        
        let totalQuantity = 0;
        document.querySelectorAll('.quantity-input').forEach(input => {
            totalQuantity += parseInt(input.value) || 0;
        });
        
        if (totalQuantity > maxCapacity) {
            Swal.fire({
                icon: 'error',
                title: 'Capacity Exceeded',
                html: `The total number of guests (${totalQuantity}) exceeds the maximum capacity of ${maxCapacity} for the selected dates.<br><br>Please adjust your selections.`,
                confirmButtonColor: '#3085d6',
            });
            
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.value = '';
            });
            
            updateClientTypeDisplay();
            if (window.updateTotalPrice) {
                window.updateTotalPrice();
            }
            return false;
        }
    }
    return true;
}

// Export functions to window for global access
window.updateClientTypeDisplay = updateClientTypeDisplay;
window.validateQuantitiesAgainstCapacity = validateQuantitiesAgainstCapacity;
window.getMinRemainingCapacity = getMinRemainingCapacity;