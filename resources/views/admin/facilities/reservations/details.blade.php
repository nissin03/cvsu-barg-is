@extends('layouts.admin')

@section('content')
    <style>
        .table-transaction>tbody>tr:nth-of-type(odd) {
            --bs-table-accent-bg: #fff !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bg-individual-green {
            background-color: oklch(49.6% 0.265 301.924);
        }

        .bg-whole-orange {
            background-color: oklch(66.6% 0.179 58.318);
        }

        .bg-whole-blue {
            background-color: oklch(58.8% 0.158 241.966);
        }

        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .date-range {
            margin-bottom: 2px;
        }

        .qualification-status-form {
            margin: 0;
        }

        .qualification-status-form .form-select {
            min-width: 120px;
        }

        .qualification-status-form .btn {
            white-space: nowrap;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .qualification-item {
            background-color: #f8f9fa;
            border-color: #dee2e6 !important;
        }

        .qualification-item h6 {
            color: #495057;
            font-weight: 600;
        }

        .qualification-item p {
            margin-bottom: 0.5rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Reservation Details</h3>
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
                        <div class="text-tiny">Reservation Details</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex justify-content-end">
                    <a class="btn  btn-danger" href="{{ route('admin.facilities.reservations') }}">Back</a>
                </div>
                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Reservation No</th>
                            <td>{{ $reservation->id ?? 'N/A' }}</td>
                            <th>Facility</th>
                            <td>{{ $reservation->availability->facility->name ?? 'N/A' }}</td>
                            <th>Type</th>
                            <td>
                                <span
                                    class="badge status-badge
                                        @if ($reservation->availability->facility->facility_type === 'individual') bg-individual-green
                                        @elseif ($reservation->availability->facility->facility_type === 'whole_place') bg-whole-orange
                                        @elseif ($reservation->availability->facility->facility_type === 'both') bg-warning
                                        @else bg-secondary @endif">
                                    {{ ucfirst($reservation->availability->facility->facility_type ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Reservation Date</th>
                            <td>
                                @php
                                    $groupedDates = [];
                            
                                    if ($reservation->grouped_availabilities && $reservation->grouped_availabilities->isNotEmpty()) {
                                        $sortedAvailabilities = $reservation->grouped_availabilities->sortBy('date_from');
                                        $currentGroup = [];
                            
                                        foreach ($sortedAvailabilities as $avail) {
                                            if (empty($currentGroup)) {
                                                $currentGroup = [
                                                    'start' => $avail->date_from,
                                                    'end' => $avail->date_to
                                                ];
                                            } elseif (\Carbon\Carbon::parse($currentGroup['end'])->addDay()->format('Y-m-d') === $avail->date_from) {
                                                $currentGroup['end'] = $avail->date_to;
                                            } else {
                                                $groupedDates[] = $currentGroup;
                                                $currentGroup = [
                                                    'start' => $avail->date_from,
                                                    'end' => $avail->date_to
                                                ];
                                            }
                                        }
                            
                                        if (!empty($currentGroup)) {
                                            $groupedDates[] = $currentGroup;
                                        }
                                    } else {
                                        // fallback: single availability
                                        $groupedDates[] = [
                                            'start' => $reservation->availability->date_from,
                                            'end' => $reservation->availability->date_to
                                        ];
                                    }
                                @endphp
                            
                                @if(!empty($groupedDates))
                                    <div class="date-ranges">
                                        @foreach($groupedDates as $range)
                                            @php
                                                $startDate = \Carbon\Carbon::parse($range['start']);
                                                $endDate = \Carbon\Carbon::parse($range['end']);
                                            @endphp
                            
                                            <div class="date-range">
                                                @if ($startDate->equalTo($endDate))
                                                    {{ $startDate->format('M j, Y') }}
                                                @else
                                                    @if($startDate->format('M Y') === $endDate->format('M Y'))
                                                        {{ $startDate->format('M j') }} - {{ $endDate->format('j, Y') }}
                                                    @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                        {{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }}
                                                    @else
                                                        {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>
                            <th>Created At</th>
                            <td>{{ $reservation->created_at->format('M d, Y H:i:s') }}</td>
                            <th>Status</th>
                            <td>
                                <span
                                    class="badge status-badge
                                        @if ($reservation->status === 'completed') bg-success
                                        @elseif ($reservation->status === 'pending') bg-primary
                                        @elseif ($reservation->status === 'canceled') bg-danger
                                        @elseif ($reservation->status === 'reserved') bg-warning
                                        @else bg-secondary @endif">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="wg-box mt-5">
                <h5>User Information</h5>
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $reservation->user->name ?? 'N/A' }}</td>
                            <th>Email</th>
                            <td>{{ $reservation->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $reservation->user->phone_number ?? 'N/A' }}</td>
                            <th>Role</th>
                            <td>{{ ucfirst($reservation->user->role ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td>{{ $reservation->user->department ?? 'N/A' }}</td>
                            <th>Course</th>
                            <td>{{ $reservation->user->course ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="wg-box mt-5">
                <h5>Transaction Details</h5>
                <table class="table table-striped table-bordered table-transaction">
                    <tbody>
                        <tr>
                            <th>Total Price</th>
                            <td>&#8369;{{ number_format($reservation->total_price, 2) }}</td>
                            <th>Last Updated By</th>
                            <td>{{ $reservation->updated_by ? $reservation->updatedBy->name : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>


            @if($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0)
            <div class="wg-box mt-5">
                <h5>Qualification Approval</h5>
                @foreach($reservation->qualification_approvals as $qualification)
                <div class="qualification-item mb-4 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">User Information</h6>
                            <p><strong>Name:</strong> {{ $qualification->user->name ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $qualification->user->email ?? 'N/A' }}</p>
                            <p><strong>Submitted:</strong> {{ $qualification->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Qualification Details</h6>
                            <p><strong>Status:</strong> 
                                <span class="badge status-badge
                                    @if ($qualification->status === 'reserved') bg-success
                                    @elseif ($qualification->status === 'pending') bg-primary
                                    @elseif ($qualification->status === 'canceled') bg-danger
                                    @elseif ($qualification->status === 'completed') bg-warning
                                    @else bg-secondary @endif">
                                    {{ ucfirst($qualification->status) }}
                                </span>
                            </p>
                            <p><strong>File:</strong> 
                                @if($qualification->hasQualificationFile())
                                    <a href="{{ $qualification->qualification_url }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> View File
                                    </a>
                                @else
                                    <span class="text-muted">No file uploaded</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3">Update Status</h6>
                            <form class="qualification-status-form" data-qualification-id="{{ $qualification->id }}">
                                @csrf
                                @method('PATCH')
                                <div class="d-flex gap-3 align-items-center">
                                    <select name="status" class="form-select qualification-status-select" style="max-width: 200px;" 
                                        @disabled($qualification->status === 'completed' || $qualification->status === 'canceled')>
                                        <option value="pending" {{ $qualification->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="reserved" {{ $qualification->status === 'reserved' ? 'selected' : '' }}>Approved</option>
                                        <option value="canceled" {{ $qualification->status === 'canceled' ? 'selected' : '' }}>Rejected</option>
                                        <option value="completed" {{ $qualification->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary qualification-update-btn"
                                        @disabled($qualification->status === 'completed' || $qualification->status === 'canceled')>
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @php
                // Determine qualification status and available reservation actions
                $qualificationStatus = 'pending';
                $canUpdateReservation = false;
                $availableStatuses = [];
                
                if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0) {
                    $qualification = $reservation->qualification_approvals->first();
                    $qualificationStatus = $qualification->status;
                    
                    switch ($qualificationStatus) {
                        case 'reserved': // Approved
                            $canUpdateReservation = true;
                            $availableStatuses = ['pending', 'reserved', 'completed', 'canceled'];
                            break;
                        case 'canceled': // Rejected
                            $canUpdateReservation = true;
                            $availableStatuses = ['canceled'];
                            break;
                        case 'pending':
                        default:
                            $canUpdateReservation = false;
                            $availableStatuses = [];
                            break;
                    }
                }
            @endphp

            <div class="wg-box mt-5">
                <h5>Update Reservation Status</h5>
                
                @if($qualificationStatus === 'pending')
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Action Required:</strong> User qualification must be approved before reservation status can be updated.
                        <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
                    </div>
                @elseif($qualificationStatus === 'canceled')
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> User qualification was rejected. Only cancellation is allowed.
                    </div>
                @endif

                <form id="statusUpdateForm" action="{{ route('admin.facilities.reservations.update', $reservation) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="initialStatus" value="{{ $reservation->status }}">
                    <div class="row">
                        <div class="col-md-3 mt-2 mb-4">
                            <div class="select">
                                <select name="status" id="status" class="" 
                                    data-current-status="{{ $reservation->status }}"
                                    @disabled(in_array($reservation->status, ['completed', 'canceled']) || !$canUpdateReservation)">
                                    @if($canUpdateReservation)
                                        @foreach($availableStatuses as $status)
                                            <option value="{{ $status }}" {{ $reservation->status === $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">No options available</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                @disabled(in_array($reservation->status, ['completed', 'canceled']) || !$canUpdateReservation)">
                                Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');

            // Only proceed if the reservation status form exists
            if (statusSelect && submitButton) {
                // Check if status has been changed (updated_by is not null)
                const tableCells = document.querySelectorAll('td');
                let lastUpdatedByValue = 'N/A';
                
                for (let cell of tableCells) {
                    if (cell.textContent.includes('Last Updated By')) {
                        // Get the next cell which contains the actual value
                        const nextCell = cell.nextElementSibling;
                        if (nextCell) {
                            lastUpdatedByValue = nextCell.textContent.trim();
                        }
                        break;
                    }
                }
                
                if (lastUpdatedByValue !== 'N/A') {
                    statusSelect.disabled = true;
                    submitButton.disabled = true;
                    statusSelect.classList.add('disabled');
                    submitButton.classList.add('disabled');
                }

                // Check qualification status and disable reservation update if needed
                const qualificationStatus = '{{ $qualificationStatus ?? "pending" }}';
                if (qualificationStatus === 'pending') {
                    statusSelect.disabled = true;
                    submitButton.disabled = true;
                }
            }
        });

        // Add event listener to reservation status form only if it exists
        const statusUpdateForm = document.getElementById('statusUpdateForm');
        if (statusUpdateForm) {
            statusUpdateForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;
                const submitButton = document.getElementById('submit-button');
                const statusSelect = document.getElementById('status');

                if (!submitButton || !statusSelect) {
                    console.error('Required form elements not found');
                    return;
                }

                // Disable the form elements immediately
                submitButton.disabled = true;
                statusSelect.disabled = true;
                submitButton.classList.add('disabled');
                statusSelect.classList.add('disabled');

                // Submit the form using fetch
                fetch(form.action, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: statusSelect.value
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.error || 'Failed to update status');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success mt-3';
                        alertDiv.textContent = 'Status updated successfully';
                        form.parentNode.insertBefore(alertDiv, form.nextSibling);

                        // Update the status badge
                        const statusBadge = document.querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.textContent = statusSelect.value.charAt(0).toUpperCase() + statusSelect.value
                                .slice(1);
                            statusBadge.className = 'badge status-badge ' + getStatusBadgeClass(statusSelect.value);
                        }

                        // Remove the alert after 3 seconds
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 3000);
                    })
                    .catch(error => {
                        // Re-enable the form elements if there's an error
                        submitButton.disabled = false;
                        statusSelect.disabled = false;
                        submitButton.classList.remove('disabled');
                        statusSelect.classList.remove('disabled');

                        // Show error message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger mt-3';
                        alertDiv.textContent = error.message || 'Error updating status. Please try again.';
                        form.parentNode.insertBefore(alertDiv, form.nextSibling);

                        // Remove the alert after 3 seconds
                        setTimeout(() => {
                            alertDiv.remove();
                        }, 3000);
                    });
            });
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'completed':
                    return 'bg-success';
                case 'pending':
                    return 'bg-primary';
                case 'canceled':
                    return 'bg-danger';
                case 'reserved':
                    return 'bg-warning';
                default:
                    return 'bg-secondary';
            }
        }

        // Handle qualification approval status updates
        document.addEventListener('DOMContentLoaded', function() {
            const qualificationForms = document.querySelectorAll('.qualification-status-form');
            
            if (qualificationForms.length > 0) {
                qualificationForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const qualificationId = this.dataset.qualificationId;
                        const statusSelect = this.querySelector('.qualification-status-select');
                        const updateBtn = this.querySelector('.qualification-update-btn');
                        
                        if (!statusSelect || !updateBtn) {
                            console.error('Required form elements not found');
                            return;
                        }
                        
                        const newStatus = statusSelect.value;
                        
                        // Disable form elements immediately
                        statusSelect.disabled = true;
                        updateBtn.disabled = true;
                        
                        // Show loading state
                        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        
                        fetch(`/admin/facilities/reservations/qualification/${qualificationId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success mt-2';
                            alertDiv.textContent = 'Qualification approval status updated successfully';
                            this.parentNode.insertBefore(alertDiv, this.nextSibling);
                            
                            // Update the status badge in the same qualification item
                            const statusBadge = this.closest('.qualification-item').querySelector('.status-badge');
                            if (statusBadge) {
                                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                                statusBadge.className = 'badge status-badge ' + getQualificationStatusBadgeClass(newStatus);
                            }
                            
                            // Update the select dropdown to show the new status
                            statusSelect.value = newStatus;
                            
                            // Update reservation status form based on new qualification status
                            updateReservationStatusForm(data.qualification_status, data.can_update_reservation, data.available_statuses);
                            
                            // Remove the alert after 3 seconds
                            setTimeout(() => {
                                alertDiv.remove();
                            }, 3000);
                            
                            // Re-enable form if status is not final
                            if (!['completed', 'canceled'].includes(newStatus)) {
                                statusSelect.disabled = false;
                                updateBtn.disabled = false;
                                updateBtn.innerHTML = 'Update Status';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            
                            // Re-enable form elements if there's an error
                            statusSelect.disabled = false;
                            updateBtn.disabled = false;
                            updateBtn.innerHTML = 'Update Status';
                            
                            // Show error message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger mt-2';
                            alertDiv.textContent = 'Error updating status. Please try again.';
                            this.parentNode.insertBefore(alertDiv, this.nextSibling);
                            
                            // Remove the alert after 3 seconds
                            setTimeout(() => {
                                alertDiv.remove();
                            }, 3000);
                        });
                    });
                });
            }
        });

        function getQualificationStatusBadgeClass(status) {
            switch (status) {
                case 'reserved':
                    return 'bg-success';
                case 'pending':
                    return 'bg-primary';
                case 'canceled':
                    return 'bg-danger';
                case 'completed':
                    return 'bg-warning';
                default:
                    return 'bg-secondary';
            }
        }

        // Function to update reservation status form based on qualification status
        function updateReservationStatusForm(qualificationStatus, canUpdate, availableStatuses) {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const statusUpdateForm = document.getElementById('statusUpdateForm');
            
            if (!statusSelect || !submitButton || !statusUpdateForm) return;
            
            // Update the alert message - find existing alert or create new one
            let alertDiv = statusUpdateForm.querySelector('.alert');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.className = 'alert mb-3';
                // Insert before the form
                statusUpdateForm.insertBefore(alertDiv, statusUpdateForm.querySelector('form'));
            }
            
            // Clear existing options
            statusSelect.innerHTML = '';
            
            if (canUpdate) {
                if (qualificationStatus === 'canceled') {
                    // Qualification rejected - show warning and only allow cancellation
                    alertDiv.className = 'alert alert-warning mb-3';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> User qualification was rejected. Only cancellation is allowed.';
                    
                    // Add only canceled option
                    const option = document.createElement('option');
                    option.value = 'canceled';
                    option.textContent = 'Canceled';
                    statusSelect.appendChild(option);
                    
                    // Disable form if reservation is already completed/canceled
                    const currentStatus = statusSelect.dataset.currentStatus || '';
                    const isDisabled = ['completed', 'canceled'].includes(currentStatus);
                    statusSelect.disabled = isDisabled;
                    submitButton.disabled = isDisabled;
                    
                } else if (qualificationStatus === 'reserved') {
                    // Qualification approved - show all options
                    alertDiv.className = 'alert alert-success mb-3';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> <strong>Qualification Approved:</strong> All reservation status options are now available.';
                    
                    // Add all available statuses
                    availableStatuses.forEach(status => {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        statusSelect.appendChild(option);
                    });
                    
                    // Enable form
                    statusSelect.disabled = false;
                    submitButton.disabled = false;
                }
            } else {
                // Qualification pending - show info message
                alertDiv.className = 'alert alert-info mb-3';
                alertDiv.innerHTML = '<i class="fas fa-info-circle"></i> <strong>Action Required:</strong> User qualification must be approved before reservation status can be updated.<br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>';
                
                // Disable form
                statusSelect.disabled = true;
                submitButton.disabled = true;
                
                // Add no options message
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No options available';
                statusSelect.appendChild(option);
            }
        }
    </script>
@endsection