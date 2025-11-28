function updateTotalPrice() {
    let totalPrice = 0;
    
    const computedTotalEl = document.getElementById('computed-total');
    const totalPriceInputEl = document.getElementById('total_price_input');
    
    if (!computedTotalEl || !totalPriceInputEl) {
        console.error('Total price elements not found');
        return;
    }
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        const quantity = parseInt(input.value) || 0;
        const formFloating = input.closest('.form-floating');
        
        if (formFloating) {
            const productTypeEl = formFloating.querySelector('.product-type');
            if (productTypeEl) {
                const priceText = productTypeEl.textContent;
                const priceValue = parseFloat(priceText.replace(/[^0-9.]/g, ''));

                if (!isNaN(priceValue) && quantity > 0) {
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
        
        if (selectedPrice > 0) {
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
    }
    
    totalPrice += calculateAddonsTotal();
    
    const formattedTotal = '₱' + totalPrice.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    computedTotalEl.textContent = formattedTotal;
    totalPriceInputEl.value = totalPrice.toFixed(2);
}

function calculateAddonsTotal() {
    let addonsTotal = 0;
    const activeAddons = getActiveAddons();
    
    activeAddons.forEach(addon => {
        const basePrice = parseFloat(addon.basePrice) || 0;
        const priceType = addon.priceType;
        const billingCycle = addon.billingCycle;
        const quantity = parseInt(addon.quantity) || 1;
        const nights = parseInt(addon.nights) || 1;
        const isBasedOnQuantity = addon.isBasedOnQuantity;
        
        let addonTotal = 0;
        
        switch(priceType) {
            case 'per_item':
                if (isBasedOnQuantity) {
                    if (billingCycle === 'per_day') {
                        addonTotal = quantity * basePrice * nights;
                    } else {
                        addonTotal = quantity * basePrice * 1;
                    }
                } else {
                    if (billingCycle === 'per_day') {
                        addonTotal = basePrice * nights;
                    } else {
                        addonTotal = basePrice * 1;
                    }
                }
                break;
                
            case 'flat_rate':
                if (billingCycle === 'per_day') {
                    addonTotal = nights * basePrice;
                } else {
                    addonTotal = basePrice * 1;
                }
                break;
                
            case 'per_unit':
                if (billingCycle === 'per_day') {
                    addonTotal = nights * basePrice;
                } else {
                    addonTotal = basePrice * 1;
                }
                break;
                
            case 'per_night':
                if (billingCycle === 'per_day') {
                    if (isBasedOnQuantity) {
                        addonTotal = basePrice * nights * quantity;
                    } else {
                        addonTotal = basePrice * nights;
                    }
                } else {
                    if (isBasedOnQuantity) {
                        addonTotal = basePrice * quantity;
                    } else {
                        addonTotal = basePrice * 1;
                    }
                }
                break;
                
            default:
                addonTotal = basePrice;
        }
        
        addonsTotal += addonTotal;
    });
    
    return addonsTotal;
}

function getActiveAddons() {
    const activeAddons = [];
    
    document.querySelectorAll('.addon-checkbox:checked').forEach(checkbox => {
        const addonId = checkbox.getAttribute('data-addon-id');
        const addonData = getAddonData(addonId);
        if (addonData) {
            activeAddons.push({
                ...addonData,
                quantity: 1
            });
        }
    });
    
    document.querySelectorAll('.addon-quantity').forEach(input => {
        const quantity = parseInt(input.value) || 0;
        if (quantity > 0) {
            const addonId = input.getAttribute('data-addon-id');
            const addonData = getAddonData(addonId);
            if (addonData) {
                activeAddons.push({
                    ...addonData,
                    quantity: quantity
                });
            }
        }
    });
    
    return activeAddons;
}

function getAddonData(addonId) {
    const basePriceInput = document.querySelector(`input[name="addon_values[${addonId}]"]`);
    const priceTypeInput = document.querySelector(`input[name="addon_types[${addonId}]"]`);
    const billingCycleInput = document.querySelector(`input[name="addon_billing_cycle[${addonId}]"]`);
    const isBasedOnQuantityInput = document.querySelector(`input[name="addon_is_quantity_based[${addonId}]"]`);
    const nightsInput = document.getElementById(`addon_nights-${addonId}`);
    
    if (!basePriceInput || !priceTypeInput) {
        return null;
    }
    
    return {
        basePrice: basePriceInput.value,
        priceType: priceTypeInput.value,
        billingCycle: billingCycleInput ? billingCycleInput.value : 'per_contract',
        isBasedOnQuantity: isBasedOnQuantityInput ? parseInt(isBasedOnQuantityInput.value) === 1 : false,
        nights: nightsInput ? parseInt(nightsInput.value) || 1 : 1
    };
}

function setupAddonEventListeners() {
    document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    document.querySelectorAll('.addon-quantity').forEach(input => {
        input.addEventListener('change', updateTotalPrice);
        input.addEventListener('input', updateTotalPrice);
    });
    
    document.querySelectorAll('.nights-input').forEach(input => {
        input.addEventListener('change', updateTotalPrice);
    });
}

window.updateTotalPrice = updateTotalPrice;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateTotalPrice);
    });
    
    setupAddonEventListeners();
    
    const addonsModal = document.getElementById('addonsModal');
    if (addonsModal) {
        addonsModal.addEventListener('hidden.bs.modal', function() {
            setTimeout(updateTotalPrice, 100);
        });
    }
});