// resources/views/admin/facilities/scripts/pricing-management.blade.php
function setupPricingManagement() {
    // Global price settings handlers
    $("#isBasedOnDaysGlobal").on("change", function () {
        const isChecked = $(this).is(":checked");
        globalPriceSettings.isBasedOnDays = isChecked;

        if (isChecked) {
            $("#dateFieldsContainerGlobal").fadeIn(200);
            let today = new Date().toISOString().split("T")[0];
            $("#date_from_global, #date_to_global").attr("min", today);

            if (globalPriceSettings.dateFrom) {
                $("#date_from_global").val(globalPriceSettings.dateFrom);
            }
            if (globalPriceSettings.dateTo) {
                $("#date_to_global").val(globalPriceSettings.dateTo);
            }
        } else {
            $("#dateFieldsContainerGlobal").fadeOut(200);
            globalPriceSettings.dateFrom = "";
            globalPriceSettings.dateTo = "";
            $("#date_from_global, #date_to_global").val("");

            prices.forEach((price) => {
                price.dateFrom = null;
                price.dateTo = null;
            });
        }

        prices.forEach((price) => {
            price.isBasedOnDays = isChecked ? "1" : "0";
        });

        renderPriceList();
    });

    $("#isThereAQuantityGlobal").on("change", function () {
        const isChecked = $(this).is(":checked");
        globalPriceSettings.isThereAQuantity = isChecked;

        prices.forEach((price) => {
            price.isThereAQuantity = isChecked ? "1" : "0";
        });

        renderPriceList();
    });

    $("#date_from_global").on("change", function () {
        let selectedDate = new Date($(this).val());
        selectedDate.setDate(selectedDate.getDate() + 1);
        let nextDay = selectedDate.toISOString().split("T")[0];

        $("#date_to_global").val(nextDay);
        $("#date_to_global").attr("min", nextDay);

        globalPriceSettings.dateFrom = $(this).val();
        globalPriceSettings.dateTo = nextDay;

        prices.forEach((price) => {
            if (price.isBasedOnDays === "1") {
                price.dateFrom = globalPriceSettings.dateFrom;
                price.dateTo = globalPriceSettings.dateTo;
            }
        });

        renderPriceList();
    });

    $("#date_to_global").on("change", function () {
        globalPriceSettings.dateTo = $(this).val();

        prices.forEach((price) => {
            if (price.isBasedOnDays === "1") {
                price.dateTo = globalPriceSettings.dateTo;
            }
        });

        renderPriceList();
    });

    // Modal handlers
    $("#addPrice").on("hidden.bs.modal", function () {
        priceEditMode = false;
        priceEditIndex = -1;
        resetPriceModal();
    });

    $(document).on("click", '[data-bs-target="#addPrice"]', function () {
        priceEditMode = false;
        priceEditIndex = -1;
        resetPriceModal();
    });

    // Price form handlers
    $("#addMultiplePricesRowBtn")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();
            if (!priceEditMode) {
                let newPriceForm = $(
                    "#priceFormTemplate .price-form-card"
                ).clone();
                newPriceForm.find("input, select").val("");
                $("#priceFormContainer").append(newPriceForm);
            }
        });

    $(document).on("click", ".removePriceBtn", function () {
        if ($(".price-form-card").length > 1 && !priceEditMode) {
            $(this).closest(".price-form-card").remove();
        } else {
            $(this).closest(".price-form-card").find("input, select").val("");
        }
    });

    $("#saveMultiplePricesBtn")
        .off("click")
        .on("click", function () {
            if (priceEditMode) {
                updateSinglePrice(priceEditIndex);
            } else {
                saveAllPrices();
            }
        });

    // Edit and delete price handlers
    $(document).on("click", ".edit-price", handleEditPrice);
    $(document).on("click", ".delete-price", handleDeletePrice);
}

function renderPriceList() {
    const container = $("#priceContainer").empty();
    if (prices.length === 0) {
        $("#noPricesMessage").show();
        $("#priceTypeContainer").hide();
        return;
    }
    $("#noPricesMessage").hide();
    $("#priceTypeContainer").show();

    prices.forEach((price, index) => {
        let badgeType =
            price.priceType === "individual"
                ? "bg-primary text-white"
                : price.priceType === "whole"
                ? "bg-warning text-white"
                : "";

        const card = $(`
            <div class="card p-3 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4>${price.priceName}</h4>
                            <p><span class="badge ${badgeType}">${
            price.priceType.charAt(0).toUpperCase() + price.priceType.slice(1)
        }</span></p>
                        </div>
                        <p class="fw-bold h4">₱ ${price.priceValue}.00</p>
                        <p>Is Based on Days?: <span class="badge ${
                            price.isBasedOnDays == "1"
                                ? "bg-success"
                                : "bg-secondary"
                        }">${
            price.isBasedOnDays == "1" ? "Yes" : "No"
        }</span></p>
                        <p>Is There a Quantity?: <span class="badge ${
                            price.isThereAQuantity == "1"
                                ? "bg-success"
                                : "bg-secondary"
                        }">${
            price.isThereAQuantity == "1" ? "Yes" : "No"
        }</span></p>
                        ${
                            price.dateFrom && price.isBasedOnDays == "1"
                                ? `<p><i class="fa-solid fa-calendar-alt me-2 text-info"></i> Date From: <span class="">${price.dateFrom}</span></p>`
                                : ""
                        }
                        ${
                            price.dateTo && price.isBasedOnDays == "1"
                                ? `<p><i class="fa-solid fa-calendar-check me-2 text-info"></i> Date To: <span class="">${price.dateTo}</span></p>`
                                : ""
                        }
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
            </div>
        `);
        container.append(card);
    });
    $("#pricesJson").val(JSON.stringify(prices));
}

