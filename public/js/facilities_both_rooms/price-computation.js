document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', updateTotalPrice);
    });
    
    const priceSelect = document.getElementById('price_id');
    if (priceSelect) {
        priceSelect.addEventListener('change', updateTotalPrice);
    }
    
    const wholePriceSelect = document.getElementById('whole_price_id');
    if (wholePriceSelect) {
        wholePriceSelect.addEventListener('change', updateTotalPrice);
    }
    
    const sharedRoomSelect = document.getElementById('shared_selected_room');
    if (sharedRoomSelect) {
        sharedRoomSelect.addEventListener('change', updateTotalPrice);
    }
    
    const wholeRoomSelect = document.getElementById('selected_room');
    if (wholeRoomSelect) {
        wholeRoomSelect.addEventListener('change', updateTotalPrice);
    }
    
    document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    document.querySelectorAll('.addon-quantity-input').forEach(input => {
        input.addEventListener('input', updateTotalPrice);
    });
    
    document.querySelectorAll('.nights-input').forEach(input => {
        input.addEventListener('input', updateTotalPrice);
    });
});

function updateTotalPrice() {
    let totalPrice = 0;
    const bookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
    
    if (bookingType === 'shared') {
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                const priceId = input.name.match(/\[(\d+)\]/)[1];
                const priceValueInput = document.querySelector(`input[name="price_values[${priceId}]"]`);
                if (priceValueInput) {
                    const priceValue = parseFloat(priceValueInput.value) || 0;
                    if (!isNaN(priceValue)) {
                        let itemTotal = priceValue * quantity;
                        const dateFrom = document.getElementById('date_from');
                        const dateTo = document.getElementById('date_to');
                        if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                            const startDate = new Date(dateFrom.value);
                            const endDate = new Date(dateTo.value);
                            const diffTime = Math.abs(endDate - startDate);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            itemTotal = itemTotal * diffDays;
                        }
                        totalPrice += itemTotal;
                    }
                }
            }
        });
        
        const priceSelect = document.getElementById('price_id');
        if (priceSelect && priceSelect.value) {
            const selectedOption = priceSelect.options[priceSelect.selectedIndex];
            const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
            const selectedPriceValue = document.getElementById('selected_price_value');
            if (selectedPriceValue) {
                selectedPriceValue.value = selectedPrice;
            }
            
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                const startDate = new Date(dateFrom.value);
                const endDate = new Date(dateTo.value);
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalPrice += selectedPrice * diffDays;
            } else {
                totalPrice += selectedPrice;
            }
        }
        
        totalPrice += calculateAddonPrice('shared');
        
    } else if (bookingType === 'whole') {
        const priceSelect = document.getElementById('whole_price_id');
        if (priceSelect && priceSelect.value) {
            const selectedOption = priceSelect.options[priceSelect.selectedIndex];
            const selectedPrice = parseFloat(selectedOption.getAttribute('data-value')) || 0;
            
            const selectedWholePriceValue = document.getElementById('selected_whole_price_value');
            if (selectedWholePriceValue) {
                selectedWholePriceValue.value = selectedPrice;
            }
            
            const dateFrom = document.getElementById('whole_date_from');
            const dateTo = document.getElementById('whole_date_to');
            
            if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                const startDate = new Date(dateFrom.value);
                const endDate = new Date(dateTo.value);
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalPrice += selectedPrice * diffDays;
            } else {
                totalPrice += selectedPrice;
            }
        }
        
        totalPrice += calculateAddonPrice('whole');
    }
    
    const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    const computedTotalElement = document.getElementById('computed-total');
    if (computedTotalElement) {
        computedTotalElement.textContent = formattedTotal;
    }
    
    const totalPriceField = document.getElementById('total-price-field');
    if (totalPriceField) {
        totalPriceField.value = totalPrice.toFixed(2);
    }
    
    console.log('Booking Type:', bookingType);
    console.log('Total Price:', totalPrice);
    console.log('Formatted Total:', formattedTotal);
}

