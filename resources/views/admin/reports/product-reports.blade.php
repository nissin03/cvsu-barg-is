@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-dark fw-semibold">Reports</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Reports</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3 text-dark fw-medium">Date Range Filter</h5>
                    <form action="{{ route('admin.generate-input-sales') }}" method="POST" id="sales-form">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label small text-muted">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-lg border-light-subtle" value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label small text-muted">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-lg border-light-subtle" value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}">
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark btn-lg flex-fill" style="min-width: 120px;">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetDateFilter()" style="min-width: 120px;">
                                        <i class="bi bi-x-circle me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 text-dark fw-medium">Monthly Sales Report</h5>
                            <small class="text-muted">Comprehensive monthly earnings overview</small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end mt-3 mt-md-0 flex-wrap">
                                <form action="{{ route('admin.reports') }}" method="GET" id="filter-form" class="d-flex gap-2 align-items-center flex-wrap">
                                    @csrf
                                    <select name="month" class="form-select fs-5 py-3 px-4 border-light-subtle rounded" style="width: 140px;">
                                        @foreach($availableMonths as $month)
                                            <option value="{{ $month->id }}" {{ $month->id == $selectedMonth->id ? 'selected' : '' }}>
                                                {{ $month->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="week" class="form-select fs-5 py-3 px-4 border-light-subtle rounded" style="width: 140px;">
                                        @foreach($availableWeeks as $week)
                                            <option value="{{ $week->week_number }}" {{ $week->week_number == $selectedWeekId ? 'selected' : '' }}>
                                                Week {{ $week->week_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="year" class="form-select fs-5 py-3 px-4 border-light-subtle rounded" style="width: 140px;">
                                        @foreach($yearRange as $year)
                                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-dark fs-5 py-3 px-4 w-auto">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                </form>

                                <form action="{{ route('admin.downloadPdf') }}" method="POST" id="monthlyPdfForm" class="d-inline">
                                    @csrf
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
                                    <button type="submit" class="btn btn-outline-dark fs-5 py-3 px-4 w-auto">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </button>
                                </form>
                            </div>
                        </div>



                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div id="loading-monthly" class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading monthly data...</p>
                        </div>
                        <div class="chart-container" id="monthly-chart-container" style="display: none;">
                            <div id="line-chart-8" class="chart-height"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Total:</span>
                                <h4>₱{{ number_format($TotalAmount, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Reserved:</span>
                                <h4>₱{{ number_format($TotalReservedAmount, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ number_format($TotalPickedUpAmount, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Canceled:</span>
                                <h4>₱{{ number_format($TotalCanceledAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0 text-dark fw-medium">Weekly Earnings</h5>
                    <small class="text-muted">Weekly performance breakdown</small>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div id="loading-weekly" class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading weekly data...</p>
                        </div>
                        <div class="chart-container" id="weekly-chart-container" style="display: none;">
                            <div id="line-chart-8-weekly" class="chart-height"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Total:</span>
                                <h4>₱{{ number_format($TotalAmountW, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Reserved:</span>
                                <h4>₱{{ number_format($TotalReservedAmountW, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ number_format($TotalPickedUpAmountW, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Canceled:</span>
                                <h4>₱{{ number_format($TotalCanceledAmountW, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0 text-dark fw-medium">Daily Earnings</h5>
                    <small class="text-muted">Daily performance analysis</small>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div id="loading-daily" class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading daily data...</p>
                        </div>
                        <div class="chart-container" id="daily-chart-container" style="display: none;">
                            <div id="line-chart-8-daily" class="chart-height"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Total:</span>
                                <h4>₱{{ number_format($TotalAmountD, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Reserved:</span>
                                <h4>₱{{ number_format($TotalReservedAmountD, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Picked Up:</span>
                                <h4>₱{{ number_format($TotalPickedUpAmountD, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-tiny">Canceled:</span>
                                <h4>₱{{ number_format($TotalCanceledAmountD, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --bs-border-radius: 0.75rem;
        --bs-border-radius-sm: 0.5rem;
        --bs-border-radius-lg: 1rem;
    }

    .chart-height {
        min-height: 300px;
        height: 400px;
    }

    .chart-container {
        background: #ffffff;
        border-radius: var(--bs-border-radius);
        padding: 1.5rem;
        border: 1px solid var(--bs-border-color);
    }

    .card {
        border-radius: var(--bs-border-radius);
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .form-control, .form-select {
        border-radius: var(--bs-border-radius-sm);
        border: 1px solid var(--bs-border-color);
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #495057;
        box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.1);
    }

    .btn {
        border-radius: var(--bs-border-radius-sm);
        font-weight: 500;
        letter-spacing: 0.025em;
        transition: all 0.2s ease;
    }

    .btn-dark {
        background-color: #343a40;
        border-color: #343a40;
    }

    .btn-dark:hover {
        background-color: #212529;
        border-color: #212529;
        transform: translateY(-1px);
    }

    .btn-outline-dark:hover {
        transform: translateY(-1px);
    }

    .breadcrumb {
        background: none;
        padding: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-weight: 600;
        color: #adb5bd;
    }

    .spinner-border {
        border-width: 0.15em;
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .chart-height {
            min-height: 250px;
            height: 300px;
        }
        
        .card-header .row {
            flex-direction: column;
        }
        
        .card-header .col-md-6:last-child {
            margin-top: 1rem;
        }
    }

    @media (max-width: 576px) {
        .btn {
            font-size: 0.875rem;
        }
    }

    .chart-container canvas {
        border-radius: var(--bs-border-radius-sm);
    }

    .form-control:focus,
    .form-select:focus,
    .btn:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }

    .spinner-border {
        opacity: 0.8;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }
</style>
@endpush

@push('scripts')
<script>
function resetDateFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('sales-form').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const monthlyData = {
            series: [{
                name: 'Total',
                data: [{{ $AmountM }}]
            }, {
                name: 'Reserved',
                data: [{{ $ReservationAmountM }}]
            }, {
                name: 'Picked Up',
                data: [{{ $PickedUpAmountM }}]
            }, {
                name: 'Canceled',
                data: [{{ $CanceledAmountM }}]
            }]
        };

        const weeklyData = {
            series: [{
                name: 'Total ',
                data: [{{ $AmountW }}]
            }, {
                name: 'Reserved',
                data: [{{ $ReservationAmountW }}]
            }, {
                name: 'Picked Up ',
                data: [{{ $PickedUpAmountW }}]
            }, {
                name: 'Canceled',
                data: [{{ $CanceledAmountW }}]
            }]
        };

        const dailyData = {
            series: [{
                name: 'Total ',
                data: [{{ $AmountD }}]
            }, {
                name: 'Reserved ',
                data: [{{ $ReservationAmountD }}]
            }, {
                name: 'Picked Up ',
                data: [{{ $PickedUpAmountD }}]
            }, {
                name: 'Canceled ',
                data: [{{ $CanceledAmountD }}]
            }]
        };

        const chartOptions = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { 
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false
                    }
                },
                animations: { 
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                },
                background: 'transparent',
                fontFamily: 'inherit'
            },
            dataLabels: { enabled: false },
            colors: ['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: {
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6c757d',
                        fontSize: '12px'
                    },
                    formatter: function(val) {
                        return '₱' + val.toLocaleString();
                    }
                }
            },
            title: {
                text: 'Monthly Sales Report for {{ $selectedYear }}',
                align: 'center',
                style: { 
                    fontSize: '18px', 
                    fontWeight: 700, 
                    color: '#2c5282'
                },
                margin: 20
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                fontWeight: 500,
                markers: {
                    radius: 4
                }
            },
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 3
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function(val) {
                        return '₱' + val.toLocaleString();
                    }
                }
            },
            noData: {
                text: 'No data available',
                align: 'center',
                verticalAlign: 'middle',
                style: {
                    color: '#6c757d',
                    fontSize: '14px'
                }
            }
        };

        const monthlyChart = new ApexCharts(document.querySelector("#line-chart-8"), {
            ...chartOptions,
            series: monthlyData.series
        });
        monthlyChart.render().then(() => {
            document.getElementById('loading-monthly').style.display = 'none';
            document.getElementById('monthly-chart-container').style.display = 'block';
        });

        const weeklyChart = new ApexCharts(document.querySelector("#line-chart-8-weekly"), {
            ...chartOptions,
            series: weeklyData.series,
            xaxis: {
                categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                title: { text: 'Weeks of the Month' }
            },
            title: {
                text: 'Weekly Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }}',
                align: 'center'
            }
        });
        weeklyChart.render().then(() => {
            document.getElementById('loading-weekly').style.display = 'none';
            document.getElementById('weekly-chart-container').style.display = 'block';
        });

        const dailyChart = new ApexCharts(document.querySelector("#line-chart-8-daily"), {
            ...chartOptions,
            series: dailyData.series,
            xaxis: {
                categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                title: { text: 'Day of the Week' }
            },
            title: {
                text: 'Daily Sales Report for {{ $selectedMonth->name }} {{ $selectedYear }} (Week {{ $selectedWeekId }})',
                align: 'center'
            }
        });
        dailyChart.render().then(() => {
            document.getElementById('loading-daily').style.display = 'none';
            document.getElementById('daily-chart-container').style.display = 'block';
        });

    } catch (error) {
        console.error('Error rendering charts:', error);
        document.querySelectorAll('[id^="loading-"]').forEach(el => {
            el.innerHTML = '<div class="alert alert-light border-0 text-center">Error loading chart data</div>';
        });
    }
});
</script>
@endpush