@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h3 class="text-primary">
                        <i class="fas fa-chart-line me-2"></i> Facilities Payments Report
                    </h3>
                    <ul class="breadcrumbs d-flex align-items-center gap-2">
                        <li>
                            <a href="{{ route('admin.index') }}">
                                <div class="text-tiny">Dashboard</div>
                            </a>
                        </li>
                        <li>
                            <i class="icon-chevron-right"></i>
                        </li>
                        <li>
                            <div class="text-tiny">Facilities Payments</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Monthly Payments Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div
                        class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-line me-2"></i> Monthly Payments
                        </h5>
                        <div class="d-flex gap-3 flex-wrap">
                            <!-- Filter Form -->
                            <form action="{{ route('admin.report.facilities') }}" method="GET"
                                class="d-flex align-items-center gap-2">
                                <select name="year" class="form-select border-primary">
                                    @foreach ($yearRange as $year)
                                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-go">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Total:</span>
                                    <h4>₱{{ $TotalAmount }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Reserved:</span>
                                    <h4>₱{{ $TotalReservedAmount }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Completed:</span>
                                    <h4>₱{{ $TotalCompletedAmount }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Canceled:</span>
                                    <h4>₱{{ $TotalCanceledAmount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Payments Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div
                        class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-line me-2"></i> Weekly Payments
                        </h5>
                        <div class="d-flex gap-3 flex-wrap">
                            <!-- Filter Form -->
                            <form action="{{ route('admin.report.facilities') }}" method="GET"
                                class="d-flex align-items-center gap-2">
                                <select name="month" class="form-select border-primary">
                                    @foreach ($availableMonths as $month)
                                        <option value="{{ $month->id }}"
                                            {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                            {{ $month->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select border-primary">
                                    @foreach ($yearRange as $year)
                                        <option value="{{ $year }}"
                                            {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-go">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Total:</span>
                                    <h4>₱{{ $TotalAmountW }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Reserved:</span>
                                    <h4>₱{{ $TotalReservedAmountW }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Completed:</span>
                                    <h4>₱{{ $TotalCompletedAmountW }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Canceled:</span>
                                    <h4>₱{{ $TotalCanceledAmountW }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Payments Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div
                        class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-line me-2"></i> Daily Payments
                        </h5>
                        <div class="d-flex gap-3 flex-wrap">
                            <!-- Filter Form -->
                            <form action="{{ route('admin.report.facilities') }}" method="GET"
                                class="d-flex align-items-center gap-2">
                                <select name="week" class="form-select border-primary">
                                    @foreach ($availableWeeks as $week)
                                        <option value="{{ $week->week_number }}"
                                            {{ $week->week_number == $selectedWeekId ? 'selected' : '' }}>
                                            Week {{ $week->week_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="month" class="form-select border-primary">
                                    @foreach ($availableMonths as $month)
                                        <option value="{{ $month->id }}"
                                            {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                            {{ $month->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select border-primary">
                                    @foreach ($yearRange as $year)
                                        <option value="{{ $year }}"
                                            {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-go">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Total:</span>
                                    <h4>₱{{ $TotalAmountD }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Reserved:</span>
                                    <h4>₱{{ $TotalReservedAmountD }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Completed:</span>
                                    <h4>₱{{ $TotalCompletedAmountD }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-tiny">Canceled:</span>
                                    <h4>₱{{ $TotalCanceledAmountD }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Daily Section -->
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .chart-height {
            min-height: 300px;
        }

        .chart-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.02);
        }

        .form-select {
            min-width: 120px;
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .btn-go {
            min-width: 120px;
        }
    </style>
@endpush
