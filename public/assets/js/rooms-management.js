function setupRoomsManagement() {
    // Modal handlers
    $("#addMultipleRoomsModal").on("hidden.bs.modal", function () {
        if (!roomEditMode) {
            $(".room-name, .room-capacity").val("");
            $(".room-sex-restriction").val("");
        }
        roomEditMode = false;
        roomEditIndex = -1;
        $("#addMultipleRoomsLabel").text("Manage Rooms");
        $("#saveMultipleRoomsBtn")
            .text("Save All")
            .off("click")
            .on("click", handleSaveRoom);
    });

    // Select all rooms functionality
    $(document).on(
        "change",
        '#checkAllRooms input[type="checkbox"]',
        function () {
            const isChecked = $(this).is(":checked");
            $(".edit-selected-btn, .delete-selected-btn").toggle(isChecked);
            $(".room-checkbox").prop("checked", isChecked ? 1 : 0);
            updateRoomActionVisibility();
        }
    );

    // Individual room checkbox handler
    $(document).on("change", ".room-checkbox", function () {
        updateRoomActionVisibility();
        updateSelectAllRoomCheckbox();
    });

    // Room management buttons
    $("#saveMultipleRoomsBtn").on("click", handleSaveRoom);
    $("#saveBulkRoomsBtn").on("click", handleSaveBulkRooms);

    // Edit and delete buttons
    $(document).on("click", ".edit-room-btn", handleEditRoom);
    $(document).on("click", ".delete-room-btn", handleDeleteRoom);
    $("#deleteSelectedRoomsBtn").on("click", handleDeleteSelectedRooms);
    $("#editSelectedRoomsBtn").on("click", handleEditSelectedRooms);

    // Room capacity handler for "both" type
    $("#roomCapacityWhole").on("input", function () {
        const facilityType = $("#rentalType").val();
        if (facilityType === "both") {
            const hasValue = $(this).val().trim() !== "";
            const addRoomButtons = $("#dormitoryRooms .d-flex.gap-2 button");

            if (hasValue) {
                addRoomButtons.prop("disabled", true);
                $("#roomButtonsMessage").hide();
                $("#noRoomsMessage").hide();
            } else {
                addRoomButtons.prop("disabled", false);
                $("#roomButtonsMessage").hide();
                if (rooms.length === 0) {
                    $("#noRoomsMessage").show();
                }
            }
        }
    });

    // Facility type change handler
    $("#rentalType").on("change", function () {
        const facilityType = $(this).val();

        // Clear previous data only if facility type changes
        if (facilityType) {
            $("#roomCapacityWhole").val("");

            // Only clear data if this is a fresh form (not on validation error)
            if (!window.facilityFormConfig.hasValidationErrors) {
                rooms = [];
                prices = [];
                globalPriceSettings = {
                    isBasedOnDays: false,
                    isThereAQuantity: false,
                    dateFrom: "",
                    dateTo: "",
                };
                $("#isBasedOnDaysGlobal").prop("checked", false);
                $("#isThereAQuantityGlobal").prop("checked", false);
                $("#date_from_global, #date_to_global").val("");
                $("#dateFieldsContainerGlobal").hide();
            }

            showFacilityTypeFields(facilityType);
            renderRoomList();
            renderPriceList();
        }
    });

    // Handle radio button changes for "both" facility type
    $('input[name="facility_selection_both"]').on("change", function () {
        const selectedValue = $(this).val();
        if (selectedValue === "whole") {
            $("#hideRoomBox").show();
            $("#dormitoryRooms").hide();
            $("#selectionContent").hide();
        } else if (selectedValue === "room") {
            $("#hideRoomBox").hide();
            $("#dormitoryRooms").show();
            $("#selectionContent").hide();
        }
    });
}

// Helper function to check if room name exists
function isRoomNameUnique(roomName, excludeIndex = -1) {
    return !rooms.some((room, index) => {
        if (index === excludeIndex) return false; // Skip the current room being edited
        return (
            room.room_name &&
            room.room_name.toLowerCase() === roomName.toLowerCase()
        );
    });
}

