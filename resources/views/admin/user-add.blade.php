@extends('layouts.admin')
@section('content')
    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 id="page-title">Add User</h3>
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
                        <a href="{{ route('admin.users') }}">
                            <div class="text-tiny">Users</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny" id="breadcrumb-title">Add User</div>
                    </li>
                </ul>
            </div>

            <!-- User Type Selection -->
            <div class="wg-box mb-4" id="user-type-selection">
                <div class="body-title mb-20 text-center">Select User Type</div>
                <div class="flex items-center justify-center gap20 flex-wrap">
                    <button type="button" class="tf-button style-1 w208" id="select-user" data-type="user">
                        <i class="icon-user"></i>
                        Add User
                    </button>
                    <button type="button" class="tf-button style-2 w208" id="select-admin" data-type="admin">
                        <i class="icon-settings"></i>
                        Add Admin
                    </button>
                </div>
            </div>

            <!-- User Form -->
            <form class="form-add-user" method="POST" enctype="multipart/form-data"
                action="{{ route('admin.users.store') }}" id="user-form" style="display: none;">
                @csrf
                <input type="hidden" name="form_type" value="user">

                <div class="wg-box">
                    <div class="body-title mb-20 text-center">
                        <i class="icon-user"></i> Add New User
                    </div>

                    <fieldset class="name">
                        <div class="body-title mb-10">Name <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Enter user name" name="name" required>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="email">
                        <div class="body-title mb-10">Email <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="email" placeholder="Enter user email" name="email" required>
                    </fieldset>
                    @error('email')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="role">
                        <div class="body-title mb-10">Role <span class="tf-color-1">*</span></div>
                        <div class="select">
                            <select name="role" id="roleSelect" required>
                                <option value="student">Student</option>
                                <option value="employee">Employee</option>
                                <option value="non-employee">Non-Employee</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('role')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <!-- Year Level -->
                    <fieldset class="year_level" id="yearLevelFieldset">
                        <div class="body-title mb-10">Year Level</div>
                        <div class="select">
                            <select name="year_level" id="yearLevel">
                                <option value="" disabled selected>Select Year Level</option>
                                @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('year_level')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <!-- Colleges -->
                    <fieldset class="college" id="collegeFieldset">
                        <div class="body-title mb-10">College</div>
                        <div class="select">
                            <select name="college_id" id="college_id">
                                <option value="" disabled selected>Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->code }} - {{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('college_id')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <!-- Course -->
                    <fieldset class="course" id="courseFieldset">
                        <div class="body-title mb-10">Course</div>
                        <div class="select">
                            <select name="course_id" id="course_id">
                                <option value="" disabled selected>Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-college="{{ $course->college_id }}">
                                        {{ $course->code }} - {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('course_id')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="phone_number">
                        <div class="body-title mb-10">Phone Number</div>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="phoneNumber" name="phone_number"
                                placeholder="Phone Number" pattern="^9\d{9}$" maxlength="10">
                        </div>
                    </fieldset>
                    @error('phone_number')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <div class="bot">
                        <button type="button" class="tf-button style-3 w208" id="back-to-selection-user">
                            <i class="icon-arrow-left"></i>
                            Back
                        </button>
                        <button class="tf-button w208" type="submit">
                            <i class="icon-save"></i>
                            Save User
                        </button>
                    </div>
                </div>
            </form>

            <!-- Admin Form -->
            <form class="form-add-admin" method="POST" enctype="multipart/form-data"
                action="{{ route('admin.users.store') }}" id="admin-form" style="display: none;">
                @csrf
                <input type="hidden" name="form_type" value="admin">
                <input type="hidden" name="role" value="employee">
                <input type="hidden" name="password" value="cvsu-barg-password">

                <div class="wg-box">
                    <div class="body-title mb-20 text-center">
                        <i class="icon-settings"></i> Add New Admin
                    </div>

                    <fieldset class="name">
                        <div class="body-title mb-10">Name <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Enter admin name" name="name" required>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="sex">
                        <div class="body-title mb-10">Sex <span class="tf-color-1">*</span></div>
                        <div class="d-flex gap-20 align-items-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sex" id="sexMale"
                                    value="male" {{ old('sex') == 'male' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sexMale">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sex" id="sexFemale"
                                    value="female" {{ old('sex') == 'female' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sexFemale">Female</label>
                            </div>
                        </div>
                        @error('sex')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <fieldset class="email">
                        <div class="body-title mb-10">Email <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="email" placeholder="Enter admin email" name="email" required>
                    </fieldset>
                    @error('email')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="password">
                        <div class="body-title mb-10">Default Password</div>
                        <div class="password-display-wrapper">
                            <input class="mb-10" type="text" value="cvsu-barg-password" readonly
                                style="background-color: #f8f9fa; cursor: not-allowed;">
                            <small class="text-muted">This is the default password for new admins</small>
                        </div>
                    </fieldset>

                    <fieldset class="phone_number">
                        <div class="body-title mb-10">Phone Number</div>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="adminPhoneNumber" name="phone_number"
                                placeholder="Phone Number" pattern="^9\d{9}$" maxlength="10">
                        </div>
                    </fieldset>
                    @error('phone_number')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <div class="bot">
                        <button type="button" class="tf-button style-3 w208" id="back-to-selection-admin">
                            <i class="icon-arrow-left"></i>
                            Back
                        </button>
                        <button class="tf-button w208" type="submit">
                            <i class="icon-save"></i>
                            Save Admin
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->

    <style>
        /* Enhanced styling for user type selection */
        #user-type-selection {
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border: 2px dashed #e1e5f2;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        #user-type-selection:hover {
            border-color: #6366f1;
            background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
        }

        .tf-button {
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
        }

        .tf-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tf-button.style-1 {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
        }

        .tf-button.style-2 {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: none;
            color: white;
        }

        .tf-button.style-3 {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            border: none;
            color: white;
        }

        /* Form styling improvements */
        .wg-box {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .password-display-wrapper {
            position: relative;
        }

        .password-display-wrapper small {
            display: block;
            margin-top: 5px;
            font-style: italic;
        }

        /* Animation for form transitions */
        .form-add-user,
        .form-add-admin {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .flex.items-center.justify-center.gap20 {
                flex-direction: column;
                gap: 15px;
            }

            .tf-button.w208 {
                width: 100% !important;
                max-width: 300px;
            }

            .bot {
                flex-direction: column;
                gap: 15px;
            }

            .bot button {
                width: 100% !important;
            }
        }

           .tf-button:disabled {
            background: linear-gradient(135deg, #cccccc 0%, #999999 100%) !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
        }
        
        .tf-button:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form elements
            const userTypeSelection = document.getElementById('user-type-selection');
            const userForm = document.getElementById('user-form');
            const adminForm = document.getElementById('admin-form');
            const selectUserBtn = document.getElementById('select-user');
            const selectAdminBtn = document.getElementById('select-admin');
            const backToSelectionUserBtn = document.getElementById('back-to-selection-user');
            const backToSelectionAdminBtn = document.getElementById('back-to-selection-admin');
            const pageTitle = document.getElementById('page-title');
            const breadcrumbTitle = document.getElementById('breadcrumb-title');

            // User form specific elements
            const emailInput = document.querySelector('#user-form input[name="email"]');
            const nameInput = document.querySelector('#user-form input[name="name"]');
            const phoneInput = document.querySelector('#user-form input[name="phone_number"]');
            const roleSelect = document.getElementById('roleSelect');
            const yearLevelSelect = document.getElementById('yearLevel');
            const yearLevelFieldset = document.getElementById('yearLevelFieldset');
            const collegeFieldset = document.getElementById('collegeFieldset');
            const courseFieldset = document.getElementById('courseFieldset');
            const collegeSelect = document.getElementById('college_id');
            const courseSelect = document.getElementById('course_id');
            const saveUserBtn = userForm.querySelector('button[type="submit"]');

            // Admin form specific elements
            const adminEmailInput = document.querySelector('#admin-form input[name="email"]');
            const adminNameInput = document.querySelector('#admin-form input[name="name"]');
            const adminPhoneInput = document.querySelector('#admin-form input[name="phone_number"]');
            const adminSexInputs = document.querySelectorAll('#admin-form input[name="sex"]');
            const saveAdminBtn = adminForm.querySelector('button[type="submit"]');

            // Show user form
            selectUserBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'none';
                userForm.style.display = 'block';
                adminForm.style.display = 'none';
                pageTitle.textContent = 'Add User';
                breadcrumbTitle.textContent = 'Add User';
                validateUserForm();
            });

            // Show admin form
            selectAdminBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'none';
                userForm.style.display = 'none';
                adminForm.style.display = 'block';
                pageTitle.textContent = 'Add Admin';
                breadcrumbTitle.textContent = 'Add Admin';
                validateAdminForm();
            });

            // Back to selection from user form
            backToSelectionUserBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'block';
                userForm.style.display = 'none';
                adminForm.style.display = 'none';
                pageTitle.textContent = 'Add User';
                breadcrumbTitle.textContent = 'Add User';
                // Reset user form
                userForm.reset();
                resetStudentFields();
            });

            // Back to selection from admin form
            backToSelectionAdminBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'block';
                userForm.style.display = 'none';
                adminForm.style.display = 'none';
                pageTitle.textContent = 'Add User';
                breadcrumbTitle.textContent = 'Add User';
                // Reset admin form
                adminForm.reset();
            });

            // User Form Logic
            if (emailInput && userForm) {
                // Email Validation for "@cvsu.edu.ph" Domain
                userForm.addEventListener('submit', function(e) {
                    const emailValue = emailInput.value;
                    const emailDomain = '@cvsu.edu.ph';
                    if (!emailValue.endsWith(emailDomain)) {
                        e.preventDefault();
                        alert('The email must be a @cvsu.edu.ph email.');
                    }
                });
            }

            if (adminEmailInput && adminForm) {
                adminForm.addEventListener('submit', function(e) {
                    const emailValue = adminEmailInput.value;
                    const emailDomain = '@cvsu.edu.ph';
                    if (!emailValue.endsWith(emailDomain)) {
                        e.preventDefault();
                        alert('The email must be a @cvsu.edu.ph email.');
                    }
                });
            }

            // Role change handler
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    if (this.value === 'student') {
                        yearLevelFieldset.style.display = 'block';
                        collegeFieldset.style.display = 'block';
                        courseFieldset.style.display = 'block';
                        yearLevelSelect.required = true;
                        collegeSelect.required = true;
                        courseSelect.required = true;
                    } else {
                        yearLevelFieldset.style.display = 'none';
                        collegeFieldset.style.display = 'none';
                        courseFieldset.style.display = 'none';
                        yearLevelSelect.required = false;
                        collegeSelect.required = false;
                        courseSelect.required = false;
                        yearLevelSelect.value = '';
                        collegeSelect.value = '';
                        courseSelect.value = '';
                    }
                    validateUserForm();
                });
                
                // Trigger change event on page load
                roleSelect.dispatchEvent(new Event('change'));
            }

            // College change handler - filter courses
            if (collegeSelect && courseSelect) {
                collegeSelect.addEventListener('change', function() {
                    const collegeId = this.value;
                    const courseOptions = courseSelect.querySelectorAll('option');
                    
                    // Show all options initially
                    courseOptions.forEach(option => {
                        option.style.display = 'block';
                    });
                    
                    // Hide options that don't belong to the selected college
                    if (collegeId) {
                        courseOptions.forEach(option => {
                            if (option.value && option.dataset.college !== collegeId) {
                                option.style.display = 'none';
                            }
                        });
                        
                        // Reset course selection
                        courseSelect.value = '';
                    }
                    validateUserForm();
                });
            }

            // Phone number formatting for both forms
            const phoneInputs = document.querySelectorAll('input[name="phone_number"]');
            phoneInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');

                    if (value && !value.startsWith('9')) {
                        value = '9' + value.substring(1);
                    }

                    if (value.length > 10) {
                        value = value.substring(0, 10);
                    }

                    this.value = value;
                    
                    // Validate form after phone input
                    if (this.closest('#user-form')) {
                        validateUserForm();
                    } else if (this.closest('#admin-form')) {
                        validateAdminForm();
                    }
                });
            });

            // Add event listeners for form validation
            if (nameInput) {
                nameInput.addEventListener('input', validateUserForm);
            }
            
            if (emailInput) {
                emailInput.addEventListener('input', validateUserForm);
            }
            
            if (yearLevelSelect) {
                yearLevelSelect.addEventListener('change', validateUserForm);
            }
            
            if (collegeSelect) {
                collegeSelect.addEventListener('change', validateUserForm);
            }
            
            if (courseSelect) {
                courseSelect.addEventListener('change', validateUserForm);
            }
            
            // Admin form validation listeners
            if (adminNameInput) {
                adminNameInput.addEventListener('input', validateAdminForm);
            }
            
            if (adminEmailInput) {
                adminEmailInput.addEventListener('input', validateAdminForm);
            }
            
            if (adminSexInputs) {
                adminSexInputs.forEach(input => {
                    input.addEventListener('change', validateAdminForm);
                });
            }

            function validateUserForm() {
                if (!saveUserBtn) return;
                
                const name = nameInput ? nameInput.value.trim() : '';
                const email = emailInput ? emailInput.value.trim() : '';
                const phone = phoneInput ? phoneInput.value.trim() : '';
                const role = roleSelect ? roleSelect.value : '';
                const yearLevel = yearLevelSelect ? yearLevelSelect.value : '';
                const college = collegeSelect ? collegeSelect.value : '';
                const course = courseSelect ? courseSelect.value : '';
                
                let isValid = true;
                
                // Basic validation for all roles
                if (!name || !email) {
                    isValid = false;
                }
                
                // Phone validation (must be exactly 10 digits if provided)
                if (phone && phone.length !== 10) {
                    isValid = false;
                }
                
                // Additional validation for students
                if (role === 'student') {
                    if (!yearLevel || !college || !course) {
                        isValid = false;
                    }
                }
                
                saveUserBtn.disabled = !isValid;
                
                // Update button style based on validation
                if (isValid) {
                    saveUserBtn.classList.remove('disabled');
                    saveUserBtn.style.opacity = '1';
                    saveUserBtn.style.cursor = 'pointer';
                } else {
                    saveUserBtn.classList.add('disabled');
                    saveUserBtn.style.opacity = '0.6';
                    saveUserBtn.style.cursor = 'not-allowed';
                }
                
                return isValid;
            }
            
            function validateAdminForm() {
                if (!saveAdminBtn) return;
                
                const name = adminNameInput ? adminNameInput.value.trim() : '';
                const email = adminEmailInput ? adminEmailInput.value.trim() : '';
                const phone = adminPhoneInput ? adminPhoneInput.value.trim() : '';
                
                // Check if at least one sex option is selected
                let sexSelected = false;
                if (adminSexInputs) {
                    sexSelected = Array.from(adminSexInputs).some(input => input.checked);
                }
                
                let isValid = true;
                
                // Basic validation for admin
                if (!name || !email || !sexSelected) {
                    isValid = false;
                }
                
                // Phone validation (must be exactly 10 digits if provided)
                if (phone && phone.length !== 10) {
                    isValid = false;
                }
                
                saveAdminBtn.disabled = !isValid;
                
                // Update button style based on validation
                if (isValid) {
                    saveAdminBtn.classList.remove('disabled');
                    saveAdminBtn.style.opacity = '1';
                    saveAdminBtn.style.cursor = 'pointer';
                } else {
                    saveAdminBtn.classList.add('disabled');
                    saveAdminBtn.style.opacity = '0.6';
                    saveAdminBtn.style.cursor = 'not-allowed';
                }
                
                return isValid;
            }

            function resetStudentFields() {
                if (yearLevelSelect) yearLevelSelect.value = '';
                if (collegeSelect) collegeSelect.value = '';
                if (courseSelect) courseSelect.value = '';
                if (yearLevelSelect) yearLevelSelect.required = false;
                if (collegeSelect) collegeSelect.required = false;
                if (courseSelect) courseSelect.required = false;
                
                validateUserForm();
            }
            
            // Initial validation
            validateUserForm();
            validateAdminForm();
        });
    </script>
@endpush