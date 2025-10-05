  <div class="modal fade" id="addMultipleRoomsModal" tabindex="-1" aria-labelledby="addMultipleRoomsLabel">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="addMultipleRoomsLabel">Manage Rooms</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                  <div class="room-form-card mb-3 p-3 border rounded">
                      <div class="row g-3">
                          <div class="col-md-4">
                              <label class="form-label">Room Name</label>
                              <input type="text" class="form-control room-name" placeholder="Enter room name">
                          </div>
                          <div class="col-md-3">
                              <label class="form-label">Capacity</label>
                              <input type="number" class="form-control room-capacity" min="1"
                                  placeholder="Enter capacity">
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Sex Restriction</label>
                              <div class="select">
                                  <select class="room-sex-restriction">
                                      <option value="">No Restriction</option>
                                      <option value="male">Male</option>
                                      <option value="female">Female</option>
                                      <option value="all">All</option>
                                  </select>
                              </div>
                          </div>
                      </div>
                  </div>

              </div>

              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="saveMultipleRoomsBtn">Save
                      All</button>
              </div>
          </div>
      </div>
  </div>
