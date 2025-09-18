@extends('layouts.admin')

@section('content')
    <style>
        table {
            table-layout: auto;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 30px;
            border: 1px solid #ddd;
            word-wrap: break-word;
            white-space: normal;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        th:nth-child(8),
        td:nth-child(8) {
            min-width: 100px;
        }

        .table-striped th:nth-child(1),
        .table-striped td:nth-child(1) {
            width: 50px;
        }

        .table-striped th:nth-child(2),
        .table-striped td:nth-child(2) {
            width: 250px;
        }

        .table-striped th:nth-child(9),
        .table-striped td:nth-child(9) {
            width: 100px;
        }

        /* Additional Styling for Price Display */
        .price-container {
            display: flex;
            flex-direction: column;
        }

        .price-container span {
            margin-bottom: 4px;
        }

        /* Tooltip Styling */
        .tooltip {
            display: none;
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 8px;
            border-radius: 4px;
            z-index: 1000;
            font-size: 12px;
            max-width: 200px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .tooltip.show {
            display: block;
        }

        /* Badge Styles */
        .badge {
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            color: #fff;
            font-weight: bold;
            font-size: 0.75em;
            display: inline-block;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .list-icon-function .item {
            cursor: pointer;/
        }


        tr:hover {
            background-color: #f1f1f1;
        }

        /* Add loading indicator styles */
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
                <h3>Facilities</h3>
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
                        <div class="text-tiny">Facilities</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="facility-search" placeholder="Search here..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" style="display:none;"> <i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <a class="tf-button w-auto" href="{{ route('admin.facility.create') }}"><i class="icon-plus"></i>Add
                        New</a>
                    <a class="tf-button w-auto" href="{{ route('admin.facilities.archive.index') }}"><i
                            class="icon-archive"></i> Archived Facility</a>
                </div>
                <div class="table-responsive">
                    @if (Session::has('success'))
                        <p class="alert alert-success">{{ Session::get('success') }}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th scope="col">Type</th>
                                <th scope="col">Prices</th>
                                <th scope="col">Rooms</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="js-facilities-partial-target">
                            @include('partials._facilities-table', ['facilities' => $facilities])
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Code -->
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                    id="js-facilities-partial-target-pagination">
                    {{ $facilities->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <!-- SweetAlert2 script for delete confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            let lastScrollPosition = 0;

            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        func.apply(context, args);
                    }, wait);
                };
            }

            const performSearch = function() {
                const searchTerm = $('#facility-search').val();
                lastScrollPosition = $(window).scrollTop();
                $('#loading-indicator').show();

                $.ajax({
                    url: '{{ route('admin.facilities.index') }}',
                    type: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        $('#js-facilities-partial-target').html(response.facilities);
                        $('#js-facilities-partial-target-pagination').html(response.pagination);
                        $('#loading-indicator').hide();
                        initArchiveButton();
                        initTooltips();
                        $(window).scrollTop(lastScrollPosition);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#loading-indicator').hide();
                    }
                });
            };

            $('#facility-search').on('keyup', debounce(performSearch, 500));

            function initArchiveButton() {
                $('.archive').on('click', function(e) {
                    e.preventDefault();
                    const form = $(this).closest('form');
                    const row = $(this).closest('tr');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to archive this facility?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, archive it!',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: form.attr('action'),
                                type: 'POST',
                                data: form.serialize(),
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Archived!', response.message,
                                            'success');
                                        row.fadeOut(300, function() {
                                            $(this).remove();
                                        });
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error',
                                        'Something went wrong while archiving the facility.',
                                        'error');
                                }
                            });
                        }
                    });
                });
            }

            function initTooltips() {
                $('.facility-name').hover(function() {
                    var tooltipId = '#tooltip-' + $(this).data('id');
                    $(tooltipId).addClass('show');
                }, function() {
                    var tooltipId = '#tooltip-' + $(this).data('id');
                    $(tooltipId).removeClass('show');
                });
            }

            initArchiveButton();
            initTooltips();
        });
    </script>
@endpush
