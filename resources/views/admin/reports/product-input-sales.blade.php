@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-dark fw-semibold">User Input Analysis</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">User Input Analysis</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 text-dark fw-medium">Date Range Filter</h5>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.generate-input-users') }}" method="POST" id="user-form">
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
                            <button type="submit" class="btn btn-dark btn-lg flex-fill">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetDateFilter()">
                                <i class="fas fa-times-circle me-1"></i>Clear
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" onclick="downloadPDF()">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($availableMonths))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 text-dark fw-medium">Monthly Registration Report</h5>
                            <small class="text-muted">Comprehensive monthly user registration overview</small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end mt-3 mt-md-0 flex-wrap">
                                <form action="{{ route('admin.report-user') }}" method="GET" id="filter-form" class="d-flex gap-2 align-items-center flex-wrap">
                                    @csrf
                                    <select name="month" class="form-select fs-5 py-3 px-4 border-light-subtle rounded" style="width: 140px;">
                                        @foreach($availableMonths as $month)
                                            <option value="{{ $month->id }}" {{ $month->id == $selectedMonth ? 'selected' : '' }}>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.download-input-users') }}" method="POST" id="download-form" style="display: none;">
        @csrf
        <input type="hidden" name="start_date" id="download_start_date" value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}">
        <input type="hidden" name="end_date" id="download_end_date" value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}">
        <input type="hidden" name="chart_image" id="chart_image">
    </form>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
        <div class="d-flex">
            <i class="fas fa-exclamation-circle me-2 mt-1"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(isset($chartData) && !empty($chartData['dates']))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark fw-medium">User Input Report</h5>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div id="user-chart" style="min-height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <i class="fas fa-calendar-x fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No date range selected</h5>
                    <p class="text-muted">Please select a start and end date to view user input data.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function resetDateFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('user-form').submit();
}

function downloadPDF() {
    document.getElementById('download_start_date').value = document.getElementById('start_date').value;
    document.getElementById('download_end_date').value = document.getElementById('end_date').value;
    
    if (typeof chart !== 'undefined') {
        chart.dataURI().then(({ imgURI }) => {
            document.getElementById('chart_image').value = imgURI;
            document.getElementById('download-form').submit();
        });
    } else {
        document.getElementById('download-form').submit();
    }
}

@if(isset($chartData) && !empty($chartData['dates']))
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        chart: {
            type: 'area',
            height: 400,
            fontFamily: '"Inter", "Helvetica Neue", Arial, sans-serif',
            background: '#ffffff',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            },
            toolbar: {
                show: true,
                offsetX: 0,
                offsetY: 0,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                },
                export: {
                    csv: {
                        filename: 'user-report',
                        columnDelimiter: ',',
                        headerCategory: 'Date',
                        headerValue: 'Value'
                    },
                    svg: {
                        filename: 'user-report'
                    },
                    png: {
                        filename: 'user-report'
                    }
                },
                autoSelected: 'zoom'
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 2,
                blur: 4,
                opacity: 0.1
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            lineCap: 'round'
        },
        colors: ['#4361ee', '#3bc9db', '#38d39f'],
        series: [
            {
                name: 'Total Users',
                data: @json($chartData['total_users']),
            },
        ],
        xaxis: {
            categories: @json($chartData['dates']),
            labels: {
                style: {
                    colors: '#637381',
                    fontSize: '12px',
                    fontWeight: 400
                },
                formatter: function(value) {
                    return new Date(value).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    });
                }
            },
            title: {
                text: 'Date Range',
                offsetY: 80,
                style: {
                    fontSize: '14px',
                    fontWeight: 600,
                    color: '#212B36'
                }
            },
            axisBorder: {
                show: true,
                color: '#E0E0E0'
            },
            axisTicks: {
                show: true,
                color: '#E0E0E0'
            },
            crosshairs: {
                show: true,
                position: 'back',
                stroke: {
                    color: '#637381',
                    width: 1,
                    dashArray: 3
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#637381',
                    fontSize: '12px',
                    fontWeight: 400
                },
                formatter: function (value) {
                    return value.toLocaleString();
                }
            },
            title: {
                text: 'User Count',
                style: {
                    fontSize: '14px',
                    fontWeight: 600,
                    color: '#212B36'
                }
            },
            min: function(min) {
                return min > 0 ? 0 : min;
            },
            tickAmount: 5,
            forceNiceScale: true
        },
        grid: {
            borderColor: '#F4F6F8',
            strokeDashArray: 4,
            padding: {
                top: 0,
                right: 20,
                bottom: 0,
                left: 20
            },
            xaxis: {
                lines: {
                    show: true
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.4,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '14px',
            fontFamily: '"Inter", sans-serif',
            fontWeight: 500,
            offsetY: -10,
            markers: {
                width: 12,
                height: 12,
                strokeWidth: 0,
                radius: 12,
                offsetX: -5
            },
            itemMargin: {
                horizontal: 10,
                vertical: 0
            }
        },
        responsive: [
            {
                breakpoint: 768,
                options: {
                    chart: {
                        height: 300
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        offsetY: 0
                    }
                }
            }
        ]
    };

    var chart = new ApexCharts(document.querySelector("#user-chart"), options);
    chart.render();

    window.addEventListener('resize', function() {
        chart.updateOptions({
            chart: {
                height: window.innerWidth < 768 ? 300 : 400
            }
        });
    });
});
@endif
</script>
@endpush