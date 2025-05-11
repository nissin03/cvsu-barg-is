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

    $pIndividual.attr("hidden", true).prop("disabled", true);
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
