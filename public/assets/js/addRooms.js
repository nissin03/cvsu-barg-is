const $roomList = $("#roomList");
const $hiddenRooms = $("#hiddenRooms");
const $multipleRoomsTable = $("#multipleRoomsTable tbody");
const $addRoomModal = $("#addRoom");


renderRoomList();

// Save single room entry
$("#saveRoomChanges").on("click", function (event) {
    event.preventDefault();
    saveRoom();
});

function saveRoom() {
    const roomName = $("#roomName").val().trim();
    const roomCapacity = $("#roomCapacity").val().trim();
    const roomSexRestriction = $("#roomSexRestriction").val();

    if (!roomName || !roomCapacity || isNaN(roomCapacity) || roomCapacity <= 0) {
        alert("Please enter a valid room name and positive capacity.");
        return;
    }

    rooms.push({
        room_name: roomName,
        capacity: parseInt(roomCapacity),
        sex_restriction: roomSexRestriction,
    });

    updateUI();
}

function renderRoomList() {
    $roomList.empty();
    rooms.forEach((room, index) => {
        $roomList.append(createRoomCard(room, index));
    });
}

function createRoomCard(room, index) {
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
        </div>`;
}

function updateUI() {
    renderRoomList();
    updateHiddenRooms();
    $addRoomModal.modal("hide");
    clearRoomForm();
}

function clearRoomForm() {
    $("#roomName, #roomCapacity").val("");
    $("#roomSexRestriction").val("");
}

$roomList.on("click", ".edit-room", function () {
    const index = $(this).data("index");
    editRoom(index);
});

function editRoom(index) {
    const room = rooms[index];
    $("#roomName").val(room.room_name);
    $("#roomCapacity").val(room.capacity);
    $("#roomSexRestriction").val(room.sex_restriction);

    $("#saveRoomChanges").off("click").on("click", function () {
        rooms[index] = {
            room_name: $("#roomName").val(),
            capacity: parseInt($("#roomCapacity").val()),
            sex_restriction: $("#roomSexRestriction").val(),
        };
        updateUI();
    });

    $addRoomModal.modal("show");
}

$roomList.on("click", ".delete-room", function () {
    const index = $(this).data("index");
    rooms.splice(index, 1);
    updateUI();
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

// Add multiple rooms
$("#addMultipleRoomsRowBtn").on("click", function (e) {
    e.preventDefault();
    $multipleRoomsTable.append(createRoomRow());
});

function createRoomRow() {
    return `
        <tr>
            <td><input type="text" class="form-control room-name" placeholder="Enter room name"></td>
            <td><input type="number" class="form-control room-capacity" min="1" placeholder="Enter capacity"></td>
            <td>
                <select class="form-control room-sex-restriction">
                    <option value="">Choose Sex Restriction...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-danger removeRowBtn">Remove</button></td>
        </tr>`;
}

// Remove multiple room row
$multipleRoomsTable.on("click", ".removeRowBtn", function () {
    $(this).closest("tr").remove();
});

// Save multiple rooms
$("#saveMultipleRoomsBtn").on("click", function (e) {
    e.preventDefault();
    saveMultipleRooms();
});

function saveMultipleRooms() {
    let valid = true;
    let newRooms = [];

    $multipleRoomsTable.find("tr").each(function () {
        const roomName = $(this).find(".room-name").val().trim();
        const roomCapacity = $(this).find(".room-capacity").val().trim();
        const roomSexRestriction = $(this).find(".room-sex-restriction").val();

        if (!roomName || !roomCapacity || isNaN(roomCapacity) || parseInt(roomCapacity) <= 0) {
            valid = false;
            return false;
        }

        newRooms.push({ room_name: roomName, capacity: parseInt(roomCapacity), sex_restriction: roomSexRestriction });
    });

    if (!valid) {
        alert("Please ensure all rows have valid inputs.");
        return;
    }

    rooms = rooms.concat(newRooms);
    updateUI();
    $multipleRoomsTable.empty();
    $("#addMultipleRoomsModal").modal("hide");
}
