@extends('layouts.admin')

@section('content')
    <div id="loading-indicator" class="loading-indicator">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Add-ons</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Add-ons</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" onsubmit="return false;">
                            <fieldset class="name">
                                <input type="text" id="addon-search"
                                    placeholder="Search addons by name, description, facility..." name="search"
                                    aria-required="true" value="{{ request('search') }}">
                            </fieldset>
                            <div class="button-submit">
                                <button type="button" style="display:none;">
                                    <i class="icon-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="filter-toggle-section d-flex align-items-center gap-3">
                        <span class="badge bg-primary fs-6 py-2 px-3" id="activeFiltersCount" style="display:none;">0
                            filters</span>
                        <button class="btn btn-outline-primary btn-lg position-relative" id="filterToggle" type="button">
                            <i class="icon-filter me-1"></i>
                            Filters
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="filterBadge" style="display:none;">0</span>
                        </button>
                        <button class="btn btn-outline-secondary" id="clearAllFilters" style="display:none;">
                            <i class="icon-x-circle me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Filter Container -->
                <div class="collapse mb-4" id="filterContainer">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Facility</label>
                                        <select name="facility" id="facility" class="filter-select form-select">
                                            <option value="">All Facilities</option>
                                            @foreach ($facilities as $facility)
                                                <option value="{{ $facility->id }}"
                                                    {{ request('facility') == $facility->id ? 'selected' : '' }}>
                                                    {{ $facility->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Price Type</label>
                                        <select name="price_type" id="price_type" class="filter-select form-select">
                                            <option value="">All Price Types</option>
                                            <option value="per_unit"
                                                {{ request('price_type') == 'per_unit' ? 'selected' : '' }}>Per Unit
                                            </option>
                                            <option value="flat_rate"
                                                {{ request('price_type') == 'flat_rate' ? 'selected' : '' }}>Flat Rate
                                            </option>
                                            <option value="per_night"
                                                {{ request('price_type') == 'per_night' ? 'selected' : '' }}>Per Night
                                            </option>
                                            <option value="per_item"
                                                {{ request('price_type') == 'per_item' ? 'selected' : '' }}>Per Item
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Availability</label>
                                        <select name="availability" id="availability" class="filter-select form-select">
                                            <option value="">All Status</option>
                                            <option value="available"
                                                {{ request('availability') == 'available' ? 'selected' : '' }}>Available
                                            </option>
                                            <option value="unavailable"
                                                {{ request('availability') == 'unavailable' ? 'selected' : '' }}>
                                                Unavailable</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Refundable</label>
                                        <select name="refundable" id="refundable" class="filter-select form-select">
                                            <option value="">All</option>
                                            <option value="yes" {{ request('refundable') == 'yes' ? 'selected' : '' }}>
                                                Refundable</option>
                                            <option value="no" {{ request('refundable') == 'no' ? 'selected' : '' }}>
                                                Non-Refundable</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Billing Cycle</label>
                                        <select name="billing_cycle" id="billing_cycle" class="filter-select form-select">
                                            <option value="">All Cycles</option>
                                            <option value="per_day"
                                                {{ request('billing_cycle') == 'per_day' ? 'selected' : '' }}>Per Day
                                            </option>
                                            <option value="per_contract"
                                                {{ request('billing_cycle') == 'per_contract' ? 'selected' : '' }}>Per
                                                Contract</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="filter-group">
                                        <label class="text-muted small mb-2 d-block">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="filter-select form-select">
                                            <option value="newest"
                                                {{ request('sort_by', 'newest') == 'newest' ? 'selected' : '' }}>Newest
                                                First</option>
                                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>
                                                Oldest First</option>
                                            <option value="name_asc"
                                                {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)
                                            </option>
                                            <option value="name_desc"
                                                {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)
                                            </option>
                                            <option value="price_low"
                                                {{ request('sort_by') == 'price_low' ? 'selected' : '' }}>Price: Low to
                                                High</option>
                                            <option value="price_high"
                                                {{ request('sort_by') == 'price_high' ? 'selected' : '' }}>Price: High to
                                                Low</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-4 gap-3">
                                <button class="btn btn-primary btn-lg" id="applyFilters">
                                    <i class="icon-filter me-1"></i> Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-lg" id="resetFilters">
                                    <i class="icon-refresh-cw me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div class="active-filters-row mb-4" id="activeFiltersRow" style="display:none;">
                    <div class="card border-0 bg-light">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="text-muted fs-6 fw-medium">Active filters:</span>
                                <div class="filter-tags d-flex gap-2 flex-wrap" id="filterTags"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="tf-button w-auto" href="{{ route('admin.addons.create') }}">
                            <i class="icon-plus"></i>Add New Add-on
                        </a>
                        <a class="tf-button w-auto" href="{{ route('admin.addons.archive') }}">
                            <i class="icon-archive"></i> Archived Add-ons
                        </a>
                    </div>
                </div>

                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif
                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        <div class="mobile-cards d-block d-md-none" id="js-addons-mobile-target">
                            @include('partials._addons-mobile', ['addons' => $addons])
                        </div>

                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-name">Name</th>
                                    <th scope="col" class="col-facility">Facility</th>
                                    <th scope="col" class="col-price">Price</th>
                                    <th scope="col" class="col-type">Price Type</th>
                                    <th scope="col" class="col-quantity">Quantity Based</th>
                                    <th scope="col" class="col-status">Status</th>
                                    <th scope="col" class="col-refundable">Refundable</th>
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody id="js-addons-partial-target">
                                @include('partials._addons-table', ['addons' => $addons])
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="pagination-container" id="js-addons-partial-target-pagination"
                        style="padding:15px 0;overflow:visible;min-height:60px;">
                        @include('partials._addons-pagination', ['addons' => $addons])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .loading-indicator {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .main-content-inner {
            padding: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }

        .gap20 {
            gap: 1rem;
        }

        .mb-27 {
            margin-bottom: 1.5rem;
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            min-width: 150px;
            background: #fff;
            transition: border-color 0.2s;
        }

        .filter-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .filter-tag-enhanced {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .filter-tag-enhanced:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .filter-tag-enhanced .btn-close {
            background-color: #fff;
            color: #dc3545;
            border-radius: 50%;
            width: 1.4rem;
            height: 1.4rem;
            padding: 0;
            opacity: 0.8;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
        }

        .filter-tag-enhanced .btn-close:hover {
            background-color: #fff;
            color: #b02a37;
            opacity: 1;
            transform: scale(1.1);
        }

        .search-input,
        .form-search input {
            width: 100%;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus,
        .form-search input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1.2;
            vertical-align: middle;
        }

        .badge-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .badge-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .badge-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .badge-purple {
            background: linear-gradient(135deg, #6a11cb, #a4508b);
        }

        .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        .col-name {
            width: 25%;
        }

        .col-facility {
            width: 13%;
        }

        .col-price {
            width: 10%;
        }

        .col-type {
            width: 12%;
        }

        .col-quantity {
            width: 10%;
        }

        .col-status {
            width: 10%;
        }

        .col-refundable {
            width: 10%;
        }

        .col-action {
            width: 20%;
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
            color: #1e293b;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .list-icon-function {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .list-icon-function .item {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .list-icon-function .edit {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .list-icon-function .edit:hover {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .list-icon-function .delete {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .list-icon-function .delete:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .mobile-card-header {
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mobile-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            flex: 1;
            min-width: 0;
            word-wrap: break-word;
        }

        .mobile-card-body {
            padding: 16px;
        }

        .mobile-card-details {
            margin-bottom: 16px;
        }

        .mobile-card-details p {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #475569;
        }

        .mobile-card-actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .mobile-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            flex: 1;
            justify-content: center;
        }

        .mobile-btn.btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .mobile-btn.btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .mobile-btn.btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .empty-state,
        .empty-state-table {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .empty-icon {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state h4,
        .empty-state-table h5 {
            color: #475569;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .empty-state p,
        .empty-state-table p {
            color: #64748b;
            margin-bottom: 20px;
        }

        .empty-state .btn,
        .empty-state-table .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .empty-state .btn:hover,
        .empty-state-table .btn:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px 0;
            overflow: visible !important;
            min-height: 60px;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .pagination li {
            display: inline-block;
            margin: 0;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-color: #3498db;
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }

        .pagination .disabled .page-link {
            background-color: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
            cursor: not-allowed;
            transform: none;
        }

        .pagination-info {
            width: 100%;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width:991px) {
            .col-name {
                width: 20%;
            }

            .col-price {
                width: 10%;
            }

            .col-type {
                width: 15%;
            }

            .col-quantity {
                width: 10%;
            }

            .col-status {
                width: 10%;
            }

            .col-refundable {
                width: 10%;
            }

            .col-action {
                width: 15%;
            }
        }

        @media (max-width:768px) {
            .mobile-cards {
                display: block;
            }

            .d-md-table {
                display: none !important;
            }

            .action-buttons {
                width: 100%;
                justify-content: flex-end;
            }

            .pagination {
                gap: 5px;
            }

            .pagination .page-link {
                min-width: 36px;
                height: 36px;
                padding: 0 8px;
                font-size: 13px;
            }

            .filter-tag-enhanced {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        @media (max-width:576px) {
            .mobile-card-actions {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-end;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .pagination-info {
                order: -1;
                margin-bottom: 10px;
            }
        }

        @media print {

            .action-buttons,
            .wg-filter,
            .list-icon-function,
            .mobile-card-actions {
                display: none !important;
            }

            .table {
                display: table !important;
            }

            .mobile-cards {
                display: none !important;
            }

            .wg-box,
            .mobile-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }

            .pagination {
                display: none !important;
            }
        }
    </style>
@endpush

@push('styles')
    <style>
        .swal2-popup {
            width: 90vw !important;
            max-width: 600px !important;
            min-height: 350px !important;
            padding: 35px !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25) !important;
            backdrop-filter: blur(10px) !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.95)) !important;
        }

        .swal2-title {
            font-size: 24px !important;
            font-weight: 700 !important;
            margin: 0 0 25px 0 !important;
            text-align: center !important;
            line-height: 1.3 !important;
            color: #1e293b !important;
        }

        .swal2-content {
            font-size: 16px !important;
            line-height: 1.6 !important;
            margin: 25px 0 35px 0 !important;
            text-align: center !important;
            color: #475569 !important;
        }

        .swal2-actions {
            margin: 35px 0 0 0 !important;
            gap: 15px !important;
            justify-content: center !important;
        }

        .swal2-confirm,
        .swal2-cancel {
            font-size: 15px !important;
            font-weight: 600 !important;
            padding: 12px 30px !important;
            min-width: 120px !important;
            height: 45px !important;
            border-radius: 8px !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3) !important;
        }

        .swal2-confirm:hover {
            background: linear-gradient(135deg, #d97706, #b45309) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4) !important;
        }

        .swal2-cancel {
            background: #f8fafc !important;
            color: #64748b !important;
            border: 2px solid #cbd5e1 !important;
        }

        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            transform: translateY(-1px) !important;
        }

        .swal2-popup::before {
            content: '' !important;
            display: block !important;
            text-align: center !important;
            margin: 20px auto 30px auto !important;
            width: 80px !important;
            height: 80px !important;
            line-height: 80px !important;
            border-radius: 50% !important;
            font-size: 32px !important;
            font-weight: 900 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            animation: swalIconPulse 1s ease-in-out !important;
        }

        .swal2-popup.swal2-success::before {
            content: "✓" !important;
            color: #22c55e !important;
            border: 4px solid #22c55e !important;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
        }

        .swal2-popup.swal2-error::before {
            content: "✕" !important;
            color: #ef4444 !important;
            border: 4px solid #ef4444 !important;
            background: linear-gradient(135deg, #fef2f2, #fecaca) !important;
        }

        .swal2-popup.swal2-info::before {
            content: "i" !important;
            color: #3b82f6 !important;
            border: 4px solid #3b82f6 !important;
            background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
            font-style: italic !important;
            font-size: 36px !important;
        }

        .swal2-popup.swal2-question::before {
            content: "?" !important;
            color: #8b5cf6 !important;
            border: 4px solid #8b5cf6 !important;
            background: linear-gradient(135deg, #faf5ff, #ede9fe) !important;
            font-size: 36px !important;
        }

        .swal2-popup.swal2-warning::before {
            content: "!" !important;
            color: #f59e0b !important;
            border: 4px solid #f59e0b !important;
            background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
            font-size: 36px !important;
        }

        .swal2-icon {
            display: none !important;
        }

        @keyframes swalIconPulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (max-width:767px) {
            .swal2-popup {
                width: 95vw !important;
                max-width: none !important;
                margin: 10px !important;
                padding: 25px !important;
                min-height: 300px !important;
            }

            .swal2-title {
                font-size: 20px !important;
                margin-bottom: 20px !important;
            }

            .swal2-content {
                font-size: 14px !important;
                margin: 20px 0 25px 0 !important;
            }

            .swal2-actions {
                flex-direction: column !important;
                width: 100% !important;
                margin-top: 25px !important;
                gap: 10px !important;
            }

            .swal2-confirm,
            .swal2-cancel {
                width: 100% !important;
                margin: 0 !important;
            }

            .swal2-popup::before {
                width: 70px !important;
                height: 70px !important;
                line-height: 70px !important;
                font-size: 28px !important;
                margin: 15px auto 25px auto !important;
            }

            .swal2-popup.swal2-info::before,
            .swal2-popup.swal2-question::before,
            .swal2-popup.swal2-warning::before {
                font-size: 30px !important;
            }
        }

        @media (max-width:575px) {
            .swal2-popup {
                padding: 20px !important;
                min-height: 280px !important;
            }

            .swal2-title {
                font-size: 18px !important;
            }

            .swal2-content {
                font-size: 13px !important;
            }

            .swal2-popup::before {
                width: 60px !important;
                height: 60px !important;
                line-height: 60px !important;
                font-size: 24px !important;
            }

            .swal2-popup.swal2-info::before,
            .swal2-popup.swal2-question::before,
            .swal2-popup.swal2-warning::before {
                font-size: 26px !important;
            }
        }

        @media (max-width:400px) {
            .swal2-popup {
                padding: 15px !important;
                min-height: 260px !important;
            }

            .swal2-title {
                font-size: 16px !important;
            }

            .swal2-content {
                font-size: 12px !important;
            }

            .swal2-popup::before {
                width: 55px !important;
                height: 55px !important;
                line-height: 55px !important;
                font-size: 20px !important;
            }

            .swal2-popup.swal2-info::before,
            .swal2-popup.swal2-question::before,
            .swal2-popup.swal2-warning::before {
                font-size: 22px !important;
            }
        }

        @media (min-width:1400px) {
            .swal2-popup {
                max-width: 700px !important;
                min-height: 400px !important;
                padding: 45px !important;
            }

            .swal2-title {
                font-size: 28px !important;
                margin-bottom: 30px !important;
            }

            .swal2-content {
                font-size: 18px !important;
                margin: 30px 0 40px 0 !important;
            }

            .swal2-actions {
                margin-top: 40px !important;
            }

            .swal2-popup::before {
                width: 90px !important;
                height: 90px !important;
                line-height: 90px !important;
                font-size: 38px !important;
                margin: 25px auto 35px auto !important;
            }

            .swal2-popup.swal2-info::before,
            .swal2-popup.swal2-question::before,
            .swal2-popup.swal2-warning::before {
                font-size: 42px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let lastScrollPosition = 0;
            let searchTimeout = null;

            $('#filterToggle').on('click', function() {
                const container = $('#filterContainer');
                const icon = $(this).find('i');

                if (container.hasClass('show')) {
                    container.removeClass('show').slideUp(300);
                    icon.removeClass('icon-x-circle').addClass('icon-filter');
                } else {
                    container.addClass('show').hide().slideDown(300);
                    icon.removeClass('icon-filter').addClass('icon-x-circle');
                }
            });

            $('#addon-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performFilter();
                }, 500);
            });

            function performFilter() {
                lastScrollPosition = $(window).scrollTop();

                const search = $('#addon-search').val();
                const facility = $('#facility').val();
                const priceType = $('#price_type').val();
                const availability = $('#availability').val();
                const refundable = $('#refundable').val();
                const billingCycle = $('#billing_cycle').val();
                const sortBy = $('#sort_by').val();

                showLoadingState(true);

                let url = '{{ route('admin.addons') }}';
                let params = [];

                if (search) params.push('search=' + encodeURIComponent(search));
                if (facility) params.push('facility=' + encodeURIComponent(facility));
                if (priceType) params.push('price_type=' + encodeURIComponent(priceType));
                if (availability) params.push('availability=' + encodeURIComponent(availability));
                if (refundable) params.push('refundable=' + encodeURIComponent(refundable));
                if (billingCycle) params.push('billing_cycle=' + encodeURIComponent(billingCycle));
                if (sortBy && sortBy !== 'newest') params.push('sort_by=' + encodeURIComponent(sortBy));

                if (params.length > 0) url += '?' + params.join('&');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#js-addons-partial-target').html(response.addons);
                        $('#js-addons-mobile-target').html(response.mobile);
                        $('#js-addons-partial-target-pagination').html(response.pagination);
                        showLoadingState(false);
                        window.history.pushState({}, '', url);
                        initPaginationEvents();
                        initArchiveButtons();
                        updateActiveFiltersDisplay();
                        $(window).scrollTop(lastScrollPosition);
                        showNotification('Found ' + response.count + ' add-on(s)', 'info', 2000);
                    },
                    error: function() {
                        showLoadingState(false);
                        showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            }

            function showLoadingState(isLoading) {
                if (isLoading) {
                    $('#loading-indicator').show();
                    $('.filter-select, #addon-search').prop('disabled', true);
                    $('#applyFilters').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Loading...'
                    );
                } else {
                    $('#loading-indicator').hide();
                    $('.filter-select, #addon-search').prop('disabled', false);
                    $('#applyFilters').prop('disabled', false).html(
                        '<i class="icon-filter me-1"></i> Apply Filters'
                    );
                }
            }

            function initPaginationEvents() {
                $('.pagination a').off('click').on('click', function(e) {
                    e.preventDefault();
                    lastScrollPosition = $(window).scrollTop();
                    const url = $(this).attr('href');
                    showLoadingState(true);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            $('#js-addons-partial-target').html(response.addons);
                            $('#js-addons-mobile-target').html(response.mobile);
                            $('#js-addons-partial-target-pagination').html(response.pagination);
                            showLoadingState(false);
                            window.history.pushState({}, '', url);
                            initPaginationEvents();
                            initArchiveButtons();
                            $(window).scrollTop(lastScrollPosition);
                        },
                        error: function() {
                            showLoadingState(false);
                            showNotification('Error loading page.', 'error');
                        }
                    });
                });
            }

            function updateActiveFiltersDisplay() {
                let count = 0;
                const filterTags = $('#filterTags');
                const activeFiltersRow = $('#activeFiltersRow');
                const activeFiltersCount = $('#activeFiltersCount');
                const clearAllButton = $('#clearAllFilters');
                const filterBadge = $('#filterBadge');

                filterTags.empty();
                const urlParams = new URLSearchParams(window.location.search);

                // Search filter
                if (urlParams.get('search')) {
                    count++;
                    addFilterTag('Search: "' + urlParams.get('search') + '"', 'search');
                }

                // Facility filter
                if (urlParams.get('facility')) {
                    count++;
                    const facilityText = $('#facility option:selected').text();
                    addFilterTag('Facility: ' + facilityText, 'facility');
                }

                // Price Type filter
                if (urlParams.get('price_type')) {
                    count++;
                    const priceTypeText = $('#price_type option:selected').text();
                    addFilterTag('Price Type: ' + priceTypeText, 'price_type');
                }

                // Availability filter
                if (urlParams.get('availability')) {
                    count++;
                    const availabilityText = $('#availability option:selected').text();
                    addFilterTag('Status: ' + availabilityText, 'availability');
                }

                // Refundable filter
                if (urlParams.get('refundable')) {
                    count++;
                    const refundableText = $('#refundable option:selected').text();
                    addFilterTag('Refundable: ' + refundableText, 'refundable');
                }

                // Billing Cycle filter
                if (urlParams.get('billing_cycle')) {
                    count++;
                    const billingCycleText = $('#billing_cycle option:selected').text();
                    addFilterTag('Billing: ' + billingCycleText, 'billing_cycle');
                }

                // Sort By filter
                if (urlParams.get('sort_by') && urlParams.get('sort_by') !== 'newest') {
                    count++;
                    const sortByText = $('#sort_by option:selected').text();
                    addFilterTag('Sort: ' + sortByText, 'sort_by');
                }

                // Show/hide filter UI elements
                if (count > 0) {
                    activeFiltersCount.show().text(count + ' filter' + (count > 1 ? 's' : ''));
                    clearAllButton.show();
                    activeFiltersRow.show();
                    filterBadge.show().text(count);
                } else {
                    activeFiltersCount.hide();
                    clearAllButton.hide();
                    activeFiltersRow.hide();
                    filterBadge.hide();
                }
            }

            function addFilterTag(text, filterName) {
                const tag = $('<span class="filter-tag-enhanced">' +
                    text +
                    '<button type="button" class="btn-close icon-x text-danger" data-filter="' + filterName +
                    '" title="Remove filter"></button>' +
                    '</span>'
                );

                tag.find('.btn-close').on('click', function() {
                    const filterToRemove = $(this).data('filter');
                    if (filterToRemove === 'search') {
                        $('#addon-search').val('');
                    } else {
                        $('#' + filterToRemove).val('');
                    }
                    performFilter();
                });

                $('#filterTags').append(tag);
            }

            // Apply filters button
            $('#applyFilters').on('click', function() {
                performFilter();
            });

            // Filter change events
            $('#facility, #price_type, #availability, #refundable, #billing_cycle, #sort_by').on('change',
            function() {
                performFilter();
            });

            // Clear all filters
            $('#clearAllFilters, #resetFilters').on('click', function() {
                $('#addon-search').val('');
                $('#facility').val('');
                $('#price_type').val('');
                $('#availability').val('');
                $('#refundable').val('');
                $('#billing_cycle').val('');
                $('#sort_by').val('newest');
                performFilter();
            });

            // Archive button confirmation
            function initArchiveButtons() {
                $('.delete').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var form = $(this).closest('form');
                    var addonName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title')
                        .text().trim();

                    Swal.fire({
                        title: 'Archive Add-on?',
                        html: '<p>Are you sure you want to archive <strong>' + addonName +
                            '</strong>?</p><p class="text-muted">This can be restored later.</p>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="icon-archive"></i> Yes, Archive',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }

            // Show notification helper
            function showNotification(message, type, duration) {
                type = type || 'info';
                duration = duration || 4000;

                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';

                const notification = $('<div class="alert ' + alertClass +
                    ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );

                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, duration);
            }

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl+Enter to apply filters
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
                    e.preventDefault();
                    performFilter();
                }
                // Escape to clear all filters
                if (e.keyCode === 27) {
                    $('#clearAllFilters').click();
                }
                // Ctrl+F to open filters
                if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
                    e.preventDefault();
                    $('#filterToggle').click();
                    setTimeout(function() {
                        $('#addon-search').focus();
                    }, 100);
                }
            });

            // Initialize everything
            initPaginationEvents();
            initArchiveButtons();
            updateActiveFiltersDisplay();

            // Success message from session
            @if (Session::has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ Session::get('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            // Welcome tip for new users
            if (window.location.search === '' || !window.location.search) {
                setTimeout(function() {
                    showNotification(
                        '💡 Tip: Use Ctrl+F to open filters, Ctrl+Enter to apply, or Esc to clear all',
                        'info', 6000
                    );
                }, 1000);
            }
        });
    </script>
