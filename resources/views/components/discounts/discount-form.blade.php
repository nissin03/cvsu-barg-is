@props(['discount' => null, 'action', 'method' => 'POST'])

<style>
    .discount-form-container {
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
    .field-select {
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

    .field-input::placeholder {
        color: #94a3b8;
    }

    .field-input:focus,
    .field-select:focus {
        outline: none;
        border-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
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
</style>

<div class="discount-form-container">
    <form id="discountForm" action="{{ $action }}" method="POST">
        @csrf
        @if ($discount)
            @method('PUT')
        @endif

        <div class="field-group">
            <label class="field-label">
                Discount Name
                <span class="required-asterisk">*</span>
            </label>
            <input class="field-input" type="text" placeholder="Enter discount name" name="name" id="name"
                value="{{ old('name', $discount->name ?? '') }}" required>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-grid">
            <div class="field-group">
                <label class="field-label">
                    Discount Percent
                    <span class="required-asterisk">*</span>
                </label>
                <input class="field-input" type="number" step="0.01" min="0" max="100"
                    placeholder="0.00" name="percent" id="percent"
                    value="{{ old('percent', $discount->percent ?? '') }}" required>
                @error('percent')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="field-group">
                <label class="field-label">
                    Applies To
                    <span class="required-asterisk">*</span>
                </label>
                <select class="field-select" name="applies_to" id="applies_to" required>
                    <option value="">Select Scope</option>
                    <option value="all"
                        {{ old('applies_to', $discount->applies_to ?? '') === 'all' ? 'selected' : '' }}>
                        All Charges
                    </option>
                    <option value="venue_only"
                        {{ old('applies_to', $discount->applies_to ?? '') === 'venue_only' ? 'selected' : '' }}>
                        Venue Only
                    </option>
                </select>
                @error('applies_to')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="checkbox-group">
            <input class="checkbox-input" type="checkbox" id="requires_proof" name="requires_proof" value="1"
                {{ old('requires_proof', $discount->requires_proof ?? false) ? 'checked' : '' }}>
            <label class="checkbox-label" for="requires_proof">
                Requires ID/Proof upload
            </label>
        </div>

        <div class="checkbox-group">
            <input class="checkbox-input" type="checkbox" id="active" name="active" value="1"
                {{ old('active', $discount->active ?? true) ? 'checked' : '' }}>
            <label class="checkbox-label" for="active">
                Active
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('discounts.index') }}" class="cancel-btn">
                Cancel
            </a>
            <button type="submit" class="save-btn" id="submitDiscountBtn" {{ $discount ? '' : 'disabled' }}>
                {{ $discount ? 'Update Discount' : 'Create Discount' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('discountForm');
            const submitBtn = document.getElementById('submitDiscountBtn');
            const nameField = document.getElementById('name');
            const percentField = document.getElementById('percent');
            const appliesField = document.getElementById('applies_to');

            function validateForm() {
                const hasName = nameField.value.trim() !== '';
                const hasPercent = percentField.value !== '' &&
                    parseFloat(percentField.value) >= 0 &&
                    parseFloat(percentField.value) <= 100;
                const hasAppliesTo = appliesField.value !== '';

                submitBtn.disabled = !(hasName && hasPercent && hasAppliesTo);
            }

            nameField.addEventListener('input', validateForm);
            percentField.addEventListener('input', validateForm);
            appliesField.addEventListener('change', validateForm);

            // Initial validation
            validateForm();

            form.addEventListener('submit', function(e) {
                if (!nameField.value.trim() || !percentField.value || !appliesField.value) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
@endpush
