"use strict";

const $roomList = $("#roomList");
const $hiddenRooms = $("#hiddenRooms");
const $addRoomModal = $("#addMultipleRoomsModal");
const $roomCardsContainer = $("#roomCardsContainer");

// Initialize rooms array if it doesn't exist
// const rooms = window.rooms || [];
let isEditMode = false;
let editRoomIndex = null;
let isAddingRoom = false; // New flag to track if we're adding a new room

renderRoomList();

// Add room row in the modal
$("#addMultipleRoomsRowBtn").on("click", function (e) {
    e.preventDefault();
    isAddingRoom = true; // Set flag when adding new room
    $("#roomFormContainer").append(createRoomFormCard());
});

function createRoomFormCard() {
    return `
    <div class="room-form-card mb-3 p-3 border rounded">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control room-name" placeholder="Enter room name">
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity">
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
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-lg btn-outline-danger removeRoomBtn">
                   <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>`;
}

// Remove room form
$(document).on("click", ".removeRoomBtn", function () {
    $(this).closest(".room-form-card").remove();
});

// Save multiple rooms - Fixed the duplicate function call
$("#saveMultipleRoomsBtn").on("click", function (e) {
    e.preventDefault();
    if (isEditMode) {
        updateEditedRoom();
    } else {
        saveMultipleRooms();
    }
});

function updateEditedRoom() {
    let updatedRooms = [];
    let valid = true;
    let duplicateNames = [];

    // Collect all room updates
    $(".room-form-card").each(function () {
        const roomIndex = parseInt($(this).data("room-index"));
        const isDisabled = $(this).find(".room-name").prop("disabled");

        if (!isDisabled) {
            // Only process enabled rooms
            const updatedRoom = {
                room_name: $(this).find(".room-name").val().trim(),
                capacity: parseInt($(this).find(".room-capacity").val().trim()),
                sex_restriction: $(this).find(".room-sex-restriction").val(),
                originalIndex: roomIndex,
            };

            if (
                !updatedRoom.room_name ||
                isNaN(updatedRoom.capacity) ||
                updatedRoom.capacity <= 0
            ) {
                valid = false;
                return false;
            }

            updatedRooms.push(updatedRoom);
        }
    });

    if (!valid) {
        alert("Please enter valid room details for all enabled rooms.");
        return;
    }

    // Check for duplicate names within the updated rooms and against existing rooms
    for (let i = 0; i < updatedRooms.length; i++) {
        const currentRoom = updatedRooms[i];

        // Check against other updated rooms
        for (let j = i + 1; j < updatedRooms.length; j++) {
            if (
                currentRoom.room_name.toLowerCase() ===
                updatedRooms[j].room_name.toLowerCase()
            ) {
                duplicateNames.push(currentRoom.room_name);
            }
        }

        // Check against existing rooms (excluding the ones being updated)
        const isDuplicate = rooms.some(
            (room, index) =>
                !updatedRooms.some((ur) => ur.originalIndex === index) &&
                room.room_name.toLowerCase() ===
                    currentRoom.room_name.toLowerCase()
        );

        if (isDuplicate) {
            duplicateNames.push(currentRoom.room_name);
        }
    }

    if (duplicateNames.length > 0) {
        alert(
            `Duplicate room names found: ${[...new Set(duplicateNames)].join(
                ", "
            )}. Please choose different names.`
        );
        return;
    }

    // Apply updates to the rooms array
    updatedRooms.forEach((updatedRoom) => {
        rooms[updatedRoom.originalIndex] = {
            room_name: updatedRoom.room_name,
            capacity: updatedRoom.capacity,
            sex_restriction: updatedRoom.sex_restriction,
            selected: rooms[updatedRoom.originalIndex].selected || false,
        };
    });

    // Reset flags
    isEditMode = false;
    editRoomIndex = null;
    isAddingRoom = false;

    updateUI();
    $("#roomFormContainer").empty();
    $addRoomModal.modal("hide");
    $("#saveMultipleRoomsBtn").text("Save All");
}

function saveMultipleRooms() {
    let valid = true;
    let newRooms = [];

    $(".room-form-card").each(function () {
        const roomName = $(this).find(".room-name").val().trim();
        const roomCapacity = $(this).find(".room-capacity").val().trim();
        const roomSexRestriction = $(this).find(".room-sex-restriction").val();

        if (
            !roomName ||
            !roomCapacity ||
            isNaN(roomCapacity) ||
            parseInt(roomCapacity) <= 0
        ) {
            valid = false;
            return false;
        }

        // Check for duplicate room names
        const isDuplicate = rooms.some(
            (room) => room.room_name.toLowerCase() === roomName.toLowerCase()
        );

        if (isDuplicate) {
            alert(
                `A room with the name "${roomName}" already exists. Please choose a different name.`
            );
            valid = false;
            return false;
        }

        newRooms.push({
            room_name: roomName,
            capacity: parseInt(roomCapacity),
            sex_restriction: roomSexRestriction,
            selected: false, // Initialize selected property for bulk operations
        });
    });

    if (!valid) {
        alert("Please ensure all rooms have valid inputs.");
        return;
    }

    rooms.push(...newRooms);
    updateUI();
    $("#roomFormContainer").empty();
    $addRoomModal.modal("hide");

    // Reset the adding flag
    isAddingRoom = false;
}

