@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Archived Positions</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <a href="{{ route('positions.index') }}">
                            <div class="text-tiny">Positions</div>
                        </a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <div class="text-tiny">Archived Positions</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" method="GET" action="{{ route('positions.archive') }}">
                            <fieldset class="name">
                                <input type="text" placeholder="Search archived positions..." class="search-input"
                                    name="search" tabindex="2" value="{{ request('search') }}" aria-required="true">
                            </fieldset>
                            <button type="submit" style="display: none"></button>
                        </form>
                    </div>

                    <a class="tf-button w-auto back-button" href="{{ route('positions.index') }}">
                        <i class="icon-arrow-left"></i>
                        <span class="button-text">Back to Positions</span>
                    </a>
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('error'))
                            <p class="alert alert-danger">{{ Session::get('error') }}</p>
                        @endif

                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($positions as $position)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title">{{ $position->name }}</h5>
                                        <span class="badge badge-secondary">{{ $position->code }}</span>
                                    </div>
                                    <div class="mobile-card-body">
                                        <p class="mobile-card-date">
                                            <strong>Deleted:</strong> {{ $position->deleted_at->format('M d, Y') }}
                                        </p>
                                        <div class="mobile-card-actions">
                                            <form action="{{ route('positions.restore', $position->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-success restore mobile-btn"
                                                    title="Restore">
                                                    <i class="icon-refresh-ccw"></i> Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('positions.forceDelete', $position->id) }}"
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
                                    <p>No archived positions found.</p>
                                </div>
                            @endforelse
                        </div>

                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-name">Name</th>
                                    <th scope="col" class="col-code">Code</th>
                                    <th scope="col" class="col-date">Deleted At</th>
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($positions as $position)
                                    <tr>
                                        <td class="name-cell">
                                            <div class="name text-truncate" title="{{ $position->name }}">
                                                <strong>{{ $position->name }}</strong>
                                            </div>
                                        </td>
                                        <td class="code-cell">
                                            <span class="badge badge-secondary">{{ $position->code }}</span>
                                        </td>
                                        <td class="date-cell">{{ $position->deleted_at->format('M d, Y') }}</td>
                                        <td class="action-cell">
                                            <div class="list-icon-function">
                                                <form action="{{ route('positions.restore', $position->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="item restore"
                                                        style="border: none; background: none;" title="Restore">
                                                        <i class="icon-refresh-ccw"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('positions.forceDelete', $position->id) }}"
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
                                        <td colspan="4" class="text-center empty-state">No archived positions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $positions->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .main-content-inner {
            padding: 15px
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0
        }

        .gap20 {
            gap: 1rem
        }

        .mb-27 {
            margin-bottom: 1.5rem
        }

        .search-input {
            width: 100%;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px
        }

        .back-button {
            white-space: nowrap;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1.2;
            vertical-align: middle
        }

        .badge-secondary {
            background-color: #6c757d
        }

        .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0
        }

        .col-name {
            width: 40%
        }

        .col-code {
            width: 20%
        }

        .col-date {
            width: 20%
        }

        .col-action {
            width: 20%
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis
        }

        .list-icon-function {
            display: flex;
            gap: 8px;
            justify-content: center
        }

        .list-icon-function .item {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .3s ease
        }

        .list-icon-function .restore {
            background-color: rgba(40, 167, 69, .1);
            color: #28a745
        }

        .list-icon-function .restore:hover {
            background-color: #28a745;
            color: #fff
        }

        .list-icon-function .force-delete {
            background-color: rgba(220, 53, 69, .1);
            color: #dc3545
        }

        .list-icon-function .force-delete:hover {
            background-color: #dc3545;
            color: #fff
        }

        .mobile-cards {
            display: none
        }

        .mobile-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1)
        }

        .mobile-card-header {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px
        }

        .mobile-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            flex: 1;
            min-width: 0;
            word-wrap: break-word
        }

        .mobile-card-body {
            padding: 15px
        }

        .mobile-card-date {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #666
        }

        .mobile-card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap
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
            transition: all .3s ease
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic
        }

        @media (max-width:991.98px) {
            .main-content-inner {
                padding: 10px
            }

            .flex-wrap {
                flex-direction: column;
                align-items: flex-start !important
            }

            .wg-filter {
                width: 100%;
                margin-bottom: 15px
            }

            .back-button {
                align-self: flex-end
            }

            .col-name {
                width: 35%
            }

            .col-code {
                width: 15%
            }

            .col-date {
                width: 25%
            }

            .col-action {
                width: 25%
            }
        }

        @media (max-width:767.98px) {
            .page-title {
                font-size: 1.25rem
            }

            .breadcrumbs {
                font-size: 12px
            }

            .button-text {
                display: none
            }

            .mobile-cards {
                display: block !important
            }

            .table {
                display: none !important
            }

            .search-input {
                min-width: unset
            }

            .mobile-card-actions {
                justify-content: space-between
            }

            .mobile-btn {
                flex: 1;
                justify-content: center;
                min-width: 0
            }
        }

        @media (max-width:575.98px) {
            .main-content-inner {
                padding: 8px
            }

            .mobile-card {
                margin-bottom: 10px
            }

            .mobile-card-header,
            .mobile-card-body {
                padding: 12px
            }

            .mobile-card-title {
                font-size: 14px
            }

            .mobile-card-date {
                font-size: 12px;
                margin-bottom: 10px
            }

            .mobile-btn {
                font-size: 11px;
                padding: 5px 8px
            }
        }

        @media (min-width:1400px) {
            .main-content-inner {
                padding: 25px
            }

            .page-title {
                font-size: 1.75rem
            }

            .search-input {
                font-size: 16px;
                padding: 10px 15px
            }
        }

        @media print {

            .back-button,
            .wg-filter,
            .list-icon-function,
            .mobile-card-actions {
                display: none !important
            }

            .table {
                display: table !important
            }

            .mobile-cards {
                display: none !important
            }
        }

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
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
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
                    html: '<div style="text-align:center;line-height:1.6;"><strong>{{ Session::get('success') }}</strong><br><br><small style="color:#666;">The operation has been completed successfully.</small></div>',
                    confirmButtonText: 'Great!',
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-success'
                    }
                });
            @endif

            $('.force-delete').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const name = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text()
                    .trim();

                Swal.fire({
                    ...swalConfig,
                    icon: 'warning',
                    title: 'Permanent Deletion Warning',
                    html: `
                        <div style="text-align:left;line-height:1.6;">
                            <p style="margin-bottom:15px;">You are about to permanently delete:</p>
                            <div style="background:#f8f9fa;padding:15px;border-radius:6px;margin:15px 0;border-left:4px solid #dc3545;">
                                <strong style="color:#dc3545;">${name}</strong>
                            </div>
                            <p style="color:#721c24;background:#f8d7da;padding:12px;border-radius:6px;margin:15px 0;">
                                <strong>Warning:</strong> This action cannot be undone!
                            </p>
                            <p style="margin-top:20px;color:#666;">Are you sure you want to proceed?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete Permanently',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-warning'
                    }
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            $('.restore').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const name = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text()
                    .trim();

                Swal.fire({
                    ...swalConfig,
                    icon: 'question',
                    title: 'Restore Position',
                    html: `
                        <div style="text-align:left;line-height:1.6;">
                            <p style="margin-bottom:15px;">You are about to restore:</p>
                            <div style="background:#d1edff;padding:15px;border-radius:6px;margin:15px 0;border-left:4px solid #28a745;">
                                <strong style="color:#155724;">${name}</strong>
                            </div>
                            <p style="margin-top:20px;color:#666;">Do you want to proceed with the restoration?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore Position',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-question'
                    }
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            let t;
            $('input[name="search"]').on('input', function() {
                clearTimeout(t);
                const form = $(this).closest('form');
                t = setTimeout(() => form.submit(), 500);
            });
        });
    </script>
@endpush
