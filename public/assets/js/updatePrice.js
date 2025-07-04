"use strict";

$(document).ready(function () {
    const $addPriceModal = $("#addPrice");
    const $priceCardsContainer = $("#priceCardsContainer");
    const $priceFormContainer = $("#priceFormContainer");
    const $hiddenPrices = $("#hiddenPrices");

    let prices = initialPrices;
    let editMode = false;
    let editIndex = -1;

    // Initialize the first price form when the modal is opened
    $("#addPrice").on("show.bs.modal", function () {
        if ($priceFormContainer.children().length === 0) {
            $priceFormContainer.append(createPriceFormCard());
            if (typeof window.updatePriceFieldVisibility === "function") {
                window.updatePriceFieldVisibility($("#rentalType").val());
            }
        }

        // Add global settings after the price form container
        if ($("#globalPriceSettings").length === 0) {
            const globalSettings = `
            <div id="globalPriceSettings" class="mt-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-check-input" id="globalIsBasedOnDays">
                        <label class="form-check-label ms-2 pt-2">Is based on days?</label>
                    </div>
                    <div class="d-flex align-items-center ms-3">
                        <input type="checkbox" class="form-check-input" id="globalIsThereAQuantity">
                        <label class="form-check-label ms-2 pt-2">Is there a quantity?</label>
                    </div>
                </div>
                <div class="row g-3 mt-2 date-fields" style="display: none;">
                    <div class="col-md-6">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="globalDateFrom">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="globalDateTo">
                    </div>
                </div>
            </div>`;
            $priceFormContainer.after(globalSettings);
        }
    });

    $("#addMultiplePricesRowBtn").on("click", function (e) {
        e.preventDefault();
        $priceFormContainer.append(createPriceFormCard());

        if (typeof window.updatePriceFieldVisibility === "function") {
            window.updatePriceFieldVisibility($("#rentalType").val());
        }
    });

    function disablePastDates($form) {
        const today = new Date().toISOString().split("T")[0];
        $form.find(".date-from").attr("min", today);
        $form.find(".date-to").attr("min", today);
    }

    // Update date fields when global date from changes
    $(document).on("change", "#globalDateFrom", function () {
        const $dateTo = $("#globalDateTo");
        if ($(this).val()) {
            $dateTo.val($(this).val());
        }
    });

    // Create the price form card
    function createPriceFormCard() {
        return `
        <div class="price-form-card mb-3 p-3 border rounded">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Price Name</label>
                    <input type="text" class="form-control price-name" placeholder="Enter price name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control price-value" min="1" placeholder="Enter price">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Price Type</label>
                    <select class="price-type">
                        <option value="">Choose Price Type</option>
                        <option value="individual">Individual</option>
                        <option value="whole">Whole Place</option>
                    </select>
                </div>
            </div>

            <div class="col-md-1 d-flex align-items-center mt-3">
                <button type="button" class="btn btn-lg btn-outline-danger removePriceBtn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
    }

    // Toggle the display of date fields based on global 'is-based-on-days' checkbox
    $(document).on("change", "#globalIsBasedOnDays", function () {
        const $dateFields = $(".date-fields");
        if ($(this).prop("checked")) {
            $dateFields.show();
            disablePastDates($("#globalPriceSettings")); // Disable past dates when shown
        } else {
            $dateFields.hide();
        }
    });

    // Remove price form when the "Remove" button is clicked
    $(document).on("click", ".removePriceBtn", function () {
        const $card = $(this).closest(".price-form-card");
        if ($(".price-form-card").length > 1) {
            $card.remove();
        } else {
            // Reset the first form card if it is the only one
            $card
                .find("input[type='text'], input[type='number'], select")
                .val("");
            $card.find("input[type='checkbox']").prop("checked", false);
            $card.find("input[type='date']").val("");
        }
    });

    // Save all prices when "Save All" button is clicked
    $("#saveMultiplePricesBtn").on("click", function (e) {
        e.preventDefault();
        if (editMode) {
            updatePrice(editIndex);
        } else {
            saveMultiplePrices();
        }
    });

    // Save multiple prices logic
    function saveMultiplePrices() {
        let valid = true;
        let newPrices = [];

        const isBasedOnDays = $("#globalIsBasedOnDays").prop("checked") ? 1 : 0;
        const isThereAQuantity = $("#globalIsThereAQuantity").prop("checked")
            ? 1
            : 0;
        const dateFrom = $("#globalDateFrom").val();
        const dateTo = $("#globalDateTo").val();

        $(".price-form-card").each(function () {
            const priceName = $(this).find(".price-name").val().trim();
            const priceType = $(this).find(".price-type").val();
            const priceValue = $(this).find(".price-value").val().trim();

            if (!priceName && !priceType && !priceValue) {
                return true;
            }

            if (
                !priceName ||
                !priceType ||
                !priceValue ||
                isNaN(priceValue) ||
                parseFloat(priceValue) <= 0
            ) {
                valid = false;
                return false;
            }

            newPrices.push({
                name: priceName,
                price_type: priceType,
                value: parseFloat(priceValue),
                is_based_on_days: isBasedOnDays,
                is_there_a_quantity: isThereAQuantity,
                date_from: dateFrom,
                date_to: dateTo,
            });
        });

        if (!valid) {
            alert("Please ensure all prices have valid inputs.");
            return;
        }

        if (newPrices.length === 0) {
            alert("Please add at least one price.");
            return;
        }

        // Push the new prices to the global prices array
        prices.push(...newPrices);
        updateUI();
        resetModal();
        $addPriceModal.modal("hide"); // Close the modal
    }

    // Update the price list UI after adding prices
    function updateUI() {
        renderPriceList();
        updateHiddenPrices();
    }

    // Render the list of prices on the page
    function renderPriceList() {
        $priceCardsContainer.empty();
        if (prices.length === 0) {
            $("#noPricesMessage").show();
            $("#priceContainer").hide();
        } else {
            $("#noPricesMessage").hide();
            $("#priceContainer").show();
        }
        prices.forEach((price, index) => {
            $priceCardsContainer.append(createPriceCard(price, index));
            if (typeof window.updatePriceFieldVisibility === "function") {
                window.updatePriceFieldVisibility($("#rentalType").val());
            }
        });
    }

    function createPriceCard(price, index) {
        const priceValue = isNaN(parseFloat(price.value))
            ? 0
            : parseFloat(price.value);
        return `
        <div class="card p-3 mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4>${price.name}</h4>
                    <p>Type: <span class="badge bg-success">${
                        price.price_type
                    }</span></p>
                    <p>Price: PHP ${priceValue.toFixed(2)}</p>
                    <p>Based on Days?: <span class="badge ${
                        price.is_based_on_days ? "bg-success" : "bg-danger"
                    }">${price.is_based_on_days ? "Yes" : "No"}</span></p>
                    <p>Quantity?: <span class="badge ${
                        price.is_there_a_quantity ? "bg-success" : "bg-danger"
                    }">${price.is_there_a_quantity ? "Yes" : "No"}</span></p>
                    <p>Date: ${
                        price.date_from || "N/A"
                    } to ${price.date_to || "N/A"}</p>
                </div>
                <div class="d-flex">
                    <button type="button" class="btn btn-lg btn-outline-warning me-2 edit-price" data-index="${index}">
                        <i class="fa-solid fa-pen"></i> Edit
                    </button>
                    <button type="button" class="btn btn-lg btn-outline-danger delete-price" data-index="${index}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    // Handle deleting a price
    $(document).on("click", ".delete-price", function () {
        const index = $(this).data("index");
        prices.splice(index, 1);
        updateUI();
    });

    // Handle editing a price
    $(document).on("click", ".edit-price", function () {
        const index = $(this).data("index");
        const price = prices[index];

        // Change to edit mode
        editMode = true;
        editIndex = index;

        // Clear existing forms
        $priceFormContainer.empty();

        // Create and populate a form with the price data
        $priceFormContainer.append(createPriceFormCard());
        if (typeof window.updatePriceFieldVisibility === "function") {
            window.updatePriceFieldVisibility($("#rentalType").val());
        }
        const $form = $priceFormContainer.find(".price-form-card:last");

        $form.find(".price-name").val(price.name);
        $form.find(".price-type").val(price.price_type);
        $form.find(".price-value").val(price.value);

        // Set global settings
        $("#globalIsBasedOnDays").prop("checked", price.is_based_on_days === 1);
        $("#globalIsThereAQuantity").prop(
            "checked",
            price.is_there_a_quantity === 1
        );
        $("#globalDateFrom").val(price.date_from);
        $("#globalDateTo").val(price.date_to);

        // Show/hide date fields based on is_based_on_days
        if (price.is_based_on_days === 1) {
            $(".date-fields").show();
        }

        // Update the save button label to indicate editing
        $("#saveMultiplePricesBtn").text("Update Price");

        // Add title to modal to indicate editing
        $("#addPriceLabel").text("Edit Price");

        // Open the modal for editing
        $addPriceModal.modal("show");
    });

    // Update the existing price
    function updatePrice(index) {
        if (index >= 0 && index < prices.length) {
            const $form = $priceFormContainer.find(".price-form-card:first");

            const priceName = $form.find(".price-name").val().trim();
            const priceType = $form.find(".price-type").val();
            const priceValue = $form.find(".price-value").val().trim();
            const isBasedOnDays = $("#globalIsBasedOnDays").prop("checked")
                ? 1
                : 0;
            const isThereAQuantity = $("#globalIsThereAQuantity").prop(
                "checked"
            )
                ? 1
                : 0;
            const dateFrom = $("#globalDateFrom").val();
            const dateTo = $("#globalDateTo").val();

            // Validate inputs
            if (
                !priceName ||
                !priceType ||
                !priceValue ||
                isNaN(priceValue) ||
                parseFloat(priceValue) <= 0
            ) {
                alert("Please ensure all fields are filled correctly.");
                return;
            }

            // Update the price object
            prices[index] = {
                name: priceName,
                price_type: priceType,
                value: parseFloat(priceValue),
                is_based_on_days: isBasedOnDays,
                is_there_a_quantity: isThereAQuantity,
                date_from: dateFrom,
                date_to: dateTo,
            };

            updateUI();
            resetModal();
            $addPriceModal.modal("hide");
        }
    }

    // Reset modal state
    function resetModal() {
        $priceFormContainer.empty();
        $priceFormContainer.append(createPriceFormCard());
        if (typeof window.updatePriceFieldVisibility === "function") {
            window.updatePriceFieldVisibility($("#rentalType").val());
        }
        editMode = false;
        editIndex = -1;
        $("#saveMultiplePricesBtn").text("Save All");
        $("#addPriceLabel").text("Add Price");

        // Reset global settings
        $("#globalIsBasedOnDays").prop("checked", false);
        $("#globalIsThereAQuantity").prop("checked", false);
        $("#globalDateFrom").val("");
        $("#globalDateTo").val("");
        $(".date-fields").hide();
    }

    // Update hidden prices for form submission
    function updateHiddenPrices() {
        $hiddenPrices.empty();
        prices.forEach((price, index) => {
            $hiddenPrices.append(
                createHiddenInput(`prices[${index}][name]`, price.name)
            );
            $hiddenPrices.append(
                createHiddenInput(
                    `prices[${index}][price_type]`,
                    price.price_type
                )
            );
            $hiddenPrices.append(
                createHiddenInput(`prices[${index}][value]`, price.value)
            );
            $hiddenPrices.append(
                createHiddenInput(
                    `prices[${index}][is_based_on_days]`,
                    price.is_based_on_days
                )
            );
            $hiddenPrices.append(
                createHiddenInput(
                    `prices[${index}][is_there_a_quantity]`,
                    price.is_there_a_quantity
                )
            );
            $hiddenPrices.append(
                createHiddenInput(
                    `prices[${index}][date_from]`,
                    price.date_from || ""
                )
            );
            $hiddenPrices.append(
                createHiddenInput(
                    `prices[${index}][date_to]`,
                    price.date_to || ""
                )
            );
        });
    }

    function createHiddenInput(name, value) {
        return `<input type="hidden" name="${name}" value="${value}">`;
    }

    $addPriceModal.on("hidden.bs.modal", function () {
        resetModal();
    });

    updateUI();
});
