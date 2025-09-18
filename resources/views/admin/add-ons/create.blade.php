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
    }
    
    .conditional-field {
        display: none;
    }
    
    .conditional-field.visible {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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
                            <option value="per_night" {{ old('price_type') == 'per_night' ? 'selected' : '' }}>Per Night</option>
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
                            value="{{ old('base_price') }}" 
                            required
                        >
                        @error('base_price')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
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
                
                <div id="capacityField" class="field-group conditional-field">
                    <label class="field-label">
                        Capacity
                        <span class="required-asterisk">*</span>
                    </label>
                    <input 
                        class="field-input" 
                        type="number" 
                        min="0" 
                        placeholder="Enter capacity" 
                        name="capacity" 
                        value="{{ old('capacity') }}"
                    >
                    @error('capacity')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div id="conditionalFields">
                    <div id="perUnitFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input 
                                class="checkbox-input" 
                                type="checkbox" 
                                id="is_based_on_quantity" 
                                name="is_based_on_quantity" 
                                value="1" 
                                {{ old('is_based_on_quantity', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_based_on_quantity">
                                Based on quantity
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input 
                                class="checkbox-input" 
                                type="checkbox" 
                                id="is_available" 
                                name="is_available" 
                                value="1" 
                                {{ old('is_available', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_available">
                                Currently available
                            </label>
                        </div>
                    </div>
                    
                    <div id="flatRateFields" class="conditional-field">
                        <div id="refundableField" class="checkbox-group">
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
                    </div>
                    
                    <div id="perNightFields" class="conditional-field">
                        <div class="checkbox-group">
                            <input 
                                class="checkbox-input" 
                                type="checkbox" 
                                id="is_based_on_quantity_night" 
                                name="is_based_on_quantity" 
                                value="1" 
                                {{ old('is_based_on_quantity', true) ? 'checked' : '' }}
                            >
                            <label class="checkbox-label" for="is_based_on_quantity_night">
                                Based on quantity
                            </label>
                        </div>
                        
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
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('admin.addons') }}" class="cancel-btn">
                        Cancel
                    </a>
                    <button type="submit" class="tf-button w-auto">
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
    const staffOption = showField.querySelector('option[value="staff"]');
    const capacityField = document.getElementById('capacityField');
    const perUnitFields = document.getElementById('perUnitFields');
    const flatRateFields = document.getElementById('flatRateFields');
    const perNightFields = document.getElementById('perNightFields');
    const refundableField = document.getElementById('refundableField');
    const isBasedOnQuantityCheckbox = document.getElementById('is_based_on_quantity');
    const isBasedOnQuantityNightCheckbox = document.getElementById('is_based_on_quantity_night');

    function toggleConditionalFields() {
        perUnitFields.classList.remove('visible');
        flatRateFields.classList.remove('visible');
        perNightFields.classList.remove('visible');
        capacityField.classList.remove('visible');

        switch(priceTypeSelect.value) {
            case 'per_unit':
                perUnitFields.classList.add('visible');
                break;
            case 'flat_rate':
                flatRateFields.classList.add('visible');
                break;
            case 'per_night':
                perNightFields.classList.add('visible');
                const quantityCheckboxGroup = isBasedOnQuantityNightCheckbox.closest('.checkbox-group');
                if (quantityCheckboxGroup) {
                    quantityCheckboxGroup.style.display = 'none';
                }
                if (isBasedOnQuantityNightCheckbox) {
                    isBasedOnQuantityNightCheckbox.checked = false;
                }
                break;
        }

        if (priceTypeSelect.value === 'per_unit' || priceTypeSelect.value === 'per_night') {
            staffOption.style.display = 'none';
            if (showField.value === 'staff') {
                showField.value = "";
            }
        } else {
            staffOption.style.display = 'block';
        }

        if (showField.value === 'staff') {
            refundableField.style.display = 'none';
        } else {
            refundableField.style.display = 'flex';
        }

        if (priceTypeSelect.value === 'per_unit' && isBasedOnQuantityCheckbox && isBasedOnQuantityCheckbox.checked) {
            capacityField.classList.add('visible');
            capacityField.querySelector('input').setAttribute('required', 'required');
        } else {
            capacityField.querySelector('input').removeAttribute('required');
        }

        if (priceTypeSelect.value === 'per_unit') {
            const perUnitQuantityGroup = isBasedOnQuantityCheckbox ? isBasedOnQuantityCheckbox.closest('.checkbox-group') : null;
            if (perUnitQuantityGroup) {
                perUnitQuantityGroup.style.display = 'flex';
            }
        }
    }

    toggleConditionalFields();

    priceTypeSelect.addEventListener('change', toggleConditionalFields);
    showField.addEventListener('change', toggleConditionalFields);

    if (isBasedOnQuantityCheckbox) {
        isBasedOnQuantityCheckbox.addEventListener('change', toggleConditionalFields);
    }

    document.getElementById('addonForm').addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = this.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
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
        });

        if (!isValid) {
            e.preventDefault();
            const firstError = this.querySelector('.error-message');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
</script>
@endpush

