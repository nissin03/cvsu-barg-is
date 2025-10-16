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

        .tf-button-style-1 {
            width: max-content;
            height: 40px;
            padding: 10px 16px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: "Inter", sans-serif;
            font-size: 13px;
            font-weight: 600;
            line-height: 18px;
            border-radius: 8px;
            background-size: 100%;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .tf-button-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .tf-button-success:hover {
            background-color: #fff;
            color: #28a745;
        }

        .tf-button-success:hover span {
            color: #28a745 !important;
        }

        .tf-button-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .tf-button-info:hover {
            background-color: #fff;
            color: #17a2b8;
        }

        .tf-button-info:hover span {
            color: #17a2b8 !important;
        }


        .tf-button-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .tf-button-danger:hover {
            background-color: #fff;
            color: #dc3545;
        }

        .tf-button-danger:hover span {
            color: #dc3545 !important;
        }


        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 1rem;
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
                    <a class="btn btn-danger" href="{{ route('admin.facilities.reservations') }}">Back to Reservation</a>
                </div>
                @if (Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif

                <!-- Responsive table wrapper -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th class="table-header" style="width: 20%;">Reservation No</th>
                                <td style="width: 30%;">{{ $reservation->id ?? 'Not Applicable' }}</td>
                                <th class="table-header" style="width: 20%;">Status</th>
                                <td style="width: 30%;">
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

                            <!-- Row 2: Facility Info -->
                            <tr>
                                <th class="table-header">Facility</th>
                                <td>{{ $reservation->availability->facility->name ?? 'Not Applicable' }}</td>
                                <th class="table-header">Type</th>
                                <td>
                                    <span
                                        class="badge status-badge
                            @if ($reservation->availability->facility->facility_type === 'individual') bg-individual-green
                            @elseif ($reservation->availability->facility->facility_type === 'whole_place') bg-whole-orange
                            @elseif ($reservation->availability->facility->facility_type === 'both') bg-warning
                            @else bg-secondary @endif">
                                        {{ ucfirst($reservation->availability->facility->facility_type ?? 'Not Applicable') }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Row 3: Date Info -->
                            <tr>
                                <th class="table-header">Reservation Date</th>
                                <td colspan="3">
                                    @php
                                        $groupedDates = [];

                                        if (
                                            $reservation->grouped_availabilities &&
                                            $reservation->grouped_availabilities->isNotEmpty()
                                        ) {
                                            $sortedAvailabilities = $reservation->grouped_availabilities->sortBy(
                                                'date_from',
                                            );
                                            $currentGroup = [];

                                            foreach ($sortedAvailabilities as $avail) {
                                                if (empty($currentGroup)) {
                                                    $currentGroup = [
                                                        'start' => $avail->date_from,
                                                        'end' => $avail->date_to,
                                                    ];
                                                } elseif (
                                                    \Carbon\Carbon::parse($currentGroup['end'])
                                                        ->addDay()
                                                        ->format('Y-m-d') === $avail->date_from
                                                ) {
                                                    $currentGroup['end'] = $avail->date_to;
                                                } else {
                                                    $groupedDates[] = $currentGroup;
                                                    $currentGroup = [
                                                        'start' => $avail->date_from,
                                                        'end' => $avail->date_to,
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
                                                'end' => $reservation->availability->date_to,
                                            ];
                                        }
                                    @endphp

                                    @if (!empty($groupedDates))
                                        <div class="date-ranges">
                                            @foreach ($groupedDates as $range)
                                                @php
                                                    $startDate = \Carbon\Carbon::parse($range['start']);
                                                    $endDate = \Carbon\Carbon::parse($range['end']);
                                                @endphp

                                                <div class="date-range mb-1">
                                                    @if ($startDate->equalTo($endDate))
                                                        <span
                                                            class="badge bg-info">{{ $startDate->format('M j, Y') }}</span>
                                                    @else
                                                        @if ($startDate->format('M Y') === $endDate->format('M Y'))
                                                            <span class="badge bg-info">{{ $startDate->format('M j') }} -
                                                                {{ $endDate->format('j, Y') }}</span>
                                                        @elseif($startDate->format('Y') === $endDate->format('Y'))
                                                            <span class="badge bg-info">{{ $startDate->format('M j') }} -
                                                                {{ $endDate->format('M j, Y') }}</span>
                                                        @else
                                                            <span class="badge bg-info">{{ $startDate->format('M j, Y') }}
                                                                - {{ $endDate->format('M j, Y') }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        Not Applicable
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="wg-box mt-5 table-responsive">
                <h5>User Information</h5>
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $reservation->user->name ?? 'Not Applicable' }}</td>
                            <th>Email</th>
                            <td>{{ $reservation->user->email ?? 'Not Applicable' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $reservation->user->phone_number ?? 'â€"' }}</td>
                            <th>Role</th>
                            <td>{{ ucfirst($reservation->user->role ?? 'â€"') }}</td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td>{{ optional($reservation->user->college)->name ?? 'â€"' }}</td>
                            <th>Course</th>
                            <td>{{ optional($reservation->user->course)->name ?? 'â€"' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="wg-box mt-5 table-responsive">
                <h5>Transaction Details</h5>
                <table class="table table-striped table-bordered table-transaction">
                    <tbody>
                        <tr>
                            <th>Total Price</th>
                            <td>&#8369;{{ number_format($reservation->total_price, 2) }}</td>
                            <th>Last Updated By</th>
                            <td>{{ $reservation->updated_by ? $reservation->updatedBy->name : 'Not Applicable' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0)
                <div class="wg-box mt-5">
                    <h4 class="fw-bold mb-4">Qualification Details</h4>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">File</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservation->qualification_approvals as $qualification)
                                    <tr>
                                        <td>
                                            <span
                                                class="badge status-badge qualification-status-badge
                                    @if ($qualification->status === 'approved') bg-success
                                    @elseif ($qualification->status === 'pending') bg-primary
                                    @elseif ($qualification->status === 'canceled') bg-danger
                                    @else bg-secondary @endif"
                                                data-qualification-id="{{ $qualification->id }}">
                                                {{ ucfirst($qualification->status) }}
                                            </span>
                                        </td>

                                        <!-- File -->
                                        <td>
                                            @if ($qualification->hasQualificationFile())
                                                <a href="{{ $qualification->qualification_url }}" target="_blank"
                                                    class="btn btn-info btn-lg text-white">
                                                    <i class="icon-download"></i> View File
                                                </a>
                                            @else
                                                <span class="text-muted">No file uploaded</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <!-- Update Status Section -->
                                            <div class="mt-6">
                                                <h6 class="fw-semibold mb-3 mt-2">Update Status</h6>

                                                @if (in_array($qualification->status, ['approved', 'canceled']))
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-lock"></i>
                                                        Status cannot be changed.
                                                    </div>
                                                @else
                                                    <form
                                                        class="qualification-status-form d-flex gap-3 align-items-center flex-wrap"
                                                        data-qualification-id="{{ $qualification->id }}"
                                                        data-current-status="{{ $qualification->status }}">
                                                        @csrf
                                                        @method('PATCH')

                                                        <select name="status" class="qualification-status-select"
                                                            style="max-width: 200px;">
                                                            <option value="{{ $qualification->status }}" selected>
                                                                {{ ucfirst($qualification->status) }} (Current)
                                                            </option>
                                                            @if ($qualification->status === 'pending')
                                                                <option value="approved">Approved</option>
                                                                <option value="canceled">Canceled</option>
                                                            @endif
                                                        </select>

                                                        <button type="submit"
                                                            class="tf-button w208 qualification-update-btn">
                                                            Update Status
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @php
                $statusTransitions = [
                    'pending' => ['reserved', 'completed', 'canceled'],
                    'reserved' => ['completed', 'canceled'],
                    'completed' => ['canceled'],
                    'canceled' => [],
                ];
                $qualificationStatus = 'pending';
                $canUpdateReservation = false;
                $availableStatuses = [];

                if ($reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0) {
                    $qualification = $reservation->qualification_approvals->first();
                    $qualificationStatus = $qualification->status;

                    if ($qualificationStatus === 'approved') {
                        $canUpdateReservation = true;
                        // Use the current reservation status to get valid transitions
                        $availableStatuses = $statusTransitions[$reservation->status] ?? [];
                    } elseif ($qualificationStatus === 'canceled') {
                        $canUpdateReservation = true;
                        $availableStatuses = ['canceled'];
                    }
                } else {
                    // No qualification required - allow normal transitions
                    $canUpdateReservation = true;
                    $availableStatuses = $statusTransitions[$reservation->status] ?? [];
                }

                if (in_array($reservation->status, ['completed', 'canceled'])) {
                    $availableStatuses = [];
                    $canUpdateReservation = false;
                }
            @endphp

            <div class="wg-box mt-5">
                <h5>Update Reservation Status</h5>

                @if (
                    $qualificationStatus === 'pending' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-info mb-3" id="reservation-status-alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Action Required:</strong> User qualification must be approved before reservation status can
                        be updated.
                        <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
                    </div>
                @elseif(
                    $qualificationStatus === 'canceled' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-warning mb-3" id="reservation-status-alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> User qualification was canceled. Only cancellation is allowed.
                    </div>
                @elseif(
                    $qualificationStatus === 'approved' &&
                        $reservation->qualification_approvals &&
                        $reservation->qualification_approvals->count() > 0)
                    <div class="alert alert-success mb-3" id="reservation-status-alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Qualification Approved:</strong>
                        @if ($reservation->status === 'pending')
                            Pending reservations can be changed to: Reserved, Completed, or Canceled.
                        @elseif($reservation->status === 'reserved')
                            Reserved reservations can be changed to: Completed or Canceled.
                        @elseif($reservation->status === 'completed')
                            Completed reservations can only be changed to: Canceled.
                        @else
                            Status transitions are now available.
                        @endif
                    </div>
                @elseif(in_array($reservation->status, ['completed', 'canceled']))
                    <div class="alert alert-secondary mb-3" id="reservation-status-alert">
                        <i class="fas fa-lock"></i>
                        <strong>Status Final:</strong> {{ ucfirst($reservation->status) }} reservations cannot be changed.
                    </div>
                @endif

                <form id="statusUpdateForm" action="{{ route('admin.facilities.reservations.update', $reservation) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="initialStatus" value="{{ $reservation->status }}">
                    <input type="hidden" id="initialQualificationStatus" value="{{ $qualificationStatus }}">
                    <div class="row">
                        <div class="col-md-3 mt-2 mb-4">
                            <div class="select">
                                <select name="status" id="status" class=""
                                    data-current-status="{{ $reservation->status }}" @disabled(!$canUpdateReservation || empty($availableStatuses))>

                                    @if ($canUpdateReservation && count($availableStatuses) > 0)
                                        <!-- Current status as first option -->
                                        <option value="{{ $reservation->status }}" selected>
                                            {{ ucfirst($reservation->status) }} (Current)
                                        </option>

                                        <!-- Available transition statuses -->
                                        @foreach ($availableStatuses as $status)
                                            @if ($status !== $reservation->status)
                                                <option value="{{ $status }}">
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @else
                                        <!-- Final status or restricted -->
                                        <option value="{{ $reservation->status }}" selected>
                                            @if (!$canUpdateReservation && $qualificationStatus === 'pending')
                                                Awaiting qualification approval
                                            @elseif (empty($availableStatuses))
                                                {{ ucfirst($reservation->status) }} (Final)
                                            @else
                                                No options available
                                            @endif
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                @disabled(!$canUpdateReservation || empty($availableStatuses))>
                                @if (!$canUpdateReservation && $qualificationStatus === 'pending')
                                    Awaiting Approval
                                @elseif (empty($availableStatuses))
                                    Cannot Change
                                @else
                                    Update Status
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize status elements
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const initialQualificationStatus = document.getElementById('initialQualificationStatus')?.value ||
                'pending';

            // Initialize reservation status form based on current state
            initializeReservationStatusForm();
        });

        function initializeReservationStatusForm() {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');

            if (!statusSelect || !submitButton) return;

            const currentReservationStatus = statusSelect.dataset.currentStatus;
            const initialQualificationStatus = document.getElementById('initialQualificationStatus')?.value || 'pending';

            // Get qualification approvals count
            const hasQualifications =
                {{ $reservation->qualification_approvals && $reservation->qualification_approvals->count() > 0 ? 'true' : 'false' }};

            // Only disable if there are qualifications AND they are pending
            if (hasQualifications && initialQualificationStatus === 'pending') {
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
            }

            // Disable if reservation status is final
            if (['completed', 'canceled'].includes(currentReservationStatus)) {
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
            }
        }

        // Reservation status update form
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

                if (submitButton.disabled) return;

                const originalButtonText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Updating...';

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
                        showAlert(form, 'success', data.message || 'Status updated successfully');

                        // Update the status badge
                        updateReservationStatusBadge(data.new_status);

                        // Update the form based on new status
                        statusSelect.dataset.currentStatus = data.new_status;
                        updateReservationDropdown(data.new_status, data.available_next_statuses, submitButton,
                            originalButtonText);
                    })
                    .catch(error => {
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                        showAlert(form, 'danger', error.message || 'Error updating status. Please try again.');
                    });
            });
        }

        // Qualification status update forms
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
                        const originalButtonText = updateBtn.innerHTML;

                        statusSelect.disabled = true;
                        updateBtn.disabled = true;
                        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

                        fetch(`/admin/facilities/reservations/qualification/${qualificationId}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
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
                                console.log('Qualification update response:', data);

                                showAlert(this, 'success',
                                    'Qualification approval status updated successfully');

                                // Update the qualification status badge
                                updateQualificationStatusBadge(qualificationId, newStatus);

                                // **FIXED: Update reservation form availability WITHOUT changing reservation status**
                                updateReservationFormAvailability(
                                    data.qualification_status,
                                    data.current_reservation_status,
                                    data.available_reservation_statuses,
                                    data.can_update_reservation
                                );

                                // Update the qualification form dropdown
                                statusSelect.value = newStatus;
                                form.dataset.currentStatus = newStatus;

                                // Disable qualification form if status is final
                                if (['approved', 'canceled'].includes(newStatus)) {
                                    statusSelect.disabled = true;
                                    updateBtn.disabled = true;
                                    updateBtn.innerHTML = 'Status Updated';

                                    // Show lock message
                                    const lockDiv = document.createElement('div');
                                    lockDiv.className = 'alert alert-info mt-2';
                                    lockDiv.innerHTML =
                                        '<i class="fas fa-lock"></i> Status cannot be changed.';
                                    this.parentNode.replaceChild(lockDiv, this);
                                } else {
                                    statusSelect.disabled = false;
                                    updateBtn.disabled = false;
                                    updateBtn.innerHTML = originalButtonText;
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                statusSelect.disabled = false;
                                updateBtn.disabled = false;
                                updateBtn.innerHTML = originalButtonText;
                                showAlert(this, 'danger',
                                    'Error updating status. Please try again.');
                            });
                    });
                });
            }
        });

        function updateQualificationStatusBadge(qualificationId, newStatus) {
            const statusBadge = document.querySelector(`[data-qualification-id="${qualificationId}"]`);
            if (statusBadge) {
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusBadge.className = 'badge status-badge qualification-status-badge ' + getQualificationStatusBadgeClass(
                    newStatus);
            }
        }

        function updateReservationFormAvailability(qualificationStatus, currentReservationStatus,
            availableReservationStatuses, canUpdate) {
            const statusSelect = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const alertDiv = document.getElementById('reservation-status-alert');

            if (!statusSelect || !submitButton) return;

            console.log('Updating reservation form:', {
                qualificationStatus,
                currentReservationStatus,
                availableReservationStatuses,
                canUpdate
            });

            // Don't change anything if reservation is already at final status
            if (['completed', 'canceled'].includes(currentReservationStatus)) {
                return;
            }

            if (qualificationStatus === 'approved') {
                // Enable reservation status form but don't change current values
                statusSelect.disabled = false;
                submitButton.disabled = false;
                statusSelect.classList.remove('disabled');
                submitButton.classList.remove('disabled');

                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-success mb-3';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle"></i>
                        <strong>Qualification Approved:</strong>
                        ${getStatusTransitionMessage(currentReservationStatus)}
                    `;
                }

                // **CRITICAL: Update dropdown options but keep current reservation status selected**
                updateReservationDropdownOptions(currentReservationStatus, availableReservationStatuses, true);

            } else if (qualificationStatus === 'canceled') {
                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-warning mb-3';
                    alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> User qualification was canceled. Only cancellation is allowed.
                    `;
                }

                if (currentReservationStatus !== 'canceled' && canUpdate) {
                    // Enable form but only show cancellation option
                    statusSelect.disabled = false;
                    submitButton.disabled = false;
                    statusSelect.classList.remove('disabled');
                    submitButton.classList.remove('disabled');

                    updateReservationDropdownOptions(currentReservationStatus, ['canceled'], true);
                } else {
                    // Already canceled or cannot update
                    statusSelect.disabled = true;
                    submitButton.disabled = true;
                    statusSelect.classList.add('disabled');
                    submitButton.classList.add('disabled');
                }

            } else if (qualificationStatus === 'pending') {
                // Disable reservation status form while qualification is pending
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');

                // Update alert message
                if (alertDiv) {
                    alertDiv.className = 'alert alert-info mb-3';
                    alertDiv.innerHTML = `
                        <i class="fas fa-info-circle"></i>
                        <strong>Action Required:</strong> User qualification must be approved before reservation status can be updated.
                        <br><small class="text-muted">Current qualification status: <strong>Pending Review</strong></small>
                    `;
                }
            }
        }

        function updateReservationDropdownOptions(currentStatus, availableStatuses, keepCurrentSelection = true) {
            const statusSelect = document.getElementById('status');
            if (!statusSelect) return;

            console.log('Updating dropdown options:', {
                currentStatus,
                availableStatuses,
                keepCurrentSelection
            });

            // Store the currently selected value if we want to keep it
            const currentSelection = keepCurrentSelection ? statusSelect.value : currentStatus;

            // Clear existing options
            statusSelect.innerHTML = '';

            // Always add current status as first option (selected)
            const currentOption = document.createElement('option');
            currentOption.value = currentStatus;
            currentOption.textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) + ' (Current)';
            currentOption.selected = (currentSelection === currentStatus);
            statusSelect.appendChild(currentOption);

            // Add available transition options
            if (availableStatuses && availableStatuses.length > 0) {
                availableStatuses.forEach(status => {
                    if (status !== currentStatus) {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        option.selected = (currentSelection === status);
                        statusSelect.appendChild(option);
                    }
                });
            }

            // If no options available, show as final status
            if (!availableStatuses || availableStatuses.length === 0) {
                currentOption.textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) + ' (Final)';
                statusSelect.disabled = true;
                const submitButton = document.getElementById('submit-button');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Cannot Change';
                }
            }
        }

        function updateReservationStatusBadge(newStatus) {
            const statusBadge = document.querySelector('.status-badge');
            if (statusBadge && !statusBadge.classList.contains('qualification-status-badge')) {
                statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusBadge.className = 'badge status-badge ' + getStatusBadgeClass(newStatus);
            }
        }

        function updateReservationDropdown(newStatus, availableNextStatuses, submitButton, originalButtonText) {
            const statusSelect = document.getElementById('status');

            statusSelect.innerHTML = '';

            if (newStatus === 'canceled' || newStatus === 'completed') {
                const option = document.createElement('option');
                option.value = newStatus;
                option.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Final)';
                option.selected = true;
                statusSelect.appendChild(option);
                statusSelect.disabled = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Cannot Change';
            } else if (availableNextStatuses && availableNextStatuses.length > 0) {
                // Add current status as first option
                const currentOption = document.createElement('option');
                currentOption.value = newStatus;
                currentOption.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Current)';
                currentOption.selected = true;
                statusSelect.appendChild(currentOption);

                // Add available next statuses
                availableNextStatuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusSelect.appendChild(option);
                });

                statusSelect.disabled = false;
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            } else {
                const option = document.createElement('option');
                option.value = newStatus;
                option.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + ' (Final)';
                option.selected = true;
                statusSelect.appendChild(option);
                statusSelect.disabled = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Cannot Change';
            }
        }

        function showAlert(parentElement, type, message) {
            // Remove existing alerts
            const existingAlerts = parentElement.querySelectorAll('.alert');
            existingAlerts.forEach(alert => {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
                    alert.remove();
                }
            });

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} mt-3`;
            alertDiv.textContent = message;
            parentElement.parentNode.insertBefore(alertDiv, parentElement.nextSibling);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
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

        function getQualificationStatusBadgeClass(status) {
            switch (status) {
                case 'approved':
                    return 'bg-success';
                case 'pending':
                    return 'bg-primary';
                case 'canceled':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        function getStatusTransitionMessage(currentStatus) {
            switch (currentStatus) {
                case 'pending':
                    return 'Pending reservations can be changed to: Reserved, Completed, or Canceled.';
                case 'reserved':
                    return 'Reserved reservations can be changed to: Completed or Canceled.';
                case 'completed':
                    return 'Completed reservations can only be changed to: Canceled.';
                default:
                    return 'Status transitions are now available.';
            }
        }
    </script>
@endsection
