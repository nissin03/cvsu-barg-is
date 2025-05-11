"use strict";

// Define key elements as constants for better access
const $roomList = $("#roomList");
const $hiddenRooms = $("#hiddenRooms");
const $addRoomModal = $("#addMultipleRoomsModal");
const $roomCardsContainer = $("#roomCardsContainer");
const $roomFormContainer = $("#roomFormContainer");

// Flag to track whether we're in edit mode or add mode
let isEditMode = false;
let editingIndices = [];

$(document).ready(function () {
    renderRoomList(); // Initial render of room list

    // Adding a new room row in the modal when in "add new" mode
    $("#addMultipleRoomsRowBtn").on("click", function (e) {
        e.preventDefault();
        if (!isEditMode) {
            // In add mode, we append empty form cards
            $roomFormContainer.append(createRoomFormCard());
        } else {
            // In edit mode, we add a new empty form with a unique identifier
            const newIndex = rooms.length;
            const $newForm = $(createRoomFormCard(null, "new-" + newIndex));
            $newForm.addClass("new-room");
            $roomFormContainer.append($newForm);
        }
    });

    // Save button functionality
    $("#saveMultipleRoomsBtn").on("click", function (e) {
        e.preventDefault();
        if (isEditMode) {
            updateRooms();
        } else {
            addNewRooms();
        }
    });

    // Handle room edit functionality
    $(document).on("click", ".edit-room", function () {
        const index = $(this).data("index");
        // Get all indices to show all rooms, but highlight this one
        const allIndices = Array.from({ length: rooms.length }, (_, i) => i);
        openEditModal(allIndices, index); // Show all, highlight the one clicked
    });

    // Handle edit all rooms functionality
    $("#editAllRoomsBtn").on("click", function (e) {
        e.preventDefault();
        // Get all indices of existing rooms
        const allIndices = Array.from({ length: rooms.length }, (_, i) => i);
        openEditModal(allIndices); // Edit all rooms
    });

    // Handle deleting a room
    $(document).on("click", ".delete-room", function () {
        const index = $(this).data("index");
        deleteRoom(index);
    });

    // Remove room form
    $(document).on("click", ".removeRoomBtn", function () {
        const $form = $(this).closest(".room-form-card");
        const index = $form.attr("data-room-index");

        // If this is an existing room in edit mode, confirm before removing
        if (isEditMode && !$form.hasClass("new-room") && index !== undefined) {
            if (confirm("Are you sure you want to delete this room?")) {
                $form.remove();
            }
        } else {
            // This is a new room form, just remove it
            $form.remove();
        }
    });

    // Reset modal state when it's closed
    $addRoomModal.on("hidden.bs.modal", function () {
        resetModalState();
    });

    // Initialize modal properly depending on the entry point
    $addRoomModal.on("show.bs.modal", function (e) {
        // If it's being opened directly (not from an edit button) and empty
        if (
            !$(e.relatedTarget).hasClass("edit-room") &&
            !$(e.relatedTarget).attr("id") === "editAllRoomsBtn" &&
            $roomFormContainer.children().length === 0
        ) {
            // This is "add new" mode
            isEditMode = false;
            $roomFormContainer.append(createRoomFormCard());
            $("#saveMultipleRoomsBtn").text("Save All");
        }
    });

    // Add styles for highlighted rooms being edited
    $("<style>")
        .prop("type", "text/css")
        .html(
            `
            .room-form-card.editing-highlight {
                border-color: #ffc107 !important;
                box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
            }
            .room-form-card.new-room {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            }
        `
        )
        .appendTo("head");
});

function resetModalState() {
    isEditMode = false;
    editingIndices = [];
    $roomFormContainer.empty();
    $("#saveMultipleRoomsBtn").text("Save All");
}

function renderRoomList() {
    // Show/hide the "No rooms" message
    if (rooms.length === 0) {
        $("#noRoomsMessage").show();
        $("#roomContainer").hide();
        $("#editAllRoomsBtn").hide(); // Hide edit all button when no rooms
    } else {
        $("#noRoomsMessage").hide();
        $("#roomContainer").show();
        $("#editAllRoomsBtn").show(); // Show edit all button when rooms exist

        // Clear and rebuild room cards
        $roomCardsContainer.empty();
        rooms.forEach((room, index) => {
            $roomCardsContainer.append(createRoomCard(room, index));
        });
    }
}

