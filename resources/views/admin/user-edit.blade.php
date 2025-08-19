@extends('layouts.admin')
@section('content')
    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap-20 mb-27">
                <h3 class="font-bold">Edit User</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap-10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <a href="{{ route('admin.users') }}">
                            <div>Users</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div>Edit User</div>
                    </li>
                </ul>
            </div>

            <!-- form-edit-user -->
            <form class="form-edit-user bg-white" method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $user->id }}" />

                <div class="wg-box">
                    <fieldset class="mb-4">
                        <div class="body-title">Name</div>
                        <input class="mb-10" type="text" value="{{ $user->name }}" disabled>
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Email</div>
                        <input class="mb-10" type="email" name="email" value="{{ $user->email }}" disabled>
                        @error('email')
                            <span class="text-red-600">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <fieldset class="phone_number">
                        <div class="body-title mb-10">Phone Number</div>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="phoneNumber" name="phone_number"
                                placeholder="Phone Number" pattern="^9\d{9}$" maxlength="10"
                                value="{{ old('phone_number', $user->phone_number) }}">
                        </div>
                    </fieldset>
                    @error('phone_number')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="mb-4">
                        <div class="body-title">Year Level</div>
                        <select class="" name="year_level">
                            <option value="" disabled>Select Year Level</option>
                            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                <option value="{{ $year }}"
                                    {{ old('year_level', $user->year_level) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        @error('year_level')
                            <span class="text-red-600">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Department</div>
                        <select class="" name="department" id="studentDepartment">
                            <option value="" disabled>Select Department</option>
                            @foreach (['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'] as $dept)
                                <option value="{{ $dept }}"
                                    {{ old('department', $user->department) == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <span class="text-red-600">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <fieldset class="mb-4">
                        <div class="body-title">Course</div>
                        <select class="" name="course" id="course">
                            <option value="" disabled>Select Course</option>
                        </select>
                        @error('course')
                            <span class="text-red-600">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <div class="cols gap-10">
                        <button type="submit" class="btn btn-dark btn-lg btn-xl">Update User</button>
                    </div>
                </div>
            </form>
            <!-- /form-edit-user -->
        </div>
        <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentDepartmentSelect = document.getElementById('studentDepartment');
            const courseSelect = document.getElementById('course');

            // Courses based on department
            const courses = {
                CEIT: ['BS Agricultural and Biosystems Engineering', 'BS Architecture', 'BS Civil Engineering',
                    'BS Computer Engineering', 'BS Computer Science', 'BS Electrical Engineering',
                    'BS Electronics Engineering', 'BS Industrial Technology Major in Automotive Technology',
                    'BS Industrial Technology Major in Electrical Technology',
                    'BS Industrial Technology Major in Electronics Technology', 'BS Information Technology'
                ],
                GSOLC: ['PhD in Agriculture', 'PhD in Education', 'PhD in Management',
                    'Master in Business Administration', 'Master in Agriculture',
                    'Master of Arts in Education', 'Master in Engineering', 'Master of Management',
                    'Master of Professional Studies', 'MS Agriculture', 'MS Biology', 'MS Food Science',
                    'Master in Information Technology'
                ],
                CAFENR: ['Bachelor of Agricultural Entrepreneurship', 'BS Agriculture',
                    'BS Environmental Science', 'BS Food Technology'
                ],
                CAS: ['BA English Language Studies', 'BA Journalism', 'BA Political Science',
                    'BA Applied Mathematics', 'BS Biology', 'BS Psychology', 'BS Social Work'
                ],
                CCJ: ['BS Criminology', 'BS Industrial Security Management'],
                CEMDS: ['BS Accountancy', 'BS Business Management', 'BS Economics', 'BS International Studies',
                    'BS Office Administration'
                ],
                CED: ['Bachelor of Early Childhood Education', 'Bachelor of Elementary Education',
                    'Bachelor of Secondary Education', 'Bachelor of Special Needs Education',
                    'Bachelor of Technology and Livelihood Education', 'BS Hospitality Management',
                    'BS Tourism Management', 'Teacher Certificate Program', 'Science High School',
                    'Elementary Education', 'Pre-Elementary Education'
                ],
                CON: ['BS Medical Technology', 'BS Midwifery', 'BS Nursing', 'Diploma in Midwifery'],
                CVMBS: ['Doctor of Veterinary Medicine']
            };

            // Update courses dropdown
            function updateCourseOptions() {
                const department = studentDepartmentSelect.value;
                courseSelect.innerHTML = '<option value="" disabled selected>Select Course</option>';

                if (courses[department]) {
                    courses[department].forEach(function(course) {
                        const option = document.createElement('option');
                        option.value = course;
                        option.textContent = course;
                        courseSelect.appendChild(option);
                    });

                    const oldValue = '{{ old('course', $user->course) }}';
                    if (oldValue) {
                        courseSelect.value = oldValue;
                    }
                }
            }
            studentDepartmentSelect.addEventListener('change', updateCourseOptions);

            updateCourseOptions();
        });
    </script>
@endpush
