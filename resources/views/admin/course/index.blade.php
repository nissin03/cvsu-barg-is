@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Courses</h3>
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
                        <div class="text-tiny">Courses</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" method="GET" action="{{ route('admin.courses.index') }}">
                            <fieldset class="name">
                                <input type="text" placeholder="Search by course name, code, or college..."
                                    class="" name="search" tabindex="2" value="{{ request('search') }}"
                                    aria-required="true" required="">
                            </fieldset>
                            <button type="submit" style="display: none"></button>
                        </form>
                    </div>

                    <a class="tf-button w-auto" href="{{ route('admin.courses.create') }}"><i class="icon-plus"></i>Add New
                        Course</a>
                    <a href="{{ route('admin.courses.archive') }}" class="tf-button w-auto">
                        <i class="icon-archive"></i>
                        <span class="button-text">View Archive</span>
                    </a>
                </div>
                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead class="thead-ligth">
                                <tr>
                                    <th scope="col" style="width: 30%; min-width: 200px;">Name</th>
                                    <th scope="col" style="width: 10%;">Code</th>
                                    <th scope="col" style="width: 35%;">College</th>
                                    <th scope="col" style="width: 15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courses as $course)
                                    <tr>
                                        <td>
                                            <div class="name text-truncate" title="{{ $course->name }}">
                                                <strong>{{ $course->name }}</strong>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge badge-secondary">{{ $course->code }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $course->college->name }}</span>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <a href="{{ route('admin.courses.edit', $course->id) }}">
                                                    <div class="item edit">
                                                        <i class="icon-edit-3"></i>
                                                    </div>
                                                </a>
                                                <form id="archive-form-{{ $course->id }}"
                                                    action="{{ route('admin.courses.destroy', $course->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="item text-warning archive-btn"
                                                        data-id="{{ $course->id }}"
                                                        style="border: none; background: none;" title="Archive Course">
                                                        <i class="icon-archive"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $courses->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1;
            vertical-align: middle;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .badge-primary {
            background-color: #3498db;
        }

        .list-icon-function {
            display: flex;
            gap: 10px;
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

        .list-icon-function .edit {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .list-icon-function .edit:hover {
            background-color: #3498db;
            color: white;
        }

        .list-icon-function .archive-btn {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .list-icon-function .archive-btn:hover {
            background-color: #e74c3c;
            color: white;
        }

        /* Fix for table layout and long text */
        .table {
            table-layout: fixed;
            width: 100%;
        }

        .name {
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table th:nth-child(1) {
                width: 30%;
            }

            .table th:nth-child(2) {
                width: 15%;
            }

            .table th:nth-child(3) {
                width: 25%;
            }

            .table th:nth-child(4) {
                width: 15%;
            }
        }

        /* SweetAlert2 Custom Styles */
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Archive Course Confirmation
            $('.archive-btn').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var courseName = $(this).closest('tr').find('.name').text().trim();

                Swal.fire({
                    ...customSwalConfig,
                    title: 'Archive Course Confirmation',
                    html: `
                        <div style="text-align: left; line-height: 1.6;">
                            <p style="margin-bottom: 15px; text-align: center;">You are about to archive the following course:</p>
                            <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); padding: 15px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #f59e0b;">
                                <strong style="color: #92400e; font-size: 16px;">${courseName}</strong>
                            </div>
                            <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin: 15px 0; border: 1px solid #fcd34d;">
                                <p style="margin: 0; color: #92400e; font-size: 14px;">
                                    <i class="icon-info" style="margin-right: 8px;"></i>
                                    <strong>Note:</strong> Archiving will move this course to the archived section. It can be restored later if needed.
                                </p>
                            </div>
                            <p style="margin-top: 20px; color: #64748b; text-align: center;">
                                This action is reversible. The course will remain in the system but will be hidden from the main list.
                            </p>
                        </div>
                    `,
                    customClass: {
                        popup: 'swal2-warning'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="icon-archive"></i> Yes, Archive It',
                    cancelButtonText: '<i class="icon-x"></i> Cancel',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Archiving Course...',
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
                                        <p style="color: #64748b; margin: 0;">Please wait while we archive the course...</p>
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

            $('.tf-button').on('click', function() {
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

            function handleResize() {
                const windowWidth = $(window).width();

                if (windowWidth <= 768) {
                    $('.table').hide();
                } else {
                    $('.table').show();
                }
            }

            $(window).on('resize', handleResize);
            handleResize();

            $('.list-icon-function .item, .tf-button').css({
                'transition': 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
            });

            $('.list-icon-function .item').on('focus', function() {
                $(this).css({
                    'outline': '2px solid #3498db',
                    'outline-offset': '2px'
                });
            }).on('blur', function() {
                $(this).css('outline', 'none');
            });

            if ('ontouchstart' in window) {
                $('.list-icon-function .item').css('cursor', 'pointer');
            }
        });
    </script>
@endpush
