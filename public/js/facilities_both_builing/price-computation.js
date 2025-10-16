class PriceComputation {
    constructor() {
        this.setupEventListeners();
        this.updateTotalPrice();
    }

    calculateDateDifference(startDateId, endDateId) {
        const startDateInput = document.getElementById(startDateId);
        const endDateInput = document.getElementById(endDateId);
        
        if (!startDateInput || !endDateInput || !startDateInput.value || !endDateInput.value) {
            return 1;
        }
        
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        const timeDiff = endDate - startDate;
        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
        
        return daysDiff > 1 ? daysDiff : 1;
    }

    calculateAddonPrice(addonType, basePrice, quantity, nights, isBasedOnDays, daysDiff) {
        let addonTotal = 0;
        
        switch(addonType) {
            case 'per_item':
                if (!isBasedOnDays) {
                    addonTotal = daysDiff * basePrice * quantity;
                } else {
                    addonTotal = basePrice * quantity;
                }
                break;
                
            case 'flat_rate':
                if (!isBasedOnDays) {
                    addonTotal = daysDiff * basePrice;
                } else {
                    addonTotal = basePrice;
                }
                break;
                
            case 'per_unit':
                if (!isBasedOnDays) {
                    addonTotal = daysDiff * basePrice * quantity;
                } else {
                    addonTotal = basePrice * quantity;
                }
                break;
                
            case 'per_night':
                if (!isBasedOnDays) {
                    addonTotal = daysDiff * basePrice * nights;
                } else {
                    addonTotal = basePrice * nights;
                }
                break;
                
            default:
                addonTotal = basePrice;
        }
        
        return addonTotal;
    }

    calculateAddonsTotal(bookingType) {
        let addonsTotal = 0;
        const daysDiff = this.calculateDateDifference(
            bookingType === 'shared' ? 'date_from' : 'whole_date_from',
            bookingType === 'shared' ? 'date_to' : 'whole_date_to'
        );
        
        const isBasedOnDays = bookingType === 'shared' ? 
            document.querySelector('input[name="price_id"]')?.dataset?.isBasedOnDays === 'true' :
            document.querySelector('select[name="whole_client_type"]')?.dataset?.isBasedOnDays === 'true';
        
        const addonCheckboxes = document.querySelectorAll(`.addon-checkbox:checked`);
        const addonQuantities = document.querySelectorAll(`.addon-quantity`);
        
        addonCheckboxes.forEach(checkbox => {
            const addonId = checkbox.getAttribute('data-addon-id');
            const basePrice = parseFloat(document.querySelector(`input[name="addon_values[${addonId}]"]`)?.value || 0);
            const addonType = document.querySelector(`input[name="addon_types[${addonId}]"]`)?.value || '';
            const isQuantityBased = document.querySelector(`input[name="addon_is_quantity_based[${addonId}]"]`)?.value === '1';
            
            let quantity = 1;
            let nights = 1;
            
            if (isQuantityBased) {
                const quantityInput = document.querySelector(`input[name="${bookingType}_addon_quantity[${addonId}]"]`);
                quantity = parseInt(quantityInput?.value || 0);
            }
            
            if (addonType === 'per_night') {
                const nightsInput = document.querySelector(`input[name="${bookingType}_addon_nights[${addonId}]"]`);
                nights = parseInt(nightsInput?.value || 1);
            }
            
            addonsTotal += this.calculateAddonPrice(addonType, basePrice, quantity, nights, isBasedOnDays, daysDiff);
        });
        
        addonQuantities.forEach(input => {
            const quantity = parseInt(input.value || 0);
            if (quantity > 0) {
                const addonId = input.getAttribute('data-addon-id');
                const basePrice = parseFloat(document.querySelector(`input[name="addon_values[${addonId}]"]`)?.value || 0);
                const addonType = document.querySelector(`input[name="addon_types[${addonId}]"]`)?.value || '';
                
                let nights = 1;
                
                if (addonType === 'per_night') {
                    const nightsInput = document.querySelector(`input[name="${bookingType}_addon_nights[${addonId}]"]`);
                    nights = parseInt(nightsInput?.value || 1);
                }
                
                addonsTotal += this.calculateAddonPrice(addonType, basePrice, quantity, nights, isBasedOnDays, daysDiff);
            }
        });
        
        return addonsTotal;
    }

    updateTotalPrice() {
        let total = 0;
        const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
        
        if (selectedBookingType && selectedBookingType.value === 'shared') {
            const priceDropdown = document.getElementById('price_id');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            if (priceDropdown && priceDropdown.value) {
                const selectedOption = priceDropdown.options[priceDropdown.selectedIndex];
                const priceValue = parseFloat(selectedOption.dataset.value) || 0;
                total += priceValue;
                
                const selectedPriceInput = document.getElementById('selected_price_value');
                if (selectedPriceInput) {
                    selectedPriceInput.value = priceValue;
                }
            }
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const priceId = input.name.match(/\[(\d+)\]/)[1];
                const priceValue = document.querySelector(`input[name="price_values[${priceId}]"]`);
                if (priceValue && quantity > 0) {
                    total += parseFloat(priceValue.value) * quantity;
                }
            });
            
            const daysDiff = this.calculateDateDifference('date_from', 'date_to');
            if (daysDiff > 1) {
                total *= daysDiff;
            }
            
            total += this.calculateAddonsTotal('shared');
        }
        
        this.updatePriceDisplay(total, 'total_price_input');
    }

    updateWholeTotalPrice() {
        let total = 0;
        const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
        
        if (selectedBookingType && selectedBookingType.value === 'whole') {
            const clientTypeDropdown = document.getElementById('whole_client_type');
            if (clientTypeDropdown && clientTypeDropdown.value) {
                const priceValue = parseFloat(clientTypeDropdown.value) || 0;
                total = priceValue;
                
                const daysDiff = this.calculateDateDifference('whole_date_from', 'whole_date_to');
                if (daysDiff > 1) {
                    total *= daysDiff;
                }
                
                total += this.calculateAddonsTotal('whole');
            }
        }
        
        this.updatePriceDisplay(total, 'whole_total_price_input');
    }

    updatePriceDisplay(total, inputId) {
        const computedTotal = document.getElementById('computed-total');
        const totalPriceInput = document.getElementById(inputId);
        
        if (computedTotal) {
            computedTotal.textContent = '₱' + total.toLocaleString('en-PH', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }
        
        if (totalPriceInput) {
            totalPriceInput.value = total.toFixed(2);
        }
    }

    setupEventListeners() {
        document.addEventListener('change', (event) => {
            const target = event.target;
            
            if (target.matches('input[name="booking_type"]')) {
                const bookingType = target.value;
                if (bookingType === 'shared') {
                    this.updateTotalPrice();
                } else if (bookingType === 'whole') {
                    this.updateWholeTotalPrice();
                }
                return;
            }
            
            if (target.matches('#price_id, .quantity-input, #date_from, #date_to')) {
                this.updateTotalPrice();
                return;
            }
            
            if (target.matches('#whole_client_type, #whole_date_from, #whole_date_to')) {
                this.updateWholeTotalPrice();
                return;
            }
            
            if (target.matches('.addon-checkbox, .addon-quantity, .nights-input')) {
                const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
                if (selectedBookingType && selectedBookingType.value === 'shared') {
                    this.updateTotalPrice();
                } else if (selectedBookingType && selectedBookingType.value === 'whole') {
                    this.updateWholeTotalPrice();
                }
                return;
            }
        });

        document.addEventListener('input', (event) => {
            const target = event.target;
            
            if (target.matches('.quantity-input')) {
                this.updateTotalPrice();
                return;
            }
            
            if (target.matches('.addon-quantity, .nights-input')) {
                const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
                if (selectedBookingType && selectedBookingType.value === 'shared') {
                    this.updateTotalPrice();
                } else if (selectedBookingType && selectedBookingType.value === 'whole') {
                    this.updateWholeTotalPrice();
                }
                return;
            }
        });

        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', () => {
                setTimeout(() => this.updateTotalPrice(), 100);
            });
        }

        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', () => {
                setTimeout(() => this.updateWholeTotalPrice(), 100);
            });
        }

        const modalSaveBtn = document.querySelector('#priceQuantityModal .btn-primary[data-bs-dismiss="modal"]');
        if (modalSaveBtn) {
            modalSaveBtn.addEventListener('click', () => {
                setTimeout(() => this.updateTotalPrice(), 100);
            });
        }
        
        const addonModalSaveBtn = document.querySelector('#sharedAddonsModal .btn-primary[data-bs-dismiss="modal"], #wholeAddonsModal .btn-primary[data-bs-dismiss="modal"]');
        if (addonModalSaveBtn) {
            addonModalSaveBtn.addEventListener('click', () => {
                const selectedBookingType = document.querySelector('input[name="booking_type"]:checked');
                if (selectedBookingType && selectedBookingType.value === 'shared') {
                    setTimeout(() => this.updateTotalPrice(), 100);
                } else if (selectedBookingType && selectedBookingType.value === 'whole') {
                    setTimeout(() => this.updateWholeTotalPrice(), 100);
                }
            });
        }
    }

    makeGlobalFunctions() {
        window.updateTotalPrice = () => this.updateTotalPrice();
        window.updateWholeTotalPrice = () => this.updateWholeTotalPrice();
    }
}