// Helper function to get duplicate room names from an array
function getDuplicateRoomNames(newRooms, excludeIndices = []) {
    const duplicates = [];
    const existingRoomNames = rooms
        .filter((room, index) => !excludeIndices.includes(index))
        .map((room) => (room.room_name ? room.room_name.toLowerCase() : ""));

    newRooms.forEach((newRoom) => {
        const roomNameLower = newRoom.room_name.toLowerCase();
        if (existingRoomNames.includes(roomNameLower)) {
            duplicates.push(newRoom.room_name);
        }
    });

    return duplicates;
}

function renderRoomList() {
    const container = $("#roomCardsContainer").empty();

    // Filter out rooms that only have whole_capacity (these shouldn't be displayed as individual rooms)
    const individualRooms = rooms.filter(
        (room) => room.room_name || room.capacity || room.sex_restriction
    );

    if (individualRooms.length === 0) {
        $("#noRoomsMessage").show();
        $("#checkAllRooms").hide();

        // Update hidden field with current rooms data (including whole_capacity records)
        $("#facilityAttributesJson").val(JSON.stringify(rooms));

        // Handle whole_capacity field state for 'both' type
        const facilityType = $("#rentalType").val();
        if (facilityType === "both") {
            const wholeCapacityField = $("#roomCapacityWhole");
            const hasWholeCapacityValue = wholeCapacityField.val() !== "";

            if (!hasWholeCapacityValue) {
                // Check if we have whole_capacity in rooms data
                const wholeCapacityRoom = rooms.find(
                    (room) => room.whole_capacity && room.whole_capacity > 0
                );
                if (wholeCapacityRoom) {
                    wholeCapacityField.val(wholeCapacityRoom.whole_capacity);
                }
            }

            wholeCapacityField.prop("disabled", false);
        }
        return;
    }

    $("#noRoomsMessage").hide();
    $("#checkAllRooms").show();

    individualRooms.forEach((room, originalIndex) => {
        // Find the original index in the rooms array
        const actualIndex = rooms.findIndex((r) => r === room);

        const card = $(`
            <div class="card p-3 mb-3 room-card" data-index="${actualIndex}">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="room-checkbox" data-index="${actualIndex}">
                            <h4 class="pe-2">${room.room_name}</h4>
                            <span class="badge bg-primary">${
                                room.sex_restriction || "No Restriction"
                            }</span>
                        </div>
                        <p class="fw-bold">Capacity: <span>${
                            room.capacity
                        }</span></p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-warning edit-room-btn" data-index="${actualIndex}">
                            <i class="icon-pen"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-room-btn" data-index="${actualIndex}">
                            <i class="icon-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `);
        container.append(card);
    });

    // Always update the hidden field with complete rooms data
    $("#facilityAttributesJson").val(JSON.stringify(rooms));
    updateRoomActionVisibility();
    updateSelectAllRoomCheckbox();

    // Handle whole_capacity field state for 'both' type
    const facilityType = $("#rentalType").val();
    if (facilityType === "both") {
        const wholeCapacityField = $("#roomCapacityWhole");
        const isEditMode =
            window.facilityFormConfig && window.facilityFormConfig.isEditMode;
        const currentWholeCapacity = wholeCapacityField.val();

        if (individualRooms.length > 0) {
            // Disable whole_capacity when individual rooms exist, but preserve value in edit mode
            if (isEditMode && currentWholeCapacity) {
                // Keep the field enabled in edit mode if it has a value
                wholeCapacityField.prop("disabled", false);
            } else {
                wholeCapacityField.prop("disabled", true);
                if (!isEditMode) {
                    wholeCapacityField.val("");
                }
            }
            $("#roomButtonsMessage").hide();
        } else {
            wholeCapacityField.prop("disabled", false);

            // Check if we have whole_capacity in rooms data but field is empty
            if (!currentWholeCapacity) {
                const wholeCapacityRoom = rooms.find(
                    (room) => room.whole_capacity && room.whole_capacity > 0
                );
                if (wholeCapacityRoom) {
                    wholeCapacityField.val(wholeCapacityRoom.whole_capacity);
                    console.log(
                        "Restored whole_capacity field with:",
                        wholeCapacityRoom.whole_capacity
                    );
                }
            }
        }
    }
}

