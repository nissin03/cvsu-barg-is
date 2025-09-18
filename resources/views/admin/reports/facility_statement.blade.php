@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <div>
             <h1 class="h3 mb-1 text-dark fw-semibold">Billing Statement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Facilities Billing Statement</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fas fa-filter text-primary"></i>
                </div>
                <h6 class="mb-0 fw-semibold text-gray-800">Filter Options</h6>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.facility-statement') }}">
                <div class="row g-3 align-items-end">
                   
                    <div class="col-lg-2 col-md-6">
                        <label for="date_from" class="form-label text-gray-700 fw-medium mb-2">From Date</label>
                        <input type="date" class="form-control form-control-lg border-gray-300" id="date_from" name="date_from" value="{{ request('date_from') }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="date_to" class="form-label text-gray-700 fw-medium mb-2">To Date</label>
                        <input type="date" class="form-control form-control-lg border-gray-300" id="date_to" name="date_to" value="{{ request('date_to') }}" style="border-radius: 8px;">
                    </div>
                     <div class="col-lg-2 col-md-6">
                        <label for="facility_id" class="form-label text-gray-700 fw-medium mb-2">Facility</label>
                        <select class="form-select form-select-lg border-gray-300" id="facility_id" name="facility_id" style="border-radius: 8px;">
                            <option value="">All Facilities</option>
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="d-flex flex-column h-100 justify-content-end">
                            <label for="status" class="form-label text-gray-700 fw-medium mb-2">Status</label>
                            <select class="form-select form-select-lg border-gray-300" id="status" name="status" style="border-radius: 8px;">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                        </div>
                    </div>
                                    
                    <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-dark btn-lg flex-fill" style="border-radius: 8px;">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.facility-statement') }}" class="btn btn-outline-secondary btn-lg px-4 fw-medium" style="border-radius: 8px;">
                            <i class="fas fa-refresh me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-file-invoice-dollar text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold text-gray-800">Billing Statements</h6>
                        <small class="text-muted">Payment records and transactions</small>
                    </div>
                </div>
               <a href="{{ route('admin.facility-statement.download', request()->query()) }}" class="btn btn-outline-dark fs-5 py-3 px-4 w-auto" style="border-radius: 8px;">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4">User</th>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Facility</th>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Reservation Dates</th>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4 text-center">Status</th>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Total Amount</th>
                            <th class="border-0 fw-semibold text-gray-700 py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="border-bottom">
                            <td class="py-4 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium text-gray-900">{{ $payment->user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="fw-medium text-gray-800">{{ $payment->availability->facility->name }}</div>
                            </td>
                            <td class="py-4 px-4">
                                @if($payment->date_from && $payment->date_to)
                                    <div class="fw-medium text-gray-700">
                                        {{ \Carbon\Carbon::parse($payment->date_from)->format('M d, Y') }} - 
                                        {{ \Carbon\Carbon::parse($payment->date_to)->format('M d, Y') }}
                                    </div>
                                @else
                                    <div class="text-muted small">No dates specified</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusClass = [
                                        'completed' => 'badge-completed',
                                        'canceled' => 'badge-canceled',
                                        'reserved' => 'badge-reserved',
                                        'pending' => 'badge-pending'
                                    ][$payment->status] ?? 'badge-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="fw-bold text-success">₱{{ number_format($payment->total_price, 2) }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <button class="btn btn-outline-primary btn-sm view-details-btn fw-medium" 
                                        data-user-name="{{ $payment->user->name }}"
                                        data-user-email="{{ $payment->user->email }}"
                                        data-user-sex="{{ $payment->user->sex }}"
                                        data-facility-name="{{ $payment->availability->facility->name }}"
                                        data-facility-type="{{ $payment->availability->facility->facility_type }}"
                                        data-room-name="{{ $payment->availability->facilityAttribute->room_name ?? '' }}"
                                        data-capacity="{{ $payment->availability->facilityAttribute->whole_capacity ?? '' }}"
                                        data-date-from="{{ $payment->date_from ? \Carbon\Carbon::parse($payment->date_from)->format('M d, Y') : '' }}"
                                        data-date-to="{{ $payment->date_to ? \Carbon\Carbon::parse($payment->date_to)->format('M d, Y') : '' }}"
                                        data-total-price="{{ number_format($payment->total_price, 2) }}"
                                        style="border-radius: 6px;">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="bg-gray-100 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-file-alt text-gray-400" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h6 class="text-gray-600 mb-1">No Records Found</h6>
                                    <p class="text-gray-500 mb-0 small">No billing statements match your current filters</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg-responsive">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-white border-0 py-4 px-4">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-receipt text-primary fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="modal-title fw-bold text-gray-900 mb-0" id="paymentDetailsModalLabel">Payment Details</h3>
                        <small class="text-muted fs-5">Complete transaction information</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <!-- User Information Table -->
                <div class="card border-0 bg-gray-50 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-user text-primary fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-semibold text-gray-800">User Information</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0 modal-detail-table">
                                <tbody>
                                    <tr>
                                        <td class="fw-medium text-muted py-2" style="width: 30%;">Full Name:</td>
                                        <td class="fw-bold text-gray-800 py-2" id="modalUserName"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted py-2">Email Address:</td>
                                        <td class="fw-bold text-gray-700 py-2" id="modalUserEmail"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted py-2">Gender:</td>
                                        <td class="fw-bold text-gray-700 py-2" id="modalUserSex"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Facility Details Table -->
                <div class="card border-0 bg-gray-50 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-building text-info fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-semibold text-gray-800">Facility Details</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0 modal-detail-table">
                                <tbody>
                                    <tr>
                                        <td class="fw-medium text-muted py-2" style="width: 30%;">Facility Name:</td>
                                        <td class="fw-bold text-gray-800 py-2" id="modalFacilityName"></td>
                                    </tr>
                                    <tr id="roomDetailsRow">
                                        <td class="fw-medium text-muted py-2">Room Name:</td>
                                        <td class="text-gray-700 py-2" id="modalRoomName"></td>
                                    </tr>
                                    <tr id="capacityDetailsRow">
                                        <td class="fw-medium text-muted py-2">Capacity:</td>
                                        <td class="text-gray-700 py-2" id="modalCapacity"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted py-2">Reservation Period:</td>
                                        <td class="fw-bold text-gray-700 py-2">
                                            <span id="modalDateFrom"></span> to <span id="modalDateTo"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information Table -->
                <div class="card border-0 bg-gray-50">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-dollar-sign text-success"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-gray-800">Pricing Information</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0 modal-detail-table">
                                <tbody>
                                    <tr>
                                        <td class="fw-medium text-muted py-2" style="width: 30%;">Total Amount:</td>
                                        <td class="py-2">
                                            <h4 class="mb-0 fw-bold text-success">₱<span id="modalTotalPrice"></span></h4>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 border-0 pb-4 px-4">
                <button type="button" class="btn btn-outline-secondary fw-medium px-4 py-2 fs-5" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.view-details-btn', function() {
        const $button = $(this);
        
        $('#modalUserName').text($button.data('user-name') || '');
        $('#modalUserEmail').text($button.data('user-email') || '');
        $('#modalUserSex').text($button.data('user-sex') || '');
        $('#modalFacilityName').text($button.data('facility-name') || '');
        $('#modalRoomName').text($button.data('room-name') || '');
        $('#modalCapacity').text($button.data('capacity') || '');
        $('#modalTotalPrice').text($button.data('total-price') || '');
        $('#modalDateFrom').text($button.data('date-from') || '');
        $('#modalDateTo').text($button.data('date-to') || '');

        // Show/hide room details row
        if ($button.data('room-name')) {
            $('#roomDetailsRow').show();
        } else {
            $('#roomDetailsRow').hide();
        }

        // Show/hide capacity details row
        if ($button.data('capacity')) {
            $('#capacityDetailsRow').show();
        } else {
            $('#capacityDetailsRow').hide();
        }

        $('#paymentDetailsModal').modal('show');
    });
});
</script>
@endpush

