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

    .field-input:invalid,
    .field-select:invalid,
    .field-textarea:invalid {
        border-color: #dc2626;
    }

    .field-input:valid,
    .field-select:valid,
    .field-textarea:valid {
        border-color: #16a34a;
    }
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Add New Addon</h3>
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
                        <div class="text-tiny">Addons</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Add New Addon</div>
                </li>
            </ul>
        </div>

        <div class="addon-form-container">
            <form id="addonForm" action="{{ route('admin.addons.store') }}" method="POST">
                @csrf

                <div class="field-group">
                    <label class="field-label">
                        Addon Name
                        <span class="required-asterisk">*</span>
                    </label>
                    <input
                        class="field-input"
                        type="text"
                        placeholder="Enter addon name"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">Description</label>
                    <textarea
                        class="field-textarea"
                        placeholder="Enter addon description"
                        name="description"
                        id="description"
                    >{{ old('description') }}</textarea>
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
                            <option value="per_unit" {{ old('price_type') == 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                            <option value="flat_rate" {{ old('price_type') == 'flat_rate' ? 'selected' : '' }}>Flat Rate</option>
                            <option value="per_night" {{ old('price_type') == 'per_night' ? 'selected' : '' }}>Per Night / Per Day</option>
                            <option value="per_item" {{ old('price_type') == 'per_item' ? 'selected' : '' }}>Per Item</option>
                            <option value="per_hour" {{ old('price_type') == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
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
                        <input
                            class="field-input"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            name="base_price"
                            id="base_price"
                            value="{{ old('base_price') }}"
                            required
                        >
                        @error('base_price')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Billing Cycle Field -->
                <div class="field-group">
                    <label class="field-label">
                        Billing Cycle
                        <span class="required-asterisk">*</span>
                    </label>
                    <select class="field-select" name="billing_cycle" id="billingCycle" required>
                        <option value="">Select Billing Cycle</option>
                        <option value="per_day" {{ old('billing_cycle', 'per_day') == 'per_day' ? 'selected' : '' }}>Per Day</option>
                        <option value="per_contract" {{ old('billing_cycle') == 'per_contract' ? 'selected' : '' }}>Per Contract</option>
                    </select>
                    @error('billing_cycle')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">
                        Show
                        <span class="required-asterisk">*</span>
                    </label>
                    <select class="field-select" name="show" id="showField" required>
                        <option value="">Select Where It Will be Showing </option>
                        <option value="both" {{ old('show') == 'both' ? 'selected' : '' }}>User and Staff</option>
                        <option value="staff" {{ old('show') == 'staff' ? 'selected' : '' }}>Staff only</option>
                    </select>
                    @error('show')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Conditional Fields -->
                <div id="conditionalFields">
                    <!-- Per Unit Fields -->
                    <div id="perUnitFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_available_unit"
                                name="is_available"
                                value="1"
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available_unit">
                                Currently available
                            </label>
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Capacity
                                <span class="required-asterisk">*</span>
                            </label>
                            <input
                                class="field-input"
                                type="number"
                                min="1"
                                placeholder="Enter capacity"
                                name="capacity"
                                id="capacity"
                                value="{{ old('capacity', 1) }}"
                                required
                            >
                            @error('capacity')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Flat Rate Fields -->
                    <div id="flatRateFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_available_flat"
                                name="is_available"
                                value="1"
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available_flat">
                                Currently available
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_refundable_flat"
                                name="is_refundable"
                                value="1"
                                {{ old('is_refundable') ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_refundable_flat">
                                Refundable
                            </label>
                        </div>
                    </div>

                    <!-- Per Night Fields -->
                    <div id="perNightFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_available_night"
                                name="is_available"
                                value="1"
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available_night">
                                Currently available
                            </label>
                        </div>

                        <div class="field-group" id="quantityNightField">
                            <label class="field-label">
                                Quantity <span style="color:#64748b; font-weight:400;">(optional)</span>
                            </label>
                            <input
                                class="field-input"
                                type="number"
                                min="1"
                                placeholder="Enter quantity (optional)"
                                name="quantity"
                                id="quantity_night"
                                value="{{ old('quantity') }}"
                            >
                            @error('quantity')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Per Item Fields -->
                    <div id="perItemFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_available_item"
                                name="is_available"
                                value="1"
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available_item">
                                Currently available
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_based_on_quantity_item"
                                name="is_based_on_quantity"
                                value="1"
                                {{ old('is_based_on_quantity', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_based_on_quantity_item">
                                Based on quantity
                            </label>
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Quantity
                                <span class="required-asterisk">*</span>
                            </label>
                            <input
                                class="field-input"
                                type="number"
                                min="1"
                                placeholder="Enter quantity"
                                name="quantity"
                                id="quantity_item"
                                value="{{ old('quantity', 1) }}"
                                required
                            >
                            @error('quantity')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Per Hour Fields (Staff Only) -->
                    <div id="perHourFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input
                                class="checkbox-input"
                                type="checkbox"
                                id="is_available_hour"
                                name="is_available"
                                value="1"
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available_hour">
                                Currently available
                            </label>
                        </div>

                        <!-- Note for per_hour price type -->
                        <div class="field-group" style="background: #f0f9ff; padding: 16px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                            <p style="color: #0369a1; font-size: 14px; margin: 0;">
                                <strong>Note:</strong> Per hour addons are only visible to staff members.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.addons') }}" class="cancel-btn">
                        Cancel
                    </a>
                    <button type="submit" class="save-btn" id="createAddonBtn" disabled>
                        Create Addon
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
    const showField = document.getElementById('showField');
    const billingCycleField = document.getElementById('billingCycle');
    const perUnitFields = document.getElementById('perUnitFields');
    const flatRateFields = document.getElementById('flatRateFields');
    const perNightFields = document.getElementById('perNightFields');
    const perItemFields = document.getElementById('perItemFields');
    const perHourFields = document.getElementById('perHourFields');
    const quantityNightFieldInput = document.getElementById('quantity_night');
    const createAddonBtn = document.getElementById('createAddonBtn');

    const commonRequiredFields = ['name', 'base_price', 'price_type', 'billing_cycle', 'show'];

    const priceTypeRequirements = {
        'per_unit': ['capacity'],
        'per_item': ['quantity_item'],
        'per_night': [],
        'flat_rate': [],
        'per_hour': []
    };

    function hideAllConditional() {
        perUnitFields.classList.remove('visible');
        flatRateFields.classList.remove('visible');
        perNightFields.classList.remove('visible');
        perItemFields.classList.remove('visible');
        perHourFields.classList.remove('visible');
    }

    function toggleConditionalFields() {
        hideAllConditional();
        showField.disabled = false;
        billingCycleField.disabled = false;

        if (priceTypeSelect.value === 'per_hour') {
            showField.value = 'staff';
            showField.disabled = true;
            perHourFields.classList.add('visible');
        } else {
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
        }

        updateCreateButtonState();
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
        const allFields = document.querySelectorAll('#addonForm input, #addonForm select, #addonForm textarea');
        allFields.forEach(field => {
            field.addEventListener('input', updateCreateButtonState);
            field.addEventListener('change', updateCreateButtonState);
        });
    }

    toggleConditionalFields();
    setupFieldListeners();

    priceTypeSelect.addEventListener('change', toggleConditionalFields);

    document.getElementById('addonForm').addEventListener('submit', function(e) {
        const currentPriceType = priceTypeSelect.value;
        const requiredFields = [...commonRequiredFields, ...priceTypeRequirements[currentPriceType]];
        let isValid = true;

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.disabled) {
                const hasValue = field.value && String(field.value).trim() !== '';
                if (!hasValue) {
                    isValid = false;
                    field.style.borderColor = '#dc2626';
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '#cbd5e1';
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                        field.nextElementSibling.remove();
                    }
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            const firstError = this.querySelector('.error-message');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
@endpush
