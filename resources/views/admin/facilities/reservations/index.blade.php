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
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="filter-dropdowns flex items-center gap10">

                        <select name="status" id="status" class="filter-select">
                            <option value="">Select Status</option>
                            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
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
                                    <th class="text-center" scope="col" style="width: 20%">Facility</th>
                                    <th class="text-center" scope="col">Dates (From - To)</th>
                                    <th class="text-center" scope="col">User</th>
                                    <th class="text-center" scope="col">Price</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Actions</th>
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
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            let lastScrollPosition = 0;
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

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                let status = $('#status').val();

                $('#loading-indicator').show();
                let url = '{{ route('admin.facilities.reservations') }}';
                let params = [];

                if (status) {
                    params.push('status=' + encodeURIComponent(status));
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
                        $('#loading-indicator').hide();
                        window.history.pushState({}, '', url);
                        initTooltips();
                        initPaginationEvents();
                        $(window).scrollTop(lastScrollPosition);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#loading-indicator').hide();
                        alert('An error occurred while filtering reservations. Please try again.');
                    }
                });
            }

            function initPaginationEvents() {
                $('.pagination a').off('click').on('click', function(e) {
                    e.preventDefault();
                    lastScrollPosition = $(window).scrollTop();

                    const url = $(this).attr('href');
                    $('#loading-indicator').show();

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
                            $('#loading-indicator').hide();
                            window.history.pushState({}, '', url);
                            initTooltips();
                            initPaginationEvents();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('#loading-indicator').hide();
                        }
                    });
                });
            }
            $('#status').on('change', function() {
                performFilter();
            });

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
                        console.log(xhr.responseText);
                        showNotification('Error updating status. Please try again.', 'error');
                    }
                });


                function getStatusBadgeClass(status) {
                    const statusClasses = {
                        'pending': 'bg-warning text-white',
                        'reserved': 'bg-primary text-white',
                        'completed': 'bg-success text-white',
                        'canceled': 'bg-danger text-white'
                    };
                    return statusClasses[status] || 'bg-secondary text-white';
                }


                function showNotification(message, type) {
                    const notification = $('<div class="alert alert-' + (type === 'success' ? 'success' :
                            'danger') +
                        ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                        message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');

                    $('body').append(notification);

                    setTimeout(function() {
                        notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                }

                initTooltips();
                initPaginationEvents();
            });
        });
    </script>
@endpush
