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

    calculateAddonPrice(addonType, basePrice, quantity, nights) {
        const base = parseFloat(basePrice) || 0;
        const qty = parseInt(quantity, 10) || 0;
        const n = parseInt(nights, 10) || 0;
        switch (addonType) {
            case 'per_item':   return base * Math.max(qty, 1);
            case 'per_night':  return base * Math.max(n, 1) * Math.max(qty, 1);
            case 'flat_rate':  return base * Math.max(qty, 1);
            case 'per_unit':   return base * Math.max(qty, 1);
            default:           return base;
        }
    }

    calculateAddonsTotal(bookingType) {
        const ns = bookingType === 'whole' ? 'whole_addons' : 'shared_addons';
        const valueInputs = Array.from(document.querySelectorAll(`input[name^="${ns}[addon_values]"]`));
        let total = 0;
        const dayMs = 24 * 60 * 60 * 1000;
        const dateDiffInclusive = (fromStr, toStr) => {
            if (!fromStr || !toStr) return 0;
            const from = new Date(fromStr);
            const to = new Date(toStr);
            if (isNaN(from) || isNaN(to)) return 0;
            const diff = Math.ceil((to - from) / dayMs) + 1;
            return diff > 0 ? diff : 0;
        };
        const getVal = (key, id) => {
            const el = document.querySelector(`input[name$="${ns}[${key}][${id}]"], select[name$="${ns}[${key}][${id}]"]`);
            return el ? el.value : undefined;
        };
        const isChecked = (key, id) => {
            const el = document.querySelector(`input[name$="${ns}[${key}][${id}]"]`);
            return !!(el && el.checked);
        };
        const intSafe = (v, d = 0) => {
            const n = parseInt(v, 10);
            return Number.isFinite(n) ? n : d;
        };
        const floatSafe = (v, d = 0) => {
            const n = parseFloat(v);
            return Number.isFinite(n) ? n : d;
        };
        const computeAddonTotal = ({ type, base, qty, nights, days, billing, qtyBased }) => {
            let t = 0;
            const factor = billing === 'per_day' ? (days > 0 ? days : 0) : 1;
            switch (type) {
                case 'per_item':
                    if (billing === 'per_day') {
                        t = base * factor;
                        if (qtyBased) t *= Math.max(qty, 0);
                    } else {
                        t = base * 1;
                        if (qtyBased) t *= Math.max(qty, 0);
                    }
                    break;
                case 'flat_rate':
                    t = (billing === 'per_day') ? (base * factor) : (base * 1);
                    if (qtyBased) t *= Math.max(qty, 0);
                    break;
                case 'per_unit':
                    t = (billing === 'per_day') ? (base * factor) : (base * 1);
                    if (qtyBased) t *= Math.max(qty, 0);
                    break;
                case 'per_night':
                    if (billing === 'per_day') {
                        t = base * Math.max(nights, 0);
                        if (qtyBased) t *= Math.max(qty, 0);
                    } else {
                        t = base * (qtyBased ? Math.max(qty, 0) : 1);
                    }
                    break;
                default:
                    t = base * (qtyBased ? Math.max(qty, 0) : 1);
            }
            return t;
        };
        valueInputs.forEach((inp) => {
            const name = inp.getAttribute('name') || '';
            const m = name.match(/\[addon_values]\[(\d+)]$/);
            if (!m) return;
            const id = m[1];
            const base = floatSafe(getVal('addon_values', id), 0);
            const type = (getVal('addon_types', id) || '').trim();
            const billing = (getVal('addon_billing_cycle', id) || 'per_day').trim();
            const qtyBased = (getVal('addon_is_quantity_based', id) || '0') === '1';
            const qty = intSafe(getVal('addon_quantity', id), 0);
            const checked = isChecked('addon_checkbox', id);
            const selected = qtyBased ? qty > 0 : checked;
            if (!selected) return;
            const nights = intSafe(getVal('addon_nights', id), 0);
            let days = 0;
            if (billing === 'per_day') {
                const df = getVal('addon_date_from', id);
                const dt = getVal('addon_date_to', id);
                days = dateDiffInclusive(df, dt);
            }
            total += computeAddonTotal({ type, base, qty, nights, days, billing, qtyBased });
        });
        return total;
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
        
        const addonModalSaveBtn = document.querySelector('#sharedAddonsModal .btn-primary[data-bs-dismiss="modal"], #wholeAddonsModal .btn-primary[data-bs-dismiss="modal"], #addonsModal-shared .btn-primary[data-bs-dismiss="modal"], #addonsModal-whole .btn-primary[data-bs-dismiss="modal"]');
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