function createRoomCard(room, index) {
    // Determine badge color based on sex restriction
    let badgeClass = "bg-secondary";
    if (room.sex_restriction === "male") {
        badgeClass = "bg-primary";
    } else if (room.sex_restriction === "female") {
        badgeClass = "bg-danger";
    }

    return `
    <div class="card p-3 mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="pe-2">${room.room_name}</h4>
                <span class="badge ${badgeClass}">${
        room.sex_restriction || "No Restriction"
    }</span>
                <p class="fw-bold">Capacity: <span class="badge bg-warning">${
                    room.capacity
                }</span></p>
            </div>
            <div class="d-flex">
                <button type="button" class="btn btn-lg btn-outline-warning me-2 edit-room" data-index="${index}">
                    <i class="icon-pen">Edit</i>
                </button>
                <button type="button" class="btn btn-lg btn-outline-danger delete-room" data-index="${index}">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    </div>
    `;
}

function createRoomFormCard(room = null, index = null) {
    // Pre-fill values if room is provided
    const roomName = room ? room.room_name : "";
    const capacity = room ? room.capacity : "";
    const sexRestriction = room ? room.sex_restriction : "";

    // Add data-index attribute if index is provided
    const indexAttr = index !== null ? `data-room-index="${index}"` : "";

    return `
    <div class="room-form-card mb-3 p-3 border rounded" ${indexAttr}>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Room Name</label>
                <input type="text" class="form-control room-name" placeholder="Enter room name" value="${roomName}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity" value="${capacity}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sex Restriction</label>
                <div class="select">
                    <select class="room-sex-restriction">
                        <option value="" ${
                            sexRestriction === "" ? "selected" : ""
                        }>No Restriction</option>
                        <option value="male" ${
                            sexRestriction === "male" ? "selected" : ""
                        }>Male</option>
                        <option value="female" ${
                            sexRestriction === "female" ? "selected" : ""
                        }>Female</option>
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

function openEditModal(indices, highlightIndex = null) {
    // Set edit mode flag
    isEditMode = true;
    editingIndices = indices;

    // Clear existing forms
    $roomFormContainer.empty();

    // Add form for all rooms
    rooms.forEach((room, index) => {
        const roomForm = createRoomFormCard(room, index);
        const $form = $(roomForm);

        // Highlight the specific room being edited if specified
        if (highlightIndex !== null && index === highlightIndex) {
            $form.addClass("editing-highlight");
        }

        $roomFormContainer.append($form);
    });

    // Update button text
    $("#saveMultipleRoomsBtn").text("Update Rooms");

    // Show the modal
    $addRoomModal.modal("show");
}

function addNewRooms() {
    let valid = true;
    let newRooms = [];

    // Collect data from all form cards
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

        newRooms.push({
            room_name: roomName,
            capacity: parseInt(roomCapacity),
            sex_restriction: roomSexRestriction,
        });
    });

    if (!valid) {
        alert("Please ensure all rooms have valid inputs.");
        return;
    }

    // Add these as new rooms
    rooms.push(...newRooms);

    // Update UI and close modal
    updateUI();
    $roomFormContainer.empty();
    $addRoomModal.modal("hide");
}

function updateRooms() {
    let valid = true;
    let newRoomsList = [];

    // Collect updated data from all form cards
    $(".room-form-card").each(function () {
        const $form = $(this);
        const index =
            $form.attr("data-room-index") !== undefined
                ? parseInt($form.attr("data-room-index"))
                : null;

        const roomName = $form.find(".room-name").val().trim();
        const roomCapacity = $form.find(".room-capacity").val().trim();
        const roomSexRestriction = $form.find(".room-sex-restriction").val();

        if (
            !roomName ||
            !roomCapacity ||
            isNaN(roomCapacity) ||
            parseInt(roomCapacity) <= 0
        ) {
            valid = false;
            return false;
        }

        // Create the updated room object
        const updatedRoom = {
            room_name: roomName,
            capacity: parseInt(roomCapacity),
            sex_restriction: roomSexRestriction,
        };

        // Add to our temporary list with its position
        newRoomsList.push({
            index: index,
            room: updatedRoom,
        });
    });

    if (!valid) {
        alert("Please ensure all rooms have valid inputs.");
        return;
    }

    // Sort by index to maintain order
    newRoomsList.sort((a, b) => a.index - b.index);

    // Replace the entire rooms array with the new list (maintaining existing IDs)
    rooms = newRoomsList.map((item) => item.room);

    // Update UI and close modal
    updateUI();
    $roomFormContainer.empty();
    $addRoomModal.modal("hide");
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
                `facility_attributes[${index}][capacity]`,
                room.capacity
            )
        );
        $hiddenRooms.append(
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
