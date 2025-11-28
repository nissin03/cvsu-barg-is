@extends('layouts.admin')
@section('content')
    <style>
        .addon-form-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 32px;
        }

        .field-group {
            position: relative;
            margin-bottom: 24px;
        }

        .field-label {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .required-asterisk {
            color: #dc2626;
            margin-left: 4px;
        }

        .field-input,
        .field-select,
        .field-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            color: #334155;
            font-size: 16px;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .field-input::placeholder,
        .field-textarea::placeholder {
            color: #94a3b8;
        }

        .field-input:focus,
        .field-select:focus,
        .field-textarea:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
        }

        .field-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: block;
            background: #fef2f2;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #fecaca;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            accent-color: #334155;
        }

        .checkbox-label {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .cancel-btn {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        .save-btn {
            background: #334155;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover:not(:disabled) {
            background: #475569;
        }

        .save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background: #94a3b8;
        }

        .conditional-field {
            display: none;
        }

        .conditional-field.visible {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        /* Remove the green valid state */
        .field-input:valid,
        .field-select:valid,
        .field-textarea:valid {
            border-color: #cbd5e1;
            /* Changed from #16a34a (green) to default border color */
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Create New Add-on</h3>
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
                        <a href="{{ route('admin.addons') }}">
                            <div class="text-tiny">Add-ons</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Create New Add-on</div>
                    </li>
                </ul>
            </div>

            <div class="addon-form-container">
                <form id="addonForm" action="{{ route('admin.addons.store') }}" method="POST">
                    @csrf

                    <div class="field-group">
                        <label class="field-label">
                            Add-on Name
                            <span class="required-asterisk">*</span>
                        </label>
                        <input class="field-input" type="text" placeholder="Enter addon name" name="name"
                            id="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label">Description</label>
                        <textarea class="field-textarea" placeholder="Enter addon description" name="description" id="description">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="field-group">
                            <label class="field-label">
                                Price Type
                                <span class="required-asterisk">*</span>
                            </label>
                            <select class="field-select" name="price_type" id="priceType" required>
                                <option value="">Select Price Type</option>
                                <option value="per_unit" {{ old('price_type') == 'per_unit' ? 'selected' : '' }}>Per Unit
                                </option>
                                <option value="flat_rate" {{ old('price_type') == 'flat_rate' ? 'selected' : '' }}>Flat Rate
                                </option>
                                <option value="per_night" {{ old('price_type') == 'per_night' ? 'selected' : '' }}>Per Night
                                    / Per Day</option>
                                <option value="per_item" {{ old('price_type') == 'per_item' ? 'selected' : '' }}>Per Item
                                </option>
                            </select>
                            @error('price_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Base Price
                                <span class="required-asterisk">*</span>
                            </label>
                            <input class="field-input" type="number" step="0.01" min="0" placeholder="0.00"
                                name="base_price" id="base_price" value="{{ old('base_price') }}" required>
                            @error('base_price')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">
                            Billing Cycle
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="field-select" name="billing_cycle" id="billingCycle" required>
                            <option value="">Select Billing Cycle</option>
                            <option value="per_day" {{ old('billing_cycle', 'per_day') == 'per_day' ? 'selected' : '' }}>
                                Per Day</option>
                            <option value="per_contract" {{ old('billing_cycle') == 'per_contract' ? 'selected' : '' }}>Per
                                Contract</option>
                        </select>
                        @error('billing_cycle')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <input type="hidden" name="show" value="both">

                    <div id="conditionalFields">
                        <div id="perUnitFields" class="conditional-field">
                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_available_unit" name="is_available"
                                    value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_available_unit">
                                    Currently available
                                </label>
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    Capacity
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input class="field-input" type="number" min="1" placeholder="Enter capacity"
                                    name="capacity" id="capacity" value="{{ old('capacity', 1) }}" required>
                                @error('capacity')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="quantity" id="hidden_quantity_unit" value="1">
                        </div>

                        <div id="flatRateFields" class="conditional-field">
                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_available_flat" name="is_available"
                                    value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_available_flat">
                                    Currently available
                                </label>
                            </div>

                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_refundable_flat"
                                    name="is_refundable" value="1" {{ old('is_refundable') ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_refundable_flat">
                                    Refundable
                                </label>
                            </div>
                            <input type="hidden" name="quantity" id="hidden_quantity_flat" value="">
                        </div>

                        <div id="perNightFields" class="conditional-field">
                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_available_night"
                                    name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_available_night">
                                    Currently available
                                </label>
                            </div>

                            <div class="field-group" id="quantityNightField">
                                <label class="field-label">
                                    Quantity <span style="color:#64748b; font-weight:400;">(optional)</span>
                                </label>
                                <input class="field-input" type="number" min="1"
                                    placeholder="Enter quantity (optional)" name="quantity_night" id="quantity_night"
                                    value="{{ old('quantity_night') }}">
                                @error('quantity_night')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="quantity" id="hidden_quantity_night"
                                value="{{ old('quantity_night', 1) }}">
                        </div>

                        <div id="perItemFields" class="conditional-field">
                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_available_item" name="is_available"
                                    value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_available_item">
                                    Currently available
                                </label>
                            </div>

                            <div class="checkbox-group">
                                <input class="checkbox-input" type="checkbox" id="is_based_on_quantity_item"
                                    name="is_based_on_quantity" value="1"
                                    {{ old('is_based_on_quantity', true) ? 'checked' : '' }}>
                                <label class="checkbox-label" for="is_based_on_quantity_item">
                                    Based on quantity
                                </label>
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    Quantity
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input class="field-input" type="number" min="1" placeholder="Enter quantity"
                                    name="quantity_item" id="quantity_item" value="{{ old('quantity_item', 1) }}"
                                    required>
                                @error('quantity_item')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="quantity" id="hidden_quantity_item"
                                value="{{ old('quantity_item', 1) }}">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.addons') }}" class="cancel-btn">
                            Cancel
                        </a>
                        <button type="submit" class="save-btn" id="createAddonBtn" disabled>
                            Create Add-on
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceTypeSelect = document.getElementById('priceType');
            const billingCycleField = document.getElementById('billingCycle');
            const perUnitFields = document.getElementById('perUnitFields');
            const flatRateFields = document.getElementById('flatRateFields');
            const perNightFields = document.getElementById('perNightFields');
            const perItemFields = document.getElementById('perItemFields');
            const quantityNightFieldInput = document.getElementById('quantity_night');
            const quantityItemFieldInput = document.getElementById('quantity_item');
            const createAddonBtn = document.getElementById('createAddonBtn');

            const hiddenQuantityUnit = document.getElementById('hidden_quantity_unit');
            const hiddenQuantityFlat = document.getElementById('hidden_quantity_flat');
            const hiddenQuantityNight = document.getElementById('hidden_quantity_night');
            const hiddenQuantityItem = document.getElementById('hidden_quantity_item');

            const commonRequiredFields = ['name', 'base_price', 'price_type', 'billing_cycle'];

            const priceTypeRequirements = {
                'per_unit': ['capacity'],
                'per_item': ['quantity_item'],
                'per_night': [],
                'flat_rate': []
            };

            function hideAllConditional() {
                perUnitFields.classList.remove('visible');
                flatRateFields.classList.remove('visible');
                perNightFields.classList.remove('visible');
                perItemFields.classList.remove('visible');
            }

            function toggleConditionalFields() {
                hideAllConditional();
                billingCycleField.disabled = false;

                switch (priceTypeSelect.value) {
                    case 'per_unit':
                        perUnitFields.classList.add('visible');
                        break;
                    case 'flat_rate':
                        flatRateFields.classList.add('visible');
                        break;
                    case 'per_night':
                        perNightFields.classList.add('visible');
                        if (quantityNightFieldInput) quantityNightFieldInput.required = false;
                        break;
                    case 'per_item':
                        perItemFields.classList.add('visible');
                        break;
                }

                updateHiddenQuantityFields();
                updateCreateButtonState();
            }

            function updateHiddenQuantityFields() {
                switch (priceTypeSelect.value) {
                    case 'per_unit':
                        hiddenQuantityUnit.disabled = false;
                        hiddenQuantityFlat.disabled = true;
                        hiddenQuantityNight.disabled = true;
                        hiddenQuantityItem.disabled = true;
                        break;
                    case 'flat_rate':
                        hiddenQuantityUnit.disabled = true;
                        hiddenQuantityFlat.disabled = false;
                        hiddenQuantityNight.disabled = true;
                        hiddenQuantityItem.disabled = true;
                        hiddenQuantityFlat.value = '';
                        break;
                    case 'per_night':
                        hiddenQuantityUnit.disabled = true;
                        hiddenQuantityFlat.disabled = true;
                        hiddenQuantityNight.disabled = false;
                        hiddenQuantityItem.disabled = true;
                        const nightValue = quantityNightFieldInput.value || '1';
                        hiddenQuantityNight.value = nightValue;
                        break;
                    case 'per_item':
                        hiddenQuantityUnit.disabled = true;
                        hiddenQuantityFlat.disabled = true;
                        hiddenQuantityNight.disabled = true;
                        hiddenQuantityItem.disabled = false;
                        const itemValue = quantityItemFieldInput.value || '1';
                        hiddenQuantityItem.value = itemValue;
                        break;
                }
            }

            function updateCreateButtonState() {
                const currentPriceType = priceTypeSelect.value;
                if (!currentPriceType) {
                    createAddonBtn.disabled = true;
                    return;
                }

                const requiredFields = [...commonRequiredFields, ...priceTypeRequirements[currentPriceType]];
                let allFieldsValid = true;

                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && !field.disabled) {
                        if (!field.value || String(field.value).trim() === '') {
                            allFieldsValid = false;
                        }
                    }
                });

                createAddonBtn.disabled = !allFieldsValid;
            }

            function setupFieldListeners() {
                const allFields = document.querySelectorAll(
                    '#addonForm input, #addonForm select, #addonForm textarea');
                allFields.forEach(field => {
                    field.addEventListener('input', function() {
                        updateHiddenQuantityFields();
                        updateCreateButtonState();
                    });
                    field.addEventListener('change', function() {
                        updateHiddenQuantityFields();
                        updateCreateButtonState();
                    });
                });

                if (quantityNightFieldInput) {
                    quantityNightFieldInput.addEventListener('input', updateHiddenQuantityFields);
                }
                if (quantityItemFieldInput) {
                    quantityItemFieldInput.addEventListener('input', updateHiddenQuantityFields);
                }
            }

            toggleConditionalFields();
            setupFieldListeners();

            priceTypeSelect.addEventListener('change', toggleConditionalFields);

            document.getElementById('addonForm').addEventListener('submit', function(e) {
                const currentPriceType = priceTypeSelect.value;
                const requiredFields = [...commonRequiredFields, ...priceTypeRequirements[
                    currentPriceType]];
                let isValid = true;

                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && !field.disabled) {
                        const hasValue = field.value && String(field.value).trim() !== '';
                        if (!hasValue) {
                            isValid = false;
                            field.style.borderColor = '#dc2626';
                            if (!field.nextElementSibling || !field.nextElementSibling.classList
                                .contains('error-message')) {
                                const errorMsg = document.createElement('span');
                                errorMsg.className = 'error-message';
                                errorMsg.textContent = 'This field is required';
                                field.parentNode.appendChild(errorMsg);
                            }
                        } else {
                            field.style.borderColor =
                                '#cbd5e1'; // Changed from green to default border color
                            if (field.nextElementSibling && field.nextElementSibling.classList
                                .contains('error-message')) {
                                field.nextElementSibling.remove();
                            }
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const firstError = this.querySelector('.error-message');
                    if (firstError) firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
        });
    </script>
@endpush
