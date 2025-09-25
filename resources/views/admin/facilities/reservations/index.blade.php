@extends('layouts.admin')


@section('content')
    <style>
        /* Enhanced Table Styles */
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .bg-individual-green {
            background-color: oklch(49.6% 0.265 301.924);
        }

        .bg-whole-orange {
            background-color: oklch(66.6% 0.179 58.318);
        }

        .bg-whole-blue {
            background-color: oklch(58.8% 0.158 241.966);
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            margin-bottom: 0;
        }

        .table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
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

        /* Name Column Enhancement */
        .name-cell {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .name-text {
            font-weight: 500;
            color: #2c3e50;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table td {
                min-width: 120px;
            }

            .table th,
            .table td {
                padding: 12px 8px;
            }

            .status-badge {
                padding: 4px 8px;
            }
        }

        /* Loading State */
        .table-loading {
            position: relative;
            min-height: 200px;
        }

        .table-loading::after {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1rem;
            color: #666;
        }

        /* Empty State */
        .table-empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-button {
            padding: 6px;
            border-radius: 4px;
            background: none;
            border: 1px solid #dee2e6;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-button:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        .filter-input[type="date"] {
            font-size: 1.1rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.2s ease;
            min-height: 3rem;
        }

        .filter-input[type="date"]:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-input[type="date"] {
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

        .date-filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-separator {
            color: #6c757d;
            font-size: 0.875rem;
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
    </style>

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
                <h3>Facility Reservation</h3>
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
                        <div class="text-tiny">Facility Reservations</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="facility-search" placeholder="Search reservations..."
                                    name="search" aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" id="searchButton" style="display:none;">
                                    <i class="icon-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>


                    <div class="filter-toggle-section d-flex align-items-center gap-3">
                        <span class="badge bg-primary fs-6 py-2 px-3" id="activeFiltersCount" style="display: none;">0
                            filters</span>
                        <button class="btn btn-outline-primary btn-lg position-relative" id="filterToggle" type="button">
                            <i class="icon-filter me-1"></i>
                            Filters
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="filterBadge" style="display: none;">0</span>
                        </button>
                        <button class="btn btn-outline-secondary" id="clearAllFilters" style="display: none;">
                            <i class="icon-x-circle me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <div class="collapse mb-4" id="filterContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Status</label>
                                        <select name="status" id="status" class="filter-select form-select">
                                            <option value="">All Status</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="reserved"
                                                {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                            <option value="completed"
                                                {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="canceled"
                                                {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Date From</label>
                                        <input type="date" name="date_from" id="date_from"
                                            class="filter-input form-control form-control-lg"
                                            value="{{ request('date_from') }}">
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Date To</label>
                                        <input type="date" name="date_to" id="date_to"
                                            class="filter-input form-control form-control-lg"
                                            value="{{ request('date_to') }}">
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Facility</label>
                                        <select name="facility" id="facility" class="filter-select form-select">
                                            <option value="">All Facilities</option>
                                            @if (isset($facilities))
                                                @foreach ($facilities as $facility)
                                                    <option value="{{ $facility->id }}"
                                                        {{ request('facility') == $facility->id ? 'selected' : '' }}>
                                                        {{ $facility->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3 mb-5">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="filter-select form-select">
                                            <option value="newest"
                                                {{ request('sort_by', 'newest') == 'newest' ? 'selected' : '' }}>
                                                Newest First</option>
                                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>
                                                Oldest First</option>
                                            <option value="price_high"
                                                {{ request('sort_by') == 'price_high' ? 'selected' : '' }}>
                                                Price: High to Low</option>
                                            <option value="price_low"
                                                {{ request('sort_by') == 'price_low' ? 'selected' : '' }}>Price:
                                                Low to High</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-4">
                                <button class="btn btn-primary btn-lg me-4" id="applyFilters">
                                    <i class="icon-filter me-1"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-lg" id="resetFilters">
                                    <i class="icon-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

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

                <div class="wg-table table-all-user table-responsive">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="table-layout: auto;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" scope="col" style="width: 25%">Facility</th>
                                    <th class="text-center align-middle" scope="col" style="width: 30%">Dates (From -
                                        To)</th>
                                    <th class="text-center align-middle" scope="col" style="width: 15%">User</th>
                                    <th class="text-center align-middle" scope="col" style="width: 15%">Price</th>
                                </tr>
                            </thead>

                            <tbody id="js-reservations-partial-target">
                                @include('partials._reservations-table', ['reservations' => $reservations])
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                    id="js-reservations-partial-target-pagination">
                    @include('partials._reservations-pagination', ['reservations' => $reservations])
                </div>
            </div>
        </div>
    </div>

    <form id="filterForm" method="GET" style="display: none;">
        <input type="hidden" name="status" id="hidden_status">
        <input type="hidden" name="date_from" id="hidden_date_from">
        <input type="hidden" name="date_to" id="hidden_date_to">
        <input type="hidden" name="facility" id="hidden_facility">
        <input type="hidden" name="sort_by" id="hidden_sort_by">
    </form>
@endsection


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

            $('#facility-search').on('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = $(this).val();

                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            $('#searchButton').on('click', function() {
                performFilter();
            });

            function initTooltips() {
                $('.reservation-date').hover(function() {
                    const timeSlot = $(this).data('time-slot');
                    tooltip.text(timeSlot).fadeIn('fast');
                }, function() {
                    tooltip.hide();
                }).mousemove(function(e) {
                    tooltip.css({
                        top: e.pageY + 10 + 'px',
                        left: e.pageX + 10 + 'px'
                    });
                });
            }

            // Enhanced filter performance function
            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                // Get filter values
                const search = $('#facility-search').val();
                const status = $('#status').val();
                const dateFrom = $('#date_from').val();
                const dateTo = $('#date_to').val();
                const facility = $('#facility').val();
                const sortBy = $('#sort_by').val();
                // Show loading state
                showLoadingState(true);

                let url = '{{ route('admin.facilities.reservations') }}';
                let params = [];

                if (search) {
                    params.push('search=' + encodeURIComponent(search));
                }
                if (status) {
                    params.push('status=' + encodeURIComponent(status));
                }
                if (dateFrom) {
                    params.push('date_from=' + encodeURIComponent(dateFrom));
                }
                if (dateTo) {
                    params.push('date_to=' + encodeURIComponent(dateTo));
                }
                if (facility) {
                    params.push('facility=' + encodeURIComponent(facility));
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
                        $('#js-reservations-partial-target').html(response.reservations);
                        $('#js-reservations-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initTooltips();
                        initPaginationEvents();
                        updateActiveFiltersDisplay();
                        initRowClicks()
                        $(window).scrollTop(lastScrollPosition);

                        // Show success feedback
                        showNotification(`Found ${response.count} reservation(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification(
                            'An error occurred while filtering reservations. Please try again.',
                            'error');
                    }
                });
            }

            // Show/hide loading state
            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select, .filter-input, #facility-search').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, .filter-input, #facility-search').prop('disabled', false);
                    $('#applyFilters').prop('disabled', false).html(
                        '<i class="icon-filter me-1"></i> Apply Filters');
                }
            }

            // Initialize pagination events
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
                            $('#js-reservations-partial-target').html(response.reservations);
                            $('#js-reservations-partial-target-pagination').html(response
                                .pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initTooltips();
                            initPaginationEvents();
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
                if (urlParams.get('status')) {
                    count++;
                    const statusText = $('#status option:selected').text();
                    addFilterTag(`Status: ${statusText}`, 'status');
                }
                if (urlParams.get('date_from')) {
                    count++;
                    addFilterTag(`From: ${urlParams.get('date_from')}`, 'date_from');
                }
                if (urlParams.get('date_to')) {
                    count++;
                    addFilterTag(`To: ${urlParams.get('date_to')}`, 'date_to');
                }
                if (urlParams.get('facility')) {
                    count++;
                    const facilityText = $('#facility option:selected').text();
                    addFilterTag(`Facility: ${facilityText}`, 'facility');
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
                        $('#facility-search').val('');
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
            $('#status, #facility, #sort_by').on('change', function() {
                performFilter();
            });

            $('#date_from, #date_to').on('change', function() {
                validateDateRange();
                performFilter();
            });

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#facility-search').val('');
                $('#status').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                $('#facility').val('');
                $('#sort_by').val('newest');

                performFilter();
            });

            function validateDateRange() {
                const dateFrom = $('#date_from').val();
                const dateTo = $('#date_to').val();

                if (dateFrom) {
                    $('#date_to').attr('min', dateFrom);
                    if (dateTo && dateTo < dateFrom) {
                        $('#date_to').val(dateFrom);
                        showNotification('End date adjusted to match start date', 'warning', 3000);
                    }
                }

                if (dateTo) {
                    $('#date_from').attr('max', dateTo);
                    if (dateFrom && dateFrom > dateTo) {
                        $('#date_from').val(dateTo);
                        showNotification('Start date adjusted to match end date', 'warning', 3000);
                    }
                }
            }

            $(document).on('click', '.reservation-row', function(e) {
                if (!$(e.target).is('select, option, button, .btn, .status-select, .btn-close')) {
                    window.location = $(this).data('href');
                }
            });

            // Status change handler
            $(document).on('change', '.status-select', function() {
                const reservationId = $(this).data('reservation-id');
                const newStatus = $(this).val();
                const statusBadge = $(this).closest('tr').find('.status-badge-container .status-badge');

                $.ajax({
                    url: '/admin/reservations/' + reservationId + '/status',
                    type: 'PATCH',
                    data: {
                        status: newStatus,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        statusBadge
                            .removeClass(
                                'bg-success bg-danger bg-warning bg-info bg-secondary text-dark text-white'
                            )
                            .addClass(getStatusBadgeClass(newStatus))
                            .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));

                        $(`.status-select[data-reservation-id="${reservationId}"]`).prop(
                            'disabled', true);
                        showNotification('Status updated successfully!', 'success');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showNotification('Error updating status. Please try again.', 'error');
                    }
                });
            });

            // Helper functions
            function getStatusBadgeClass(status) {
                const statusClasses = {
                    'pending': 'bg-warning text-white',
                    'reserved': 'bg-primary text-white',
                    'completed': 'bg-success text-white',
                    'canceled': 'bg-danger text-white'
                };
                return statusClasses[status] || 'bg-secondary text-white';
            }

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
                    setTimeout(() => $('#facility-search').focus(), 100);
                }
            });

            // Initialize
            initTooltips();
            initPaginationEvents();
            updateActiveFiltersDisplay();
            validateDateRange();
            initRowClicks();

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
