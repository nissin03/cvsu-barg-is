@extends('layouts.admin')

@section('content')
    <style>
        /* Existing styles from your code */
        .box {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 4px;
        }

        .badge-success {
            background-color: #28a745;
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .badge-warning {
            background-color: #ffc107;
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .badge-danger {
            background-color: #dc3545;
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        /* Enhanced tooltip styles */
        .variant-cell {
            position: relative;
            cursor: pointer;
        }

        .variant-cell:hover .variant-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .variant-tooltip {
            position: absolute;
            top: -100%;
            left: 40%;
            transform: translateX(-100%) translateY(-100px);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 0 10px;
            min-width: 150px;
            opacity: 0;
            border: 1px solid;
            transition: all 0.2s ease-in-out;
            z-index: 100;
            margin-top: 19px;
            pointer-events: none;
        }

        .variant-group {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .variant-group:last-child {
            border-bottom: none;
        }

        .variant-attribute-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .variant-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #666;
        }

        .variant-value {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .variant-value:hover {
            color: #28a745;
        }

        .variant-tooltip {
            display: none;
        }

        .variant-cell:hover .variant-tooltip {
            display: block;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table th,
        .table td {
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Loading indicator */
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

        .custom-tooltip {
            position: absolute;
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            z-index: 1000;
            display: none;
            max-width: 250px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Filter styles from orders page */
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

        /* RESPONSIVE LAYOUT ADDITIONS (mobile cards, table, pagination) */

        .table-all-user {
            width: 100%;
        }

        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .mobile-card-header {
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-card-image {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .mobile-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mobile-card-title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            word-wrap: break-word;
        }

        .mobile-card-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: #6b7280;
        }

        .mobile-card-body {
            padding: 12px 16px 14px;
        }

        .mobile-card-details p {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #475569;
        }

        .mobile-card-details p strong {
            color: #111827;
        }

        .mobile-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .mobile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
        }

        .mobile-btn-primary {
            background: linear-gradient(135deg, #0d6efd, #2563eb);
            color: #ffffff;
        }

        .mobile-btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
        }

        .mobile-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }


        .name-cell .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
            color: #1e293b;
        }

        /* Pagination wrapper */
        .wgp-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px 0;
        }

        .wgp-pagination .pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 0;
        }

        .wgp-pagination .page-link {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Filter badge responsive text */
        @media (max-width: 768px) {
            .filter-tag-enhanced {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .mobile-card-actions {
                flex-direction: column;
            }

            .filter-toggle-section {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
        }

        @media (max-width: 576px) {
            .mobile-card-header {
                flex-wrap: wrap;
                align-items: flex-start;
            }

            .mobile-card-image {
                width: 40px;
                height: 40px;
            }

            .mobile-card-title {
                font-size: 14px;
            }

            .mobile-card-details p {
                font-size: 12px;
            }

            .mobile-btn {
                font-size: 11px;
                padding: 7px 10px;
            }
        }

        /* Switch between table and cards using breakpoints */
        @media (max-width: 767.98px) {
            .mobile-cards {
                display: block;
            }

            .d-md-table {
                display: none !important;
            }
        }

        @media (min-width: 768px) {
            .mobile-cards {
                display: none !important;
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
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>All Products</h3>
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
                        <div class="text-tiny">All Products</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="product-search"
                                    placeholder="Search products by name, description..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
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
                                <!-- Category Filter -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Category</label>
                                        <select name="category" id="category" class="filter-select form-select">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                                @if ($category->children->isNotEmpty())
                                                    @foreach ($category->children as $child)
                                                        <option value="{{ $child->id }}"
                                                            {{ request('category') == $child->id ? 'selected' : '' }}>
                                                            &nbsp;&nbsp;&nbsp;→ {{ $child->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Stock Status Filter -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Stock Status</label>
                                        <select name="stock_status" id="stock_status" class="filter-select form-select">
                                            <option value="">All Products</option>
                                            <option value="instock"
                                                {{ request('stock_status') == 'instock' ? 'selected' : '' }}>
                                                In Stock
                                            </option>
                                            <option value="reorder"
                                                {{ request('stock_status') == 'reorder' ? 'selected' : '' }}>
                                                Reorder Level
                                            </option>
                                            <option value="outofstock"
                                                {{ request('stock_status') == 'outofstock' ? 'selected' : '' }}>
                                                Out of Stock
                                            </option>
                                        </select>
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
                                            <option value="name_asc"
                                                {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                                                Name (A-Z)
                                            </option>
                                            <option value="name_desc"
                                                {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                                                Name (Z-A)
                                            </option>
                                            <option value="stock_low"
                                                {{ request('sort_by') == 'stock_low' ? 'selected' : '' }}>
                                                Stock: Low to High
                                            </option>
                                            <option value="stock_high"
                                                {{ request('sort_by') == 'stock_high' ? 'selected' : '' }}>
                                                Stock: High to Low
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-4 gap-3 flex-wrap">
                                <button class="btn btn-primary btn-lg" id="applyFilters">
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

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="tf-button w-auto" href="{{ route('admin.product.add') }}">
                            <i class="icon-plus"></i>Add Product
                        </a>
                        <a class="tf-button w-auto" href="{{ route('admin.product-attribute-add') }}">
                            <i class="icon-plus"></i>Add Variations
                        </a>
                        <a class="tf-button w-auto" href="{{ route('admin.archived-products') }}">
                            <i class="icon-archive"></i> Archived Products
                        </a>
                    </div>
                </div>

                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        <div id="js-products-partial-target">
                            {{-- This partial now contains BOTH mobile cards + desktop table --}}
                            @include('partials._products-table', ['products' => $products])
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="d-flex justify-content-end">
                    <div class="legend d-flex align-items-center gap-3 small text-body-secondary" aria-label="Legend">
                        <span class="d-inline-flex align-items-center gap-3">
                            <div class="box bg-success"></div>
                            <p>INSTOCK</p>
                            <div class="box bg-warning"></div>
                            <p>REORDER LEVEL</p>
                            <div class="box bg-danger"></div>
                            <p>OUT OF STOCK</p>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                    id="js-products-partial-target-pagination">
                    @include('partials._products-pagination', ['products' => $products])
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
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

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
            $('#date_range').on('change', function() {
                const dateRangeInputs = $('.date-range-inputs');
                if ($(this).val() === 'custom') {
                    dateRangeInputs.addClass('active');
                } else {
                    dateRangeInputs.removeClass('active');
                    $('#date_from, #date_to').val('');
                }
            });

            // Search with debounce
            $('#product-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            function initRowClicks() {
                // Make both table rows & mobile cards clickable, but ignore clicks on buttons/links/forms
                $('.product-row, .product-card').off('click').on('click', function(e) {
                    if ($(e.target).closest('a, button, .archive, form').length) {
                        return;
                    }
                    window.location = $(this).data('href');
                });
            }

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                const searchInput = $('#product-search');
                const searchValue = searchInput.val();
                const cursorPosition = searchInput[0].selectionStart;
                const wasSearchFocused = searchInput.is(':focus')

                // Get all filter values
                const search = $('#product-search').val();
                const category = $('#category').val();
                const dateRange = $('#date_range').val();
                const dateFrom = $('#date_from').val();
                const dateTo = $('#date_to').val();
                const stockStatus = $('#stock_status').val();
                const sortBy = $('#sort_by').val();

                showLoadingState(true);

                let url = '{{ route('admin.products') }}';
                let params = [];

                if (search) params.push('search=' + encodeURIComponent(search));
                if (category) params.push('category=' + encodeURIComponent(category));
                if (dateRange) {
                    params.push('date_range=' + encodeURIComponent(dateRange));
                    if (dateRange === 'custom') {
                        if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
                        if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
                    }
                }
                if (stockStatus) params.push('stock_status=' + encodeURIComponent(stockStatus));
                if (sortBy && sortBy !== 'newest') params.push('sort_by=' + encodeURIComponent(sortBy));
                params.push('archived=' + '{{ request('archived', 0) }}');

                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        // response.products now contains the mobile cards + table markup
                        $('#js-products-partial-target').html(response.products);
                        $('#js-products-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initPaginationEvents();
                        initArchiveButtons();
                        initTooltips();
                        initRowClicks();
                        updateActiveFiltersDisplay();
                        $(window).scrollTop(lastScrollPosition);
                        if (wasSearchFocused) {
                            const newSearchInput = $('#product-search');
                            newSearchInput.focus();
                            if (newSearchInput[0].setSelectionRange) {
                                newSearchInput[0].setSelectionRange(cursorPosition, cursorPosition);
                            }
                        }
                        showNotification(`Found ${response.count} product(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification(
                            'An error occurred while filtering products. Please try again.', 'error'
                        );
                        if (wasSearchFocused) {
                            $('#product-search').focus();
                        }
                    }
                });
            }

            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...'
                    );
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, .filter-input, #product-search').prop('disabled', false);
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
                            $('#js-products-partial-target').html(response.products);
                            $('#js-products-partial-target-pagination').html(response
                                .pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initArchiveButtons();
                            initTooltips();
                            initRowClicks();
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
                if (urlParams.get('category')) {
                    count++;
                    const categoryText = $('#category option:selected').text();
                    addFilterTag(`Category: ${categoryText}`, 'category');
                }
                if (urlParams.get('date_range')) {
                    count++;
                    const dateRangeVal = urlParams.get('date_range');
                    if (dateRangeVal === 'custom') {
                        const from = urlParams.get('date_from') || 'start';
                        const to = urlParams.get('date_to') || 'end';
                        addFilterTag(`Date: ${from} to ${to}`, 'date_range');
                    } else {
                        const dateText = $('#date_range option:selected').text();
                        addFilterTag(`Date: ${dateText}`, 'date_range');
                    }
                }
                if (urlParams.get('stock_status')) {
                    count++;
                    const stockText = $('#stock_status option:selected').text();
                    addFilterTag(`Stock: ${stockText}`, 'stock_status');
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
                        $('#product-search').val('');
                    } else if (filterToRemove === 'date_range') {
                        $('#date_range').val('');
                        $('#date_from, #date_to').val('');
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

            $('#category, #date_range, #stock_status, #sort_by').on('change', function() {
                performFilter();
            });

            $('#date_from, #date_to').on('change', function() {
                if ($('#date_range').val() === 'custom') {
                    performFilter();
                }
            });

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#product-search').val('');
                $('#category').val('');
                $('#date_range').val('');
                $('#date_from, #date_to').val('');
                $('.date-range-inputs').removeClass('active');
                $('#stock_status').val('');
                $('#sort_by').val('newest');
                performFilter();
            });

            // Initialize archive confirmation
            function initArchiveButtons() {
                $('.archive').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = $(this).closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to archive this product?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, archive it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }

            function initTooltips() {
                const tooltip = $('.custom-tooltip');
                if (!tooltip.length) {
                    $('<div class="custom-tooltip"></div>').appendTo('body');
                }

                $('.variant-value').hover(function() {
                    const $this = $(this);
                    $this.data('title', $this.attr('title')).removeAttr('title');

                    const content = $this.data('title') || $this.data('bs-content');
                    $('.custom-tooltip').text(content).fadeIn('fast');
                }, function() {
                    const $this = $(this);
                    $this.attr('title', $this.data('title'));
                    $('.custom-tooltip').hide();
                }).mousemove(function(e) {
                    $('.custom-tooltip').css({
                        top: e.pageY + 10 + 'px',
                        left: e.pageX + 10 + 'px'
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
                    setTimeout(() => $('#product-search').focus(), 100);
                }
            });

            // Initialize everything
            initPaginationEvents();
            initArchiveButtons();
            initTooltips();
            initRowClicks();
            updateActiveFiltersDisplay();

            // Welcome tip for new users
            if (window.location.search === '' || window.location.search === '?archived=0') {
                setTimeout(function() {
                    showNotification(
                        '💡 Tip: Use Ctrl+F to open filters, Ctrl+Enter to apply, or Esc to clear all',
                        'info', 6000);
                }, 1000);
            }
        });
    </script>
@endpush
