@extends('layouts.admin')
@section('content')
    <style>
        /* Existing styles */
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
    </style>

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
                        <form class="form-search">
                            <fieldset class="name">
                                <input type="text" id="product-search" placeholder="Search here..." name="name"
                                    aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button class="" type="submit" style="display:none;"> <i
                                        class="icon-search"></i></button> <!-- Hidden submit button -->
                            </div>
                        </form>
                    </div>
                    <a class="tf-button style-1 w208" href="{{ route('admin.categories') }}"><i class="icon-plus"></i>Add
                        Categories</a>
                    <a class="tf-button style-1 w208" href="{{ route('admin.product-attribute-add') }}"><i
                            class="icon-plus"></i>Add Variations</a>
                    <a class="tf-button style-1 w208" href="{{ route('admin.product.add') }}"><i class="icon-plus"></i>Add
                        new</a>
                    <a class="tf-button style-1 w208" href="{{ route('admin.archived-products') }}"><i
                            class="icon-archive"></i> Archived Products</a>
                </div>
                <div class="table-responsive">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{ Session::get('status') }}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%">Name</th>
                                <th style="width: 3%">Quantity</th>
                                <th style="width: 3%">Price</th>
                                <th style="width: 4%">Category</th>
                                <th style="width: 9%">Action</th>
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
            $('#product-search').on('keyup', function() {
                var searchTerm = $(this).val();


                $.ajax({
                    url: '{{ route('admin.products') }}',
                    type: 'GET',
                    data: {
                        search: searchTerm,
                        archived: '{{ request('archived', 0) }}'
                    },
                    success: function(response) {

                        $('#js-products-partial-target').html(response.products);
                        $('#js-products-partial-target-pagination').html(response.pagination);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });

        });


        $(function() {

            // $('[data-toggle="tooltip"]').tooltip();

            // Archive button confirmation
            $('.archive').on('click', function(e) {
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
        });

        // $(document).ready(function() {
        //     $('#search-input').on('keyup', function() {
        //         var searchTerm = $(this).val();

        //         clearTimeout(window.typingTimer);
        //         if (searchTerm) {
        //             window.typingTimer = setTimeout(function() {

        //                 $.ajax({
        //                     url: '{{ route('admin.products') }}',
        //                     type: 'GET',
        //                     data: {
        //                         search: searchTerm
        //                     },
        //                     success: function(response) {

        //                         $('#js-products-partial-target-table').html(response
        //                             .products);
        //                         $('#js-products-partial-target-pagination').html(
        //                             response.pagination);
        //                     },
        //                     error: function(xhr, status, error) {
        //                         console.error('Error:', error);
        //                     }
        //                 });
        //             }, 300);
        //         } else {

        //             $('#products-table').html('');
        //             $('#pagination').html('');
        //         }
        //     });
        // });

        $(document).ready(function() {
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

            $('.variant-value').hover(function() {
                const $this = $(this);
                $this.data('title', $this.attr('title')).removeAttr('title');

                const content = $this.data('title');
                tooltip.text(content).fadeIn('fast');
            }, function() {
                const $this = $(this);

                $this.attr('title', $this.data('title'));

                tooltip.hide();
            }).mousemove(function(e) {
                tooltip.css({
                    top: e.pageY + 10 + 'px',
                    left: e.pageX + 10 + 'px'
                });
            });
        });
    </script>
@endpush
