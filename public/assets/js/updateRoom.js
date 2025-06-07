$(document).ready(function () {
    renderRoomList(); // Initial render of room list

    // Adding a new room row in the modal - Fixed to prevent duplicate event binding
    $("#addMultipleRoomsRowBtn")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();
            if (!isEditMode) {
                $("#roomFormContainer").append(createRoomFormCard());
            }
        });

    // Save button functionality for both create and edit
    $("#saveMultipleRoomsBtn").on("click", function (e) {
        e.preventDefault();
        if (isEditMode) {
            updateEditedRoom();
        } else {
            saveMultipleRooms();
        }
    });

    // Handle the room edit functionality
    $(document).on("click", ".edit-room", function () {
        const index = $(this).data("index");
        editRoom(index);
    });

    // Handle deleting a room - Fixed to prevent multiple triggers and ensure single deletion
    $(document)
        .off("click", ".delete-room")
        .on("click", ".delete-room", function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const index = $(this).data("index");
            const room = rooms[index];

            if (
                confirm(
                    `Are you sure you want to delete room "${room.room_name}"?`
                )
            ) {
                // Remove only the specific room
                rooms.splice(index, 1);
                // Update UI after deletion
                updateUI();
                // Prevent any further event handling
                return false;
            }
        });

    // Remove room form - Fixed to prevent event bubbling
    $(document)
        .off("click", ".removeRoomBtn")
        .on("click", ".removeRoomBtn", function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $(this).closest(".room-form-card").remove();
            return false;
        });

    // Handle select all checkbox
    $("#selectAllRooms").on("change", function () {
        const isChecked = $(this).prop("checked");
        $(".room-checkbox").prop("checked", isChecked);
        rooms.forEach((room) => (room.selected = isChecked));
        toggleBulkActionButtons();
    });

    // Handle individual checkboxes
    $(document).on("change", ".room-checkbox", function () {
        const index = $(this).data("index");
        rooms[index].selected = $(this).prop("checked");
        updateSelectAllCheckbox();
        toggleBulkActionButtons();
    });

    // Initialize modal with proper event handling
    $("#addMultipleRoomsModal").on("show.bs.modal", function (e) {
        if (!isEditMode && $("#roomFormContainer").children().length === 0) {
            $("#roomFormContainer").append(createRoomFormCard());
        }
    });

    // Reset modal state when hidden
    $("#addMultipleRoomsModal").on("hidden.bs.modal", function () {
        if (!isEditMode) {
            $("#roomFormContainer").empty();
        } else {
            isEditMode = false;
            editRoomIndex = null;
            $("#roomFormContainer").empty();
        }
        $("#saveMultipleRoomsBtn").text("Save All");
        $("#addMultipleRoomsRowBtn").show();
    });
});

let isEditMode = false;
let editRoomIndex = null;

function renderRoomList() {
    $("#roomCardsContainer").empty();
    $("#noRoomsMessage").toggle(rooms.length === 0);
    $("#roomContainer").toggle(rooms.length > 0);

    // Only show bulk actions if there are multiple rooms
    $(".bulk-actions").toggle(rooms.length > 1);

    rooms.forEach((room, index) => {
        const roomCard = createRoomCard(room, index);
        $("#roomCardsContainer").append(roomCard);
    });
}

function createRoomCard(room, index) {
    // Determine badge color based on sex restriction
    let badgeClass = "bg-secondary";
    let restrictionText = "No Restriction";

    if (room.sex_restriction === "male") {
        badgeClass = "bg-primary";
        restrictionText = "Male";
    } else if (room.sex_restriction === "female") {
        badgeClass = "bg-danger";
        restrictionText = "Female";
    }

    return `
    <div class="card p-3 mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="form-check me-3">
                    <input class="form-check-input room-checkbox" type="checkbox"
                           data-index="${index}" ${
        room.selected ? "checked" : ""
    }>
                </div>
                <div>
                    <h4 class="pe-2">${room.room_name}</h4>
                    <span class="badge ${badgeClass}">${restrictionText}</span>
                    <p class="fw-bold">Capacity: <span class="badge bg-warning">${
                        room.capacity
                    }</span></p>
                </div>
            </div>
            <div class="d-flex">
                <button type="button" class="btn btn-lg btn-outline-warning me-2 edit-room" data-index="${index}">
                    <i class="icon-pen"></i> Edit
                </button>
                <button type="button" class="btn btn-lg btn-outline-danger delete-room" data-index="${index}">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    </div>`;
}

