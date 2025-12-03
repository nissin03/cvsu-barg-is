@if ($products->count() > 0)
    <div class="mobile-cards d-block d-md-none">
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

                // Quantity & price display for mobile
                if ($product->attributeValues->count() > 0) {
                    $mobileQuantity = $product->attributeValues->sum('quantity');
                    $mobilePrice = $product->attributeValues->first()->price;
                } else {
                    $mobileQuantity = $product->quantity;
                    $mobilePrice = $product->price;
                }
            @endphp

            <div class="mobile-card product-card" data-href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                <div class="mobile-card-header">
                    <div class="mobile-card-image">
                        <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                            alt="{{ $product->name }}">
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mobile-card-title">{{ $product->name }}</h5>
                        <div class="mobile-card-subtitle">
                            <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                        </div>
                    </div>
                </div>
                <div class="mobile-card-body">
                    <div class="mobile-card-details">
                        <p class="d-flex justify-content-between align-items-center">
                            <strong>Quantity:</strong>
                            <span class="d-flex align-items-center gap-2">
                                {{ $mobileQuantity }}
                                @if ($product->attributeValues->count() > 0)
                                    <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();"
                                        data-bs-toggle="modal" data-bs-target="#quantityModal{{ $product->id }}"
                                        title="View All Quantities">
                                        <i class="icon-eye"></i>
                                    </button>
                                @endif
                            </span>
                        </p>
                        <p class="d-flex justify-content-between align-items-center">
                            <strong>Price:</strong>
                            <span class="d-flex align-items-center gap-2">
                                &#8369;{{ number_format($mobilePrice, 2) }}
                                @if ($product->attributeValues->count() > 0)
                                    <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();"
                                        data-bs-toggle="modal" data-bs-target="#priceModal{{ $product->id }}"
                                        title="View All Prices">
                                        <i class="icon-eye"></i>
                                    </button>
                                @endif
                            </span>
                        </p>
                        <p>
                            <strong>Category:</strong>
                            @if ($product->category)
                                @if ($product->category->parent)
                                    {{ $product->category->parent->name }} &raquo; {{ $product->category->name }}
                                @else
                                    {{ $product->category->name }}
                                @endif
                            @else
                                No Category
                            @endif
                        </p>
                    </div>
                    <div class="mobile-card-actions">
                        <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}"
                            class="mobile-btn mobile-btn-primary">
                            <i class="icon-edit-3"></i> Edit
                        </a>

                        <button type="submit" onclick="event.preventDefault(); $(this).closest('form').submit();"
                            class="mobile-btn mobile-btn-warning archive">
                            <i class="icon-archive"></i> Archive
                        </button>

                        <form action="{{ route('admin.product.archive', ['id' => $product->id]) }}" method="POST"
                            class="d-none archive-form">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    {{-- DESKTOP TABLE (visible on md and up) --}}
    <table class="table table-striped table-bordered d-none d-md-table">
        <thead>
            <tr>
                <th class="text-center align-middle" scope="col" style="width: 30%">Name</th>
                <th class="text-center align-middle" scope="col" style="width: 15%">Quantity</th>
                <th class="text-center align-middle" scope="col" style="width: 20%">Price</th>
                <th class="text-center align-middle" scope="col" style="width: 20%">Category</th>
                <th class="text-center align-middle" scope="col" style="width: 15%">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                {{-- @php
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
                @endphp --}}
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

                    // Check if product has variants
                    if ($product->attributeValues->count() > 0) {
                        // Check individual variant quantities
                        $hasOutOfStock = false;
                        $hasReorderLevel = false;
                        $allInStock = true;

                        foreach ($product->attributeValues as $variant) {
                            if ($variant->quantity == 0) {
                                $hasOutOfStock = true;
                                $allInStock = false;
                            } elseif ($variant->quantity <= $product->reorder_quantity) {
                                $hasReorderLevel = true;
                                $allInStock = false;
                            }
                        }

                        // Determine stock status based on variant conditions
                        if ($hasOutOfStock) {
                            $stockStatus = 'Out of Stock';
                            $badgeClass = 'badge-danger';
                        } elseif ($hasReorderLevel) {
                            $stockStatus = 'Reorder Level';
                            $badgeClass = 'badge-warning';
                        } elseif ($allInStock) {
                            $stockStatus = 'In Stock';
                            $badgeClass = 'badge-success';
                        } else {
                            $stockStatus = 'In Stock';
                            $badgeClass = 'badge-success';
                        }
                    } else {
                        // Product without variants - use product's own quantity
    $currentStock = $product->quantity;

    if ($currentStock == 0) {
        $stockStatus = 'Out of Stock';
        $badgeClass = 'badge-danger';
    } elseif ($currentStock <= $product->reorder_quantity) {
        $stockStatus = 'Reorder Level';
        $badgeClass = 'badge-warning';
    } else {
        $stockStatus = 'In Stock';
        $badgeClass = 'badge-success';
                        }
                    }
                @endphp

                <tr class="product-row" data-href="{{ route('admin.product.edit', ['id' => $product->id]) }}"
                    style="cursor: pointer;">
                    <td scope="row" class="align-middle">
                        <div class="d-flex align-items-center gap-2 h-100">
                            <div class="image"
                                style="width: 48px; height: 48px; flex-shrink: 0; overflow: hidden; border-radius: 8px;">
                                <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                    alt="{{ $product->name }}" class="img-fluid"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="name d-flex flex-column justify-content-center flex-grow-1">
                                <a href="#" class="body-title-2 d-block text-truncate mb-1"
                                    title="{{ $product->name }}">{{ $product->name }}</a>
                                <div class="text-tiny">
                                    <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td scope="row" class="text-center align-middle position-relative">
                        @if ($product->attributeValues->count() > 0)
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <div>{{ $product->attributeValues->sum('quantity') }}</div>
                            </div>
                            <button class="btn btn-lg btn-outline-primary position-absolute" title="View All Quantities"
                                style="top: 4px; right: 4px;" onclick="event.stopPropagation();" data-bs-toggle="modal"
                                data-bs-target="#quantityModal{{ $product->id }}">
                                <i class="icon-eye"></i>
                            </button>
                        @else
                            {{ $product->quantity }}
                        @endif
                    </td>

                    <td scope="row" class="text-center align-middle position-relative">
                        @if ($product->attributeValues->count() > 0)
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <div>&#8369;{{ number_format($product->attributeValues->first()->price, 2) }}</div>
                            </div>
                            <button class="btn btn-lg btn-outline-primary position-absolute" title="View All Prices"
                                style="top: 4px; right: 4px;" onclick="event.stopPropagation();" data-bs-toggle="modal"
                                data-bs-target="#priceModal{{ $product->id }}">
                                <i class="icon-eye"></i>
                            </button>
                        @else
                            &#8369;{{ number_format($product->price, 2) }}
                        @endif
                    </td>

                    <td scope="row" class="align-middle">
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

                    <td class="text-center align-middle">
                        <div class="list-icon-function d-flex justify-content-center align-items-center">
                            <form action="{{ route('admin.product.archive', ['id' => $product->id]) }}" method="POST">
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

    {{-- SHARED MODALS FOR BOTH MOBILE AND DESKTOP --}}
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
        @endphp

        @if ($product->attributeValues->count() > 0)
            {{-- Quantity Modal --}}
            <div class="modal fade" id="quantityModal{{ $product->id }}" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" aria-labelledby="quantityModalLabel{{ $product->id }}"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="quantityModalLabel{{ $product->id }}">
                                <i class="icon-box me-2"></i>Quantity Breakdown
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="product-info-header mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $product->name }}</h6>
                                        <p class="text-muted mb-0">
                                            Total Stock: <strong
                                                class="text-primary fs-5">{{ $product->attributeValues->sum('quantity') }}
                                                units</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @foreach ($groupedAttributes as $attributeId => $variants)
                                <div class="attribute-section mb-4">
                                    <h3 class="text-primary fw-bold mb-3 d-flex align-items-center">
                                        <i class="icon-layers me-2"></i>
                                        {{ $uniqueAttributes[$attributeId]->name }}
                                    </h3>
                                    <div class="row g-2">
                                        @foreach ($variants as $variant)
                                            @php
                                                $vQty = $variant->quantity;

                                                if ($vQty == 0) {
                                                    $variantStockStatus = 'Out of Stock';
                                                    $variantBadgeClass = 'badge-danger';
                                                } elseif ($vQty <= $product->outofstock_quantity) {
                                                    $variantStockStatus = 'Low Stock';
                                                    $variantBadgeClass = 'badge-danger';
                                                } elseif ($vQty <= $product->reorder_quantity) {
                                                    $variantStockStatus = 'Reorder Level';
                                                    $variantBadgeClass = 'badge-warning';
                                                } else {
                                                    $variantStockStatus = 'In Stock';
                                                    $variantBadgeClass = 'badge-success';
                                                }
                                            @endphp
                                            <div class="col-md-6">
                                                <div
                                                    class="variant-card p-3 border rounded d-flex justify-content-between align-items-center bg-light">
                                                    <p class="fw-semibold">{{ $variant->value }}</p>
                                                    <div class="d-flex align-items-end gap-3">
                                                        <p class="text-tiny">
                                                            <span class="badge badge-primary">
                                                                {{ $variant->quantity }} units</span>
                                                        </p>
                                                        <div class="text-tiny">
                                                            <span
                                                                class="badge {{ $variantBadgeClass }}">{{ $variantStockStatus }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Price Modal --}}
            <div class="modal fade" id="priceModal{{ $product->id }}" data-bs-backdrop="static"
                data-bs-keyboard="false" tabindex="-1" aria-labelledby="priceModalLabel{{ $product->id }}"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="priceModalLabel{{ $product->id }}">
                                <i class="icon-dollar-sign me-2"></i>Price Breakdown
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="product-info-header mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $product->name }}</h6>
                                        <p class="text-muted mb-0">
                                            Price Range:
                                            <strong class="text-success fs-5">
                                                &#8369;{{ number_format($product->attributeValues->min('price'), 2) }}
                                                -
                                                &#8369;{{ number_format($product->attributeValues->max('price'), 2) }}
                                            </strong>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @foreach ($groupedAttributes as $attributeId => $variants)
                                <div class="attribute-section mb-4">
                                    <h3 class="text-success fw-bold mb-3 d-flex align-items-center">
                                        <i class="icon-layers me-2"></i>
                                        {{ $uniqueAttributes[$attributeId]->name }}
                                    </h3>
                                    <div class="row g-2">
                                        @foreach ($variants as $variant)
                                            <div class="col-md-6">
                                                <div
                                                    class="variant-card p-3 border rounded d-flex justify-content-between align-items-center bg-light">
                                                    <p class="fw-semibold">{{ $variant->value }}</p>
                                                    <p class="badge bg-success fs-6 px-3 py-2">
                                                        &#8369;{{ number_format($variant->price, 2) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@else
    <div class="mobile-cards d-block d-md-none">
        <div class="mobile-card">
            <div class="mobile-card-body">
                <div class="empty-state">
                    <div class="empty-icon text-center mb-2">
                        <i class="icon-box"></i>
                    </div>
                    <h5 class="text-center mb-2">No Products Found</h5>
                    <p class="text-center mb-3">Try a different search term or adjust your filters.</p>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('admin.product.add') }}" class="btn btn-primary">
                            <i class="icon-plus"></i> Add Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped table-bordered d-none d-md-table">
        <thead>
            <tr>
                <th class="text-center align-middle" scope="col" style="width: 30%">Name</th>
                <th class="text-center align-middle" scope="col" style="width: 15%">Quantity</th>
                <th class="text-center align-middle" scope="col" style="width: 20%">Price</th>
                <th class="text-center align-middle" scope="col" style="width: 20%">Category</th>
                <th class="text-center align-middle" scope="col" style="width: 15%">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr id="no-results-message">
                <td colspan="5" class="text-center p-3">
                    <div class="alert alert-info mb-0">
                        No products found. Try a different search term.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
@endif
