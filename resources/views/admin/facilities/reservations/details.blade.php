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
                    {{-- <div class="wg-filter flex-grow">
                        <h5>Reservation Details</h5>
                    </div> --}}
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
                            <td>{{ \Carbon\Carbon::parse($reservation->availability->date_from)->format('M d, Y') }} -
                                {{ \Carbon\Carbon::parse($reservation->availability->date_to)->format('M d, Y') }}</td>
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

            <div class="wg-box mt-5">
                <h5>Update Reservation Status</h5>
                <form id="statusUpdateForm" action="{{ route('admin.facilities.reservations.update', $reservation) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="initialStatus" value="{{ $reservation->status }}">
                    <div class="row">
                        <div class="col-md-3 mt-2 mb-4">
                            <div class="select">
                                <select name="status" id="status" class="" @disabled(in_array($reservation->status, ['completed', 'canceled']))>
                                    <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="reserved" {{ $reservation->status === 'reserved' ? 'selected' : '' }}>
                                        Reserved</option>
                                    <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="canceled" {{ $reservation->status === 'canceled' ? 'selected' : '' }}>
                                        Canceled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary tf-button w208" id="submit-button"
                                @disabled(in_array($reservation->status, ['completed', 'canceled']))>
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

            // Check if status has been changed (updated_by is not null)
            const lastUpdatedBy = document.querySelector('td:contains("Last Updated By")').textContent.trim();
            if (lastUpdatedBy !== 'N/A') {
                statusSelect.disabled = true;
                submitButton.disabled = true;
                statusSelect.classList.add('disabled');
                submitButton.classList.add('disabled');
            }
        });

        document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const submitButton = document.getElementById('submit-button');
            const statusSelect = document.getElementById('status');

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
                .then(response => response.json())
                .then(data => {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success mt-3';
                    alertDiv.textContent = 'Status updated successfully';
                    form.parentNode.insertBefore(alertDiv, form.nextSibling);

                    // Update the status badge
                    const statusBadge = document.querySelector('.status-badge');
                    statusBadge.textContent = statusSelect.value.charAt(0).toUpperCase() + statusSelect.value
                        .slice(1);
                    statusBadge.className = 'badge status-badge ' + getStatusBadgeClass(statusSelect.value);

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
                    alertDiv.textContent = 'Error updating status. Please try again.';
                    form.parentNode.insertBefore(alertDiv, form.nextSibling);

                    // Remove the alert after 3 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 3000);
                });
        });

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
    </script>
@endsection