function updateRoomActionVisibility() {
    const checkedCount = $(".room-checkbox:checked").length;
    $("#checkAllRooms").toggle(checkedCount >= 1);
    $(".edit-selected-btn, .delete-selected-btn").toggle(checkedCount >= 1);
}

function updateSelectAllRoomCheckbox() {
    const totalCheckboxes = $(".room-checkbox").length;
    const checkedCheckboxes = $(".room-checkbox:checked").length;
    const selectAllCheckbox = $('#checkAllRooms input[type="checkbox"]');

    if (checkedCheckboxes === 0) {
        selectAllCheckbox.prop("checked", false).prop("indeterminate", false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        selectAllCheckbox.prop("checked", true).prop("indeterminate", false);
    } else {
        selectAllCheckbox.prop("checked", false).prop("indeterminate", true);
    }
}

function handleSaveRoom(e) {
    e.preventDefault();
    const name = $(".room-name").val().trim();
    const capacity = $(".room-capacity").val().trim();
    const sex = $(".room-sex-restriction").val();

    if (!name || !capacity || !sex) {
        alert("Name, capacity, and sex restriction are required.");
        return;
    }

    // Check for unique room name
    if (!isRoomNameUnique(name, roomEditIndex)) {
        alert(
            `Room name "${name}" already exists. Please use a different name.`
        );
        return;
    }

    if (roomEditMode && roomEditIndex !== -1) {
        rooms[roomEditIndex] = {
            room_name: name,
            capacity: capacity,
            sex_restriction: sex,
        };
    } else {
        rooms.push({
            room_name: name,
            capacity: capacity,
            sex_restriction: sex,
        });
    }

    $("#addMultipleRoomsModal").modal("hide");
    renderRoomList();
}

function handleEditRoom() {
    const index = $(this).data("index");
    const room = rooms[index];

    roomEditMode = true;
    roomEditIndex = index;

    $(".room-name").val(room.room_name);
    $(".room-capacity").val(room.capacity);
    $(".room-sex-restriction").val(room.sex_restriction);

    $("#addMultipleRoomsLabel").text("Edit Room");
    $("#saveMultipleRoomsBtn").text("Update Room");
    $("#addMultipleRoomsModal").modal("show");
}

function handleDeleteRoom() {
    if (confirm("Are you sure you want to delete this room?")) {
        const index = $(this).data("index");
        rooms.splice(index, 1);
        renderRoomList();
    }
}

function handleDeleteSelectedRooms() {
    if (confirm("Are you sure you want to delete the selected rooms?")) {
        const selected = getSelectedRooms();

        if (selected.length === 0) {
            alert("Please select at least one room to delete.");
            return;
        }
        rooms = rooms.filter((_, index) => !selected.includes(index));
        renderRoomList();
    }
}

function handleEditSelectedRooms() {
    const selected = getSelectedRooms();
    if (selected.length === 0) {
        alert("Select at least one room to edit.");
        return;
    }
    const originalValues = {};
    let modalContent = "";

    selected.forEach((index) => {
        const room = rooms[index];
        originalValues[index] = {
            room_name: room.room_name,
            capacity: room.capacity,
            sex_restriction: room.sex_restriction || "",
        };

        modalContent += `
        <div class="room-edit-section mb-4 p-3 border rounded">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Room Name</label>
                    <input type="text" class="form-control edit-room-name" data-index="${index}" value="${
            room.room_name
        }">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" class="form-control edit-room-capacity" data-index="${index}" value="${
            room.capacity
        }">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sex Restriction</label>
                    <select class="edit-room-sex-restriction" data-index="${index}">
                        <option value="">No Restriction</option>
                        <option value="male" ${
                            room.sex_restriction === "male" ? "selected" : ""
                        }>Male</option>
                        <option value="female" ${
                            room.sex_restriction === "female" ? "selected" : ""
                        }>Female</option>
                        <option value="all" ${
                            room.sex_restriction === "all" ? "selected" : ""
                        }>All</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    });
    const modalBody = $("#addMultipleRoomsModal .modal-body");
    const originalContent = modalBody.html();

    modalBody.html(`
        <div class="bulk-edit-container">
            <h6 class="mb-3">Edit ${selected.length} Selected Room(s)</h6>
            ${modalContent}
        </div>
    `);

    $("#addMultipleRoomsModal").modal("show");
    $("#saveMultipleRoomsBtn")
        .off("click")
        .click(function (e) {
            e.preventDefault();

            let hasChanges = false;
            let hasErrors = false;
            let duplicateNames = [];

            // First pass: validate all fields and check for duplicates
            selected.forEach((index) => {
                const newName = $(`.edit-room-name[data-index="${index}"]`)
                    .val()
                    .trim();
                const newCapacity = $(
                    `.edit-room-capacity[data-index="${index}"]`
                )
                    .val()
                    .trim();

                if (!newName || !newCapacity) {
                    hasErrors = true;
                    return;
                }

                // Check if the name is unique (excluding all selected rooms being edited)
                if (!isRoomNameUnique(newName, index)) {
                    // Check if it's not just keeping its own name
                    const original = originalValues[index];
                    if (
                        newName.toLowerCase() !==
                        original.room_name.toLowerCase()
                    ) {
                        duplicateNames.push(newName);
                    }
                }
            });

            if (hasErrors) {
                alert("Name and capacity are required for all rooms.");
                return;
            }

            if (duplicateNames.length > 0) {
                alert(
                    `The following room name(s) already exist: ${duplicateNames.join(
                        ", "
                    )}\n\nPlease use different names.`
                );
                return;
            }

            // Second pass: apply changes
            selected.forEach((index) => {
                const newName = $(`.edit-room-name[data-index="${index}"]`)
                    .val()
                    .trim();
                const newCapacity = $(
                    `.edit-room-capacity[data-index="${index}"]`
                )
                    .val()
                    .trim();
                const newSexRestriction = $(
                    `.edit-room-sex-restriction[data-index="${index}"]`
                ).val();

                const original = originalValues[index];

                if (newName !== original.room_name) {
                    rooms[index].room_name = newName;
                    hasChanges = true;
                }

                if (newCapacity !== original.capacity) {
                    rooms[index].capacity = newCapacity;
                    hasChanges = true;
                }

                if (newSexRestriction !== original.sex_restriction) {
                    rooms[index].sex_restriction = newSexRestriction;
                    hasChanges = true;
                }
            });

            modalBody.html(originalContent);

            $("#addMultipleRoomsModal").modal("hide");
            renderRoomList();

            if (hasChanges) {
                alert(`${selected.length} room(s) updated successfully!`);
            } else {
                alert("No changes were made.");
            }
        });
}

function getSelectedRooms() {
    return $(".room-checkbox:checked")
        .map(function () {
            return $(this).data("index");
        })
        .get();
}

function handleSaveBulkRooms() {
    const prefix = $("#roomPrefix").val().trim();
    const start = parseInt($("#startNumber").val());
    const end = parseInt($("#endNumber").val());
    const capacity = $("#bulkCapacity").val().trim();
    const sex = $("#bulkSexRestriction").val();

    if (!prefix || !start || !end || !capacity || !sex) {
        alert("All fields are required.");
        return;
    }

    if (start > end) {
        alert("Start number must be less than or equal to end number.");
        return;
    }

    // Generate preview and check for duplicates
    bulkRoomPreview = [];
    const duplicates = [];

    for (let i = start; i <= end; i++) {
        const roomName = `${prefix}${i}`;

        // Check if this room name already exists
        if (!isRoomNameUnique(roomName)) {
            duplicates.push(roomName);
        }

        bulkRoomPreview.push({
            room_name: roomName,
            capacity: capacity,
            sex_restriction: sex,
        });
    }

    // If there are duplicates, show warning and stop
    if (duplicates.length > 0) {
        const duplicateList = duplicates.join(", ");
        alert(
            `Cannot add rooms. The following room name(s) already exist:\n\n${duplicateList}\n\nPlease use a different prefix or number range.`
        );
        return;
    }

    showBulkRoomPreview();
}

function showBulkRoomPreview() {
    $("#addBulkRoomsLabel").text("Preview Generated Rooms");
    let previewContent = `
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            Preview of ${bulkRoomPreview.length} rooms to be added. You can modify individual room capacities before saving.
        </div>
        <div class="row g-3" id="bulkRoomPreviewContainer">
    `;

    bulkRoomPreview.forEach((room, index) => {
        previewContent += `
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">${room.room_name}</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Capacity</label>
                            <input type="number"
                                   class="form-control preview-room-capacity"
                                   data-index="${index}"
                                   value="${room.capacity}"
                                   min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sex Restriction</label>
                            <select class="form-select preview-room-sex" data-index="${index}">
                                <option value="">No Restriction</option>
                                <option value="male" ${
                                    room.sex_restriction === "male"
                                        ? "selected"
                                        : ""
                                }>Male</option>
                                <option value="female" ${
                                    room.sex_restriction === "female"
                                        ? "selected"
                                        : ""
                                }>Female</option>
                                <option value="all" ${
                                    room.sex_restriction === "all"
                                        ? "selected"
                                        : ""
                                }>All</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    });
    previewContent += `
        </div>
      <div class="mt-5 d-flex justify-content-center gap-2">
    <button type="button" class="btn btn-secondary btn-lg" id="backToBulkFormBtn">
        <i class="bi bi-arrow-left"></i> Back to Form
    </button>
    <button type="button" class="btn btn-success btn-lg" id="confirmBulkRoomsBtn">
        <i class="bi bi-check-circle"></i> Confirm & Add All Rooms
    </button>
</div>

    `;

    $("#addBulkRoomsModal .modal-body").html(previewContent);
    $("#addBulkRoomsModal .modal-footer").hide();

    $("#backToBulkFormBtn").on("click", showBulkRoomForm);
    $("#confirmBulkRoomsBtn").on("click", confirmBulkRooms);

    $(document).on("change", ".preview-room-capacity", function () {
        const index = $(this).data("index");
        const newCapacity = $(this).val();
        if (bulkRoomPreview[index]) {
            bulkRoomPreview[index].capacity = newCapacity;
        }
    });

    $(document).on("change", ".preview-room-sex", function () {
        const index = $(this).data("index");
        const newSex = $(this).val();
        if (bulkRoomPreview[index]) {
            bulkRoomPreview[index].sex_restriction = newSex;
        }
    });
}

function showBulkRoomForm() {
    $("#addBulkRoomsLabel").text("Add Multiple Rooms");

    const originalFormContent = `
        <form id="bulkRoomForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Room Prefix</label>
                    <input type="text" class="form-control" id="roomPrefix" placeholder="e.g., Room">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Number</label>
                    <input type="number" class="form-control" id="startNumber" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Number</label>
                    <input type="number" class="form-control" id="endNumber" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="bulkCapacity" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sex Restriction</label>
                    <select class="" id="bulkSexRestriction">
                        <option value="">No Restriction</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="all">All</option>
                    </select>
                </div>
            </div>
        </form>
    `;

    $("#addBulkRoomsModal .modal-body").html(originalFormContent);
    $("#addBulkRoomsModal .modal-footer").show();
}

function confirmBulkRooms() {
    let isValid = true;
    let errorMessage = "";

    bulkRoomPreview.forEach((room, index) => {
        if (!room.room_name || !room.capacity || !room.sex_restriction) {
            isValid = false;
            errorMessage = `Room ${
                room.room_name || index + 1
            } is missing required information.`;
            return false;
        }

        if (parseInt(room.capacity) < 1) {
            isValid = false;
            errorMessage = `Room ${room.room_name} must have a capacity of at least 1.`;
            return false;
        }
    });

    if (!isValid) {
        alert(errorMessage);
        return;
    }

    // Store the count before clearing
    const roomCount = bulkRoomPreview.length;

    rooms.push(...bulkRoomPreview);
    $("#addBulkRoomsModal").modal("hide");
    resetBulkRoomModal();
    renderRoomList();

    // Show success message with stored count
    alert(`Successfully added ${roomCount} room(s)!`);
    bulkRoomPreview = [];
}

function resetBulkRoomModal() {
    showBulkRoomForm();
    $("#bulkRoomForm")[0].reset();
    bulkRoomPreview = [];
}

// Modal event handlers
$("#addBulkRoomsModal").on("hidden.bs.modal", function () {
    resetBulkRoomModal();
});

$('[data-bs-target="#addBulkRoomsModal"]').on("click", function () {
    resetBulkRoomModal();
});
