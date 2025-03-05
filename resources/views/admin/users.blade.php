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
                                <input type="text" id="user-search" placeholder="Search here..." name="name"
                                    tabindex="2" required>
                            </fieldset>
                            <div class="button-submit">
                                <button type="submit"><i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="filter-dropdowns flex items-center gap10">
                        <!-- Year Level Dropdown -->
                        <select name="year_level" id="year_level" class="form-select">
                            <option value="">Year Level</option>
                            <option value="1st Year" {{ request('year_level') == '1st Year' ? 'selected' : '' }}>1st Year
                            </option>
                            <option value="2nd Year" {{ request('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year
                            </option>
                            <option value="3rd Year" {{ request('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year
                            </option>
                            <option value="4th Year" {{ request('year_level') == '4th Year' ? 'selected' : '' }}>4th Year
                            </option>
                        </select>

                        <!-- Colleges Dropdown -->
                        <select name="department" id="department" class="form-select">
                            <option value="">Select College</option>
                            <option value="CEIT" {{ request('department') == 'CEIT' ? 'selected' : '' }}>CEIT</option>
                            <option value="GSOLC" {{ request('department') == 'GSOLC' ? 'selected' : '' }}>GSOLC</option>
                            <option value="CAFENR" {{ request('department') == 'CAFENR' ? 'selected' : '' }}>CAFENR</option>
                            <option value="CAS" {{ request('department') == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="CCJ" {{ request('department') == 'CCJ' ? 'selected' : '' }}>CCJ</option>
                            <option value="CEMDS" {{ request('department') == 'CEMDS' ? 'selected' : '' }}>CEMDS</option>
                            <option value="CED" {{ request('department') == 'CED' ? 'selected' : '' }}>CED</option>
                            <option value="CON" {{ request('department') == 'CON' ? 'selected' : '' }}>CON</option>
                            <option value="CVMBS" {{ request('department') == 'CVMBS' ? 'selected' : '' }}>CVMBS</option>
                        </select>
                    </div>

                    <div>
                        <a class="tf-button style-1 w208" href="{{ route('admin.users.add') }}"><i
                                class="icon-plus"></i>Add new</a>
                    </div>
                </div>

                @if (Session::has('status'))
                    <div class="alert alert-success mb-4">{{ Session::get('status') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="users-table">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Phone</th>
                                <th class="text-center">Year Level</th>
                                <th class="text-center">Colleges</th>
                                <th class="text-center">Programs</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="text-center">{{ $user->id }}</td>
                                    <td class="text-center">{{ $user->name }}</td>
                                    <td class="text-center">{{ $user->email }}</td>
                                    <td class="text-center">{{ $user->phone_number ?? 'Not Provided' }}</td>
                                    <td class="text-center">{{ $user->year_level ?? 'Not Provided' }}</td>
                                    <td class="text-center">{{ $user->department ?? 'Not Provided' }}</td>
                                    <td class="text-center">{{ $user->course ?? 'Not Provided' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                            class="btn btn-outline-primary btn-sm" title="Edit User">
                                            <i class="icon-edit-3"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm delete"
                                                title="Delete User">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {

            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this user?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });


            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                searchUsers();
            });

            $('#user-search').on('input', function() {
                if ($(this).val().length > 0) {
                    $(this).closest('form').find('button').prop('disabled', false);
                } else {
                    $(this).closest('form').find('button').prop('disabled', true);
                    location.reload();
                }
            });

            function searchUsers() {
                let query = $('#user-search').val();

                if (query.length > 0) {
                    $.ajax({
                        url: "{{ route('admin.users.search') }}",
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(data) {
                            updateTableWithUsers(data);
                        },
                        error: function() {
                            Swal.fire('Error', 'An error occurred while searching users.', 'error');
                        }
                    });
                }
            }


            $('#year_level, #department').on('change', function() {
                filterUsers();
            });

            function filterUsers() {
                let yearLevel = $('#year_level').val();
                let department = $('#department').val();

                $.ajax({
                    url: "{{ route('admin.users.filter') }}",
                    type: "GET",
                    data: {
                        year_level: yearLevel,
                        department: department
                    },
                    success: function(data) {
                        updateTableWithUsers(data);
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while filtering users.', 'error');
                    }
                });
            }

            function updateTableWithUsers(users) {
                let tableBody = $('#users-table tbody');
                tableBody.empty();

                if (users.length > 0) {
                    $.each(users, function(index, user) {
                        let row = `
                            <tr>
                                <td class="text-center">${user.id}</td>
                                <td class="text-center">${user.name}</td>
                                <td class="text-center">${user.email}</td>
                                <td class="text-center">${user.phone_number || 'Not Provided'}</td>
                                <td class="text-center">${user.year_level || 'Not Provided'}</td>
                                <td class="text-center">${user.department || 'Not Provided'}</td>
                                <td class="text-center">${user.course || 'Not Provided'}</td>
                                <td class="text-center">
                                    <a href="/admin/users/edit/${user.id}" class="btn btn-outline-primary btn-sm" title="Edit User">
                                        <i class="icon-edit-3"></i>
                                    </a>
                                    <form action="/admin/users/destroy/${user.id}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm delete" title="Delete User">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });

                    $('.delete').on('click', function(e) {
                        e.preventDefault();
                        var form = $(this).closest('form');
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You want to delete this user?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                } else {
                    tableBody.append(`
                        <tr>
                            <td colspan="8" class="text-center">No users found matching your criteria.</td>
                        </tr>
                    `);
                }
            }
        });
    </script>
@endpush
