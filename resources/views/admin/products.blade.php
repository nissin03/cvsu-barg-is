@extends('layouts.admin')
@section('content')
    <style>
        /* Existing styles from your code */
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
            /* Green on hover */
        }

        .variant-tooltip {
            display: none;
            /* Hide tooltip by default */
        }

        .variant-cell:hover .variant-tooltip {
            display: block;
            /* Show tooltip on hover */
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

        /* New styles for loading indicator */
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
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;"> <!-- Prevent form submission -->
                            <fieldset class="name">
                                <input type="text" id="product-search" placeholder="Search here..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" style="display:none;"> <i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <a class="tf-button w-auto" href="{{ route('admin.product.add') }}"><i class="icon-plus"></i>Add
                        new</a>
                    <a class="tf-button w-auto" href="{{ route('admin.product-attribute-add') }}"><i
                            class="icon-plus"></i>Add Variations</a>
                    <a class="tf-button w-auto" href="{{ route('admin.archived-products') }}"><i class="icon-archive"></i>
                        Archived Products</a>
                </div>
                <div class="table-responsive">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center align-middle" scope="col" style="width: 20%">Name</th>
                                <th class="text-center align-middle" scope="col" style="width: 10%">Quantity</th>
                                <th class="text-center align-middle" scope="col" style="width: 15%">Price</th>
                                <th class="text-center align-middle" scope="col" style="width: 15%">Category</th>
                                <th class="text-center align-middle" scope="col" style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="js-products-partial-target">
                            @include('partials._products-table', ['products' => $products])
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
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
                const searchTerm = $('#product-search').val();
                lastScrollPosition = $(window).scrollTop();
                $('#loading-indicator').show();

                $.ajax({
                    url: '{{ route('admin.products') }}',
                    type: 'GET',
                    data: {
                        search: searchTerm,
                        archived: '{{ request('archived', 0) }}',
                        sort_column: determineSortColumn(searchTerm),
                        sort_direction: 'DESC'
                    },
                    success: function(response) {
                        $('#js-products-partial-target').html(response.products);
                        $('#js-products-partial-target-pagination').html(response.pagination);

                        $('#loading-indicator').hide();
                        initPaginationEvents();
                        initArchiveButtons();
                        initTooltips();
                        initRowClicks();
                        $(window).scrollTop(lastScrollPosition);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#loading-indicator').hide();
                    }
                });
            };

            function initRowClicks() {
                $('.product-row').off('click').on('click', function() {
                    window.location = $(this).data('href');
                });
            }

            function determineSortColumn(searchTerm) {
                if (!searchTerm) return 'created_at';
                if (!isNaN(searchTerm) && searchTerm.trim() !== '') {
                    return 'quantity';
                }

                return 'name';
            }

            $('#product-search').on('keyup', debounce(performSearch, 500));

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
                            $('#js-products-partial-target').html(response.products);
                            $('#js-products-partial-target-pagination').html(response
                                .pagination);

                            $('#loading-indicator').hide();
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initArchiveButtons();
                            initTooltips();
                            initRowClicks();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('#loading-indicator').hide();
                        }
                    });
                });
            }

            // Initialize archive confirmation
            function initArchiveButtons() {
                $('.archive').off('click').on('click', function(e) {
                    e.preventDefault();
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

            initPaginationEvents();
            initArchiveButtons();
            initTooltips();
            initRowClicks();
        });
    </script>
@endpush