function createRoomFormCard() {
    return `
    <div class="room-form-card mb-3 p-3 border rounded">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control room-name" placeholder="Enter room name">
                <div class="invalid-feedback room-name-error"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity">
                <div class="invalid-feedback room-capacity-error"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sex Restriction</label>
                <div class="select">
                    <select class="room-sex-restriction">
                        <option value="">No Restriction</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="invalid-feedback room-sex-restriction-error"></div>
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-lg btn-outline-danger removeRoomBtn">
                   <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>`;
}

function createEditRoomFormCard(room, roomIndex, isEditable) {
    const borderClass = isEditable ? "border-primary" : "border-secondary";
    const disabledAttr = isEditable ? "" : "disabled";

    return `
    <div class="room-form-card mb-3 p-3 border rounded ${borderClass}" data-room-index="${roomIndex}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-muted">Room ${roomIndex + 1}</small>
            ${
                !isEditable
                    ? '<small class="text-muted"><i class="fa-solid fa-lock"></i> Locked</small>'
                    : '<small class="text-primary"><i class="fa-solid fa-edit"></i> Editable</small>'
            }
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control room-name" placeholder="Enter room name"
                       value="${room.room_name}" ${disabledAttr}>
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity"
                       value="${room.capacity}" ${disabledAttr}>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sex Restriction</label>
                <div class="select">
                    <select class="room-sex-restriction" ${disabledAttr}>
                        <option value="" ${
                            room.sex_restriction === "" ? "selected" : ""
                        }>No Restriction</option>
                        <option value="male" ${
                            room.sex_restriction === "male" ? "selected" : ""
                        }>Male</option>
                        <option value="female" ${
                            room.sex_restriction === "female" ? "selected" : ""
                        }>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-center">
                ${
                    isEditable
                        ? ""
                        : '<i class="fa-solid fa-lock text-muted"></i>'
                }
            </div>
        </div>
    </div>`;
}

function validateRoomForm($form) {
    let isValid = true;
    const roomName = $form.find(".room-name").val().trim();
    const roomCapacity = $form.find(".room-capacity").val().trim();
    const roomSexRestriction = $form.find(".room-sex-restriction").val();

    // Reset previous validation states
    $form.find(".is-invalid").removeClass("is-invalid");
    $form.find(".invalid-feedback").text("");

    // Validate room name
    if (!roomName) {
        $form.find(".room-name").addClass("is-invalid");
        $form.find(".room-name-error").text("Room name is required");
        isValid = false;
    }

    // Validate capacity
    if (!roomCapacity || isNaN(roomCapacity) || parseInt(roomCapacity) <= 0) {
        $form.find(".room-capacity").addClass("is-invalid");
        $form
            .find(".room-capacity-error")
            .text("Please enter a valid capacity");
        isValid = false;
    }

    return {
        isValid,
        roomName,
        roomCapacity: parseInt(roomCapacity),
        roomSexRestriction,
    };
}

