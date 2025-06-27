@extends('layouts.app')
@section('content')
    <style>
        #header {
            padding-top: 0 !important;
        }

        /* Header Carousel */
        .header-carousel {
            padding-top: 104px;
        }

        /* Carousel Item */
        .carousel-item {
            position: relative;
            padding: 100px;
        }

        /* Carousel Image */
        .carousel-image {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: cover;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0));
            z-index: 1;
        }

        /* Carousel Content */
        .carousel-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Vertically center */
            align-items: center;
            /* Horizontally center */
            text-align: center;
            z-index: 2;
        }

        /* Small Text */
        .small-text {
            font-size: 0.95rem;
        }

        .category-card {
            width: 150px;
            margin: auto;
        }

        .category-img {
            width: 100%;
            aspect-ratio: 1 / 1;
        }
    </style>
    <!-- Header Section -->
    <header id="header" class="carousel slide vh-100 header-carousel" data-bs-ride="carousel">
        <div class="carousel-inner h-100">
            @if ($slides->isNotEmpty())
                @foreach ($slides as $slide)
                    <!-- Slide -->
                    <div class="carousel-item h-100 position-relative @if ($loop->first) active @endif">
                        <img src="{{ asset('uploads/slides/' . $slide->image) }}" alt="{{ $slide->title }}"
                            class="carousel-image">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-content">
                            <div class="text-center py-2">
                                <h3 class="py-2 fw-normal text-white small-text">{{ $slide->tagline }}</h3>
                                <h2 class="text-white mb-2">{{ $slide->subtitle }}</h2>
                                <h1 class="text-uppercase py-2 fw-normal text-white display-3">{{ $slide->title }}</h1>
                                <a href="{{ $slide->link }}" class="btn btn-header mt-3 text-uppercase px-4 py-2">SHOP
                                    NOW</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="carousel-item h-100 position-relative active">
                    <img src="{{ asset('./images/bargImage.png') }}" alt="Default Slide" class="carousel-image"
                        loading="lazy">
                    <div class="carousel-overlay"></div>
                    <div class="carousel-content">
                        <div class="text-center py-2">
                            <h3 class="py-2 fw-normal text-white small-text">Welcome to CvSU BaRG</h3>
                            <h2 class="text-white mb-2">We're glad you're here</h2>
                            <h1 class="text-uppercase py-2 fw-normal text-white display-3">OVPEBA</h1>
                            <a href="#" class="btn btn-header mt-3 text-uppercase px-4 py-2">Explore</a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#header" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#header" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </header>

    <div class="container">
        <div class="swiper mySwiper py-5">
            <div class="swiper-wrapper">
                @foreach ($categories as $category)
                    <div class="swiper-slide shadow-sm py-4">
                        <div class="card border-0 text-center category-card">
                            <a href="{{ route('shop.index', ['categories' => $category->id]) }}"
                                title="{{ $category->name }}" class="text-decoration-none text-success">
                                <img src="{{ asset('uploads/categories/' . $category->image) }}"
                                    class="category-img mb-2 object-fit-cover rounded" loading="lazy">
                                <h6 class="fw-semibold mb-0">{{ $category->name }}</h6>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>


    @if ($fproducts->isNotEmpty())
        <section id="collection" class="py-3 pt-5">
            <div class="container pb-5">
                <div class="title-h2 text-center">
                    <h2 class="position-relative d-inline-block">Marketing</h2>
                </div>


                <div class="row g-4 my-5 mx-auto owl-carousel owl-theme">
                    @foreach ($fproducts as $fproduct)
                        <div class="col product-item d-flex flex-column h-100">
                            <div class="product-img h-100">
                                <!-- Swiper Slider for Product Images -->
                                <div class="swiper-container js-swiper-slider">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <a
                                                href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products') }}/{{ $fproduct->image }}"
                                                    alt="{{ $fproduct->name }}" class="img-fluid d-block mx-auto"
                                                    style="object-fit: cover; aspect-ratio: 1/1;">
                                            </a>
                                        </div>
                                        @foreach (explode(',', $fproduct->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    alt="{{ $fproduct->name }}" class="img-fluid d-block mx-auto"
                                                    style="object-fit: cover; aspect-ratio: 1/1;">
                                            </div>
                                        @endforeach
                                        <!-- Duplicate slides for loop functionality -->
                                        <div class="swiper-slide">
                                            <a
                                                href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products') }}/{{ $fproduct->image }}"
                                                    alt="{{ $fproduct->name }}" class="img-fluid d-block mx-auto"
                                                    style="object-fit: cover; aspect-ratio: 1/1;">
                                            </a>
                                        </div>
                                        @foreach (explode(',', $fproduct->images) as $gimg)
                                            <div class="swiper-slide">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products') }}/{{ $gimg }}"
                                                    alt="{{ $fproduct->name }}" class="img-fluid d-block mx-auto"
                                                    style="object-fit: cover; aspect-ratio: 1/1;">
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Pagination -->
                                    <div class="swiper-pagination slideshow-pagination"></div>
                                    <!-- Navigation Buttons -->
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>

                            </div>
                            <div class="product-info px-3 py-3"
                                onclick="window.location.href='{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}'">
                                <h1 class="title-cards fw-normal text-capitalize mb-2 display-2 ">{{ $fproduct->name }}
                                </h1>
                                <p class="span-description fw-light mb-2">{{ $fproduct->short_description }}</p>
                                <div class="avail d-flex justify-content-center">
                                    <span class="availability fw-bold me-2">Available items: </span>
                                    <span class="avail-tags"
                                        style="color:
                                    @if ($fproduct->attributeValues->isNotEmpty() && isset($fproduct->attributeValues[0]->quantity)) {{ $fproduct->attributeValues[0]->quantity > 0 ? ($fproduct->attributeValues[0]->quantity == 1 ? 'orange' : 'green') : 'red' }}
                                    @else
                                        {{ $fproduct->quantity > 0 ? ($fproduct->quantity == 1 ? 'orange' : 'green') : 'red' }} @endif
                                ;">
                                        @if ($fproduct->attributeValues->isNotEmpty() && isset($fproduct->attributeValues[0]->quantity))
                                            {{ $fproduct->attributeValues[0]->quantity > 0 ? $fproduct->attributeValues[0]->quantity : 0 }}
                                        @else
                                            {{ $fproduct->quantity > 0 ? $fproduct->quantity : 0 }}
                                        @endif
                                    </span>
                                </div>

                                <h2 class="product-type text-primary pt-2">
                                    &#8369;
                                    @if ($fproduct->attributeValues->isNotEmpty() && isset($fproduct->attributeValues[0]->price))
                                        {{ number_format($fproduct->attributeValues[0]->price, 2) }}
                                    @else
                                        {{ number_format($fproduct->price, 2) }}
                                    @endif
                                </h2>


                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="container d-flex justify-content-center">
                    <a href="{{ route('shop.index') }}"><button type="button" class="btn btn-link">See
                            More</button></a>
                </div>
            </div>
        </section>
    @endif

    <section class="about-us py-4">
        <div class="title-h2 text-center">
            <h2 class="position-relative d-inline-block" id="about">About us</h2>
        </div>
        <div class="we-help-section">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-lg-7 mb-5 mb-lg-0">
                        <div class="imgs-grid">
                            <div class="grid grid-1"><img src="{{ asset('./images/OVPEBA.jpg') }}"></div>
                            <div class="grid grid-2"><img src="{{ asset('./images/ovp.jpg') }}"></div>
                            <div class="grid grid-3"><img src="{{ asset('./images/ovp.jpg') }}"></div>
                        </div>
                    </div>
                    <div class="col-lg-5 ps-lg-5">
                        <h2 class="section-title mb-4 text-capitalize">we help you to make your life easier</h2>
                        <p>The Business and Resource Generation (BaRG) Unit has been a cornerstone of our campus
                            community
                            for years, offering a wide range of resources, items, and rentals. From uniforms and books
                            to
                            souvenirs and essential supplies, BaRG Unit is your go-to marketplace for all campus needs.
                        </p>
                        <p><a href="{{ route('about.index') }}" class="btn text-white btn-about">Explore</a></p>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const swiper = new Swiper('.mySwiper', {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                576: {
                    slidesPerView: 3
                },
                768: {
                    slidesPerView: 4
                },
                992: {
                    slidesPerView: 5
                }
            }
        });
    </script>
@endpush
