document.addEventListener('DOMContentLoaded', function() {
    window.updateTotalPrice = updateTotalPrice;
    
    document.addEventListener('addonsUpdated', function() {
        updateTotalPrice();
    });
});

function updateTotalPrice() {
    const hasDayBasedPricing = document.querySelector('[data-has-day-based-pricing]').dataset.hasDayBasedPricing === 'true';
    const clientTypeDropdown = document.getElementById('client_type');
    const totalPriceElement = document.getElementById('total-price')?.querySelector('span');
    const totalPriceInput = document.getElementById('total_price_input');
    
    if (!clientTypeDropdown || !totalPriceElement || !totalPriceInput) return;
    
    const ctVal = clientTypeDropdown.value;
    let baseTotal = 0;
    
    if (hasDayBasedPricing) {
        if (ctVal) {
            baseTotal = parseFloat(ctVal);
        }
    } else {
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        
        const f = dateFromInput?.value;
        const t = dateToInput?.value;

        if (ctVal && f && t) {
            const fromDate = new Date(f),
                toDate = new Date(t),
                diffMs = toDate - fromDate,
                daysDiff = Math.floor(diffMs / (1000*60*60*24)) + 1;
            
            if (daysDiff > 0) {
                baseTotal = daysDiff * parseFloat(ctVal);
            }
        }
    }
    
    const addonsTotal = calculateAddonsFromDisplay(hasDayBasedPricing);
    const finalTotal = baseTotal + addonsTotal;
    
    totalPriceElement.textContent = '₱' + finalTotal.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    totalPriceInput.value = finalTotal.toFixed(2);
}

function calculateAddonsFromDisplay(hasDayBasedPricing) {
    let addonsTotal = 0;
    
    const savedAddonsState = window.savedAddonsState;
    if (!savedAddonsState) return 0;
    
    const addonValueInputs = document.querySelectorAll('input[name^="addon_values["]');
    
    addonValueInputs.forEach(input => {
        const match = input.name.match(/addon_values\[(\d+)\]/);
        if (!match) return;
        
        const addonId = match[1];
        
        const isChecked = savedAddonsState.checkboxes?.[addonId] || false;
        const savedQuantity = savedAddonsState.quantities?.[addonId] || 0;
        
        if (!isChecked && savedQuantity <= 0) return;
        
        const basePrice = parseFloat(input.value) || 0;
        
        const priceTypeInput = document.querySelector(`input[name="addon_types[${addonId}]"]`);
        const isQuantityBasedInput = document.querySelector(`input[name="addon_is_quantity_based[${addonId}]"]`);
        
        if (!priceTypeInput) return;
        
        const priceType = priceTypeInput.value;
        const isQuantityBased = isQuantityBasedInput?.value === '1';
        
        const quantity = isQuantityBased ? savedQuantity : 0;
        const nights = savedAddonsState.nights?.[addonId] || 1;
        
        let addonPrice = 0;
        
        switch(priceType) {
            case 'per_item':
    if (hasDayBasedPricing) {
        // prices->is_based_on_days is true
        if (isQuantityBased) {
            // Fifth: base_price * quantity
            addonPrice = basePrice * quantity;
        } else {
            // Fourth: just base_price * nights (user input)
            addonPrice = basePrice * nights;
        }
    } else {
        // prices->is_based_on_days is false
        if (isQuantityBased) {
            // Third: quantity * base_price * nights
            addonPrice = quantity * basePrice * nights;  // ← This is correct!
        } else {
            // Second: nights * base_price (user input for "how many days")
            addonPrice = nights * basePrice;
        }
    }
    break;
                
            case 'flat_rate':
                if (hasDayBasedPricing) {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                } else {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity * nights;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                }
                break;
                
            case 'per_unit':
                if (hasDayBasedPricing) {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                } else {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity * nights;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                }
                break;
                
            case 'per_night':
                if (hasDayBasedPricing) {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                } else {
                    if (isQuantityBased) {
                        addonPrice = basePrice * quantity * nights;
                    } else {
                        addonPrice = basePrice * nights;
                    }
                }
                break;
                
            default:
                addonPrice = basePrice;
        }
        
        addonsTotal += addonPrice;
    });
    
    return addonsTotal;
}