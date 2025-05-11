@extends('layouts.app')
@section('content')
<style>
 /* Global Styles */
:root {
    --primary-color: #3a6ea5;
    --secondary-color: #ff6b6b;
    --accent-color: #f7c04a;
    --text-color: #333333;
    --light-bg: #f8f9fa;
    --dark-bg: #343a40;
    --transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, color 0.3s ease;
}

/* Typography Improvements */
body {
    font-family: 'Poppins', sans-serif;
    color: var(--text-color);
}

h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
}

/* Hero Section Styles */
.hero-carousel {
    margin-top: 0;
    position: relative;
    height: 85vh;
    overflow: hidden;
}

.hero-carousel .carousel-item {
    height: 85vh;
    padding: 0;
}

.carousel-image {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    will-change: transform;
    transform: translateZ(0);
}

.carousel-overlay {
    background: linear-gradient(to right, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.2) 100%);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.carousel-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    text-align: left;
    align-items: flex-start;
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: center;
}

.carousel-content .tagline {
    font-size: 1.2rem;
    font-weight: 500;
    color: var(--accent-color);
    margin-bottom: 1rem;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.carousel-content .subtitle {
    font-size: 1.8rem;
    color: #ffffff;
    margin-bottom: 1rem;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.8s ease 0.2s, transform 0.8s ease 0.2s;
}

.carousel-content .title {
    font-size: 3.5rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #ffffff;
    margin-bottom: 2rem;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.8s ease 0.4s, transform 0.8s ease 0.4s;
}

.btn-shop-now {
    background-color: var(--accent-color);
    color: var(--dark-bg);
    font-weight: 600;
    text-transform: uppercase;
    padding: 12px 30px;
    border-radius: 30px;
    transition: var(--transition);
    border: none;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.8s ease 0.6s, transform 0.8s ease 0.6s, background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

.btn-shop-now:hover {
    background-color: #ffffff;
    color: var(--dark-bg);
    transform: translateY(17px); /* Account for the initial 20px transform */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

/* Animation classes for JavaScript to target */
.fadeInUp {
    opacity: 1 !important;
    transform: translateY(0) !important;
}

/* Category Section */
#categories {
    padding: 80px 0;
    background-color: var(--light-bg);
}

.section-title {
    position: relative;
    margin-bottom: 3rem;
    font-size: 2.2rem;
    text-align: center;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background-color: var(--secondary-color);
    border-radius: 2px;
}

.category-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    cursor: pointer;
    margin-bottom: 30px;
    will-change: transform;
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.category-card img {
    width: 100%;
    height: 250px; /* Fixed height for consistency */
    object-fit: cover;
    transition: var(--transition);
    will-change: transform;
    aspect-ratio: 16/9;
}

.category-card:hover img {
    transform: scale(1.1);
}

.category-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 20px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
    color: #ffffff;
}

.category-content h3 {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.category-content p {
    font-size: 1rem;
    opacity: 0.8;
}

/* Featured Products Section */
#featured-products {
    background-color: var(--light-bg);
    position: relative;
    overflow: hidden;
    padding: 80px 0;
}

#featured-products:before {
    content: '';
    position: absolute;
    top: -50px;
    left: 0;
    width: 100%;
    height: 100px;
    background: linear-gradient(to bottom right, transparent 49%, var(--light-bg) 50%);
}

.product-card {
    background-color: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
    will-change: transform;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
    padding-top: 100%; /* 1:1 Aspect Ratio */
    aspect-ratio: 1/1;
}

.product-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: var(--transition);
    will-change: transform;
}

.product-card:hover .swiper-slide img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background-color: var(--secondary-color);
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 3;
    box-shadow: 0 3px 8px rgba(255, 107, 107, 0.3);
}

.product-actions {
    position: absolute;
    right: 15px;
    top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 3;
    opacity: 0;
    transform: translateX(20px);
    transition: var(--transition);
}

.product-card:hover .product-actions {
    opacity: 1;
    transform: translateX(0);
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    color: var(--dark-bg);
}

.action-btn:hover {
    background-color: var(--primary-color);
    color: #ffffff;
    transform: scale(1.1);
}

