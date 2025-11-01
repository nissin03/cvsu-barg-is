{{-- resources/views/admin/discounts/archived.blade.php --}}
@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Archived Discounts</h3>
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
                        <a href="{{ route('discounts.index') }}">
                            <div class="text-tiny">Discounts</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Archived</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                @if (session('status'))
                    <div class="alert alert-success mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="bulk-actions mb-3">
                    <label class="select-all-label">
                        <input type="checkbox" id="select-all" class="select-all-checkbox">
                        <span id="selected-count">0 selected</span>
                    </label>
                    <button class="btn btn-success" id="restore-btn" disabled>
                        <i class="icon-refresh"></i>
                        <span>Restore</span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%;"></th>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">Name</th>
                                <th style="width: 10%;">Percent</th>
                                <th style="width: 15%;">Applies To</th>
                                <th style="width: 12%;">Requires Proof</th>
                                <th style="width: 15%;">Archived On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($discounts as $discount)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" data-id="{{ $discount->id }}">
                                    </td>
                                    <td>{{ $discount->id }}</td>
                                    <td><strong>{{ $discount->name }}</strong></td>
                                    <td>{{ rtrim(rtrim(number_format($discount->percent, 2, '.', ''), '0'), '.') }}%</td>
                                    <td>
                                        @if ($discount->applies_to === 'venue_only')
                                            <span class="badge badge-primary">Venue Only</span>
                                        @else
                                            <span class="badge badge-info">All Charges</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($discount->requires_proof)
                                            <span class="badge badge-warning">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $discount->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No archived discounts available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $discounts->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #28a745;
            --danger-color: #dc3545;
            --hover-opacity: 0.9;
            --transition-speed: 0.3s;
            --border-radius: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
        }

        table {
            table-layout: auto;
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            word-wrap: break-word;
            white-space: normal;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Badge Styles */
        .badge {
            padding: 0.25em 0.4em;
            border-radius: var(--border-radius);
            font-size: 0.75em;
            font-weight: 600;
            transition: opacity var(--transition-speed) ease;
        }

        .badge:hover {
            opacity: var(--hover-opacity);
        }

        .badge-success {
            background-color: var(--primary-color);
            color: #fff;
        }

        .badge-danger {
            background-color: var(--danger-color);
            color: #fff;
        }

        .badge-info {
            background-color: #17a2b8;
            color: #fff;
        }

        .badge-primary {
            background-color: #007bff;
            color: #fff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        /* Alert */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md) 0;
        }

        .select-all-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin: 0;
        }

        .select-all-checkbox {
            cursor: pointer;
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .select-item {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        #selected-count {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-success {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
        }

        .btn-success:hover:not(:disabled) {
            background-color: #218838;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding-top: var(--spacing-lg);
        }
    </style>
@endpush

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const updateButtons = () => {
                const selected = $('.select-item:checked').length;
                $('#selected-count').text(`${selected} selected`);
                $('#restore-btn').prop('disabled', selected === 0);
            };

            // Select All functionality
            $('#select-all').on('change', function() {
                $('.select-item').prop('checked', $(this).prop('checked'));
                updateButtons();
            });

            // Individual checkbox change
            $('.select-item').on('change', function() {
                const allChecked = $('.select-item:checked').length === $('.select-item').length;
                $('#select-all').prop('checked', allChecked);
                updateButtons();
            });

            // Restore Button Click
            $('#restore-btn').on('click', function() {
                const ids = $('.select-item:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length > 0) {
                    Swal.fire({
                        title: 'Restore Discounts',
                        text: `Do you want to restore ${ids.length} discount${ids.length > 1 ? 's' : ''}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, restore them!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('discounts.restore.bulk') }}',
                                method: 'POST',
                                data: {
                                    ids: ids,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.status,
                                        icon: 'success'
                                    }).then(() => {
                                        // Remove restored rows dynamically
                                        response.restoredIds.forEach(function(
                                            id) {
                                            $(`.select-item[data-id="${id}"]`)
                                                .closest('tr').remove();
                                        });

                                        // Update the select-all checkbox and restore button state
                                        $('#select-all').prop('checked', false);
                                        updateButtons();

                                        // If table is empty, display "no data" message
                                        if ($('.table-responsive tbody tr')
                                            .length === 0) {
                                            $('.table-responsive tbody').html(
                                                '<tr><td colspan="7" class="text-center">No archived discounts available.</td></tr>'
                                            );
                                        }
                                    });
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'An error occurred while restoring the discounts.',
                                        icon: 'error'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
