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
            $product->attributeValues->count() > 0 ? $product->attributeValues->sum('quantity') : $product->quantity;

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
                <img src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}" alt="{{ $product->name }}"
                    class="image">
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
                                    <span class="variant-detail">&#8369;{{ number_format($variant->price, 2) }}</span>
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
