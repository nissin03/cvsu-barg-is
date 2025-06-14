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
    const spinner = submitBtn.find(".spinner-border");
    const btnText = submitBtn.find(".btn-text");
    submitBtn.prop("disabled", false);
    spinner.addClass("d-none");
    btnText.text("Create Facility");
}

// Function to validate file uploads
function validateFileUploads() {
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
    } else if (galleryFiles.length > 3) {
        showAlert("You can only upload up to 3 gallery images", "danger");
        isValid = false;
    }

    return isValid;
}

// Function to get required fields based on facility type
function getRequiredFields() {
    const type = $("#rentalType").val();
    let fields = [
        "name",
        "facility_type",
        "description",
        "rules_and_regulations",
    ];

    if (type === "whole_place") {
        fields.push("whole_capacity");
    } else if (type === "individual") {
        fields.push("room_name", "capacity");
    } else if (type === "both") {
        // Check if rooms exist
        const hasRooms = rooms && rooms.length > 0;
        if (!hasRooms) {
            fields.push("whole_capacity");
        } else {
            fields.push("room_name", "capacity");
        }
    }
    return fields;
}

// Function to validate form fields
function isFormValid() {
    const form = $("#facilityForm");
    const requiredFields = getRequiredFields();

    for (let field of requiredFields) {
        if (field === "room_name" || field === "capacity") {
            const inputs = form.find(
                `[name^="facility_attributes"][name$="[${field}]"]`
            );
            if (
                !inputs.length ||
                inputs.toArray().some((input) => !$(input).val().trim())
            ) {
                return false;
            }
        } else {
            const input = form.find(`[name="${field}"]`);
            if (!input.length || !input.val().trim()) {
                return false;
            }
        }
    }
    return true;
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

// Handle facility type changes
$("#rentalType").on("change", function () {
    const type = $(this).val();
    const wholeCapacityField = $("#roomCapacityWhole");

    if (type === "both") {
        // Enable whole capacity field initially
        wholeCapacityField.prop("disabled", false);

        // Check if rooms exist and disable whole capacity if they do
        if (rooms && rooms.length > 0) {
            wholeCapacityField.prop("disabled", true);
            wholeCapacityField.val("");
        }
    } else {
        wholeCapacityField.prop("disabled", type !== "whole_place");
    }

    resetSubmitButton();
    clearValidationErrors();
});

// Form submission handler
$("#facilityForm").on("submit", function (event) {
    event.preventDefault();

    // Clear any existing error messages
    clearValidationErrors();

    // Validate file uploads
    if (!validateFileUploads()) {
        return;
    }

    // Basic form validation
    if (!isFormValid()) {
        showAlert("Please fill in all required fields.", "danger");
        return;
    }

    // Get the form data
    const formData = new FormData(this);
    const facilityType = $("#rentalType").val();

    // Handle facility type specific data
    if (facilityType === "both" && rooms && rooms.length > 0) {
        // Remove whole_capacity if rooms exist
        formData.delete("whole_capacity");

        // Add room data
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
    const spinner = submitBtn.find(".spinner-border");
    const btnText = submitBtn.find(".btn-text");
    submitBtn.prop("disabled", true);
    spinner.removeClass("d-none");
    btnText.text("Submitting...");

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
