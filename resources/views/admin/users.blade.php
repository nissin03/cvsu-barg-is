@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Users</h3>
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
                    <div class="text-tiny">Users</div>
                </li>
            </ul>
        </div>
        
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap mb-4">
                <div class="wg-filter flex-grow">
                    <form class="form-search" id="search-form">
                        <fieldset class="name">
                            <input type="text" id="user-search" placeholder="Search by name..." name="name"
                                value="{{ request('name') }}" tabindex="2">
                        </fieldset>
                        <div class="button-submit">
                            <button type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>

                <div class="filter-dropdowns flex items-center gap10 flex-wrap">
                    <select name="year_level" id="year_level" class="w-auto">
                        <option value="">All Year Levels</option>
                        <option value="1st Year" {{ request('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                        <option value="2nd Year" {{ request('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                        <option value="3rd Year" {{ request('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                        <option value="4th Year" {{ request('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                        <option value="5th Year" {{ request('year_level') == '5th Year' ? 'selected' : '' }}>5th Year</option>
                    </select>

                    <select name="college_id" id="college_id" class="w-auto">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->code }} - {{ $college->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- <select name="course_id" id="course_id" class="w-auto">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select> --}}

                    <button type="button" id="clear-filters" class="tf-button style-1 w-auto">
                        <i class="icon-refresh"></i>Clear
                    </button>
                </div>

                <div>
                    <a class="tf-button w-auto" href="{{ route('admin.users.add') }}">
                        <i class="icon-plus"></i>Add new
                    </a>
                </div>

                <div>
                    <button type="button" class="tf-button w-auto" data-bs-toggle="modal" data-bs-target="#addCollegesModal">
                        <i class="icon-plus"></i>Add colleges
                    </button>
                </div>

                <div>
                    <button type="button" class="tf-button w-auto" data-bs-toggle="modal" data-bs-target="#addCoursesModal">
                        <i class="icon-plus"></i>Add Courses
                    </button>
                </div>
            </div>

            @if (Session::has('status'))
                <div class="alert alert-success mb-4">{{ Session::get('status') }}</div>
            @endif

            <div id="loading-indicator" class="text-center py-4" style="display: none;">
                <i class="icon-loading"></i> Loading...
            </div>

            <div class="table-responsive">
                <div class="overflow-auto">
                    <table class="table table-striped table-bordered" id="users-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="min-width: 150px;">Name</th>
                                <th class="text-center" style="min-width: 200px;">Email</th>
                                <th class="text-center" style="min-width: 120px;">Phone</th>
                                <th class="text-center" style="min-width: 100px;">Year Level</th>
                                <th class="text-center" style="min-width: 100px;">College Code</th>
                                <th class="text-center" style="min-width: 120px;">Course Code</th>
                                <th class="text-center" style="min-width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="text-center">
                                        <div class="text-truncate" style="max-width: 150px;" title="{{ $user->name }}">
                                            {{ $user->name }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $user->email }}">
                                            {{ $user->email }}
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $user->phone_number ?? 'Not Provided' }}</td>
                                    <td class="text-center">{{ $user->year_level ?? 'Not Provided' }}</td>
                                    <td class="text-center">{{ $user->college->code ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <div class="text-truncate" style="max-width: 120px;" title="{{ $user->course->code ?? 'N/A' }}">
                                            {{ $user->course->code ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="list-icon-function justify-center">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="" title="Edit User">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination" id="pagination-wrapper">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@include('components.admin_college')
@include('components.admin_course') 

<style>
    @media (max-width: 768px) {
        .filter-dropdowns {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }

        .filter-dropdowns select,
        .filter-dropdowns button {
            width: 100%;
        }

        .flex.items-center.justify-between.gap10.flex-wrap.mb-4 {
            flex-direction: column;
            align-items: stretch;
        }

        .wg-filter {
            margin-bottom: 15px;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }

        .text-truncate {
            max-width: 100px !important;
        }
    }

    @media (max-width: 576px) {
        .text-truncate {
            max-width: 80px !important;
        }

        .table {
            font-size: 0.75rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        min-width: 800px;
        margin-bottom: 0;
    }

    .table-loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endsection

@push('scripts')
<script>
    $(function() {
        let searchTimeout;
        let isLoading = false;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Update course dropdown based on selected college
        $('#college_id').on('change', function() {
            const collegeId = $(this).val();
            const courseSelect = $('#course_id');
            
            courseSelect.html('<option value="">All Courses</option>');
            
            if (collegeId) {
                $.get(`/admin/courses-by-college/${collegeId}`, function(courses) {
                    courses.forEach(function(course) {
                        courseSelect.append(
                            $('<option></option>').val(course.id).text(course.code + ' - ' + course.name)
                        );
                    });
                });
            }
            
            performFilter(true);
        });

        updateButtonStates();

        $('#user-search').on('input', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val().trim();
            updateButtonStates();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performFilter(true);
                }, 500);
            } else if (query.length === 0) {
                performFilter(true);
            }
        });

        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            performFilter(true);
        });

        $('#year_level, #course_id').on('change', function() {
            performFilter(true);
        });

        $('#clear-filters').on('click', function() {
            clearAllFilters();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url && !isLoading) {
                performFilter(false, url);
            }
        });

        function updateButtonStates() {
            const query = $('#user-search').val().trim();
            const $submitBtn = $('#search-form button[type="submit"]');
            if (query.length > 0) {
                $submitBtn.prop('disabled', false);
            } else {
                $submitBtn.prop('disabled', true);
            }
        }

        function performFilter(resetPage = false, customUrl = null) {
            if (isLoading) return;
            
            const query = $('#user-search').val().trim();
            const yearLevel = $('#year_level').val();
            const collegeId = $('#college_id').val();
            const courseId = $('#course_id').val();
            
            const data = {};
            if (query) data.name = query;
            if (yearLevel) data.year_level = yearLevel;
            if (collegeId) data.college_id = collegeId;
            if (courseId) data.course_id = courseId;
            
            let url = customUrl || "{{ route('admin.users') }}";
            if (resetPage && !customUrl) {
                data.page = 1;
            }
            
            showLoadingState(true);
            
            $.ajax({
                url: url,
                type: "GET",
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    updateTable(response.users, response.links);
                    updateURL(data);
                },
                error: function(xhr) {
                    console.error('Filter error:', xhr);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'An error occurred while filtering users.', 'error');
                    } else {
                        alert('An error occurred while filtering users.');
                    }
                },
                complete: function() {
                    showLoadingState(false);
                }
            });
        }

        function updateTable(users, paginationLinks) {
            const $tbody = $('#users-tbody');
            $tbody.empty();
            
            if (users && users.length > 0) {
                $.each(users, function(index, user) {
                    const editUrl = `/admin/users/${user.id}/edit`;
                    const row = `
                        <tr>
                            <td class="text-center">
                                <div class="text-truncate" style="max-width: 150px;" title="${user.name || ''}">
                                    ${user.name || 'N/A'}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="text-truncate" style="max-width: 200px;" title="${user.email || ''}">
                                    ${user.email || 'N/A'}
                                </div>
                            </td>
                            <td class="text-center">${user.phone_number || 'Not Provided'}</td>
                            <td class="text-center">${user.year_level || 'Not Provided'}</td>
                            <td class="text-center">${user.college ? user.college.code : 'N/A'}</td>
                            <td class="text-center">
                                <div class="text-truncate" style="max-width: 120px;" title="${user.course ? user.course.code : 'N/A'}">
                                    ${user.course ? user.course.code : 'N/A'}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="list-icon-function justify-center">
                                    <a href="${editUrl}" class="btn btn-outline-primary btn-sm" title="Edit User">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                    $tbody.append(row);
                });
            } else {
                $tbody.append(`
                    <tr>
                        <td colspan="8" class="text-center py-4">No users found matching your criteria.</td>
                    </tr>
                `);
            }
            
            if (paginationLinks) {
                $('#pagination-wrapper').html(paginationLinks);
            }
        }

        function clearAllFilters() {
            $('#user-search').val('');
            $('#year_level').val('');
            $('#college_id').val('');
            $('#course_id').val('');
            updateButtonStates();
            performFilter(true);
        }

        function showLoadingState(loading) {
            isLoading = loading;
            const $table = $('#users-table');
            const $loading = $('#loading-indicator');
            
            if (loading) {
                $table.addClass('table-loading');
                $loading.show();
            } else {
                $table.removeClass('table-loading');
                $loading.hide();
            }
        }

        function updateURL(params) {
            const url = new URL(window.location);
            url.searchParams.delete('name');
            url.searchParams.delete('year_level');
            url.searchParams.delete('college_id');
            url.searchParams.delete('course_id');
            url.searchParams.delete('page');
            
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                }
            });
            
            window.history.replaceState({}, '', url);
        }
    });
</script>
@endpush