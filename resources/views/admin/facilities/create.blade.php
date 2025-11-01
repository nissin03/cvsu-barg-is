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
            <form action="{{ route('admin.facilities.store') }}" id="facilityForm" class="tf-section-2 form-add-rental"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($facility))
                    @method('PUT')
                @endif
                <input type="hidden" id="facilityAttributesJson" name="facility_attributes_json">
                <input type="hidden" id="pricesJson" name="prices_json">


                @include('admin.facilities.partials.basic-info')

                @include('admin.facilities.partials.media-upload')

                <div class="wg-box" id="roomBox">
                    @include('admin.facilities.partials.room-management')
                    <div class="mt-3">
                        <x-addons.addon-selector :addons="$addons" />
                    </div>
                </div>

                <div class="wg-box" id="priceBox">

                    @include('admin.facilities.partials.pricing-management')
                    <x-discounts.discount-selector :discounts="$discounts" />


                    <div class="cols gap10">
                        <button id="facilitySubmitBtn" class="tf-button w-full" type="submit">
                            <span class="btn-text">Create Facility</span>
                        </button>
                    </div>
                </div>
                @include('admin.facilities.partials.modals.add-price-modal')
            </form>
        </div>

    </div>
    @include('admin.facilities.partials.modals.bulk-rooms-modal')
    {{-- @include('admin.facilities.partials.modals.add-addon-modal') --}}
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/form-validation.js') }}"></script>
    <script src="{{ asset('assets/js/rooms-management.js') }}"></script>
    <script src="{{ asset('assets/js/pricing-management.js') }}"></script>
    <script src="{{ asset('assets/js/addons-management.js') }}"></script>

    <script src="{{ asset('assets/js/hideFields.js') }}"></script>
    <script src="{{ asset('assets/js/imagefile.js') }}"></script>
    <script src="{{ asset('assets/js/discount-selector.js') }}"></script>
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
        });

        function initializeForm() {
            preserveFormData();
            setupFormValidation();
            setupRoomsManagement();
            setupPricingManagement();
            setupDiscountsUi();
            setupAddonsUi();
        }


        function preserveFormData() {
            const oldRooms = {!! json_encode(old('facility_attributes_json', '[]')) !!};
            if (oldRooms && oldRooms !== '[]') {
                try {
                    rooms = JSON.parse(oldRooms);
                } catch (e) {
                    rooms = [];
                }
            }

            const oldPrices = {!! json_encode(old('prices_json', '[]')) !!};
            if (oldPrices && oldPrices !== '[]') {
                try {
                    prices = JSON.parse(oldPrices);
                } catch (e) {
                    prices = [];
                }
            }

            const facilityType = $('#rentalType').val();
            if (facilityType) {
                showFacilityTypeFields(facilityType);
            }

            // Show price and room control options
            if (facilityType && (facilityType === 'individual' || facilityType === 'both')) {
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
            }

            renderRoomList();
            renderPriceList();
        }

        function showFacilityTypeFields(facilityType) {
            if (facilityType === 'both') {
                $('#selectionBothType').show();
                $('#roomBox').show();
                $('#priceBox').show();
                $('#hideRoomBox').hide();
                $('#dormitoryRooms').hide();
                $('#selectionContent').show();
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
                $('#discountBox').show();
                $('#addonBox').show();
            } else if (facilityType === 'individual') {
                $('#selectionBothType').hide();
                $('#roomBox').show();
                $('#hideRoomBox').hide();
                $('#dormitoryRooms').show();
                $('#selectionContent').hide();
                $('#priceBox').show();
                $('#discountBox').hide();
                $('#addonBox').show();
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
            } else if (facilityType === 'whole_place') {
                $('#selectionBothType').hide();
                $('#roomBox').show();
                $('#hideRoomBox').show();
                $('#dormitoryRooms').hide();
                $('#selectionContent').hide();
                $('#priceBox').show();
                $('#isBasedOnDaysContainer, #isThereAQuantityContainer').show();
                $('#addonBox').show();
                $('#discountBox').show();
            } else {
                $('#roomBox').hide();
                $('#priceBox').hide();
                $('#discountBox').hide();
                $('#addonBox').hide();
            }
        }

        // function setupDiscountsUi() {
        //     const selectEl = document.getElementById('discountMultiSelect');
        //     const hiddenEl = document.getElementById('selected_discounts');
        //     const previewEl = document.getElementById('selectedDiscountsPreview');
        //     const showBtn = document.getElementById('showSelectedDiscountsBtn');

        //     if (!selectEl || !hiddenEl || !previewEl || !showBtn) {
        //         return;
        //     }

        //     function updateHidden() {
        //         const values = Array.from(selectEl.selectedOptions).map(o => o.value);
        //         hiddenEl.value = values.join(',');
        //     }

        //     function renderPreview() {
        //         const items = Array.from(selectEl.selectedOptions).map(o => o.textContent);
        //         previewEl.innerHTML = items.length ? '<ul class="mb-0 ps-3">' + items.map(t => `<li>${t}</li>`).join('') +
        //             '</ul>' : '<span class="text-muted">No discounts selected</span>';
        //         previewEl.style.display = 'block';
        //     }

        //     // if (selectEl) {
        //     //     $('#discountBox').show();
        //     //     selectEl.addEventListener('change', updateHidden);
        //     // }
        //     // if (btn) {
        //     //     btn.addEventListener('click', renderPreview);
        //     // }

        //     selectEl.addEventListener('change', updateHidden);
        //     showBtn.addEventListener('click', renderPreview);
        // }
    </script>
@endpush
