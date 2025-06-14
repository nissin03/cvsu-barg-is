const $rentalType = $("#rentalType");
const $roomBox = $("#roomBox");
const $hideRoomBox = $("#hideRoomBox");
const $dormitoryRooms = $("#dormitoryRooms");
const $quantityChecked = $("#QuantityChecked");
const $pIndividual = $("#pIndividual");
const $priceBox = $("#priceBox");
const $addRoomButtons = $("#dormitoryRooms .d-flex.gap-2 button");

// Add message container after the h4 title
if ($("#roomButtonsMessage").length === 0) {
    $("#dormitoryRooms #container-error-message").after(
        '<div id="roomButtonsMessage" class="alert alert-info mb-4" style="display: none;"><i class="bi bi-exclamation-circle me-2"></i>Adding rooms is not applicable when whole capacity is set.</div>'
    );
}

// Add click handler for disabled buttons
$addRoomButtons.on("click", function (e) {
    if ($(this).prop("disabled")) {
        e.preventDefault();
        e.stopPropagation();

        // Create and show toast
        const toast = `
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Cannot add rooms when whole capacity is set.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Add toast to container if it doesn't exist
        if ($("#toastContainer").length === 0) {
            $("body").append(
                '<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>'
            );
        }

        // Add and show the toast
        const $toast = $(toast);
        $("#toastContainer").append($toast);
        const bsToast = new bootstrap.Toast($toast[0], {
            autohide: true,
            delay: 3000,
        });
        bsToast.show();

        // Remove toast from DOM after it's hidden
        $toast.on("hidden.bs.toast", function () {
            $(this).remove();
        });
    }
});

$rentalType.on("change", function () {
    handleRentalTypeChange($(this).val());
});

function handleRentalTypeChange(rentalType) {
    console.log("Facility Type:", rentalType);

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
            $addRoomButtons.prop("disabled", false);
            $("#roomButtonsMessage").hide();
            break;

        case "whole_place":
            $roomBox.show();
            $hideRoomBox.show();
            $addRoomButtons.prop("disabled", true);
            $("#roomButtonsMessage").hide();
            break;

        case "both":
            $roomBox.show();
            $hideRoomBox.show();
            $dormitoryRooms.show();
            $quantityChecked.show();
            $pIndividual.removeAttr("hidden").prop("disabled", false);
            // Check whole_capacity value to determine button state
            const wholeCapacity = $("#roomCapacityWhole").val();
            if (wholeCapacity && wholeCapacity.trim() !== "") {
                $addRoomButtons.prop("disabled", true);
                $("#roomButtonsMessage").show();
                $("#noRoomsMessage").hide();
            } else {
                $addRoomButtons.prop("disabled", false);
                $("#roomButtonsMessage").hide();
            }
            break;
    }
}

// Listen for changes to whole_capacity field
$("#roomCapacityWhole").on("input", function () {
    const rentalType = $("#rentalType").val();
    if (rentalType === "both") {
        const hasValue = $(this).val().trim() !== "";
        $addRoomButtons.prop("disabled", hasValue);
        $("#roomButtonsMessage").toggle(hasValue);
        $("#noRoomsMessage").toggle(!hasValue);

        // Update price modal if it's open
        if ($("#addPrice").is(":visible")) {
            window.updatePriceFieldVisibility(rentalType);
        }
    }
});

$rentalType.trigger("change");
window.updatePriceFieldVisibility = function (rentalType) {
    const $globalSettings = $("#globalPriceSettings");
    const $globalIsBasedOnDays = $("#globalIsBasedOnDays");
    const $globalIsThereAQuantity = $("#globalIsThereAQuantity");
    const $globalDateFields = $(".date-fields");

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

            // Hide and reset global checkboxes
            $globalSettings.hide();
            $globalIsBasedOnDays.prop("checked", false);
            $globalIsThereAQuantity.prop("checked", false);
            $globalDateFields.hide();
        } else if (rentalType === "both") {
            $checksRow.show();
            $priceTypeSelect.find("option[value='individual']").show();

            const $wholeCapacity = $("#roomCapacityWhole");
            if ($wholeCapacity.length && $wholeCapacity.val()) {
                // Disable is_based_on_days checkbox when whole_capacity has value
                $globalIsBasedOnDays
                    .prop("disabled", true)
                    .prop("checked", false);
                $globalIsBasedOnDays.closest(".d-flex").hide();
                $globalDateFields.hide();
            } else {
                $globalIsBasedOnDays.prop("disabled", false);
                $globalIsBasedOnDays.closest(".d-flex").show();
            }
        } else {
            $checksRow.show();
            $checksRow.find("input[type='checkbox']").prop("disabled", false);
            $priceTypeSelect.find("option[value='individual']").show();

            // Show global checkboxes
            $globalSettings.show();
            $globalIsBasedOnDays.prop("disabled", false);
            $globalIsBasedOnDays.closest(".d-flex").show();
        }
    });
};

$rentalType.on("change", function () {
    const selectedType = $(this).val();
    handleRentalTypeChange(selectedType);
    window.updatePriceFieldVisibility(selectedType);
});
