$(function () {
    const $rentalType = $("#rentalType");
    const $roomBox = $("#roomBox");
    const $hideRoomBox = $("#hideRoomBox");
    const $dormitoryRooms = $("#dormitoryRooms");
    const $priceBox = $("#priceBox");
    const $isBasedOnDays = $("#isBasedOnDaysContainer");
    const $isThereAQuantity = $("#isThereAQuantityContainer");
    const $priceTypeIndividual = $(".price-type option[value='individual']");
    const $priceTypeWhole = $(".price-type option[value='whole']");

    function initializeForm() {
        if (!$rentalType.val()) {
            $roomBox.hide();
            $priceBox.hide();
        }
    }

    $rentalType.on("change", function () {
        const selectedType = $(this).val();
        handleFacilityTypeChange(selectedType);
    });

    function handleFacilityTypeChange(facilityType) {
        console.log("Facility Type changed to:", facilityType);

        if (!facilityType) {
            $roomBox.hide();
            $priceBox.hide();
            return;
        }

        // Show boxes
        $roomBox.show();
        $priceBox.show();

        switch (facilityType) {
            case "individual":
                handleIndividualType();
                break;
            case "whole_place":
                handleWholePlaceType();
                break;
            case "both":
                handleBothType();
                break;
        }
    }

    function handleIndividualType() {
        $hideRoomBox.hide();
        $dormitoryRooms.show();

        $isBasedOnDays.show();
        $isThereAQuantity.hide();

        $priceTypeIndividual.show();
        $priceTypeWhole.hide();

        $(".price-type").each(function () {
            if ($(this).val() === "whole") {
                $(this).val("");
            }
        });
    }

    function handleWholePlaceType() {
        $hideRoomBox.show();
        $dormitoryRooms.hide();

        $isBasedOnDays.show();
        $isThereAQuantity.hide();

        $priceTypeIndividual.hide();
        $priceTypeWhole.show();

        $(".price-type").each(function () {
            if ($(this).val() === "individual") {
                $(this).val("");
            }
        });
    }

    function handleBothType() {
        Swal.fire({
            title: "Choose Facility Configuration",
            text: "How would you like to configure this facility?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Add Room(s)",
            cancelButtonText: "Add Whole Capacity",
            reverseButtons: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            allowEnterKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                handleBothTypeRooms();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                handleBothTypeWholeCapacity();
            } else {
                resetToDefaultState();
            }
        });
    }

    function handleBothTypeRooms() {
        $hideRoomBox.hide();
        $dormitoryRooms.show();

        $isBasedOnDays.show();
        $isThereAQuantity.show();

        $priceTypeIndividual.show();
        $priceTypeWhole.show();
    }

    function handleBothTypeWholeCapacity() {
        $hideRoomBox.show();
        $dormitoryRooms.hide();

        $isBasedOnDays.show();
        $isThereAQuantity.show();

        $priceTypeIndividual.show();
        $priceTypeWhole.show();
    }

    function resetToDefaultState() {
        $rentalType.val("");

        $roomBox.hide();
        $priceBox.hide();

        console.log("Reset to default state - no facility type selected");
    }

    initializeForm();
});
