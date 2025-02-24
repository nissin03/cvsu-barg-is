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
                                <input type="text" id="product-search" placeholder="Search here..." class=""
                                    name="name" tabindex="2" value="" aria-required="true" required="">
                            </fieldset>
                            <div class="button-submit">
                                <button class="" type="submit"><i class="icon-search"></i></button>
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
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $groupedAttributes = [];
                                    $uniqueAttributes = [];
                                    foreach ($product->attributeValues as $value) {
                                        if ($value->productAttribute) {
                                            $attributeId = $value->product_attribute_id;
                                            $groupedAttributes[$attributeId][] = $value;
                                            if (!isset($uniqueAttributes[$attributeId])) {
                                                $uniqueAttributes[$attributeId] = $value->productAttribute;
                                            }
                                        }
                                    }

                                    $currentStock =
                                        $product->attributeValues->count() > 0
                                            ? $product->attributeValues->sum('quantity')
                                            : $product->quantity;

                                    // Set stock status based on quantities
                                    if ($currentStock == 0) {
                                        $stockStatus = 'Out of Stock';
                                        $badgeClass = 'badge-danger';
                                    } elseif ($currentStock <= $product->outofstock_quantity) {
                                        $stockStatus = 'Low Stock';
                                        $badgeClass = 'badge-danger';
                                    } elseif ($currentStock <= $product->reorder_quantity) {
                                        $stockStatus = 'Reorder Level';
                                        $badgeClass = 'badge-warning';
                                    } else {
                                        $stockStatus = 'In Stock';
                                        $badgeClass = 'badge-success';
                                    }

                                @endphp
                                <tr>
                                    {{-- <td style="width: 3%">{{ $product->id }}</td> --}}
                                    <td class="pname" style="width: 100%">
                                        <div class="image">
                                            <img src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}"
                                                alt="{{ $product->name }}" class="image">
                                        </div>
                                        <div class="name">
                                            <a href="#" class="body-title-2">{{ $product->name }}</a>
                                            <div class="text-tiny mt-3">
                                                <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Quantity column with detailed variant tooltip --}}
                                    <td class="variant-cell" style="width: 10%">
                                        @if ($product->attributeValues->count() > 0)
                                            <span class="variant-value"
                                                data-bs-content="Quantity: {{ $product->attributeValues->first()->quantity }}">
                                                {{ $product->attributeValues->sum('quantity') }}
                                            </span>
                                            <div class="variant-tooltip">
                                                <!-- Tooltip content for variant quantities -->
                                                @foreach ($groupedAttributes as $attributeId => $variants)
                                                    <div class="variant-group">
                                                        <div class="variant-attribute-name">
                                                            {{ $uniqueAttributes[$attributeId]->name }}</div>
                                                        @foreach ($variants as $variant)
                                                            <div class="variant-row tooltip-highlight">
                                                                <span class="variant-value">{{ $variant->value }}</span>
                                                                <span class="variant-detail">{{ $variant->quantity }}
                                                                    units</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $product->quantity }}
                                        @endif
                                    </td>

                                    <td class="variant-cell" style="width: 10%">
                                        @if ($product->attributeValues->count() > 0)
                                            <span class="variant-value"
                                                data-bs-content="Price: &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}">
                                                &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}
                                            </span>
                                            <div class="variant-tooltip">
                                                <!-- Tooltip content for variant prices -->
                                                @foreach ($groupedAttributes as $attributeId => $variants)
                                                    <div class="variant-group">
                                                        <div class="variant-attribute-name">
                                                            {{ $uniqueAttributes[$attributeId]->name }}</div>
                                                        @foreach ($variants as $variant)
                                                            <div class="variant-row tooltip-highlight">
                                                                <span class="variant-value">{{ $variant->value }}</span>
                                                                <span
                                                                    class="variant-detail">&#8369;{{ number_format($variant->price, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            &#8369;{{ number_format($product->price, 2) }}
                                        @endif
                                    </td>


                                    <td style="width: 10%">
                                        @if ($product->category)
                                            @if ($product->category->parent)
                                                {{ $product->category->parent->name }} &raquo;
                                                {{ $product->category->name }}
                                            @else
                                                {{ $product->category->name }}
                                            @endif
                                        @else
                                            No Category
                                        @endif
                                    </td>


                                    <td style="width: 7%">
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                            <form action="{{ route('admin.product.archive', ['id' => $product->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="item text-warning archive">
                                                    <i class="icon-archive"></i>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
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

        $(document).ready(function() {
            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

            $('.variant-value').hover(function() {
                const $this = $(this);

                // Temporarily clear the title attribute to avoid the black tooltip
                $this.data('title', $this.attr('title')).removeAttr('title');

                // Use the custom tooltip
                const content = $this.data('title'); // Retrieve the saved title content
                tooltip.text(content).fadeIn('fast'); // Show custom tooltip
            }, function() {
                const $this = $(this);

                // Restore the title attribute for other purposes
                $this.attr('title', $this.data('title'));

                tooltip.hide(); // Hide custom tooltip
            }).mousemove(function(e) {
                tooltip.css({
                    top: e.pageY + 10 + 'px', // Adjust position relative to cursor
                    left: e.pageX + 10 + 'px'
                });
            });
        });


        $(document).ready(function() {
            $('#product-search').on('input', function() {
                let query = $(this).val();

                if (query.length > 0) {
                    $.ajax({
                        url: "{{ route('admin.products.search') }}",
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(data) {
                            console.log(data); // Debug the response structure
                            let tableBody = $('tbody');
                            tableBody.empty(); // Clear existing table rows

                            if (data.length > 0) {
                                data.forEach(function(product) {
                                    let category = product.category ?
                                        (product.category.parent ?
                                            `${product.category.parent.name} &raquo; ${product.category.name}` :
                                            product.category.name) :
                                        "No Category";

                                    // Use the badge class and stock status returned from the backend
                                    let badgeClass = product
                                    .badge_class; // From backend
                                    let stockStatus = product
                                    .stock_status; // From backend
                                    let productPrice = product.price; // From backend

                                    let variantPricesHtml = '';
                                    // Render the variant prices if available
                                    if (product.variant_prices && product.variant_prices
                                        .length > 0) {
                                        product.variant_prices.forEach(function(
                                        variant) {
                                            variantPricesHtml += `
                                        <div class="variant-row">
                                            <span class="variant-value">${variant.value}</span>
                                            <span class="variant-price">&#8369;${parseFloat(variant.price).toFixed(2)}</span>
                                        </div>
                                    `;
                                        });
                                    }

                                    tableBody.append(`
                                <tr>
                                    <td>
                                        <div class="product-info d-flex align-items-center">
                                            <div class="product-image" style="margin-right: 10px;">
                                                <img src="/uploads/products/thumbnails/${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                            </div>
                                            <div>
                                                <a href="#" class="product-name">${product.name}</a>
                                                <div class="badge-container mt-2">
                                                    <span class="badge ${badgeClass}">${stockStatus}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">${product.total_quantity}</td>
                                    <td class="text-center">&#8369;${parseFloat(productPrice).toFixed(2)}</td> <!-- Correct price -->
                                    <td class="text-center">${category}</td>
                                    <td class="text-center">
                                        <div class="list-icon-function">
                                            <a href="/admin/products/edit/${product.id}">
                                                <div class="item edit"><i class="icon-edit-3"></i></div>
                                            </a>
                                            <form action="/admin/products/archive/${product.id}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="item text-warning archive"><i class="icon-archive"></i></div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            `);
                                });
                            } else {
                                tableBody.append(`
                            <tr>
                                <td colspan="5" class="text-center">No products found with "${query}".</td>
                            </tr>
                        `);
                            }
                        }
                    });
                } else {
                    location.reload(); // Reload the page if search is cleared
                }
            });
        });
    </script>
@endpush
