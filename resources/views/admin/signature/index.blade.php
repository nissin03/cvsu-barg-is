@extends('layouts.admin')

@section('content')
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
                <h3 class="page-title">Signatures</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <div class="text-tiny">Signatures</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <!-- Search and Filter Toggle -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="signature-search" placeholder="Search by name, position, label..."
                                    name="search" tabindex="2" value="{{ request('search') }}" aria-required="true">
                            </fieldset>
                            <button type="submit" style="display: none"></button>
                        </form>
                    </div>

                    <div class="filter-toggle-section d-flex align-items-center gap-3">
                        <span class="badge bg-primary fs-6 py-2 px-3" id="activeFiltersCount" style="display: none;">
                            0 filters
                        </span>
                        <button class="btn btn-outline-primary btn-lg position-relative" id="filterToggle" type="button">
                            <i class="icon-filter me-1"></i> Filters
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
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Category</label>
                                        <select name="category" id="category" class="filter-select form-select">
                                            <option value="">All Categories</option>
                                            <option value="facility"
                                                {{ request('category') == 'facility' ? 'selected' : '' }}>
                                                Facility
                                            </option>
                                            <option value="product"
                                                {{ request('category') == 'product' ? 'selected' : '' }}>
                                                Product
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Report Type Filter -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Report Type</label>
                                        <select name="report_type" id="report_type" class="filter-select form-select">
                                            <option value="">All Report Types</option>
                                            <option value="sales"
                                                {{ request('report_type') == 'sales' ? 'selected' : '' }}>
                                                Sales
                                            </option>
                                            <option value="product"
                                                {{ request('report_type') == 'product' ? 'selected' : '' }}>
                                                Product
                                            </option>
                                            <option value="inventory"
                                                {{ request('report_type') == 'inventory' ? 'selected' : '' }}>
                                                Inventory
                                            </option>
                                            <option value="users"
                                                {{ request('report_type') == 'users' ? 'selected' : '' }}>
                                                Users
                                            </option>
                                            <option value="all" {{ request('report_type') == 'all' ? 'selected' : '' }}>
                                                All
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Status</label>
                                        <select name="status" id="status" class="filter-select form-select">
                                            <option value="">All Status</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option value="inactive"
                                                {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sort By -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="filter-select form-select">
                                            <option value="order"
                                                {{ request('sort_by', 'order') == 'order' ? 'selected' : '' }}>
                                                Order (Default)
                                            </option>
                                            <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>
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
                                            <option value="order_asc"
                                                {{ request('sort_by') == 'order_asc' ? 'selected' : '' }}>
                                                Order: Low to High
                                            </option>
                                            <option value="order_desc"
                                                {{ request('sort_by') == 'order_desc' ? 'selected' : '' }}>
                                                Order: High to Low
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

                <!-- Action Buttons -->
                <div class="action-buttons mb-3">
                    <a class="tf-button w-auto" href="{{ route('admin.signatures.create') }}">
                        <i class="icon-plus"></i> Add New Signature
                    </a>
                    <a class="tf-button w-auto" href="{{ route('admin.signatures.archive') }}">
                        <i class="icon-archive"></i> Archived Signatures
                    </a>
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($signatures as $signature)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title">{{ $signature->name }}</h5>
                                        <span
                                            class="badge {{ $signature->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $signature->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="mobile-card-body">
                                        <div class="mobile-card-details">
                                            <p><strong>Position:</strong> {{ $signature->position }}</p>
                                            <p><strong>Category:</strong> {{ ucfirst($signature->category) }}</p>
                                            <p><strong>Report Type:</strong> {{ ucfirst($signature->report_type) }}</p>
                                            <p><strong>Label:</strong> {{ $signature->label }}</p>
                                            <p><strong>Order:</strong> #{{ $signature->order_by }}</p>
                                        </div>
                                        <div class="mobile-card-actions">
                                            <a href="{{ route('admin.signatures.edit', $signature->id) }}"
                                                class="btn btn-sm btn-primary mobile-btn">
                                                <i class="icon-edit-3"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.signatures.destroy', $signature->id) }}"
                                                method="POST" class="d-inline archive-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning archive mobile-btn">
                                                    <i class="icon-archive"></i> Archive
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="icon-file-text"></i>
                                    </div>
                                    <h4>No Signatures Found</h4>
                                    <p>Start by creating your first signature.</p>
                                    <a href="{{ route('admin.signatures.create') }}" class="btn btn-primary">
                                        <i class="icon-plus"></i> Add New Signature
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-label">Label</th>
                                    <th scope="col" class="col-name">Name</th>
                                    <th scope="col" class="col-position">Position</th>
                                    <th scope="col" class="col-category">Category</th>
                                    <th scope="col" class="col-report-type">Report Type</th>
                                    <th scope="col" class="col-order">Order</th>
                                    <th scope="col" class="col-status">Status</th>
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody id="js-signatures-partial-target">
                                @include('partials._signatures-table', [
                                    'signatures' => $signatures,
                                ])
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="pagination-container" id="js-signatures-partial-target-pagination">
                        {{ $signatures->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
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
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Base Layout */
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

        /* Filter Styles */
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

        /* Filter Tags */
        .filter-tag-enhanced {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .filter-tag-enhanced:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .filter-tag-enhanced .btn-close {
            background-color: #fff;
            color: #dc3545;
            border-radius: 50%;
            width: 1.4rem;
            height: 1.4rem;
            padding: 0;
            opacity: 0.8;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
        }

        .filter-tag-enhanced .btn-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* Search Input */
        .search-input,
        .form-search input {
            width: 100%;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus,
        .form-search input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1.2;
        }

        .badge-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .badge-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .badge-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        /* Table Styles */
        .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        .col-label {
            width: 12%;
        }

        .col-name {
            width: 18%;
        }

        .col-position {
            width: 15%;
        }

        .col-category {
            width: 10%;
        }

        .col-report-type {
            width: 12%;
        }

        .col-order {
            width: 8%;
        }

        .col-status {
            width: 10%;
        }

        .col-action {
            width: 15%;
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
            color: #1e293b;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .list-icon-function {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .list-icon-function .item {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .list-icon-function .edit {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .list-icon-function .edit:hover {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .list-icon-function .archive {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .list-icon-function .archive:hover {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        /* Mobile Card Styles */
        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .mobile-card-header {
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mobile-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
            min-width: 0;
        }

        .mobile-card-body {
            padding: 16px;
        }

        .mobile-card-details {
            margin-bottom: 16px;
        }

        .mobile-card-details p {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #475569;
        }

        .mobile-card-actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .mobile-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            flex: 1;
            justify-content: center;
        }

        .mobile-btn.btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .mobile-btn.btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        /* Empty State */
        .empty-state,
        .empty-state-table {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .empty-icon {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            padding: 15px 0;
            min-height: 60px;
        }

        /* SweetAlert Styles */
        .swal2-popup {
            border-radius: 16px !important;
        }

        .swal2-popup.swal2-warning::before {
            content: "!" !important;
            color: #f59e0b !important;
            border: 4px solid #f59e0b !important;
            background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
            width: 70px !important;
            height: 70px !important;
            line-height: 70px !important;
            border-radius: 50% !important;
            font-size: 32px !important;
            font-weight: 900 !important;
            display: block !important;
            margin: 20px auto 25px auto !important;
        }

        .swal2-icon {
            display: none !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-cards {
                display: block;
            }

            .d-md-table {
                display: none !important;
            }

            .action-buttons {
                width: 100%;
                justify-content: flex-end;
            }

            .filter-tag-enhanced {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .mobile-card-actions {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-end;
            }
        }
    </style>
@endpush

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
                    container.removeClass('show').slideUp(300);
                    icon.removeClass('icon-x-circle').addClass('icon-filter');
                } else {
                    container.addClass('show').hide().slideDown(300);
                    icon.removeClass('icon-filter').addClass('icon-x-circle');
                }
            });

            // Search with debounce
            $('#signature-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                // added to make the search functionality focus.
                const searchInput = $('#signature-search');
                const searchValue = searchInput.val();
                const cursorPosition = searchInput[0].selectionStart;
                const wasSearchFocused = searchInput.is(':focus')

                const search = $('#signature-search').val();
                const category = $('#category').val();
                const reportType = $('#report_type').val();
                const status = $('#status').val();
                const sortBy = $('#sort_by').val();

                showLoadingState(true);

                let url = '{{ route('admin.signatures.index') }}';
                let params = [];

                if (search) params.push('search=' + encodeURIComponent(search));
                if (category) params.push('category=' + encodeURIComponent(category));
                if (reportType) params.push('report_type=' + encodeURIComponent(reportType));
                if (status) params.push('status=' + encodeURIComponent(status));
                if (sortBy && sortBy !== 'order') params.push('sort_by=' + encodeURIComponent(sortBy));

                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#js-signatures-partial-target').html(response.signatures);
                        $('#js-signatures-mobile-target').html(response.mobile);
                        $('#js-signatures-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initPaginationEvents();
                        initArchiveButtons();
                        updateActiveFiltersDisplay();
                        $(window).scrollTop(lastScrollPosition);
                        // added to make the search functionality focus.
                        if (wasSearchFocused) {
                            const newSearchInput = $('#signature-search');
                            newSearchInput.focus();
                            if (newSearchInput[0].setSelectionRange) {
                                newSearchInput[0].setSelectionRange(cursorPosition, cursorPosition);
                            }
                        }
                        showNotification(`Found ${response.count} signature(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification('An error occurred. Please try again.', 'error');
                        // added to make the search functionality focus.
                        if (wasSearchFocused) {
                            $('#signature-search').focus();
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
                    // $('.filter-select, #signature-search').prop('disabled', true);
                    // $('#applyFilters').prop('disabled', true).html(
                    //     '<span class="spinner-border spinner-border-sm me-1"></span>Loading...'
                    // );
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, #signature-search').prop('disabled', false);
                    $('#applyFilters').prop('disabled', false).html(
                        '<i class="icon-filter me-1"></i> Apply Filters'
                    );
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
                            $('#js-signatures-partial-target').html(response.signatures);
                            $('#js-signatures-mobile-target').html(response.mobile);
                            $('#js-signatures-partial-target-pagination').html(response
                                .pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initArchiveButtons();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function() {
                            showLoadingState(false);
                            showNotification('Error loading page.', 'error');
                        }
                    });
                });
            }

            function updateActiveFiltersDisplay() {
                let count = 0;
                const filterTags = $('#filterTags');
                filterTags.empty();
                const urlParams = new URLSearchParams(window.location.search);

                const filters = [{
                        param: 'search',
                        label: 'Search',
                        format: v => `"${v}"`
                    },
                    {
                        param: 'category',
                        label: 'Category',
                        selector: '#category'
                    },
                    {
                        param: 'report_type',
                        label: 'Report Type',
                        selector: '#report_type'
                    },
                    {
                        param: 'status',
                        label: 'Status',
                        selector: '#status'
                    },
                    {
                        param: 'sort_by',
                        label: 'Sort',
                        selector: '#sort_by',
                        skip: 'order'
                    }
                ];

                filters.forEach(f => {
                    const val = urlParams.get(f.param);
                    if (val && val !== f.skip) {
                        count++;
                        const text = f.format ? f.format(val) :
                            (f.selector ? $(`${f.selector} option:selected`).text() : val);
                        addFilterTag(`${f.label}: ${text}`, f.param);
                    }
                });

                if (count > 0) {
                    $('#activeFiltersCount').show().text(`${count} filter${count > 1 ? 's' : ''}`);
                    $('#clearAllFilters, #activeFiltersRow').show();
                    $('#filterBadge').show().text(count);
                } else {
                    $('#activeFiltersCount, #clearAllFilters, #activeFiltersRow, #filterBadge').hide();
                }
            }

            function addFilterTag(text, filterName) {
                const tag = $(`
                    <span class="filter-tag-enhanced">
                        ${text}
                        <button type="button" class="btn-close icon-x text-danger"
                            data-filter="${filterName}" title="Remove"></button>
                    </span>
                `);

                tag.find('.btn-close').on('click', function() {
                    const filter = $(this).data('filter');
                    if (filter === 'search') {
                        $('#signature-search').val('');
                    } else {
                        $(`#${filter}`).val('');
                    }
                    performFilter();
                });

                $('#filterTags').append(tag);
            }

            // Event bindings
            $('#applyFilters').on('click', performFilter);
            $('#category, #report_type, #status, #sort_by').on('change', performFilter);

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#signature-search, #category, #report_type, #status').val('');
                $('#sort_by').val('order');
                performFilter();
            });

            function initArchiveButtons() {
                $('.archive').off('click').on('click', function(e) {
                    e.preventDefault();
                    var form = $(this).closest('form');
                    var signatureName = $(this).closest('tr, .mobile-card')
                        .find('.name, .mobile-card-title').text().trim();

                    Swal.fire({
                        title: 'Archive Signature?',
                        html: `
                            <div style="text-align: left; line-height: 1.6;">
                                <p style="text-align: center;">You are about to archive:</p>
                                <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7);
                                    padding: 15px; border-radius: 10px; margin: 15px 0;
                                    border-left: 4px solid #f59e0b;">
                                    <strong style="color: #92400e;">${signatureName}</strong>
                                </div>
                                <p style="color: #64748b; text-align: center; font-size: 14px;">
                                    This can be restored from the archived section.
                                </p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="icon-archive"></i> Yes, Archive',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            }

            function showNotification(message, type = 'info', duration = 4000) {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' : 'alert-info';
                const notification = $(`
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                        style="top:20px;right:20px;z-index:9999;min-width:300px;">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('body').append(notification);
                setTimeout(() => notification.fadeOut(function() {
                    $(this).remove();
                }), duration);
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
                    setTimeout(() => $('#signature-search').focus(), 100);
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('#signature-search').focus();
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    window.location.href = "{{ route('admin.signatures.create') }}";
                }
            });

            // Initialize
            initPaginationEvents();
            initArchiveButtons();
            updateActiveFiltersDisplay();

            @if (Session::has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ Session::get('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
