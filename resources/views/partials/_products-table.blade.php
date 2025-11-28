@if ($products->count() > 0)
    {{-- MOBILE CARDS (visible on < md) --}}
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
                        <p>
                            <strong>Quantity:</strong>
                            {{ $mobileQuantity }}
                        </p>
                        <p>
                            <strong>Price:</strong>
                            &#8369;{{ number_format($mobilePrice, 2) }}
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
                @endphp

                <tr class="product-row" data-href="{{ route('admin.product.edit', ['id' => $product->id]) }}"
                    style="cursor: pointer;">
                    <td scope="row" class="pname">
                        <div class="d-flex align-items-center gap-2">
                            <div class="image"
                                style="width: 48px; height: 48px; overflow: hidden; border-radius: 8px;">
                                <img src="{{ asset('uploads/products/thumbnails/' . $product->image) }}"
                                    alt="{{ $product->name }}" class="img-fluid"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="name">
                                <a href="#" class="body-title-2 d-block text-truncate"
                                    title="{{ $product->name }}">{{ $product->name }}</a>
                                <div class="text-tiny mt-2">
                                    <span class="badge {{ $badgeClass }}">{{ $stockStatus }}</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Quantity column with detailed variant tooltip --}}
                    <td scope="row" class="variant-cell text-center align-middle">
                        @if ($product->attributeValues->count() > 0)
                            <span class="variant-value"
                                data-bs-content="Quantity: {{ $product->attributeValues->first()->quantity }}">
                                {{ $product->attributeValues->sum('quantity') }}
                            </span>
                            <div class="variant-tooltip">
                                @foreach ($groupedAttributes as $attributeId => $variants)
                                    <div class="variant-group">
                                        <div class="variant-attribute-name">
                                            {{ $uniqueAttributes[$attributeId]->name }}
                                        </div>
                                        @foreach ($variants as $variant)
                                            <div class="variant-row tooltip-highlight">
                                                <span class="variant-value">{{ $variant->value }}</span>
                                                <span class="variant-detail">{{ $variant->quantity }} units</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{ $product->quantity }}
                        @endif
                    </td>

                    {{-- Price column with detailed variant tooltip --}}
                    <td scope="row" class="variant-cell text-center align-middle">
                        @if ($product->attributeValues->count() > 0)
                            <span class="variant-value"
                                data-bs-content="Price: &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}">
                                &#8369;{{ number_format($product->attributeValues->first()->price, 2) }}
                            </span>
                            <div class="variant-tooltip">
                                @foreach ($groupedAttributes as $attributeId => $variants)
                                    <div class="variant-group">
                                        <div class="variant-attribute-name">
                                            {{ $uniqueAttributes[$attributeId]->name }}
                                        </div>
                                        @foreach ($variants as $variant)
                                            <div class="variant-row tooltip-highlight">
                                                <span class="variant-value">{{ $variant->value }}</span>
                                                <span class="variant-detail">
                                                    &#8369;{{ number_format($variant->price, 2) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
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
