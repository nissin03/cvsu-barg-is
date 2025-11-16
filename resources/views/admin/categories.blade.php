@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Categories</h3>
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
                        <div class="text-tiny">Categories</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="category-search" placeholder="Search here..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" style="display:none;"><i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <a class="tf-button  w-auto" href="{{ route('admin.category.add') }}"><i class="icon-plus"></i>Add
                        new</a>
                    <a class="tf-button  w-auto" href="{{ route('admin.archived-categories') }}">
                        <i class="icon-archive"></i> Archived Categories</a>
                </div>
                <div class="table-all-user g-table">
                    <div class="table-responsive" id="js-categories-partial-target">
                        @include('partials._categories-table', ['categories' => $categories])
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination"
                        id="js-categories-partial-target-pagination">
                        @include('partials._categories-pagination', ['categories' => $categories])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .category-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .parent-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-left: 3px solid transparent;
        }

        .parent-row:hover {
            background-color: #f8f9fa !important;
        }

        .parent-row.expanded {
            border-left: 3px solid #007bff;
            background-color: #f0f7ff !important;
        }

        .child-row {
            background-color: #fafbfc !important;
            border-left: 3px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .child-row:hover {
            background-color: #f1f3f5 !important;
        }

        .category-content {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 0;
        }

        .child-content {
            padding-left: 20px;
        }

        .category-toggle {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
            color: #6c757d;
        }

        .parent-row.expanded .toggle-icon {
            transform: rotate(90deg);
            color: #007bff;
        }

        .toggle-placeholder {
            width: 20px;
            display: inline-block;
        }

        .child-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 8px;
            color: #6c757d;
            position: relative;
        }

        .child-line {
            width: 2px;
            height: 100%;
            background-color: #dee2e6;
            position: absolute;
            left: -15px;
            top: 0;
        }

        .child-indicator i {
            font-size: 16px;
        }

        .category-name-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 12px;
            line-height: 1;
            vertical-align: middle;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .list-icon-function {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .list-icon-function .item {
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .list-icon-function .item:hover {
            background-color: #f8f9fa;
            transform: scale(1.1);
        }

        .list-icon-function .edit:hover {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .list-icon-function .delete:hover {
            background-color: #ffebee;
            color: #c62828;
        }

        .action-link {
            text-decoration: none;
        }

        /* Smooth expand/collapse animation */
        .child-row {
            opacity: 0;
            transform: translateY(-10px);
        }

        .child-row.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Image styling */
        .category-content .image img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .child-content .image img {
            width: 35px;
            height: 35px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let lastScrollPosition = 0;

            // Debounce function for search
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
                const searchTerm = $('#category-search').val();
                lastScrollPosition = $(window).scrollTop();
                $('#loading-indicator').show();

                $.ajax({
                    url: '{{ route('admin.categories') }}',
                    type: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        $('#js-categories-partial-target').html(response.categories);
                        $('#js-categories-partial-target-pagination').html(response.pagination);

                        $('#loading-indicator').hide();
                        initPaginationEvents();
                        initArchiveButtons();
                        initCategoryToggle();
                        $(window).scrollTop(lastScrollPosition);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#loading-indicator').hide();
                    }
                });
            };

            $('#category-search').on('keyup', debounce(performSearch, 500));


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
                            $('#js-categories-partial-target').html(response.categories);
                            $('#js-categories-partial-target-pagination').html(response
                                .pagination);

                            $('#loading-indicator').hide();
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initArchiveButtons();
                            initCategoryToggle();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('#loading-indicator').hide();
                        }
                    });
                });
            }


            function initArchiveButtons() {
                $('.delete').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = $(this).closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to archive this record?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, archive it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }

            function initCategoryToggle() {

                $('.parent-row').off('click').on('click', function(e) {
                    if (!$(e.target).closest('.list-icon-function, .action-link').length) {
                        const categoryId = $(this).data('category-id');
                        const childRows = $(`.children-of-${categoryId}`);

                        if (childRows.length > 0) {
                            $(this).toggleClass('expanded');

                            childRows.each(function(index) {
                                const $row = $(this);
                                setTimeout(() => {
                                    if ($row.is(':visible')) {
                                        $row.removeClass('show');
                                        setTimeout(() => $row.hide(), 300);
                                    } else {
                                        $row.show();
                                        setTimeout(() => $row.addClass('show'), 10);
                                    }
                                }, index * 50);
                            });
                        }
                    }
                });


                $('.action-link, .delete, .list-icon-function form').off('click').on('click', function(e) {
                    e.stopPropagation();
                });
            }

            initPaginationEvents();
            initArchiveButtons();
            initCategoryToggle();
        });
    </script>
@endpush
