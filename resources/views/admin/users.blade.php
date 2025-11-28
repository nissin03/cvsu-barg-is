@extends('layouts.admin')

@section('content')
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="loading-indicator">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Users</h3>
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
                <div class="d-flex flex-column gap-3 mb-4">
                    {{-- First row: Search + Add buttons --}}
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div class="wg-filter flex-grow">
                            <form class="form-search" onsubmit="return false;">
                                <fieldset class="name">
                                    <input type="text" id="user-search" class="filter-input"
                                        placeholder="Search by name, email, college, or course..." name="search"
                                        aria-required="true" value="{{ request('search') }}">
                                </fieldset>
                                <div class="button-submit">
                                    <button type="button" id="searchButton" style="display:none;">
                                        <i class="icon-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="action-buttons">
                            <a class="tf-button w-auto" href="{{ route('admin.users.add') }}">
                                <i class="icon-plus"></i>Add User
                            </a>
                        </div>
                    </div>

                    {{-- Filters toggle row --}}
                    <div class="d-flex justify-content-end">
                        <div class="filter-toggle-section d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge bg-primary fs-6 py-2 px-3" id="activeFiltersCount" style="display: none;">
                                0 filters
                            </span>

                            <button class="btn btn-outline-primary btn-lg position-relative" id="filterToggle"
                                type="button">
                                <i class="icon-filter me-1"></i>
                                Filters
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    id="filterBadge" style="display: none;">0</span>
                            </button>

                            <button class="btn btn-outline-secondary" id="clearAllFilters" style="display: none;">
                                <i class="icon-x-circle me-1"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filters panel --}}
                <div class="collapse mb-4" id="filterContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Year Level</label>
                                        <select name="year_level" id="year_level" class="filter-select form-select">
                                            <option value="">All Year Levels</option>
                                            <option value="1st Year"
                                                {{ request('year_level') == '1st Year' ? 'selected' : '' }}>1st Year
                                            </option>
                                            <option value="2nd Year"
                                                {{ request('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year
                                            </option>
                                            <option value="3rd Year"
                                                {{ request('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year
                                            </option>
                                            <option value="4th Year"
                                                {{ request('year_level') == '4th Year' ? 'selected' : '' }}>4th Year
                                            </option>
                                            <option value="5th Year"
                                                {{ request('year_level') == '5th Year' ? 'selected' : '' }}>5th Year
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">College</label>
                                        <select name="college_id" id="college_id" class="filter-select form-select">
                                            <option value="">All Colleges</option>
                                            @if (isset($colleges))
                                                @foreach ($colleges as $college)
                                                    <option value="{{ $college->id }}"
                                                        {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                                        {{ $college->code }} - {{ $college->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Course</label>
                                        <select name="course_id" id="course_id" class="filter-select form-select">
                                            <option value="">All Courses</option>
                                            @if (isset($courses))
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}"
                                                        {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                        {{ $course->code }} - {{ $course->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Email Filter</label>
                                        <select name="email_filter" id="email_filter" class="filter-select form-select">
                                            <option value="">All Emails</option>
                                            <option value="gmail.com"
                                                {{ request('email_filter') == 'gmail.com' ? 'selected' : '' }}>
                                                Gmail (@gmail.com)
                                            </option>
                                            <option value="cvsu.edu.ph"
                                                {{ request('email_filter') == 'cvsu.edu.ph' ? 'selected' : '' }}>
                                                CVSU (@cvsu.edu.ph)
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3 mb-5">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="filter-select form-select">
                                            <option value="newest"
                                                {{ request('sort_by', 'newest') == 'newest' ? 'selected' : '' }}>
                                                Newest First
                                            </option>
                                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>
                                                Oldest First
                                            </option>
                                            <option value="name_asc"
                                                {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                                                Name: A to Z
                                            </option>
                                            <option value="name_desc"
                                                {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                                                Name: Z to A
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-4 flex-wrap gap-2">
                                <button class="btn btn-primary btn-lg me-2" id="applyFilters">
                                    <i class="icon-filter me-1"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-lg" id="resetFilters">
                                    <i class="icon-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active filters row --}}
                <div class="active-filters-row mb-4" id="activeFiltersRow" style="display: none;">
                    <div class="card border-0 bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="text-muted fs-6 fw-medium">Active filters:</span>
                                <div class="filter-tags d-flex gap-2 flex-wrap" id="filterTags"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (Session::has('status'))
                    <div class="alert alert-success mb-4">{{ Session::get('status') }}</div>
                @endif

                <div class="wg-table table-all-user g-table">
                    <div class="table-responsive">
                        {{-- Mobile Card View (built from table via JS) --}}
                        <div class="mobile-cards d-block d-md-none" id="mobile-cards-container">
                            {{-- Populated dynamically from table rows --}}
                        </div>

                        {{-- Desktop Table View --}}
                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" scope="col" style="width: 20%">Name</th>
                                    <th class="text-center align-middle" scope="col" style="width: 25%">Email</th>
                                    <th class="text-center align-middle" scope="col" style="width: 15%">Phone</th>
                                    <th class="text-center align-middle" scope="col" style="width: 10%">Year Level
                                    </th>
                                    <th class="text-center align-middle" scope="col" style="width: 10%">College Code
                                    </th>
                                    <th class="text-center align-middle" scope="col" style="width: 10%">Course Code
                                    </th>
                                    {{-- <th class="text-center align-middle" scope="col" style="width: 10%">Actions</th> --}}
                                </tr>
                            </thead>

                            <tbody id="js-users-partial-target">
                                @include('partials._users-table', ['users' => $users])
                            </tbody>
                        </table>
                    </div>

                    <div class="divider"></div>

                    <div class="pagination-container flex items-center justify-between flex-wrap gap10 wgp-pagination"
                        id="js-users-partial-target-pagination">
                        @include('partials._users-pagination', ['users' => $users])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.admin_college')
    @include('components.admin_course')

    <form id="filterForm" method="GET" style="display: none;">
        <input type="hidden" name="search" id="hidden_search">
        <input type="hidden" name="year_level" id="hidden_year_level">
        <input type="hidden" name="college_id" id="hidden_college_id">
        <input type="hidden" name="course_id" id="hidden_course_id">
        <input type="hidden" name="email_filter" id="hidden_email_filter">
        <input type="hidden" name="sort_by" id="hidden_sort_by">
    </form>
@endsection

@push('styles')
    <style>
        .main-content-inner {
            padding: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }

        .gap20 {
            gap: 1rem;
        }

        .mb-27 {
            margin-bottom: 1.5rem;
        }

        /* Enhanced Table Styles */
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .g-table .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            margin-bottom: 0;
            table-layout: fixed;
        }

        .g-table .table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .g-table .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }

        .g-table .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* Enhanced Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }

        /* Search input */
        .filter-input[type="text"],
        #user-search.filter-input {
            font-size: 1.1rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.2s ease;
            min-height: 3rem;
            width: 100%;
        }

        .filter-input[type="text"]:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            outline: none;
        }

        /* Enhanced filter tags */
        .filter-tag-enhanced {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: white;
            padding: 0.7rem 1.3rem;
            border-radius: 2rem;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            min-height: 2.5rem;
        }

        .filter-tag-enhanced:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .filter-tag-enhanced .btn-close {
            background-color: #fff;
            color: #dc3545;
            border-radius: 50%;
            width: 1.6rem;
            height: 1.6rem;
            padding: 0;
            opacity: 0.8;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-tag-enhanced .btn-close:hover {
            background-color: #fff;
            color: #b02a37;
            opacity: 1;
            transform: scale(1.1);
        }

        /* Loading Indicator */
        .loading-indicator {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            text-align: center;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            min-width: 150px;
            background: #fff;
            transition: border-color 0.2s;
        }

        .filter-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .filter-select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* Mobile cards */
        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
        }

        .mobile-card-header {
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .mobile-card-header-main {
            flex: 1;
            min-width: 0;
        }

        .mobile-card-title {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            overflow-wrap: anywhere;
        }

        .mobile-card-subtitle {
            margin: 0;
            font-size: 13px;
            color: #64748b;
            overflow-wrap: anywhere;
        }

        .mobile-card-badges {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-end;
        }

        .mobile-card-body {
            padding: 12px 16px 14px;
        }

        .mobile-card-row {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .mobile-card-label {
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
        }

        .mobile-card-value {
            color: #111827;
            overflow-wrap: anywhere;
        }

        .empty-state {
            text-align: center;
            padding: 30px 15px;
            color: #64748b;
        }

        .empty-state .empty-icon {
            font-size: 40px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .empty-state h4 {
            color: #475569;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .empty-state p {
            margin: 0;
        }

        /* Pagination container */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px 0;
            overflow: visible !important;
            min-height: 60px;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .pagination li {
            display: inline-block;
            margin: 0;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-color: #3498db;
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }

        .pagination .disabled .page-link {
            background-color: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .g-table .table th,
            .g-table .table td {
                padding: 12px 8px;
            }

            .filter-input[type="text"],
            #user-search.filter-input {
                font-size: 1rem !important;
                padding: 0.6rem 0.8rem !important;
            }

            .filter-tag-enhanced {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .active-filters-row .fs-5 {
                font-size: 1rem !important;
            }

            .mobile-cards {
                display: block;
            }

            .pagination {
                gap: 5px;
            }

            .pagination .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 8px;
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            .action-buttons {
                width: 100%;
                flex-direction: column;
                align-items: flex-end;
            }

            .tf-button {
                width: 100%;
                justify-content: center;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let lastScrollPosition = 0;
            let searchTimeout = null;
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

            $('#filterToggle').on('click', function() {
                const container = $('#filterContainer');
                const icon = $(this).find('i');

                if (container.hasClass('show')) {
                    container.removeClass('show').slideUp(300, function() {
                        $(this).removeClass('show');
                    });
                    icon.removeClass('icon-x-circle').addClass('icon-filter');
                } else {
                    container.addClass('show').hide().slideDown(300);
                    icon.removeClass('icon-filter').addClass('icon-x-circle');
                }
            });

            function initRowClicks() {
                $('.user-row').off('click').on('click', function() {
                    window.location = $(this).data('href');
                });
            }

            function buildMobileCardsFromTable() {
                const $container = $('#mobile-cards-container');
                $container.empty();

                const $rows = $('#js-users-partial-target').find('tr');

                if (!$rows.length) {
                    const $empty = $(`
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="icon-user"></i>
                            </div>
                            <h4>No Users Found</h4>
                            <p>Try adjusting your filters or search criteria.</p>
                        </div>
                    `);
                    $container.append($empty);
                    return;
                }

                $rows.each(function() {
                    const $row = $(this);
                    const $cells = $row.find('td');
                    if (!$cells.length) return;

                    const name = $cells.eq(0).text().trim();
                    const email = $cells.eq(1).text().trim();
                    const phone = $cells.eq(2).text().trim();
                    const yearLevel = $cells.eq(3).text().trim();
                    const collegeCode = $cells.eq(4).text().trim();
                    const courseCode = $cells.eq(5).text().trim();
                    const href = $row.data('href');

                    const cardHtml = `
                        <div class="mobile-card user-mobile-card" ${href ? 'data-href="' + href + '"' : ''}>
                            <div class="mobile-card-header">
                                <div class="mobile-card-header-main">
                                    <h5 class="mobile-card-title" title="${name}">${name}</h5>
                                    <p class="mobile-card-subtitle" title="${email}">${email}</p>
                                </div>
                                <div class="mobile-card-badges">
                                    ${collegeCode ? `<span class="badge badge-primary mb-1">${collegeCode}</span>` : ''}
                                    ${courseCode ? `<span class="badge badge-secondary">${courseCode}</span>` : ''}
                                </div>
                            </div>
                            <div class="mobile-card-body">
                                <div class="mobile-card-row">
                                    <span class="mobile-card-label">Phone:</span>
                                    <span class="mobile-card-value">${phone || '—'}</span>
                                </div>
                                <div class="mobile-card-row">
                                    <span class="mobile-card-label">Year:</span>
                                    <span class="mobile-card-value">${yearLevel || '—'}</span>
                                </div>
                            </div>
                        </div>
                    `;

                    const $card = $(cardHtml);
                    if (href) {
                        $card.css('cursor', 'pointer').on('click', function() {
                            window.location = href;
                        });
                    }

                    $container.append($card);
                });
            }

            $('#user-search').on('input', function() {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            $('#searchButton').on('click', function() {
                performFilter();
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
                                $('<option></option>').val(course.id).text(course.code +
                                    ' - ' + course.name)
                            );
                        });
                    });
                }

                performFilter();
            });

            // Enhanced filter performance function
            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                // Get filter values
                const search = $('#user-search').val();
                const yearLevel = $('#year_level').val();
                const collegeId = $('#college_id').val();
                const courseId = $('#course_id').val();
                const emailFilter = $('#email_filter').val();
                const sortBy = $('#sort_by').val();

                // Show loading state
                showLoadingState(true);

                let url = '{{ route('admin.users') }}';
                let params = [];

                if (search) {
                    params.push('search=' + encodeURIComponent(search));
                }
                if (yearLevel) {
                    params.push('year_level=' + encodeURIComponent(yearLevel));
                }
                if (collegeId) {
                    params.push('college_id=' + encodeURIComponent(collegeId));
                }
                if (courseId) {
                    params.push('course_id=' + encodeURIComponent(courseId));
                }
                if (emailFilter) {
                    params.push('email_filter=' + encodeURIComponent(emailFilter));
                }
                if (sortBy && sortBy !== 'newest') {
                    params.push('sort_by=' + encodeURIComponent(sortBy));
                }

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#js-users-partial-target').html(response.users);
                        $('#js-users-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initPaginationEvents();
                        initRowClicks();
                        updateActiveFiltersDisplay();
                        buildMobileCardsFromTable();
                        $(window).scrollTop(lastScrollPosition);

                        // Show success feedback
                        showNotification(`Found ${response.count} user(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification(
                            'An error occurred while filtering users. Please try again.',
                            'error');
                    }
                });
            }

            // Show/hide loading state
            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select, .filter-input, #user-search').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, .filter-input, #user-search').prop('disabled', false);
                    $('#applyFilters').prop('disabled', false).html(
                        '<i class="icon-filter me-1"></i> Apply Filters');
                }
            }

            function initPaginationEvents() {
                $('.pagination a').off('click').on('click', function(e) {
                    e.preventDefault();
                    lastScrollPosition = $(window).scrollTop();

                    const url = $(this).attr('href');
                    showLoadingState(true);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            $('#js-users-partial-target').html(response.users);
                            $('#js-users-partial-target-pagination').html(response.pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initRowClicks();
                            buildMobileCardsFromTable();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            showLoadingState(false);
                            showNotification(
                                'An error occurred while loading page. Please try again.',
                                'error');
                        }
                    });
                });
            }

            function updateActiveFiltersDisplay() {
                let count = 0;
                const filterTags = $('#filterTags');
                const activeFiltersRow = $('#activeFiltersRow');
                const activeFiltersCount = $('#activeFiltersCount');
                const clearAllButton = $('#clearAllFilters');
                const filterBadge = $('#filterBadge');

                filterTags.empty();
                const urlParams = new URLSearchParams(window.location.search);

                if (urlParams.get('search')) {
                    count++;
                    addFilterTag(`Search: "${urlParams.get('search')}"`, 'search');
                }
                if (urlParams.get('year_level')) {
                    count++;
                    const yearLevelText = $('#year_level option:selected').text();
                    addFilterTag(`Year Level: ${yearLevelText}`, 'year_level');
                }
                if (urlParams.get('college_id')) {
                    count++;
                    const collegeText = $('#college_id option:selected').text();
                    addFilterTag(`College: ${collegeText}`, 'college_id');
                }
                if (urlParams.get('course_id')) {
                    count++;
                    const courseText = $('#course_id option:selected').text();
                    addFilterTag(`Course: ${courseText}`, 'course_id');
                }
                if (urlParams.get('email_filter')) {
                    count++;
                    const emailText = $('#email_filter option:selected').text();
                    addFilterTag(`Email: ${emailText}`, 'email_filter');
                }
                if (urlParams.get('sort_by') && urlParams.get('sort_by') !== 'newest') {
                    count++;
                    const sortText = $('#sort_by option:selected').text();
                    addFilterTag(`Sort: ${sortText}`, 'sort_by');
                }

                if (count > 0) {
                    activeFiltersCount.show().text(`${count} filter${count > 1 ? 's' : ''}`);
                    clearAllButton.show();
                    activeFiltersRow.show();
                    filterBadge.show().text(count);
                } else {
                    activeFiltersCount.hide();
                    clearAllButton.hide();
                    activeFiltersRow.hide();
                    filterBadge.hide();
                }
            }

            function addFilterTag(text, filterName) {
                const tag = $(`
                    <span class="filter-tag-enhanced">
                        ${text}
                        <button type="button" class="btn-close icon-x text-danger" data-filter="${filterName}" title="Remove filter"></button>
                    </span>
                `);

                tag.find('.btn-close').on('click', function() {
                    const filterToRemove = $(this).data('filter');

                    if (filterToRemove === 'search') {
                        $('#user-search').val('');
                    } else {
                        $(`#${filterToRemove}`).val('');
                    }

                    performFilter();
                });

                $('#filterTags').append(tag);
            }

            $('#applyFilters').on('click', function() {
                performFilter();
            });

            $('#year_level, #college_id, #course_id, #email_filter, #sort_by').on('change', function() {
                performFilter();
            });

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#user-search').val('');
                $('#year_level').val('');
                $('#college_id').val('');
                $('#course_id').val('');
                $('#email_filter').val('');
                $('#sort_by').val('newest');

                performFilter();
            });

            function showNotification(message, type = 'info', duration = 4000) {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';

                const notification = $(`
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                         style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, duration);
            }

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
                    e.preventDefault();
                    performFilter();
                }
                if (e.keyCode === 27) {
                    $('#clearAllFilters').click();
                }
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
                    e.preventDefault();
                    $('#filterToggle').click();
                    setTimeout(() => $('#user-search').focus(), 100);
                }
            });

            // Initialize
            initPaginationEvents();
            updateActiveFiltersDisplay();
            initRowClicks();
            buildMobileCardsFromTable();

            // Welcome tip
            if (window.location.search === '') {
                setTimeout(function() {
                    showNotification(
                        '💡 Tip: Use Ctrl+F to open filters, Ctrl+Enter to apply, or Esc to clear all',
                        'info', 6000);
                }, 1000);
            }
        });
    </script>
@endpush
