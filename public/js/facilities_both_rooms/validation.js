// Form validation functionality
document.addEventListener('DOMContentLoaded', function() {
    const reserveBtn = document.getElementById('reserve-btn');
    if (reserveBtn) {
        reserveBtn.disabled = true;
    }

    function checkFormValidity() {
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        let isValid = true;

        if (bookingType === 'shared') {
            const hasQuantityPrices = window.hasQuantityPrices || false;
            const hasNonQuantityPrices = window.hasNonQuantityPrices || false;
            const isBasedOnDays = window.isBasedOnDays || false;

            if (isBasedOnDays) {
                const dateFrom = document.getElementById('date_from')?.value;
                const dateTo = document.getElementById('date_to')?.value;
                if (!dateFrom || !dateTo) isValid = false;
            }

            const roomSelect = document.getElementById('shared_selected_room');
            if (roomSelect && roomSelect.value === "") isValid = false;

            if (hasQuantityPrices) {
                let hasQuantity = false;
                document.querySelectorAll('.quantity-input').forEach(input => {
                    if (parseInt(input.value) > 0) hasQuantity = true;
                });
                if (!hasQuantity) isValid = false;
            }

            if (hasNonQuantityPrices) {
                const priceSelect = document.getElementById('price_id');
                if (!priceSelect || priceSelect.value === "") isValid = false;
            }
        } 
        else if (bookingType === 'whole') {
            const dateFrom = document.getElementById('whole_date_from')?.value;
            const dateTo = document.getElementById('whole_date_to')?.value;
            const roomSelect = document.getElementById('selected_room');
            const priceSelect = document.getElementById('whole_price_id');

            if (!dateFrom || !dateTo) isValid = false;
            if (!roomSelect || roomSelect.value === "") isValid = false;
            if (!priceSelect || priceSelect.value === "") isValid = false;
        }

        if (isValid) {
            isValid = validateQuantitiesAgainstCapacity();
        }

        if (reserveBtn) {
            reserveBtn.disabled = !isValid;
        }
    }

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

    function validateQuantitiesAgainstCapacity() {
        const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        
        if (bookingType === 'shared') {
            let maxCapacity;
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            
            if (dateFrom && dateTo) {
                const selectedDates = getDatesInRange(dateFrom, dateTo);
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
                
                if (window.updateClientTypeDisplay) {
                    window.updateClientTypeDisplay();
                }
                if (window.updateTotalPrice) {
                    window.updateTotalPrice();
                }
                return false;
            }
        }
        return true;
    }

    function getDatesInRange(startDate, endDate) {
        const dates = [];
        const currentDate = new Date(startDate);
        const end = new Date(endDate);
        
        while (currentDate <= end) {
            dates.push(new Date(currentDate).toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        return dates;
    }

    // Add event listeners for validation
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        });
    });
    
    const sharedRoomSelect = document.getElementById('shared_selected_room');
    if (sharedRoomSelect) {
        sharedRoomSelect.addEventListener('change', function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        });
    }
    
    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            validateQuantitiesAgainstCapacity();
            checkFormValidity();
        });
    });
    
    const confirmDatesBtn = document.getElementById('confirm-dates');
    if (confirmDatesBtn) {
        confirmDatesBtn.addEventListener('click', function() {
            setTimeout(function() {
                validateQuantitiesAgainstCapacity();
                checkFormValidity();
            }, 100);
        });
    }

    const priceSelect = document.getElementById('price_id');
    if (priceSelect) {
        priceSelect.addEventListener('change', checkFormValidity);
    }

    const wholePriceSelect = document.getElementById('whole_price_id');
    if (wholePriceSelect) {
        wholePriceSelect.addEventListener('change', checkFormValidity);
    }

    const wholeRoomSelect = document.getElementById('selected_room');
    if (wholeRoomSelect) {
        wholeRoomSelect.addEventListener('change', checkFormValidity);
    }

    const wholeConfirmDatesBtn = document.getElementById('whole-confirm-dates');
    if (wholeConfirmDatesBtn) {
        wholeConfirmDatesBtn.addEventListener('click', function() {
            setTimeout(checkFormValidity, 100);
        });
    }

    // Form submission validation (if needed)
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const activeBookingType = document.querySelector('input[name="booking_type"]:checked');
            if (activeBookingType && !checkFormValidity()) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Initial validation check
    checkFormValidity();

    // Export functions to window for global access
    window.checkFormValidity = checkFormValidity;
    window.validateQuantitiesAgainstCapacity = validateQuantitiesAgainstCapacity;
    window.getMinRemainingCapacity = getMinRemainingCapacity;
    window.getDatesInRange = getDatesInRange;
});