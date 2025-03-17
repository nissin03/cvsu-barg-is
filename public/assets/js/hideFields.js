const $rentalType = $("#rentalType");
const $roomBox = $("#roomBox");
const $hideRoomBox = $("#hideRoomBox");
const $dormitoryRooms = $("#dormitoryRooms");
const $quantityChecked = $("#QuantityChecked");
const $pIndividual = $("#pIndividual");
const $pWhole = $("#pWhole");
// const $roomList = $("#roomList");
// const $hiddenRooms = $("#hiddenRooms");
// const $multipleRoomsTable = $("#multipleRoomsTable tbody");
// const $addRoomModal = $("#addRoom");

$rentalType.on("change", function () {
    handleRentalTypeChange($(this).val());
});

function handleRentalTypeChange(rentalType) {
    console.log("Facility Type:", rentalType);

    // Reset visibility
    $pIndividual.add($pWhole).attr("hidden", true).prop("disabled", true);
    $roomBox.add($hideRoomBox).add($dormitoryRooms).add($quantityChecked).hide();

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
            $pWhole.removeAttr("hidden").prop("disabled", false);
            break;

        case "both":
            $roomBox.show();
            $hideRoomBox.show();
            $dormitoryRooms.show();
            $quantityChecked.show();
            $pIndividual.add($pWhole).removeAttr("hidden").prop("disabled", false);
            break;
    }
}

// Trigger initial change event
$rentalType.trigger("change");