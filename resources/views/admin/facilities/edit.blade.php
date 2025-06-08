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
    </style>
    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Edit Facility</h3>
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
                        <div class="text-tiny">Edit Facility</div>
                    </li>
                </ul>
            </div>
            <!-- form-add-rental -->
            <form action="{{ route('admin.facilities.update', $facility->id) }}" class="tf-section-2 form-add-rental"
                method="POST" enctype="multipart/form-data" id="facilityForm">
                <input type="hidden" name="id" value="{{ $facility->id }}">
                @csrf
                @method('PUT')

                <div class="wg-box">
                    <div id="alertContainer"></div>

                    {{-- Name --}}
                    <fieldset class="name">
                        <div class="body-title mb-10">Facility name <span class="tf-color-1">*</span></div>
                        <input class="form-control" type="text" value="{{ $facility->name }}"
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
                                    <option value="" selected disabled>Choose rental facility_type...</option>
                                    <option value="individual"
                                        {{ $facility->facility_type === 'individual' ? 'selected' : '' }}>
                                        Individual
                                    </option>
                                    <option value="whole_place"
                                        {{ $facility->facility_type === 'whole_place' ? 'selected' : '' }}>
                                        Whole Place
                                    </option>
                                    <option value="both" {{ $facility->facility_type === 'both' ? 'selected' : '' }}>
                                        Both
                                    </option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('type')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    {{-- Description --}}
                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
                            required="">{{ $facility->description }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <fieldset class="rules_and_regulations">
                        <div class="body-title mb-10">Rules and Regulation <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" id="rules" name="rules_and_regulations" placeholder="rules_and_regulations" tabindex="0"
                            aria-required="true">{{ $facility->rules_and_regulations }}</textarea>
                    </fieldset>
                    @error('rules_and_regulations')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <!-- Requirements formatted like upload image -->

                </div>

                <div class="wg-box">

                    <fieldset>
                        <div class="body-title mb-10">Requirements <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="requirementsPreview"
                                style="{{ $facility->requirements ? '' : 'display:none' }}">
                                @if ($facility->requirements)
                                    <p class="file-name-overlay">Current file: {{ $facility->requirements }}</p>
                                @endif
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
                            <div class="item" id="imgpreview" style="{{ $facility->image ? '' : 'display:none' }}">
                                @if ($facility->image)
                                    <img src="{{ asset('storage/' . $facility->image) }}" id="preview-img" class="effect8"
                                        alt="">
                                @endif
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
                            @if ($facility->images)
                                @foreach (explode(',', $facility->images) as $img)
                                    <div class="item gitems">
                                        <img src="{{ asset('storage/' . $img) }}"
                                            style="width: 100px; height: 100px; object-fit: cover;" />
                                        <button type="button" class="remove-upload show"
                                            onclick="removeGalleryImage(this, 'gFile')">Remove</button>
                                    </div>
                                @endforeach
                            @endif
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Select your images here or click to browse</span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                                </label>
                            </div>
                            <div id="image-preview-container"></div>
                        </div>
                    </fieldset>

                    @error('images')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Status</div>
                            <div class="select mb-10">
                                <select name="featured">
                                    <option value="0" {{ $facility->status == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $facility->status == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('status')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror

                        <fieldset class="name">
                            <div class="body-title mb-10">Featured</div>
                            <div class="select mb-10">
                                <select name="featured">
                                    <option value="0" {{ $facility->featured == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $facility->featured == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>
                </div>


                <div class="wg-box" id="roomBox">
                    @if ($facilityAttributes && $facilityAttributes->whole_capacity)
                        <fieldset class="name" id="hideRoomBox">
                            <div class="body-title mb-10">Capacity <span class="tf-color-1"
                                    id="option">(optional)</span></div>
                            <input type="number" id="roomCapacityWhole" min="0" name="whole_capacity"
                                placeholder="Enter capacity"
                                value="{{ old('whole_capacity', $facility->facilityAttributes->first()->whole_capacity) }}">
                        </fieldset>
                    @endif


                    <div id="hiddenRooms"></div>

                    @if (
                        !isset($facility->facilityAttributes->first()->whole_capacity) ||
                            $facility->facilityAttributes->first()->whole_capacity === null)
                        <div id="dormitoryRooms" class="mt-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                                <h4>Room Management</h4>
                                <div class="d-flex gap-2">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#addBulkRoomsModal">
                                        <i class="bi bi-plus-circle"></i> Add Multiple Rooms
                                    </button>
                                    <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#addMultipleRoomsModal">
                                        <i class="bi bi-plus-circle"></i> Add Rooms
                                    </button>
                                </div>
                            </div>

                            <!-- No rooms message -->
                            <div id="noRoomsMessage" class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i> No rooms added yet. Click "Add Rooms" to get
                                started.
                            </div>

                            <!-- Room display -->
                            <div id="roomContainer" class="mt-4">
                                <h4 class="mb-3">Rooms</h4>
                                <div class="row" id="roomCardsContainer">
                                    <!-- Existing rooms will be rendered here -->
                                </div>
                                <ul class="list-group d-none" id="roomList"></ul>
                            </div>

                            <!-- Add/Edit Rooms Modal -->
                            <div class="modal fade" id="addMultipleRoomsModal" tabindex="-1"
                                aria-labelledby="addMultipleRoomsLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addMultipleRoomsLabel">Manage Rooms</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div id="roomFormContainer">
                                                <!-- Dynamic room form elements will go here -->
                                            </div>
                                            <button type="button" id="addMultipleRoomsRowBtn" class="mt-3">
                                                <i class="bi bi-plus-circle"></i> Add Another Room
                                            </button>
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
                    @endif
                </div>

                {{-- prices fields  --}}
                <div class="wg-box">

                    <div id="dormitoryFields"
                        class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <h4>Prices</h4>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addPrice">Add Price</button>
                    </div>

                    <div id="hiddenPrices"></div>
                    <div id="priceContainer" class="mt-4">
                        <div class="row" id="priceCardsContainer">
                            <!-- The price cards will be inserted here dynamically -->

                        </div>
                    </div>

                    <div class="cols gap10">
                        <button id="facilitySubmitBtn" class="tf-button w-full" type="submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="btn-text">Update Facility</span>
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="addPriceLabel">Add Price</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="priceFormContainer">
                                    <!-- Dynamic price form cards will be appended here -->
                                </div>

                                <button type="button" id="addMultiplePricesRowBtn" class="mt-3">
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

                {{-- <div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Add Price</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="input-group">
                                    <label for="user-type">Name<span class="tf-color-1">*</span></label>
                                    <input type="text" id="priceName" name="prices[{{ $index }}][name]"
                                        value="{{ old('prices.' . $index . '.name') }}">
                                </div>
                                @error('name')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div class="gap22 cols">
                                    <fieldset class="price_type">
                                        <div class="body-title mb-10">Price Type<span class="tf-color-1">*</span></div>
                                        <div class="select">
                                            <select id="priceTypeSelect" name="prices[{{ $index }}][price_type]">
                                                <!-- Notice the change here -->
                                                <option value="" selected disabled>Choose Price Type...</option>
                                                <option value="individual"
                                                    {{ old('prices.' . $index . '.price_type') === 'individual' ? 'selected' : '' }}>
                                                    Individual
                                                </option>
                                                <option value="whole"
                                                    {{ old('prices.' . $index . '.price_type') === 'whole' ? 'selected' : '' }}>
                                                    Whole Place
                                                </option>
                                            </select>
                                        </div>
                                    </fieldset>
                                </div>

                                @error('price_type')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div id="individualPriceFields">
                                    <fieldset class="name">
                                        <div class="body-title mb-10">Price <span class="tf-color-1">*</span>
                                        </div>
                                        <input type="number" min="1" id="value"
                                            name="prices[{{ $index }}][value]"
                                            value="{{ old('prices.' . $index . '.value', $price->value ?? '') }}"
                                            placeholder="Enter price">
                                    </fieldset>
                                </div>
                                @error('value')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <!-- Change these checkbox inputs -->
                                <div class="form-check d-flex justify-content-center align-items-center my-4">
                                    <input type="checkbox" class="form-check-input" id="isBasedOnDays"
                                        name="prices[{{ $index }}][is_based_on_days]" value="1"
                                        {{ old('prices.' . $index . '.is_based_on_days', $price->is_based_on_days ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 pt-2" for="isBasedOnDays">Is based on
                                        days?</label>
                                </div>

                                <div class="form-check d-flex justify-content-center align-items-center my-4">
                                    <input type="checkbox" class="form-check-input" id="isThereAQuantity"
                                        name="prices[{{ $index }}][is_there_a_quantity]" value="1"
                                        {{ old('prices.' . $index . '.is_there_a_quantity', $price->is_there_a_quantity ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 pt-2" for="isThereAQuantity">Is there a
                                        quantity?</label>
                                </div>

                                <div id="dateFields" style="display: none;">
                                    <div class="input-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" id="date_from"
                                            name="prices[{{ $index }}][date_from]"
                                            value="{{ old('prices.' . $index . '.date_from', $price->date_from ?? '') }}">
                                    </div>
                                    <div class="input-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" id="date_to" name="prices[{{ $index }}][date_to]"
                                            value="{{ old('prices.' . $index . '.date_to', $price->date_to ?? '') }}">
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="savePriceChanges">Save</button>
                            </div>
                        </div>
                    </div>
                </div> --}}


            </form>

        </div>
    </div>

    <!-- Add Bulk Rooms Modal -->
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
                    <button type="button" class="btn btn-primary" id="saveBulkRoomsBtn">Save Rooms</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        window.rooms = @json($facility->facilityAttributes->filter(fn($attr) => $attr->facility_id == $facility->id)->toArray());
        const initialPrices = @json($prices ?? []);
        window.facilityId = {{ $facility->id }};
    </script>

    <script src="{{ asset('assets/js/formSubmitUpdate.js') }}"></script>
    <script src="{{ asset('assets/js/hideFields.js') }}"></script>
    <script src="{{ asset('assets/js/addRooms.js') }}"></script>
    <script src="{{ asset('assets/js/bulkRooms.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/updateRoom.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/updatePrice.js') }}"></script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('facilityForm');
            const submitBtn = document.getElementById('facilitySubmitBtn');
            const spinner = submitBtn.querySelector('.spinner-border');
            const btnText = submitBtn.querySelector('.btn-text');
            const facilityTypeSelect = document.getElementById('rentalType');
            const originalText = btnText.textContent;

            function getRequiredFields() {
                const type = facilityTypeSelect.value;
                let fields = ['name', 'facility_type', 'description', 'rules_and_regulations'];
                if (type === 'whole_place') {
                    fields.push('whole_capacity');
                } else if (type === 'individual' || type === 'both') {
                    fields.push('room_name', 'capacity');
                }
                return fields;
            }

            function isFormValid() {
                const requiredFields = getRequiredFields();
                for (let field of requiredFields) {
                    if (field === 'room_name' || field === 'capacity') {
                        const inputs = form.querySelectorAll(`[name^="facility_attributes"][name$="[${field}]"]`);
                        if (!inputs.length || Array.from(inputs).some(input => !input.value.trim())) {
                            return false;
                        }
                    } else {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (!input || !input.value.trim()) {
                            return false;
                        }
                    }
                }
                return true;
            }

            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Submitting...';

                if (!isFormValid()) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = originalText;
                    return;
                }
            });

            facilityTypeSelect.addEventListener('change', function() {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = originalText;
            });
        });
    </script>
@endpush
