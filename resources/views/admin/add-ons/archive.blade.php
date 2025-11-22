@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Archived Add-ons</h3>
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
                        <a href="{{ route('admin.addons') }}">
                            <div class="text-tiny">Add-ons</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Archived Add-ons</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" method="GET" action="{{ route('admin.addons.archive') }}">
                            <fieldset class="name">
                                <input type="text" placeholder="Search archived addons by name or description..."
                                    class="search-input" name="search" value="{{ request('search') }}"
                                    aria-required="true">
                            </fieldset>
                            <button type="submit" class="search-submit" style="display: none">
                                <i class="icon-search"></i>
                            </button>
                        </form>
                    </div>

                    <a class="tf-button w-auto back-button" href="{{ route('admin.addons') }}">
                        <i class="icon-arrow-left"></i>
                        <span class="button-text">Back to Add-ons</span>
                    </a>
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('error'))
                            <p class="alert alert-danger">{{ Session::get('error') }}</p>
                        @endif

                        <!-- Mobile Card View -->
                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($addons as $addon)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title">{{ $addon->name }}</h5>
                                        <span class="badge badge-secondary">{{ $addon->price_type }}</span>
                                    </div>
                                    <div class="mobile-card-body">
                                        <p class="mobile-card-date">
                                            <strong>Price:</strong> ${{ number_format($addon->base_price, 2) }}
                                        </p>
                                        <p class="mobile-card-date">
                                            <strong>Role:</strong> {{ ucfirst($addon->show) }}
                                        </p>
                                        <p class="mobile-card-date">
                                            <strong>Deleted:</strong> {{ $addon->deleted_at->format('M d, Y') }}
                                        </p>
                                        <div class="mobile-card-actions">
                                            <form action="{{ route('admin.addons.restore', $addon->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success restore mobile-btn"
                                                    title="Restore">
                                                    <i class="icon-refresh-ccw"></i> Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.addons.force-delete', $addon->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger force-delete mobile-btn"
                                                    title="Permanently Delete">
                                                    <i class="icon-trash-2"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <p>No archived add-ons found.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-name">Name</th>
                                    <th scope="col" class="col-type">Price Type</th>
                                    <th scope="col" class="col-price">Price</th>
                                    <th scope="col" class="col-show">Role</th>
                                    <th scope="col" class="col-date">Deleted At</th>
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
                                        </td>
                                        <td class="type-cell">
                                            <span
                                                class="badge badge-secondary">{{ str_replace('_', ' ', $addon->price_type) }}</span>
                                        </td>
                                        <td class="price-cell">${{ number_format($addon->base_price, 2) }}</td>
                                        <td class="show-cell">{{ ucfirst($addon->show) }}</td>
                                        <td class="date-cell">{{ $addon->deleted_at->format('M d, Y') }}</td>
                                        <td class="action-cell">
                                            <div class="list-icon-function">
                                                <form action="{{ route('admin.addons.restore', $addon->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="item restore"
                                                        style="border: none; background: none;" title="Restore">
                                                        <i class="icon-refresh-ccw"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.addons.force-delete', $addon->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="item text-danger force-delete"
                                                        style="border: none; background: none;" title="Permanently Delete">
                                                        <i class="icon-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center empty-state">No archived add-ons found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $addons->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Base Responsive Styles */
        .main-content-inner {
            padding: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0;
        }

        .gap20 {
            gap: 1rem;
        }

        .mb-27 {
            margin-bottom: 1.5rem;
        }

        /* Search and Button Styles */
        .search-input {
            width: 100%;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .back-button {
            white-space: nowrap;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1.2;
            vertical-align: middle;
        }

        .badge-secondary {
            background-color: #6c757d;
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

        .col-type {
            width: 15%;
        }

        .col-price {
            width: 15%;
        }

        .col-show {
            width: 15%;
        }

        .col-date {
            width: 15%;
        }

        .col-action {
            width: 15%;
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .list-icon-function {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .list-icon-function .item {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .list-icon-function .restore {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .list-icon-function .restore:hover {
            background-color: #28a745;
            color: white;
        }

        .list-icon-function .force-delete {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .list-icon-function .force-delete:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Mobile Card Styles */
        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .mobile-card-header {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mobile-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            flex: 1;
            min-width: 0;
            word-wrap: break-word;
        }

        .mobile-card-body {
            padding: 15px;
        }

        .mobile-card-date {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }

        .mobile-card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mobile-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
        }

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

        /* Warning Icon */
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

        /* Tablet Styles */
        @media (max-width: 991.98px) {
            .main-content-inner {
                padding: 10px;
            }

            .flex-wrap {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .wg-filter {
                width: 100%;
                margin-bottom: 15px;
            }

            .back-button {
                align-self: flex-end;
            }

            .col-name {
                width: 25%;
            }

            .col-type {
                width: 15%;
            }

            .col-price {
                width: 15%;
            }

            .col-show {
                width: 15%;
            }

            .col-date {
                width: 15%;
            }

            .col-action {
                width: 15%;
            }
        }

        /* Mobile Styles */
        @media (max-width: 767.98px) {
            .page-title {
                font-size: 1.25rem;
            }

            .breadcrumbs {
                font-size: 12px;
            }

            .button-text {
                display: none;
            }

            .mobile-cards {
                display: block !important;
            }

            .table {
                display: none !important;
            }

            .search-input {
                min-width: unset;
            }

            .mobile-card-actions {
                justify-content: space-between;
            }

            .mobile-btn {
                flex: 1;
                justify-content: center;
                min-width: 0;
            }

            /* Mobile SweetAlert2 */
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

        /* Small Mobile Styles */
        @media (max-width: 575.98px) {
            .main-content-inner {
                padding: 8px;
            }

            .mobile-card {
                margin-bottom: 10px;
            }

            .mobile-card-header,
            .mobile-card-body {
                padding: 12px;
            }

            .mobile-card-title {
                font-size: 14px;
            }

            .mobile-card-date {
                font-size: 12px;
                margin-bottom: 10px;
            }

            .mobile-btn {
                font-size: 11px;
                padding: 5px 8px;
            }

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
            .main-content-inner {
                padding: 25px;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .search-input {
                font-size: 16px;
                padding: 10px 15px;
            }

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

        /* Print Styles */
        @media print {

            .back-button,
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
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            // Custom SweetAlert2 configuration
            const swalConfig = {
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content',
                    actions: 'custom-swal-actions',
                    confirmButton: 'custom-swal-confirm',
                    cancelButton: 'custom-swal-cancel'
                },
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                showCloseButton: true,
                focusConfirm: false,
                reverseButtons: true
            };

            @if (Session::has('success'))
                Swal.fire({
                    ...swalConfig,
                    icon: 'success',
                    title: 'Operation Successful!',
                    html: '<div style="text-align: center; line-height: 1.6;"><strong>{{ Session::get('success') }}</strong><br><br><small style="color: #666;  font-size: 18px;">The operation has been completed successfully.</small></div>',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Great!',
                    showCancelButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-success'
                    }
                });
            @endif

            // Force delete confirmation
            $('.force-delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var addonName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text()
                    .trim();

                Swal.fire({
                    ...swalConfig,
                    icon: 'warning',
                    title: 'Permanent Deletion Warning',
                    html: `
                        <div style="text-align: left; line-height: 1.6;">
                            <p style="margin-bottom: 15px;">You are about to permanently delete:</p>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #dc3545;">
                                <strong style="color: #dc3545;">${addonName}</strong>
                            </div>
                            <p style="color: #721c24; background: #f8d7da; padding: 12px; border-radius: 6px; margin: 15px 0;">
                                <strong>Warning:</strong> This action cannot be undone! All associated data will be permanently removed from the system.
                            </p>
                            <p style="margin-top: 20px; color: #666;">Are you absolutely sure you want to proceed?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete Permanently',
                    cancelButtonText: 'Cancel',
                    focusCancel: true,
                    customClass: {
                        popup: 'swal2-warning'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            html: 'Please wait while we permanently delete the addon.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });

            // Restore confirmation
            $('.restore').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var addonName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text()
                    .trim();

                Swal.fire({
                    ...swalConfig,
                    icon: 'question',
                    title: 'Restore Addon',
                    html: `
                        <div style="text-align: left; line-height: 1.6;">
                            <p style="margin-bottom: 15px;">You are about to restore:</p>
                            <div style="background: #d1edff; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #28a745;">
                                <strong style="color: #155724;">${addonName}</strong>
                            </div>
                            <div style="background: #d4edda; padding: 12px; border-radius: 6px; margin: 15px 0;">
                                This addon will be restored to active status and will be available for normal operations again.
                            </div>
                            <p style="margin-top: 20px; color: #666;">Do you want to proceed with the restoration?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Restore Addon',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-question'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Restoring...',
                            html: 'Please wait while we restore the addon.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });

            // Auto-submit search on input with debounce
            let searchTimeout;
            $('input[name="search"]').on('input', function() {
                clearTimeout(searchTimeout);
                const form = $(this).closest('form');
                searchTimeout = setTimeout(() => {
                    form.submit();
                }, 500);
            });

            // Handle responsive table on window resize
            $(window).on('resize', function() {
                // Force redraw to handle any layout issues
                setTimeout(() => {
                    $(window).trigger('resize');
                }, 100);
            });
        });
    </script>
@endpush
