// export let prices = [];

const $priceList = $("#priceList");
const $hiddenPrices = $("#hiddenPrices");
const $addPriceModal = $("#addPrice");

// Handle Save Price Changes (Add Price)
$("#savePriceChanges").on("click", function (event) {
    event.preventDefault();
    savePrice();
});

export function savePrice() {
    const name = $("#priceName").val().trim();
    const priceType = $("#priceTypeSelect").val();
    const value = $("#value").val().trim();
    const isBasedOnDays = $("#isBasedOnDays").prop("checked") ? 1 : 0;
    const isThereAQuantity = $("#isThereAQuantity").prop("checked") ? 1 : 0;

    if (!name || !priceType || !value) {
        alert("Please fill in all fields.");
        return;
    }

    if (isNaN(value) || parseFloat(value) <= 0) {
        alert("Price must be a valid positive number.");
        return;
    }

    prices.push({
        name,
        price_type: priceType,
        value: parseFloat(value),
        is_based_on_days: isBasedOnDays,
        is_there_a_quantity: isThereAQuantity,
    });

    updatePriceUI();
}

function renderPriceList() {
    $priceList.empty();
    prices.forEach((price, index) => {
        $priceList.append(createPriceCard(price, index));
    });
}

function createPriceCard(price, index) {
    return `
            <div class="card p-3 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4>${price.name}</h4>
                        <p>Type: <span class="badge bg-success">${price.price_type}</span></p>
                        <p>Price: PHP ${price.value}</p>
                        <p>Is Based on Days?: 
                            <span class="badge ${price.is_based_on_days ? "bg-success" : "bg-danger"}">
                                ${price.is_based_on_days ? "Yes" : "No"}
                            </span>
                        </p>
                        <p>Is There A Quantity?: 
                            <span class="badge ${price.is_there_a_quantity ? "bg-success" : "bg-danger"}">
                                ${price.is_there_a_quantity ? "Yes" : "No"}
                            </span>
                        </p>
                    </div>
                    <button class="btn btn-lg btn-outline-danger delete-price" data-index="${index}">
                        <i class="icon-trash"></i>
                    </button>
                </div>
            </div>`;
}

function updatePriceUI() {
    renderPriceList();
    updateHiddenPrices();
    $addPriceModal.modal("hide");
    clearPriceForm();
}

function clearPriceForm() {
    $("#priceName, #value").val("");
    $("#priceTypeSelect").val("");
    $("#isBasedOnDays, #isThereAQuantity").prop("checked", false);
}

// Delete price (using event delegation)
$priceList.on("click", ".delete-price", function () {
    const index = $(this).data("index");
    prices.splice(index, 1);
    updatePriceUI();
});

function updateHiddenPrices() {
    $hiddenPrices.empty();
    prices.forEach((price, index) => {
        $hiddenPrices.append(createHiddenInput(`prices[${index}][name]`, price.name));
        $hiddenPrices.append(createHiddenInput(`prices[${index}][price_type]`, price.price_type));
        $hiddenPrices.append(createHiddenInput(`prices[${index}][value]`, price.value));
        $hiddenPrices.append(createHiddenInput(`prices[${index}][is_based_on_days]`, price.is_based_on_days));
        $hiddenPrices.append(createHiddenInput(`prices[${index}][is_there_a_quantity]`, price.is_there_a_quantity));
    });
}