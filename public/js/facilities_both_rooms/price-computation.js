document.addEventListener('DOMContentLoaded', function() {
    const on = (el, ev, sel, handler) => {
        el.addEventListener(ev, function(e) {
            const target = e.target.closest(sel);
            if (target && el.contains(target)) handler(e, target);
        });
    };

    document.querySelectorAll('.quantity-input').forEach(input => input.addEventListener('input', updateTotalPrice));

    const priceSelect = document.getElementById('price_id');
    if (priceSelect) priceSelect.addEventListener('change', updateTotalPrice);

    const wholePriceSelect = document.getElementById('whole_price_id');
    if (wholePriceSelect) wholePriceSelect.addEventListener('change', updateTotalPrice);

    const sharedRoomSelect = document.getElementById('shared_selected_room');
    if (sharedRoomSelect) sharedRoomSelect.addEventListener('change', updateTotalPrice);

    const wholeRoomSelect = document.getElementById('selected_room');
    if (wholeRoomSelect) wholeRoomSelect.addEventListener('change', updateTotalPrice);

    on(document, 'change', '.addon-checkbox', () => updateTotalPrice());
    on(document, 'input', '.addon-quantity', () => updateTotalPrice());
    on(document, 'input', '.addon-quantity-input', () => updateTotalPrice());
    on(document, 'input', '.nights-input', () => updateTotalPrice());
    on(document, 'change', '.nights-input', () => updateTotalPrice());

    document.querySelectorAll('button[onclick^="saveAddonsChanges"]').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(updateTotalPrice, 0));
    });

    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('hidden.bs.modal', updateTotalPrice);
    });

    document.querySelectorAll('input[name="booking_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedPriceValue = document.getElementById('selected_price_value');
            const selectedWholePriceValue = document.getElementById('selected_whole_price_value');
            if (this.value === 'shared') {
                if (selectedWholePriceValue) selectedWholePriceValue.value = '';
                const wholePriceSelect = document.getElementById('whole_price_id');
                if (wholePriceSelect) wholePriceSelect.value = '';
            } else if (this.value === 'whole') {
                if (selectedPriceValue) selectedPriceValue.value = '';
                const priceSelect = document.getElementById('price_id');
                if (priceSelect) priceSelect.value = '';
                document.querySelectorAll('.quantity-input').forEach(input => { input.value = ''; });
                if (window.updateClientTypeDisplay) window.updateClientTypeDisplay();
            }
            updateTotalPrice();
        });
    });

    updateTotalPrice();
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
            if (selectedPriceValue) selectedPriceValue.value = selectedPrice;

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
            if (selectedWholePriceValue) selectedWholePriceValue.value = selectedPrice;

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
    if (computedTotalElement) computedTotalElement.textContent = formattedTotal;

    const totalPriceField = document.getElementById('total-price-field');
    if (totalPriceField) totalPriceField.value = totalPrice.toFixed(2);
}

function calculateAddonPrice(section) {
    let addonTotal = 0;
    const idSet = new Set();
    document.querySelectorAll(`.addon-quantity[data-section="${section}"], .addon-checkbox[data-section="${section}"], .nights-input[data-section="${section}"]`).forEach(el => {
        const id = el.getAttribute('data-addon-id');
        if (id) idSet.add(id);
    });

    const q = (suffix) => document.querySelector(`input[name$="${suffix}"]`);

    idSet.forEach((addonId) => {
        const basePrice = parseFloat(q(`[addon_values][${addonId}]`)?.value) || 0;
        const priceType = q(`[addon_types][${addonId}]`)?.value || "";
        const isQtyBased = (q(`[addon_is_quantity_based][${addonId}]`)?.value === "1");
        const billing = q(`[addon_billing_cycle][${addonId}]`)?.value || "";

        const qtyInput = document.querySelector(`.addon-quantity[data-section="${section}"][data-addon-id="${addonId}"]`);
        const checkInput = document.querySelector(`.addon-checkbox[data-section="${section}"][data-addon-id="${addonId}"]`);
        const nightsInput = document.querySelector(`.nights-input[data-section="${section}"][data-addon-id="${addonId}"]`);

        const quantity = parseInt(qtyInput?.value) || 0;
        const nightsDays = parseInt(nightsInput?.value) || 0;
        const selected = isQtyBased ? (quantity > 0) : !!(checkInput && checkInput.checked);
        if (!selected) return;

        let add = 0;

        if (priceType === 'per_item') {
            if (!isQtyBased) {
                if (billing === 'per_day') add = basePrice * nightsDays;
                else if (billing === 'per_contract') add = basePrice * 1;
            } else {
                if (billing === 'per_day') add = quantity * basePrice * nightsDays;
                else if (billing === 'per_contract') add = quantity * basePrice * 1;
            }
        } else if (priceType === 'flat_rate') {
            if (billing === 'per_day') add = nightsDays * basePrice;
            else if (billing === 'per_contract') add = basePrice * 1;
        } else if (priceType === 'per_unit') {
            if (billing === 'per_day') add = nightsDays * basePrice;
            else if (billing === 'per_contract') add = basePrice * 1;
        } else if (priceType === 'per_night') {
            if (billing === 'per_day') {
                add = basePrice * nightsDays;
                if (isQtyBased) add *= quantity;
            } else if (billing === 'per_contract') {
                add = basePrice * 1;
                if (isQtyBased) add *= quantity;
            }
        }

        addonTotal += add;
    });

    return addonTotal;
}

window.updateTotalPrice = updateTotalPrice;
