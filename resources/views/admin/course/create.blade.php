@extends('layouts.admin')
@section('content')
    <style>
        .colleges-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-bottom: 32px;
        }

        .college-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .college-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .college-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #e2e8f0;
        }

        .college-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .college-number {
            background: #f8fafc;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #e2e8f0;
        }

        .college-title {
            color: #334155;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            flex-grow: 1;
            margin-left: 16px;
        }

        .remove-college {
            background: #ac0927;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            color: #e3e6eb;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remove-college:hover {
            background: #6b0409;
            color: #f2f3f5;
            border-color: #cbd5e1;
        }

        .college-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .college-fields {
                grid-template-columns: 1fr;
            }

            .college-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .college-title {
                margin-left: 0;
            }
        }

        .field-group {
            position: relative;
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

        .add-college-btn {
            background: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-college-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .save-btn {
            background: #334155;
            color: white;
            border: none;
            padding: 16px 40px;
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

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .add-college-btn,
            .save-btn {
                width: 100%;
                justify-content: center;
            }
        }

        .breadcrumb-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            color: #1e293b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b;
        }

        .breadcrumbs a {
            color: #475569;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumbs a:hover {
            color: #334155;
        }

        .breadcrumb-separator {
            color: #cbd5e1;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Courses</h3>
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
                        <a href="{{ route('admin.courses.index') }}">
                            <div class="text-tiny">Courses</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add New Course</div>
                    </li>
                </ul>
            </div>

            <div class="form-container">
                <form id="courseForm" action="{{ route('admin.courses.store') }}" method="POST">
                    @csrf
                    <div class="college-card">
                        <div class="college-header">
                            <div class="college-number">1</div>
                            <h4 class="college-title">Course Information</h4>
                        </div>

                        <div class="college-fields">
                            <div class="field-group">
                                <label class="field-label">
                                    Course Name
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input class="field-input" type="text" placeholder="Enter course name" name="name"
                                    tabindex="0" value="{{ old('name') }}" aria-required="true" required>
                                @error('name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    Course Code
                                    <span class="required-asterisk">*</span>
                                </label>
                                <input class="field-input" type="text" placeholder="Enter course code" name="code"
                                    tabindex="0" value="{{ old('code') }}" aria-required="true" required>
                                @error('code')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label class="field-label">
                                    College
                                    <span class="required-asterisk">*</span>
                                </label>
                                <select class="field-select" name="college_id" aria-required="true" required>
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}"
                                            {{ old('college_id') == $college->id ? 'selected' : '' }}>
                                            {{ $college->name }} ({{ $college->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('college_id')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.courses.index') }}" class="add-college-btn">
                            <i class="icon-arrow-left"></i>
                            Back to Courses
                        </a>

                        <button class="tf-button w208" type="submit">
                            <i class="icon-save"></i>
                            Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('focus', '.field-input, .field-select', function() {
                $(this).parent().addClass('focused');
            });

            $(document).on('blur', '.field-input, .field-select', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>
@endpush
