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
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }

        .transaction-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-top: 4px;
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

            .filter-tag-enhanced {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
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

        .amount-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .amount-input {
            max-width: 120px;
        }

        /* Enhanced Tooltip */
        .custom-tooltip {
            position: absolute;
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            max-width: 200px;
            white-space: normal;
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
                <h3>Orders</h3>
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
                        <div class="text-tiny">Orders</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="order-search"
                                    placeholder="Search by customer, product, or transaction status..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
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
                                <!-- Order Status -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Order Status</label>
                                        <select name="order_status" id="order_status" class="filter-select form-select">
                                            <option value="">All Orders</option>
                                            <option value="reserved"
                                                {{ request('order_status') == 'reserved' ? 'selected' : '' }}>Reserved
                                            </option>
                                            <option value="pickedup"
                                                {{ request('order_status') == 'pickedup' ? 'selected' : '' }}>Picked Up
                                            </option>
                                            <option value="canceled"
                                                {{ request('order_status') == 'canceled' ? 'selected' : '' }}>Canceled
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Transaction Status -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Transaction Status</label>
                                        <select name="transaction_status" id="transaction_status"
                                            class="filter-select form-select">
                                            <option value="">All Transactions</option>
                                            <option value="paid"
                                                {{ request('transaction_status') == 'paid' ? 'selected' : '' }}>Paid
                                            </option>
                                            <option value="unpaid"
                                                {{ request('transaction_status') == 'unpaid' ? 'selected' : '' }}>Unpaid
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Time Slot -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Time Slot</label>
                                        <select name="time_slot_range" id="time_slot_range"
                                            class="filter-select form-select">
                                            <option value="">Select Time Slot</option>
                                            @foreach ($timeSlots as $slot)
                                                <option value="{{ $slot }}"
                                                    {{ request('time_slot_range') == $slot ? 'selected' : '' }}>
                                                    {{ $slot }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Sort By -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="filter-select form-select">
                                            <option value="newest"
                                                {{ request('sort_by', 'newest') == 'newest' ? 'selected' : '' }}>Newest
                                                First</option>
                                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>
                                                Oldest First</option>
                                            <option value="amount_high"
                                                {{ request('sort_by') == 'amount_high' ? 'selected' : '' }}>Amount: High to
                                                Low</option>
                                            <option value="amount_low"
                                                {{ request('sort_by') == 'amount_low' ? 'selected' : '' }}>Amount: Low to
                                                High</option>
                                            <option value="reservation_date"
                                                {{ request('sort_by') == 'reservation_date' ? 'selected' : '' }}>
                                                Reservation Date</option>
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

                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Reservation Schedule</th>
                                <th class="text-center">Items</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Order Date</th>
                            </tr>
                        </thead>
                        <tbody id="js-orders-partial-target">
                            @include('partials._orders-table', ['orders' => $orders])
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                    id="js-orders-partial-target-pagination">
                    @include('partials._orders-pagination', ['orders' => $orders])
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

            $('#order-search').on('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = $(this).val();

                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            // function initTooltips() {
            //     $('.reservation-date').hover(function() {
            //         const timeSlot = $(this).data('time-slot');
            //         if (timeSlot) {
            //             tooltip.text(timeSlot).fadeIn('fast');
            //         }
            //     }, function() {
            //         tooltip.hide();
            //     }).mousemove(function(e) {
            //         tooltip.css({
            //             top: e.pageY + 10 + 'px',
            //             left: e.pageX + 10 + 'px'
            //         });
            //     });
            // }

            function initRowClicks() {
                $('.order-row').off('click').on('click', function() {
                    window.location = $(this).data('href');
                });
            }

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                // Get all filter values
                const search = $('#order-search').val();
                const orderStatus = $('#order_status').val();
                const transactionStatus = $('#transaction_status').val();
                const timeSlotRange = $('#time_slot_range').val();
                const sortBy = $('#sort_by').val();

                showLoadingState(true);

                let url = '{{ route('admin.orders') }}';
                let params = [];

                if (search) params.push('search=' + encodeURIComponent(search));
                if (orderStatus) params.push('order_status=' + encodeURIComponent(orderStatus));
                if (transactionStatus) params.push('transaction_status=' + encodeURIComponent(transactionStatus));
                if (timeSlotRange) params.push('time_slot_range=' + encodeURIComponent(timeSlotRange));
                if (sortBy && sortBy !== 'newest') params.push('sort_by=' + encodeURIComponent(sortBy));

                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#js-orders-partial-target').html(response.orders);
                        $('#js-orders-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        // initTooltips();
                        initPaginationEvents();
                        initRowClicks();
                        updateActiveFiltersDisplay();
                        $(window).scrollTop(lastScrollPosition);
                        showNotification(`Found ${response.count} order(s)`, 'info', 2000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        showLoadingState(false);
                        showNotification('An error occurred while filtering orders. Please try again.',
                            'error');
                    }
                });
            }

            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select, .filter-input, #order-search').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...');
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, .filter-input, #order-search').prop('disabled', false);
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
                            $('#js-orders-partial-target').html(response.orders);
                            $('#js-orders-partial-target-pagination').html(response.pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            // initTooltips();
                            initPaginationEvents();
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
                if (urlParams.get('order_status')) {
                    count++;
                    const statusText = $('#order_status option:selected').text();
                    addFilterTag(`Order: ${statusText}`, 'order_status');
                }
                if (urlParams.get('transaction_status')) {
                    count++;
                    const transactionText = $('#transaction_status option:selected').text();
                    addFilterTag(`Transaction: ${transactionText}`, 'transaction_status');
                }
                if (urlParams.get('time_slot_range')) {
                    count++;
                    const timeText = $('#time_slot_range option:selected').text();
                    addFilterTag(`Time: ${timeText}`, 'time_slot_range');
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
                        $('#order-search').val('');
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

            $('#order_status, #transaction_status, #time_slot_range, #sort_by').on('change',
                function() {
                    performFilter();
                });

            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#order-search').val('');
                $('#order_status').val('');
                $('#transaction_status').val('');
                $('#time_slot_range').val('');
                $('#sort_by').val('newest');
                performFilter();
            });

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
                    setTimeout(() => $('#order-search').focus(), 100);
                }
            });

            // Initialize everything
            // initTooltips();
            initPaginationEvents();
            initRowClicks();
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
