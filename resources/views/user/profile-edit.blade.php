@extends('layouts.app')

@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}" :breadcrumbs="$breadcrumbs" />

    <style>
        .profile-sidebar {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-content {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-content h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }

        .profile-content p {
            font-size: 16px;
            color: #555;
        }

        .alert {
            margin-top: 20px;
        }

        .btn-black {
            background-color: #343a40;
            color: #fff;
        }

        .btn-black:hover {
            background-color: #23272b;
        }
        
        .btn-black:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        .input-error {
            border-color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-5">
                <div class="row h-100">
                    <x-profile-sidebar />

                    <div class="col-md-9 mb-4">
                        <div class="bg-white p-4 border rounded shadow-sm profile-content h-100">
                            <h2>Update Profile</h2>
                            <p>Edit your personal information</p>
                            <hr>
                            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="fullName" name="name" placeholder="Enter Full Name" disabled value="{{ old('name', $user->name) }}">
                                        <label for="fullName">Full Name</label>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" disabled value="{{ old('email', $user->email) }}">
                                        <label for="email">Email</label>
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phoneNumber" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+63</span>
                                        <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Phone Number" value="{{ old('phone_number', $user->phone_number) }}" pattern="^9\d{9}$" maxlength="10">
                                    </div>
                                    <div id="phoneError" class="error-message"></div>
                                    @error('phone_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="sex" name="sex" required>
                                            <option value="" disabled>Select Sex</option>
                                            <option value="male" {{ old('sex', $user->sex) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('sex', $user->sex) === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        <label for="sex" class="form-label">Sex</label>
                                    </div>
                                    @error('sex')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="role" name="role" {{ auth()->user()->role_change_allowed ? 'required' : 'disabled' }}>
                                            <option value="" disabled>Select Role</option>
                                            <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                            <option value="non-employee" {{ old('role', $user->role) == 'non-employee' ? 'selected' : '' }}>Non-Employee</option>
                                        </select>
                                        <label for="role">Role</label>
                                    </div>
                                    @error('role')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div id="studentFields" style="display: none;">
                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="yearLevel" name="year_level">
                                                <option value="" disabled>Select Year Level</option>
                                                @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                                    <option value="{{ $year }}" {{ old('year_level', $user->year_level) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                                @endforeach
                                            </select>
                                            <label for="yearLevel">Year Level</label>
                                            <div id="yearLevelError" class="error-message"></div>
                                            @error('year_level')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="college_id" name="college_id">
                                                <option value="" disabled selected>Select College</option>
                                                @foreach($colleges as $college)
                                                    <option value="{{ $college->id }}" {{ old('college_id', $user->college_id) == $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="college_id">College</label>
                                            <div id="collegeError" class="error-message"></div>
                                            @error('college_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="course_id" name="course_id">
                                                <option value="" disabled selected>Select Course</option>
                                                @if($user->college_id)
                                                    @foreach($colleges->find($user->college_id)->courses ?? [] as $course)
                                                        <option value="{{ $course->id }}" {{ old('course_id', $user->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <label for="course_id">Course</label>
                                            <div id="courseError" class="error-message"></div>
                                            @error('course_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div id="othersFields" style="display: none;"></div>

                                <div class="mt-4 mb-3">
                                    <button type="submit" class="btn btn-black" id="updateProfileBtn" disabled>Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const studentFields = document.getElementById('studentFields');
        const othersFields = document.getElementById('othersFields');
        const currentRole = '{{ old('role', $user->role) }}';
        const phoneInput = document.getElementById('phoneNumber');
        const updateBtn = document.getElementById('updateProfileBtn');
        const sexSelect = document.getElementById('sex');
        const yearLevelSelect = document.getElementById('yearLevel');
        const emailInput = document.getElementById('email');
        const collegeSelect = document.getElementById('college_id');
        const courseSelect = document.getElementById('course_id');
        
        // Error message elements
        const phoneError = document.getElementById('phoneError');
        const yearLevelError = document.getElementById('yearLevelError');
        const collegeError = document.getElementById('collegeError');
        const courseError = document.getElementById('courseError');

        function filterRoleOptions() {
            const email = emailInput.value.trim();
            const roleOptions = roleSelect.querySelectorAll('option');
            
            roleOptions.forEach(option => {
                option.style.display = 'block';
                option.disabled = false;
            });

            if (email.includes('@cvsu.edu.ph')) {
                roleOptions.forEach(option => {
                    if (option.value === 'non-employee') {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });
                
                if (roleSelect.value === 'non-employee') {
                    roleSelect.value = '';
                    updateFieldsVisibility('');
                }
            } else if (email.includes('@gmail.com')) {
                roleOptions.forEach(option => {
                    if (option.value === 'student' || option.value === 'employee') {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                });
                
                if (roleSelect.value === 'student' || roleSelect.value === 'employee') {
                    roleSelect.value = '';
                    updateFieldsVisibility('');
                }
            }
        }

        filterRoleOptions();

        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                if (this.value.length > 0 && this.value[0] !== '9') {
                    this.value = '9' + this.value.slice(1);
                }
                validateForm();
            });
        }

        function updateFieldsVisibility(role) {
            if (!role) {
                studentFields.style.display = 'none';
                othersFields.style.display = 'none';
                return;
            }

            studentFields.style.display = 'none';
            othersFields.style.display = 'none';

            if (role === 'student') {
                studentFields.style.display = 'block';
            } else if (role === 'employee' || role === 'non-employee') {
                othersFields.style.display = 'block';
            }
            
            validateForm();
        }

        updateFieldsVisibility(currentRole);

        roleSelect.addEventListener('change', function() {
            updateFieldsVisibility(this.value);
            validateForm();
        });

        function updateCourses(collegeId) {
            if (!collegeId) {
                courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';
                validateForm();
                return;
            }

            fetch(`/colleges/${collegeId}/courses`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(courses => {
                    courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';
                    courses.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.id;
                        option.textContent = course.name;
                        courseSelect.appendChild(option);
                    });
                    
                    const oldCourseId = '{{ old('course_id', $user->course_id) }}';
                    if (oldCourseId) {
                        courseSelect.value = oldCourseId;
                    }
                    validateForm();
                })
                .catch(error => {
                    console.error('Error fetching courses:', error);
                    courseSelect.innerHTML = '<option value="" disabled selected>Error loading courses</option>';
                    validateForm();
                });
        }

        collegeSelect.addEventListener('change', function() {
            updateCourses(this.value);
            validateForm();
        });

        courseSelect.addEventListener('change', function() {
            validateForm();
        });

        yearLevelSelect.addEventListener('change', function() {
            validateForm();
        });

        sexSelect.addEventListener('change', function() {
            validateForm();
        });

        @if($user->college_id)
            updateCourses({{ $user->college_id }});
        @endif

        function validateForm() {
            let isValid = true;

            phoneError.textContent = '';
            yearLevelError.textContent = '';
            collegeError.textContent = '';
            courseError.textContent = '';

            phoneInput.classList.remove('input-error');
            yearLevelSelect.classList.remove('input-error');
            collegeSelect.classList.remove('input-error');
            courseSelect.classList.remove('input-error');

            const phoneValue = phoneInput.value.trim();
            if (phoneValue.length === 0) {
                phoneError.textContent = 'Phone number is required';
                phoneInput.classList.add('input-error');
                isValid = false;
            } else if (phoneValue.length !== 10 || !phoneValue.match(/^9\d{9}$/)) {
                phoneError.textContent = 'Please enter a valid 10-digit phone number starting with 9';
                phoneInput.classList.add('input-error');
                isValid = false;
            }

            if (!sexSelect.value) {
                isValid = false;
            }

            const roleValue = roleSelect.value;
            if (roleValue === 'student') {
                if (!yearLevelSelect.value) {
                    yearLevelError.textContent = 'Year level is required for students';
                    yearLevelSelect.classList.add('input-error');
                    isValid = false;
                }
                
                if (!collegeSelect.value) {
                    collegeError.textContent = 'College is required for students';
                    collegeSelect.classList.add('input-error');
                    isValid = false;
                }
                
                if (!courseSelect.value) {
                    courseError.textContent = 'Course is required for students';
                    courseSelect.classList.add('input-error');
                    isValid = false;
                }
            }
            
            updateBtn.disabled = !isValid;
            
            return isValid;
        }

        // Initial validation
        validateForm();
        
        // Add event listeners for validation
        phoneInput.addEventListener('input', validateForm);
        sexSelect.addEventListener('change', validateForm);
        roleSelect.addEventListener('change', validateForm);
        yearLevelSelect.addEventListener('change', validateForm);
        collegeSelect.addEventListener('change', validateForm);
        courseSelect.addEventListener('change', validateForm);
    });
    </script>
@endpush