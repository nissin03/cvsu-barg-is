"use strict";

$(document).ready(function () {
    const $bulkRoomModal = $("#addBulkRoomsModal");
    const $bulkRoomForm = $("#bulkRoomForm");
    const $saveBulkRoomsBtn = $("#saveBulkRoomsBtn");

    // Add select all checkbox and bulk action buttons container
    function addBulkActionButtons() {
        const $bulkActions = $(
            '<div class="bulk-actions mt-3 mb-3" style="display: none;">' +
                '<div class="d-flex align-items-center gap-3">' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox" id="selectAllRooms">' +
                '<label class="form-check-label" for="selectAllRooms">Select All</label>' +
                "</div>" +
                '<div class="bulk-buttons">' +
                '<button type="button" class="btn btn-warning me-2" id="bulkEditBtn" style="display: none;">Edit Selected</button>' +
                '<button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">Delete Selected</button>' +
                "</div>" +
                "</div>" +
                "</div>"
        );

        $("#roomContainer").prepend($bulkActions);

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

        $("#bulkEditBtn").on("click", bulkEditRooms);
        $("#bulkDeleteBtn").on("click", bulkDeleteRooms);
    }

    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        const totalRooms = rooms.length;
        const selectedRooms = rooms.filter((room) => room.selected).length;
        $("#selectAllRooms").prop(
            "checked",
            totalRooms > 0 && totalRooms === selectedRooms
        );
    }

    // Toggle bulk action buttons visibility
    function toggleBulkActionButtons() {
        const hasSelectedRooms = rooms.some((room) => room.selected);
        $("#bulkEditBtn, #bulkDeleteBtn").toggle(hasSelectedRooms);
    }

    // Show/hide bulk actions container
    function toggleBulkActionsContainer() {
        const hasRooms = rooms.length > 0;
        $(".bulk-actions").toggle(hasRooms);
    }

    // Validate start and end numbers
    function validateRoomNumbers(start, end) {
        if (start > end) {
            alert("Start number cannot be greater than end number");
            return false;
        }

        // Check if any room numbers in the range already exist
        const existingRooms = rooms.filter((room) => {
            const roomNumber = parseInt(room.room_name.replace(/[^0-9]/g, ""));
            return (
                !isNaN(roomNumber) && roomNumber >= start && roomNumber <= end
            );
        });

        if (existingRooms.length > 0) {
            alert(
                "Some room numbers in this range already exist. Please choose a different range."
            );
            return false;
        }

        return true;
    }

    // Create bulk rooms
    $saveBulkRoomsBtn.on("click", function () {
        const prefix = $("#roomPrefix").val().trim();
        const start = parseInt($("#startNumber").val());
        const end = parseInt($("#endNumber").val());
        const capacity = parseInt($("#bulkCapacity").val());
        const sexRestriction = $("#bulkSexRestriction").val();

        if (!prefix || isNaN(start) || isNaN(end) || isNaN(capacity)) {
            alert("Please fill in all required fields with valid values.");
            return;
        }

        if (!validateRoomNumbers(start, end)) {
            return;
        }

        const newRooms = [];
        const timestamp = Date.now();
        for (let i = start; i <= end; i++) {
            const uniqueId = `${timestamp}_${Math.random()
                .toString(36)
                .substr(2, 9)}`;
            newRooms.push({
                room_name: `${prefix} ${i}`,
                capacity: capacity,
                sex_restriction: sexRestriction,
                facility_id: window.facilityId,
                unique_id: uniqueId,
                selected: false,
            });
        }

        // Add new rooms to the global rooms array
        rooms.push(...newRooms);
        updateUI();
        toggleBulkActionsContainer();
        $bulkRoomModal.modal("hide");
        $bulkRoomForm[0].reset();
    });

    // Bulk edit functionality
    function bulkEditRooms() {
        const selectedRooms = rooms.filter((room) => room.selected);
        if (selectedRooms.length === 0) {
            alert("Please select rooms to edit");
            return;
        }

        // Show edit modal with pre-filled values
        $("#roomPrefix").val(
            selectedRooms[0].room_name.replace(/[0-9]/g, "").trim()
        );
        $("#bulkCapacity").val(selectedRooms[0].capacity);
        $("#bulkSexRestriction").val(selectedRooms[0].sex_restriction);

        // Change save button to update mode
        $saveBulkRoomsBtn
            .text("Update Rooms")
            .off("click")
            .on("click", function () {
                const newCapacity = parseInt($("#bulkCapacity").val());
                const newSexRestriction = $("#bulkSexRestriction").val();

                // Update selected rooms
                rooms = rooms.map((room) => {
                    if (room.selected) {
                        return {
                            ...room,
                            capacity: newCapacity,
                            sex_restriction: newSexRestriction,
                            facility_id: window.facilityId,
                        };
                    }
                    return room;
                });

                updateUI();
                $bulkRoomModal.modal("hide");
                $bulkRoomForm[0].reset();
                $saveBulkRoomsBtn
                    .text("Save Rooms")
                    .off("click")
                    .on("click", saveBulkRooms);
            });

        $bulkRoomModal.modal("show");
    }

    // Bulk delete functionality
    function bulkDeleteRooms() {
        const selectedRooms = rooms.filter((room) => room.selected);
        if (selectedRooms.length === 0) {
            alert("Please select rooms to delete");
            return;
        }

        if (
            confirm(
                `Are you sure you want to delete ${selectedRooms.length} rooms?`
            )
        ) {
            rooms = rooms.filter((room) => !room.selected);
            updateUI();
            toggleBulkActionsContainer();
            updateSelectAllCheckbox();
        }
    }

    // Add checkbox to room cards
    function addCheckboxToRoomCard(room, index) {
        return `
            <div class="form-check">
                <input class="form-check-input room-checkbox" type="checkbox"
                    data-index="${index}" ${room.selected ? "checked" : ""}>
            </div>
        `;
    }

    // Update the createRoomCard function to include checkbox
    const originalCreateRoomCard = window.createRoomCard;
    window.createRoomCard = function (room, index) {
        const card = originalCreateRoomCard(room, index);
        const $card = $(card);
        $card.prepend(addCheckboxToRoomCard(room, index));
        return $card[0].outerHTML;
    };

    // Initialize bulk actions
    addBulkActionButtons();
});
