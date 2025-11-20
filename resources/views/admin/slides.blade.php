@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Slider</h3>
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
                        <div class="text-tiny">Slider</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <div class="flex-grow"></div>

                    <div class="action-buttons">
                        <a class="tf-button w-auto" href="{{ route('admin.slide.add') }}">
                            <i class="icon-plus"></i>Add new
                        </a>
                    </div>
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('status'))
                            <p class="alert alert-success mb-3">{{ Session::get('status') }}</p>
                        @endif

                        <!-- Mobile Card View -->
                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($slides as $slide)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <div class="mobile-card-image">
                                            <img src="{{ asset('uploads/slides') }}/{{ $slide->image }}"
                                                alt="{{ $slide->title }}">
                                        </div>
                                        <div class="mobile-card-header-text">
                                            <h5 class="mobile-card-title" title="{{ $slide->title }}">
                                                {{ $slide->title }}
                                            </h5>
                                            @if ($slide->tagline)
                                                <p class="mobile-card-tagline text-truncate" title="{{ $slide->tagline }}">
                                                    {{ $slide->tagline }}
                                                </p>
                                            @endif
                                            <div class="mobile-card-status">
                                                @if ($slide->status)
                                                    <span class="status-badge status-active">Active</span>
                                                @else
                                                    <span class="status-badge status-inactive">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mobile-card-body">
                                        @if ($slide->subtitle)
                                            <div class="mobile-card-row">
                                                <span class="mobile-card-label">Subtitle:</span>
                                                <span class="mobile-card-value" title="{{ $slide->subtitle }}">
                                                    {{ $slide->subtitle }}
                                                </span>
                                            </div>
                                        @endif

                                        @if ($slide->link)
                                            <div class="mobile-card-row">
                                                <span class="mobile-card-label">Link:</span>
                                                <a href="{{ $slide->link }}" class="mobile-card-link" target="_blank"
                                                    rel="noopener">
                                                    {{ $slide->link }}
                                                </a>
                                            </div>
                                        @endif

                                        <div class="mobile-card-actions">
                                            <a href="{{ route('admin.slide.edit', ['id' => $slide->id]) }}"
                                                class="btn btn-sm btn-primary mobile-btn">
                                                <i class="icon-edit-3"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.slide.delete', ['id' => $slide->id]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger mobile-btn delete">
                                                    <i class="icon-trash-2"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="icon-image"></i>
                                    </div>
                                    <h4>No Slides Found</h4>
                                    <p>Start by creating your first slider banner.</p>
                                    <a href="{{ route('admin.slide.add') }}" class="btn btn-primary">
                                        <i class="icon-plus"></i> Add New Slide
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-image">Image</th>
                                    <th scope="col" class="col-text">Tagline</th>
                                    <th scope="col" class="col-text">Title</th>
                                    <th scope="col" class="col-text">Subtitle</th>
                                    <th scope="col" class="col-link">Link</th>
                                    <th scope="col" class="col-status">Status</th>
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($slides as $slide)
                                    <tr>
                                        <td class="image-cell">
                                            <div class="slider-image">
                                                <img src="{{ asset('uploads/slides') }}/{{ $slide->image }}"
                                                    alt="{{ $slide->title }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" title="{{ $slide->tagline }}">
                                                {{ $slide->tagline }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" title="{{ $slide->title }}">
                                                <strong>{{ $slide->title }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" title="{{ $slide->subtitle }}">
                                                {{ $slide->subtitle }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($slide->link)
                                                <a href="{{ $slide->link }}" target="_blank" rel="noopener"
                                                    class="table-link text-truncate d-inline-block"
                                                    title="{{ $slide->link }}">
                                                    {{ $slide->link }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="status-cell">
                                            @if ($slide->status)
                                                <span class="status-badge status-active">Active</span>
                                            @else
                                                <span class="status-badge status-inactive">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="action-cell">
                                            <div class="list-icon-function">
                                                <a href="{{ route('admin.slide.edit', ['id' => $slide->id]) }}"
                                                    title="Edit Slide">
                                                    <div class="item edit">
                                                        <i class="icon-edit-3"></i>
                                                    </div>
                                                </a>
                                                <form action="{{ route('admin.slide.delete', ['id' => $slide->id]) }}"
                                                    method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="item text-danger delete"
                                                        style="border: none; background: none;" title="Delete Slide">
                                                        <i class="icon-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center empty-state-table">
                                            <div class="empty-icon">
                                                <i class="icon-image"></i>
                                            </div>
                                            <h5>No Slides Found</h5>
                                            <p>Start by creating your first slider banner.</p>
                                            <a href="{{ route('admin.slide.add') }}" class="btn btn-primary">
                                                <i class="icon-plus"></i> Add New Slide
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divider"></div>

                    <div class="pagination-container">
                        {{ $slides->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
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

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .g-table .table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        .col-image {
            width: 14%;
        }

        .col-text {
            width: 18%;
        }

        .col-link {
            width: 20%;
        }

        .col-status {
            width: 10%;
        }

        .col-action {
            width: 14%;
        }

        .image-cell {
            vertical-align: middle;
        }

        .slider-image {
            width: 100%;
            max-width: 140px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
        }

        .slider-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        .table-link {
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
            background-color: rgba(52, 152, 219, 0.08);
            color: #3498db;
        }

        .list-icon-function .edit:hover {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .list-icon-function .delete {
            background-color: rgba(239, 68, 68, 0.08);
            color: #ef4444;
        }

        .list-icon-function .delete:hover {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            line-height: 1.2;
            vertical-align: middle
        }

        .status-active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .status-inactive {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .status-cell {
            text-align: center;
            vertical-align: middle;
        }

        .mobile-card-status {
            margin-top: 4px;
        }

        /* Mobile cards */
        .mobile-cards {
            display: none;
        }

        .mobile-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
        }

        .mobile-card-header {
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .mobile-card-image {
            width: 72px;
            height: 72px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
        }

        .mobile-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .mobile-card-header-text {
            flex: 1;
            min-width: 0;
        }

        .mobile-card-title {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            overflow-wrap: anywhere;
        }

        .mobile-card-tagline {
            margin: 0;
            font-size: 13px;
            color: #64748b;
        }

        .mobile-card-body {
            padding: 12px 16px 14px;
        }

        .mobile-card-row {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .mobile-card-label {
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
        }

        .mobile-card-value {
            color: #111827;
            overflow-wrap: anywhere;
        }

        .mobile-card-link {
            color: #2563eb;
            text-decoration: none;
            overflow-wrap: anywhere;
        }

        .mobile-card-link:hover {
            text-decoration: underline;
        }

        .mobile-card-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
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
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
        }

        .mobile-btn.btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c, #7f1d1d);
            transform: translateY(-1px);
        }

        /* Empty states */
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

        /* Pagination */
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

        /* Responsive tweaks */
        @media (max-width: 991px) {
            .col-image {
                width: 18%;
            }

            .col-text {
                width: 20%;
            }

            .col-link {
                width: 20%;
            }

            .col-status {
                width: 12%;
            }

            .col-action {
                width: 16%;
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

            .tf-button {
                width: 100%;
                justify-content: center;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }

        @media print {

            .action-buttons,
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

        /* SweetAlert basic responsive tweak */
        @media (max-width: 767px) {
            .swal2-popup {
                width: 95vw !important;
                max-width: none !important;
                margin: 10px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this slide?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
