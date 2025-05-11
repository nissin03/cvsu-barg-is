@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-sm-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">User Report</h2>
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
                            <a href="{{ route('admin.report-user') }}">
                              <div class="text-tiny">Report User</div>
                            </a>
                          </li>
                          <li>
                            <i class="icon-chevron-right"></i>
                        </li>

                        <li>
                            <div class="text-tiny">User Report</div>
                        </li>
                    </ul>



                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card shadow-sm">
        <div class="card">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <h4 class="card-title text-primary fw-semibold mb-0">
                            <i class="bi bi-person-fill me-2"></i>User Input Analysis
                        </h4>
                    </div>
                    <div class="col-lg-8">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-lg-end">
                            <!-- Date Range Form -->
                            <form action="{{ route('admin.generate-input-users') }}" method="POST" id="user-form" class="d-flex flex-grow-1 flex-md-grow-0 gap-2">
                                @csrf
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-calendar3"></i>
                                    </span>
                                    <input type="date"
                                           name="start_date"
                                           id="start_date"
                                           class="form-control border-start-0"
                                           value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}"
                                           required>
                                    <span class="input-group-text bg-light border-start-0 border-end-0">to</span>
                                    <input type="date"
                                           name="end_date"
                                           id="end_date"
                                           class="form-control border-start-0"
                                           value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-funnel-fill me-1"></i>Filter
                                    </button>
                                </div>
                            </form>

                            <!-- Clear Button -->
                            <button type="button" class="btn btn-secondary btn-sm"
                                onclick="document.getElementById('start_date').value=''; document.getElementById('end_date').value='';">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>

                            <!-- PDF Download Form -->
                            <form action="{{ route('admin.download-input-users') }}" method="POST" id="download-form">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ old('start_date', isset($startDate) ? $startDate->toDateString() : '') }}">
                                <input type="hidden" name="end_date" value="{{ old('end_date', isset($endDate) ? $endDate->toDateString() : '') }}">
                                <input type="hidden" name="chart_image" id="chart_image">
                                <button type="submit" class="btn btn-danger btn-sm w-100 w-md-auto" style="height: 45px; line-height: 45px;">
                                    <i class="bi bi-file-pdf-fill me-1"></i>Export PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <div>
                            <ul class="list-unstyled mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Report Results -->
            @isset($chartData)
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                            {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                        </span>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-primary">
                                                <i class="bi bi-person fs-3"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Total Users</h6>
                                            <h3 class="mb-0">{{ number_format($chartData['total_users_count']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div id="user-chart" style="min-height: 400px;"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div>No user data available for the selected date range.</div>
                </div>
            @endisset
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@if(isset($chartData))
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var options = {
        chart: {
            type: 'line',
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
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
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
                type: 'area'
            }
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
                    // Format date to display more readably
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
        tooltip: {
            enabled: false,
            shared: true,
            followCursor: false,
            intersect: false,
            inverseOrder: false,
            custom: undefined,
            fillSeriesColor: false,
            theme: 'light',
            style: {
                fontSize: '12px',
                fontFamily: '"Inter", sans-serif'
            },
            onDatasetHover: {
                highlightDataSeries: true
            },
            x: {
                show: true,
                format: 'dd MMM yyyy'
            },
            y: {
                formatter: function (val) {
                    return val.toLocaleString() + ' users';
                },
                title: {
                    formatter: (seriesName) => seriesName + ':',
                }
            },
            marker: {
                show: true
            },
            fixed: {
                enabled: false
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
        ],
        states: {
            hover: {
                filter: {
                    type: 'lighten',
                    value: 0.05
                }
            },
            active: {
                allowMultipleDataPointsSelection: false,
                filter: {
                    type: 'darken',
                    value: 0.35
                }
            }
        }
    };

    // Initialize chart
    var chart = new ApexCharts(document.querySelector("#user-chart"), options);
    chart.render();

    // Capture chart image for PDF export
    document.getElementById('download-form').addEventListener('submit', function (event) {
        event.preventDefault();
        chart.dataURI().then(({ imgURI }) => {
            document.getElementById('chart_image').value = imgURI;
            this.submit();
        });
    });

    // Add resize listener to handle responsive behavior
    window.addEventListener('resize', function() {
        chart.updateOptions({
            chart: {
                height: window.innerWidth < 768 ? 300 : 400
            }
        });
    });
});
</script>
@endif
@endpush
