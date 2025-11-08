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
                                                    <button type="button" class="item text-danger archive-btn"
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

        /* Question Icon */
        .swal2-popup.swal2-question::before {
            content: "?" !important;
            color: #8b5cf6 !important;
            border: 4px solid #8b5cf6 !important;
            background: linear-gradient(135deg, #faf5ff, #ede9fe) !important;
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

            .swal2-popup.swal2-question::before {
                font-size: 30px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $('.archive-btn').on('click', function(e) {
                e.preventDefault();
                var courseId = $(this).data('id');
                var form = $('#archive-form-' + courseId);

                Swal.fire({
                    title: 'Archive Course?',
                    text: "Are you sure you want to archive this course?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, archive it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-question'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
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
        });
    </script>
@endpush
