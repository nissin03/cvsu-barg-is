@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h3 class="text-primary">
                    <i class="fas fa-chart-line me-2"></i> Reports
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
                        <div class="text-tiny">Reports</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Monthly Earned Sales Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <!-- Card Header with Filter & PDF Download -->
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-chart-line me-2"></i>Monthly Earned Sales
                    </h5>
                    <div class="d-flex gap-3 flex-wrap">
                        <!-- Filter Form -->
                        <form action="{{ route('admin.reports') }}" method="GET" class="d-flex align-items-center gap-2">
                            <select name="year" class="form-select border-primary">
                                @foreach($yearRange as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-go">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </form>
                        <!-- PDF Export Form -->
                        <form action="{{ route('admin.downloadPdf') }}" method="POST" id="monthlyPdfForm" class="d-flex align-items-center">
                            @csrf
                            <!-- Hidden inputs for report values -->
                            <input type="hidden" name="total_amount" value="{{ $TotalAmount }}">
                            <input type="hidden" name="total_reserved_amount" value="{{ $TotalReservedAmount }}">
                            <input type="hidden" name="total_picked_up_amount" value="{{ $TotalPickedUpAmount }}">
                            <input type="hidden" name="total_canceled_amount" value="{{ $TotalCanceledAmount }}">
                            <input type="hidden" name="total_amount_w" value="{{ $TotalAmountW }}">
                            <input type="hidden" name="total_reserved_amount_w" value="{{ $TotalReservedAmountW }}">
                            <input type="hidden" name="total_picked_up_amount_w" value="{{ $TotalPickedUpAmountW }}">
                            <input type="hidden" name="total_canceled_amount_w" value="{{ $TotalCanceledAmountW }}">
                            <input type="hidden" name="total_amount_d" value="{{ $TotalAmountD }}">
                            <input type="hidden" name="total_reserved_amount_d" value="{{ $TotalReservedAmountD }}">
                            <input type="hidden" name="total_picked_up_amount_d" value="{{ $TotalPickedUpAmountD }}">
                            <input type="hidden" name="total_canceled_amount_d" value="{{ $TotalCanceledAmountD }}">
                            <input type="hidden" name="selected_year" value="{{ $selectedYear }}">
                            <input type="hidden" name="selected_month_name" value="{{ $selectedMonth->name }}">
                            <input type="hidden" name="selected_week_id" value="{{ $selectedWeekId }}">
                            
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i> Download PDF
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Card Body with Chart & Summary Numbers -->
                <div class="card-body">
                    <div class="chart-container mb-4">
                        <div id="line-chart-8" class="chart-height"></div>
                    </div>
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
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ $TotalPickedUpAmount }}</h4>
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

            <!-- Weekly Earnings Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-chart-line me-2"></i>Weekly Earnings
                    </h5>
                    <div class="d-flex gap-3 flex-wrap">
                        <!-- Filter Form -->
                        <form action="{{ route('admin.reports') }}" method="GET" class="d-flex align-items-center gap-2">
                            <select name="month" class="form-select border-primary">
                                @foreach($availableMonths as $month)
                                    <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                        {{ $month->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="form-select border-primary">
                                @foreach($yearRange as $year)
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
                    <div class="chart-container mb-4">
                        <div id="line-chart-8-weekly" class="chart-height"></div>
                    </div>
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
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ $TotalPickedUpAmountW }}</h4>
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

            <!-- Daily Earned Sales Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-chart-line me-2"></i>Daily Earned Sales
                    </h5>
                    <div class="d-flex gap-3 flex-wrap">
                        <!-- Filter Form -->
                        <form action="{{ route('admin.reports') }}" method="GET" class="d-flex align-items-center gap-2">
                            <select name="week" class="form-select border-primary">
                                @foreach($availableWeeks as $week)
                                    <option value="{{ $week->week_number }}" {{ $week->week_number == $selectedWeekId ? 'selected' : '' }}>
                                        Week {{ $week->week_number }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="month" class="form-select border-primary">
                                @foreach($availableMonths as $month)
                                    <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                        {{ $month->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="form-select border-primary">
                                @foreach($yearRange as $year)
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
                    <div class="chart-container mb-4">
                        <div id="line-chart-8-daily" class="chart-height"></div>
                    </div>
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
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ $TotalPickedUpAmountD }}</h4>
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
        box-shadow: 0 0 10px rgba(0,0,0,0.02);
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
    /* Class to widen the "Go" button */
    .btn-go {
        min-width: 120px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Earned Sales Chart
    var monthlyChart = new ApexCharts(document.querySelector("#line-chart-8"), {
        series: [{
            name: 'Total',
            data: [{{ $AmountM }}]
        }, {
            name: 'Reserved',
            data: [{{ $ReservationAmountM }}]
        }, {
            name: 'Pickedup',
            data: [{{ $PickedUpAmountM }}]
        }, {
            name: 'Canceled',
            data: [{{ $CanceledAmountM }}]
        }],
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: true }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728'],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        title: {
            text: 'Monthly Earned Sales for {{ $selectedYear }}',
            align: 'center',
            style: { fontSize: '18px', fontWeight: 700, color: '#2c5282' }
        }
    });
    monthlyChart.render();

    // Weekly Earnings Chart
    var weeklyChart = new ApexCharts(document.querySelector("#line-chart-8-weekly"), {
        series: [{
            name: 'Total Amount',
            data: [{{ $AmountW }}]
        }, {
            name: 'Reserved Amount',
            data: [{{ $ReservationAmountW }}]
        }, {
            name: 'Picked Up Amount',
            data: [{{ $PickedUpAmountW }}]
        }, {
            name: 'Canceled Amount',
            data: [{{ $CanceledAmountW }}]
        }],
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: false }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728'],
        xaxis: {
            categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            title: { text: 'Weeks of the Month' }
        },
        title: {
            text: 'Weekly Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }}',
            align: 'center'
        }
    });
    weeklyChart.render();

    // Daily Earned Sales Chart
    var dailyChart = new ApexCharts(document.querySelector("#line-chart-8-daily"), {
        series: [{
            name: 'Total Amount',
            data: [{{ $AmountD }}]
        }, {
            name: 'Reserved Amount',
            data: [{{ $ReservationAmountD }}]
        }, {
            name: 'Picked Up Amount',
            data: [{{ $PickedUpAmountD }}]
        }, {
            name: 'Canceled Amount',
            data: [{{ $CanceledAmountD }}]
        }],
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: false }
        },
        dataLabels: {
            enabled: false
        },
        colors: ['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728'],
        xaxis: {
            categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            title: { text: 'Day of the Week' }
        },
        title: {
            text: 'Daily Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }} (Week {{ $selectedWeekId }})',
            align: 'center'
        }
    });
    dailyChart.render();
});
</script>
@endpush
