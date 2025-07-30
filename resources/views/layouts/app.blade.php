<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="barg unit">

    <title>{{ config('app.name', 'Information System') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('../images/favicon-logo.png') }}" type="image/x-icon">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- FullCalendar -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>

    <!-- Moment.js and Pikaday -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.13.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Local Assets -->
    <link rel="stylesheet" href="{{ asset('owl_carousel/owl.theme.default.css') }}">
    <link rel="stylesheet" href="{{ asset('owl_carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('./font-awesome-6-pro-main/css/all.min.css') }}">

    @stack('styles')
</head>

<body data-bs-spy="scroll" data-bs-target="#goTop">
    <style>
        #box-content-search li {
            list-style: none;
        }

        #box-content-search .product-item {
            margin-bottom: 2px;
        }

        .divider {
            border-top: 1px solid #eaeaea;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 1px;
        }
    </style>

    <nav class="navbar navbar-expand-lg navbar-light bg-white py-4 fixed-top">
        <div class="container">
            <a href="{{ route('home.index') }}" class="navbar-brand d-flex justify-content-between align-items-center order-lg-0">
                <img src="{{ asset('images/logo.png') }}" alt="Site Logo" class="navbar-logo">
            </a>

            <div class="order-lg-2 nav-btns header-tools__item header-tools__cart">
                <div class="header-tools d-flex align-items-center">
                    <div class="header-tools__item hover-container">
                        <a class="js-search-popup search-field__actor" href="#" id="searchToggle">
                            <i class="fa-regular fa-magnifying-glass" id="searchIcon"></i>
                            <i class="fa-solid fa-times d-none" id="closeIcon"></i>
                        </a>
                    </div>

                    <button type="button" class="btn position-relative" onclick="window.location.href='{{ route('cart.index') }}'">
                        <i class="fa-sharp fa-regular fa-bag-shopping"></i>
                        @if (Cart::instance('cart')->content()->count() > 0)
                            <span class="cart-amount d-block position-absolute js-cart-items-count">{{ Cart::instance('cart')->content()->count() }}</span>
                        @endif
                    </button>
                    
                    @guest
                        <button type="button" class="btn position-relative" onclick="location.href='{{ route('login') }}'">
                            <i class="fa-regular fa-user"></i>
                        </button>
                    @else
                        <button type="button" class="btn d-flex align-items-center position-relative"
                            onclick="location.href='{{ Auth::user()->utype == 'ADM' ? route('admin.index') : (Auth::user()->utype == 'DIR' ? route('admin.index') : route('user.index')) }}'">
                            <div class="profile-image d-flex justify-content-center me-2">
                                @if (Auth::user()->profile_image)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image"
                                        class="img-fluid rounded-circle"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/profile.jpg') }}" loading="lazy" alt="Default Profile Image"
                                        class="img-fluid rounded-circle"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                @endif
                            </div>
                            <span class="d-none d-md-inline">{{ strtok(Auth::user()->name, ' ') }}</span>
                        </button>
                    @endguest
                </div>
            </div>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-lg-1" id="navMenu">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('home.index') }}" class="nav-link text-uppercase text-dark">Home</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('shop.index') }}" class="nav-link text-uppercase text-dark">Shop</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('user.facilities.index') }}" class="nav-link text-uppercase text-dark">Facilities</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('about.index') }}" class="nav-link text-uppercase text-dark">About Us</a>
                    </li>
                    <li class="nav-item px-2 py-2 border-0">
                        <a href="{{ route('home.contact') }}" class="nav-link text-uppercase text-dark">Contact</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="search-popup js-hidden-content">
            <form action="#" method="GET" class="search-field container">
                <p class="text-uppercase text-secondary fw-medium mb-4">What are you looking for?</p>
                <div class="position-relative">
                    <input class="search-field__input search-popup__input w-100 fw-medium" id="search-input"
                        type="text" name="search-keyword" placeholder="Search products" />
                    <button class="search-popup__submit" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <div class="search-popup__results">
                    <ul id="box-content-search"></ul>
                </div>
            </form>
        </div>
    </nav>

    <div id="goTop">
        <a href="#topbar" id="goTopbtn"><i class="fa-solid fa-chevron-up"></i></a>
    </div>

    @yield('content')

    <footer>
        <div class="footer-section footer-top-copyright">
            <div class="container">
                <div class="row g-5 mb-5">
                    <div class="col-lg-4">
                        <div class="footer-logo-wrap mb-4">
                            <a href="#" class="footer-logo text-capitalize">BaRG<span>.</span></a>
                        </div>
                        <p class="mb-4">
                            The Business and Resource Generation (BaRG) Unit has been a cornerstone of our campus
                            community for years, offering a wide range of resources, items, and rentals.
                        </p>
                        <ul class="list-unstyled custom-social">
                            <li>
                                <a href="https://www.facebook.com/obarg.cvsu/">
                                    <span class="fa fa-brands fa-facebook-f"></span>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.youtube.com/channel/UC6p5srphejusQzaulrxmtow">
                                    <span class="fa fa-brands fa-youtube"></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-8">
                        <div class="row links-wrap">
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">About Us</li>
                                    <li><a class="text-muted" href="#">Values</a></li>
                                    <li><a class="text-muted" href="#">Our Team</a></li>
                                    <li><a class="text-muted" href="#">History</a></li>
                                    <li><a class="text-muted" href="#">Services</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">Contact Us</li>
                                    <li><a class="text-muted" href="#">Cavite State University - Don Severino
                                            Delas Alas Campus, Indang, Cavite</a></li>
                                    <li><a class="text-muted" href="#">barg@gmail.com</a></li>
                                    <li><a class="text-muted" href="#">5VXJ+4H Indang, Cavite</a></li>
                                    <li><a class="text-muted" href="#">+639</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-sm-6 col-md-3">
                                <ul class="list-unstyled">
                                    <li class="fw-bold">Links</li>
                                    <li><a class="text-muted" href="#">Product</a></li>
                                    <li><a class="text-muted" href="#">Rental</a></li>
                                    <li><a class="text-muted" href="#about">About Us</a></li>
                                    <li><a class="text-muted" href="#">Contacts</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top copyright">
                    <div class="row pt-4">
                        <div class="col-lg-6 text-center text-lg-start mb-2 mb-lg-0">
                            <p>
                                &copy;
                                <script>
                                    document.write(new Date().getFullYear());
                                </script> All Rights Reserved.
                            </p>
                        </div>
                        <div class="col-lg-6 text-center text-lg-end">
                            <ul class="list-unstyled d-inline-flex ms-auto">
                                <li class="me-4"><a href="#">Terms &amp; Conditions</a></li>
                                <li><a href="#">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Local Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/swiper.min.js') }}"></script>
    <script src="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.js') }}"></script>
    <script src="{{ asset('owl_carousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('js/countdown.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
        $(document).ready(function() {
            $("#search-input").on("keyup", function() {
                var searchQuery = $(this).val().trim();

                if (searchQuery.length > 2) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('home.search') }}",
                        data: { query: searchQuery },
                        dataType: 'json',
                        success: function(data) {
                            $("#box-content-search").empty();

                            if (data.length > 0) {
                                $.each(data, function(index, item) {
                                    var urlTemplate = "{{ route('shop.product.details', ['product_slug' => 'product_slug_pls']) }}";
                                    var productUrl = urlTemplate.replace('product_slug_pls', item.slug);

                                    $("#box-content-search").append(
                                        `<li>
                                            <ul>
                                                <li class="product-item-search gap14 mb-10">
                                                    <div class="image no-bg">
                                                        <img src="{{ asset('uploads/products/thumbnails/') }}/${item.image}" alt="${item.name}">
                                                    </div>
                                                    <div class="flex items-center justify-between gap20 flex-grow">
                                                        <div class="name">
                                                            <a href="${productUrl}" class="body-text">${item.name}</a>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-10">
                                                    <div class="divider"></div>
                                                </li>
                                            </ul>
                                        </li>`
                                    );
                                });
                            } else {
                                $("#box-content-search").html('<p>No results found</p>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                } else {
                    $("#box-content-search").empty();
                }
            });
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    @stack('scripts')
</body>

</html>