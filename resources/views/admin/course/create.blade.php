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
            $('#courseForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html('<i class="icon-loader"></i> Saving...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                html: '<div style="text-align: center; line-height: 1.6;"><strong>' +
                                    response.message +
                                    '</strong><br><br><small style="color: #666;">The course has been successfully created.</small></div>',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'Great!',
                                showCancelButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'swal2-success'
                                }
                            }).then(() => {
                                window.location.href =
                                    "{{ route('admin.courses.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalText);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });

                            Swal.fire({
                                title: 'Validation Error',
                                html: `
                                <div style="text-align: left; line-height: 1.6;">
                                    <p style="margin-bottom: 15px;">Please correct the following errors:</p>
                                    <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin: 15px 0;">
                                        ${errorMessage.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                            `,
                                confirmButtonColor: '#dc3545',
                                confirmButtonText: 'OK, I\'ll fix them',
                                customClass: {
                                    popup: 'swal2-error'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while saving the course. Please try again.',
                                confirmButtonColor: '#dc3545',
                                confirmButtonText: 'Try Again',
                                customClass: {
                                    popup: 'swal2-error'
                                }
                            });
                        }
                    }
                });
            });

            // $(document).on('input', 'input[name="code"]', function() {
            //     $(this).val($(this).val().toUpperCase());
            // });

            $(document).on('focus', '.field-input, .field-select', function() {
                $(this).parent().addClass('focused');
            });

            $(document).on('blur', '.field-input, .field-select', function() {
                $(this).parent().removeClass('focused');
            });
        });
    </script>

    @push('styles')
        <style>
            /* SweetAlert2 Custom Styles */
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
                background: linear-gradient(135deg, #f59e0b, #d97706) !important;
                color: white !important;
                box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3) !important;
            }

            .swal2-confirm:hover {
                background: linear-gradient(135deg, #d97706, #b45309) !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4) !important;
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

            /* Custom Icons using ::before pseudo-element */
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

            /* Success Icon */
            .swal2-popup.swal2-success::before {
                content: "✓" !important;
                color: #22c55e !important;
                border: 4px solid #22c55e !important;
                background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
            }

            /* Error Icon */
            .swal2-popup.swal2-error::before {
                content: "✕" !important;
                color: #ef4444 !important;
                border: 4px solid #ef4444 !important;
                background: linear-gradient(135deg, #fef2f2, #fecaca) !important;
            }

            /* Hide default SweetAlert2 icons */
            .swal2-icon {
                display: none !important;
            }

            /* Animation for icons */
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

                .swal2-popup::before {
                    width: 70px !important;
                    height: 70px !important;
                    line-height: 70px !important;
                    font-size: 28px !important;
                    margin: 15px auto 25px auto !important;
                }
            }
        </style>
    @endpush
@endpush
