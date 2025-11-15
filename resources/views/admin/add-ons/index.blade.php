@extends('layouts.admin')
@section('content')
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
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" method="GET" action="{{ route('admin.addons') }}">
                            <fieldset class="name">
                                <input type="text" placeholder="Search addons..." class="search-input" name="search"
                                    tabindex="2" value="{{ request('search') }}" aria-required="true">
                            </fieldset>
                            <button type="submit" style="display: none"></button>
                        </form>
                    </div>

                    <div class="action-buttons">
                        <a class="tf-button w-auto" href="{{ route('admin.addons.create') }}"><i class="icon-plus"></i>Add
                            New Add-on</a>
                        <a class="tf-button w-auto" href="{{ route('admin.addons.archive') }}">
                            <i class="icon-archive"></i> Archived Add-ons
                        </a>
                    </div>

                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        <!-- Mobile Card View -->
                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($addons as $addon)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title">{{ $addon->name }}</h5>
                                        <span
                                            class="badge {{ $addon->is_available ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $addon->is_available ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </div>
                                    <div class="mobile-card-body">
                                        <div class="mobile-card-details">
                                            <p><strong>Price:</strong> ₱{{ number_format($addon->base_price, 2) }}
                                                ({{ ucfirst(str_replace('_', ' ', $addon->price_type)) }})
                                            </p>
                                            <p><strong>Type:</strong>
                                                {{ $addon->is_based_on_quantity ? 'Quantity-based' : 'Flat rate' }}</p>
                                            <p><strong>Refundable:</strong> {{ $addon->is_refundable ? 'Yes' : 'No' }}</p>
                                            <p><strong>Show:</strong>
                                                <span
                                                    class="badge {{ $addon->show == 'staff' ? 'badge-purple' : 'badge-info' }}">
                                                    {{ ucfirst($addon->show) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mobile-card-actions">
                                            {{-- {{ route('admin.addons.edit', $addon->id) }} --}}
                                            <a href="{{ route('admin.addons.edit', $addon->id) }}"
                                                class="btn btn-sm btn-primary mobile-btn">
                                                <i class="icon-edit-3"></i> Edit
                                            </a>
                                            {{-- {{ route('admin.addons.destroy', $addon->id) }} --}}
                                            <form action=" {{ route('admin.addons.destroy', $addon->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning delete mobile-btn">
                                                    <i class="icon-archive"></i> Archive
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="icon-package"></i>
                                    </div>
                                    <h4>No Addons Found</h4>
                                    <p>Start by creating your first addon.</p>
                                    <a href="{{ route('admin.addons.create') }}" class="btn btn-primary">
                                        <i class="icon-plus"></i> Add New Addon
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
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
                                    {{-- <th scope="col" class="col-show">Show</th> --}}
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($addons as $addon)
                                    <tr>
                                        <td class="name-cell">
                                            <div class="name text-truncate" title="{{ $addon->name }}">
                                                <strong>{{ $addon->name }}</strong>
                                            </div>
                                            @if ($addon->description)
                                                <div class="text-tiny text-muted text-truncate"
                                                    title="{{ $addon->description }}">
                                                    {{ Str::limit($addon->description, 50) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="facility-cell">
                                            @if ($addon->facility)
                                                <span class="text-primary">{{ $addon->facility->name }}</span>
                                                <div class="text-tiny text-muted">ID: {{ $addon->facility->id }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="price-cell">
                                            ₱ {{ number_format($addon->base_price, 2) }}
                                        </td>
                                        <td class="type-cell">
                                            {{ ucfirst(str_replace('_', ' ', $addon->price_type)) }}
                                        </td>
                                        <td class="quantity-cell">
                                            <span
                                                class="badge {{ $addon->is_based_on_quantity ? 'badge-info' : 'badge-secondary' }}">
                                                {{ $addon->is_based_on_quantity ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="status-cell">
                                            <span
                                                class="badge {{ $addon->is_available ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $addon->is_available ? 'Available' : 'Unavailable' }}
                                            </span>
                                        </td>
                                        <td class="refundable-cell">
                                            <span
                                                class="badge {{ $addon->is_refundable ? 'badge-warning' : 'badge-secondary' }}">
                                                {{ $addon->is_refundable ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        {{-- <td class="show-cell">
                                            <span
                                                class="badge {{ $addon->show == 'staff' ? 'badge-purple' : 'badge-info' }}">
                                                {{ ucfirst($addon->show) }}
                                            </span>
                                        </td> --}}
                                        <td class="action-cell">
                                            <div class="list-icon-function">
                                                {{-- {{ route('admin.addons.edit', $addon->id) }} --}}
                                                <a href="{{ route('admin.addons.edit', $addon->id) }}" title="Edit Addon">
                                                    <div class="item edit">
                                                        <i class="icon-edit-3"></i>
                                                    </div>
                                                </a>
                                                {{-- {{ route('admin.addons.destroy', $addon->id) }} --}}
                                                <form action="{{ route('admin.addons.destroy', $addon->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="item text-warning delete"
                                                        style="border: none; background: none;" title="Delete Addon">
                                                        <i class="icon-archive"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center empty-state-table">
                                            <div class="empty-icon">
                                                <i class="icon-package"></i>
                                            </div>
                                            <h5>No Addons Found</h5>
                                            <p>Start by creating your first addon.</p>
                                            <a href="{{ route('admin.addons.create') }}" class="btn btn-primary">
                                                <i class="icon-plus"></i> Add New Addon
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <!-- Fixed pagination section -->
                    <div class="pagination-container" style="padding: 15px 0; overflow: visible; min-height: 60px;">
                        {{ $addons->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Base Responsive Layout */
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

        /* Search and Action Button Styles */
        .search-input {
            width: 100%;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .add-button {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            white-space: nowrap;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            text-decoration: none;
        }

        .add-button:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
            color: white;
        }

        /* Badge Styles */
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

        /* Desktop Table Styles */
        .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        .col-name {
            width: 25%;
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

        .col-show {
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

        /* Mobile Card Styles */
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

        .mobile-btn.btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-1px);
        }

        .mobile-btn.btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .mobile-btn.btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
        }

        /* Empty State Styles */
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

        /* Pagination Fixes */
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

        /* Show pagination info */
        .pagination-info {
            width: 100%;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #6b7280;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
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

            .col-show {
                width: 10%;
            }

            .col-action {
                width: 15%;
            }
        }

        @media (max-width: 768px) {
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
        }

        @media (max-width: 576px) {
            .mobile-card-actions {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-end;
            }

            .add-button {
                width: 100%;
                justify-content: center;
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

        /* Print Styles */
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
        /* SweetAlert2 Custom Styles - Clean Version */
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

        /* Custom Icons using ::before pseudo-element */
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

        /* Success Icon */
        .swal2-popup.swal2-success::before {
            content: "✓" !important;
            color: #22c55e !important;
            border: 4px solid #22c55e !important;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
        }

        /* Error Icon */
        .swal2-popup.swal2-error::before {
            content: "✕" !important;
            color: #ef4444 !important;
            border: 4px solid #ef4444 !important;
            background: linear-gradient(135deg, #fef2f2, #fecaca) !important;
        }

        /* Info Icon */
        .swal2-popup.swal2-info::before {
            content: "i" !important;
            color: #3b82f6 !important;
            border: 4px solid #3b82f6 !important;
            background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
            font-style: italic !important;
            font-size: 36px !important;
        }

        /* Question Icon */
        .swal2-popup.swal2-question::before {
            content: "?" !important;
            color: #8b5cf6 !important;
            border: 4px solid #8b5cf6 !important;
            background: linear-gradient(135deg, #faf5ff, #ede9fe) !important;
            font-size: 36px !important;
        }

        /* Warning Icon - FIXED */
        .swal2-popup.swal2-warning::before {
            content: "!" !important;
            color: #f59e0b !important;
            border: 4px solid #f59e0b !important;
            background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
            font-size: 36px !important;
        }

        /* Hide default SweetAlert2 icons */
        .swal2-icon {
            display: none !important;
        }

        /* Animation for icons */
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

        /* Responsive Adjustments */
        @media (max-width: 767px) {
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

        @media (max-width: 575px) {
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

        @media (max-width: 400px) {
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

        /* Extra Large Screens */
        @media (min-width: 1400px) {
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
        $(function() {
            // Enhanced SweetAlert2 configuration
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

            // Success message display
            @if (Session::has('success'))
                Swal.fire({
                    ...customSwalConfig,
                    // icon: 'success',
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

            // Delete confirmation with enhanced modal
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
                        // Show loading state
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

            // Auto-submit search with debounce
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

            // Enhanced search functionality
            $('.search-input').on('focus', function() {
                $(this).css({
                    'border-color': '#3498db',
                    'box-shadow': '0 0 0 3px rgba(52, 152, 219, 0.15)',
                    'transform': 'translateY(-1px)'
                });
            }).on('blur', function() {
                $(this).css({
                    'border-color': '#ddd',
                    'box-shadow': 'none',
                    'transform': 'translateY(0)'
                });
            });

            // Add loading states to buttons
            $('.add-button').on('click', function() {
                const $this = $(this);
                const originalHtml = $this.html();

                $this.html('<i class="icon-loader"></i> Loading...')
                    .prop('disabled', true)
                    .css('opacity', '0.7');

                // Reset after a delay (for page navigation)
                setTimeout(() => {
                    $this.html(originalHtml)
                        .prop('disabled', false)
                        .css('opacity', '1');
                }, 2000);
            });

            // Mobile card hover effects
            $('.mobile-card').on('touchstart mouseenter', function() {
                $(this).css('transform', 'translateY(-3px)');
            }).on('touchend mouseleave', function() {
                $(this).css('transform', 'translateY(0)');
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('.search-input').focus();
                }

                // Ctrl/Cmd + N for new addon
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    window.location.href = "{{ route('admin.addons.create') }}";
                }
            });

            // Table row hover effects for desktop
            $('.table tbody tr').on('mouseenter', function() {
                $(this).css({
                    'background-color': '#f8fafc',
                    'transform': 'scale(1.01)',
                    'box-shadow': '0 2px 8px rgba(0,0,0,0.1)'
                });
            }).on('mouseleave', function() {
                $(this).css({
                    'background-color': '',
                    'transform': 'scale(1)',
                    'box-shadow': ''
                });
            });

            // Enhanced action button animations
            $('.list-icon-function .item').on('mouseenter', function() {
                $(this).css({
                    'transform': 'translateY(-2px) scale(1.1)',
                    'box-shadow': '0 4px 12px rgba(0,0,0,0.15)'
                });
            }).on('mouseleave', function() {
                $(this).css({
                    'transform': 'translateY(0) scale(1)',
                    'box-shadow': ''
                });
            });

            // Responsive adjustments
            function handleResize() {
                const windowWidth = $(window).width();

                if (windowWidth <= 768) {
                    $('.table').hide();
                    $('.mobile-cards').show();
                } else {
                    $('.table').show();
                    $('.mobile-cards').hide();
                }

                // Ensure pagination is always visible
                $('.pagination-container').css('display', 'flex');
            }

            $(window).on('resize', handleResize);
            handleResize();

            // Add smooth transitions to all interactive elements
            $('.mobile-btn, .list-icon-function .item, .add-button').css({
                'transition': 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
            });

            // Accessibility improvements
            $('.mobile-btn, .list-icon-function .item').on('focus', function() {
                $(this).css({
                    'outline': '2px solid #3498db',
                    'outline-offset': '2px'
                });
            }).on('blur', function() {
                $(this).css('outline', 'none');
            });

            // Progressive enhancement for touch devices
            if ('ontouchstart' in window) {
                $('.mobile-card').css('cursor', 'pointer');
                $('.list-icon-function .item').css('cursor', 'pointer');
            }
        });
    </script>
@endpush
