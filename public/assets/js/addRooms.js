'use strict';

const $roomList = $("#roomList");
const $hiddenRooms = $("#hiddenRooms");
const $addRoomModal = $("#addMultipleRoomsModal");
const $roomCardsContainer = $("#roomCardsContainer");

// // Initialize rooms array if it doesn't exist
// const rooms = window.rooms || [];

renderRoomList();

// Add room row in the modal
$("#addMultipleRoomsRowBtn").on("click", function (e) {
    e.preventDefault();
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

// Save multiple rooms
$("#saveMultipleRoomsBtn").on("click", function (e) {
    e.preventDefault();
    saveMultipleRooms();
});

function saveMultipleRooms() {
    let valid = true;
    let newRooms = [];

    $(".room-form-card").each(function () {
        const roomName = $(this).find(".room-name").val().trim();
        const roomCapacity = $(this).find(".room-capacity").val().trim();
        const roomSexRestriction = $(this).find(".room-sex-restriction").val();

        if (!roomName || !roomCapacity || isNaN(roomCapacity) || parseInt(roomCapacity) <= 0) {
            valid = false;
            return false;
        }

        newRooms.push({
            room_name: roomName,
            capacity: parseInt(roomCapacity),
            sex_restriction: roomSexRestriction
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
                    <span class="badge bg-info">${room.sex_restriction}</span>
                    <p class="fw-bold">Capacity: <span class="badge bg-warning">${room.capacity}</span></p>
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
    `;
}

function updateUI() {
    renderRoomList();
    updateHiddenRooms();
    calculateTotalCapacity();
}

// Edit room
$(document).on("click", ".edit-room", function () {
    const index = $(this).data("index");
    editRoom(index);
});

function editRoom(index) {
    const room = rooms[index];

    // Clear existing forms and add a single form with the room's data
    $("#roomFormContainer").empty().append(createRoomFormCard());

    // Fill the form with room data
    const $form = $(".room-form-card").first();
    $form.find(".room-name").val(room.room_name);
    $form.find(".room-capacity").val(room.capacity);
    $form.find(".room-sex-restriction").val(room.sex_restriction);

    // Change the save button to update mode
    $("#saveMultipleRoomsBtn").text("Update Room").off("click").on("click", function () {
        const updatedRoom = {
            room_name: $form.find(".room-name").val().trim(),
            capacity: parseInt($form.find(".room-capacity").val().trim()),
            sex_restriction: $form.find(".room-sex-restriction").val()
        };

        // Validate
        if (!updatedRoom.room_name || isNaN(updatedRoom.capacity) || updatedRoom.capacity <= 0) {
            alert("Please enter valid room details.");
            return;
        }

        // Update the room
        rooms[index] = updatedRoom;
        updateUI();
        $("#roomFormContainer").empty();
        $addRoomModal.modal("hide");

        // Reset the button
        $("#saveMultipleRoomsBtn").text("Save All").off("click").on("click", saveMultipleRooms);
    });

    // Show the modal
    $addRoomModal.modal("show");
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
        $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][room_name]`, room.room_name));
        $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][sex_restriction]`, room.sex_restriction));
        $hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][capacity]`, room.capacity));
    });
}

function createHiddenInput(name, value) {
    return `<input type="hidden" name="${name}" value="${value}">`;
}

// Initialize on document ready
$(document).ready(function () {
    // Add at least one form card when opening the modal
    $("#addMultipleRoomsModal").on("show.bs.modal", function () {
        if ($("#roomFormContainer").children().length === 0) {
            $("#roomFormContainer").append(createRoomFormCard());
        }
    });

    // Reset the save button when opening the modal for new rooms
    $("#addMultipleRoomsModal").on("show.bs.modal", function (e) {
        // If not triggered by an edit button
        if (!$(e.relatedTarget).hasClass("edit-room")) {
            $("#saveMultipleRoomsBtn").text("Save All").off("click").on("click", saveMultipleRooms);
        }
    });

});