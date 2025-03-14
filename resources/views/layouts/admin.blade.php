<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <title>{{ config('app.name', 'Information System') }}</title> --}}
    <title>{{ isset($pageTitle) ? $pageTitle : config('app.name', 'Information System') }}</title>

    <meta http-equiv="content-type" content="text/html; charset-utf-8" />
    <meta name="author" content="barg unit" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animate.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animation.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-select.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/font/fonts.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/icon/style.css') }} ">
    {{-- <link rel="stylesheet" type="text/css" href="{{asset('css/sweetalert.min.css')}}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }} ">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
            "extendedTimeOut": "0"
        };

        const notificationCount = $('.notification-count');
        const notificationList = $('#notifications-list');

        // CSRF Token for AJAX Requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });

        Pusher.logToConsole = true;

        var pusher = new Pusher('bfb378da684dcc605648', {
            cluster: 'ap1',
            encrypted: true
        });

        var channel = pusher.subscribe('admin-notification');
        // channel.bind('low-stock-event', function(data) {
        //     console.log("Received data from Pusher:", data);
        //     if (data && data.product) {
        //         const productName = data.product.name;
        //         const productQuantity = data.product.quantity;
        //         toastr.warning(`${productName} stock is low. Only ${productQuantity} left in stock.`);
        //     } else {
        //         console.error('Product data is missing:', data);
        //     }
        // });

        channel.bind('contact-message-event', function(data) {
            console.log("Received contact-message-event from Pusher:", data);
            const contactMessage = data.contactMessage;
            toastr.success(
                `New message from ${contactMessage.name} (${contactMessage.email}): ${contactMessage.message}`
            );

        });
    </script>

    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1040;

            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }

        .modal-content {
            width: 100%;
            max-width: 800px;

        }
    </style>
    @stack('styles')
</head>

