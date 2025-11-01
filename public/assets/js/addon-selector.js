// public/assets/js/addon-selector.js
function setupAddonsUi() {
    const checkboxes = document.querySelectorAll(".addon-checkbox");
    const hiddenEl = document.getElementById("selected_addons");
    const previewEl = document.getElementById("selectedAddonsPreview");
    const listEl = document.getElementById("selectedAddonsList");

    if (!hiddenEl || !previewEl || checkboxes.length === 0) {
        console.warn("Addon selector elements not found");
        return;
    }

    function updateHidden() {
        const values = Array.from(checkboxes)
            .filter((cb) => cb.checked)
            .map((cb) => cb.value);
        hiddenEl.value = values.join(",");
        console.log("Selected addons updated:", values);
    }

    function renderPreview() {
        const selectedItems = Array.from(checkboxes)
            .filter((cb) => cb.checked)
            .map((cb) => ({
                name: cb.dataset.name,
                price: cb.dataset.price,
                type: cb.dataset.type,
            }));

        if (selectedItems.length > 0) {
            listEl.innerHTML = selectedItems
                .map(
                    (item) =>
                        `<li>${item.name} — ₱${item.price} (${item.type})</li>`
                )
                .join("");
            previewEl.style.display = "block";
        } else {
            listEl.innerHTML =
                '<li class="no-addons-message" style="list-style: none;">No addons selected</li>';
            previewEl.style.display = "block";
        }
    }

    // Update hidden field and preview on checkbox change
    checkboxes.forEach((cb) => {
        cb.addEventListener("change", function () {
            updateHidden();
            renderPreview();
        });
    });

    // Initialize
    updateHidden();
    const hasPreselected = hiddenEl.value && hiddenEl.value.trim() !== "";
    if (hasPreselected) {
        console.log("Found preselected addons, auto-rendering preview");
        renderPreview();
    }

    console.log("Addon selector initialized");
    console.log("Initial selected addons:", hiddenEl.value);
}

// Initialize on DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupAddonsUi);
} else {
    setupAddonsUi();
}
