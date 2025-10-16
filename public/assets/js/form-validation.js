function setupFormValidation() {
    $("#facilityForm").on("submit", function (e) {
        e.preventDefault();

        const isEdit = $(this).hasClass("form-update-rental");
        const submitBtn = $("#facilitySubmitBtn");
        const originalText = submitBtn.find(".btn-text").text();

        submitBtn
            .prop("disabled", true)
            .find(".btn-text")
            .text(isEdit ? "Updating..." : "Submitting...");

        const facilityType = $("#rentalType").val();
        let hasValidationError = false;
        let errorMessage = "";

        // Basic field validations
        if (!$('input[name="name"]').val().trim()) {
            hasValidationError = true;
            errorMessage = "Facility name is required.";
        } else if (!facilityType) {
            hasValidationError = true;
            errorMessage = "Facility type is required.";
        } else if (!$('textarea[name="description"]').val().trim()) {
            hasValidationError = true;
            errorMessage = "Description is required.";
        } else if (!$('textarea[name="rules_and_regulations"]').val().trim()) {
            hasValidationError = true;
            errorMessage = "Rules and regulations are required.";
        }

        // File validations - different logic for create vs edit
        if (!isEdit) {
            // Create mode - files are required
            if (!$('input[name="requirements"]')[0].files.length) {
                hasValidationError = true;
                errorMessage = "Requirements file is required.";
            } else if (!$('input[name="image"]')[0].files.length) {
                hasValidationError = true;
                errorMessage = "Main image is required.";
            }
        }

        // Validate file types and sizes for both create and edit modes when files are uploaded
        const requirementsFile = $('input[name="requirements"]')[0];
        const imageFile = $('input[name="image"]')[0];
        const galleryFiles =
            $('input[name="images[]"]')[0] || $('input[name="images"]')[0];

        // Requirements file validation (only PDF, DOC, DOCX - no images)
        if (requirementsFile && requirementsFile.files.length > 0) {
            const file = requirementsFile.files[0];
            const allowedTypes = [
                "application/pdf",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            ];
            const maxSize = 2048 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                hasValidationError = true;
                errorMessage =
                    "Requirements file must be PDF, DOC, or DOCX format only.";
            } else if (file.size > maxSize) {
                hasValidationError = true;
                errorMessage = "Requirements file must be less than 2MB.";
            }
        }

        // Main image validation
        if (imageFile && imageFile.files.length > 0) {
            const file = imageFile.files[0];
            const allowedImageTypes = [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
                "image/svg+xml",
            ];
            const maxSize = 10240 * 1024; // 2MB

            if (!allowedImageTypes.includes(file.type)) {
                hasValidationError = true;
                errorMessage =
                    "Main image must be JPEG, PNG, JPG, GIF, or SVG format.";
            } else if (file.size > maxSize) {
                hasValidationError = true;
                errorMessage = "Main image must be less than 10MB.";
            }
        }

        // Gallery images validation
        if (galleryFiles && galleryFiles.files.length > 0) {
            const allowedImageTypes = [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
                "image/svg+xml",
            ];
            const maxSize = 10240 * 1024; // 2MB

            for (let i = 0; i < galleryFiles.files.length; i++) {
                const file = galleryFiles.files[i];
                if (!allowedImageTypes.includes(file.type)) {
                    hasValidationError = true;
                    errorMessage = `Gallery image ${
                        i + 1
                    } must be JPEG, PNG, JPG, GIF, or SVG format.`;
                    break;
                } else if (file.size > maxSize) {
                    hasValidationError = true;
                    errorMessage = `Gallery image ${
                        i + 1
                    } must be less than 10MB.`;
                    break;
                }
            }
        }

        // Facility type specific validations
        if (!hasValidationError) {
            if (facilityType === "both") {
                // Get the actual selection value for 'both' type
                let facilitySelectionBoth = $(
                    'input[name="facility_selection_both"]:checked'
                ).val();

                // If radio is disabled (edit mode), get from hidden field
                if (!facilitySelectionBoth) {
                    facilitySelectionBoth = $(
                        'input[name="facility_selection_both"][type="hidden"]'
                    ).val();
                }

                // If still no value, infer from current data
                if (!facilitySelectionBoth) {
                    const hasRooms = rooms && rooms.length > 0;
                    const hasWholeCapacity =
                        $("#roomCapacityWhole").val() &&
                        $("#roomCapacityWhole").val().trim() !== "";

                    if (hasWholeCapacity && !hasRooms) {
                        facilitySelectionBoth = "whole";
                    } else if (hasRooms && !hasWholeCapacity) {
                        facilitySelectionBoth = "room";
                    } else if (hasRooms) {
                        facilitySelectionBoth = "room"; // Default to room if both exist
                    }

                    // Set the hidden field for form submission
                    if (facilitySelectionBoth) {
                        // Remove any existing hidden field first
                        $(
                            'input[name="facility_selection_both"][type="hidden"]'
                        ).remove();
                        // Add new hidden field
                        $("#facilityForm").append(
                            `<input type="hidden" name="facility_selection_both" value="${facilitySelectionBoth}">`
                        );
                        console.log(
                            "Set facility_selection_both to:",
                            facilitySelectionBoth
                        );
                    }
                }

                // Validate based on the actual selection
                if (facilitySelectionBoth === "whole") {
                    // Only validate whole capacity, ignore rooms
                    const wholeCapacity = $("#roomCapacityWhole").val();
                    if (!wholeCapacity || wholeCapacity.trim() === "") {
                        hasValidationError = true;
                        errorMessage =
                            'For "Both" facility type with whole capacity selected, you must provide a whole capacity value.';
                    }
                } else if (facilitySelectionBoth === "room") {
                    // Only validate rooms, ignore whole capacity
                    const hasRooms = rooms && rooms.length > 0;
                    if (!hasRooms) {
                        hasValidationError = true;
                        errorMessage =
                            'For "Both" facility type with rooms selected, you must add at least one room.';
                    } else {
                        // Validate all rooms
                        for (let room of rooms) {
                            if (
                                !room.room_name ||
                                !room.capacity ||
                                !room.sex_restriction
                            ) {
                                hasValidationError = true;
                                errorMessage =
                                    "All rooms must have a name, capacity, and sex restriction.";
                                break;
                            }
                        }
                    }
                } else {
                    // No selection made
                    hasValidationError = true;
                    errorMessage =
                        'For "Both" facility type, you must select either "Has Whole Capacity" or "Has Room(s)".';
                }
            } else if (facilityType === "individual") {
                if (!rooms || rooms.length === 0) {
                    hasValidationError = true;
                    errorMessage =
                        'For "Individual" facility type, you must add at least one room.';
                } else {
                    for (let room of rooms) {
                        if (
                            !room.room_name ||
                            !room.capacity ||
                            !room.sex_restriction
                        ) {
                            hasValidationError = true;
                            errorMessage =
                                "All rooms must have a name, capacity, and sex restriction.";
                            break;
                        }
                    }
                }
            } else if (facilityType === "whole_place") {
                const wholeCapacity = $("#roomCapacityWhole").val();
                if (!wholeCapacity || wholeCapacity.trim() === "") {
                    hasValidationError = true;
                    errorMessage =
                        'For "Whole Place" facility type, you must provide a whole capacity.';
                }
            }
        }

        // Price validation
        if (!hasValidationError && prices.length === 0) {
            hasValidationError = true;
            errorMessage = "At least one price must be added.";
        }

        // Handle validation errors
        if (hasValidationError) {
            submitBtn
                .prop("disabled", false)
                .find(".btn-text")
                .text(originalText);
            alert(errorMessage);
            return false;
        }

        // Prepare data for submission
        if (facilityType === "both") {
            // Get the selection to determine what data to send
            let facilitySelectionBoth =
                $('input[name="facility_selection_both"]:checked').val() ||
                $('input[name="facility_selection_both"][type="hidden"]').val();

            if (facilitySelectionBoth === "room") {
                // Send rooms data, clear whole capacity
                $("#facilityAttributesJson").val(JSON.stringify(rooms || []));
                $("#roomCapacityWhole").val(""); // Clear whole capacity
            } else if (facilitySelectionBoth === "whole") {
                // Send empty rooms array, keep whole capacity
                $("#facilityAttributesJson").val(JSON.stringify([]));
                // Keep the whole capacity value as is
            }
        } else if (facilityType === "individual") {
            $("#facilityAttributesJson").val(JSON.stringify(rooms));
            $("#roomCapacityWhole").val(""); // Clear whole capacity for individual type
        } else if (facilityType === "whole_place") {
            $("#facilityAttributesJson").val(JSON.stringify([]));
            // Keep whole capacity value for whole_place type
        }

        $("#pricesJson").val(JSON.stringify(prices));

        // Debug: Final check before submission
        console.log("=== FINAL FORM SUBMISSION DEBUG ===");
        console.log("facilityType:", facilityType);
        console.log(
            "All facility_selection_both fields:",
            $('input[name="facility_selection_both"]').length
        );
        $('input[name="facility_selection_both"]').each(function (i, el) {
            console.log(`Field ${i}:`, {
                type: el.type,
                value: el.value,
                checked: el.checked,
                disabled: el.disabled,
            });
        });
        console.log(
            "facilityAttributesJson:",
            $("#facilityAttributesJson").val()
        );
        console.log("wholeCapacity:", $("#roomCapacityWhole").val());

        // Force create hidden field if it doesn't exist for 'both' type
        if (facilityType === "both") {
            let hiddenField = $(
                'input[name="facility_selection_both"][type="hidden"]'
            );
            if (hiddenField.length === 0) {
                const hasWholeCapacity =
                    $("#roomCapacityWhole").val() &&
                    $("#roomCapacityWhole").val().trim() !== "";
                const hasRooms = rooms && rooms.length > 0;

                let inferredValue = "";
                if (hasWholeCapacity && !hasRooms) {
                    inferredValue = "whole";
                } else if (hasRooms) {
                    inferredValue = "room";
                }

                if (inferredValue) {
                    $("#facilityForm").append(
                        `<input type="hidden" name="facility_selection_both" value="${inferredValue}">`
                    );
                    console.log(
                        "EMERGENCY: Created missing hidden field with value:",
                        inferredValue
                    );
                }
            }
        }

        this.submit();
    });
}