@push('styles')
<style>
:root {
    --bs-gray-50: #f8fafc;
    --bs-gray-100: #f1f5f9;
    --bs-gray-300: #cbd5e1;
    --bs-gray-400: #94a3b8;
    --bs-gray-500: #64748b;
    --bs-gray-600: #475569;
    --bs-gray-700: #334155;
    --bs-gray-800: #1e293b;
    --bs-gray-900: #0f172a;
}

.text-gray-900 { color: var(--bs-gray-900) !important; }
.text-gray-800 { color: var(--bs-gray-800) !important; }
.text-gray-700 { color: var(--bs-gray-700) !important; }
.text-gray-600 { color: var(--bs-gray-600) !important; }
.text-gray-500 { color: var(--bs-gray-500) !important; }
.text-gray-400 { color: var(--bs-gray-400) !important; }

.bg-gray-50 { background-color: var(--bs-gray-50) !important; }
.bg-gray-100 { background-color: var(--bs-gray-100) !important; }

.border-gray-300 { border-color: var(--bs-gray-300) !important; }

.card {
    border-radius: 12px !important;
    transition: all 0.2s ease-in-out;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.form-control, .form-select {
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease-in-out;
    border: 1px solid var(--bs-gray-300);
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

.form-label {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.btn {
    font-size: 0.9rem;
    padding: 0.5rem 1.25rem;
    transition: all 0.2s ease-in-out;
    border-radius: 8px !important;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
}

.table-hover tbody tr:hover {
    background-color: var(--bs-gray-50);
}

.table th {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-color: #e2e8f0;
    font-size: 1.5rem;
}

.badge {
    font-size: 1rem;
    padding: 0.4em 0.8em;
    border-radius: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
    min-width: 85px;
    text-align: center;
}

.badge-completed { 
    background: linear-gradient(45deg, #10b981, #059669); 
    color: white; 
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.badge-canceled { 
    background: linear-gradient(45deg, #ef4444, #dc2626); 
    color: white;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.badge-reserved { 
    background: linear-gradient(45deg, #3b82f6, #1e40af); 
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.badge-pending { 
    background: linear-gradient(45deg, #f59e0b, #d97706); 
    color: white;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.badge-secondary {
    background: linear-gradient(45deg, #6b7280, #4b5563);
    color: white;
}

.modal-lg-responsive {
    max-width: min(90vw, 800px);
    margin: 1rem auto;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 2rem);
}

.modal-content {
    max-height: calc(100vh - 2rem);
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
}

.modal-body p {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.bg-primary.bg-opacity-10 { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-success.bg-opacity-10 { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-info.bg-opacity-10 { background-color: rgba(13, 202, 240, 0.1) !important; }

@media (max-width: 1200px) {
    .modal-lg-responsive {
        max-width: 95vw;
    }
    
    .modal-body p {
        font-size: 1rem !important;
    }
    
    .modal-title {
        font-size: 1.25rem !important;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .modal-lg-responsive {
        max-width: 98vw;
        margin: 0.5rem;
    }
    
    .modal-dialog-centered {
        min-height: calc(100vh - 1rem);
    }
    
    .modal-content {
        max-height: calc(100vh - 1rem);
    }
    
    .modal-body {
        padding: 1rem !important;
    }
    
    .modal-body .card-body {
        padding: 1rem !important;
    }
    
    .modal-body p.fs-3 {
        font-size: 1rem !important;
    }
    
    .modal-body p.fs-4 {
        font-size: 0.9rem !important;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 1rem;
    }
    
    .row.g-4 {
        gap: 1rem !important;
    }
}

@media (max-width: 480px) {
    .modal-body .col-md-4,
    .modal-body .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .modal-body p.fs-3 {
        font-size: 0.9rem !important;
    }
    
    .modal-body h5 {
        font-size: 1rem !important;
    }
    
    .modal-title {
        font-size: 1.1rem !important;
    }
}

@media print {
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .btn {
        display: none !important;
    }
}

.modal-detail-table {
    font-size: 0.95rem;
}

.modal-detail-table td {
    border: none !important;
    padding: 0.75rem 0.5rem !important;
    vertical-align: middle;
}

.modal-detail-table tr {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.modal-detail-table tr:last-child {
    border-bottom: none;
}

.modal-detail-table td:first-child {
    padding-left: 0 !important;
}

.modal-detail-table td:last-child {
    padding-right: 0 !important;
}

/* Responsive adjustments for modal tables */
@media (max-width: 768px) {
    .modal-detail-table {
        font-size: 0.875rem;
    }
    
    .modal-detail-table td {
        padding: 0.5rem 0.25rem !important;
    }
    
    .modal-detail-table td:first-child {
        width: 35% !important;
    }
}

@media (max-width: 480px) {
    .modal-detail-table td {
        display: block;
        width: 100% !important;
        padding: 0.25rem 0 !important;
    }
    
    .modal-detail-table td:first-child {
        font-weight: 600 !important;
        margin-bottom: 0.25rem;
        padding-bottom: 0 !important;
    }
    
    .modal-detail-table td:last-child {
        padding-top: 0 !important;
        margin-bottom: 1rem;
    }
    
    .modal-detail-table tr {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
    }
}

</style>
@endpush