@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
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
    
    <!-- Filters Card -->
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
                <div class="row g-3  align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label for="date_from" class="form-label text-gray-700 fw-medium mb-2">From Date</label>
                        <input type="date" class="form-control form-control-lg border-gray-300" id="date_from" name="date_from" value="{{ request('date_from') }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="date_to" class="form-label text-gray-700 fw-medium mb-2">To Date</label>
                        <input type="date" class="form-control form-control-lg border-gray-300" id="date_to" name="date_to" value="{{ request('date_to') }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-column h-100 justify-content-end"> <!-- Added wrapper div -->
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
                                    
                    <div class="col-lg-3 col-md-6 d-flex align-items-end gap-2">
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

    <!-- Billing Statements Card -->
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
                                <button class="btn btn-outline-primary btn-sm view-details fw-medium" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#paymentDetailsModal"
                                        data-payment-id="{{ $payment->id }}"
                                        data-user-name="{{ $payment->user->name }}"
                                        data-user-email="{{ $payment->user->email }}"
                                        data-user-sex="{{ $payment->user->sex }}"
                                        data-facility-name="{{ $payment->availability->facility->name }}"
                                        data-facility-type="{{ $payment->availability->facility->facility_type }}"
                                        data-room-name="{{ $payment->availability->facilityAttribute->room_name ?? '' }}"
                                        data-capacity="{{ $payment->availability->facilityAttribute->capacity ?? '' }}"
                                        data-whole-capacity="{{ $payment->availability->facilityAttribute->whole_capacity ?? '' }}"
                                        {{-- data-price-type="{{ $payment->paymentDetails->first()->price->price_type ?? '' }}"
                                        data-price-name="{{ $payment->paymentDetails->first()->price->name ?? '' }}"
                                        data-price-value="{{ $payment->paymentDetails->first()->price->value ?? '' }}" --}}
                                        data-date-from="{{ $payment->availability->date_from ? \Carbon\Carbon::parse($payment->availability->date_from)->format('M d, Y') : '' }}"
                                        data-date-to="{{ $payment->availability->date_to ? \Carbon\Carbon::parse($payment->availability->date_to)->format('M d, Y') : '' }}"
                                        data-total-price="{{ number_format($payment->total_price, 2) }}"
                                        style="border-radius: 6px;">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
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

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-receipt text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-gray-900 mb-0" id="paymentDetailsModalLabel">Payment Details</h5>
                        <small class="text-muted">Complete transaction information</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- User Information Section -->
                <div class="card border-0 bg-gray-50 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-gray-800">User Information</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small text-muted fw-medium mb-1">Full Name</label>
                                <p class="mb-0 fw-medium text-gray-800" id="modalUserName"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-medium mb-1">Email Address</label>
                                <p class="mb-0 text-gray-700" id="modalUserEmail"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted fw-medium mb-1">Gender</label>
                                <p class="mb-0 text-gray-700" id="modalUserSex"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facility Details Section -->
                <div class="card border-0 bg-gray-50 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-building text-info"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-gray-800">Facility Details</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-medium mb-1">Facility Name</label>
                                <p class="mb-2 fw-medium text-gray-800" id="modalFacilityName"></p>
                                <label class="small text-muted fw-medium mb-1">Facility Type</label>
                                <p class="mb-0 text-gray-700" id="modalFacilityType"></p>
                            </div>
                            <div class="col-md-6">
                                <div id="roomDetails">
                                    <label class="small text-muted fw-medium mb-1">Room Name</label>
                                    <p class="mb-2 text-gray-700" id="modalRoomName"></p>
                                    <label class="small text-muted fw-medium mb-1">Capacity</label>
                                    <p class="mb-0 text-gray-700" id="modalCapacity"></p>
                                </div>
                                <div id="wholeCapacityDetails">
                                    <label class="small text-muted fw-medium mb-1">Whole Capacity</label>
                                    <p class="mb-0 text-gray-700" id="modalWholeCapacity"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information Section -->
                <div class="card border-0 bg-gray-50">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-dollar-sign text-success"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-gray-800">Pricing Information</h6>
                        </div>
                        <div class="row g-3">
                            {{-- <div class="col-md-6">
                                <label class="small text-muted fw-medium mb-1">Price Type</label>
                                <p class="mb-2 text-gray-700" id="modalPriceType"></p>
                                <label class="small text-muted fw-medium mb-1">Price Name</label>
                                <p class="mb-0 text-gray-700" id="modalPriceName"></p>
                            </div> --}}
                            <div class="col-md-6">
                                {{-- <label class="small text-muted fw-medium mb-1">Price Value</label>
                                <p class="mb-2 text-gray-700">₱<span id="modalPriceValue"></span></p> --}}
                                <label class="small text-muted fw-medium mb-1">Total Amount</label>
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 fw-bold text-success">₱<span id="modalTotalPrice"></span></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 border-0">
                <button type="button" class="btn btn-outline-secondary fw-medium px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Custom Bootstrap 5 Variables */
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

