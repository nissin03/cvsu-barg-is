$(document).ready(function () {
    renderRoomList(); // Initial render of room list

    // Adding a new room row in the modal
    $("#addMultipleRoomsRowBtn").on("click", function (e) {
        e.preventDefault();
        $("#roomFormContainer").append(createRoomFormCard());
    });

    // Save button functionality for both create and edit
    $("#saveMultipleRoomsBtn").on("click", function (e) {
        e.preventDefault();
        saveMultipleRooms();
    });

    // Handle the room edit functionality
    $(document).on("click", ".edit-room", function () {
        const index = $(this).data("index");
        editRoom(index);
    });

    // Handle deleting a room
    $(document).on("click", ".delete-room", function () {
        const index = $(this).data("index");
        deleteRoom(index);
    });

});

function renderRoomList() {
    $("#roomCardsContainer").empty();
    rooms.forEach((room, index) => {
        const roomCard = `
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
        </div>
    `;
        $("#roomCardsContainer").append(roomCard);
    });
}

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
            <select class="room-sex-restriction">
                <option value="">No Restriction</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-lg btn-outline-danger removeRoomBtn">
               <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
</div>`;
}

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
    $("#addMultipleRoomsModal").modal("hide");
}

function editRoom(index) {
    const room = rooms[index];
    const $form = createRoomFormCard();
    $("#roomFormContainer").empty().append($form);

    $(".room-name").val(room.room_name);
    $(".room-capacity").val(room.capacity);
    $(".room-sex-restriction").val(room.sex_restriction);

    // Update save button for editing
    $("#saveMultipleRoomsBtn").text("Update Room").off("click").on("click", function () {
        rooms[index] = {
            room_name: $(".room-name").val(),
            capacity: parseInt($(".room-capacity").val()),
            sex_restriction: $(".room-sex-restriction").val()
        };
        updateUI();
        $("#roomFormContainer").empty();
        $("#addMultipleRoomsModal").modal("hide");
    });

    $("#addMultipleRoomsModal").modal("show");
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
        hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][room_name]`, room.room_name));
        hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][capacity]`, room.capacity));
        hiddenRooms.append(createHiddenInput(`facility_attributes[${index}][sex_restriction]`, room
            .sex_restriction));
    });
}

function createHiddenInput(name, value) {
    return `<input type="hidden" name="${name}" value="${value}">`;
}

