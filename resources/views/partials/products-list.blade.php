<div class="row g-3 g-md-4">
    @foreach ($products as $product)
        @php
            $totalQuantity = $product->attributeValues->isNotEmpty()
                ? $product->attributeValues->sum('quantity')
                : $product->quantity;
            $isOutOfStock = $totalQuantity <= 0;
        @endphp

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4 d-flex justify-content-center">
            <div class="product-item d-flex flex-column {{ $isOutOfStock ? 'out-of-stock' : '' }}">
                <div class="product-img position-relative flex-grow-1">
                    <div class="swiper-container js-swiper-slider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                @if ($isOutOfStock)
                                    <div class="text-decoration-none text-dark d-block">
                                        <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $product->image }}"
                                            alt="{{ $product->name }}" class="img-fluid d-block mx-auto product-image">
                                    </div>
                                @else
                                    <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}"
                                        class="text-decoration-none text-dark d-block" style="cursor: pointer;">
                                        <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $product->image }}"
                                            alt="{{ $product->name }}" class="img-fluid d-block mx-auto product-image">
                                    </a>
                                @endif
                            </div>
                            @if ($product->images)
                                @foreach (explode(',', $product->images) as $gimg)
                                    @if (trim($gimg))
                                        <div class="swiper-slide">
                                            @if ($isOutOfStock)
                                                <div class="text-decoration-none text-dark d-block">
                                                    <img loading="lazy"
                                                        src="{{ asset('uploads/products') }}/{{ trim($gimg) }}"
                                                        alt="{{ $product->name }}"
                                                        class="img-fluid d-block mx-auto product-image">
                                                </div>
                                            @else
                                                <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}"
                                                    class="text-decoration-none text-dark d-block"
                                                    style="cursor: pointer;">
                                                    <img loading="lazy"
                                                        src="{{ asset('uploads/products') }}/{{ trim($gimg) }}"
                                                        alt="{{ $product->name }}"
                                                        class="img-fluid d-block mx-auto product-image">
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>

                        <div class="position-absolute top-0 start-0 p-2" style="z-index: 10;">
                            @php
                                $isNew = $product->created_at->diffInDays(now()) <= 7;
                            @endphp

                            @if ($isNew)
                                <span class="badge bg-success text-white px-2 py-1 rounded-pill shadow-sm">
                                    <i class="fas fa-star me-1"></i>New
                                </span>
                            @endif

                            @if ($totalQuantity <= 5 && $totalQuantity > 0)
                                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill shadow-sm ms-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="product-info px-3 py-3 {{ !$isOutOfStock ? 'clickable' : '' }}"
                    @if (!$isOutOfStock) onclick="window.location.href='{{ route('shop.product.details', ['product_slug' => $product->slug]) }}'" @endif>
                    <h1 class="title-cards fw-normal text-capitalize mb-2 display-2">{{ $product->name }}</h1>

                    <p class="span-description fw-light mb-3">{{ $product->short_description }}</p>

                    <div class="avail d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <span class="availability fw-bold me-2">Stock:</span>
                            <span class="avail-tags fw-bold"
                                style="color: {{ $totalQuantity > 0 ? ($totalQuantity <= 5 ? '#f39c12' : '#27ae60') : '#e74c3c' }};">
                                {{ $totalQuantity > 0 ? $totalQuantity . ' available' : 'Out of Stock' }}
                            </span>
                        </div>

                        <div class="stock-icon">
                            @if ($totalQuantity > 0)
                                <i class="fas fa-check-circle text-success ms-2"></i>
                            @else
                                <i class="fas fa-times-circle text-danger ms-2"></i>
                            @endif
                        </div>
                    </div>

                    <div class="price-section text-center">
                        <h2 class="product-type text-primary pt-2 mb-0">
                            <span class="currency-symbol">₱</span>
                            <span class="price-value">
                                @if ($product->attributeValues->isNotEmpty())
                                    @php
                                        $prices = $product->attributeValues->pluck('price')->filter()->sort()->values();
                                        if ($prices->isNotEmpty()) {
                                            $minPrice = $prices->first();
                                            $maxPrice = $prices->last();
                                            if ($minPrice == $maxPrice) {
                                                echo number_format($minPrice, 2);
                                            } else {
                                                echo number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2);
                                            }
                                        } else {
                                            echo number_format($product->price, 2);
                                        }
                                    @endphp
                                @else
                                    {{ number_format($product->price, 2) }}
                                @endif
                            </span>
                        </h2>

                        <div class="mt-3">
                            @if ($isOutOfStock)
                                <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                    <i class="fas fa-ban me-1"></i>
                                    Out of Stock
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($products->isEmpty())
    <div class="col-12">
        <div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-search fa-4x text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">No products found</h3>
            <p class="text-muted mb-4">Try checking our other products.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Shop
            </a>
        </div>
    </div>
@endif

<style>
    .product-item {
        width: 100%;
        max-width: 360px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        position: relative;
    }

    .product-img {
        width: 100%;
    }

    .product-image {
        width: 100%;
        height: auto;
        object-fit: cover;
        aspect-ratio: 1 / 1;
        display: block;
    }

    .currency-symbol {
        font-size: clamp(1rem, 0.9rem + 0.3vw, 1.2rem);
        font-weight: 600;
        vertical-align: top;
    }

    .price-value {
        font-size: clamp(1.25rem, 1.1rem + 0.4vw, 1.6rem);
        font-weight: 700;
        letter-spacing: -0.025em;
        white-space: nowrap;
    }

    .quick-view-btn {
        transition: all 0.3s ease;
        border-radius: 8px;
        font-weight: 500;
        transform: translateY(10px);
    }

    .product-item:hover .quick-view-btn {
        transform: translateY(0);
    }

    .quick-view-btn:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        color: white;
        transform: translateY(-2px);
    }

    .stock-icon {
        font-size: 1.1rem;
    }

    .product-info {
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        background: #fff;
    }

    .title-cards {
        font-size: clamp(1rem, 0.9rem + 0.25vw, 1.2rem);
        line-height: 1.3;
    }

    .span-description {
        font-size: clamp(0.85rem, 0.8rem + 0.15vw, 0.95rem);
        color: var(--text-secondary);
    }

    .availability,
    .avail-tags {
        font-size: clamp(0.85rem, 0.8rem + 0.15vw, 0.95rem);
    }

    .price-section {
        margin-top: auto;
    }

    .out-of-stock {
        position: relative;
    }

    .out-of-stock::after {
        content: "";
        position: absolute;
        inset: 0;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 1;
    }

    .clickable {
        cursor: pointer;
    }

    .clickable:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .badge i {
        font-size: 0.7rem;
    }

    @media (max-width: 768px) {
        .product-info {
            min-height: 180px;
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .product-item {
            max-width: 100%;
        }

        .product-info {
            min-height: 160px;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.25em 0.5em;
        }
    }

    @media (min-width: 1200px) {
        .price-value {
            font-size: 1.7rem;
        }
    }
</style>
