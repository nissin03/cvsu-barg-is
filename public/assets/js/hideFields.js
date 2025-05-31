const $rentalType = $("#rentalType");
const $roomBox = $("#roomBox");
const $hideRoomBox = $("#hideRoomBox");
const $dormitoryRooms = $("#dormitoryRooms");
const $quantityChecked = $("#QuantityChecked");
const $pIndividual = $("#pIndividual");
const $priceBox = $("#priceBox");

$rentalType.on("change", function () {
    handleRentalTypeChange($(this).val());
});

function handleRentalTypeChange(rentalType) {
    console.log("Facility Type:", rentalType);

    // $pIndividual.attr("hidden", true).prop("disabled", true);
    $pIndividual.hide().prop("disabled", true);

    $roomBox
        .add($hideRoomBox)
        .add($dormitoryRooms)
        .add($quantityChecked)
        .hide();

    if (rentalType) {
        $priceBox.removeAttr("hidden").prop("disabled", false);
    } else {
        $priceBox.attr("hidden", true).prop("disabled", true);
    }

    switch (rentalType) {
        case "individual":
            $roomBox.show();
            $dormitoryRooms.show();
            $quantityChecked.show();
            $pIndividual.removeAttr("hidden").prop("disabled", false);
            break;

        case "whole_place":
            $roomBox.show();
            $hideRoomBox.show();
            break;

        case "both":
            $roomBox.show();
            $hideRoomBox.show();
            $dormitoryRooms.show();
            $quantityChecked.show();
            $pIndividual.removeAttr("hidden").prop("disabled", false);
            break;
    }
}

$rentalType.trigger("change");
window.updatePriceFieldVisibility = function (rentalType) {
    $(".price-form-card").each(function () {
        const $card = $(this);
        const $checksRow = $card.find(".price-checks");
        const $priceTypeSelect = $card.find(".price-type");

        if (rentalType === "whole_place") {
            $checksRow.hide();
            $priceTypeSelect.find("option[value='individual']").hide();
            $checksRow
                .find("input[type='checkbox']")
                .prop("checked", false)
                .prop("disabled", true);
            $card.find(".date-fields").hide();

            if ($priceTypeSelect.val() === "individual") {
                $priceTypeSelect.val("");
            }
        } else if (rentalType === "both") {
            $checksRow.show();
            $priceTypeSelect.find("option[value='individual']").show();

            const $wholeCapacity = $("#wholeCapacity");
            if ($wholeCapacity.length && $wholeCapacity.val()) {
                $card.find(".is-based-on-days").closest(".d-flex").hide();
            }
        } else {
            $checksRow.show();
            $checksRow.find("input[type='checkbox']").prop("disabled", false);
            $priceTypeSelect.find("option[value='individual']").show();
        }
    });
};

$rentalType.on("change", function () {
    const selectedType = $(this).val();
    handleRentalTypeChange(selectedType);
    window.updatePriceFieldVisibility(selectedType);
});
