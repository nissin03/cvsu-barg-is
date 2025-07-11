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

        /* Filter Section */
        .filter-section {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            min-width: 150px;
            background: #fff;
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
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="filter-dropdowns flex items-center gap10">
                        <!-- Time Slot Dropdown -->
                        <select name="time_slot" id="time_slot" class="filter-select">
                            <option value="">Select Time Slot</option>
                            <option value="08:00 am - 09:00 am"
                                {{ request('time_slot') == '08:00 am - 09:00 am' ? 'selected' : '' }}>08:00 am - 09:00 am
                            </option>
                            <option value="10:00 am - 11:00 am"
                                {{ request('time_slot') == '10:00 am - 11:00 am' ? 'selected' : '' }}>10:00 am - 11:00 am
                            </option>
                            <option value="01:00 pm - 02:00 pm"
                                {{ request('time_slot') == '01:00 pm - 02:00 pm' ? 'selected' : '' }}>01:00 pm - 02:00 pm
                            </option>
                            <option value="03:00 pm - 04:00 pm"
                                {{ request('time_slot') == '03:00 pm - 04:00 pm' ? 'selected' : '' }}>03:00 pm - 04:00 pm
                            </option>
                        </select>

                        <!-- Status Dropdown -->
                        <select name="status" id="status" class="filter-select">
                            <option value="">Select Status</option>
                            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved
                            </option>
                            <option value="pickedup" {{ request('status') == 'pickedup' ? 'selected' : '' }}>Picked Up
                            </option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled
                            </option>
                        </select>
                    </div>
                </div>

                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Reservation Date</th>
                                    <th class="text-center">Total Items</th>
                                    <th class="text-center">Total Price</th>
                                    <th class="text-center">Order Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="js-orders-partial-target">
                                @include('partials._orders-table', ['orders' => $orders])
                            </tbody>
                        </table>
                    </div>
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
            // Store current scroll position
            let lastScrollPosition = 0;

            // Tooltip for Reservation Date
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

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

            // Function to perform AJAX filter
            function performFilter() {
                // Save current scroll position
                lastScrollPosition = $(window).scrollTop();

                // Get filter values
                let status = $('#status').val();
                let timeSlot = $('#time_slot').val();

                // Show loading indicator
                $('#loading-indicator').show();

                // Build the URL with query parameters
                let url = '{{ route('admin.orders') }}';
                let params = [];

                if (status) {
                    params.push('status=' + encodeURIComponent(status));
                }

                if (timeSlot) {
                    params.push('time_slot=' + encodeURIComponent(timeSlot));
                }

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                // Perform AJAX request
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        // Update table content
                        $('#js-orders-partial-target').html(response.orders);

                        // Update pagination
                        $('#js-orders-partial-target-pagination').html(response.pagination);

                        // Hide loading indicator
                        $('#loading-indicator').hide();

                        // Update browser URL without page reload
                        window.history.pushState({}, '', url);

                        // Reinitialize tooltips
                        initTooltips();

                        // Reinitialize pagination events
                        initPaginationEvents();

                        // Restore scroll position
                        $(window).scrollTop(lastScrollPosition);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);

                        // Hide loading indicator
                        $('#loading-indicator').hide();

                        // Show error message if needed
                        alert('An error occurred while filtering orders. Please try again.');
                    }
                });
            }

            // Function to initialize pagination click events
            function initPaginationEvents() {
                $('.pagination a').off('click').on('click', function(e) {
                    e.preventDefault();

                    // Save current scroll position
                    lastScrollPosition = $(window).scrollTop();

                    const url = $(this).attr('href');

                    // Show loading indicator
                    $('#loading-indicator').show();

                    $.ajax({
                        url: url,
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            // Update table content
                            $('#js-orders-partial-target').html(response.orders);

                            // Update pagination
                            $('#js-orders-partial-target-pagination').html(response.pagination);

                            // Hide loading indicator
                            $('#loading-indicator').hide();

                            // Update browser URL without page reload
                            window.history.pushState({}, '', url);

                            // Reinitialize tooltips
                            initTooltips();

                            // Reinitialize pagination events
                            initPaginationEvents();

                            // Restore scroll position
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);

                            // Hide loading indicator
                            $('#loading-indicator').hide();
                        }
                    });
                });
            }

            // Add change event listeners to the filter dropdowns
            $('#status, #time_slot').on('change', function() {
                performFilter();
            });

            // Initialize tooltips and pagination events on page load
            initTooltips();
            initPaginationEvents();
        });
    </script>
@endpush
