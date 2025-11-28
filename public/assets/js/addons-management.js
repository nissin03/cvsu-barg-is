// resources/views/admin/facilities/scripts/addons-management.blade.php
function setupAddonsManagement() {
    const addonItems = document.querySelectorAll(".addon-item");
    const selectedContainer = document.getElementById(
        "selectedAddonsContainer"
    );
    const hiddenInput = document.getElementById("selectedAddonsInput");
    // let selectedAddons = [];

    let selectedAddons = hiddenInput.value ? hiddenInput.value.split(",") : [];

    addonItems.forEach((item) => {
        item.addEventListener("click", () => {
            const id = item.dataset.id;
            const name = item.dataset.name;

            if (selectedAddons.includes(id)) {
                selectedAddons = selectedAddons.filter((a) => a !== id);
                item.classList.remove("text-decoration-line-through");
                const chip = selectedContainer.querySelector(
                    `[data-id='${id}']`
                );
                if (chip) chip.remove();
            } else {
                selectedAddons.push(id);
                item.classList.add("text-decoration-line-through");

                const chip = document.createElement("span");
                chip.className = "badge bg-primary px-3 py-2";
                chip.innerText = name;
                chip.dataset.id = id;
                chip.style.cursor = "pointer";

                chip.addEventListener("click", () => {
                    selectedAddons = selectedAddons.filter((a) => a !== id);
                    item.classList.remove("text-decoration-line-through");
                    chip.remove();
                    hiddenInput.value = selectedAddons.join(",");
                });

                selectedContainer.appendChild(chip);
            }
            hiddenInput.value = selectedAddons.join(",");
        });
    });
}