<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap">

                {{-- <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div> --}}

                <div class="section-menu-left">
                    <div class="box-logo">
                        <a href="{{ route('admin.index') }}" class="" style="width: 50px;">
                            <img src="{{ asset('../images/logo.png') }}" alt="site icon">
                        </a>
                        <div class="button-show-hide">
                            <i class="icon-menu-left"></i>
                        </div>
                    </div>
                    <div class="center">
                        <div class="center-item">
                            <div class="center-heading">Main Home</div>
                            <ul class="menu-list">
                                <li class="menu-item">
                                    <a href="{{ route('admin.index') }}" class="">
                                        <div class="icon"><i class="icon-grid"></i></div>
                                        <div class="text">Dashboard</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="center-item">
                            <ul class="menu-list">

                                {{-- Admin Menu Items --}}
                                @if (auth()->user()->utype === 'ADM')
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Facilities</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.facility.create') }}">
                                                    <div class="text">Add Facility</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.facilities.index') }}">
                                                    <div class="text">Facilities</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    {{-- <li class="menu-item">
                                        <a href="{{ route('admin.facilities.reservations') }}">
                                            <div class="icon"><i class="icon-calendar"></i></div>
                                            <div class="text">Reservations</div>
                                        </a>
                                    </li> --}}

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Category</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.category.add') }}">
                                                    <div class="text">Add Category</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.categories') }}">
                                                    <div class="text">Categories</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-shopping-bag"></i></div>
                                            <div class="text">Products</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product.add') }}">
                                                    <div class="text">Add Product</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.products') }}">
                                                    <div class="text">View Products</div>
                                                </a>
                                            </li>
                                            <li class="divider mb-10"></li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product-attribute-add') }}">
                                                    <div class="text">Add Product Attributes</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product-attributes') }}">
                                                    <div class="text">View Product Attributes</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.orders') }}">
                                            <div class="icon"><i class="icon-file-plus"></i></div>
                                            <div class="text">Product Orders</div>
                                        </a>
                                    </li>

                                    {{-- <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-home"></i></div>
                                            <div class="text">Rentals</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rental.add') }}">
                                                    <div class="text">Add Rentals</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.rentals') }}">
                                                    <div class="text">View Rentals</div>
                                                </a>
                                            </li>

                                        </ul>
                                    </li> --}}

                                    {{-- <li class="menu-item">
                                        <a href="{{ route('admin.reservation') }}">
                                            <div class="icon"><i class="icon-calendar"></i></div>
                                            <div class="text">Rental Reservations</div>
                                        </a>
                                    </li> --}}

                                    <li class="menu-item">
                                        <a href="{{ route('admin.slides') }}">
                                            <div class="icon"><i class="icon-image"></i></div>
                                            <div class="text">Slides</div>
                                        </a>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.contacts') }}">
                                            <div class="icon"><i class="icon-mail"></i></div>
                                            <div class="text">Messages</div>
                                        </a>
                                    </li>

                                    <li class="menu-item">
                                        <a href="{{ route('admin.users') }}">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Users</div>
                                        </a>
                                    </li>
                                @endif

                                {{-- Director Menu Items --}}
                                @if (auth()->user()->utype === 'DIR' || auth()->user()->utype === 'ADM')
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="">
                                            <div class="icon"><i class="icon-bar-chart-2"></i></div>
                                            <div class="text">Products Reports</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.reports') }}">
                                                    <div class="text">Sales</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-product') }}">
                                                    <div class="text">Products</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-user') }}">
                                                    <div class="text">Users</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-inventory') }}">
                                                    <div class="text">Inventory</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-statements') }}">
                                                    <div class="text">Statements</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.generate-input-users') }}">
                                                    <div class="text">Input User</div>
                                                </a>
                                            </li>

                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.generate-input-sales') }}">
                                                    <div class="text">Input Sales</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="">
                                            <div class="icon"><i class="icon-bar-chart-2"></i></div>
                                            <div class="text">Facilties Reports</div>
                                        </a>
                                        <ul class="sub-menu">
                                            {{-- <li class="sub-menu-item">
                                                <a href="{{ route('admin.report.facilities') }}">
                                                    <div class="text">Sales Reports</div>
                                                </a>
                                            </li> --}}


                                        </ul>
                                    </li>


                                    <!-- <li class="menu-item">
                                        <a href="admin.facilities-payment-report">
                                            <div class="icon"><i class=""></i></div>
                                            <div class="text">Settings</div>
                                        </a>
                                    </li> -->
                                @endif

                            </ul>
                        </div>

                    </div>
                </div>
                <div class="section-content-right">

                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <a href="index-2.html">
                                    <img class="" alt="" src="{{ asset('../images/logo.png') }}"
                                        style="width: 50px;">
                                </a>
                                <div class="button-show-hide">
                                    <i class="icon-menu-left"></i>
                                </div>


                                <form class="form-search flex-grow" id="searchForm"
                                    action="{{ route('admin.searchproduct') }}" method="GET">
                                    <fieldset class="name">
                                        <input type="text" placeholder="Search here..." class="show-search"
                                            name="query" id="searchQuery" required />
                                    </fieldset>
                                    <div class="button-submit">
                                        <button type="submit"><i class="icon-search"></i></button>
                                    </div>
                                </form>


                            </div>
                            <div class="header-grid">



                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        @if ((Auth::check() && Auth::user()->utype == 'ADM') || (Auth::check() && Auth::user()->utype == 'DIR'))
                                            <div class="popup-wrap user type-header">
                                                <div class="dropdown">
                                                    <!-- Dropdown Button -->
                                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                                        id="dropdownMenuButton3" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <span class="header-user wg-user d-flex align-items-center">
                                                            <span class="image">
                                                                @if (Auth::user()->profile_image)
                                                                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}"
                                                                        alt="Profile Image"
                                                                        class="img-fluid rounded-circle"
                                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                                @else
                                                                    <img src="{{ asset('images/profile.jpg') }}"
                                                                        alt="Default Profile Image"
                                                                        class="img-fluid rounded-circle"
                                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                                @endif
                                                            </span>
                                                            <span class="flex flex-column ms-2">
                                                                <span
                                                                    class="body-title mb-2">{{ strtok(Auth::user()->name, ' ') }}</span>
                                                                <span class="text-tiny">
                                                                    @if (Auth::user()->utype === 'ADM')
                                                                        Admin
                                                                    @elseif(Auth::user()->utype === 'DIR')
                                                                        Director
                                                                    @endif
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </button>

                                                    <!-- Dropdown Menu -->
                                                    <ul class="dropdown-menu dropdown-menu-end has-content"
                                                        aria-labelledby="dropdownMenuButton3">
                                                        <li>
                                                            <a href="{{ route('admin.profile.index') }}"
                                                                class="user-item">
                                                                <div class="icon">
                                                                    <i class="icon-user"></i>
                                                                </div>
                                                                <div class="body-title-2">Account</div>
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <form action="{{ route('logout') }}" method="POST"
                                                                id="logout-form">
                                                                @csrf
                                                                <a href="{{ route('logout') }}" class="user-item"
                                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                                    <div class="icon">
                                                                        <i class="icon-log-out"></i>
                                                                    </div>
                                                                    <div class="body-title-2">Log out</div>
                                                                </a>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="main-content">
                        @yield('content')
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/apexcharts/apexcharts.js') }}"></script> --}}
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchQuery');
            const searchResults = document.getElementById('searchResults');
            const menuItems = document.querySelectorAll('.menu-item, .sub-menu-item');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase().trim();
                searchResults.innerHTML = ''; // Clear previous results

                if (query) {
                    menuItems.forEach(function(item) {
                        const text = item.textContent.toLowerCase();
                        if (text.includes(query)) {
                            const resultItem = document.createElement('li');
                            const link = item.querySelector('a');
                            if (link) {
                                resultItem.innerHTML = `<a href="${link.href}">${text}</a>`;
                            } else {
                                resultItem.textContent = text;
                            }
                            searchResults.appendChild(resultItem);
                        }
                    });
                }
            });

        });
    </script>



    @stack('scripts')
</body>

</html>
