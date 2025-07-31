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
    const $selectionBothType = $("#selectionBothType");
    function initializeForm() {
        const selectedType = $rentalType.val();

        if (!selectedType) {
            $roomBox.hide();
            $priceBox.hide();
            $selectionBothType.hide();
            return;
        }

        handleFacilityTypeChange(selectedType);

        // If "both", re-trigger the mode selection (whole/room)
        if (selectedType === "both") {
            const checkedMode = $(
                'input[name="facility_selection_both"]:checked'
            ).val();
            if (checkedMode === "whole") {
                $("#selectionContent").hide();
                handleBothTypeWholeCapacity();
            } else if (checkedMode === "room") {
                $("#selectionContent").hide();
                handleBothTypeRooms();
            } else {
                $("#selectionContent").show();
            }
        }
    }
    console.log("Rental type on load:", $rentalType.val());
    console.log(
        "Checked facility_selection_both:",
        $('input[name="facility_selection_both"]:checked').val()
    );

    $rentalType.on("change", function () {
        const selectedType = $(this).val();
        handleFacilityTypeChange(selectedType);
    });

    function handleFacilityTypeChange(facilityType) {
        console.log("Facility Type changed to:", facilityType);

        if (!facilityType) {
            $roomBox.hide();
            $priceBox.hide();
            $selectionBothType.hide();
            $("#selectionContent").hide();
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

        $("#selectionContent").hide();
        $selectionBothType.hide();

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

        $("#selectionContent").hide();
        $selectionBothType.hide();

        $(".price-type").each(function () {
            if ($(this).val() === "individual") {
                $(this).val("");
            }
        });
    }

    function handleBothType() {
        $selectionBothType.show();
        $hideRoomBox.hide();
        $dormitoryRooms.hide();

        const oldMode = $(
            'input[name="facility_selection_both"]:checked'
        ).val();
        if (oldMode === "whole") {
            $("#selectionContent").hide();
            handleBothTypeWholeCapacity();
        } else if (oldMode === "room") {
            $("#selectionContent").hide();
            handleBothTypeRooms();
        } else {
            $("#selectionContent").show();
        }

        $('input[name="facility_selection_both"]').on("change", function () {
            $("#selectionContent").hide();

            const selected = $(this).val();
            if (selected === "whole") {
                handleBothTypeWholeCapacity();
            } else {
                handleBothTypeRooms();
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
