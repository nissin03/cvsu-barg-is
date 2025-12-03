class ClientTypeHandler {
    constructor(facility, facilityCapacity) {
        this.facility = facility;
        this.facilityCapacity = facilityCapacity;
        this.setupEventListeners();
    }

    validateQuantityInput(input) {
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        let maxCapacity = this.facilityCapacity;
        if (dateFrom && dateTo && dateFrom.value && dateTo.value && window.calendarValidation) {
            maxCapacity = window.calendarValidation.getMinCapacityForDateRange(dateFrom.value, dateTo.value);
        }
        
        const quantity = parseInt(input.value) || 0;
        
        if (quantity > maxCapacity) {
            Swal.fire({
                icon: 'error',
                title: 'Capacity Exceeded',
                text: `The quantity exceeds the maximum capacity of ${maxCapacity} persons.`,
                confirmButtonColor: '#3085d6',
            }).then(() => {
                input.value = '';
                input.focus();
            });
            return false;
        }
        
        const quantityInputs = document.querySelectorAll('.quantity-input');
        let totalQuantity = 0;
        
        quantityInputs.forEach(qInput => {
            const qty = parseInt(qInput.value) || 0;
            totalQuantity += qty;
        });
        
        if (totalQuantity > maxCapacity) {
            Swal.fire({
                icon: 'error',
                title: 'Total Capacity Exceeded',
                text: `The total number of persons (${totalQuantity}) exceeds the maximum capacity of ${maxCapacity} persons.`,
                confirmButtonColor: '#3085d6',
            }).then(() => {
                input.value = '';
                input.focus();
            });
            return false;
        }
        
        return true;
    }

    updateClientTypeDisplay() {
        const container = document.getElementById('selected-client-types-display');
        if (!container) return;
        
        container.innerHTML = '';
        
        const quantityInputs = document.querySelectorAll('.quantity-input');
        let hasSelection = false;
        
        quantityInputs.forEach(input => {
            if (input.value && parseInt(input.value) > 0) {
                hasSelection = true;
                const priceId = input.name.match(/\[(.*?)\]/)[1];
                const priceNameInput = document.querySelector(`input[name="price_names[${priceId}]"]`);
                const priceValueInput = document.querySelector(`input[name="price_values[${priceId}]"]`);
                
                if (priceNameInput && priceValueInput) {
                    const priceName = priceNameInput.value;
                    const priceValue = parseFloat(priceValueInput.value);
                    
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'client-type-item';
                    itemDiv.innerHTML = `
                        <strong>${priceName}</strong>
                        <span>Price: ₱${priceValue.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span><br>
                        <span>Qty: ${parseInt(input.value).toLocaleString()}</span>
                    `;
                    
                    container.appendChild(itemDiv);
                }
            }
        });
        
        if (hasSelection) {
            container.style.display = 'flex'; 
        } else {
            container.style.display = 'none';
        }
        
        if (window.priceComputation) {
            window.priceComputation.updateTotalPrice();
        }
    }

    setupEventListeners() {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', () => {
                if (this.validateQuantityInput(input)) {
                    this.updateClientTypeDisplay();
                }
            });
        });

        const priceQuantityModal = document.getElementById('priceQuantityModal');
        if (priceQuantityModal) {
            priceQuantityModal.addEventListener('hidden.bs.modal', () => {
                this.updateClientTypeDisplay();
            });
        }

        const wholeClientTypeDropdown = document.getElementById('whole_client_type');
        if (wholeClientTypeDropdown) {
            wholeClientTypeDropdown.addEventListener('change', () => {
                if (window.priceComputation) {
                    window.priceComputation.updateWholeTotalPrice();
                }
            });
        }

        const priceSelect = document.getElementById('price_id');
        if (priceSelect) {
            priceSelect.addEventListener('change', () => {
                if (window.priceComputation) {
                    window.priceComputation.updateTotalPrice();
                }
            });
        }
    }

    initializeDisplay() {
        this.updateClientTypeDisplay();
    }
}