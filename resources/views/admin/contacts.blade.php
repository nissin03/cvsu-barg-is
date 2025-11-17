@extends('layouts.admin')

@section('content')
    <style>
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

        .filter-input {
            font-size: 1.1rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.2s ease;
            min-height: 3rem;
        }

        .filter-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
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

        /* Date range inputs */
        .date-range-inputs {
            display: none;
        }

        .date-range-inputs.active {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .date-range-inputs input {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-tag-enhanced {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
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
            <!-- Page header with breadcrumbs -->
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>All Messages</h3>
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
                        <div class="text-tiny">All Messages</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="contact-search" placeholder="Search by name or email..."
                                    name="search" aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" style="display:none;">
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

                <!-- Filter Container -->
                <div class="collapse mb-4" id="filterContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <!-- Date Range Filter -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Date Range</label>
                                        <select name="date_filter" id="date_filter" class="filter-select form-select">
                                            <option value="">All Time</option>
                                            <option value="today"
                                                {{ request('date_filter') == 'today' ? 'selected' : '' }}>
                                                Today
                                            </option>
                                            <option value="7days"
                                                {{ request('date_filter') == '7days' ? 'selected' : '' }}>
                                                Last 7 Days
                                            </option>
                                            <option value="30days"
                                                {{ request('date_filter') == '30days' ? 'selected' : '' }}>
                                                Last 30 Days
                                            </option>
                                            <option value="custom"
                                                {{ request('date_filter') == 'custom' ? 'selected' : '' }}>
                                                Custom Range
                                            </option>
                                        </select>
                                        <div
                                            class="date-range-inputs {{ request('date_filter') == 'custom' ? 'active' : '' }}">
                                            <input type="date" id="start_date" name="start_date"
                                                value="{{ request('start_date') }}" class="form-control"
                                                placeholder="Start Date">
                                            <input type="date" id="end_date" name="end_date"
                                                value="{{ request('end_date') }}" class="form-control"
                                                placeholder="End Date">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort By -->
                                <div class="col-md-6 col-lg-4">
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
                                            <option value="replied"
                                                {{ request('sort_by') == 'replied' ? 'selected' : '' }}>
                                                Recently Replied
                                            </option>
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

                <!-- Active Filters Display -->
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

                <!-- Success message -->
                @if (Session::has('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Card-based layout for messages -->
                <div id="js-contacts-partial-target">
                    @include('partials._contacts-cards', ['contacts' => $contacts])
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                    id="js-contacts-partial-target-pagination">
                    @include('partials._contacts-pagination', ['contacts' => $contacts])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let lastScrollPosition = 0;
            let searchTimeout = null;

            // Filter toggle functionality
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

            // Date range custom inputs toggle
            $('#date_filter').on('change', function() {
                const dateRangeInputs = $('.date-range-inputs');
                if ($(this).val() === 'custom') {
                    dateRangeInputs.addClass('active');
                } else {
                    dateRangeInputs.removeClass('active');
                    $('#start_date, #end_date').val('');
                }
            });

            // Search with debounce
            $('#contact-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                // Get all filter values
                const search = $('#contact-search').val();
                const dateFilter = $('#date_filter').val();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const sortBy = $('#sort_by').val();

                showLoadingState(true);

                let url = '{{ route('admin.contacts') }}';
                let params = [];

                if (search) params.push('search=' + encodeURIComponent(search));
                if (dateFilter) {
                    params.push('date_filter=' + encodeURIComponent(dateFilter));
                    if (dateFilter === 'custom') {
                        if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
                        if (endDate) params.push('end_date=' + encodeURIComponent(endDate));
                    }
                }
                if (sortBy && sortBy !== 'newest') params.push('sort_by=' + encodeURIComponent(sortBy));

                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#js-contacts-partial-target').html(response.contacts);
                        $('#js-contacts-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initPaginationEvents();
                        initDeleteButtons();
                        updateActiveFiltersDisplay();
                        $(window).scrollTop(lastScrollPosition);
                        showNotification(`Found ${response.count} message(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification(
                            'An error occurred while filtering messages. Please try again.', 'error'
                            );
                    }
                });
            }

            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select, .filter-input, #contact-search').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, .filter-input, #contact-search').prop('disabled', false);
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
                            $('#js-contacts-partial-target').html(response.contacts);
                            $('#js-contacts-partial-target-pagination').html(response
                                .pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initDeleteButtons();
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
                if (urlParams.get('date_filter')) {
                    count++;
                    const dateFilterVal = urlParams.get('date_filter');
                    if (dateFilterVal === 'custom') {
                        const from = urlParams.get('start_date') || 'start';
                        const to = urlParams.get('end_date') || 'end';
                        addFilterTag(`Date: ${from} to ${to}`, 'date_filter');
                    } else {
                        const dateText = $('#date_filter option:selected').text();
                        addFilterTag(`Date: ${dateText}`, 'date_filter');
                    }
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
                        $('#contact-search').val('');
                    } else if (filterToRemove === 'date_filter') {
                        $('#date_filter').val('');
                        $('#start_date, #end_date').val('');
                        $('.date-range-inputs').removeClass('active');
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

            $('#date_filter, #sort_by').on('change', function() {
                performFilter();
            });

            $('#start_date, #end_date').on('change', function() {
                if ($('#date_filter').val() === 'custom') {
                    performFilter();
                }
            });

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#contact-search').val('');
                $('#date_filter').val('');
                $('#start_date, #end_date').val('');
                $('.date-range-inputs').removeClass('active');
                $('#sort_by').val('newest');
                performFilter();
            });

            // Delete confirmation
            function initDeleteButtons() {
                $('.delete').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = $(this).closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to delete this message?",
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
            }

            // Show notification helper
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
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) { // Ctrl+Enter
                    e.preventDefault();
                    performFilter();
                }
                if (e.keyCode === 27) { // Escape
                    $('#clearAllFilters').click();
                }
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) { // Ctrl+F
                    e.preventDefault();
                    $('#filterToggle').click();
                    setTimeout(() => $('#contact-search').focus(), 100);
                }
            });

            // Initialize everything
            initPaginationEvents();
            initDeleteButtons();
            updateActiveFiltersDisplay();

            // Welcome tip for new users
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
