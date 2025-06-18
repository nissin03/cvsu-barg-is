@extends('layouts.admin')
@section('content')
    <style>
        .remove-btn:hover {
            background-color: darkred;
        }

        .file-name-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            font-size: 12px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .item {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .upload-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }

        .upload-image {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }

        .uploadfile {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .remove-upload {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(20, 19, 20, 0.8);
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            display: none;
            /* Initially hidden */
        }

        .remove-upload.show {
            display: block;
        }


        /* Room Field Container */
        .room-field-container {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            position: relative;
        }

        .remove-room {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .box {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 4px;
        }
    </style>
    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add Facility</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{ route('admin.facilities.index') }}">
                            <div class="text-tiny">Facility</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add Facility</div>
                    </li>
                </ul>
            </div>
            <!-- form-add-rental -->
            <form action="{{ route('admin.facilities.store') }}" class="tf-section-2 form-add-rental" method="POST"
                enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="facilityAttributesJson" name="facility_attributes_json">
                <input type="hidden" id="pricesJson" name="prices_json">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Facility name <span class="tf-color-1">*</span></div>
                        <input class="form-control" type="text" value="{{ old('name') }}"
                            placeholder="Facility name ..." name="name" tabindex="0" required>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <div class="gap22 cols">
                        <fieldset class="type">
                            <div class="body-title mb-10">Facility Type<span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select id="rentalType" name="facility_type" required>
                                    <option value="" selected disabled>Choose Facility Type...</option>
                                    <option value="individual"
                                        {{ old('facility_type') === 'individual' ? 'selected' : '' }}>
                                        Individual
                                    </option>
                                    <option value="whole_place"
                                        {{ old('facility_type') === 'whole_place' ? 'selected' : '' }}>
                                        Whole Place
                                    </option>
                                    <option value="both" {{ old('facility_type') === 'both' ? 'selected' : '' }}>
                                        Both
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('facility_type')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    {{-- Description --}}
                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
                            required="">{{ old('description') }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <fieldset class="rules_and_regulations">
                        <div class="body-title mb-10">Rules and Regulation <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" id="rules" name="rules_and_regulations" placeholder="rules_and_regulations" tabindex="0"
                            aria-required="true">{{ old('rules_and_regulations') }}</textarea>
                    </fieldset>
                    @error('rules_and_regulations')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                </div>

                <div class="wg-box">
                    <fieldset>
                        <div class="body-title mb-10">Requirements <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="requirementsPreview" style="display:none">
                                <img src="{{ asset('images/upload/upload-1.png') }}" id="requirements-preview-img"
                                    class="effect8" alt="">
                                <button type="button" class="remove-upload"
                                    onclick="removeUpload('requirementsPreview', 'requirementsFile')">Remove</button>
                            </div>
                            <div id="upload-requirements" class="item up-load">
                                <label class="uploadfile" for="requirementsFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Select your Requirements file here or click to browse</span>
                                    <input type="file" id="requirementsFile" name="requirements"
                                        accept=".pdf,.doc,.docx,.jpg,.png">

                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('requirements')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <!-- Image upload -->
                    <fieldset>
                        <div class="body-title">Upload main image <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="imgpreview" style="display:none">
                                <img src="{{ asset('images/upload/upload-1.png') }}" id="preview-img" class="effect8"
                                    alt="">
                                <button type="button" class="remove-upload"
                                    onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
                            </div>
                            <div id="upload-file" class="item up-load">
                                <label class="uploadfile" for="myFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Select your main image here or click to browse</span>
                                    <input type="file" id="myFile" name="image" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('image')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <!-- Gallery images upload -->
                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images</div>
                        <div class="upload-image mb-16 flex-grow" id="gallery-container">
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Select your images here or click to browse</span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                                </label>
                            </div>
                        </div>
                    </fieldset>

                    @error('images')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                </div>
                <div class="wg-box" id="roomBox">
                    <fieldset class="name" id="hideRoomBox">
                        <div class="body-title mb-10">Capacity</div>
                        <input type="number" min="0" id="roomCapacityWhole" name="whole_capacity"
                            placeholder="Enter capacity">
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
                                    class="btn btn-lg btn-outline-warning me-2 edit-selected-btn" style="display: none; ">
                                    <i class="icon-pen"></i>
                                    Edit Selected
                                </button>
                                <button type="button" id="deleteSelectedRoomsBtn"
                                    class="btn btn-lg btn-outline-danger delete-selected-btn" style="display: none; ">
                                    <i class="icon-trash"></i>
                                    Delete Selected
                                </button>
                            </div>
                        </div>

                        <div id="noRoomsMessage" class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i> No rooms added yet:(. Click "Add Rooms" to get started.
                        </div>

                        <div id="roomContainer" class="mt-4">
                            <div class="row" id="roomCardsContainer">
                                <!-- Room cards will be rendered here -->
                            </div>
                        </div>

                        <div class="modal fade" id="addMultipleRoomsModal" tabindex="-1"
                            aria-labelledby="addMultipleRoomsLabel">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addMultipleRoomsLabel">Manage Rooms</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="room-form-card mb-3 p-3 border rounded">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Room Name</label>
                                                    <input type="text" class="form-control room-name"
                                                        placeholder="Enter room name">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Capacity</label>
                                                    <input type="number" class="form-control room-capacity"
                                                        min="1" placeholder="Enter capacity">
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
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveMultipleRoomsBtn">Save
                                            All</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @if ($errors->has('facility_attributes_json'))
                        <span
                            class="alert alert-danger text-center">{{ $errors->first('facility_attributes_json') }}</span>
                    @endif
                </div>

                <div class="wg-box" id="priceBox">
                    <div id="dormitoryFields"
                        class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <h4>Prices</h4>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addPrice">
                            <i class="bi bi-plus-circle"></i> Add Price
                        </button>
                    </div>

                    <p id="noPricesMessage" class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i> No prices added yet :(
                    </p>

                    <div id="priceTypeContainer" style="display: none;">
                        <div class="d-flex align-items-center justify-items-center gap-4 text-white">
                            <div class="box bg-primary"></div>
                            <p>Individual</p>
                            <div class="box bg-warning "></div>
                            <p>Whole Place</p>
                        </div>
                    </div>
                    <div id="priceContainer" class="mt-4">
                        <div class="row" id="priceCardsContainer">
                        </div>
                    </div>
                    @if ($errors->has('prices_json'))
                        <span class="alert alert-danger text-center">{{ $errors->first('prices_json') }}</span>
                    @endif

                    <div class="cols gap10">
                        <button id="facilitySubmitBtn" class="tf-button w-full" type="submit">
                            <span class="btn-text">Create Facility</span>
                        </button>
                    </div>
                </div>

                <!-- Modal for Adding Prices -->
                <div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="addPriceLabel">Add Price</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="priceFormContainer">
                                    <div class="price-form-card mb-3 p-3 border rounded">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Price Name</label>
                                                <input type="text" class="form-control price-name"
                                                    placeholder="Enter price name">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Price</label>
                                                <input type="number" class="form-control price-value" min="1"
                                                    placeholder="Enter price">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Price Type</label>
                                                <select class="price-type">
                                                    <option value="">Choose Price Type</option>
                                                    <option value="individual">Individual</option>
                                                    <option value="whole">Whole Place</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-12 d-flex align-items-center justify-items-center mt-5 gap-5">
                                            <button type="button"
                                                class="btn btn-lg btn-outline-danger removePriceBtn mb-3">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex align-items-end justify-content-end gap-5 px-3"
                                    style="margin-top: 10px;">
                                    <div id="isBasedOnDaysContainer">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" class="is-based-on-days" id="isBasedOnDays">
                                            <label for="is-based-on-days">Is Based on Days?</label>
                                        </div>
                                    </div>
                                    <div id="isThereAQuantityContainer">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" class="is-there-a-quantity" id="isThereAQuantity">
                                            <label for="is-there-a-quantity">Is There a Quantity?</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="dateFieldsContainer" class="row my-4">
                                    <div class="col-md-6">
                                        <label for="date_from">Date From:</label>
                                        <input type="date" id="date_from" name="date_from">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_to">Date To:</label>
                                        <input type="date" id="date_to" name="date_to">
                                    </div>
                                </div>
                                <button type="button" id="addMultiplePricesRowBtn" style="margin-top: 10px;">
                                    <i class="fa-solid fa-plus"></i> Add Another Price
                                </button>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="saveMultiplePricesBtn">Save
                                    All</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <!-- /form-add-rental -->
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
    <div class="modal fade" id="addBulkRoomsModal" tabindex="-1" aria-labelledby="addBulkRoomsLabel"
        aria-hidden="true">
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
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        let rooms = [];
        let prices = [];
        $(document).ready(function() {
            $(document).on('change', '#checkAllRooms input[type="checkbox"]', function() {
                const isChecked = $(this).is(':checked');
                $('.edit-selected-btn, .delete-selected-btn').toggle(isChecked);
                $('.room-checkbox').prop('checked', isChecked ? 1 : 0);
                updateActionVisibility();
            });

            const updateActionVisibility = () => {
                const checkedCount = $('.room-checkbox:checked').length;
                $('#checkAllRooms').toggle(checkedCount >= 1);
                $('.edit-selected-btn, .delete-selected-btn').toggle(checkedCount >= 1);
            }

            const updateSelectAllCheckbox = () => {
                const totalCheckboxes = $('.room-checkbox').length;
                const checkedCheckboxes = $('.room-checkbox:checked').length;
                const selectAllCheckbox = $('#checkAllRooms input[type="checkbox"]');

                if (checkedCheckboxes === 0) {
                    selectAllCheckbox.prop('checked', false).prop('indeterminate', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    selectAllCheckbox.prop('checked', true).prop('indeterminate', false);
                } else {
                    selectAllCheckbox.prop('checked', false).prop('indeterminate', true);
                }
            }

            $(document).on('change', '.room-checkbox', function() {
                updateActionVisibility();
                updateSelectAllCheckbox();
            });

            function renderRoomList() {
                const container = $('#roomCardsContainer').empty();
                if (rooms.length === 0) {
                    $('#noRoomsMessage').show();
                    $('#checkAllRooms').hide();
                    return;
                }
                $('#noRoomsMessage').hide();
                $('#checkAllRooms').show();

                rooms.forEach((room, index) => {
                    const card = $(`
                        <div class="card p-3 mb-3 room-card" data-index="${index}">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="checkbox" class="room-checkbox" data-index="${index}">
                                        <h4 class="pe-2">${room.room_name}</h4>
                                        <span class="badge bg-primary">${room.sex_restriction || 'No Restriction'}</span>
                                    </div>
                                    <p class="fw-bold">Capacity: <span>${room.capacity}</span></p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-warning edit-room-btn" data-index="${index}">
                                        <i class="icon-pen"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-room-btn" data-index="${index}">
                                        <i class="icon-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                    container.append(card);
                });
                $('#facilityAttributesJson').val(JSON.stringify(rooms));
                updateActionVisibility();
                updateSelectAllCheckbox();
                const facilityType = $('#rentalType').val();
                if (facilityType === 'both') {
                    const wholeCapacityField = $('#roomCapacityWhole');
                    if (rooms.length > 0) {
                        wholeCapacityField.prop('disabled', true).val('');
                        $('#roomButtonsMessage').hide();
                    } else {
                        wholeCapacityField.prop('disabled', false);
                    }
                }
            }

            $('#saveMultipleRoomsBtn').on("click", function(e) {
                e.preventDefault();
                const name = $('.room-name').val().trim();
                const capacity = $('.room-capacity').val().trim();
                const sex = $('.room-sex-restriction ').val();

                if (!name || !capacity || !sex) {
                    alert('Name and capacity required.');
                    return;
                }

                rooms.push({
                    room_name: name,
                    capacity: capacity,
                    sex_restriction: sex
                });

                $('#addMultipleRoomsModal').modal('hide');
                $('.room-name, .room-capacity').val('');
                $('.room-sex-restriction').val('');
                renderRoomList();

            });

            $(document).on('click', '.edit-room-btn', function() {
                const index = $(this).data('index');
                const room = rooms[index];

                $('.room-name').val(room.room_name);
                $('.room-capacity').val(room.capacity);
                $('.room-sex-restriction').val(room.sex_restriction);

                $('#addMultipleRoomsModal').modal('show');
                $('#saveMultipleRoomsBtn').off().click(function() {
                    rooms[index] = {
                        room_name: $('.room-name').val(),
                        capacity: $('.room-capacity').val(),
                        sex_restriction: $('.room-sex-restriction').val()
                    };
                    $('#addMultipleRoomsModal').modal('hide');
                    $('.room-name, .room-capacity').val('');
                    $('.room-sex-restriction').val('');
                    renderRoomList();
                });
            });

            $(document).on('click', '.delete-room-btn', function() {
                if (confirm('Are you sure you want to delete this room?')) {
                    const index = $(this).data('index');
                    rooms.splice(index, 1);
                    renderRoomList();
                }
            });


            $('#saveBulkRoomsBtn').on('click', function() {
                const prefix = $('#roomPrefix').val().trim();
                const start = parseInt($('#startNumber').val());
                const end = parseInt($('#endNumber').val());
                const capacity = $('#bulkCapacity').val().trim();
                const sex = $('#bulkSexRestriction').val();

                if (!prefix || !start || !end || !capacity || !sex) {
                    alert('All fields are required.');
                    return;
                }
                if (sex !== 'male' && sex !== 'female') {
                    alert('Sex restriction must be either male or female.');
                    return;
                }

                for (let i = start; i <= end; i++) {
                    rooms.push({
                        room_name: `${prefix}${i}`,
                        capacity: capacity,
                        sex_restriction: sex,
                    });
                }

                $('#addBulkRoomsModal').modal('hide');
                $('#bulkRoomForm')[0].reset();
                renderRoomList();
            });

            const getSelectedRooms = () => {
                return $('.room-checkbox:checked').map(function() {
                    return $(this).data('index');
                }).get();
            };
            $('#deleteSelectedRoomsBtn').on('click', function() {
                if (confirm('Are you sure you want to delete the selected rooms?')) {
                    const selected = getSelectedRooms();

                    if (selected.length === 0) {
                        alert('Please select at least one room to delete.');
                        return;
                    }
                    rooms = rooms.filter((_, index) => !selected.includes(index));
                    renderRoomList();
                }
            });

            $('#editSelectedRoomsBtn').off('click').on('click', function() {
                const selected = getSelectedRooms();
                if (selected.length === 0) {
                    alert('Select at least one room to edit.');
                    return;
                }
                const originalValues = {};
                let modalContent = '';

                selected.forEach(index => {
                    const room = rooms[index];
                    originalValues[index] = {
                        room_name: room.room_name,
                        capacity: room.capacity,
                        sex_restriction: room.sex_restriction || ''
                    };

                    modalContent += `
                    <div class="room-edit-section mb-4 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Room Name</label>
                                <input type="text" class="form-control edit-room-name" data-index="${index}" value="${room.room_name}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" class="form-control edit-room-capacity" data-index="${index}" value="${room.capacity}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sex Restriction</label>
                                <select class=" edit-room-sex-restriction" data-index="${index}">
                                    <option value="">No Restriction</option>
                                    <option value="male" ${room.sex_restriction === 'male' ? 'selected' : ''}>Male</option>
                                    <option value="female" ${room.sex_restriction === 'female' ? 'selected' : ''}>Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
        `;
                });
                const modalBody = $('#addMultipleRoomsModal .modal-body');
                const originalContent = modalBody.html();

                modalBody.html(`
            <div class="bulk-edit-container">
                <h6 class="mb-3">Edit ${selected.length} Selected Room(s)</h6>
                ${modalContent}
            </div>
        `);

                $('#addMultipleRoomsModal').modal('show');
                $('#saveMultipleRoomsBtn').off('click').click(function(e) {
                    e.preventDefault();

                    let hasChanges = false;
                    let hasErrors = false;

                    selected.forEach(index => {
                        const newName = $(`.edit-room-name[data-index="${index}"]`).val()
                            .trim();
                        const newCapacity = $(`.edit-room-capacity[data-index="${index}"]`)
                            .val()
                            .trim();
                        const newSexRestriction = $(
                                `.edit-room-sex-restriction[data-index="${index}"]`)
                            .val();

                        if (!newName || !newCapacity) {
                            hasErrors = true;
                            return;
                        }

                        const original = originalValues[index];

                        if (newName !== original.room_name) {
                            rooms[index].room_name = newName;
                            hasChanges = true;
                        }

                        if (newCapacity !== original.capacity) {
                            rooms[index].capacity = newCapacity;
                            hasChanges = true;
                        }

                        if (newSexRestriction !== original.sex_restriction) {
                            rooms[index].sex_restriction = newSexRestriction;
                            hasChanges = true;
                        }
                    });

                    if (hasErrors) {
                        alert('Name and capacity are required for all rooms.');
                        return;
                    }
                    modalBody.html(originalContent);

                    $('#addMultipleRoomsModal').modal('hide');
                    renderRoomList();

                    if (hasChanges) {
                        alert(`${selected.length} room(s) updated successfully!`);
                    } else {
                        alert('No changes were made.');
                    }
                });
                $('#addMultipleRoomsModal').on('hidden.bs.modal.bulk-edit', function() {
                    modalBody.html(originalContent);
                    $(this).off('hidden.bs.modal.bulk-edit');
                });
            });

            $('#dateFieldsContainer').hide();
            $('#isBasedOnDays').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#dateFieldsContainer').fadeIn(200);
                    let today = new Date().toISOString().split('T')[0];
                    $('#date_from, #date_to').attr('min', today);
                } else {
                    $('#dateFieldsContainer').fadeOut(200);
                    $('#date_from, #date_to').val('');
                }
            });
            $('#date_from').on('change', function() {
                let selectedDate = new Date($(this).val());
                selectedDate.setDate(selectedDate.getDate() + 1);
                let nextDay = selectedDate.toISOString().split('T')[0];

                $('#date_to').val(nextDay);
                $('#date_to').attr('min', nextDay);
            });

            function renderPriceList() {
                const container = $('#priceContainer').empty();
                if (prices.length === 0) {
                    $('#noPricesMessage').show();
                    $('#priceTypeContainer').hide();
                    return;
                }
                $('#noPricesMessage').hide();
                $('#priceTypeContainer').show();

                prices.forEach((price, index) => {
                    let badgeType = price.priceType === 'individual' ? 'bg-primary text-white' :
                        price
                        .priceType ===
                        'whole' ? 'bg-warning text-white' : '';
                    const card = /*HTML*/ $(`
                       <div class="card p-3 mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4>${price.priceName}</h4>
                                    <p><span class="badge ${badgeType}">${price.priceType.charAt(0).toUpperCase() + price.priceType.slice(1)}</span></p>
                                </div>
                                <p class="fw-bold h4">₱ ${price.priceValue}.00</p>
                                  <p>Is Based on Days?: <span class="badge bg-success">${price.isBasedOnDays ? 'Yes' : 'No'}</span></p>
                        <p>Is There a Quantity?: <span class="badge bg-success">${price.isThereAQuantity ? 'Yes' : 'No'}</span></p>
                           ${price.dateFrom && price.isBasedOnDays == '1' ? `<p><i class="fa-solid fa-calendar-alt me-2 text-info"></i> Date From: <span class="">${price.dateFrom}</span></p>` : ''}
                        ${price.dateTo && price.isBasedOnDays == '1' ? `<p><i class="fa-solid fa-calendar-check me-2 text-info"></i> Date To: <span class="">${price.dateTo}</span></p>` : ''}
                            </div>

                    <div class="d-flex">
                            <button type="button" class="btn btn-lg btn-outline-warning me-2 edit-price"
                                data-index="${index}">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <button type="button" class="btn btn-lg btn-outline-danger delete-price"
                                data-index="${index}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        </div>
                    </div>
                `);
                    container.append(card);
                })
                $('#pricesJson').val(JSON.stringify(prices));
            }


            $('#addMultiplePricesRowBtn').on('click', function() {
                let newPriceForm = $('.price-form-card:first').clone();
                newPriceForm.find('input', 'select').val('');
                $('#priceFormContainer').append(newPriceForm);
            });

            $(document).on('click', '.removePriceBtn', function() {
                if ($('.price-form-card').length > 1) {
                    $(this).closest('.price-form-card').remove();
                }
            });

            $('#saveMultiplePricesBtn').on('click', function() {
                $('.price-form-card').each(function() {
                    const priceName = $(this).find('.price-name').val();
                    const priceValue = $(this).find('.price-value').val();
                    const priceType = $(this).find('.price-type').val();
                    const dateFrom = $('#date_from').val();
                    const dateTo = $('#date_to').val();
                    let isBasedOnDays = $('#isBasedOnDays').is(':checked') ? 1 : 0;
                    let isThereAQuantity = $('#isThereAQuantity').is(':checked') ? 1 : 0;
                    if (priceName !== '' && priceValue !== '' && priceType !== '') {
                        prices.push({
                            priceName,
                            priceValue,
                            priceType,
                            isBasedOnDays,
                            isThereAQuantity,
                            dateFrom: dateFrom ? dateFrom : null,
                            dateTo: dateTo ? dateTo : null,
                        });
                    }
                });
                renderPriceList();
                $('.price-form-card').find('input, select').val('');
                $('#isBasedOnDays, #isThereAQuantity').prop('checked', false);
                $('#addPrice').modal('hide');
            });

            $(document).on('click', '.delete-price', function() {
                let index = $(this).data('index');
                prices.splice(index, 1);
                renderPriceList();
            });

            $(document).on('click', '.edit-price', function() {
                let index = $(this).data('index');
                let price = prices[index];

                $('#addPriceLabel').text(`Edit Price: ${price.priceName}`);
                $('.price-name').val(price.priceName);
                $('.price-value').val(price.priceValue);
                $('.price-type').val(price.priceType);

                $('#isBasedOnDays').prop('checked', price.isBasedOnDays === '1');
                $('#isThereAQuantity').prop('checked', price.isThereAQuantity === '1');

                if (price.isBasedOnDays === '1') {
                    $('#dateFieldsContainer').show();
                    $('#date_from').val(price.dateFrom || '');
                    $('#date_to').val(price.dateTo || '');
                } else {
                    $('#dateFieldsContainer').hide();
                    $('#date_from, #date_to').val('');
                }

                $('#addPrice').modal('show');
                $('#saveMultiplePricesBtn').off('click').on('click', function() {
                    prices[index].priceName = $('.price-name').val();
                    prices[index].priceValue = $('.price-value').val();
                    prices[index].priceType = $('.price-type').val();
                    prices[index].isBasedOnDays = $('#isBasedOnDays').is(':checked') ? '1' : '0';
                    prices[index].isThereAQuantity = $('#isThereAQuantity').is(':checked') ? '1' :
                        '0';
                    prices[index].dateFrom = $('#date_from').val() || null;
                    prices[index].dateTo = $('#date_to').val() || null;

                    $('#addPrice').modal('hide');
                    renderPriceList();
                });
                $('#addPrice').on('hidden.bs.modal', function() {
                    $('#priceFormContainer').append(price);
                });
            });

            $(document).on('click', '.delete-price', function() {
                if (confirm('Are you sure you want to delete this price?')) {
                    const index = $(this).data('index');
                    prices.splice(index, 1);
                    renderPriceList();
                }
            });

            renderRoomList();
            renderPriceList();

            $('#roomCapacityWhole').on('input', function() {
                const facilityType = $('#rentalType').val();
                if (facilityType === 'both') {
                    const hasValue = $(this).val().trim() !== '';
                    const addRoomButtons = $('#dormitoryRooms .d-flex.gap-2 button');

                    if (hasValue) {
                        addRoomButtons.prop('disabled', true);
                        $('#roomButtonsMessage').show();
                        $('#noRoomsMessage').hide();
                    } else {
                        addRoomButtons.prop('disabled', false);
                        $('#roomButtonsMessage').hide();
                        if (rooms.length === 0) {
                            $('#noRoomsMessage').show();
                        }
                    }
                }
            });

            $('#rentalType').on('change', function() {
                const facilityType = $(this).val();
                if (facilityType) {
                    $('#roomCapacityWhole').val('');
                    rooms = [];
                    renderRoomList();

                    prices = [];
                    renderPriceList();

                    $('#isBasedOnDays').prop('checked', false);
                    $('#isThereAQuantity').prop('checked', false);

                    $('#date_from, #date_to').val('');
                    $('#dateFieldsContainer').hide();
                    $(".price-type").val('');
                }
            });

            // Form submission handler
            $('.form-add-rental').on('submit', function(e) {
                e.preventDefault();
                const facilityType = $('#rentalType').val();

                if (facilityType === 'both') {
                    const hasRooms = rooms && rooms.length > 0;
                    const hasWholeCapacity = $('#roomCapacityWhole').val() && $('#roomCapacityWhole').val()
                        .trim() !== '';

                    if (!hasRooms && !hasWholeCapacity) {
                        alert(
                            'For "Both" facility type, you must provide either individual rooms OR whole capacity.'
                        );
                        return false;
                    }

                    if (hasRooms) {
                        for (let room of rooms) {
                            if (!room.room_name || !room.capacity || !room.sex_restriction) {
                                alert('All rooms must have a name, capacity, and sex restriction.');
                                return false;
                            }
                        }
                    }
                }

                if (facilityType === 'individual') {
                    if (!rooms || rooms.length === 0) {
                        alert('For "Individual" facility type, you must add at least one room.');
                        return false;
                    }

                    for (let room of rooms) {
                        if (!room.room_name || !room.capacity || !room.sex_restriction) {
                            alert('All rooms must have a name, capacity, and sex restriction.');
                            return false;
                        }
                    }
                }

                if (facilityType === 'whole_place') {
                    const wholeCapacity = $('#roomCapacityWhole').val();
                    if (!wholeCapacity || wholeCapacity.trim() === '') {
                        alert('For "Whole Place" facility type, you must provide a whole capacity.');
                        return false;
                    }
                }

                if (facilityType === 'both') {
                    const hasRooms = rooms && rooms.length > 0;
                    if (hasRooms) {
                        $('#facilityAttributesJson').val(JSON.stringify(rooms));
                    } else {
                        $('#facilityAttributesJson').val(JSON.stringify([]));
                    }
                } else if (facilityType === 'individual') {
                    $('#facilityAttributesJson').val(JSON.stringify(rooms));
                } else if (facilityType === 'whole_place') {
                    $('#facilityAttributesJson').val(JSON.stringify([]));
                }

                $('#pricesJson').val(JSON.stringify(prices));
                this.submit();
            });
        });
    </script>

    <script src="{{ asset('assets/js/hideFields.js') }}"></script>
    <script src="{{ asset('assets/js/imagefile.js') }}"></script>

    <script>
        function removeUpload(previewId, inputId) {
            // Hide the preview block
            $('#' + previewId).hide();
            $('#' + previewId + ' img').attr('src', '{{ asset('images/upload/upload-1.png') }}');
            $('#' + previewId + ' p.file-name-overlay').remove();
            $('#' + previewId + ' .remove-upload').hide();
            $('#' + inputId).val('');
            $('#upload-file').show();
        }

        function removeGalleryImage(button, inputId) {
            $(button).parent('.gitems').remove();
            if ($('.gitems').length === 0) {
                $('#' + inputId).val('');
                $('#galUpload').addClass('up-load');
            }
        }
    </script>
@endpush
