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

        .custom-icon {
            fill: oklch(55.1% 0.027 264.364);
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


        /* Scrollable container for rooms */
        .room-scroll-container {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 10px;
        }

        .room-scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .room-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .room-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .room-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .room-card {
            margin-bottom: 15px;
        }

        .room-card:last-child {
            margin-bottom: 0;
        }

        /* Responsive max-height adjustments */
        .room-scroll-container {
            max-height: 400px;
        }

        @media (min-width: 768px) {
            .room-scroll-container {
                max-height: 500px;
            }
        }

        @media (min-width: 1024px) {
            .room-scroll-container {
                max-height: 600px;
            }
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
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <h6><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.facilities.update', $facility->id) }}" id="facilityForm"
                class="tf-section-2 form-update-rental" method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($facility))
                    @method('PUT')
                @endif
                {{-- <input type="hidden" id="facilityAttributesJson" name="facility_attributes_json">
                <input type="hidden" id="pricesJson" name="prices_json"> --}}
                <input type="hidden" id="facilityAttributesJson" name="facility_attributes_json"
                    value='@json($facilityAttributes ?? [])'>
                <input type="hidden" id="pricesJson" name="prices_json" value='@json($prices ?? [])'>

                @include('admin.facilities.partials.basic-info')

                @include('admin.facilities.partials.media-upload')

                <div class="wg-box" id="roomBox">
                    @include('admin.facilities.partials.room-management')
                    <x-addons.addon-selector :addons="$addons" :facility="$facility" />
                </div>

                <div class="wg-box" id="priceBox">

                    @include('admin.facilities.partials.pricing-management')
                    {{-- <x-discounts.discount-selector :discounts="$discounts" :facility="$facility" /> --}}
                    <div class="cols gap10">
                        <button id="facilitySubmitBtn" class="tf-button w-full" type="submit">
                            <span class="btn-text">Edit Facility</span>
                        </button>
                    </div>
                </div>

                <!-- Modal for Adding Prices -->
                @include('admin.facilities.partials.modals.add-price-modal')
                {{-- @include('admin.facilities.partials.addons-selection') --}}
                {{-- @include('admin.facilities.partials.addons-selection', [
                    'facility' => $facility,
                    'addons' => $addons,
                ]) --}}

            </form>


            <!-- /form-add-rental -->
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
    @include('admin.facilities.partials.modals.bulk-rooms-modal')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/form-validation.js') }}"></script>
    <script src="{{ asset('assets/js/rooms-management.js') }}"></script>
    <script src="{{ asset('assets/js/pricing-management.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/addons-management.js') }}"></script> --}}

    <script src="{{ asset('assets/js/hideFields.js') }}"></script>
    <script src="{{ asset('assets/js/imagefile.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/discount-selector.js') }}"></script> --}}
    <script src="{{ asset('assets/js/addon-selector.js') }}"></script>

    <script>
        let rooms = [];
        let prices = [];
        let priceEditMode = false;
        let priceEditIndex = -1;
        let roomEditMode = false;
        let roomEditIndex = -1;
        let bulkRoomPreview = [];

        let globalPriceSettings = {
            isBasedOnDays: false,
            isThereAQuantity: false,
            dateFrom: '',
            dateTo: ''
        };

        window.facilityFormConfig = {
            hasValidationErrors: {!! json_encode($errors->any()) !!},
            isEditMode: {{ isset($facility) ? 'true' : 'false' }},
            existingRooms: {!! json_encode($facilityAttributes ?? []) !!},
            existingPrices: {!! json_encode($prices ?? []) !!}
        };

        $(document).ready(function() {
            initializeForm();
            setupRadioButtonHandlers();

            // Add form submission debug
            $('#facilityForm').on('submit', function(e) {
                const facilityType = $('#rentalType').val();
                if (facilityType === 'both') {
                    console.log('🔍 PRE-SUBMIT CHECK for facility type "both":');

                    // Check all facility_selection_both fields
                    $('input[name="facility_selection_both"]').each(function(i, field) {
                        console.log(`Field ${i}:`, {
                            type: field.type,
                            value: field.value,
                            disabled: field.disabled,
                            checked: field.checked
                        });
                    });

                    // Ensure we have at least one non-disabled field with a value
                    const activeField = $('input[name="facility_selection_both"]:not([disabled])').first();
                    const hiddenField = $('input[name="facility_selection_both"][type="hidden"]').first();

                    console.log('Active field value:', activeField.val());
                    console.log('Hidden field value:', hiddenField.val());

                    // Emergency backup - ensure we have a value
                    if (!activeField.val() && !hiddenField.val()) {
                        const hasWholeCapacity = $('#roomCapacityWhole').val() !== '';
                        const hasRooms = rooms && rooms.length > 0;

                        let emergencyValue = '';
                        if (hasWholeCapacity && !hasRooms) {
                            emergencyValue = 'whole';
                        } else if (hasRooms) {
                            emergencyValue = 'room';
                        }

                        if (emergencyValue) {
                            // Remove existing hidden fields and create new one
                            $('input[name="facility_selection_both"][type="hidden"]').remove();
                            $(this).append(
                                `<input type="hidden" name="facility_selection_both" value="${emergencyValue}">`
                            );
                            console.log('🚨 EMERGENCY: Added missing hidden field with value:',
                                emergencyValue);
                        }
                    }
                }
            });
        });

        function initializeForm() {
            console.log("=== Starting Form Initialization ===");

            if (window.facilityFormConfig && window.facilityFormConfig.isEditMode) {
                loadExistingData();
            }

            preserveFormData();
            setupFormValidation();
            setupRoomsManagement();
            setupPricingManagement();
            handleInitialUIState();
            // setupDiscountsUi();
            setupAddonsUi();
        }

        function loadExistingData() {
            try {
                console.log("=== Loading Existing Data ===");

                // Load rooms data
                let roomsRaw = $('#facilityAttributesJson').val();
                if (roomsRaw) {
                    rooms = typeof roomsRaw === 'string' ? JSON.parse(roomsRaw) : roomsRaw;
                    rooms = rooms.filter(room => room !== null && room !== undefined);

                    console.log("Loaded rooms:", rooms);

                    // Check for whole_capacity in room data and populate field if empty
                    const roomWithWholeCapacity = rooms.find(room => room.whole_capacity && room.whole_capacity > 0);
                    const wholeCapacityField = $('#roomCapacityWhole');

                    if (roomWithWholeCapacity && !wholeCapacityField.val()) {
                        wholeCapacityField.val(roomWithWholeCapacity.whole_capacity);
                        console.log('Populated whole_capacity field with:', roomWithWholeCapacity.whole_capacity);
                    }
                }

                // Helper function to format date from database to yyyy-MM-dd
                function formatDateForInput(dateString) {
                    if (!dateString) return '';

                    // If already in correct format, return as-is
                    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                        return dateString;
                    }

                    // Parse ISO 8601 format and convert to yyyy-MM-dd
                    try {
                        const date = new Date(dateString);
                        if (isNaN(date.getTime())) return '';

                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');

                        return `${year}-${month}-${day}`;
                    } catch (e) {
                        console.error('Error formatting date:', dateString, e);
                        return '';
                    }
                }

                // Load prices data
                let pricesRaw = $('#pricesJson').val();
                if (pricesRaw) {
                    prices = typeof pricesRaw === 'string' ? JSON.parse(pricesRaw) : pricesRaw;

                    prices = prices.map(price => ({
                        id: price.id || null,
                        priceName: price.priceName || price.name || '',
                        priceValue: price.priceValue || price.value || '',
                        priceType: price.priceType || price.price_type || 'individual',
                        isBasedOnDays: price.isBasedOnDays != null ? price.isBasedOnDays : (price
                            .is_based_on_days == 1 ? '1' : '0'),
                        isThereAQuantity: price.isThereAQuantity != null ? price.isThereAQuantity : (price
                            .is_there_a_quantity == 1 ? '1' : '0'),
                        isThisADiscount: price.isThisADiscount != null ? price.isThisADiscount : (price
                            .is_this_a_discount == 1 ? '1' : '0'),
                        dateFrom: formatDateForInput(price.dateFrom || price.date_from || ''),
                        dateTo: formatDateForInput(price.dateTo || price.date_to || '')
                    }));

                    if (prices.length > 0) {
                        const firstPrice = prices[0];
                        globalPriceSettings.isBasedOnDays = firstPrice.isBasedOnDays === '1';
                        globalPriceSettings.isThereAQuantity = firstPrice.isThereAQuantity === '1';
                        globalPriceSettings.dateFrom = firstPrice.dateFrom;
                        globalPriceSettings.dateTo = firstPrice.dateTo;

                        $('#isBasedOnDaysGlobal').prop('checked', globalPriceSettings.isBasedOnDays);
                        $('#isThereAQuantityGlobal').prop('checked', globalPriceSettings.isThereAQuantity);

                        if (globalPriceSettings.isBasedOnDays) {
                            $('#dateFieldsContainerGlobal').show();
                            $('#date_from_global').val(globalPriceSettings.dateFrom);
                            $('#date_to_global').val(globalPriceSettings.dateTo);
                        }
                    }
                }

                console.log("Final loaded state - Rooms:", rooms.length, "Prices:", prices.length);

            } catch (error) {
                console.error('Failed to parse room/price JSON:', error);
                rooms = [];
                prices = [];
            }
        }

        function preserveFormData() {
            // Only override with old data if there were validation errors
            if (window.facilityFormConfig && window.facilityFormConfig.hasValidationErrors) {
                const oldRooms = {!! json_encode(old('facility_attributes_json', '[]')) !!};
                if (oldRooms && oldRooms !== '[]') {
                    try {
                        rooms = JSON.parse(oldRooms);
                        console.log("Using old rooms data due to validation errors:", rooms);
                    } catch (e) {
                        console.error("Failed to parse old rooms data:", e);
                    }
                }

                const oldPrices = {!! json_encode(old('prices_json', '[]')) !!};
                if (oldPrices && oldPrices !== '[]') {
                    try {
                        prices = JSON.parse(oldPrices);
                        console.log("Using old prices data due to validation errors:", prices);
                    } catch (e) {
                        console.error("Failed to parse old prices data:", e);
                    }
                }
            }

            const facilityType = $('#rentalType').val();
            if (facilityType) {
                showFacilityTypeFields(facilityType);
            }
        }

        function setupRadioButtonHandlers() {
            $('input[name="facility_selection_both"]').on('change', function() {
                const selectedValue = $(this).val();
                const hiddenField = $('input[name="facility_selection_both"][type="hidden"]');

                if (hiddenField.length > 0) {
                    hiddenField.val(selectedValue);
                    console.log('Updated hidden field to:', selectedValue);
                }

                if (selectedValue === 'whole') {
                    $('#selectionContent').hide();
                    $('#hideRoomBox').show();
                    $('#dormitoryRooms').hide();
                } else if (selectedValue === 'room') {
                    $('#selectionContent').hide();
                    $('#hideRoomBox').hide();
                    $('#dormitoryRooms').show();
                }
            });

            $('#rentalType').on('change', function() {
                const facilityType = $(this).val();
                showFacilityTypeFields(facilityType);
            });
        }

        function handleInitialUIState() {
            const facilityType = $('#rentalType').val();
            if (!facilityType) {
                console.log("No facility type selected, hiding all sections");
                resetToDefaultState();
                return;
            }

            $('#roomBox').show();
            $('#priceBox').show();

            if (facilityType === 'both' || facilityType === 'whole_place') {
                $('#discountBox').show();
            } else {
                $('#discountBox').hide();
            }

            if (facilityType === 'both') {
                handleBothTypeInitialization();
            } else if (facilityType === 'individual') {
                handleIndividualTypeInitialization();
            } else if (facilityType === 'whole_place') {
                handleWholePlaceTypeInitialization();
            }

            // if (facilityType === 'individual' || facilityType === 'both' || facilityType === 'whole_place') {
            // $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
            // }

            renderRoomList();
            renderPriceList();
        }

        function handleBothTypeInitialization() {
            $('#selectionBothType').show();

            let hasWholeCapacity = $('#roomCapacityWhole').val() !== '';
            if (!hasWholeCapacity && window.facilityFormConfig && window.facilityFormConfig.isEditMode) {
                // Check if any existing room data has whole_capacity
                const existingRooms = window.facilityFormConfig.existingRooms || [];
                hasWholeCapacity = existingRooms.some(room => room.whole_capacity && room.whole_capacity > 0);

                if (hasWholeCapacity) {
                    const wholeCapacityValue = existingRooms.find(room => room.whole_capacity && room.whole_capacity > 0)
                        ?.whole_capacity;
                    if (wholeCapacityValue) {
                        $('#roomCapacityWhole').val(wholeCapacityValue);
                        console.log('🔧 Populated whole capacity field with:', wholeCapacityValue);
                    }
                }
            }
            const hasRooms = rooms.length > 0;
            const isEditMode = window.facilityFormConfig && window.facilityFormConfig.isEditMode;

            let selectedMode = $('input[name="facility_selection_both"]:checked').val();
            if (!selectedMode) {
                if (hasWholeCapacity && !hasRooms) {
                    selectedMode = 'whole';
                    $('#hasWholeCapacity').prop('checked', true);
                } else if (hasRooms && !hasWholeCapacity) {
                    selectedMode = 'room';
                    $('#hasRooms').prop('checked', true);
                } else if (hasRooms) {

                    selectedMode = 'room';
                    $('#hasRooms').prop('checked', true);
                }
            }
            if (isEditMode && selectedMode) {
                $('input[name="facility_selection_both"]').prop('disabled', true);
                $('input[name="facility_selection_both"][type="hidden"]').remove();

                $('#facilityForm').append(
                    `<input type="hidden" name="facility_selection_both" value="${selectedMode}" id="hiddenFacilitySelection">`
                );

                if (!$('#edit-mode-notice').length) {
                    $('#selectionBothType').append(`
                <small class="text-muted mt-2 d-block" id="edit-mode-notice">
                    <i class="bi bi-info-circle me-1"></i>
                    Selection is locked in edit mode to preserve facility structure.
                </small>
            `);
                }
            }
            if (selectedMode === 'whole') {
                $('#selectionContent').hide();
                $('#hideRoomBox').show();
                $('#dormitoryRooms').hide();
            } else if (selectedMode === 'room') {
                $('#selectionContent').hide();
                $('#hideRoomBox').hide();
                $('#dormitoryRooms').show();
            } else {
                // No selection made yet
                $('#selectionContent').show();
                $('#hideRoomBox').hide();
                $('#dormitoryRooms').hide();
            }
        }

        function handleIndividualTypeInitialization() {
            console.log("=== Handling Individual Type Initialization ===");
            $('#selectionBothType').hide();
            $('#selectionContent').hide();
            $('#hideRoomBox').hide();
            $('#dormitoryRooms').show();
        }

        function handleWholePlaceTypeInitialization() {
            $('#selectionBothType').hide();
            $('#selectionContent').hide();
            $('#hideRoomBox').show();
            $('#dormitoryRooms').hide();
        }

        function showFacilityTypeFields(facilityType) {
            if (facilityType === 'both') {
                $('#selectionBothType').show();
                $('#roomBox').show();
                $('#priceBox').show();
                // $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
                $('#discountBox').show();
                $('#addonBox').show();

                const hasWholeCapacity = $('#roomCapacityWhole').val() !== '';
                const hasRooms = rooms.length > 0;

                if (hasWholeCapacity) {
                    $('#hasWholeCapacity').prop('checked', true);
                    $('#selectionContent').hide();
                    $('#hideRoomBox').show();
                    $('#dormitoryRooms').hide();
                    $('#addonBox').show();
                } else if (hasRooms) {
                    $('#hasRooms').prop('checked', true);
                    $('#selectionContent').hide();
                    $('#hideRoomBox').hide();
                    $('#dormitoryRooms').show();
                    $('#addonBox').show();
                } else {
                    $('#selectionContent').show();
                    $('#hideRoomBox').hide();
                    $('#dormitoryRooms').hide();
                    $('#addonBox').hide();
                }

            } else if (facilityType === 'individual') {
                $('#selectionBothType').hide();
                $('#roomBox').show();
                $('#hideRoomBox').hide();
                $('#dormitoryRooms').show();
                $('#selectionContent').hide();
                $('#priceBox').show();
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
                $('#discountBox').hide();
                $('#addonBox').show();
            } else if (facilityType === 'whole_place') {
                $('#selectionBothType').hide();
                $('#roomBox').show();
                $('#hideRoomBox').show();
                $('#dormitoryRooms').hide();
                $('#selectionContent').hide();
                $('#priceBox').show();
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
                $('#discountBox').show();
                $('#addonBox').show();
            } else {
                $('#roomBox').hide();
                $('#priceBox').hide();
                $('#discountBox').hide();
                $('#addonBox').hide();
            }

            renderRoomList();
            renderPriceList();
        }

        function resetToDefaultState() {
            $('#roomBox').hide();
            $('#priceBox').hide();
            $('#selectionBothType').hide();
            $('#selectionContent').hide();
            $('#hideRoomBox').hide();
            $('#dormitoryRooms').hide();
            $('#isBasedOnDaysContainer, #isThereAQuantityContainer').hide();
            $('#discountBox').hide();
        }
    </script>
@endpush
