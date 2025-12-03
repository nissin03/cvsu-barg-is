@extends('layouts.admin')

@section('content')
    <style>
        table {
            table-layout: auto;
            width: 100%;
            border-collapse: collapse;
            /* Collapses borders for a cleaner look */
        }

        th,
        td {
            padding: 12px 15px;
            /* Adjusted padding for better spacing */
            border: 1px solid #ddd;
            word-wrap: break-word;
            white-space: normal;
            text-align: left;
            /* Align text to the left for better readability */
        }

        th {
            background-color: #f8f9fa;
            /* Light background for headers */
            font-weight: bold;
            /* Make header text bold */
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Additional Styling for Select All Checkbox */
        .select-all-checkbox {
            cursor: pointer;
            margin-right: 10px;
        }

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

        /* Layout & Spacing */
        .main-content-wrap {
            padding: var(--spacing-lg);
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid #eee;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: 0.875rem;
            color: #6c757d;
        }

        .breadcrumbs a {
            color: inherit;
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .breadcrumbs a:hover {
            color: var(--primary-color);
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md) 0;
        }

        .bulk-actions label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin: 0;
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

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Archived Facility</h3>
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
                        <div class="text-tiny">All Archived Facilities</div>
                    </li>
                </ul>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <div class="bulk-actions">
                        <button class="btn btn-success" id="restore-btn" disabled>
                            <i class="icon-restore"></i>
                            <span>Restore</span>
                        </button>
                    </div>

                    @if (Session::has('status'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ Session::get('status') }}
                        </div>
                    @endif
                    <thead>
                        <tr>
                            <th></th> <!-- Select All -->
                            <th>#</th>
                            <th>Facility</th>
                            <th>Archived On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archivedFacilities as $facility)
                            <tr>
                                <td><input type="checkbox" class="select-item" data-id="{{ $facility->id }}"></td>
                                <td>{{ $facility->id }}</td>
                                <td>{{ $facility->name }}</td>
                                <td>{{ $facility->archived_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No archived facilities available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- SweetAlert2 script for delete confirmation -->
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
                        title: 'Restore Facility',
                        text: `Do you want to restore ${ids.length} facility/ies?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, restore them!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/admin/facility/restore',
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
                                            $(`tr input.select-item[data-id="${id}"]`)
                                                .closest('tr').remove();
                                        });

                                        // Update the select-all checkbox and restore button state
                                        $('#select-all').prop('checked', false);
                                        updateButtons();

                                        // If table is empty, display "no data" message
                                        if ($('.table-responsive tbody tr')
                                            .length === 0) {
                                            $('.table-responsive tbody').append(
                                                '<tr><td colspan="4" class="text-center">No archived facilities available.</td></tr>'
                                            );
                                        }
                                    });
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'An error occurred while restoring the products.',
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