/* Modern Typography */
.text-gray-900 { color: var(--bs-gray-900) !important; }
.text-gray-800 { color: var(--bs-gray-800) !important; }
.text-gray-700 { color: var(--bs-gray-700) !important; }
.text-gray-600 { color: var(--bs-gray-600) !important; }
.text-gray-500 { color: var(--bs-gray-500) !important; }
.text-gray-400 { color: var(--bs-gray-400) !important; }

.bg-gray-50 { background-color: var(--bs-gray-50) !important; }
.bg-gray-100 { background-color: var(--bs-gray-100) !important; }

.border-gray-300 { border-color: var(--bs-gray-300) !important; }

/* Custom Card Styling */
.card {
    border-radius: 12px !important;
    transition: all 0.2s ease-in-out;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

/* Form Controls */
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

/* Button Styling */
.btn {
    font-size: 0.9rem;
    padding: 0.5rem 1.25rem;
    transition: all 0.2s ease-in-out;
    border-radius: 8px !important;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
}

/* Table Styling */
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

/* Modal Styling */
.modal-content {
    border-radius: 12px;
}

.modal-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
}

.modal-body p {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

/* Icon Background Styling */
.bg-primary.bg-opacity-10 { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-success.bg-opacity-10 { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-info.bg-opacity-10 { background-color: rgba(13, 202, 240, 0.1) !important; }

/* Responsive Adjustments */
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
}

/* Print Styles */
@media print {
    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .btn {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#paymentDetailsModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        
        // Set modal content from data attributes
        $('#modalUserName').text(button.data('user-name'));
        $('#modalUserEmail').text(button.data('user-email'));
        $('#modalUserSex').text(button.data('user-sex'));
        $('#modalFacilityName').text(button.data('facility-name'));
        $('#modalFacilityType').text(button.data('facility-type'));
        $('#modalRoomName').text(button.data('room-name'));
        $('#modalCapacity').text(button.data('capacity'));
        $('#modalWholeCapacity').text(button.data('whole-capacity'));
        // $('#modalPriceType').text(button.data('price-type'));
        // $('#modalPriceName').text(button.data('price-name'));
        // $('#modalPriceValue').text(button.data('price-value'));
        $('#modalDateFrom').text(button.data('date-from'));
        $('#modalDateTo').text(button.data('date-to'));
        $('#modalTotalPrice').text(button.data('total-price'));

        // Show/hide room details based on availability
        if(button.data('room-name') || button.data('capacity')) {
            $('#roomDetails').show();
        } else {
            $('#roomDetails').hide();
        }

        // Show/hide whole capacity details based on availability
        if(button.data('whole-capacity')) {
            $('#wholeCapacityDetails').show();
        } else {
            $('#wholeCapacityDetails').hide();
        }
    });
});
</script>
@endpush