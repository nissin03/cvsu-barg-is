@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h4 class="mb-0 fs-2">Billing Statements</h4>
                        <ul class="breadcrumbs d-flex align-items-center gap-2">
                            <li>
                              <a href="{{ route('admin.index') }}">
                                <div class="text-tiny">
                                  <i class="bi bi-house-door me-1"></i>Dashboard
                                </div>
                              </a>
                            </li>
                            <li>
                              <i class="icon-chevron-right"></i>
                            </li>
                            <li>
                              <a href="{{ route('admin.report-statements') }}">
                                <div class="text-tiny">Statements</div>
                              </a>
                            </li>

                          </ul>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show fs-5" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                 <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="bg-light p-4 rounded">
                        <div class="row g-3 align-items-center">
                            <div class="col">
                            <form action="{{ route('admin.report-statements') }}" method="GET" id="filterForm" class="row g-3 align-items-center">
                                <div class="col-auto">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="today" id="today" value="1" {{ request('today') ? 'checked' : '' }}>
                                    <label class="form-check-label fs-4 ms-2" for="today">Today</label>
                                </div>
                                </div>
                                <div class="col-md-3">
                                <div class="input-group input-group-lg" id="start_date_container">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', request('start_date')) }}" class="form-control fs-5" placeholder="Start Date">
                                </div>
                                </div>
                                <div class="col-md-3">
                                <div class="input-group input-group-lg" id="end_date_container">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', request('end_date')) }}" class="form-control fs-5" placeholder="End Date">
                                </div>
                                </div>
                                <div class="col-md-3">
                                <div class="input-group input-group-lg">
                                    <input type="text" name="search" class="form-control fs-5" placeholder="Search by name or email" value="{{ request('search') }}">
                                    {{-- <button type="submit" class="btn btn-primary btn-lg fs-5 px-4">
                                    <i class="bi bi-search me-1"></i>Search
                                    </button> --}}
                                </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-outline-secondary btn-lg fs-5 px-4 me-2" onclick="clearForm()">
                                        <i class="bi bi-x-circle me-1"></i>Clear
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg fs-5 px-4">
                                        <i class="bi bi-funnel me-1"></i>Filter
                                    </button>
                                </div>
                            </form>
                            </div>
                            <div class="col-auto">
                            <form action="{{ route('admin.report-statements.download') }}" method="GET">
                                <input type="hidden" name="start_date" value="{{ old('start_date', request('start_date')) }}">
                                <input type="hidden" name="end_date" value="{{ old('end_date', request('end_date')) }}">
                                <input type="hidden" name="today" value="{{ request('today') ? '1' : '' }}">
                                <button type="submit" class="btn btn-danger btn-lg fs-5 px-4">
                                <i class="bi bi-file-pdf me-1"></i>Export PDF
                                </button>
                            </form>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>


                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center fs-5">Name</th>
                                    <th class="text-center fs-5">Email</th>
                                    <th class="text-center fs-5">Total Amount</th>
                                    <th class="text-center fs-5">Order Date</th>
                                    <th class="text-center fs-5">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="text-center fs-4">{{ $order->user->name }}</td>
                                        <td class="text-center fs-4">{{ $order->user->email }}</td>
                                        <td class="text-center fs-4">
                                            <span class="badge bg-success fs-4">
                                                ${{ number_format($order->total, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center fs-4">
                                            <span class="text-muted">
                                                {{ $order->created_at->format('F d, Y') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.report-statement', ['orderId' => $order->id]) }}" 
                                               class="btn btn-outline-primary fs-4">
                                                <i class="bi bi-eye me-1"></i>View Statement
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted fs-4">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                No billing statements found
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .form-switch .form-check-input {
        width: 3.5em;
        height: 1.75em;
        margin-top: 0.25em;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    
    .readonly-input {
        background-color: var(--bs-gray-100);
        pointer-events: none;
    }
    
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
    }

    /* Ensure all input groups and buttons have the same height */
    .input-group-lg > .form-control,
    .input-group-lg > .input-group-text,
    .btn-lg {
        height: 3.5rem;
        line-height: 2.5;
    }

    /* Adjust form switch size and alignment */
    .form-check.form-switch {
        padding-top: 0.5rem;
        min-height: 3.5rem;
        display: flex;
        align-items: center;
    }

    /* Make pagination larger */
    .pagination {
        font-size: 1.1rem;
    }
    
    /* Increase text size for all form controls */
    .form-control {
        font-size: 1.2rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const todayCheckbox = document.getElementById('today');
    const startDateContainer = document.getElementById('start_date_container');
    const endDateContainer = document.getElementById('end_date_container');
    const downloadStartDate = document.getElementById('download_start_date');
    const downloadEndDate = document.getElementById('download_end_date');

    function updateDateInputs() {
        const today = new Date().toISOString().split('T')[0];
        const startDateValue = '{{ old('start_date', request('start_date')) }}';
        const endDateValue = '{{ old('end_date', request('end_date')) }}';

        if (todayCheckbox.checked) {
            startDateContainer.innerHTML = `
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="text" class="form-control readonly-input fs-5" value="Today" readonly>
            `;
            endDateContainer.innerHTML = `
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="text" class="form-control readonly-input fs-5" value="Today" readonly>
            `;

            downloadStartDate.value = today;
            downloadEndDate.value = today;
        } else {
            startDateContainer.innerHTML = `
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="start_date" id="start_date" value="${startDateValue}" class="form-control fs-5">
            `;
            endDateContainer.innerHTML = `
                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="end_date" id="end_date" value="${endDateValue}" class="form-control fs-5">
            `;

            downloadStartDate.value = startDateValue;
            downloadEndDate.value = endDateValue;
        }
    }

    todayCheckbox.addEventListener('change', updateDateInputs);
    updateDateInputs();
});

function clearForm() {
    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('input:not([type="submit"])');
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            input.checked = false;
        } else {
            input.value = '';
        }
    });
    updateDateInputs();
}
</script>
@endpush