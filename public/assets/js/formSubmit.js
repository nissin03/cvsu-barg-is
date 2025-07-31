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

// Function to validate file size (10MB limit)
function validateFileSize(file, fieldName) {
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes

    if (file && file.size > maxSize) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showAlert(
            `${fieldName} file size (${fileSizeMB}MB) exceeds the 10MB limit. Please choose a smaller file.`,
            "danger"
        );
        return false;
    }
    return true;
}

// Function to clear file input and reset UI
function clearFileInput(inputId, previewId, uploadDivId) {
    // Clear the file input
    $(`#${inputId}`).val("");

    // Hide preview and show upload div
    $(`#${previewId}`).hide();
    $(`#${uploadDivId}`).show();

    // Clear preview image source and reset preview state
    $(`#${previewId} img`).attr("src", "");
    $(`#${previewId} .file-name-overlay`).remove();
    $(`#${previewId} .remove-upload`).hide();

    // Clear validation errors for this input
    $(`#${inputId}`).removeClass("is-invalid");
    $(`#${inputId}`).next(".invalid-feedback").remove();
}

// Function to handle file preview with size validation
function handleFilePreview(
    file,
    inputId,
    previewId,
    uploadDivId,
    previewImgId,
    fieldName
) {
    // Validate file size first
    if (!validateFileSize(file, fieldName)) {
        clearFileInput(inputId, previewId, uploadDivId);
        return false;
    }

    // Show preview based on file type
    if (file.type.startsWith("image/")) {
        // For images, show actual preview
        const reader = new FileReader();
        reader.onload = function (e) {
            $(`#${previewImgId}`).attr("src", e.target.result);
            $(`#${uploadDivId}`).hide();
            $(`#${previewId}`).show();
            $(`#${previewId} .remove-upload`).show();
        };
        reader.readAsDataURL(file);
    } else {
        // For documents, show generic preview with filename
        $(`#${previewImgId}`).attr(
            "src",
            "/images/upload/document-preview.png"
        );

        // Remove existing file name overlay and add new one
        $(`#${previewId} .file-name-overlay`).remove();
        $(`#${previewId}`).append(
            $('<p class="file-name-overlay">').text(file.name)
        );

        $(`#${uploadDivId}`).hide();
        $(`#${previewId}`).show();
        $(`#${previewId} .remove-upload`).show();
    }

    return true;
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
    } else if (!validateFileSize(mainImage, "Main image")) {
        clearFileInput("myFile", "imgpreview", "upload-file");
        isValid = false;
    }

    // Validate requirements file
    const requirementsFile = $("#requirementsFile")[0].files[0];
    if (!requirementsFile) {
        showAlert("Please upload a requirements file", "danger");
        isValid = false;
    } else if (!validateFileSize(requirementsFile, "Requirements")) {
        clearFileInput(
            "requirementsFile",
            "requirementsPreview",
            "upload-requirements"
        );
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
    } else {
        // Check each gallery image size
        for (let i = 0; i < galleryFiles.length; i++) {
            if (!validateFileSize(galleryFiles[i], `Gallery image ${i + 1}`)) {
                // Clear all gallery files if any exceed size limit
                $("#gFile").val("");
                // Clear gallery preview container
                $("#gallery-container .preview-item").remove();
                $("#galUpload").show();
                isValid = false;
                break;
            }
        }
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

function removeUpload(previewId, inputId) {
    // Hide the inner preview content, not the entire container
    const preview = $("#" + previewId);
    preview.find("img").attr("src", "/images/upload/upload-1.png");
    preview.find(".file-name-overlay").remove();
    preview.find(".remove-upload").hide();
    $("#" + inputId).val("");

    // Restore the upload section
    $("#upload-" + inputId).show();

    // Clear validation
    $("#" + inputId).removeClass("is-invalid");
    $("#" + inputId)
        .next(".invalid-feedback")
        .remove();
    $(".alert-danger").remove();
    $("#alertContainer").empty();
}

// Add event listeners to clear validation errors when inputs change
$(document).on("change input", "input, select, textarea", function () {
    $(this).removeClass("is-invalid");
    const feedback = $(this).next(".invalid-feedback");
    if (feedback.length) {
        feedback.text("");
    }
});

// Add event listeners for file inputs with immediate validation
$(document).on("change", 'input[type="file"]', function () {
    const inputId = $(this).attr("id");
    const files = this.files;

    // Clear previous validation errors
    $(this).removeClass("is-invalid");
    $(this).next(".invalid-feedback").remove();
});

// Function to handle gallery preview (implement based on your existing gallery logic)
function handleGalleryPreview(files) {
    // Clear existing previews
    $("#gallery-container .preview-item").remove();

    // Hide upload button initially
    $("#galUpload").hide();

    // Create previews for each file
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewItem = $(`
                  <div class="item preview-item">
                      <img src="${
                          e.target.result
                      }" class="effect8" alt="Gallery ${index + 1}">
                      <button type="button" class="remove-upload" onclick="removeGalleryItem(this)">Remove</button>
                  </div>
              `);
            $("#galUpload").before(previewItem);
        };
        reader.readAsDataURL(file);
    });

    // Show upload button again
    $("#galUpload").show();
}

// Function to remove individual gallery items
function removeGalleryItem(button) {
    $(button).closest(".preview-item").remove();

    // If no more preview items, clear the file input
    if ($("#gallery-container .preview-item").length === 0) {
        $("#gFile").val("");
    }
}

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
    clearValidationErrors();

    if (!validateFileUploads()) {
        return;
    }
    if (!isFormValid()) {
        showAlert("Please fill in all required fields.", "danger");
        return;
    }

    const formData = new FormData(this);
    const facilityType = $("#rentalType").val();
    if (facilityType === "both" && rooms && rooms.length > 0) {
        formData.delete("whole_capacity");
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
        beforeSend: function () {
            $("#facilitySubmitBtn").prop("disabled", true);
            $("#facilitySubmitBtn .btn-text").text("Submitting...");
        },
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
            $("#facilitySubmitBtn").prop("disabled", false);
            $("#facilitySubmitBtn .btn-text").text("Create Facility");

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
