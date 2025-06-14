$("#facilityForm").on("submit", function (event) {
    event.preventDefault();

    var formData = new FormData(this);
    const facilityType = $("#rentalType").val(); // Get the facility type

    // Add price data to formData
    prices.forEach((price, index) => {
        formData.append(`prices[${index}][name]`, price.name);
        formData.append(`prices[${index}][price_type]`, price.price_type);
        formData.append(`prices[${index}][value]`, price.value);
        formData.append(
            `prices[${index}][is_based_on_days]`,
            price.is_based_on_days
        );
        formData.append(
            `prices[${index}][is_there_a_quantity]`,
            price.is_there_a_quantity
        );
    });

    // Check for facility type and append appropriate data
    if (facilityType === "whole_place") {
        const wholeCapacity = $("#roomCapacityWhole").val();
        formData.append("whole_capacity", wholeCapacity);
    } else {
        // Append rooms data for individual or both facility types
        rooms.forEach((room, index) => {
            formData.append(
                `facility_attributes[${index}][room_name]`,
                room.room_name
            );
            formData.append(
                `facility_attributes[${index}][capacity]`,
                room.capacity
            );
            formData.append(
                `facility_attributes[${index}][sex_restriction]`,
                room.sex_restriction
            );
        });
    }

    // Debugging: Log formData before submission
    console.log("Form Data Before Submission:");
    for (var pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
    }

    // Make AJAX request to submit the form data
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
            if (response.action === "create") {
                showAlert("Facility created successfully!", "success");
            } else if (response.action === "update") {
                showAlert("Facility updated successfully!", "success");
            }
            setTimeout(function () {
                window.location.href = "/admin/facilities";
            }, 2000);
        },
        error: function (xhr) {
            console.log("Error:", xhr);
            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                // Show the main error message if it exists
                if (response.message) {
                    showAlert(response.message, "danger");
                }
                // Display individual field errors
                if (response.errors) {
                    displayValidationErrors(response.errors);
                }
            } else {
                showAlert(
                    "An unexpected error occurred. Please try again.",
                    "danger"
                );
            }
        },
    });

    console.log("Hidden form data", $("#hiddenRooms").html());
});

// Display validation errors in the form
function displayValidationErrors(errors) {
    // Clear any existing error messages
    $(".alert-danger").remove();

    // Create a container for all errors if it doesn't exist
    let errorContainer = $("#alertContainer");
    if (errorContainer.length === 0) {
        errorContainer = $('<div id="alertContainer"></div>');
        $("#facilityForm").prepend(errorContainer);
    }

    // Display each error message
    for (const [key, messages] of Object.entries(errors)) {
        const errorMessage = messages[0]; // Get the first error message
        const alertBox = $("<div>", {
            class: "alert alert-danger alert-dismissible fade show",
            role: "alert",
            text: errorMessage,
        }).append(
            $("<button>", {
                type: "button",
                class: "btn-close",
                "data-bs-dismiss": "alert",
                "aria-label": "Close",
            })
        );
        errorContainer.append(alertBox);
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
