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
                    @foreach ($facility->facilityAttributes as $facilityAttribute)
                        <fieldset class="name" id="hideRoomBox">
                            <div class="body-title mb-10">Capacity <span class="tf-color-1"
                                    id="option">(optional)</span>
                            </div>
                            <input type="number" min="0" id="roomCapacityWhole" name="whole_capacity"
                                placeholder="Enter capacity" value="{{ $facilityAttribute->whole_capacity }}">
                        </fieldset>
                    @endforeach


                    <div id="dormitoryRooms">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                            <h4>Details</h4>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addRoom">Add Room</button>
                        </div>
                        {{-- <p class="d-flex justify-content-center align-items-center">No rooms yet :(</p> --}}
                        <div id="roomContainer" class="mt-4">

                            <ul class="list-group" id="roomList">
                                <ul class="list-group" id="roomList">
                                    @forelse ($facility->facilityAttributes->filter(fn($attr) => $attr->facility_id == $facility->id)->values() as $index => $facilityAttribute)
                                        @if ($facilityAttribute->room_name && $facilityAttribute->capacity > 0)
                                            <div class="card p-3 mb-3">
                                                <div class="card-body d-flex justify-content-between align-items-center">
                                                    <div class="text-start">
                                                        <div class="d-flex justify-content-center align-items-center">
                                                            <h4 class="pe-2">{{ $facilityAttribute->room_name }}</h4>
                                                            @if ($facilityAttribute->sex_restriction)
                                                                <span class="badge bg-info">
                                                                    {{ ucfirst($facilityAttribute->sex_restriction) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="fw-bold">Capacity: <span
                                                                class="badge bg-warning">{{ $facilityAttribute->capacity }}</span>
                                                        </p>
                                                    </div>
                                                    <div class="d-flex">
                                                        <button type="button" class="btn btn-lg btn-outline-warning me-2"
                                                            onclick="editRoom({{ $index }})"><i
                                                                class="icon-pen">Edit</i></button>
                                                        <button type="button"
                                                            class="btn btn-lg btn-outline-danger delete-btn"
                                                            onclick="deleteRoom({{ $index }})">
                                                            <i class="icon-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <li class="list-group-item">
                                            No facility attributes available.
                                        </li>
                                    @endforelse
                                </ul>

                            </ul>

                        </div>
                        <div class="modal fade" id="addRoom" tabindex="-1" aria-labelledby="addRoomLabel"
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
                                                <option value="" selected>Choose Sex Restriction... </option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </fieldset>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveRoomChanges"> Save
                                            changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>




                {{-- prices fields  --}}


                <div class="wg-box">

                    <div id="dormitoryFields"
                        class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <h4>Prices</h4>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addPrice">Add Price</button>
                    </div>

                    <div id="priceContainer" class="mt-4">
                        <ul class="list-group container-sm" id="priceList">
                            @foreach ($prices as $price)
                                <div class="card p-3 mb-3">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div class="text-start">
                                            <h4>{{ $price->name }}</h4>
                                            <p>Type: <span class="badge bg-success">{{ $price->price_type }}</span></p>
                                            <p>Price: PHP {{ $price->value }}</p>
                                            <p>
                                                Is based on days?:
                                                <span
                                                    class="badge {{ $price->is_based_on_days ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $price->is_based_on_days ?: 'N/A' }}
                                                </span>
                                            </p>
                                            <p>
                                                Is there a quantity?:
                                                <span
                                                    class="badge {{ $price->is_there_a_quantity ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $price->is_there_a_quantity ?: 'N/A' }}
                                                </span>
                                            </p>

                                        </div>
                                        <button type="button" class="btn btn-lg btn-outline-danger delete-btn"
                                            onclick="deletePrice(${index})">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </ul>
                    </div>


                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Update facility</button>
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
                                    <input type="text" id="priceName" name="name" value="{{ old('name') }}">
                                </div>
                                @error('name')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div class="gap22 cols">
                                    <fieldset class="price_type">
                                        <div class="body-title mb-10">Price Type<span class="tf-color-1">*</span>
                                        </div>
                                        <div class="select">
                                            <select id="priceTypeSelect" name="price_type">
                                                <option value="" selected disabled>Choose Price Type...</option>
                                                <option value="individual"
                                                    {{ old('price_type', $price->price_type ?? '') === 'individual' ? 'selected' : '' }}>
                                                    Individual
                                                </option>
                                                <option value="whole"
                                                    {{ old('price_type', $price->price_type ?? '') === 'whole' ? 'selected' : '' }}>
                                                    Whole Place
                                                </option>
                                            </select>
                                        </div>
                                    </fieldset>
                                </div>

                                @error('type')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <div id="individualPriceFields">
                                    <fieldset class="name">
                                        <div class="body-title mb-10">Price <span class="tf-color-1">*</span>
                                        </div>
                                        <input type="number" min="1" id="value" name="value"
                                            value="{{ old('value') }}" placeholder="Enter price">
                                    </fieldset>
                                </div>
                                @error('value')
                                    <span class="alert alert-danger text-center">{{ $message }} </span>
                                @enderror

                                <!-- Change these checkbox inputs -->
                                <div class="form-check d-flex justify-content-center align-items-center my-4">
                                    <input type="checkbox" class="form-check-input" id="isBasedOnDays"
                                        name="is_based_on_days" value="1" 
                                    {{ old('is_based_on_days') ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 pt-2" for="isBasedOnDays">Is based on
                                        days?</label>
                                </div>

                                <div class="form-check d-flex justify-content-center align-items-center my-4">
                                    <input type="checkbox" class="form-check-input" id="isThereAQuantity"
                                        name="is_there_a_quantity" value="1" 
                                    {{ old('is_there_a_quantity') ? 'checked' : '' }}> 
                                    <label class="form-check-label ms-2 pt-2" for="isThereAQuantity">Is there a
                                        quantity?</label>
                                </div>

                                <div id="dateFields" style="display: none;">
                                    <div class="input-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" id="date_from" name="prices[0][date_from]" value="{{ old('prices.0.date_from', $price->date_from ?? '') }}">
                                    </div>
                                    <div class="input-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" id="date_to" name="prices[0][date_to]" value="{{ old('prices.0.date_to', $price->date_to ?? '') }}">
                                    </div>
                                </div> 

                                {{-- <div id="dateFields" style="display: none;">
                                    <div class="input-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" id="date_from" name="date_from"
                                            value="{{ old('date_from') }}">
                                    </div>
                                    <div class="input-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" id="date_to" name="date_to"
                                            value="{{ old('date_to') }}">
                                    </div>
                                </div> --}}


                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="savePriceChanges">Save</button>
                            </div>
                        </div>
                    </div>
                </div>


                @php
                    $rooms = $facility->facilityAttributes
                        ->filter(function ($attr) use ($facility) {
                            return $attr->facility_id == $facility->id;
                        })
                        ->map(function ($attribute) {
                            return [
                                'id' => $attribute->id,
                                'facility_id' => $attribute->facility_id,
                                'room_name' => $attribute->room_name,
                                'capacity' => $attribute->capacity,
                                'sex_restriction' => $attribute->sex_restriction,
                            ];
                        })
                        ->values();
                @endphp

                @php
                    $price = $facility->prices
                        ->filter(function ($attr) use ($facility) {
                            return $attr->facility_id == $facility->id;
                        })
                        ->map(function ($price) {
                            return [
                                'id' => $price->id,
                                'facility_id' => $price->facility_id,
                                'name' => $price->name,
                                'priceTypeSelect' => $price->price_type,
                                'value' => $price->value,
                                'isBasedOnDays' => $price->is_based_on_days,
                                'isThereAQuantity' => $price->is_there_a_quantity,
                            ];
                        })
                        ->values();
                @endphp



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
    <script src="{{ asset('assets/js/imagefile.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/roomandprices.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            // let rooms = [];
            let rooms = @json($rooms);
            console.log("Initial Rooms Data:", rooms);


            $("#rentalType").on("change", function() {
                const rentalType = $(this).val();
                console.log("Facility Type:", rentalType);

                $("#pIndividual, #pWhole").attr("hidden", true).prop("disabled", true);

                $("#roomBox, #hideRoomBox, #dormitoryRooms, #QuantityChecked").hide();

                switch (rentalType) {
                    case "individual":
                        $("hideRoomBox").hide();
                        $("#roomBox").show();
                        $("#dormitoryRooms").show();
                        $("#QuantityChecked").show();

                        $("#pIndividual").removeAttr("hidden").prop("disabled", false);
                        break;
                    case "whole_place":
                        $("#dormitoryRooms").hide();
                        $("#roomBox").show();
                        $("#hideRoomBox").show();
                        $("#QuantityChecked").hide();

                        $("#pWhole").removeAttr("hidden").prop("disabled", false);
                        break;

                    case "both":
                        $("#roomBox").show();
                        $("#hideRoomBox").show();
                        $("#dormitoryRooms").show();
                        $("#option").show();
                        $("#QuantityChecked").show();
                        $("#pIndividual").show();
                        $("#pIndividual, #pWhole").removeAttr("hidden").prop("disabled", false);
                        break;
                    default:
                        break;
                }
            });


            $("#rentalType").trigger("change");

            // renderRoomList();

            $("#saveRoomChanges").on("click", function(event) {
                event.preventDefault();

                const roomName = $("#roomName").val();
                const roomCapacity = $("#roomCapacity").val();
                const roomSexRestriction = $("#roomSexRestriction").val();

                // Validate room data
                if (!roomName || !roomCapacity) {
                    alert("Please fill in all fields");
                    return;
                }
                if (isNaN(roomCapacity) || roomCapacity <= 0) {
                    alert("Capacity must be a positive number.");
                    return;
                }



                const validSexRestrictions = ["male", "female"];
                const newRoom = {
                    room_name: roomName,
                    capacity: parseInt(roomCapacity) || null,
                    sex_restriction: validSexRestrictions.includes(roomSexRestriction) ?
                        roomSexRestriction :
                        null,
                };
                rooms.push(newRoom);
                renderRoomList();
                updateHiddenRooms();

                $("#addRoom").modal("hide");

                $("#roomName, #roomCapacity, #roomSexRestriction").val("");

                // Reset modal title if it was changed during edit
                $("#addRoomLabel").text("Add Room");
            });

            function renderRoomList() {
                $("#roomList").empty();
                if (rooms.length === 0) {
                    $("#roomList").append('<li class="list-group-item">No rooms yet :(</li>');
                    return;
                }
                rooms.forEach((room, index) => {
                    const listItem = `
                    <div class="card p-3 mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4>${room.room_name}</h4>
                                <p>Capacity: <span class="badge bg-warning">${room.capacity}</span></p>
                                ${room.sex_restriction ? `<span class="badge bg-info">${room.sex_restriction}</span>` : ""}
                            </div>
                            <div class="d-flex">
                                <button type="button" class="btn btn-warning me-2" onclick="editRoom(${index})">Edit</button>
                                <button type="button" class="btn btn-danger" onclick="deleteRoom(${index})">Delete</button>
                            </div>
                        </div>
                    </div>`;
                    $("#roomList").append(listItem);
                });
            }

            $(document).ready(function() {
                renderRoomList();
            });




            window.editRoom = function(index) {
                const room = rooms[index];
                $("#roomName").val(room.room_name || "");
                $("#roomCapacity").val(room.capacity || "");
                $("#roomSexRestriction").val(room.sex_restriction || "");

                $("#saveRoomChanges")
                    .off("click")
                    .on("click", function() {
                        const updatedRoomName = $("#roomName").val();
                        const updatedCapacity = $("#roomCapacity").val();
                        let updatedSexRestriction = $("#roomSexRestriction").val();

                        if (!updatedRoomName || !updatedCapacity || isNaN(updatedCapacity) ||
                            updatedCapacity <= 0) {
                            alert("Please provide valid room details.");
                            return;
                        }


                        rooms[index] = {
                            room_name: updatedRoomName,
                            capacity: parseInt(updatedCapacity),
                            sex_restriction: updatedSexRestriction || null,
                        };
                        renderRoomList();
                        updateHiddenRooms();

                        // Close the modal
                        $("#addRoom").modal("hide");
                    });

                // Change modal title to 'Edit Room'
                $("#addRoomLabel").text("Edit Room");

                // Show the modal
                $("#addRoom").modal("show");
            };


            window.deleteRoom = function(index) {
                if (confirm("Are you sure you want to delete this room?")) {
                    rooms.splice(index, 1);
                    renderRoomList();
                    updateHiddenRooms();
                }
            };

            function updateHiddenRooms() {
                const roomInput = $("#hiddenRooms");
                roomInput.empty();

                rooms.forEach((room, index) => {
                    const sexRestrictionValue =
                        room.sex_restriction && ['male', 'female'].includes(room.sex_restriction) ?
                        room.sex_restriction :
                        '';

                    if (room.room_name && room.capacity > 0) {
                        roomInput.append(createHiddenInputRooms(`facility_attributes[${index}][room_name]`,
                            room.room_name));
                        roomInput.append(createHiddenInputRooms(`facility_attributes[${index}][capacity]`,
                            room.capacity));
                        roomInput.append(createHiddenInputRooms(
                            `facility_attributes[${index}][sex_restriction]`, sexRestrictionValue));
                    }
                });
            }
            // Helper function to create hidden input for rooms
            function createHiddenInputRooms(name, value) {
                return `<input type="hidden" name="${name}" value="${value}">`;
            }

            // Prices functions  

            $(document).ready(function () {
                renderPriceList();
            });

            let prices = @json($prices);

            // Handle Save Price Changes (Add Price)
            $("#savePriceChanges").on("click", function(event) {
                event.preventDefault();

                const name = $("#priceName").val();
                const price_type = $("#priceTypeSelect").val();
                const value = $("#value").val();
                const isBasedOnDays = $("#isBasedOnDays").prop("checked");
                const isThereAQuantity = $("#isThereAQuantity").prop("checked");
                const dateFrom = $("#date_from").val();
                 const dateTo = $("#date_to").val();
                 if (isBasedOnDays && (!dateFrom || !dateTo)) {
                    alert("Date From and Date To are required when 'Is Based on Days?' is checked.");
                    return;
                }

                // Add new price
                const newPrice = {
                    name,
                    price_type,
                    value: parseFloat(value),
                    is_based_on_days: isBasedOnDays,
                    is_there_a_quantity: isThereAQuantity,

                    ...(isBasedOnDays ? { date_from: dateFrom, date_to: dateTo } : {})
                };

                prices.push(newPrice);
                renderPriceList();
                updateHiddenPrices();

                // Close the price modal
                $("#addPrice").modal("hide");
                clearPriceFields();
            });

            // Render the price list dynamically
            function renderPriceList() {
                $("#priceList").empty();

                if (prices && prices.length > 0) {
                    prices.forEach((price, index) => {
                        const listItem = `
                                <div class="card p-3 mb-3">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div class="text-start">
                                            <h4>${price.name}</h4>
                                            <p>Type: <span class="badge bg-success">${price.price_type}</span></p>
                                            <p>Price: PHP ${price.value}</p>
                                            <p>Is Based on Days?: 
                                                <span class="badge ${price.is_based_on_days ? 'bg-success' : 'bg-danger'}">
                                                    ${price.is_based_on_days ? 'Yes' : 'No'}
                                                </span>
                                            </p>
                                            <p>Is there a quantity?: 
                                                <span class="badge ${price.is_there_a_quantity ? 'bg-success' : 'bg-danger'}">
                                                    ${price.is_there_a_quantity ? 'Yes' : 'No'}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-lg btn-outline-warning me-2" onclick="editPrice(${index})">
                                                <i class="icon-pen">Edit</i>
                                            </button>
                                            <button type="button" class="btn btn-lg btn-outline-danger delete-btn" onclick="deletePrice(${index})">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
            `;
                        $("#priceList").append(listItem);
                    });
                }
            }

            window.editPrice = function(index) {
                const price = prices[index];
                $("#priceName").val(price.name || "");
                $("#priceTypeSelect").val(price.price_type || "");
                $("#value").val(price.value || "");
                $("#isBasedOnDays").prop("checked", price.is_based_on_days);
                $("#isThereAQuantity").prop("checked", price.is_there_a_quantity);
                
                // Only set date fields if is_based_on_days is true
                if (price.is_based_on_days) {
                    $("#date_from").val(price.date_from || "");
                    $("#date_to").val(price.date_to || "");
                    $("#dateFields").show();
                } else {
                    $("#dateFields").hide();
                }

                $("#savePriceChanges")
                    .off("click")
                    .on("click", function() {
                        const updatedPriceName = $("#priceName").val();
                        const updatedPriceTypeSelect = $("#priceTypeSelect").val();
                        const updatedValue = $("#value").val();
                        const updatedBasedOnDays = $("#isBasedOnDays").prop("checked");
                        const updatedAQuantity = $("#isThereAQuantity").prop("checked");
                        const updatedDateFrom = updatedBasedOnDays ? $("#date_from").val() : "";
                        const updatedDateTo = updatedBasedOnDays ? $("#date_to").val() : "";

                        if (!updatedPriceName || !updatedValue || isNaN(updatedValue) || parseFloat(updatedValue) <= 0) {
                            alert("Please enter a valid price.");
                            return;
                        }

                        prices[index] = {
                            ...prices[index],
                            name: updatedPriceName,
                            price_type: updatedPriceTypeSelect,
                            value: parseFloat(updatedValue),
                            is_based_on_days: updatedBasedOnDays,
                            is_there_a_quantity: updatedAQuantity,
                            ...(updatedBasedOnDays && { 
                                date_from: updatedDateFrom, 
                                date_to: updatedDateTo 
                            })
                        };

                        renderPriceList();
                        updateHiddenPrices();

                        $("#addPrice").modal("hide");
                        clearPriceFields();
                        $("#addPriceLabel").text("Add Price");
                    });

                $("#addPriceLabel").text("Edit Price");
                $("#addPrice").modal("show");
            };

            window.deletePrice = function(index) {
                if (confirm("Are you sure you want to delete this room?")) {
                    prices.splice(index, 1);
                    renderPriceList();
                    updateHiddenPrices();
                }
            };

            function clearPriceFields() {
                $("#priceName").val("");
                $("#priceTypeSelect").val("");
                $("#value").val("");
                $("#isBasedOnDays").prop("checked", false);
                $("#isThereAQuantity").prop("checked", false);
            }

            function updateHiddenPrices() {
            const priceInput = $("#hiddenPrices");
                priceInput.empty();

                prices.forEach((price, index) => {
                    if (price.name && price.price_type && !isNaN(price.value)) {
                        priceInput.append(createHiddenInput(`prices[${index}][name]`, price.name));
                        priceInput.append(createHiddenInput(`prices[${index}][price_type]`, price.price_type));
                        priceInput.append(createHiddenInput(`prices[${index}][value]`, price.value));
                        priceInput.append(createHiddenInput(`prices[${index}][is_based_on_days]`, price.is_based_on_days ? "1" : "0"));
                        priceInput.append(createHiddenInput(`prices[${index}][is_there_a_quantity]`, price.is_there_a_quantity ? "1" : "0"));
                        
                         if (price.is_based_on_days) {
                        const dateFrom = price.date_from || "";
                        const dateTo = price.date_to || "";
                        priceInput.append(createHiddenInput(`prices[${index}][date_from]`, dateFrom));
                        priceInput.append(createHiddenInput(`prices[${index}][date_to]`, dateTo));
                }
                    }
                });
            }

            function createHiddenInput(name, value) {
                return `<input type="hidden" name="${name}" value="${value}">`;
            }



            $("#facilityForm").on("submit", function(event) {
                event.preventDefault();

                const formData = new FormData(this);

                const facilityType = $("#rentalType").val();

                if (prices && prices.length > 0) {
                    prices.forEach((price, index) => {
                        // Only append if values exist
                        if (price.name) formData.append(`prices[${index}][name]`, price.name);
                        if (price.price_type) formData.append(`prices[${index}][price_type]`, price
                            .price_type);
                        if (price.value) formData.append(`prices[${index}][value]`, price.value);
                        formData.append(`prices[${index}][is_based_on_days]`, price
                            .is_based_on_days ? '1' : '0');
                        formData.append(`prices[${index}][is_there_a_quantity]`, price
                            .is_there_a_quantity ? '1' : '0');
                    });
                }

                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                if (rooms.length > 0) {
                    rooms.forEach((room, index) => {
                        if (room.room_name && room.capacity > 0) { // Only add valid room data
                            formData.append(`facility_attributes[${index}][room_name]`, room
                                .room_name);
                            formData.append(`facility_attributes[${index}][capacity]`, room
                                .capacity);
                            formData.append(`facility_attributes[${index}][sex_restriction]`, room
                                .sex_restriction || '');
                        }
                    });
                }

                if (facilityType === "whole_place") {
                    // Only append whole_capacity
                    const wholeCapacity = $("#roomCapacityWhole").val();
                    formData.append("whole_capacity", wholeCapacity);
                } else {
                    // Append facility attributes for individual or both
                    rooms.forEach((room, index) => {
                        formData.append(
                            `facility_attributes[${index}][room_name]`,
                            room.room_name
                        );
                        formData.append(
                            `facility_attributes[${index}][capacity]`,
                            room.capacity
                        );
                        formData.append(
                            `facility_attributes[${index}][sex_restriction]`,
                            room.sex_restriction || ''
                        );
                    });
                }


                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function(response) {
                        console.log("Success response:", response);
                        if (response.action === "create") {
                            showAlert("Facility created successfully!", "success");
                        } else if (response.action === "update") {
                            showAlert("Facility updated successfully!", "success");
                        }
                        setTimeout(function() {
                            window.location.href = "/admin/facilities";
                        }, 2000);
                    },
                    error: function(xhr) {
                        console.log("Error:", xhr);
                        if (xhr.status === 422) {
                            displayValidationErrors(xhr.responseJSON.errors);
                        } else {
                            showAlert(
                                "An unexpected error occurred. Please try again.",
                                "danger"
                            );
                        }
                    },
                });

                console.log("Hidden form data", $("#hiddenRooms").html()); // Log hidden input fields
            });

            // Display validation errors in form
            function displayValidationErrors(errors) {
                for (const [key, messages] of Object.entries(errors)) {
                    const errorContainer = $(`#${key}Error`);
                    if (errorContainer.length) {
                        errorContainer.html(messages[0]).show();
                    }
                }
            }

            // Show custom alert
            function showAlert(message, type) {
                const alertBox = $("<div>", {
                    class: `alert alert-${type} alert-dismissible fade show`,
                    role: "alert",
                    text: message,
                }).append(
                    $("<button>", {
                        type: "button",
                        class: "btn-close",
                        "data-bs-dismiss": "alert",
                        "aria-label": "Close",
                    })
                );

                $("#alertContainer").html(alertBox);
                alertBox.alert();
            }
        });

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
    </script>
@endpush
