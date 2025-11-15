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
            background: #334155;
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
            color: #334155;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #334155;
        }

        .college-title {
            color: #334155;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            flex-grow: 1;
            margin-left: 16px;
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
            border-color: #334155;
            box-shadow: 0 0 0 3px rgba(51, 65, 85, 0.15);
        }

        .field-input.error,
        .field-select.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
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

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .cancel-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .cancel-btn:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
            text-decoration: none;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .save-btn:hover:not(:disabled) {
            background: #475569;
            transform: translateY(-1px);
        }

        .save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .cancel-btn,
            .save-btn {
                width: 100%;
                justify-content: center;
            }
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
                <h3>Edit Course</h3>
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
                        <div class="text-tiny">Edit Course</div>
                    </li>
                </ul>
            </div>

            <div class="form-container">
                <form id="courseForm" action="{{ route('admin.courses.update', $course->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div id="colleges-container" class="colleges-container">
                        <div class="college-card" id="college-0">
                            <div class="college-header">
                                <div class="college-number">1</div>
                                <h4 class="college-title">Edit Course Information</h4>
                            </div>

                            <div class="college-fields">
                                <div class="field-group">
                                    <label class="field-label">
                                        Course Name
                                        <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input @error('name') error @enderror" type="text"
                                        placeholder="Enter course name" name="name"
                                        value="{{ old('name', $course->name) }}" aria-required="true" required
                                        maxlength="255">
                                    @error('name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label class="field-label">
                                        Course Code
                                        <span class="required-asterisk">*</span>
                                    </label>
                                    <input class="field-input @error('code') error @enderror" type="text"
                                        placeholder="Enter course code" name="code"
                                        value="{{ old('code', $course->code) }}" aria-required="true" required
                                        maxlength="50">
                                    @error('code')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group" style="grid-column: 1 / -1;">
                                    <label class="field-label">
                                        College
                                        <span class="required-asterisk">*</span>
                                    </label>
                                    <select class="field-select @error('college_id') error @enderror" name="college_id"
                                        aria-required="true" required>
                                        <option value="">Select College</option>
                                        @foreach ($colleges as $collegeOption)
                                            <option value="{{ $collegeOption->id }}"
                                                {{ old('college_id', $course->college_id) == $collegeOption->id ? 'selected' : '' }}>
                                                {{ $collegeOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('college_id')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.courses.index') }}" class="cancel-btn">
                            <i class="icon-x"></i>
                            Cancel
                        </a>

                        <button type="submit" class="save-btn">
                            <i class="icon-save"></i> Update Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
