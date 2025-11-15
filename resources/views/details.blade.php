@extends('layouts.app')

@section('content')

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        @if (session('incomplete_profile'))
            <script>
                Swal.fire({
                    title: 'Incomplete Profile',
                    text: "{{ session('message') }}",
                    icon: 'warning',
                    confirmButtonText: 'Complete Profile'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('user.profile') }}";
                    }
                });
            </script>
        @endif

        <div id="loading-overlay"
            class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
            style="background: rgba(255,255,255,0.8); z-index: 9999; display: none !important;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="mb-md-1 pb-md-3"></div>
        <section class="product-single container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="product-gallery">
                        <div class="gallery-wrapper">
                            <div class="thumbnails">
                                <div class="swiper-container thumbnail-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="thumbnail-img"
                                                src="{{ asset('uploads/products/thumbnails') }}/{{ $product->image }}"
                                                alt="{{ $product->name }}" height="204"
                                                onerror="this.src='{{ asset('images/no-image.jpg') }}'">
                                        </div>
                                        @foreach (explode(',', $product->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="thumbnail-img"
                                                    src="{{ asset('uploads/products/thumbnails') }}/{{ $gimg }}"
                                                    alt="{{ $product->name }}"
                                                    onerror="this.src='{{ asset('images/no-image.jpg') }}'">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="main-image">
                                <div class="swiper-container main-swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img loading="lazy" class="h-auto main-img"
                                                src="{{ asset('uploads/products') }}/{{ $product->image }}"
                                                alt="{{ $product->name }}">
                                            <a data-fancybox="gallery"
                                                href="{{ asset('uploads/products') }}/{{ $product->image }}"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                title="{{ $product->name }}" aria-label="View full image"></a>
                                        </div>
                                        @foreach (explode(',', $product->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy" class="h-auto main-img"
                                                    src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    alt="{{ $product->name }}">
                                                <a data-fancybox="gallery"
                                                    href="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ $product->name }}" aria-label="View full image"></a>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-button-prev" aria-label="Previous image"></div>
                                    <div class="swiper-button-next" aria-label="Next image"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="product-header">
                        <h1 class="product-single__name h2 mb-3">
                            {{ ucfirst(strtolower(strtok($product->name, ' '))) . substr($product->name, strlen(strtok($product->name, ' '))) }}
                        </h1>

                        <div class="product-single__price mb-3">
                            <span class="current-price text-success h2" id="current-price">&#8369;
                                @if ($product->attributeValues->isNotEmpty() && isset($product->attributeValues[0]->price))
                                    {{ number_format($product->attributeValues[0]->price, 2) }}
                                @else
                                    {{ number_format($product->price, 2) }}
                                @endif
                            </span>
                        </div>

                        <div class="stock-status mb-3">
                            @php
                                $quantityToCheck = $product->quantity;
                                $hasAttributes = $product->attributeValues->isNotEmpty();

                                if ($hasAttributes) {
                                    $initialVariantQuantity = $product->attributeValues[0]->quantity ?? 0;
                                    $quantityToCheck = $initialVariantQuantity;
                                }

                                $stockStatus = 'instock';
                                if ($quantityToCheck <= $product->outofstock_quantity) {
                                    $stockStatus = 'outofstock';
                                } elseif ($quantityToCheck <= $product->reorder_quantity) {
                                    $stockStatus = 'reorder';
                                }
                            @endphp

                            @if ($hasAttributes)
                                <span
                                    class="badge
                                    @if ($stockStatus == 'outofstock') bg-danger
                                    @elseif($stockStatus == 'reorder') bg-warning
                                    @else bg-success @endif"
                                    id="dynamic-stock-badge" data-reorder-qty="{{ $product->reorder_quantity }}"
                                    data-outofstock-qty="{{ $product->outofstock_quantity }}">
                                    <i
                                        class="fas
                                        @if ($stockStatus == 'outofstock') fa-times-circle
                                        @elseif($stockStatus == 'reorder') fa-exclamation-triangle
                                        @else fa-check-circle @endif"></i>
                                    @if ($stockStatus == 'outofstock')
                                        Out of Stock
                                    @elseif($stockStatus == 'reorder')
                                        Stock ({{ $initialVariantQuantity }} left)
                                    @else
                                        In Stock
                                    @endif
                                </span>
                            @else
                                @if ($stockStatus == 'outofstock')
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                @elseif($stockStatus == 'reorder')
                                    <span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Stock
                                        ({{ $product->quantity }} left)</span>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> In Stock</span>
                                @endif
                            @endif
                        </div>

                    </div>

                    <div class="variant-container mb-3 product-single__meta-info py-3">
                        <div class="d-flex flex-column">
                            @foreach ($uniqueAttributes as $attributeId => $attribute)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="product-attributes me-3 mx-2"
                                        style="max-width: 150px; word-wrap: break-word;">
                                        <strong>{{ $attribute->name }}:</strong>
                                    </div>
                                    <div class="product-attribute-values d-flex" style="flex-wrap: wrap;">
                                        @if (isset($groupedAttributes[$attributeId]))
                                            @foreach ($groupedAttributes[$attributeId] as $variant)
                                                <button type="button"
                                                    class="btn btn-outline-primary variant-button me-2 mb-2"
                                                    data-attribute-id="{{ $attributeId }}"
                                                    data-variant-id="{{ $variant->id }}"
                                                    data-variant-label="{{ $attribute->name }}: {{ $variant->value }}"
                                                    data-variant-value="{{ $variant->value }}"
                                                    data-variant-price="{{ $variant->price }}"
                                                    data-variant-quantity="{{ $variant->quantity }}"
                                                    data-variant-description="{{ $variant->description ?? '' }}"
                                                    aria-pressed="false"
                                                    aria-label="Select {{ $attribute->name }}: {{ $variant->value }}">
                                                    {{ $variant->value }}
                                                </button>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No values available</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="attribute[{{ $attributeId }}]"
                                        id="attribute-{{ $attributeId }}" value="">
                                </div>
                            @endforeach
                            <div id="error-message" class="mt-2 p-3 alert alert-danger" role="alert"
                                style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Please select a variant.
                            </div>
                        </div>
                    </div>

                    <!-- Selected Variant Summary (hidden by default) -->
                    <div id="variant-summary" class="card border-0 shadow-sm mb-3" style="display:none;">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold" id="variant-summary-label"></div>
                                <div class="small text-muted">
                                    <span id="variant-summary-price"></span>
                                    <span class="mx-2">•</span>
                                    <span id="variant-summary-stock"></span>
                                    <span class="mx-2" id="variant-summary-sku-wrap" style="display:none;">• SKU: <span
                                            id="variant-summary-sku"></span></span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="variant-details-btn"
                                data-bs-toggle="modal" data-bs-target="#variantDetailsModal">
                                View details
                            </button>
                        </div>
                    </div>

                    <!-- Variant Details Modal -->
                    <div class="modal fade" id="variantDetailsModal" tabindex="-1"
                        aria-labelledby="variantDetailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="variantDetailsModalLabel">Variant details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-5" id="variant-modal-image-wrap" style="display:none;">
                                            <img id="variant-modal-image" src="" alt=""
                                                class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-7">
                                            <div class="mb-2">
                                                <div class="fw-semibold" id="variant-modal-label"></div>
                                                <div class="small text-muted">ID: <span id="variant-modal-id"></span>
                                                </div>
                                            </div>
                                            <div class="border rounded p-2 mb-3">
                                                <div class="d-flex flex-wrap gap-3">
                                                    <div><span class="text-muted small d-block">Price</span><span
                                                            id="variant-modal-price" class="fw-semibold"></span></div>
                                                    <div><span class="text-muted small d-block">Stock</span><span
                                                            id="variant-modal-stock" class="fw-semibold"></span></div>
                                                    <div id="variant-modal-sku-wrap" style="display:none;"><span
                                                            class="text-muted small d-block">SKU</span><span
                                                            id="variant-modal-sku" class="fw-semibold"></span></div>
                                                </div>
                                            </div>
                                            <div id="variant-modal-description-wrap" style="display:none;">
                                                <div class="text-muted small mb-1">Description</div>
                                                <div id="variant-modal-description"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">

                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="product-single__meta-info mb-4">
                        <div class="meta-item mb-2">
                            <label class="fw-bold">Category:</label>
                            <span>
                                <a href="{{ route('shop.index', ['categories' => $product->category->id]) }}"
                                    class="text-primary text-decoration-underline">
                                    {{ $product->category->name }}
                                </a>
                            </span>
                        </div>
                    </div>

                    <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="product-single__addtocart">
                            <div class="quantity-selector-container d-flex align-items-center gap-3 w-100 mb-3">
                                <div class="quantity-selector d-flex align-items-center">
                                    <button type="button" class="btn p-1"
                                        onclick="this.parentNode.querySelector('input').stepDown()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1"
                                        class="border-0 text-center mx-1" style="width: 40px;" aria-label="Quantity">
                                    <button type="button" class="btn p-1"
                                        onclick="this.parentNode.querySelector('input').stepUp()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="meta-item flex-grow-1">
                                    <span style="font-size: 14px" id="available-quantity" class="text-muted">
                                        @if ($product->attributeValues->isNotEmpty() && isset($product->attributeValues[0]->quantity))
                                            {{ $product->attributeValues[0]->quantity > 0 ? $product->attributeValues[0]->quantity . ' pieces available' : 'Out of Stock' }}
                                        @else
                                            {{ $product->quantity > 0 ? $product->quantity . ' pieces available' : 'Out of Stock' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="id" value="{{ $product->id }}" />
                            <input type="hidden" name="name" value="{{ $product->name }}" />
                            <input type="hidden" name="price" id="selected-price" value="{{ $product->price }}" />
                            <input type="hidden" name="variant_id" id="selected-variant-id" value="" />
                            <button type="submit" class="btn btn-shop btn-addtocart w-100" id="add-to-cart-button">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Add to Cart
                            </button>
                        </div>
                    </form>

                    <div class="social-share">
                        <h6 class="mb-2">Share this product:</h6>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote=Check out this product: {{ urlencode($product->name) }}"
                                class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                                aria-label="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>

                            <a href="https://www.instagram.com/?url={{ urlencode(request()->url()) }}"
                                class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener"
                                aria-label="Share on Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>

                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text=Check out this product: {{ urlencode($product->name) }}&hashtags=Shop"
                                class="btn btn-outline-info btn-sm" target="_blank" rel="noopener"
                                aria-label="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="copy-link-btn"
                                aria-label="Copy link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="section-separator" />

            <!-- Improved Product Description Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="product-description-wrapper">
                        <div class="section-header text-center mb-4">
                            <h3 class="section-title">Product Description</h3>
                            <div class="section-divider"></div>
                        </div>
                        <div class="description-card">
                            <div class="description-content">
                                <div class="description-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="description-text">
                                    <p>{{ $product->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <hr class="section-separator" />

    <!-- Improved Related Products Section -->
    <section id="related-products" class="related-products-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">You May Also Like</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">Discover more products from our collection</p>
            </div>

            <div class="related-products-grid">
                <div class="row g-4 my-3 mx-auto owl-carousel owl-theme">
                    @foreach ($rproducts as $rproduct)
                        <div class="col product-item">
                            <div class="product-card h-100">
                                <div class="product-image-wrapper">
                                    <div class="swiper-container js-swiper-slider">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <a href="{{ route('shop.product.details', ['product_slug' => $rproduct->slug]) }}"
                                                    class="product-link">
                                                    <img loading="lazy"
                                                        src="{{ asset('uploads/products') }}/{{ $rproduct->image }}"
                                                        alt="{{ $rproduct->name }}" class="product-image"
                                                        onerror="this.src='{{ asset('images/no-image.jpg') }}'">
                                                </a>
                                            </div>
                                            @foreach (explode(',', $rproduct->images) as $gimg)
                                                <div class="swiper-slide">
                                                    <img loading="lazy"
                                                        src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                        alt="{{ $rproduct->name }}" class="product-image"
                                                        onerror="this.src='{{ asset('images/no-image.jpg') }}'">
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-pagination slideshow-pagination"></div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                    <div class="product-overlay">
                                        <span class="view-product">View Product</span>
                                    </div>
                                </div>

                                <div class="product-details"
                                    onclick="window.location.href='{{ route('shop.product.details', ['product_slug' => $rproduct->slug]) }}'">
                                    <h3 class="product-title">{{ Str::limit($rproduct->name, 50) }}</h3>

                                    <p class="product-description">
                                        {{ Str::limit($rproduct->short_description ?? 'No description available', 80) }}
                                    </p>

                                    <div class="product-meta">
                                        <div class="availability-info">
                                            <span class="availability-label">Available:</span>
                                            <span class="availability-count"
                                                data-stock="@if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->quantity)) {{ $rproduct->attributeValues[0]->quantity }}@else{{ $rproduct->quantity }} @endif">
                                                @if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->quantity))
                                                    {{ $rproduct->attributeValues[0]->quantity > 0 ? $rproduct->attributeValues[0]->quantity : 0 }}
                                                @else
                                                    {{ $rproduct->quantity > 0 ? $rproduct->quantity : 0 }}
                                                @endif
                                            </span>
                                        </div>

                                        <div class="product-price">
                                            <span class="price-symbol">₱</span>
                                            <span class="price-amount">
                                                @if ($rproduct->attributeValues->isNotEmpty() && isset($rproduct->attributeValues[0]->price))
                                                    {{ number_format($rproduct->attributeValues[0]->price, 2) }}
                                                @else
                                                    {{ number_format($rproduct->price, 2) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #006633;
            --primary-hover: #004d26;
            --secondary-color: #FFD700;
            --text-dark: #2c3e50;
            --text-muted: #6c757d;
            --border-light: #e9ecef;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.12);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        /* Original styles preserved */
        .quantity-selector {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px;
        }

        .quantity-selector input {
            -moz-appearance: textfield;
            border: none;
            outline: none;
            background: transparent;
        }

        .quantity-selector input::-webkit-outer-spin-button,
        .quantity-selector input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-selector button {
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            transition: all 0.2s;
        }

        .quantity-selector button:hover {
            color: #0d6efd;
            background: rgba(0, 0, 0, 0.05);
        }

        .thumbnail-img {
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .thumbnail-img.active {
            border-color: #007bff;
        }

        .variant-button {
            transition: all 0.2s;
        }

        .variant-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .social-share .btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-single__addtocart {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .quantity-selector-container {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .meta-item {
            flex-grow: 1;
            text-align: right;
            min-width: 120px;
        }

        .btn-addtocart {
            width: 100%;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-addtocart:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        @media (max-width: 768px) {
            .quantity-selector-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .meta-item {
                text-align: left;
                width: 100%;
            }
        }

        .stock-status .badge {
            font-size: 0.875rem;
        }

        /* ========== IMPROVED PRODUCT DESCRIPTION STYLES ========== */
        .product-description-wrapper {
            margin: 3rem 0;
        }

        .section-header {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            position: relative;
        }

        .section-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            margin: 0 auto 1rem;
            border-radius: 2px;
        }

        .description-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            overflow: hidden;
        }

        .description-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .description-content {
            padding: 2.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .description-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 102, 51, 0.3);
        }

        .description-text {
            flex: 1;
        }

        .description-text p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--text-dark);
            margin: 0;
            text-align: justify;
        }

        /* ========== IMPROVED RELATED PRODUCTS STYLES ========== */
        .related-products-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            position: relative;
        }

        .related-products-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-light), transparent);
        }

        .section-separator {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--border-light), transparent);
            margin: 3rem 0;
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin: 0;
            font-weight: 300;
        }

        .related-products-grid {
            position: relative;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 250px;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 102, 51, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .view-product {
            color: var(--white);
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--secondary-color);
            border-radius: 25px;
            background: rgba(255, 215, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .view-product:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        .product-details {
            padding: 1.5rem;
            height: calc(100% - 250px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            line-height: 1.4;
            text-transform: capitalize;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-description {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-light);
            margin-top: auto;
        }

        .availability-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .availability-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .availability-count {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            min-width: 30px;
            text-align: center;
            transition: var(--transition);
        }

        .availability-count[data-stock="0"] {
            background-color: #fee;
            color: #dc3545;
            border: 1px solid #fecaca;
        }

        .availability-count:not([data-stock="0"]) {
            background-color: #f0f9f4;
            color: var(--primary-color);
            border: 1px solid #d1fae5;
        }

        .availability-count[data-stock]:not([data-stock="0"]):not([data-stock="1"]):not([data-stock="2"]):not([data-stock="3"]):not([data-stock="4"]):not([data-stock="5"]):not([data-stock="6"]):not([data-stock="7"]):not([data-stock="8"]):not([data-stock="9"]):not([data-stock="10"]) {
            background-color: #f0f9f4;
            color: var(--primary-color);
        }

        .availability-count[data-stock="1"],
        .availability-count[data-stock="2"],
        .availability-count[data-stock="3"],
        .availability-count[data-stock="4"],
        .availability-count[data-stock="5"] {
            background-color: #fff8e1;
            color: #f57c00;
            border: 1px solid #ffecb3;
        }

        .product-price {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .price-symbol {
            font-size: 1rem;
        }

        .price-amount {
            font-size: 1.2rem;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 768px) {
            .section-title {
                font-size: 1.75rem;
            }

            .description-content {
                padding: 2rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .description-icon {
                align-self: center;
            }

            .description-text p {
                font-size: 1rem;
                text-align: left;
            }

            .product-image-wrapper {
                height: 200px;
            }

            .product-details {
                padding: 1.25rem;
                height: auto;
            }

            .product-title {
                font-size: 1rem;
            }

            .product-meta {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
            }

            .availability-info {
                align-self: stretch;
                justify-content: space-between;
            }

            .product-price {
                align-self: stretch;
                justify-content: center;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .related-products-section {
                padding: 2rem 0;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .section-subtitle {
                font-size: 1rem;
            }

            .description-content {
                padding: 1.5rem;
            }

            .description-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .product-image-wrapper {
                height: 180px;
            }

            .view-product {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }

        /* ========== SWIPER CUSTOMIZATION FOR RELATED PRODUCTS ========== */
        .related-products-section .swiper-button-next,
        .related-products-section .swiper-button-prev {
            color: var(--primary-color);
            background: var(--white);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-top: -20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .related-products-section .swiper-button-next:hover,
        .related-products-section .swiper-button-prev:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: scale(1.1);
        }

        .related-products-section .swiper-button-next::after,
        .related-products-section .swiper-button-prev::after {
            font-size: 16px;
            font-weight: 600;
        }

        .related-products-section .swiper-pagination-bullet {
            background: var(--primary-color);
            opacity: 0.3;
            transition: var(--transition);
        }

        .related-products-section .swiper-pagination-bullet-active {
            opacity: 1;
            background: var(--secondary-color);
            transform: scale(1.2);
        }

        /* ========== LOADING AND HOVER EFFECTS ========== */
        .product-card {
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
            z-index: 1;
            pointer-events: none;
        }

        .product-card:hover::before {
            left: 100%;
        }

        /* ========== ACCESSIBILITY IMPROVEMENTS ========== */
        .product-card:focus-within {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .product-link:focus {
            outline: none;
        }

        .view-product {
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .product-card:hover .view-product {
            transform: translateY(0);
        }

        /* ========== ANIMATION DELAYS FOR STAGGERED EFFECT ========== */
        .product-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .product-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .product-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .product-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainSwiper = new Swiper('.main-swiper', {
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                loop: true,
                keyboard: {
                    enabled: true,
                },
                a11y: {
                    prevSlideMessage: 'Previous image',
                    nextSlideMessage: 'Next image',
                },
                on: {
                    slideChange: function() {
                        const activeIndex = this.realIndex;
                        document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                            thumbnail.classList.toggle('active', index === activeIndex);
                        });
                    }
                }
            });

            const thumbnailSwiper = new Swiper('.thumbnail-swiper', {
                direction: 'vertical',
                slidesPerView: 'auto',
                spaceBetween: 10,
                freeMode: true,
            });

            document.querySelectorAll('.thumbnail-img').forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', function() {
                    mainSwiper.slideToLoop(index);
                    document.querySelectorAll('.thumbnail-img').forEach((thumb) => thumb.classList
                        .remove('active'));
                    this.classList.add('active');
                });

                thumbnail.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });

                thumbnail.setAttribute('tabindex', '0');
                thumbnail.setAttribute('role', 'button');
            });

            document.querySelector('.thumbnail-img').classList.add('active');

            const qtyInputs = document.querySelectorAll('.qty-control__number');
            const decreaseBtns = document.querySelectorAll('.qty-control__reduce');
            const increaseBtns = document.querySelectorAll('.qty-control__increase');
            const availableQuantitySpan = document.getElementById('available-quantity');
            let maxAvailableQty = parseInt(availableQuantitySpan.textContent) || 0;

            decreaseBtns.forEach((decreaseBtn, index) => {
                decreaseBtn.addEventListener('click', function() {
                    const qtyInput = qtyInputs[index];
                    if (qtyInput.value > 1) {
                        qtyInput.value--;
                    } else {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You are about to remove this item from your cart.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, remove it!',
                            cancelButtonText: 'No, keep it'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const form = document.getElementById(
                                    `remove-item-form-${index}`);
                                form.submit();
                            }
                        });
                    }
                });

                decreaseBtn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            increaseBtns.forEach((increaseBtn, index) => {
                increaseBtn.addEventListener('click', function() {
                    const qtyInput = qtyInputs[index];
                    let currentQty = parseInt(qtyInput.value);
                    if (currentQty < maxAvailableQty) {
                        qtyInput.value++;
                    } else {
                        Swal.fire({
                            title: 'Maximum quantity reached!',
                            text: `Only ${maxAvailableQty} items are available for this product.`,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                    }
                });

                increaseBtn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            const addToCartButton = document.getElementById('add-to-cart-button');
            const errorMessage = document.getElementById('error-message');
            const selectedVariantIdInput = document.getElementById('selected-variant-id');
            const variantButtons = document.querySelectorAll('.variant-button');
            const hasVariants = variantButtons.length > 0;
            let activeButton = null;

            if (!addToCartButton) return;

            const variantData = {};
            variantButtons.forEach(button => {
                variantData[button.dataset.variantId] = {
                    quantity: parseInt(button.dataset.variantQuantity) || 0,
                    price: parseFloat(button.dataset.variantPrice) || 0
                };
            });

            addToCartButton.addEventListener('click', function(event) {
                event.preventDefault();

                if (hasVariants && !selectedVariantIdInput.value) {
                    errorMessage.style.display = 'block';
                    errorMessage.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                } else {
                    errorMessage.style.display = 'none';
                }

                const formData = new FormData(document.getElementById('add-to-cart-form'));

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('cart.add') }}', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'));

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 401) {
                            window.location.href = "{{ route('login') }}";
                        } else if (xhr.status === 403) {
                            Swal.fire({
                                title: 'Error!',
                                text: JSON.parse(xhr.responseText).message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else if (xhr.status === 200) {
                            Swal.fire({
                                title: 'Added to Cart!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "{{ route('cart.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'There was an issue adding the product to your cart.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                };

                xhr.send(formData);
            });

            if (hasVariants) {
                variantButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        if (activeButton) {
                            activeButton.classList.remove('btn-primary');
                            activeButton.classList.add('btn-outline-primary');
                        }

                        const variantId = this.dataset.variantId;
                        selectedVariantIdInput.value = variantId;
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-primary');
                        activeButton = this;

                        const variantPrice = parseFloat(this.dataset.variantPrice);
                        document.getElementById('current-price').innerText = '₱ ' + variantPrice
                            .toFixed(2);
                        document.getElementById('selected-price').value = variantPrice;

                        if (availableQuantitySpan && variantData[variantId]) {
                            maxAvailableQty = variantData[variantId].quantity;
                            availableQuantitySpan.textContent = maxAvailableQty > 0 ?
                                `${maxAvailableQty} pieces available` :
                                'Out of Stock';
                            addToCartButton.disabled = maxAvailableQty <= 0;
                        }

                        errorMessage.style.display = 'none';
                    });
                });
            }

            const copyLinkBtn = document.getElementById('copy-link-btn');
            if (copyLinkBtn) {
                copyLinkBtn.addEventListener('click', function() {
                    navigator.clipboard.writeText(window.location.href).then(function() {
                        const originalHTML = copyLinkBtn.innerHTML;
                        copyLinkBtn.innerHTML = '<i class="fas fa-check"></i>';
                        copyLinkBtn.classList.add('btn-success');
                        copyLinkBtn.classList.remove('btn-outline-secondary');

                        setTimeout(() => {
                            copyLinkBtn.innerHTML = originalHTML;
                            copyLinkBtn.classList.remove('btn-success');
                            copyLinkBtn.classList.add('btn-outline-secondary');
                        }, 2000);

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            title: 'Link copied to clipboard!',
                            icon: 'success'
                        });
                    }).catch(function(err) {
                        console.error('Could not copy text: ', err);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Could not copy link to clipboard.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                });
            }

            qtyInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const value = parseInt(this.value);
                    const max = parseInt(this.max) || maxAvailableQty;
                    const min = parseInt(this.min) || 1;

                    if (value > max) {
                        this.value = max;
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            title: `Maximum quantity is ${max}`,
                            icon: 'warning'
                        });
                    } else if (value < min) {
                        this.value = min;
                    }
                });

                input.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e
                        .key !== 'Tab') {
                        e.preventDefault();
                    }
                });
            });

            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    if (this.src !== '{{ asset('images/no-image.jpg') }}') {
                        this.src = '{{ asset('images/no-image.jpg') }}';
                        this.alt = 'Image not available';
                    }
                });
            });

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <script>
        (function() {
            const currency = '₱'; // Change if needed or pull from server
            const formatMoney = (num) => {
                if (num === null || num === undefined || num === '') return '';
                const n = Number(num);
                if (Number.isNaN(n)) return String(num);
                return currency + new Intl.NumberFormat('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(n);
            };

            let lastSelectedBtn = null;

            const summaryCard = document.getElementById('variant-summary');
            const sLabel = document.getElementById('variant-summary-label');
            const sPrice = document.getElementById('variant-summary-price');
            const sStock = document.getElementById('variant-summary-stock');
            const sSkuWrap = document.getElementById('variant-summary-sku-wrap');
            const sSku = document.getElementById('variant-summary-sku');

            const mLabel = document.getElementById('variant-modal-label');
            const mId = document.getElementById('variant-modal-id');
            const mPrice = document.getElementById('variant-modal-price');
            const mStock = document.getElementById('variant-modal-stock');
            const mSkuWrap = document.getElementById('variant-modal-sku-wrap');
            const mSku = document.getElementById('variant-modal-sku');
            const mDescWrap = document.getElementById('variant-modal-description-wrap');
            const mDesc = document.getElementById('variant-modal-description');
            const mImgWrap = document.getElementById('variant-modal-image-wrap');
            const mImg = document.getElementById('variant-modal-image');

            // Handle selection of a variant
            document.querySelectorAll('.variant-button').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Visual toggle within the same attribute group
                    const attributeId = this.dataset.attributeId;
                    document.querySelectorAll('.variant-button[data-attribute-id="' + attributeId +
                        '"]').forEach(function(sib) {
                        sib.classList.remove('active');
                        sib.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-pressed', 'true');

                    // Update the hidden input for this attribute
                    const hidden = document.getElementById('attribute-' + attributeId);
                    if (hidden) hidden.value = this.dataset.variantId || '';

                    // Store last selected (for modal "View details" button)
                    lastSelectedBtn = this;

                    // Update summary
                    const label = this.dataset.variantLabel || (this.dataset.variantValue ||
                        'Selected variant');
                    const price = this.dataset.variantPrice;
                    const qty = this.dataset.variantQuantity;
                    const sku = (this.dataset.variantSku || '').trim();

                    sLabel.textContent = label;
                    sPrice.textContent = formatMoney(price);
                    sStock.textContent = (qty !== undefined && qty !== null && qty !== '') ? (Number(
                        qty) > 0 ? `${qty} in stock` : 'Out of stock') : '';
                    if (sku) {
                        sSku.textContent = sku;
                        sSkuWrap.style.display = '';
                    } else {
                        sSkuWrap.style.display = 'none';
                    }
                    summaryCard.style.display = '';

                    // Hide any prior error message if you use it
                    const err = document.getElementById('error-message');
                    if (err) err.style.display = 'none';
                });
            });

            // Populate modal when it opens
            const detailsModalEl = document.getElementById('variantDetailsModal');
            detailsModalEl.addEventListener('show.bs.modal', function() {
                if (!lastSelectedBtn) return;

                const label = lastSelectedBtn.dataset.variantLabel || lastSelectedBtn.dataset.variantValue ||
                    'Variant';
                const id = lastSelectedBtn.dataset.variantId || '';
                const price = lastSelectedBtn.dataset.variantPrice;
                const qty = lastSelectedBtn.dataset.variantQuantity;
                const sku = (lastSelectedBtn.dataset.variantSku || '').trim();
                const desc = (lastSelectedBtn.dataset.variantDescription || '').trim();
                const img = (lastSelectedBtn.dataset.variantImage || '').trim();

                mLabel.textContent = label;
                mId.textContent = id;
                mPrice.textContent = formatMoney(price);
                mStock.textContent = (qty !== undefined && qty !== null && qty !== '') ? (Number(qty) > 0 ?
                    `${qty} available` : 'Out of stock') : '';
                if (sku) {
                    mSku.textContent = sku;
                    mSkuWrap.style.display = '';
                } else {
                    mSkuWrap.style.display = 'none';
                }
                if (desc) {
                    mDesc.textContent = desc;
                    mDescWrap.style.display = '';
                } else {
                    mDescWrap.style.display = 'none';
                }
                if (img) {
                    mImg.src = img;
                    mImg.alt = label;
                    mImgWrap.style.display = '';
                } else {
                    mImgWrap.style.display = 'none';
                    mImg.src = '';
                    mImg.alt = '';
                }
                // Title
                document.getElementById('variantDetailsModalLabel').textContent = label;
            });

            // (Optional) If you want to block form submits unless every attribute has a selection:
            // document.querySelector('form').addEventListener('submit', function(e) {
            //   const missing = Array.from(document.querySelectorAll('input[id^="attribute-"]')).some(i => !i.value);
            //   if (missing) {
            //     e.preventDefault();
            //     const err = document.getElementById('error-message');
            //     if (err) err.style.display = '';
            //     window.scrollTo({ top: err.offsetTop - 120, behavior: 'smooth' });
            //   }
            // });
        })();
    </script>


    @if ($hasAttributes)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const stockBadge = document.getElementById('dynamic-stock-badge');
                const reorderQty = parseInt(stockBadge.dataset.reorderQty);
                const outofstockQty = parseInt(stockBadge.dataset.outofstockQty);
                const variantButtons = document.querySelectorAll('.variant-button');
                const addToCartButton = document.getElementById('add-to-cart-button');

                function updateStockStatus(quantity) {
                    let badgeClass, icon, text;

                    if (quantity <= outofstockQty) {
                        badgeClass = 'bg-danger';
                        icon = 'fa-times-circle';
                        text = 'Out of Stock';
                        if (addToCartButton) {
                            addToCartButton.disabled = true;
                            addToCartButton.innerHTML = '<i class="fas fa-ban me-2"></i> ADD TO CART';
                        }
                    } else if (quantity <= reorderQty) {
                        badgeClass = 'bg-warning';
                        icon = 'fa-exclamation-triangle';
                        // text = `Stock (${quantity} left)`;
                        text = `Stock`;
                        if (addToCartButton) {
                            addToCartButton.disabled = false;
                            addToCartButton.innerHTML = '<i class="fas fa-shopping-cart me-2"></i> Add to Cart';
                        }
                    } else {
                        badgeClass = 'bg-success';
                        icon = 'fa-check-circle';
                        text = 'In Stock';
                        if (addToCartButton) {
                            addToCartButton.disabled = false;
                            addToCartButton.innerHTML = '<i class="fas fa-shopping-cart me-2"></i> Add to Cart';
                        }
                    }

                    stockBadge.className = `badge ${badgeClass}`;
                    stockBadge.innerHTML = `<i class="fas ${icon}"></i> ${text}`;

                    const availableQtySpan = document.getElementById('available-quantity');
                    if (availableQtySpan) {
                        availableQtySpan.textContent = quantity > 0 ?
                            `${quantity} pieces available` : '';
                    }
                }

                variantButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const quantity = parseInt(this.dataset.variantQuantity);
                        updateStockStatus(quantity);
                    });

                    button.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            const quantity = parseInt(this.dataset.variantQuantity);
                            updateStockStatus(quantity);
                        }
                    });
                });

                if (variantButtons.length > 0) {
                    const initialQuantity = parseInt(variantButtons[0].dataset.variantQuantity);
                    updateStockStatus(initialQuantity);
                }
            });
        </script>
    @endif
@endpush
