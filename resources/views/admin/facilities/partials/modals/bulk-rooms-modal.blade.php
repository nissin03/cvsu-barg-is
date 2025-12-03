<div class="modal fade" id="addBulkRoomsModal" tabindex="-1" aria-labelledby="addBulkRoomsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBulkRoomsLabel">Add Multiple Rooms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                            <select class="select" id="bulkSexRestriction">
                                <option value="">No Restriction</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBulkRoomsBtn">Save
                    Rooms</button>
            </div>
        </div>
    </div>
</div>
