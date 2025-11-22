// // D:\Herd\cvsu-barg-is\public\assets\js\discount-selector.js
// function setupDiscountsUi() {
//     const selectEl = document.getElementById("discountMultiSelect");
//     const hiddenEl = document.getElementById("selected_discounts");
//     const previewEl = document.getElementById("selectedDiscountsPreview");
//     const showBtn = document.getElementById("showSelectedDiscountsBtn");

//     if (!selectEl || !hiddenEl || !previewEl || !showBtn) {
//         console.warn("Discount selector elements not found");
//         return;
//     }

//     function updateHidden() {
//         const values = Array.from(selectEl.selectedOptions).map((o) => o.value);
//         hiddenEl.value = values.join(",");
//         console.log("Selected discounts updated:", values);
//     }

//     function renderPreview() {
//         const items = Array.from(selectEl.selectedOptions).map((o) =>
//             o.textContent.trim()
//         );
//         if (items.length > 0) {
//             previewEl.innerHTML =
//                 '<ul class="mb-0 ps-3">' +
//                 items.map((t) => `<li>${t}</li>`).join("") +
//                 "</ul>";
//             previewEl.style.display = "block";
//         } else {
//             previewEl.innerHTML =
//                 '<span class="text-muted">No discounts selected</span>';
//             previewEl.style.display = "block";
//         }
//     }

//     selectEl.addEventListener("change", function () {
//         updateHidden();
//         renderPreview();
//     });

//     showBtn.addEventListener("click", renderPreview);

//     updateHidden();
//     const hasPreselected = hiddenEl.value && hiddenEl.value.trim() !== "";
//     if (hasPreselected) {
//         console.log("Found preselected discounts, auto-rendering preview");
//         renderPreview();
//     }

//     console.log("Discount selector initialized");
//     console.log("Initial selected discounts:", hiddenEl.value);
// }

// public/assets/js/discount-selector.js
function setupDiscountsUi() {
    const checkboxes = document.querySelectorAll(".discount-checkbox");
    const hiddenEl = document.getElementById("selected_discounts");
    const previewEl = document.getElementById("selectedDiscountsPreview");
    const listEl = document.getElementById("selectedDiscountsList");

    if (!hiddenEl || !previewEl || checkboxes.length === 0) {
        console.warn("Discount selector elements not found");
        return;
    }

    function updateHidden() {
        const values = Array.from(checkboxes)
            .filter((cb) => cb.checked)
            .map((cb) => cb.value);
        hiddenEl.value = values.join(",");
        console.log("Selected discounts updated:", values);
    }

    function renderPreview() {
        const selectedItems = Array.from(checkboxes)
            .filter((cb) => cb.checked)
            .map((cb) => ({
                name: cb.dataset.name,
                percent: cb.dataset.percent,
                appliesTo: cb.dataset.appliesTo,
            }));

        if (selectedItems.length > 0) {
            listEl.innerHTML = selectedItems
                .map(
                    (item) =>
                        `<li>${item.name} (${item.percent}%${item.appliesTo})</li>`
                )
                .join("");
            previewEl.style.display = "block";
        } else {
            listEl.innerHTML =
                '<li class="no-discounts-message" style="list-style: none;">No discounts selected</li>';
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
        console.log("Found preselected discounts, auto-rendering preview");
        renderPreview();
    }

    console.log("Discount selector initialized");
    console.log("Initial selected discounts:", hiddenEl.value);
}

// Initialize on DOM ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupDiscountsUi);
} else {
    setupDiscountsUi();
}