.quick-add {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 15px;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    opacity: 0;
    transform: translateY(100%);
    transition: var(--transition);
    z-index: 3;
}

.product-card:hover .quick-add {
    opacity: 1;
    transform: translateY(0);
}

.btn-add-to-cart {
    width: 100%;
    padding: 8px 15px;
    border: none;
    border-radius: 30px;
    background-color: var(--accent-color);
    color: var(--dark-bg);
    font-weight: 600;
    transition: var(--transition);
    text-transform: uppercase;
    font-size: 0.9rem;
}

.btn-add-to-cart:hover {
    background-color: var(--primary-color);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(58, 110, 165, 0.3);
}

.btn-add-to-cart.adding {
    background-color: var(--primary-color);
    color: #ffffff;
    pointer-events: none;
}

.product-info {
    padding: 20px;
}

.product-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
    transition: var(--transition);
    line-height: 1.4;
}

.product-title a {
    color: var(--text-color);
    text-decoration: none;
    transition: var(--transition);
}

.product-card:hover .product-title a {
    color: var(--primary-color);
}

.product-description {
    color: #777;
    font-size: 0.9rem;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 40px;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    border-top: 1px dashed rgba(0,0,0,0.1);
    padding-top: 15px;
}

.product-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
}

.availability {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.availability .status {
    font-weight: 600;
}

.availability .count {
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.count.in-stock {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.count.low-stock {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.count.out-of-stock {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

/* Custom Swiper Pagination */
.swiper-pagination {
    bottom: 10px;
}

.swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    background-color: rgba(255, 255, 255, 0.7);
    opacity: 1;
    transition: var(--transition);
}

.swiper-pagination-bullet-active {
    width: 20px;
    border-radius: 5px;
    background-color: var(--accent-color);
}

/* View All Button */
.btn-explore {
    background-color: var(--primary-color);
    color: #ffffff;
    padding: 12px 30px;
    border-radius: 30px;
    font-weight: 600;
    text-transform: uppercase;
    transition: var(--transition);
    border: none;
    box-shadow: 0 5px 15px rgba(58, 110, 165, 0.2);
}

.btn-explore:hover {
    background-color: var(--secondary-color);
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

/* Product hover overlay */
.product-image::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 2;
}

.product-card:hover .product-image::after {
    opacity: 1;
}

/* Carousel Indicators */
.carousel-indicators {
    bottom: 30px;
    z-index: 5;
}

.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #ffffff;
    opacity: 0.5;
    margin: 0 6px;
    transition: var(--transition);
}

.carousel-indicators .active {
    opacity: 1;
    width: 30px;
    border-radius: 10px;
    background-color: var(--accent-color);
}

/* Newsletter Section */
.newsletter {
    padding: 80px 0;
    background-color: var(--primary-color);
    color: #ffffff;
}

.newsletter-container {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.newsletter h3 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.newsletter p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.newsletter-form {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    border-radius: 30px 0 0 30px;
    font-size: 1rem;
}

.newsletter-btn {
    background-color: var(--accent-color);
    color: var(--dark-bg);
    border: none;
    border-radius: 0 30px 30px 0;
    padding: 0 25px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.newsletter-btn:hover {
    background-color: var(--secondary-color);
    color: #ffffff;
}

/* About Us Section */
.about-us {
    padding: 80px 0;
    background-color: #ffffff;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 15px;
    height: 500px;
}

.grid-item {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

.grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.grid-item:hover img {
    transform: scale(1.05);
}

.grid-item:nth-child(1) {
    grid-column: 1;
    grid-row: 1 / span 2;
}

.grid-item:nth-child(2) {
    grid-column: 2;
    grid-row: 1;
}

.grid-item:nth-child(3) {
    grid-column: 2;
    grid-row: 2;
}

.about-content {
    padding: 20px;
}

.about-content h2 {
    font-size: 2.2rem;
    margin-bottom: 20px;
    position: relative;
}

.about-content h2:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 4px;
    background-color: var(--secondary-color);
    border-radius: 2px;
}

.about-content p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 20px;
    line-height: 1.8;
}

/* Sticky Header */
header.sticky {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-out;
}

/* Scroll-to-top button */
.scroll-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.scroll-top.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Cart counter animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.cart-counter.pulse {
    animation: pulse 0.5s ease-in-out;
}

/* Transition stopper during resize to prevent jank */
.resize-animation-stopper * {
    transition: none !important;
}

/* Improve focus states for accessibility */
a:focus,
button:focus,
input:focus {
    outline: 2px solid var(--accent-color);
    outline-offset: 2px;
}

/* Dark mode support */
.dark-mode {
    --primary-color: #4a8ec7;
    --secondary-color: #ff8a8a;
    --accent-color: #ffd066;
    --text-color: #e1e1e1;
    --light-bg: #222528;
    --dark-bg: #1a1d21;
}

.dark-mode body {
    background-color: var(--dark-bg);
    color: var(--text-color);
}

.dark-mode .product-card,
.dark-mode .newsletter-input {
    background-color: #2a2d31;
    border-color: rgba(255, 255, 255, 0.1);
}

.dark-mode #categories,
.dark-mode #featured-products {
    background-color: var(--dark-bg);
}

.dark-mode .product-title a {
    color: #e1e1e1;
}

.dark-mode header.sticky {
    background-color: rgba(26, 29, 33, 0.95);
}

/* Print styles */
@media print {
    .newsletter, 
    .carousel-control-prev,
    .carousel-control-next,
    .product-actions,
    .quick-add {
        display: none !important;
    }
    
    .product-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .hero-carousel,
    .carousel-item {
        height: 70vh;
    }
    
    .carousel-content .title {
        font-size: 2.5rem;
    }
    
    .about-grid {
        height: 400px;
    }
    
    .product-title {
        font-size: 1.1rem;
    }
    
    .product-price {
        font-size: 1.2rem;
    }
}

@media (max-width: 768px) {
    .hero-carousel,
    .carousel-item {
        height: 60vh;
    }
    
    .carousel-content {
        text-align: center;
        align-items: center;
    }
    
    .carousel-content .tagline {
        font-size: 1rem;
    }
    
    .carousel-content .subtitle {
        font-size: 1.4rem;
    }
    
    .carousel-content .title {
        font-size: 2rem;
    }
    
    .about-grid {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(3, 1fr);
        height: auto;
    }
    
    .grid-item:nth-child(1) {
        grid-column: 1;
        grid-row: 1;
    }
    
    .grid-item:nth-child(2) {
        grid-column: 1;
        grid-row: 2;
    }
    
    .grid-item:nth-child(3) {
        grid-column: 1;
        grid-row: 3;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-input {
        border-radius: 30px;
        margin-bottom: 10px;
    }
    
    .newsletter-btn {
        border-radius: 30px;
        padding: 15px 25px;
    }
    
    .product-actions {
        opacity: 1;
        transform: translateX(0);
    }
    
    .quick-add {
        opacity: 1;
        transform: translateY(0);
        background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0.1) 80%, transparent);
    }
}

/* Container Queries for component-level responsiveness */
@container (max-width: 400px) {
    .product-description {
        -webkit-line-clamp: 1;
        height: 20px;
    }
    
    .product-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>
<!-- Hero Carousel Section -->
@if($slides->isNotEmpty())
<header id="hero-carousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
    <div class="carousel-indicators">
        @foreach ($slides as $key => $slide)
            <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="{{ $key }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $key + 1 }}"></button>
        @endforeach
    </div>
    
    <div class="carousel-inner h-100">
        @foreach ($slides as $slide)
        <div class="carousel-item h-100 @if ($loop->first) active @endif">
            <img src="{{ asset('uploads/slides/' . $slide->image) }}" alt="{{ $slide->title }}" class="carousel-image" loading="lazy">
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                {{-- <h3 class="tagline">{{ $slide->tagline }}</h3> --}}
                <h2 class="subtitle">{{ $slide->subtitle }}</h2>
                <h1 class="title">{{ $slide->title }}</h1>
                <a href="{{ $slide->link }}" class="btn btn-shop-now">{{ $slide->tagline }}</a>
            </div>
        </div>
        @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#hero-carousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#hero-carousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</header>
@endif
<!-- Categories Section -->
<section id="categories">
    <div class="container">
        <h2 class="section-title">Shop by Categories</h2>
        
        <div class="row g-4">
            @foreach($categories as $category)
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ route('shop.index', ['categories' => $category->id]) }}" class="text-decoration-none">
                        <div class="category-card">
                            <img src="{{ asset('uploads/categories/' . $category->image) }}" alt="{{ $category->name }}" class="category-image" loading="lazy">
                            <div class="category-content">
                                <h3>{{ $category->name }}</h3>
                                <p>{{ $category->description }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Featured Products Section -->
@if ($fproducts->isNotEmpty())
<section id="featured-products" class="py-5">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        
        <div class="row g-4" id="product-container">
            @foreach ($fproducts as $fproduct)
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4" data-product-id="{{ $fproduct->id }}">
                    <div class="product-card h-100">
                          <a href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">
                        <div class="product-image">
                            <!-- Product Badge -->
                            @if($fproduct->sale_price > 0)
                                <span class="product-badge">SALE</span>
                            @endif
                            
                            <!-- Swiper Slider for Product Images -->
                            <div class="swiper-container product-slider">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                      
                                            <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $fproduct->image }}"
                                                alt="{{ $fproduct->name }}" class="img-fluid">
                                       
                                    </div>
                                    
                                </div>
                                <!-- Pagination -->
                                <div class="swiper-pagination"></div>
                            </div>
                            
        
                        </div>
                         </a>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">
                                    {{ $fproduct->name }}
                                </a>
                            </h3>
                            
                            <p class="product-description">
                                {{ \Illuminate\Support\Str::limit($fproduct->short_description, 70) ?: 'No description available' }}
                            </p>
                            
                            <div class="product-meta">
                                <div class="product-price">
                                    &#8369;{{ number_format($fproduct->attributeValues->isNotEmpty() && isset($fproduct->attributeValues[0]->price) ? $fproduct->attributeValues[0]->price : $fproduct->price, 2) }}
                                </div>
                                
                                <div class="availability">
                                    <span class="status">Stock:</span>
                                    @php
                                        $quantity = $fproduct->attributeValues->isNotEmpty() && isset($fproduct->attributeValues[0]->quantity) 
                                            ? $fproduct->attributeValues[0]->quantity 
                                            : $fproduct->quantity;
                                        $stockClass = $quantity > 5 ? 'in-stock' : ($quantity > 0 ? 'low-stock' : 'out-of-stock');
                                    @endphp
                                    <span class="count {{ $stockClass }}">
                                        {{ $quantity > 0 ? $quantity : 'Out of stock' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach 
        </div >
        
        <div class="text-center mt-5">
            <a href="{{ route('shop.index') }}" class="btn btn-explore">View All Products</a>
        </div>
    </div>
</section>
@endif
<!-- About Us Section -->
<section class="about-us">
    <div class="container">
        <h2 class="section-title">About Us</h2>
        
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="about-grid">
                    <div class="grid-item">
                        <img src="{{ asset('./images/OVPEBA.jpg') }}" alt="About Image 1" loading="lazy">
                    </div>
                    <div class="grid-item">
                        <img src="{{ asset('./images/ovp.jpg') }}" alt="About Image 2" loading="lazy">
                    </div>
                    <div class="grid-item">
                        <img src="{{ asset('./images/ovp.jpg') }}" alt="About Image 3" loading="lazy">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2>We Help You Make Your Life Easier</h2>
                    <p>The Business and Resource Generation (BaRG) Unit has been a cornerstone of our campus community for years, offering a wide range of resources, items, and rentals. From uniforms and books to souvenirs and essential supplies, BaRG Unit is your go-to marketplace for all campus needs.</p>
                    <p>We pride ourselves on providing high-quality products and exceptional service to enhance your campus experience.</p>
                    <a href="{{ route('about.index') }}" class="btn btn-explore">Explore More</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const animateElements = (elements, className, delay = 0) => {
            if (!elements || elements.length === 0) return;
            let index = 0;
            const animate = () => {
                if (index >= elements.length) return;
                elements[index].classList.add(className);
                index++;
                setTimeout(() => {
                    requestAnimationFrame(animate);
                }, delay);
            };
            animate();
        };

        const heroCarousel = document.getElementById('hero-carousel');
        if (heroCarousel) {
            const carousel = new bootstrap.Carousel(heroCarousel, {
                interval: 6000
            });

            const applyHeroAnimations = () => {
                const allSlides = heroCarousel.querySelectorAll('.carousel-item');
                allSlides.forEach(slide => {
                    const elements = [
                        slide.querySelector('.tagline'),
                        slide.querySelector('.subtitle'),
                        slide.querySelector('.title'),
                        slide.querySelector('.btn-shop-now')
                    ];

                    elements.forEach(el => {
                        if (el) el.classList.remove('fadeInUp');
                    });
                });

                const activeSlide = heroCarousel.querySelector('.carousel-item.active');
                if (!activeSlide) return;

                const elements = [
                    activeSlide.querySelector('.tagline'),
                    activeSlide.querySelector('.subtitle'),
                    activeSlide.querySelector('.title'),
                    activeSlide.querySelector('.btn-shop-now')
                ];

                setTimeout(() => {
                    elements.forEach((el, index) => {
                        if (el) {
                            setTimeout(() => {
                                el.classList.add('fadeInUp');
                            }, index * 200);
                        }
                    });
                }, 300);
            };

            setTimeout(applyHeroAnimations, 100);
            heroCarousel.addEventListener('slid.bs.carousel', applyHeroAnimations);
        }

        const productCards = document.querySelectorAll('.product-card');
        const productObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const slider = entry.target.querySelector('.product-slider');
                    if (slider) {
                        initializeSwiper(slider);
                        productObserver.unobserve(entry.target);
                    }
                }
            });
        }, {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        });

        productCards.forEach(card => {
            productObserver.observe(card);
        });

        const productContainer = document.getElementById('product-container');
        if (productContainer) {
            productContainer.addEventListener('mouseenter', (e) => {
                const card = e.target.closest('.product-card');
                if (card) {
                    card.classList.add('hovered');
                }
            }, { passive: true });

            productContainer.addEventListener('mouseleave', (e) => {
                const card = e.target.closest('.product-card');
                if (card) {
                    card.classList.remove('hovered');
                }
            }, { passive: true });
        }

        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '200px 0px'
        });

        lazyImages.forEach(img => {
            if (img.getAttribute('data-src') && !img.complete) {
                imageObserver.observe(img);
            }
        });

        const animateSections = document.querySelectorAll('.section-title, .category-card, .product-card, .about-content');
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fadeInUp');
                    sectionObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2
        });

        animateSections.forEach(section => {
            sectionObserver.observe(section);
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            document.body.classList.add('resize-animation-stopper');
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                document.body.classList.remove('resize-animation-stopper');
            }, 400);
        }, { passive: true });

       

        const header = document.querySelector('header');
        const scrollThreshold = 100;

        window.addEventListener('scroll', () => {
            if (!window.requestAnimationFrame) {
                handleScroll();
                return;
            }

            if (!header.dataset.ticking) {
                window.requestAnimationFrame(() => {
                    handleScroll();
                    header.dataset.ticking = 'false';
                });
                header.dataset.ticking = 'true';
            }
        }, { passive: true });

        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > scrollThreshold) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }

            const scrollTopBtn = document.querySelector('.scroll-top');
            if (scrollTopBtn) {
                if (scrollTop > window.innerHeight / 2) {
                    scrollTopBtn.classList.add('visible');
                } else {
                    scrollTopBtn.classList.remove('visible');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
            const links = [
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://cdn.jsdelivr.net' }
            ];

            links.forEach(linkData => {
                const link = document.createElement('link');
                Object.keys(linkData).forEach(key => {
                    link[key] = linkData[key];
                });
                document.head.appendChild(link);
            });
        });
    });
</script>
@endpush