function handleEditPrice() {
    let index = $(this).data("index");
    let price = prices[index];
    priceEditMode = true;
    priceEditIndex = index;

    $("#priceFormContainer").empty();
    let $form = $("#priceFormTemplate .price-form-card").clone();

    $form.find(".removePriceBtn").parent().remove();

    $form.find(".price-name").val(price.priceName);
    $form.find(".price-value").val(price.priceValue);
    $form.find(".price-type").val(price.priceType);
    $("#priceFormContainer").append($form);
    $("#isBasedOnDaysGlobal").prop("checked", price.isBasedOnDays == "1");
    $("#isThereAQuantityGlobal").prop("checked", price.isThereAQuantity == "1");

    if (price.isBasedOnDays == "1") {
        $("#dateFieldsContainerGlobal").show();
        $("#date_from_global").val(price.dateFrom || "");
        $("#date_to_global").val(price.dateTo || "");
        globalPriceSettings.dateFrom = price.dateFrom || "";
        globalPriceSettings.dateTo = price.dateTo || "";
    } else {
        $("#dateFieldsContainerGlobal").hide();
        $("#date_from_global, #date_to_global").val("");
    }

    globalPriceSettings.isBasedOnDays = price.isBasedOnDays == "1";
    globalPriceSettings.isThereAQuantity = price.isThereAQuantity == "1";

    $("#addPriceLabel").text("Edit Price");
    $("#saveMultiplePricesBtn").text("Update Price");
    $("#addPrice").modal("show");
}

function handleDeletePrice() {
    if (confirm("Are you sure you want to delete this price?")) {
        const index = $(this).data("index");
        prices.splice(index, 1);
        renderPriceList();
    }
}

function resetPriceModal() {
    $("#priceFormContainer").empty();
    let $form = $("#priceFormTemplate .price-form-card").clone();
    $form.find("input, select").val("");
    $("#priceFormContainer").append($form);

    $("#addPriceLabel").text("Add Price");
    $("#saveMultiplePricesBtn").text("Save All");
}

function saveAllPrices() {
    let valid = true;
    let newPrices = [];
    let isBasedOnDays = $("#isBasedOnDaysGlobal").is(":checked") ? "1" : "0";
    let isThereAQuantity = $("#isThereAQuantityGlobal").is(":checked")
        ? "1"
        : "0";
    let dateFrom = $("#date_from_global").val();
    let dateTo = $("#date_to_global").val();

    $("#priceFormContainer .price-form-card").each(function () {
        const priceName = $(this).find(".price-name").val();
        const priceValue = $(this).find(".price-value").val();
        const priceType = $(this).find(".price-type").val();

        if (!priceName || !priceValue || !priceType) {
            valid = false;
            return false;
        }

        newPrices.push({
            priceName,
            priceValue,
            priceType,
            isBasedOnDays,
            isThereAQuantity,
            dateFrom: isBasedOnDays === "1" && dateFrom ? dateFrom : null,
            dateTo: isBasedOnDays === "1" && dateTo ? dateTo : null,
        });
    });

    if (!valid) {
        alert("Please fill all required fields for all prices.");
        return;
    }

    prices.push(...newPrices);
    renderPriceList();
    resetPriceModal();
    $("#addPrice").modal("hide");
}

function updateSinglePrice(index) {
    let $form = $("#priceFormContainer .price-form-card").first();
    const priceName = $form.find(".price-name").val();
    const priceValue = $form.find(".price-value").val();
    const priceType = $form.find(".price-type").val();
    let isBasedOnDays = $("#isBasedOnDaysGlobal").is(":checked") ? "1" : "0";
    let isThereAQuantity = $("#isThereAQuantityGlobal").is(":checked")
        ? "1"
        : "0";
    let dateFrom = $("#date_from_global").val();
    let dateTo = $("#date_to_global").val();

    if (!priceName || !priceValue || !priceType) {
        alert("Please fill all required fields.");
        return;
    }

    prices[index] = {
        priceName,
        priceValue,
        priceType,
        isBasedOnDays,
        isThereAQuantity,
        dateFrom: isBasedOnDays === "1" && dateFrom ? dateFrom : null,
        dateTo: isBasedOnDays === "1" && dateTo ? dateTo : null,
    };

    renderPriceList();
    resetPriceModal();
    $("#addPrice").modal("hide");
}
