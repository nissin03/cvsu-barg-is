<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="barg unit">
    @auth
        <meta name="user-id" content="{{ Auth::user()->id }}">
        <meta name="user-role" content="{{ Auth::user()->utype }}">
    @endauth

    <title>{{ config('app.name', 'Information System') }}</title>

    <link rel="shortcut icon" href="{{ asset('../images/logo/BaRG-logo.png') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.13.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11.13.1/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('./font-awesome-6-pro-main/css/all.min.css') }}">

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

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            flex-shrink: 0;
            background-color: #fff;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .notification-heading {
            margin: 0;
            font-size: 18px;
        }

        .notification-item.read {
            background-color: #f8f9fa;
            opacity: 0.8;
        }

        .notification-item.read .notification-text {
            font-weight: normal !important;
        }

        .mark-read {
            background: none;
            border: none;
            color: #30d683;
            font-weight: 600;
            font-size: 14px;
        }

        .category-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            font-size: 12px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
        }

        .remove-notification {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0.5;
            transition: all 0.2s ease;
            z-index: 10;
            border-radius: 50%;
        }

        .remove-notification:hover {
            opacity: 1;
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .notification-actions {
            display: flex;
            gap: 15px;
        }

        .mark-read,
        .remove-all {
            background: none;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .mark-read {
            color: #30d683;
        }

        .remove-all {
            color: #dc3545;
        }

        .mark-read:hover,
        .remove-all:hover {
            opacity: 0.8;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
            position: relative;
            min-height: 60px;
            transition: background-color 0.2s ease;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            overflow: hidden;
        }

        .notification-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e9ecef;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
            padding-right: 25px;
        }

        .notification-text {
            margin: 0 0 4px 0;
            font-size: 14px;
            line-height: 1.3;
            word-wrap: break-word;
        }

        .notification-subtext {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
            line-height: 1.3;
            display: flex;
            align-items: center;
            word-wrap: break-word;
        }

        #notification-list,
        #all-notification-list {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .notification-list-container::-webkit-scrollbar-track,
        #notification-list::-webkit-scrollbar-track,
        #all-notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .notification-list-container::-webkit-scrollbar-thumb,
        #notification-list::-webkit-scrollbar-thumb,
        #all-notification-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .notification-list-container::-webkit-scrollbar-thumb:hover,
        #notification-list::-webkit-scrollbar-thumb:hover,
        #all-notification-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .all-notifications {
            max-height: 400px;
        }

        .unread-indicator {
            width: 8px;
            height: 8px;
            background-color: #30d683;
            border-radius: 50%;
            margin-left: auto;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .dropdown-footer {
            padding: 0;
            flex-shrink: 0;
            background-color: #fff;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #f0f0f0;
        }

        .dropdown-footer button {
            width: 100%;
            padding: 12px;
            border: none;
            background-color: #f8f9fa;
            color: #212529;
            font-weight: 600;
            border-radius: 0 0 10px 10px;
            transition: background-color 0.2s ease;
        }

        .dropdown-footer button:hover {
            background-color: #e9ecef;
        }

        .popup-wrap.message.type-header {
            position: relative;
        }

        .header-item {
            display: flex;
            align-items: center;
            position: relative;
        }

        .text-tiny.notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 15px;
            height: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            min-width: 15px;
        }

        .fa-regular.fa-bell {
            font-size: 18px;
            color: black !important;
        }

        .btn.btn-primary.dropdown-toggle {
            background: none;
            border: none;
            color: #6c757d;
            padding: 8px 12px;
        }

        .btn.btn-primary.dropdown-toggle:hover {
            background-color: #f8f9fa;
            color: #495057;
        }

        .btn.btn-primary.dropdown-toggle:focus {
            box-shadow: none;
            background-color: #f8f9fa;
            color: #495057;
        }

        .btn.btn-primary.dropdown-toggle::after {
            display: none !important;
        }

        .dropdown-toggle::after {
            display: none !important;
        }

        .dropdown-menu {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 350px;
            max-width: 400px;
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            z-index: 1000;
            display: none;
            max-height: 80vh;
            overflow: hidden;
            flex-direction: column;
            background-color: #fff;
        }

        .dropdown-menu.show {
            display: flex !important;
        }

        .dropdown-menu-end {
            right: 0;
            left: auto;
        }
    </style>

    <nav class="navbar navbar-expand-lg navbar-light bg-white py-4 fixed-top">
        <div
            class="container position-relative d-flex align-items-center justify-content-between flex-wrap flex-lg-nowrap">
            <a href="{{ route('home.index') }}"
                class="navbar-brand d-flex justify-content-between align-items-center order-lg-0">
                <img src="{{ asset('../images/logo/BaRG-logo.png') }}" alt="Site Logo" class="navbar-logo">
            </a>

            <div
                class="order-lg-2 nav-btns header-tools__item header-tools__cart d-flex align-items-center ms-auto ms-lg-0 mt-2 mt-lg-0">
                <div class="header-tools d-flex align-items-center">
                    <div class="header-tools__item hover-container">
                        <a class="js-search-popup search-field__actor" href="#" id="searchToggle">
                            <i class="fa-regular fa-magnifying-glass" id="searchIcon"></i>
                            <i class="fa-solid fa-times d-none" id="closeIcon"></i>
                        </a>
                    </div>

                    <button type="button" class="btn position-relative"
                        onclick="window.location.href='{{ route('cart.index') }}'">
                        <i class="fa-sharp fa-regular fa-bag-shopping"></i>
                        @if (Cart::instance('cart')->content()->count() > 0)
                            <span
                                class="cart-amount d-block position-absolute js-cart-items-count">{{ Cart::instance('cart')->content()->count() }}</span>
                        @endif
                    </button>

                    @guest
                        <button type="button" class="btn position-relative"
                            onclick="location.href='{{ route('login') }}'">
                            <i class="fa-regular fa-user"></i>
                        </button>
                    @else
                        @if (Auth::user()->utype == 'USR')
                            @include('components.notification-dropdown')
                        @endif
                        <button type="button" class="btn d-flex align-items-center position-relative"
                            onclick="location.href='{{ Auth::user()->utype == 'ADM' ? route('admin.index') : (Auth::user()->utype == 'DIR' ? route('admin.index') : route('user.index')) }}'">
                            <div class="profile-image d-flex justify-content-center me-2 flex-shrink-0">
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

            <div class="collapse navbar-collapse order-lg-1 w-100 mt-3 mt-lg-0" id="navMenu">
                <ul class="navbar-nav mx-auto text-center flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3">
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('home.index') }}" class="nav-link text-uppercase text-dark">Home</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('shop.index') }}" class="nav-link text-uppercase text-dark">Shop</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('user.facilities.index') }}"
                            class="nav-link text-uppercase text-dark">Facilities</a>
                    </li>
                    <li class="nav-item px-2 py-2">
                        <a href="{{ route('about.index') }}" class="nav-link text-uppercase text-dark">About Us</a>
                    </li>
                    <li class="nav-item px-2 py-2 border-0">
                        <a href="{{ route('home.contact') }}" class="nav-link text-uppercase text-dark">Contact</a>
                    </li>
                </ul>
            </div>

            <div class="search-popup js-hidden-content">
                <div class="container">
                    <form action="#" method="GET" class="search-field" id="searchForm">
                        <div class="position-relative">
                            <input class="search-field__input search-popup__input w-100 fw-medium" id="search-input"
                                type="text" name="search-keyword" placeholder="Search products or facilities..."
                                autocomplete="off" />
                            <button class="search-popup__submit" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                        <div class="search-popup__results">
                            <ul id="box-content-search"></ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <style>
        .search-popup {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            padding: 15px 0;
            border-top: 1px solid #f0f0f0;
        }

        .search-popup.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .search-popup .container {
            padding: 0 15px;
        }

        .search-popup__input {
            padding: 12px 50px 12px 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            width: 100%;
        }

        .search-popup__submit {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            font-size: 18px;
        }

        .search-popup__results {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 15px;
            border-top: 1px solid #f0f0f0;
            padding-top: 10px;
        }

        .search-result-item {
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: all 0.2s ease;
        }

        .search-result-item a:hover {
            color: #0066cc;
        }

        .search-result-item .image {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .search-result-item .image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .search-result-item .info {
            flex-grow: 1;
        }

        .search-result-item .name {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .search-result-item .type {
            font-size: 13px;
            color: #888;
        }

        .no-results {
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 15px;
        }

        .search-result-item.active {
            background-color: #f5f5f5;
        }

        .search-result-item.active a {
            color: #000;
        }

        .navbar .navbar-brand .navbar-logo {
            width: 90px;
            height: auto;
        }
    </style>

    <style>
        @media (max-width: 768px) {
            .navbar.fixed-top {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .navbar .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .navbar .navbar-brand .navbar-logo {
                width: 60px;
                height: auto;
            }

            #navMenu {
                background-color: #ffffff;
                border-top: 1px solid #f0f0f0;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .navbar-nav .nav-item {
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
            }

            .search-popup {
                left: 0;
                right: 0;
                width: 100%;
            }

            .search-popup__input {
                font-size: 14px;
            }
        }

        @media (max-width: 575.98px) {
            .modal-dialog {
                margin: 0.75rem;
                max-width: 100%;
                width: 100%;
            }

            .modal-content {
                border-radius: 0.75rem;
            }

            .modal-body {
                max-height: calc(100vh - 7rem);
                overflow-y: auto;
            }
        }

        @media (max-width: 575.98px) {
            .swal2-popup {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0.5rem !important;
                box-sizing: border-box;
            }
        }


        .fc {
            width: 100%;
        }

        @media (max-width: 768px) {
            .fc .fc-view-harness {
                overflow-x: auto;
            }

            .fc .fc-scrollgrid,
            .fc .fc-dayGridMonth-view .fc-scrollgrid {
                min-width: 700px;
            }
        }

        @media (max-width: 768px) {
            .card {
                margin-bottom: 1rem;
            }

            .form-inline,
            .d-flex.form-inline,
            form.d-flex:not(.navbar-form) {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .form-inline>* {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }

            .btn-group,
            .btn-toolbar {
                flex-wrap: wrap;
                width: 100%;
            }

            .btn-group .btn,
            .btn-toolbar .btn {
                flex: 1 0 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>

    <script>
        $(document).ready(function() {
            var selectedIndex = -1;

            function resetSelection() {
                selectedIndex = -1;
                $("#box-content-search .search-result-item").removeClass('active');
            }

            function moveSelection(direction) {
                var $items = $("#box-content-search .search-result-item");
                if ($items.length === 0) return;

                if (direction === 'down') {
                    selectedIndex = (selectedIndex + 1) % $items.length;
                } else if (direction === 'up') {
                    selectedIndex = (selectedIndex - 1 + $items.length) % $items.length;
                }

                $items.removeClass('active');
                var $selected = $items.eq(selectedIndex).addClass('active');

                var $container = $("#box-content-search");
                var itemTop = $selected.position().top;
                var itemBottom = itemTop + $selected.outerHeight();
                var scrollTop = $container.scrollTop();
                var containerHeight = $container.height();

                if (itemBottom > scrollTop + containerHeight) {
                    $container.scrollTop(itemBottom - containerHeight);
                } else if (itemTop < scrollTop) {
                    $container.scrollTop(itemTop);
                }
            }

            $('#searchToggle').on('click', function(e) {
                e.preventDefault();
                $('.search-popup').toggleClass('active');
                $('#searchIcon').toggleClass('d-none');
                $('#closeIcon').toggleClass('d-none');

                if ($('.search-popup').hasClass('active')) {
                    $('#search-input').focus();
                } else {
                    resetSelection();
                }
            });

            $("#search-input").on("keyup", function(e) {
                var key = e.key;
                if (key === 'ArrowDown' || key === 'ArrowUp' || key === 'Enter' || key === 'Escape' ||
                    key === 'Tab') {
                    return;
                }

                var searchQuery = $(this).val().trim();

                if (searchQuery.length > 2) {
                    performSearch(searchQuery);
                } else {
                    $("#box-content-search").empty();
                    resetSelection();
                }
            });


            $("#search-input").on("keydown", function(e) {
                var key = e.key;

                if (key === 'ArrowDown') {
                    e.preventDefault();
                    moveSelection('down');
                } else if (key === 'ArrowUp') {
                    e.preventDefault();
                    moveSelection('up');
                } else if (key === 'Enter') {
                    var $items = $("#box-content-search .search-result-item");
                    var hasNoResults = $("#box-content-search .no-results").length > 0;

                    if ($items.length === 0 && hasNoResults) {
                        e.preventDefault();
                        return;
                    }

                    if ($items.length === 0) {
                        return;
                    }

                    e.preventDefault();

                    if (selectedIndex < 0 || selectedIndex >= $items.length) {
                        selectedIndex = 0;
                        $items.removeClass('active');
                        $items.eq(0).addClass('active');
                    }

                    var $selected = $items.eq(selectedIndex);
                    var href = $selected.find('a').attr('href');

                    if (href) {
                        window.location.href = href;
                    }
                }
            });

            $("#searchForm").on("submit", function(e) {
                e.preventDefault();
                var searchQuery = $("#search-input").val().trim();
                if (searchQuery.length > 0) {
                    performSearch(searchQuery);
                }
            });

            function performSearch(query) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('home.search') }}",
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(data) {
                        $("#box-content-search").empty();
                        resetSelection();

                        if (data.products.length > 0 || data.facilities.length > 0) {
                            $.each(data.products, function(index, item) {
                                var productUrl =
                                    "{{ route('shop.product.details', ['product_slug' => ':slug']) }}"
                                    .replace(':slug', item.slug);
                                var imageUrl = item.image ?
                                    "{{ asset('uploads/products/thumbnails') }}/" + item
                                    .image : "{{ asset('images/default-product.png') }}";
                                var priceDisplay = item.price ? '₱' + parseFloat(item.price)
                                    .toFixed(2) : '₱0.00';

                                $("#box-content-search").append(
                                    `<li class="search-result-item">
                                    <a href="${productUrl}">
                                        <div class="image">
                                            <img src="${imageUrl}" alt="${item.name}" loading="lazy">
                                        </div>
                                        <div class="info">
                                            <div class="name">${item.name}</div>
                                            <div class="type">Product • ${priceDisplay}</div>
                                        </div>
                                    </a>
                                </li>`
                                );
                            });

                            $.each(data.facilities, function(index, item) {
                                var facilityUrl =
                                    "{{ route('user.facilities.details', ['slug' => ':slug']) }}"
                                    .replace(':slug', item.slug || item.id);
                                var imageUrl;

                                if (item.image) {
                                    var cleanImageName = item.image;
                                    if (cleanImageName.startsWith('facilities/')) {
                                        cleanImageName = cleanImageName.replace('facilities/',
                                            '');
                                    }
                                    imageUrl = "/storage/facilities/thumbnails/" +
                                        cleanImageName;
                                } else {
                                    imageUrl = "{{ asset('images/default-facility.png') }}";
                                }

                                $("#box-content-search").append(
                                    `<li class="search-result-item">
                                    <a href="${facilityUrl}">
                                        <div class="image">
                                            <img src="${imageUrl}"
                                                alt="${item.name}"
                                                loading="lazy"
                                                onerror="
                                                    if (this.src.includes('/thumbnails/')) {
                                                        var fallbackUrl = this.src.replace('/thumbnails/', '/');
                                                        this.src = fallbackUrl;
                                                    } else {
                                                        this.src = '{{ asset('images/default-facility.png') }}';
                                                        this.onerror = null;
                                                    }
                                                ">
                                        </div>
                                        <div class="info">
                                            <div class="name">${item.name}</div>
                                            <div class="type">Facility</div>
                                        </div>
                                    </a>
                                </li>`
                                );
                            });
                        } else {
                            $("#box-content-search").html(
                                '<li class="no-results">No results found for "' + query + '"</li>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Search error:', xhr.responseText);
                        $("#box-content-search").html(
                            '<li class="no-results">Error loading results. Please try again.</li>');
                        resetSelection();
                    }
                });
            }

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-popup, #searchToggle').length) {
                    closeSearch();
                }
            });

            $(document).on('keyup', function(e) {
                if (e.key === "Escape") {
                    closeSearch();
                }
            });

            function closeSearch() {
                $('.search-popup').removeClass('active');
                $('#searchIcon').removeClass('d-none');
                $('#closeIcon').addClass('d-none');
                $("#search-input").val('');
                $("#box-content-search").empty();
                resetSelection();
            }
        });
    </script>

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

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/swiper.min.js') }}"></script>
    <script src="{{ asset('plugins/ijaboCropTool/ijaboCropTool.min.js') }}"></script>
    <script src="{{ asset('owl_carousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('js/countdown.js') }}"></script>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>

    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var errorToast = document.getElementById('errorToast');
            if (errorToast) {
                new bootstrap.Toast(errorToast).show();
            }
        });
    </script>

    @vite('resources/js/app.js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            const dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            const dropdownToggle = document.getElementById('dropdownMenuButton2');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (dropdownToggle && dropdownMenu && !window.bootstrap) {
                console.log('Bootstrap not available, using fallback dropdown');
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isOpen = dropdownMenu.classList.contains('show');
                    if (isOpen) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    } else {
                        dropdownMenu.classList.add('show');
                        dropdownToggle.setAttribute('aria-expanded', 'true');
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
