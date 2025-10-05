class FormValidation {
    constructor(facility, facilityCapacity) {
        this.facility = facility;
        this.facilityCapacity = facilityCapacity;
        this.reserveBtn = document.getElementById('reserve-btn');
        this.bookingTypeRadios = document.querySelectorAll('input[name="booking_type"]');
        this.sharedSection = document.getElementById('shared-section');
        this.wholeSection = document.getElementById('whole-section');
        
        this.initializeValidation();
    }

    initializeValidation() {
        if (this.reserveBtn) {
            this.reserveBtn.disabled = true;
            this.reserveBtn.style.opacity = '0.5';
            this.reserveBtn.style.cursor = 'not-allowed';
        }
        
        this.bookingTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'shared') {
                    this.sharedSection.style.display = 'block';
                    this.wholeSection.style.display = 'none';
                } else if (radio.value === 'whole') {
                    this.sharedSection.style.display = 'none';
                    this.wholeSection.style.display = 'block';
                }
                this.validateForm();
            });
        });
        
        this.setupEventListeners();
        this.validateForm();
    }

    validateSharedSection() {
        const isSharedSelected = document.querySelector('input[name="booking_type"]:checked')?.value === 'shared';
        if (!isSharedSelected) return false;
        
        let isValid = true;
        
        const dateBasedPricing = this.facility.prices.some(price => price.is_based_on_days);
        
        if (!dateBasedPricing) {
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            
            if (!dateFrom || !dateTo) {
                isValid = false;
            }
        }
        
        const hasQuantityPricing = this.facility.prices.filter(price => price.is_there_a_quantity).length > 0;
        const maxCapacity = parseInt(document.querySelector('.capacity-value')?.textContent) || 0;
        
        if (hasQuantityPricing) {
            const quantityInputs = document.querySelectorAll('input[name^="internal_quantity"]');
            let hasValidQuantity = false;
            let totalQuantity = 0;
            
            quantityInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                if (value > 0) {
                    hasValidQuantity = true;
                    totalQuantity += value;
                }
            });
            
            if (!hasValidQuantity) {
                isValid = false;
            }
            
            if (totalQuantity > maxCapacity) {
                isValid = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Capacity Exceeded',
                    text: `The total number of persons (${totalQuantity}) exceeds the facility's maximum capacity of ${maxCapacity}. Please adjust your quantities.`,
                    confirmButtonColor: '#3085d6',
                });
            }
        } else {
            const priceSelect = document.getElementById('price_id');
            if (priceSelect && !priceSelect.value) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    validateWholeSection() {
        const isWholeSelected = document.querySelector('input[name="booking_type"]:checked')?.value === 'whole';
        if (!isWholeSelected) return false;
        
        let isValid = true;
        
        const dateBasedPricing = this.facility.prices.some(price => price.is_based_on_days);
        
        if (!dateBasedPricing) {
            const wholeDateFrom = document.getElementById('whole_date_from')?.value;
            const wholeDateTo = document.getElementById('whole_date_to')?.value;
            
            if (!wholeDateFrom || !wholeDateTo) {
                isValid = false;
            }
        }
        
        const wholeClientType = document.getElementById('whole_client_type');
        if (wholeClientType && !wholeClientType.value) {
            isValid = false;
        }
        
        return isValid;
    }
    
    validateForm() {
        const selectedBookingType = document.querySelector('input[name="booking_type"]:checked')?.value;
        let isFormValid = false;
        
        if (selectedBookingType === 'shared') {
            isFormValid = this.validateSharedSection();
        } else if (selectedBookingType === 'whole') {
            isFormValid = this.validateWholeSection();
        }
        
        if (this.reserveBtn) {
            if (isFormValid) {
                this.reserveBtn.disabled = false;
                this.reserveBtn.style.opacity = '1';
                this.reserveBtn.style.cursor = 'pointer';
            } else {
                this.reserveBtn.disabled = true;
                this.reserveBtn.style.opacity = '0.5';
                this.reserveBtn.style.cursor = 'not-allowed';
            }
        }
    }

    setupEventListeners() {
        const quantityInputs = document.querySelectorAll('input[name^="internal_quantity"], .quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', () => this.validateForm());
            input.addEventListener('change', () => this.validateForm());
        });
        
        const dateInputs = ['date_from', 'date_to', 'whole_date_from', 'whole_date_to'];
        dateInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', () => this.validateForm());
            }
        });
        
        const priceSelect = document.getElementById('price_id');
        if (priceSelect) {
            priceSelect.addEventListener('change', () => this.validateForm());
        }
        
        const wholeClientTypeSelect = document.getElementById('whole_client_type');
        if (wholeClientTypeSelect) {
            wholeClientTypeSelect.addEventListener('change', () => this.validateForm());
        }
        
        const sharedConfirmBtn = document.getElementById('shared-confirm-dates');
        const wholeConfirmBtn = document.getElementById('whole-confirm-dates');
        
        if (sharedConfirmBtn) {
            sharedConfirmBtn.addEventListener('click', () => {
                setTimeout(() => this.validateForm(), 100);
            });
        }
        
        if (wholeConfirmBtn) {
            wholeConfirmBtn.addEventListener('click', () => {
                setTimeout(() => this.validateForm(), 100);
            });
        }
        
        const modalSaveBtn = document.querySelector('#priceQuantityModal .btn-primary[data-bs-dismiss="modal"]');
        if (modalSaveBtn) {
            modalSaveBtn.addEventListener('click', () => {
                setTimeout(() => this.validateForm(), 100);
            });
        }
    }
}