function calculateAddonPrice(section) {
    let addonTotal = 0;
    const isPriceBasedOnDays = window.isBasedOnDays || false;
    
    let dateFromField, dateToField;
    if (section === 'shared') {
        dateFromField = document.getElementById('date_from');
        dateToField = document.getElementById('date_to');
    } else {
        dateFromField = document.getElementById('whole_date_from');
        dateToField = document.getElementById('whole_date_to');
    }
    
    let daysDiff = 1;
    if (dateFromField && dateToField && dateFromField.value && dateToField.value) {
        const startDate = new Date(dateFromField.value);
        const endDate = new Date(dateToField.value);
        const diffTime = Math.abs(endDate - startDate);
        daysDiff = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    }
    
    document.querySelectorAll(`input[name^="${section}_addon_"]`).forEach(input => {
        const addonId = input.getAttribute('data-addon-id');
        if (!addonId) return;
        
        const addonValueInput = document.querySelector(`input[name="addon_values[${addonId}]"]`);
        const addonTypeInput = document.querySelector(`input[name="addon_types[${addonId}]"]`);
        const addonIsQuantityBasedInput = document.querySelector(`input[name="addon_is_quantity_based[${addonId}]"]`);
        
        if (!addonValueInput || !addonTypeInput) return;
        
        const basePrice = parseFloat(addonValueInput.value) || 0;
        const priceType = addonTypeInput.value;
        const isQuantityBased = addonIsQuantityBasedInput && addonIsQuantityBasedInput.value === '1';
        
        let isAddonSelected = false;
        let quantity = 0;
        let nights = 1;
        
        if (input.type === 'checkbox' && input.checked) {
            isAddonSelected = true;
        } else if (input.type === 'number' && input.classList.contains('addon-quantity-input')) {
            quantity = parseInt(input.value) || 0;
            isAddonSelected = quantity > 0;
        }
        
        const nightsInput = document.querySelector(`input[name="${section}_addon_nights[${addonId}]"]`);
        if (nightsInput) {
            nights = parseInt(nightsInput.value) || 1;
        }
        
        if (isAddonSelected) {
            let addonPrice = 0;
            
            if (priceType === 'per_item') {
                if (!isPriceBasedOnDays && !isQuantityBased) {
                    addonPrice = daysDiff * basePrice;
                } else if (!isPriceBasedOnDays && isQuantityBased) {
                    addonPrice = quantity * basePrice * daysDiff;
                } else if (isPriceBasedOnDays && !isQuantityBased) {
                    addonPrice = basePrice;
                } else if (isPriceBasedOnDays && isQuantityBased) {
                    addonPrice = basePrice * quantity;
                }
            } else if (priceType === 'flat_rate') {
                if (!isPriceBasedOnDays) {
                    addonPrice = daysDiff * basePrice;
                } else {
                    addonPrice = basePrice;
                }
            } else if (priceType === 'per_unit') {
                if (!isPriceBasedOnDays) {
                    addonPrice = daysDiff * basePrice;
                } else {
                    addonPrice = basePrice;
                }
            } else if (priceType === 'per_night') {
                if (!isPriceBasedOnDays) {
                    addonPrice = daysDiff * basePrice * nights;
                } else {
                    addonPrice = basePrice * nights;
                }
            }
            
            addonTotal += addonPrice;
        }
    });
    
    return addonTotal;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedPriceValue = document.getElementById('selected_price_value');
            const selectedWholePriceValue = document.getElementById('selected_whole_price_value');
            
            if (this.value === 'shared') {
                if (selectedWholePriceValue) {
                    selectedWholePriceValue.value = '';
                }
                const wholePriceSelect = document.getElementById('whole_price_id');
                if (wholePriceSelect) {
                    wholePriceSelect.value = '';
                }
            } else if (this.value === 'whole') {
                if (selectedPriceValue) {
                    selectedPriceValue.value = '';
                }
                const priceSelect = document.getElementById('price_id');
                if (priceSelect) {
                    priceSelect.value = '';
                }
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.value = '';
                });
                if (window.updateClientTypeDisplay) {
                    window.updateClientTypeDisplay();
                }
            }
            
            updateTotalPrice();
        });
    });
    
    updateTotalPrice();
});

window.updateTotalPrice = updateTotalPrice;