@endpush

@push('scripts')
    <script>
        $(function() {
            const customSwalConfig = {
                customClass: {
                    popup: 'enhanced-swal-popup',
                    title: 'enhanced-swal-title',
                    content: 'enhanced-swal-content',
                    actions: 'enhanced-swal-actions',
                    confirmButton: 'enhanced-swal-confirm',
                    cancelButton: 'enhanced-swal-cancel'
                },
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                showCloseButton: true,
                focusConfirm: false,
                reverseButtons: true,
                backdrop: true
            };

            @if (Session::has('success'))
                Swal.fire({
                    ...customSwalConfig,
                    customClass: {
                        popup: 'swal2-success'
                    },
                    title: 'Operation Successful!',
                    html: `
                        <div style="text-align: center; line-height: 1.6;">
                            <p style="font-size: 16px; color: #22c55e; margin-bottom: 15px;">
                                <strong>{{ Session::get('success') }}</strong>
                            </p>
                            <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); padding: 15px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #22c55e;">
                                <p style="margin: 0; color: #166534; font-weight: 500;">
                                    <i class="icon-check-circle" style="margin-right: 8px;"></i>
                                    The operation has been completed successfully.
                                </p>
                            </div>
                            <p style="color: #64748b; font-size: 14px; margin-top: 15px;">
                                Your changes have been saved and are now active.
                            </p>
                        </div>
                    `,
                    confirmButtonText: '<i class="icon-check"></i> Great!',
                    timer: 5000,
                    timerProgressBar: true,
                    showCloseButton: true
                });
            @endif

            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var addonName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text()
                    .trim();

                Swal.fire({
                    ...customSwalConfig,
                    title: 'Archive Add-on Confirmation',
                    html: `
                          <div style="text-align: left; line-height: 1.6;">
                                <p style="margin-bottom: 15px; text-align: center;">You are about to archive the following college:</p>
                                <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); padding: 15px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #f59e0b;">
                                    <strong style="color: #92400e; font-size: 16px;">${addonName}</strong>
                                </div>

                             <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin: 15px 0; border: 1px solid #fcd34d;">
                                    <p style="margin: 0; color: #92400e; font-size: 14px;">
                                        <i class="icon-info" style="margin-right: 8px;"></i>
                                        <strong>Note:</strong> Archiving will move this Add-on to the archived section. It can be restored later if needed.
                                    </p>
                                </div>
                                <p style="margin-top: 20px; color: #64748b; text-align: center;">
                                    This action is reversible. The Add-on will remain in the system but will be hidden from the main list.
                                </p>
                            </div>
                    `,
                    customClass: {
                        popup: 'swal2-warning'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="icon-trash-2"></i> Yes, Archive It',
                    cancelButtonText: '<i class="icon-x"></i> Cancel',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting Addon...',
                            html: `
                                <div style="text-align: center;">
                                    <div style="margin: 20px 0;">
                                        <div class="loading-spinner" style="
                                            width: 40px;
                                            height: 40px;
                                            border: 4px solid #f3f4f6;
                                            border-top: 4px solid #ef4444;
                                            border-radius: 50%;
                                            animation: spin 1s linear infinite;
                                            margin: 0 auto 15px auto;
                                        "></div>
                                        <p style="color: #64748b; margin: 0;">Please wait while we delete the addon...</p>
                                    </div>
                                </div>
                                <style>
                                    @keyframes spin {
                                        0% { transform: rotate(0deg); }
                                        100% { transform: rotate(360deg); }
                                    }
                                </style>
                            `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            showCancelButton: false
                        });
                        form.submit();
                    }
                });
            });

            let searchTimeout;
            $('input[name="search"]').on('input', function() {
                clearTimeout(searchTimeout);
                const form = $(this).closest('form');
                const query = $(this).val().trim();

                searchTimeout = setTimeout(() => {
                    if (query.length >= 2 || query.length === 0) {
                        form.submit();
                    }
                }, 500);
            });

            $('.search-input').on('focus', function() {
                $(this).css({
                    borderColor: '#3498db',
                    boxShadow: '0 0 0 3px rgba(52, 152, 219, 0.15)',
                    transform: 'translateY(-1px)'
                });
            }).on('blur', function() {
                $(this).css({
                    borderColor: '#ddd',
                    boxShadow: 'none',
                    transform: 'translateY(0)'
                });
            });

            $('.add-button').on('click', function() {
                const $this = $(this);
                const originalHtml = $this.html();

                $this.html('<i class="icon-loader"></i> Loading...')
                    .prop('disabled', true)
                    .css('opacity', '0.7');

                setTimeout(() => {
                    $this.html(originalHtml)
                        .prop('disabled', false)
                        .css('opacity', '1');
                }, 2000);
            });

            $('.mobile-card').on('touchstart mouseenter', function() {
                $(this).css('transform', 'translateY(-3px)');
            }).on('touchend mouseleave', function() {
                $(this).css('transform', 'translateY(0)');
            });

            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('.search-input').focus();
                }

                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    window.location.href = "{{ route('admin.addons.create') }}";
                }
            });

            $('.table tbody tr').on('mouseenter', function() {
                $(this).css({
                    backgroundColor: '#f8fafc',
                    transform: 'scale(1.01)',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.1)'
                });
            }).on('mouseleave', function() {
                $(this).css({
                    backgroundColor: '',
                    transform: 'scale(1)',
                    boxShadow: ''
                });
            });

            $('.list-icon-function .item').on('mouseenter', function() {
                $(this).css({
                    transform: 'translateY(-2px) scale(1.1)',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
                });
            }).on('mouseleave', function() {
                $(this).css({
                    transform: 'translateY(0) scale(1)',
                    boxShadow: ''
                });
            });

            function handleResize() {
                const windowWidth = $(window).width();

                if (windowWidth <= 768) {
                    $('.table').hide();
                    $('.mobile-cards').show();
                } else {
                    $('.table').show();
                    $('.mobile-cards').hide();
                }

                $('.pagination-container').css('display', 'flex');
            }

            $(window).on('resize', handleResize);
            handleResize();

            $('.mobile-btn, .list-icon-function .item, .add-button').css({
                transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
            });

            $('.mobile-btn, .list-icon-function .item').on('focus', function() {
                $(this).css({
                    outline: '2px solid #3498db',
                    outlineOffset: '2px'
                });
            }).on('blur', function() {
                $(this).css('outline', 'none');
            });

            if ('ontouchstart' in window) {
                $('.mobile-card').css('cursor', 'pointer');
                $('.list-icon-function .item').css('cursor', 'pointer');
            }
        });
    </script>
@endpush
