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

        /* Loading state */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

                        <button type="submit" id="submitBtn">
                            <a class="tf-button w-auto"><i class="icon-save"></i> Update Course </a>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* SweetAlert2 Custom Styles - Clean Version */
        .swal2-popup {
            width: 90vw !important;
            max-width: 600px !important;
            min-height: 350px !important;
            padding: 35px !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25) !important;
            backdrop-filter: blur(10px) !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.95)) !important;
        }

        .swal2-title {
            font-size: 24px !important;
            font-weight: 700 !important;
            margin: 0 0 25px 0 !important;
            text-align: center !important;
            line-height: 1.3 !important;
            color: #1e293b !important;
        }

        .swal2-content {
            font-size: 16px !important;
            line-height: 1.6 !important;
            margin: 25px 0 35px 0 !important;
            text-align: center !important;
            color: #475569 !important;
        }

        .swal2-actions {
            margin: 35px 0 0 0 !important;
            gap: 15px !important;
            justify-content: center !important;
        }

        .swal2-confirm,
        .swal2-cancel {
            font-size: 15px !important;
            font-weight: 600 !important;
            padding: 12px 30px !important;
            min-width: 120px !important;
            height: 45px !important;
            border-radius: 8px !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm {
            background: #334155 !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(51, 65, 85, 0.3) !important;
        }

        .swal2-confirm:hover {
            background: #475569 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(51, 65, 85, 0.4) !important;
        }

        .swal2-cancel {
            background: #f8fafc !important;
            color: #64748b !important;
            border: 2px solid #cbd5e1 !important;
        }

        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            transform: translateY(-1px) !important;
        }

        /* Custom Icons */
        .swal2-popup::before {
            content: '' !important;
            display: block !important;
            text-align: center !important;
            margin: 20px auto 30px auto !important;
            width: 80px !important;
            height: 80px !important;
            line-height: 80px !important;
            border-radius: 50% !important;
            font-size: 32px !important;
            font-weight: 900 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            animation: swalIconPulse 1s ease-in-out !important;
        }

        .swal2-popup.swal2-success::before {
            content: "✓" !important;
            color: #22c55e !important;
            border: 4px solid #22c55e !important;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
        }

        .swal2-popup.swal2-error::before {
            content: "✕" !important;
            color: #ef4444 !important;
            border: 4px solid #ef4444 !important;
            background: linear-gradient(135deg, #fef2f2, #fecaca) !important;
        }

        .swal2-popup.swal2-info::before {
            content: "i" !important;
            color: #3b82f6 !important;
            border: 4px solid #3b82f6 !important;
            background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
            font-style: italic !important;
            font-size: 36px !important;
        }

        .swal2-popup.swal2-question::before {
            content: "?" !important;
            color: #8b5cf6 !important;
            border: 4px solid #8b5cf6 !important;
            background: linear-gradient(135deg, #faf5ff, #ede9fe) !important;
            font-size: 36px !important;
        }

        .swal2-icon {
            display: none !important;
        }

        @keyframes swalIconPulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .swal2-popup {
                width: 95vw !important;
                max-width: none !important;
                margin: 10px !important;
                padding: 25px !important;
                min-height: 300px !important;
            }

            .swal2-title {
                font-size: 20px !important;
                margin-bottom: 20px !important;
            }

            .swal2-content {
                font-size: 14px !important;
                margin: 20px 0 25px 0 !important;
            }

            .swal2-actions {
                flex-direction: column !important;
                width: 100% !important;
                margin-top: 25px !important;
                gap: 10px !important;
            }

            .swal2-confirm,
            .swal2-cancel {
                width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-uppercase the code field
            // $('input[name="code"]').on('input', function() {
            //     $(this).val($(this).val().toUpperCase());
            // });

            // Clear validation errors on input
            $('.field-input, .field-select').on('input change', function() {
                $(this).removeClass('error');
                $(this).siblings('.error-message').remove();
            });

            // Form submission handling with proper AJAX
            $('#courseForm').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = form.serialize();
                const submitBtn = $('#submitBtn');
                const originalHtml = submitBtn.html();

                // Disable submit button and show loading state
                submitBtn.prop('disabled', true).html('<i class="loading"></i> Updating...');

                // Clear previous errors
                $('.field-input, .field-select').removeClass('error');
                $('.error-message').remove();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Course updated successfully!',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#334155',
                            customClass: {
                                popup: 'swal2-success'
                            }
                        }).then(() => {
                            window.location.href =
                            "{{ route('admin.courses.index') }}";
                        });
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalHtml);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;

                            // Display field-specific errors
                            $.each(errors, function(field, messages) {
                                const input = $(`[name="${field}"]`);
                                input.addClass('error');

                                if (messages.length > 0) {
                                    input.after(
                                        `<span class="error-message">${messages[0]}</span>`
                                        );
                                }
                            });

                            // Show general error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please check the form and fix the highlighted errors.',
                                confirmButtonColor: '#334155',
                                customClass: {
                                    popup: 'swal2-error'
                                }
                            });

                            // Scroll to first error
                            const firstError = $('.field-input.error, .field-select.error')
                                .first();
                            if (firstError.length) {
                                $('html, body').animate({
                                    scrollTop: firstError.offset().top - 100
                                }, 500);
                                firstError.focus();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'An error occurred while updating the course. Please try again.',
                                confirmButtonColor: '#334155',
                                customClass: {
                                    popup: 'swal2-error'
                                }
                            });
                        }
                    }
                });
            });

            // Focus/blur effects
            $('.field-input, .field-select').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>
@endpush
