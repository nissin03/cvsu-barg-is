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
                enctype="multipart/form-data" id="facilityForm">
                @csrf
                <div class="wg-box">
                    <div id="alertContainer"></div>

                    {{-- Name --}}
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
                                <select name="status">
                                    <option value="1" {{ old('status') ? 'selected' : '' }}>Available</option>
                                    <option value="0" {{ old('status') ? 'selected' : '' }}>Not Available</option>
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
                                    <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>

                </div>


                <div class="wg-box" id="roomBox">
                    <fieldset class="name" id="hideRoomBox">
                        <div class="body-title mb-10">Capacity</div>
                        <input type="number" min="0" id="roomCapacityWhole" name="whole_capacity"
                            placeholder="Enter capacity">
                    </fieldset>

                    <div id="dormitoryRooms">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                            <h4>Details</h4>
                            {{-- <button type="button" data-bs-toggle="modal" data-bs-target="#addRoom">Add Room</button> --}}
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addMultipleRoomsModal">
                                Add Rooms
                            </button>
                        </div>
                        <p>No rooms yet :(</p>
                        <div id="roomContainer" class="mt-4">
                            <h4>Rooms</h4>
                            <ul class="list-group" id="roomList"></ul>
                        </div>
                        {{-- <div class="modal fade" id="addRoom" tabindex="-1" aria-labelledby="addRoomLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="addRoomLabel">Add Room</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <fieldset class="name">
                                            <div class="body-title mb-10">Room Name <span class="tf-color-1">*</span>
                                            </div>
                                            <input type="text" id="roomName" name="room_name"
                                                placeholder="Enter room name">
                                        </fieldset>

                                        <fieldset class="name">
                                            <div class="body-title mb-10">Capacity <span class="tf-color-1">*</span></div>
                                            <input type="number" min="0" id="roomCapacity" name="capacity"
                                                placeholder="Enter room capacity">
                                        </fieldset>

                                        <fieldset class="sex-restriction">
                                            <div class="body-title mb-10">Sex Restriction</div>
                                            <select id="roomSexRestriction" name="sex_restriction">
                                                <option value="">Choose Sex Restriction... </option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </fieldset>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveRoomChanges"> Save
                                            Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <!-- Add Multiple Rooms Modal -->
                        <div class="modal fade" id="addMultipleRoomsModal" tabindex="-1"
                            aria-labelledby="addMultipleRoomsLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="addMultipleRoomsLabel">Add Room</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <!-- Table for multiple rooms -->
                                        <table class="table" id="multipleRoomsTable">
                                            <thead>
                                                <tr>
                                                    <th>Room Name</th>
                                                    <th>Capacity</th>
                                                    <th>Sex Restriction</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>


                                        <button type="button" id="addMultipleRoomsRowBtn">
                                            Add Another Row
                                        </button>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>

                                        <button type="button" class="btn btn-primary" id="saveMultipleRoomsBtn">
                                            Save All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>



                {{-- prices fields  --}}


                <div class="wg-box" id="priceBox">

                    <div id="dormitoryFields"
                        class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <h4>Prices</h4>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addPrice">Add Price</button>
                    </div>

                    <p>No prices yet :(</p>

                    <div id="priceContainer" class="mt-4">
                        <h4>Price List</h4>
                        <ul class="list-group container-sm" id="priceList"></ul>
                    </div>


                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Create facility</button>
                    </div>
                </div>



                <div class="modal fade" id="addPrice" tabindex="-1" aria-labelledby="addPriceLabel"
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
                                    <input type="text" id="priceName" name="{{ old('name') }}">
                                </div>
                                @error('name')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div class="gap22 cols">
                                    <fieldset class="price_type">
                                        <div class="body-title mb-10">Price Type<span class="tf-color-1">*</span></div>
                                        <div class="select">
                                            <select id="priceTypeSelect" name="price_type">
                                                <option value="" selected disabled>Choose Price Type...</option>
                                                <option value="individual"
                                                    {{ old('price_type') === 'individual' ? 'selected' : '' }} hidden
                                                    disabled id="pIndividual">
                                                    Individual
                                                </option>
                                                <option value="whole"
                                                    {{ old('price_type') === 'whole' ? 'selected' : '' }} hidden disabled
                                                    id="pWhole">
                                                    Whole Place
                                                </option>
                                            </select>
                                        </div>
                                    </fieldset>
                                </div>

                                @error('price_type')
                                    <span class="alert alert-danger text-center">{{ $message }}</span>
                                @enderror



                                <div id="individualPriceFields">
                                    <fieldset class="name">
                                        <div class="body-title mb-10">Price <span class="tf-color-1">*</span>
                                        </div>
                                        <input type="number" min="1" id="value" name="{{ old('value') }}"
                                            placeholder="Enter price">
                                    </fieldset>
                                </div>
                                @error('value')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div class="form-check d-flex justify-content-center align-items-center my-4">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" class="form-check-input" id="isBasedOnDays"
                                            name="is_based_on_days" value="1">
                                        <label class="form-check-label ms-2 pt-2" for="isBasedOnDays">Is based on
                                            days?</label>
                                    </div>

                                    <div id="QuantityChecked" style="padding-left: 10px; display: none;">
                                        <input class="form-check-input"
                                            style="height: 1.5rem; width: 1.5rem; padding-left: 5px;" type="checkbox"
                                            id="isThereAQuantity" name="is_there_a_quantity" value="1">
                                        <label class="form-check-label pt-1" for="isThereAQuantity">Is there a
                                            quantity?</label>
                                    </div>
                                </div>

                                <div id="dateFields" style="display: none;">
                                    <div class="input-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" id="date_from" name="prices[0][date_from]">
                                    </div>
                                    <div class="input-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" id="date_to" name="prices[0][date_to]">
                                    </div>
                                </div>


                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="savePriceChanges">Save</button>
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
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script src="{{ asset('assets/js/roomandprices.js') }}"></script>
    <script src="{{ asset('assets/js/imagefile.js') }}"></script>
    <script>
        function removeUpload(previewId, inputId) {
            $('#' + previewId).hide(); // Hide the preview
            $('#' + previewId + ' img').attr('src', '{{ asset('images/upload/upload-1.png') }}');
            $('#' + previewId + ' p.file-name-overlay').remove();
            $('#' + previewId + ' .remove-upload').hide();
            $('#' + inputId).val('');
        }


        function removeGalleryImage(button, inputId) {
            $(button).parent('.gitems').remove();
            $('#' + inputId).val('');
            if ($('.gitems').length === 0) {
                $('#galUpload').addClass('up-load');
            }
        }


        document.addEventListener("DOMContentLoaded", function() {
            const isBasedOnDaysCheckbox = document.getElementById('isBasedOnDays');
            const dateFieldsDiv = document.getElementById('dateFields');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');


            function disablePastDates() {
                const today = new Date().toISOString().split('T')[0];
                dateFromInput.setAttribute('min', today);
                dateToInput.setAttribute('min', today);
            }


            dateFromInput.addEventListener('change', function() {
                if (dateFromInput.value) {
                    dateToInput.value = dateFromInput.value;
                }
            });

            // Handle the checkbox state to show/hide the date fields
            isBasedOnDaysCheckbox.addEventListener('change', function() {
                if (isBasedOnDaysCheckbox.checked) {
                    dateFieldsDiv.style.display = 'block';
                } else {
                    dateFieldsDiv.style.display = 'none';
                }
            });

            // Disable past dates initially
            disablePastDates();
        });


        // document.getElementById('isBasedOnDays').addEventListener('change', function() {

        //     const dateFields = document.getElementById('dateFields');
        //     if (this.checked) {
        //         dateFields.style.display = 'block';

        //         document.getElementById('isBasedOnDays').value = '1';
        //     } else {
        //         dateFields.style.display = 'none';

        //         document.getElementById('isBasedOnDays').value = '0';
        //     }
        // });
    </script>
@endpush
