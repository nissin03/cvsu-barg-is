document.addEventListener('DOMContentLoaded', function() {
    window.updateTotalPrice = updateTotalPrice;

    ensureSavedAddonsState();
    bootstrapAddonStateFromDOM();
    wireEvents();
    observeAddonsDOM();

    updateTotalPrice();
});

function ensureSavedAddonsState() {
    if (!window.savedAddonsState) {
        window.savedAddonsState = { checkboxes: {}, quantities: {}, nights: {} };
    } else {
        window.savedAddonsState.checkboxes ||= {};
        window.savedAddonsState.quantities ||= {};
        window.savedAddonsState.nights ||= {};
    }
}

function wireEvents() {
    document.addEventListener('addonsUpdated', updateTotalPrice);

    const clientTypeDropdown = document.getElementById('client_type');
    if (clientTypeDropdown) {
        clientTypeDropdown.addEventListener('change', updateTotalPrice);
    }

    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    if (dateFromInput) dateFromInput.addEventListener('change', updateTotalPrice);
    if (dateToInput) dateToInput.addEventListener('change', updateTotalPrice);

    const confirmBtn = document.getElementById('confirm-dates');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            document.dispatchEvent(new CustomEvent('addonsUpdated'));
            updateTotalPrice();
        });
    }

    const addonsModal = document.getElementById('addonsModal');
    if (addonsModal) {
        addonsModal.addEventListener('shown.bs.modal', () => {
            bootstrapAddonStateFromDOM();
            updateTotalPrice();
        });
        addonsModal.addEventListener('hidden.bs.modal', () => {
            bootstrapAddonStateFromDOM();
            updateTotalPrice();
        });
    }

    document.addEventListener('input', handleAddonInputEvent, true);
    document.addEventListener('change', handleAddonInputEvent, true);
}

function handleAddonInputEvent(e) {
    const t = e.target;
    if (!t) return;

    if (t.classList.contains('addon-checkbox')) {
        const addonId = t.dataset.addonId;
        window.savedAddonsState.checkboxes[addonId] = !!t.checked;
        updateTotalPrice();
        return;
    }

    if (t.classList.contains('addon-quantity')) {
        const addonId = t.dataset.addonId;
        const qty = Math.max(0, Number(t.value || 0));
        window.savedAddonsState.quantities[addonId] = qty;
        updateTotalPrice();
        return;
    }

    if (t.classList.contains('nights-input') || t.classList.contains('per-night-nights')) {
        const addonId = t.dataset.addonId;
        const nights = Math.max(0, Number(t.value || 0));
        window.savedAddonsState.nights[addonId] = nights;
        updateTotalPrice();
        return;
    }
}

function bootstrapAddonStateFromDOM() {
    ensureSavedAddonsState();

    const checkboxEls = document.querySelectorAll('.addon-checkbox');
    checkboxEls.forEach(el => {
        const id = el.dataset.addonId;
        window.savedAddonsState.checkboxes[id] = !!el.checked;
    });

    const qtyEls = document.querySelectorAll('.addon-quantity');
    qtyEls.forEach(el => {
        const id = el.dataset.addonId;
        window.savedAddonsState.quantities[id] = Math.max(0, Number(el.value || 0));
    });

    const nightEls = document.querySelectorAll('.nights-input, .per-night-nights');
    nightEls.forEach(el => {
        const id = el.dataset.addonId;
        window.savedAddonsState.nights[id] = Math.max(0, Number(el.value || 0));
    });
}

function observeAddonsDOM() {
    const container = document.getElementById('addonsModal') || document;
    const observer = new MutationObserver(() => {
        bootstrapAddonStateFromDOM();
        updateTotalPrice();
    });
    observer.observe(container, { childList: true, subtree: true });
}

function updateTotalPrice() {
    const hasDayBasedPricing = document.querySelector('[data-has-day-based-pricing]')?.dataset.hasDayBasedPricing === 'true';
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
                  toDate   = new Date(t),
                  diffMs   = toDate - fromDate,
                  daysDiff = Math.floor(diffMs / (1000*60*60*24)) + 1;
            if (daysDiff > 0) {
                baseTotal = daysDiff * parseFloat(ctVal);
            }
        }
    }

    const addonsTotal = calculateAddonsFromDisplay();
    const finalTotal  = baseTotal + addonsTotal;

    totalPriceElement.textContent = '₱' + finalTotal.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    totalPriceInput.value = finalTotal.toFixed(2);
}

function calculateAddonsFromDisplay() {
    let addonsTotal = 0;
    const savedAddonsState = window.savedAddonsState;
    if (!savedAddonsState) return 0;

    const addonValueInputs = document.querySelectorAll('input[name^="addon_values["]');

    addonValueInputs.forEach(input => {
        const match = input.name.match(/addon_values\[(\d+)\]/);
        if (!match) return;

        const addonId = match[1];
        const isChecked = !!savedAddonsState.checkboxes?.[addonId];
        const savedQuantity = Number(savedAddonsState.quantities?.[addonId] ?? 0);
        if (!isChecked && savedQuantity <= 0) return;

        const basePrice = parseFloat(input.value) || 0;
        const priceTypeInput = document.querySelector(`input[name="addon_types[${addonId}]"]`);
        const isQuantityBasedInput = document.querySelector(`input[name="addon_is_quantity_based[${addonId}]"]`);
        const billingCycleInput = document.querySelector(`input[name="addon_billing_cycle[${addonId}]"]`);
        if (!priceTypeInput || !billingCycleInput) return;

        const priceType = String(priceTypeInput.value || '').trim();
        const isQuantityBased = (isQuantityBasedInput?.value === '1');
        const billingCycle = String(billingCycleInput.value || 'per_day').trim();

        const daysOrNights = Number(savedAddonsState.nights?.[addonId] ?? 0);
        const how_many_days = Math.max(0, daysOrNights);
        const how_many_nights = Math.max(0, daysOrNights);

        let addonPrice = 0;

        switch (priceType) {
            case 'per_item':
                if (isQuantityBased) {
                    if (billingCycle === 'per_day') {
                        addonPrice = savedQuantity * basePrice * how_many_days;
                    } else {
                        addonPrice = savedQuantity * basePrice * 1;
                    }
                } else {
                    if (billingCycle === 'per_day') {
                        addonPrice = basePrice * how_many_days;
                    } else {
                        addonPrice = basePrice * 1;
                    }
                }
                break;

            case 'flat_rate':
                if (billingCycle === 'per_day') {
                    addonPrice = how_many_days * basePrice;
                } else {
                    addonPrice = basePrice * 1;
                }
                break;

            case 'per_unit':
                if (billingCycle === 'per_day') {
                    addonPrice = how_many_days * basePrice;
                } else {
                    addonPrice = basePrice * 1;
                }
                break;

            case 'per_night':
                if (billingCycle === 'per_day') {
                    if (isQuantityBased) {
                        addonPrice = basePrice * how_many_nights * savedQuantity;
                    } else {
                        addonPrice = basePrice * how_many_nights;
                    }
                } else {
                    if (isQuantityBased) {
                        addonPrice = basePrice * savedQuantity;
                    } else {
                        addonPrice = basePrice * 1;
                    }
                }
                break;

            default:
                addonPrice = 0;
        }

        addonsTotal += addonPrice;
    });

    return addonsTotal;
}
