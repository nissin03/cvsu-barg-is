<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::user()->id }}">
        <meta name="user-role" content="{{ Auth::user()->utype }}">
    @endauth

    {{-- <title>{{ config('app.name', 'Information System') }}</title> --}}
    <title>{{ isset($pageTitle) ? $pageTitle : config('app.name', 'Information System') }}</title>

    {{-- @vite(['resources/js/app.js']) --}}
    <meta http-equiv="content-type" content="text/html; charset-utf-8" />
    <meta name="author" content="barg unit" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animate.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/animation.css') }} ">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.css') }} "> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-select.min.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/font/fonts.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/icon/style.css') }} ">
    {{-- <link rel="stylesheet" type="text/css" href="{{asset('css/sweetalert.min.css')}}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }} ">
    {{-- <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    @vite('resources/js/app.js')

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

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-heading {
            margin: 0;
            font-size: 18px;
        }


        /* Add this to your CSS file */
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
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0.7;
            transition: all 0.2s ease;
            z-index: 10;
        }

        .remove-notification:hover {
            opacity: 1;
            color: #dc3545;
        }

        .notification-actions {
            display: flex;
            gap: 15px;
        }

        .remove-all {
            background: none;
            border: none;
            color: #dc3545;
            font-weight: 600;
            font-size: 14px;
        }

        .remove-notification i {
            font-size: 14px;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            position: relative;
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e9ecef;
        }

        .notification-content {
            flex: 1;
        }

        .notification-text {
            margin: 0;
            font-size: 14px;
        }

        .notification-subtext {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
        }

        /* Add these new styles to your existing CSS */
        .notification-list-container {
            max-height: 300px;
            overflow-y: auto;
            transition: max-height 0.3s ease;
            scrollbar-width: thin;
        }

        .notification-list-container::-webkit-scrollbar {
            width: 6px;
        }

        .notification-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .notification-list-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .all-notifications {
            max-height: 400px;
            /* Taller container for all notifications */
        }

        .unread-indicator {
            width: 10px;
            height: 10px;
            background-color: #30d683;
            border-radius: 50%;
            margin-left: 10px;
        }

        .dropdown-footer {
            padding: 0;
        }

        .dropdown-footer button {
            width: 100%;
            padding: 12px;
            border: none;
            background-color: #f8f9fa;
            color: #212529;
            font-weight: 600;
            border-radius: 0 0 10px 10px;
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
                        <a href="{{ route('admin.index') }}" class="" style="width: 80px;">
                            <img src="{{ asset('../images/logo/BaRG-logo.png') }}" alt="site icon">
                        </a>
                        <div class="button-show-hide">
                            <i class="icon-menu-left"></i>
                        </div>
                    </div>
                    <div class="center">
                        <div class="center-item">
                            <div class="center-heading">Main Home</div>
                            <ul class="menu-list">
                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="">
                                        <div class="icon"><i class="icon-grid"></i></div>
                                        <div class="text">Dashboard</div>
                                    </a>

                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.index') }}">
                                                <div class="text">Products Dashboard</div>
                                            </a>
                                        </li>

                                        <li class="sub-menu-item">
                                            <a href="{{ route('admin.facilities.dashboard') }}">
                                                <div class="text">Facilities Dashboard</div>
                                            </a>
                                        </li>
                                    </ul>


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
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Facilities</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.addons') }}">
                                                    <div class="text">Manage Add-ons</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.addons.create') }}">
                                                    <div class="text">Create Add-ons</div>
                                                </a>
                                            </li>
                                            <li class="divider mb-10"></li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('discounts.index') }}">
                                                    <div class="text">Manage Discounts</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('discounts.create') }}">
                                                    <div class="text">Create Discount</div>
                                                </a>
                                            </li>
                                            <li class="divider mb-10"></li>
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
                                    <li class="menu-item">
                                        <a href="{{ route('admin.facilities.reservations') }}">
                                            <div class="icon"><i class="icon-file-plus"></i></div>
                                            <div class="text">Facility Reservation</div>
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

                                    {{-- <li class="menu-item">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                        <a href="{{ route('admin.users') }}">
                                            <div class="icon"><i class="icon-users"></i></div>

                                            <div class="text">Users</div>
                                        </a>
                                    </li> --}}

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            {{-- <a href="{{ route('admin.users') }}"> --}}
                                            <div class="icon"><i class="icon-users"></i></div>
                                            <div class="text">Users</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.users') }}">
                                                    <div class="text">Users</div>
                                                </a>
                                            </li>

                                            <li class="divider mb-10"></li>

                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.colleges.index') }}">
                                                    <div class="text">Manage Colleges</div>
                                                </a>
                                            </li>


                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.colleges.create') }}">
                                                    <div class="text">Add Colleges</div>
                                                </a>
                                            </li>


                                            <li class="divider mb-10"></li>

                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.courses.index') }}">
                                                    <div class="text">Manage Courses</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.courses.create') }}">
                                                    <div class="text">Add Courses</div>
                                                </a>
                                            </li>

                                        </ul>
                                    </li>


                                    {{-- <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">

                                            <div class="icon"><i class="icon-settings"></i></div>
                                            <div class="text">College</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.colleges.index') }}">
                                                    <div class="text">Colleges</div>
                                                </a>
                                            </li>
                                        </ul>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.colleges.create') }}">
                                                    <div class="text">Add Colleges</div>
                                                </a>
                                            </li>
                                        </ul>

                                    </li> --}}

                                    {{-- <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">

                                            <div class="icon"><i class="icon-book-open"></i></div>
                                            <div class="text">Course</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.courses.index') }}">
                                                    <div class="text">Courses</div>
                                                </a>
                                            </li>
                                        </ul>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.courses.create') }}">
                                                    <div class="text">Add Courses</div>
                                                </a>
                                            </li>
                                        </ul>

                                    </li> --}}


                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">

                                            <div class="icon"><i class="icon-pen-tool"></i></div>
                                            <div class="text">Signature</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.signatures.index') }}">
                                                    <div class="text">Manage Signature</div>
                                                </a>
                                            </li>
                                        </ul>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.signatures.create') }}">
                                                    <div class="text">Add Signature</div>
                                                </a>
                                            </li>
                                        </ul>

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
                                                    <div class="text">Sales Charts</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-statements') }}">
                                                    <div class="text">Sales</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report-product') }}">
                                                    <div class="text">Products</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.report.product-list') }}">
                                                    <div class="text">Product List</div>
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

                                            {{-- <li class="sub-menu-item">
                                                <a href="{{ route('admin.generate-input-users') }}">
                                                    <div class="text">Input User</div>
                                                </a>
                                            </li> --}}

                                            {{-- <li class="sub-menu-item">
                                                <a href="{{ route('admin.generate-input-sales') }}">
                                                    <div class="text">Input Sales</div>
                                                </a>
                                            </li> --}}
                                        </ul>
                                    </li>

                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="">
                                            <div class="icon"><i class="icon-bar-chart-2"></i></div>
                                            <div class="text">Facilties Reports</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="menu-item">
                                                <a href="{{ route('admin.facility-statement') }}">
                                                    <div class="text">Sales</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                {{-- <a href="{{ route('admin.facilities.analytics') }}">
                                                    <div class="text">Facilities Analytics</div>
                                                </a> --}}
                                            </li>




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
                                {{-- <form class="form-search flex-grow" id="searchForm"
                                    action="{{ route('admin.searchproduct') }}" method="GET">
                                    <fieldset class="name">
                                        <input type="text" placeholder="Search here..." class="show-search"
                                            name="query" id="searchQuery" required />
                                    </fieldset>
                                    <div class="button-submit">
                                        <button type="submit"><i class="icon-search"></i></button>
                                    </div>
                                </form> --}}
                            </div>
                            <div class="header-grid">
                                <div class="popup-wrap message type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-primary dropdown-toggle" type="button"
                                            id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-item">
                                                <span
                                                    class="text-tiny notification-count">{{ Auth::user()->unreadNotifications()->count() }}</span>
                                                <i class="fas fa-bell"></i>
                                            </span>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="dropdownMenuButton2">
                                            <div class="dropdown-header">
                                                <div class="notification-actions">
                                                    <button type="button" id="markAllReadBtn" class="mark-read">Mark
                                                        all as read</button>
                                                    <button type="button" class="remove-all">Remove all</button>
                                                </div>
                                            </div>

                                            <div id="notification-list">
                                                @if (Auth::user()->unreadNotifications->isEmpty())
                                                    <div class="notification-item">
                                                        <div class="notification-content">
                                                            <p class="notification-text text-center">No notifications
                                                            </p>
                                                        </div>
                                                    </div>
                                                @else
                                                    @foreach (Auth::user()->unreadNotifications->take(5) as $notification)
                                                        <div class="notification-item"
                                                            data-notification-id="{{ $notification->id }}">
                                                            <div class="badge-icon h5">
                                                                <i
                                                                    class="{{ $notification->data['icon'] ?? 'fas fa-envelope' }} text-dark"></i>
                                                            </div>
                                                            <div class="notification-content">
                                                                <p class="notification-text fw-bold">
                                                                    {{ $notification->data['title'] ?? ($notification->data['name'] ?? 'Notification') }}
                                                                </p>
                                                                <p class="notification-subtext">
                                                                <div class="unread-indicator"></div>
                                                                {{ Str::limit($notification->data['body'] ?? ($notification->data['message'] ?? 'No message'), 30) }}
                                                                </p>
                                                            </div>
                                                            <div class="remove-notification"
                                                                data-id="{{ $notification->id }}">
                                                                <i class="fas fa-times"></i>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div id="all-notification-list"
                                                class="notification-list-container all-notifications"
                                                style="display: none;">
                                                <!-- All notifications will be loaded here dynamically -->
                                            </div>
                                            <div class="dropdown-footer">
                                                <button id="toggle-notifications" class="btn">See all
                                                    notifications</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                searchResults.innerHTML = '';

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
    <script>
        window.userId = @json(Auth::id());
    </script>
</body>

</html>
