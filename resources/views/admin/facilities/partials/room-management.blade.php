<div id="selectionBothType">
    <div class="d-flex align-items-center justify-items-center gap-5">
        <div class="d-flex align-items-center gap-2">
            <input type="radio" id="hasWholeCapacity" name="facility_selection_both" value="whole"
                {{ isset($facility) ? 'disabled' : '' }}
                {{ old('facility_selection_both', $facility->facility_selection_both ?? '') === 'whole' ? 'checked' : '' }}>
            <label for="hasWholeCapacity">Has Whole Capacity?</label>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="radio" id="hasRooms" name="facility_selection_both" value="room"
                {{ isset($facility) ? 'disabled' : '' }}
                {{ old('facility_selection_both', $facility->facility_selection_both ?? '') === 'room' ? 'checked' : '' }}>
            <label for="hasRooms">Has a Room(s)?</label>
        </div>
    </div>

    @if (isset($facility))
        <input type="hidden" name="facility_selection_both" value="{{ $facility->facility_selection_both }}">
    @endif
</div>

<div id="selectionContent" class="mt-4">
    <div class="card" style="border: none;">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-center gap-2">
                <img src="{{ asset('images/choose.svg') }}" alt="no selection" class="img-fluid custom-icon"
                    style="width: 100px; height: 100px; fill: oklch(55.1% 0.027 264.364);">
                <h5 class="card-title">Choose one option to show the content</h5>
            </div>
        </div>
    </div>
</div>

<fieldset class="name" id="hideRoomBox">
    <div class="body-title mb-10">Whole Place Capacity</div>
    <input type="number" min="0" id="roomCapacityWhole" name="whole_capacity" placeholder="Enter whole capacity"
        value="{{ old('whole_capacity',optional($facility->facilityAttributes ?? collect())->whereNotNull('whole_capacity')->first()->whole_capacity ?? '') }}">
</fieldset>

<div id="dormitoryRooms" class="mt-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <h4>Rooms</h4>
        <div class="d-flex gap-2">
            <button type="button" data-bs-toggle="modal" data-bs-target="#addBulkRoomsModal">
                <i class="bi bi-plus-circle"></i> Add Multiple Rooms
            </button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#addMultipleRoomsModal">
                <i class="bi bi-plus-circle"></i> Add Rooms
            </button>
        </div>
    </div>

    <div class="card-header" id="checkAllRooms">
        <div class="d-flex align-items-center justify-items-start gap-2">
            <input type="checkbox" />
            <p>Select All</p>
            <button type="button" id="editSelectedRoomsBtn"
                class="btn btn-lg btn-outline-warning me-2 edit-selected-btn" style="display: none;">
                <i class="icon-pen"></i>
                Edit Selected
            </button>
            <button type="button" id="deleteSelectedRoomsBtn" class="btn btn-lg btn-outline-danger delete-selected-btn"
                style="display: none;">
                <i class="icon-trash"></i>
                Delete Selected
            </button>
        </div>
    </div>

    <div id="noRoomsMessage" class="alert alert-warning">
        <i class="bi bi-info-circle me-2"></i> No rooms added yet:(. Click "Add Rooms" to get started.
    </div>

    <div id="roomContainer" class="mt-4">
        <div class="room-scroll-container">
            <div class="row" id="roomCardsContainer" class="room-scroll-container">
                <!-- Room cards will be rendered here -->
            </div>
        </div>
    </div>

    @include('admin.facilities.partials.modals.add-rooms-modal')
</div>

@if ($errors->has('facility_attributes_json'))
    <span class="alert alert-danger text-center">{{ $errors->first('facility_attributes_json') }}</span>
@endif
