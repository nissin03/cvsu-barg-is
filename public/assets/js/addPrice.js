"use strict";

$(document).ready(function () {
    const $addPriceModal = $("#addPrice");
    const $priceCardsContainer = $("#priceCardsContainer");
    const $priceFormContainer = $("#priceFormContainer");
    const $hiddenPrices = $("#hiddenPrices");

    let prices = [];
    let editMode = false;
    let editIndex = -1;

    // Initialize the first price form when the modal is opened
    $("#addPrice").on("show.bs.modal", function () {
        if ($priceFormContainer.children().length === 0) {
            $priceFormContainer.append(createPriceFormCard());
        }
    });

    // Add new price form when the "Add Another Price" button is clicked
    $("#addMultiplePricesRowBtn").on("click", function (e) {
        e.preventDefault();
        $priceFormContainer.append(createPriceFormCard());
    });

    // Dynamically disable past dates for all price forms
    function disablePastDates($form) {
        const today = new Date().toISOString().split("T")[0];
        $form.find(".date-from").attr("min", today);
        $form.find(".date-to").attr("min", today);
    }

    // Ensure that date-to is never earlier than date-from
    $(document).on("change", ".date-from", function () {
        const $dateFrom = $(this);
        const $dateTo = $dateFrom.closest(".price-form-card").find(".date-to");
        if ($dateFrom.val()) {
            $dateTo.val($dateFrom.val());
        }
    });

    // Create the price form card with global checkboxes moved outside individual cards
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
        </div>`;
    }

    // Add global option controls outside of individual price forms
    $("#priceFormContainer").after(`
        <div class="d-flex justify-content-between align-items-center my-4 mx-auto global-options" style="display:none">
            <div class="d-flex align-items-center">
                <input type="checkbox" class="form-check-input global-is-based-on-days" id="globalIsBasedOnDays">
                <label class="form-check-label ms-2 pt-2" for="globalIsBasedOnDays">Is that based on days?</label>
            </div>
            <div class="d-flex align-items-center ms-3">
                <input type="checkbox" class="form-check-input global-is-there-a-quantity" id="globalIsThereAQuantity">
                <label class="form-check-label ms-2 pt-2" for="globalIsThereAQuantity">Is have quantity?</label>
            </div>
        </div>
        <div class="row g-3 mt-2 global-date-fields" id="globalDateFields" style="display: none;">
            <div class="col-md-6">
                <label class="form-label">Date From</label>
                <input type="date" class="form-control global-date-from" id="globalDateFrom">
            </div>
            <div class="col-md-6">
                <label class="form-label">Date To</label>
                <input type="date" class="form-control global-date-to" id="globalDateTo">
            </div>
        </div>
    `);

    // Show global options only when all price forms are filled
    function checkAllFormsFilled() {
        let allFilled = true;

        $(".price-form-card").each(function () {
            const priceName = $(this).find(".price-name").val().trim();
            const priceType = $(this).find(".price-type").val();
            const priceValue = $(this).find(".price-value").val().trim();

            if (!priceName || !priceType || !priceValue) {
                allFilled = false;
                return false; // break the loop
            }
        });

        // Show or hide global options based on all forms being filled
        if (allFilled && $(".price-form-card").length > 0) {
            $(".global-options").show();
        } else {
            $(".global-options").hide();
            $("#globalDateFields").hide();
            $(".global-is-based-on-days").prop("checked", false);
            $(".global-is-there-a-quantity").prop("checked", false);
        }
    }

    // Check if all forms are filled whenever an input changes
    $(document).on(
        "input change",
        ".price-name, .price-value, .price-type",
        function () {
            checkAllFormsFilled();
        }
    );

    // Toggle global date fields when "All prices based on days" is clicked
    $(document).on("change", ".global-is-based-on-days", function () {
        if ($(this).prop("checked")) {
            $("#globalDateFields").show();
            disablePastDates($("#globalDateFields"));
        } else {
            $("#globalDateFields").hide();
        }
    });

    // Update the date-from field on the global date-from
    $(document).on("change", ".global-date-from", function () {
        if ($(this).val()) {
            $(".global-date-to").val($(this).val());
        }
    });

    // Remove price form when the "Remove" button is clicked
    $(document).on("click", ".removePriceBtn", function () {
        const $card = $(this).closest(".price-form-card");
        if ($(".price-form-card").length > 1) {
            $card.remove();
            checkAllFormsFilled();
        } else {
            // Reset the first form card if it is the only one
            $card
                .find("input[type='text'], input[type='number'], select")
                .val("");

            // Hide global options
            $(".global-options").hide();
            $("#globalDateFields").hide();
            $(".global-is-based-on-days").prop("checked", false);
            $(".global-is-there-a-quantity").prop("checked", false);
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

        // Get global settings
        const globalIsBasedOnDays = $(".global-is-based-on-days").prop(
            "checked"
        )
            ? 1
            : 0;
        const globalIsThereAQuantity = $(".global-is-there-a-quantity").prop(
            "checked"
        )
            ? 1
            : 0;
        const globalDateFrom = $("#globalDateFrom").val();
        const globalDateTo = $("#globalDateTo").val();

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
                is_based_on_days: globalIsBasedOnDays,
                is_there_a_quantity: globalIsThereAQuantity,
                date_from: globalIsBasedOnDays ? globalDateFrom : null,
                date_to: globalIsBasedOnDays ? globalDateTo : null,
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
        });
    }

    // Create a price card for each price in the list
    function createPriceCard(price, index) {
        return `
        <div class="card p-3 mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4>${price.name}</h4>
                    <p>Type: <span class="badge bg-success">${
                        price.price_type
                    }</span></p>
                    <p>Price: PHP ${price.value.toFixed(2)}</p>
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

    $(document).ready(function () {
        renderPriceList();
    });

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
        const $form = $priceFormContainer.find(".price-form-card:last");

        $form.find(".price-name").val(price.name);
        $form.find(".price-type").val(price.price_type);
        $form.find(".price-value").val(price.value);

        // Set global options for editing
        $(".global-is-based-on-days").prop(
            "checked",
            price.is_based_on_days === 1
        );
        $(".global-is-there-a-quantity").prop(
            "checked",
            price.is_there_a_quantity === 1
        );
        $("#globalDateFrom").val(price.date_from);
        $("#globalDateTo").val(price.date_to);

        // Show/hide date fields based on is_based_on_days
        if (price.is_based_on_days === 1) {
            $("#globalDateFields").show();
        } else {
            $("#globalDateFields").hide();
        }

        // Show global options for editing
        $(".global-options").show();

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

            // Get global settings
            const globalIsBasedOnDays = $(".global-is-based-on-days").prop(
                "checked"
            )
                ? 1
                : 0;
            const globalIsThereAQuantity = $(
                ".global-is-there-a-quantity"
            ).prop("checked")
                ? 1
                : 0;
            const globalDateFrom = $("#globalDateFrom").val();
            const globalDateTo = $("#globalDateTo").val();

            // Update the price object
            prices[index] = {
                name: priceName,
                price_type: priceType,
                value: parseFloat(priceValue),
                is_based_on_days: globalIsBasedOnDays,
                is_there_a_quantity: globalIsThereAQuantity,
                date_from: globalIsBasedOnDays ? globalDateFrom : null,
                date_to: globalIsBasedOnDays ? globalDateTo : null,
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

        // Reset global options
        $(".global-options").hide();
        $("#globalDateFields").hide();
        $(".global-is-based-on-days").prop("checked", false);
        $(".global-is-there-a-quantity").prop("checked", false);
        $("#globalDateFrom").val("");
        $("#globalDateTo").val("");

        editMode = false;
        editIndex = -1;
        $("#saveMultiplePricesBtn").text("Save All");
        $("#addPriceLabel").text("Add Price");
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
