// Function to clear all validation errors
function clearValidationErrors() {
    $(".alert-danger").remove();
    $("#alertContainer").empty();
    $(".is-invalid").removeClass("is-invalid");
    $(".invalid-feedback").remove();
}

// Function to reset submit button state
function resetSubmitButton() {
    const submitBtn = $("#facilitySubmitBtn");
    submitBtn.prop("disabled", false).html("Submit");
}

// Function to validate files before submission
function validateFiles() {
    let isValid = true;
    clearValidationErrors();

    // Validate main image
    const mainImage = $("#myFile")[0].files[0];
    if (!mainImage) {
        showAlert("Please upload a main image", "danger");
        isValid = false;
    }

    // Validate requirements file
    const requirementsFile = $("#requirementsFile")[0].files[0];
    if (!requirementsFile) {
        showAlert("Please upload a requirements file", "danger");
        isValid = false;
    }

    // Validate gallery images
    const galleryFiles = $("#gFile")[0].files;
    if (galleryFiles.length === 0) {
        showAlert("Please upload at least one gallery image", "danger");
        isValid = false;
    }

    return isValid;
}

// Function to display validation errors
function displayValidationErrors(errors) {
    clearValidationErrors();

    // Create a container for all errors if it doesn't exist
    let errorContainer = $("#alertContainer");
    if (errorContainer.length === 0) {
        errorContainer = $('<div id="alertContainer"></div>');
        $("#facilityForm").prepend(errorContainer);
    }

    // Display each error message
    for (const [key, messages] of Object.entries(errors)) {
        const errorMessage = messages[0];

        // Find the input field
        const input = $(`[name="${key}"]`);
        if (input.length) {
            // Add invalid class to the input
            input.addClass("is-invalid");

            // Add or update the error message
            const feedback = input.next(".invalid-feedback");
            if (feedback.length) {
                feedback.text(errorMessage);
            } else {
                input.after(
                    `<div class="invalid-feedback">${errorMessage}</div>`
                );
            }
        }

        // Also show in alert container
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

// Function to show custom alert
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

// Add event listeners to clear validation errors when inputs change
$(document).on("change input", "input, select, textarea", function () {
    $(this).removeClass("is-invalid");
    const feedback = $(this).next(".invalid-feedback");
    if (feedback.length) {
        feedback.text("");
    }
});

// Add event listeners for file inputs
$(document).on("change", 'input[type="file"]', function () {
    $(this).removeClass("is-invalid");
    $(this).next(".invalid-feedback").remove();
    clearValidationErrors();
});

// Form submission handler
$("#facilityForm").on("submit", function (event) {
    event.preventDefault();

    // Clear any existing error messages
    clearValidationErrors();

    // Get the form data
    const formData = new FormData(this);
    const facilityType = $("#rentalType").val();

    // Add price data to formData
    const prices = [];
    $("#hiddenPrices input").each(function () {
        const name = $(this).attr("name");
        const value = $(this).val();
        const matches = name.match(/prices\[(\d+)\]\[(\w+)\]/);

        if (matches) {
            const index = parseInt(matches[1]);
            const field = matches[2];

            if (!prices[index]) {
                prices[index] = {};
            }
            prices[index][field] = value;
        }
    });

    prices.forEach((price, index) => {
        if (price.name && price.price_type && price.value) {
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
            if (price.date_from) {
                formData.append(`prices[${index}][date_from]`, price.date_from);
            }
            if (price.date_to) {
                formData.append(`prices[${index}][date_to]`, price.date_to);
            }
        }
    });

    // Handle facility type specific data
    if (facilityType === "whole_place") {
        const wholeCapacity = $("#roomCapacityWhole").val();
        formData.append("whole_capacity", wholeCapacity);
    } else if (facilityType === "both") {
        const wholeCapacity = $("#roomCapacityWhole").val();
        formData.append("whole_capacity", wholeCapacity);

        // Only append rooms if they exist
        if (Array.isArray(rooms) && rooms.length > 0) {
            rooms.forEach((room, index) => {
                if (room.room_name && room.capacity) {
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
                }
            });
        }
    } else if (facilityType === "individual") {
        rooms.forEach((room, index) => {
            if (room.room_name && room.capacity) {
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
            }
        });
    }

    // Disable submit button and show loading state
    const submitBtn = $("#facilitySubmitBtn");
    submitBtn
        .prop("disabled", true)
        .html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...'
        );

    // Make AJAX request
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
            showAlert("Facility created successfully!", "success");
            setTimeout(function () {
                window.location.href = "/admin/facilities";
            }, 2000);
        },
        error: function (xhr) {
            resetSubmitButton();

            if (xhr.status === 422) {
                // Handle validation errors
                const response = xhr.responseJSON;
                if (response.errors) {
                    displayValidationErrors(response.errors);
                } else if (response.message) {
                    showAlert(response.message, "danger");
                }
            } else {
                showAlert(
                    "An unexpected error occurred. Please try again.",
                    "danger"
                );
            }
        },
    });
});