function renderRoomList() {
    $roomList.empty();

    // Show/hide the "No rooms" message
    if (rooms.length === 0) {
        $("#noRoomsMessage").show();
        $("#roomContainer").hide();
    } else {
        $("#noRoomsMessage").hide();
        $("#roomContainer").show();

        // Update room cards
        $roomCardsContainer.empty();
        rooms.forEach((room, index) => {
            $roomCardsContainer.append(createRoomCard(room, index));
        });
    }
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
            <div>
                <h4 class="pe-2">${room.room_name}</h4>
                <span class="badge ${badgeClass}">${restrictionText}</span>
                <p class="fw-bold">Capacity: <span class="badge bg-warning">${room.capacity}</span></p>
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

function updateUI() {
    renderRoomList();
    updateHiddenRooms();
    // Update bulk actions if they exist
    if (typeof toggleBulkActionsContainer === "function") {
        toggleBulkActionsContainer();
    }
}

// Edit room - Show all rooms with toggle functionality
$(document).on("click", ".edit-room", function () {
    const index = $(this).data("index");
    editRoom(index);
});

function editRoom(index) {
    isEditMode = true;
    editRoomIndex = index;
    isAddingRoom = false; // Not adding, we're editing

    $("#roomFormContainer").empty();

    // Add toggle button for editing all rooms
    const toggleButton = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Edit Room: ${rooms[index].room_name}</h5>
            <button type="button" id="toggleEditAllBtn" class="btn btn-outline-primary">
                <i class="fa-solid fa-edit"></i> Edit All Rooms
            </button>
        </div>
    `;
    $("#roomFormContainer").append(toggleButton);

    // Create form cards for all rooms but initially hide them
    rooms.forEach((room, roomIndex) => {
        const formCard = createEditRoomFormCard(
            room,
            roomIndex,
            roomIndex === index
        );
        const $formCard = $(formCard);
        $("#roomFormContainer").append($formCard);

        // Hide all rooms except the target room initially
        if (roomIndex !== index) {
            $formCard.hide();
        }
    });

    // Handle toggle edit all button
    $("#toggleEditAllBtn").on("click", function () {
        const isEditingAll = $(this).hasClass("btn-primary");

        if (isEditingAll) {
            // Switch back to single room edit
            $(this)
                .removeClass("btn-primary")
                .addClass("btn-outline-primary")
                .html('<i class="fa-solid fa-edit"></i> Edit All Rooms');

            // Hide all other room forms
            $(".room-form-card").each(function () {
                const cardIndex = $(this).data("room-index");
                if (cardIndex === index) {
                    $(this).show();
                    $(this).find("input, select").prop("disabled", false);
                    $(this)
                        .removeClass("border-secondary")
                        .addClass("border-primary");
                } else {
                    $(this).hide();
                    $(this).find("input, select").prop("disabled", true);
                    $(this)
                        .removeClass("border-primary")
                        .addClass("border-secondary");
                }
            });
        } else {
            // Switch to edit all rooms
            $(this)
                .removeClass("btn-outline-primary")
                .addClass("btn-primary")
                .html('<i class="fa-solid fa-lock-open"></i> Cancel Edit All');

            // Show all room forms
            $(".room-form-card").each(function () {
                $(this).show();
                $(this).find("input, select").prop("disabled", false);
                $(this)
                    .removeClass("border-secondary")
                    .addClass("border-primary");
            });
        }
    });

    // Hide the "Add Another Room" button in edit mode
    $("#addMultipleRoomsRowBtn").hide();

    // Change the save button text
    $("#saveMultipleRoomsBtn").text("Update Room(s)");

    // Show the modal
    $addRoomModal.modal("show");
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

// Delete room
$(document).on("click", ".delete-room", function () {
    const index = $(this).data("index");
    if (confirm("Are you sure you want to delete this room?")) {
        rooms.splice(index, 1);
        updateUI();
    }
});

function updateHiddenRooms() {
    $hiddenRooms.empty();
    rooms.forEach((room, index) => {
        $hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][room_name]`,
                room.room_name
            )
        );
        $hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][sex_restriction]`,
                room.sex_restriction
            )
        );
        $hiddenRooms.append(
            createHiddenInput(
                `facility_attributes[${index}][capacity]`,
                room.capacity
            )
        );
    });
}

function createHiddenInput(name, value) {
    return `<input type="hidden" name="${name}" value="${value}">`;
}

// Initialize on document ready
$(document).ready(function () {
    // Add at least one form card when opening the modal for new rooms
    $("#addMultipleRoomsModal").on("show.bs.modal", function (e) {
        // Check if this is not an edit operation
        if (!isEditMode) {
            if ($("#roomFormContainer").children().length === 0) {
                $("#roomFormContainer").append(createRoomFormCard());
                isAddingRoom = false; // Reset the flag
            }
        }
    });

    // Reset everything when modal is hidden
    $("#addMultipleRoomsModal").on("hidden.bs.modal", function () {
        if (!isEditMode) {
            $("#roomFormContainer").empty();
        } else {
            // Reset edit mode when modal is closed
            isEditMode = false;
            editRoomIndex = null;
            $("#roomFormContainer").empty();
        }
        isAddingRoom = false;
        $("#saveMultipleRoomsBtn").text("Save All");
    });
});
