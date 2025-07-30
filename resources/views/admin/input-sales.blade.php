@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-sm-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">Sales Report</h2>
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
                            <div class="text-tiny">Sales Report</div>
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
                            <i class="bi bi-graph-up-arrow me-2"></i>Sales Analysis
                        </h4>
                    </div>
                    <div class="col-lg-8">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-lg-end">
                            <!-- Date Range Form -->
                            <form action="{{ route('admin.generate-input-sales') }}" method="POST" id="sales-form" class="d-flex flex-grow-1 flex-md-grow-0 gap-2">
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
                            <form action="{{ route('admin.download-input-sales') }}" method="POST" id="download-form">
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
                                                <i class="bi bi-cart3 fs-3"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Total Orders</h6>
                                            <h3 class="mb-0">{{ number_format($chartData['total_orders']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-warning">
                                                <i class="bi bi-bookmark-check fs-3"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Reserved Sales</h6>
                                            <h3 class="mb-0">${{ number_format($chartData['reserved_sales_total'], 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-success">
                                                <i class="bi bi-bag-check fs-3"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Picked Up Sales</h6>
                                            <h3 class="mb-0">${{ number_format($chartData['pickedup_sales_total'], 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-danger">
                                                <i class="bi bi-x-circle fs-3"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Canceled Sales</h6>
                                            <h3 class="mb-0">${{ number_format($chartData['canceled_sales_total'], 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div id="sales-chart" style="min-height: 400px;"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div>No sales data available for the selected date range.</div>
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
                type: 'area',
                height: 400,
                fontFamily: 'inherit',
                offsetX: -20,
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
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#0d6efd', '#ffc107', '#198754', '#dc3545'],
            series: [
                { name: 'Total Sales', data: @json($chartData['total_sales']) },
                { name: 'Reserved Sales', data: @json($chartData['reserved_sales']) },
                { name: 'Picked Up Sales', data: @json($chartData['pickedup_sales']) },
                { name: 'Canceled Sales', data: @json($chartData['canceled_sales']) }
            ],
            xaxis: {
                categories: @json($chartData['dates']),
                title: {
                    text: 'Date',
                    style: {
                        fontSize: '14px',
                        fontWeight: 500
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                title: {
                    text: 'Amount ($)',
                    style: {
                        fontSize: '14px',
                        fontWeight: 500
                    }
                },
                labels: {
                    formatter: function (val) {
                        return `$${val.toFixed(2)}`;
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return `$${val.toFixed(2)}`;
                    }
                },
                theme: 'dark'
            },
            grid: {
                borderColor: '#f1f1f1',
                padding: {
                    top: 0,
                    right: 20,
                    bottom: 0,
                    left: 20
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        var chart = new ApexCharts(document.querySelector("#sales-chart"), options);
        chart.render();

        // Capture chart image for PDF export
        document.getElementById('download-form').addEventListener('submit', function (event) {
            event.preventDefault();
            chart.dataURI().then(({ imgURI }) => {
                document.getElementById('chart_image').value = imgURI;
                this.submit();
            });
        });
    });
</script>
@endif
@endpush
