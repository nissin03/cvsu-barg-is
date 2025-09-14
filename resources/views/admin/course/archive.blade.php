@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3 class="page-title">Archived Courses</h3>
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
                        <a href="{{ route('admin.courses.index') }}">
                            <div class="text-tiny">Courses</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Archived Courses</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <div class="wg-filter flex-grow">
                        <form class="form-search" method="GET" action="{{ route('admin.courses.archive') }}">
                            <fieldset class="name">
                                <input type="text" placeholder="Search archived courses..." class="search-input" name="search"
                                    tabindex="2" value="{{ request('search') }}" aria-required="true">
                            </fieldset>
                            <button type="submit" style="display: none"></button>
                        </form>
                    </div>

                    <a class="tf-button w-auto back-button" href="{{ route('admin.courses.index') }}">
                        <i class="icon-arrow-left"></i> 
                        <span class="button-text">Back to Courses</span>
                    </a>
                </div>
                
                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('error'))
                            <p class="alert alert-danger">{{ Session::get('error') }}</p>
                        @endif
                        
                        <!-- Mobile Card View -->
                        <div class="mobile-cards d-block d-md-none">
                            @forelse ($courses as $course)
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title">{{ $course->name }}</h5>
                                        <span class="badge badge-secondary">{{ $course->code }}</span>
                                    </div>
                                    <div class="mobile-card-body">
                                        <p class="mobile-card-date">
                                            <strong>College:</strong> {{ $course->college->name ?? 'N/A' }}
                                        </p>
                                        <p class="mobile-card-date">
                                            <strong>Deleted:</strong> {{ $course->deleted_at->format('M d, Y') }}
                                        </p>
                                        <div class="mobile-card-actions">
                                            <form action="{{ route('admin.courses.restore', $course->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success restore mobile-btn" title="Restore">
                                                    <i class="icon-refresh-ccw"></i> Restore
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.courses.force-delete', $course->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger force-delete mobile-btn" title="Permanently Delete">
                                                    <i class="icon-trash-2"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <p>No archived courses found.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
                        <table class="table table-striped table-bordered d-none d-md-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="col-name">Name</th>
                                    <th scope="col" class="col-code">Code</th>
                                    <th scope="col" class="col-college">College</th>
                                    <th scope="col" class="col-date">Deleted At</th>
                                    <th scope="col" class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr>
                                        <td class="name-cell">
                                            <div class="name text-truncate" title="{{ $course->name }}">
                                                <strong>{{ $course->name }}</strong>
                                            </div>
                                        </td>
                                        <td class="code-cell">
                                            <span class="">{{ $course->code }}</span>
                                        </td>
                                        <td class="college-cell">
                                            {{ $course->college->name ?? 'N/A' }}
                                        </td>
                                        <td class="date-cell">{{ $course->deleted_at->format('M d, Y') }}</td>
                                        <td class="action-cell">
                                            <div class="list-icon-function">
                                                <form action="{{ route('admin.courses.restore', $course->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="item restore" style="border: none; background: none;" title="Restore">
                                                        <i class="icon-refresh-ccw"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.courses.force-delete', $course->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="item text-danger force-delete" style="border: none; background: none;" title="Permanently Delete">
                                                        <i class="icon-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center empty-state">No archived courses found.</td>
                                    </tr>
                                @endforelse
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


/sweet alert format code for icon

@push('styles')
<style>
/* SweetAlert2 Custom Styles - Clean Version */
.swal2-popup {
    width: 90vw !important;
    max-width: 600px !important;
    min-height: 350px !important;
    padding: 35px !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25) !important;
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
    .swal2-popup.swal2-question::before {
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
    .swal2-popup.swal2-question::before {
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
    .swal2-popup.swal2-question::before {
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
    .swal2-popup.swal2-question::before {
        font-size: 42px !important;
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
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    content: 'swal2-content',
                    actions: 'swal2-actions',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
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
                    html: '<div class="text-center"><strong>{{ Session::get('success') }}</strong><br><br><small class="text-muted">The operation has been completed successfully.</small></div>',
                    confirmButtonText: 'Great!',
                    showCancelButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-popup swal2-success' 
                    }
                });
            @endif

            // Force delete confirmation
            $('.force-delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var courseName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text().trim();
                
                Swal.fire({
                    ...swalConfig,
                    icon: 'warning',
                    title: 'Permanent Deletion Warning',
                    html: `
                        <div class="text-left">
                            <p class="mb-3">You are about to permanently delete:</p>
                            <div class="bg-light p-3 rounded mb-3 border-left border-danger">
                                <strong class="text-danger">${courseName}</strong>
                            </div>
                            <p class="text-danger bg-light-danger p-3 rounded mb-3">
                                <strong>Warning:</strong> This action cannot be undone! All associated data will be permanently removed from the system.
                            </p>
                            <p class="mt-4 text-muted">Are you absolutely sure you want to proceed?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete Permanently',
                    cancelButtonText: 'Cancel',
                    focusCancel: true,
                    customClass: {
                        popup: 'swal2-popup swal2-error' 
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            html: 'Please wait while we permanently delete the course.',
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
                var courseName = $(this).closest('tr, .mobile-card').find('.name, .mobile-card-title').text().trim();
                
                Swal.fire({
                    ...swalConfig,
                    icon: 'question',
                    title: 'Restore Course',
                    html: `
                        <div class="text-left">
                            <p class="mb-3">You are about to restore:</p>
                            <div class="bg-info-light p-3 rounded mb-3 border-left border-success">
                                <strong class="text-success">${courseName}</strong>
                            </div>
                            <div class="bg-success-light p-3 rounded mb-3">
                                This course will be restored to active status and will be available for normal operations again.
                            </div>
                            <p class="mt-4 text-muted">Do you want to proceed with the restoration?</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore Course',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-popup swal2-question' 
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Restoring...',
                            html: 'Please wait while we restore the course.',
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