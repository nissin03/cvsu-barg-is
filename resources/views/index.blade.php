@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/shop/home.css') }}" rel="stylesheet">
    <!-- Hero Carousel Section -->
    @if ($slides->isNotEmpty())
        <header id="hero-carousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach ($slides as $key => $slide)
                    <button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="{{ $key }}"
                        class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        aria-label="Slide {{ $key + 1 }}"></button>
                @endforeach
            </div>

            <div class="carousel-inner h-100">
                @foreach ($slides as $slide)
                    <div class="carousel-item h-100 @if ($loop->first) active @endif">
                        <img src="{{ asset('uploads/slides/' . $slide->image) }}" alt="{{ $slide->title }}"
                            class="carousel-image" loading="lazy">
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
                @foreach ($categories as $category)
                    <div class="col-sm-6 col-lg-4">
                        <a href="{{ route('shop.index', ['categories' => $category->id]) }}" class="text-decoration-none">
                            <div class="category-card">
                                <img src="{{ asset('uploads/categories/' . $category->image) }}"
                                    alt="{{ $category->name }}" class="category-image" loading="lazy">
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
                                        @if ($fproduct->sale_price > 0)
                                            <span class="product-badge">SALE</span>
                                        @endif

                                        <!-- Swiper Slider for Product Images -->
                                        <div class="swiper-container product-slider">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">

                                                    <img loading="lazy"
                                                        src="{{ asset('uploads/products') }}/{{ $fproduct->image }}"
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
                                                $quantity =
                                                    $fproduct->attributeValues->isNotEmpty() &&
                                                    isset($fproduct->attributeValues[0]->quantity)
                                                        ? $fproduct->attributeValues[0]->quantity
                                                        : $fproduct->quantity;
                                                $stockClass =
                                                    $quantity > 5
                                                        ? 'in-stock'
                                                        : ($quantity > 0
                                                            ? 'low-stock'
                                                            : 'out-of-stock');
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
                </div>

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
                        <p>The Business and Resource Generation (BaRG) Unit has been a cornerstone of our campus community
                            for years, offering a wide range of resources, items, and rentals. From uniforms and books to
                            souvenirs and essential supplies, BaRG Unit is your go-to marketplace for all campus needs.</p>
                        <p>We pride ourselves on providing high-quality products and exceptional service to enhance your
                            campus experience.</p>
                        <a href="{{ route('about.index') }}" class="btn btn-explore">Explore More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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

            function initializeSwiper(container) {
                new Swiper(container, {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    loop: true,
                    navigation: {
                        nextEl: container.querySelector('.swiper-button-next'),
                        prevEl: container.querySelector('.swiper-button-prev'),
                    },
                    pagination: {
                        el: container.querySelector('.swiper-pagination'),
                        clickable: true,
                    },
                });
            }

            const productContainer = document.getElementById('product-container');
            if (productContainer) {
                productContainer.addEventListener('mouseenter', (e) => {
                    const card = e.target.closest('.product-card');
                    if (card) {
                        card.classList.add('hovered');
                    }
                }, {
                    passive: true
                });

                productContainer.addEventListener('mouseleave', (e) => {
                    const card = e.target.closest('.product-card');
                    if (card) {
                        card.classList.remove('hovered');
                    }
                }, {
                    passive: true
                });
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

            const animateSections = document.querySelectorAll(
                '.section-title, .category-card, .product-card, .about-content');
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
            }, {
                passive: true
            });



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
            }, {
                passive: true
            });

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
                const links = [{
                        rel: 'preconnect',
                        href: 'https://fonts.googleapis.com'
                    },
                    {
                        rel: 'preconnect',
                        href: 'https://cdn.jsdelivr.net'
                    }
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
