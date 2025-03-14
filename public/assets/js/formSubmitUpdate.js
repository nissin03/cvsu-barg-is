$("#facilityForm").on("submit", function (event) {
    event.preventDefault();

    var formData = new FormData(this);
    const facilityType = $("#rentalType").val();

    // Ensure prices array is defined
    if (Array.isArray(window.prices)) {
        window.prices.forEach((price, index) => {
            formData.append(`prices[${index}][name]`, price.name);
            formData.append(`prices[${index}][price_type]`, price.price_type);
            formData.append(`prices[${index}][value]`, price.value);
            formData.append(`prices[${index}][is_based_on_days]`, price.is_based_on_days);
            formData.append(`prices[${index}][is_there_a_quantity]`, price.is_there_a_quantity);
        });
    } else {
        console.error("Prices is not an array or is undefined");
    }

    if (facilityType === "whole_place") {
        const wholeCapacity = $("#roomCapacityWhole").val();
        if (wholeCapacity && !isNaN(wholeCapacity) && parseInt(wholeCapacity, 10) > 0) {
            formData.append("whole_capacity", parseInt(wholeCapacity, 10));
        } else {
            alert("Please enter a valid whole capacity.");
            return;
        }
    } else {

        if (Array.isArray(window.rooms) && window.rooms.length > 0) {
            window.rooms.forEach((room, index) => {
                // Ensure capacity is an integer
                const roomCapacity = parseInt(room.capacity, 10);
                if (!isNaN(roomCapacity) && roomCapacity > 0) {
                    formData.append(`facility_attributes[${index}][room_name]`, room.room_name);
                    formData.append(`facility_attributes[${index}][capacity]`, roomCapacity);
                    formData.append(`facility_attributes[${index}][sex_restriction]`, room.sex_restriction);
                }
            });
        } else {
            console.error("Rooms is not an array or is undefined");
        }
    }


    console.log("Form Data Before Submission:");
    for (var pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
    }


    $.ajax({
        url: $(this).attr("action"),
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            console.log("Success response:", response);
            if (response.action === "update") {
                showAlert("Facility updated successfully!", "success");
            }
            setTimeout(function () {
                window.location.href = "/admin/facilities";
            }, 2000);
        },
        error: function (xhr) {
            console.log("Error:", xhr);
            if (xhr.status === 422) {
                displayValidationErrors(xhr.responseJSON.errors);
            } else {
                showAlert("An unexpected error occurred. Please try again.", "danger");
            }
        }
    });

    console.log("Hidden form data", $("#hiddenRooms").html());
});

// Display validation errors in the form
function displayValidationErrors(errors) {
    for (const [key, messages] of Object.entries(errors)) {
        const errorContainer = $(`#${key}Error`);
        if (errorContainer.length) {
            errorContainer.html(messages[0]).show();
        }
    }
}

// Show custom alert
function showAlert(message, type) {
    const alertBox = $("<div>", {
        class: `alert alert-${type} alert-dismissible fade show`,
        role: "alert",
        text: message,
    }).append(
        $("<button>", {
            type: "button",
            class: "btn-close",
            "data-bs-dismiss": "alert",
            "aria-label": "Close",
        })
    );

    $("#alertContainer").html(alertBox);
    alertBox.alert();
}
