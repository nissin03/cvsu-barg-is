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

        /* Remove the green valid state */
        .field-input:valid,
        .field-select:valid,
        .field-textarea:valid {
            border-color: #cbd5e1;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Create New Signature</h3>
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
                        <a href="{{ route('admin.signatures.index') }}">
                            <div class="text-tiny">Signatures</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Create New Signature</div>
                    </li>
                </ul>
            </div>

            <div class="addon-form-container">
                <form id="signatureForm" action="{{ route('admin.signatures.store') }}" method="POST">
                    @csrf

                    <div class="form-grid">
                        <div class="field-group">
                            <label class="field-label">
                                Name
                                <span class="required-asterisk">*</span>
                            </label>
                            <input class="field-input" type="text" name="name" id="name" placeholder="Enter name"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Position
                                <span class="required-asterisk">*</span>
                            </label>
                            <input class="field-input" type="text" name="position" id="position"
                                placeholder="Enter position" value="{{ old('position') }}" required>
                            @error('position')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field-group">
                            <label class="field-label">
                                Category
                                <span class="required-asterisk">*</span>
                            </label>
                            <select class="field-select" name="category" id="category" required>
                                <option value="">Select Category</option>
                                <option value="facility" {{ old('category') == 'facility' ? 'selected' : '' }}>Facility
                                </option>
                                <option value="product" {{ old('category') == 'product' ? 'selected' : '' }}>Product
                                </option>
                            </select>
                            @error('category')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field-group">
                            <label class="field-label">
                                Report Type
                                <span class="required-asterisk">*</span>
                            </label>
                            <select class="field-select" name="report_type" id="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="sales" {{ old('report_type') == 'sales' ? 'selected' : '' }}>Sales</option>
                                <option value="product" {{ old('report_type') == 'product' ? 'selected' : '' }}>Product
                                </option>
                                {{-- <option value="facility" {{ old('report_type') == 'facility' ? 'selected' : '' }}>Facility
                                </option> --}}
                                <option value="inventory" {{ old('report_type') == 'inventory' ? 'selected' : '' }}>
                                    Inventory</option>
                                {{-- <option value="users" {{ old('report_type') == 'users' ? 'selected' : '' }}>Users</option> --}}
                                <option value="all" {{ old('report_type') == 'all' ? 'selected' : '' }}>All</option>
                            </select>
                            @error('report_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field-group">
                            <label class="field-label">
                                Label
                                <span class="required-asterisk">*</span>
                            </label>
                            <input class="field-input" type="text" name="label" id="label"
                                placeholder="Noted by, approve by, etc.." value="{{ old('label') }}" required>
                            @error('label')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- <div class="field-group">
                            <label class="field-label">
                                Order
                                <span class="required-asterisk">*</span>
                            </label>
                            <input class="field-input" type="number" name="order_by" id="order_by" min="0"
                                placeholder="Enter display order" value="{{ old('order_by', 0) }}" required>
                            @error('order_by')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div> --}}
                    </div>

                    <div class="field-group">
                        <label class="field-label">Status</label>

                        <div class="checkbox-group">
                            <input class="checkbox-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active') ? 'checked' : '' }}>
                            <label class="checkbox-label" for="is_active">
                                Active
                            </label>
                        </div>

                        {{-- <div class="checkbox-group">
                            <input class="checkbox-input" type="checkbox" id="is_archived" name="is_archived" value="1"
                                {{ old('is_archived') ? 'checked' : '' }}>
                            <label class="checkbox-label" for="is_archived">
                                Archived
                            </label>
                        </div> --}}
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.signatures.index') }}" class="cancel-btn">
                            Cancel
                        </a>
                        <button type="submit" class="save-btn" id="createSignatureBtn">
                            Create Signature
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const categorySelect = document.getElementById('category');
        const reportTypeSelect = document.getElementById('report_type');

        function updateReportTypeOptions() {
            const selectedCategory = categorySelect.value;
            reportTypeSelect.innerHTML = "";

            if (selectedCategory === "facility") {
                reportTypeSelect.innerHTML = `
                    <option value="sales">Sales</option>
                `;
            } else {
                reportTypeSelect.innerHTML = `
                    <option value="">Select Report Type</option>
                    <option value="sales">Sales</option>
                    <option value="product">Product</option>
                   
                    <option value="inventory">Inventory</option>
                
                    <option value="all">All</option>
                `;
            }
        }

        categorySelect.addEventListener('change', updateReportTypeOptions);
        window.addEventListener('DOMContentLoaded', updateReportTypeOptions);
    </script>
@endpush
