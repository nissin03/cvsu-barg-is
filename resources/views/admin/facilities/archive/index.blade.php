@extends('layouts.admin')

@section('content')
<style>
    table {
        table-layout: auto;
        width: 100%;
        border-collapse: collapse; /* Collapses borders for a cleaner look */
    }

    th, td {
        padding: 12px 15px; /* Adjusted padding for better spacing */
        border: 1px solid #ddd;
        word-wrap: break-word;
        white-space: normal;
        text-align: left; /* Align text to the left for better readability */
    }

    th {
        background-color: #f8f9fa; /* Light background for headers */
        font-weight: bold; /* Make header text bold */
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




        
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
    <div class="header-section">
                <h3 class="text-2xl font-bold">Archived Facility</h3>
                <ul class="breadcrumbs">
                    <li><a href="{{ route('admin.index') }}">Dashoard</a></li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>Archived Facility</li>
                </ul>
            </div>

          

            <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="select-all-checkbox"></th> <!-- Select All -->
                        <th>#</th>
                        <th>Facility</th>
                        <th>Archived On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($facilities as $facility)
                    <tr>
                        <td><input type="checkbox" class="select-item" data-id="{{ $facility->id }}"></td> <!-- Individual checkbox -->
                        <td>{{ $facility->id }}</td>
                        <td>{{ $facility->name }}</td>
                        <td>{{ $facility->archived_on }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button id="restore-btn" class="btn btn-success">Restore Selected</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- SweetAlert2 script for delete confirmation -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   $(function() {
    // Select All checkbox functionality
    $('.select-all-checkbox').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.select-item').prop('checked', isChecked);
    });

    // Individual checkbox click behavior
    $('.select-item').on('change', function() {
        var allChecked = $('.select-item:checked').length === $('.select-item').length;
        $('.select-all-checkbox').prop('checked', allChecked);
    });

    // Restore Button Click
    $('#restore-btn').on('click', function() {
        const ids = $('.select-item:checked').map(function() {
            return $(this).data('id');
        }).get();

        if (ids.length > 0) {
            Swal.fire({
                title: 'Restore Facility',
                text: `Do you want to restore ${ids.length} facility(s)?`,
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
                                // Remove ALL selected facilities from the archived table
                                ids.forEach(function(id) {
                                    $(`tr[data-id="${id}"]`).remove(); // Remove from archived table
                                });

                                // Add the restored facilities to the active facilities table
                                response.facilities.forEach(function(facility) {
                                    const restoredRow = `<tr data-id="${facility.id}">
                                        <td><input type="checkbox" class="select-item" data-id="${facility.id}"></td>
                                        <td>${facility.id}</td>
                                        <td>${facility.name}</td>
                                        <td>${facility.archived_at ? facility.archived_at : 'Not archived'}</td>
                                    </tr>`;

                                    $('#active-facilities-table tbody').prepend(restoredRow);
                                });

                                // Uncheck the select all checkbox
                                $('.select-all-checkbox').prop('checked', false);
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while restoring the facility.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                title: 'No items selected!',
                text: 'Please select at least one item to restore.',
                icon: 'warning'
            });
        }
    });
});



</script>
@endpush
