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
                    <fieldset class="year_level">
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

                    <!-- Department -->
                    <fieldset class="department">
                        <div class="body-title mb-10">Department</div>
                        <div class="select">
                            <select name="department" id="department">
                                <option value="" disabled selected>Select Department</option>
                                @foreach (['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'] as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('department')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <!-- Course -->
                    <fieldset class="course">
                        <div class="body-title mb-10">Course</div>
                        <div class="select">
                            <select name="course" id="course">
                                <option value="" disabled selected>Select Course</option>
                                <!-- Course options will be populated dynamically based on department -->
                            </select>
                        </div>
                    </fieldset>
                    @error('course')
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
                <input type="hidden" name="role" value="admin">
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

                    <fieldset class="email">
                        <div class="body-title mb-10">Email <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="email" placeholder="Enter admin email" name="email" required>
                    </fieldset>
                    @error('email')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <!-- Password Display (read-only) -->
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
            const departmentSelect = document.getElementById('department');
            const courseSelect = document.getElementById('course');
            const roleSelect = document.getElementById('roleSelect');
            const yearLevelSelect = document.getElementById('yearLevel');

            // Admin form specific elements
            const adminEmailInput = document.querySelector('#admin-form input[name="email"]');

            // Show user form
            selectUserBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'none';
                userForm.style.display = 'block';
                adminForm.style.display = 'none';
                pageTitle.textContent = 'Add User';
                breadcrumbTitle.textContent = 'Add User';
            });

            // Show admin form
            selectAdminBtn.addEventListener('click', function() {
                userTypeSelection.style.display = 'none';
                userForm.style.display = 'none';
                adminForm.style.display = 'block';
                pageTitle.textContent = 'Add Admin';
                breadcrumbTitle.textContent = 'Add Admin';
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

            const courses = {
                CEIT: ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering',
                    'BS Computer Science', 'BS Information Technology'
                ],
                GSOLC: ['PhD in Agriculture', 'PhD in Management', 'Master of Arts in Education',
                    'MS Agriculture'
                ],
                CAFENR: ['BS Agriculture', 'BS Environmental Science', 'BS Food Technology'],
                CAS: ['BA Political Science', 'BS Psychology', 'BS Biology'],
                CCJ: ['BS Criminology'],
                CEMDS: ['BS Accountancy', 'BS Business Management'],
                CED: ['Bachelor of Elementary Education', 'Bachelor of Secondary Education'],
                CON: ['BS Nursing'],
                CVMBS: ['Doctor of Veterinary Medicine']
            };

            if (departmentSelect && courseSelect) {
                departmentSelect.addEventListener('change', function() {
                    const selectedDepartment = this.value;
                    courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';
                    if (courses[selectedDepartment]) {
                        courses[selectedDepartment].forEach(function(course) {
                            const option = document.createElement('option');
                            option.value = course;
                            option.textContent = course;
                            courseSelect.appendChild(option);
                        });
                    }
                });
            }

            // Disable Year Level and Course for Employees and Non-Employees
            if (roleSelect && yearLevelSelect && courseSelect) {
                roleSelect.addEventListener('change', function() {
                    if (this.value === 'employee' || this.value === 'non-employee') {
                        yearLevelSelect.value = '';
                        yearLevelSelect.disabled = true;
                        courseSelect.value = '';
                        courseSelect.disabled = true;
                    } else {
                        yearLevelSelect.disabled = false;
                        courseSelect.disabled = false;
                    }
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
                });
            });
        });
    </script>
@endpush