function saveMultipleRooms() {
    let newRooms = [];
    let hasDuplicate = false;

    // First, validate all forms and collect valid rooms
    $(".room-form-card").each(function () {
        const $form = $(this);
        const validation = validateRoomForm($form);

        if (!validation.isValid) {
            return false; // Stop processing if form is invalid
        }

        // Check for duplicate room name + sex restriction combination
        const isDuplicate = rooms.some(
            (room) =>
                room.room_name.toLowerCase() ===
                    validation.roomName.toLowerCase() &&
                room.sex_restriction === validation.roomSexRestriction
        );

        if (isDuplicate) {
            hasDuplicate = true;
            $form.find(".room-name").addClass("is-invalid");
            $form
                .find(".room-name-error")
                .text(
                    `A room with the name "${
                        validation.roomName
                    }" and sex restriction "${
                        validation.roomSexRestriction || "No Restriction"
                    }" already exists`
                );
            return false;
        }

        newRooms.push({
            room_name: validation.roomName,
            capacity: validation.roomCapacity,
            sex_restriction: validation.roomSexRestriction,
            facility_id: window.facilityId,
            selected: false,
        });
    });

    // If there are any validation errors or duplicates, don't proceed
    if (!newRooms.length || hasDuplicate) {
        return;
    }

    // Add valid rooms and update UI
    rooms.push(...newRooms);
    updateUI();
    $("#roomFormContainer").empty();
    $("#addMultipleRoomsModal").modal("hide");
}

function editRoom(index) {
    isEditMode = true;
    editRoomIndex = index;
    const room = rooms[index];

    $("#roomFormContainer").empty();
    const $form = createEditRoomFormCard(room, index, true);
    $("#roomFormContainer").append($form);

    // Hide the "Add Another Room" button in edit mode
    $("#addMultipleRoomsRowBtn").hide();

    $("#saveMultipleRoomsBtn").text("Update Room");
    $("#addMultipleRoomsModal").modal("show");
}

function updateEditedRoom() {
    const $form = $(".room-form-card");
    const validation = validateRoomForm($form);

    if (!validation.isValid) {
        return;
    }

    // Check for duplicate room name + sex restriction combination (excluding current room)
    const isDuplicate = rooms.some(
        (room, idx) =>
            idx !== editRoomIndex &&
            room.room_name.toLowerCase() ===
                validation.roomName.toLowerCase() &&
            room.sex_restriction === validation.roomSexRestriction
    );

    if (isDuplicate) {
        $form.find(".room-name").addClass("is-invalid");
        $form
            .find(".room-name-error")
            .text(
                `A room with the name "${
                    validation.roomName
                }" and sex restriction "${
                    validation.roomSexRestriction || "No Restriction"
                }" already exists`
            );
        return;
    }

    rooms[editRoomIndex] = {
        ...rooms[editRoomIndex],
        room_name: validation.roomName,
        capacity: validation.roomCapacity,
        sex_restriction: validation.roomSexRestriction,
    };

    isEditMode = false;
    editRoomIndex = null;
    updateUI();
    $("#roomFormContainer").empty();
    $("#addMultipleRoomsModal").modal("hide");
    $("#saveMultipleRoomsBtn").text("Save All");
    $("#addMultipleRoomsRowBtn").show();
}

function deleteRoom(index) {
    if (confirm("Are you sure you want to delete this room?")) {
        rooms.splice(index, 1);
        updateUI();
    }
}

function updateUI() {
    renderRoomList();
    updateHiddenRooms();
}

function updateHiddenRooms() {
    const hiddenRooms = $("#hiddenRooms");
    hiddenRooms.empty();

    rooms.forEach((room, index) => {
        hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][room_name]`,
                room.room_name
            )
        );
        hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][capacity]`,
                room.capacity
            )
        );
        hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][sex_restriction]`,
                room.sex_restriction
            )
        );
    });
}

function createHiddenInput(name, value) {
    return `<input type="hidden" name="${name}" value="${value}">`;
}

function updateSelectAllCheckbox() {
    const totalRooms = rooms.length;
    const selectedRooms = rooms.filter((room) => room.selected).length;
    $("#selectAllRooms").prop(
        "checked",
        totalRooms > 0 && totalRooms === selectedRooms
    );
}

function toggleBulkActionButtons() {
    const hasSelectedRooms = rooms.some((room) => room.selected);
    $("#bulkEditBtn, #bulkDeleteBtn").toggle(hasSelectedRooms);
}
