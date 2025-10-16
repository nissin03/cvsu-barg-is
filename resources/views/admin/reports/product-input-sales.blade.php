@php
    $startDate = $startDate ?? null;
    $endDate = $endDate ?? null;
    $selectedMonth = $selectedMonth ?? ($availableMonths->first() ?? null);
    $selectedYear = $selectedYear ?? date('Y');
    $selectedWeekId = $selectedWeekId ?? 1;
    
    if(isset($chartData) && $chartData !== null) {
        $totalSales = $chartData['reserved_sales_total'] + $chartData['pickedup_sales_total'] + $chartData['canceled_sales_total'];
    } else {
        $totalSales = 0;
        $chartData = [
            'reserved_sales_total' => 0,
            'pickedup_sales_total' => 0,
            'canceled_sales_total' => 0,
            'dates' => [],
            'total_sales' => [],
            'reserved_sales' => [],
            'pickedup_sales' => [],
            'canceled_sales' => [],
            'total_orders' => 0
        ];
    }
@endphp

@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-dark fw-semibold">Sales Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
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
                                    <button type="button" class="btn  btn-outline-dark  btn-lg flex-fill" id="pdf-export-btn" style="min-width: 120px;">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </button>
                                   
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
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

                               
                            </div>
                        </div>



        </div>
    </div>

    @if ($errors->any())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <ul class="list-unstyled mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

@isset($chartData)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 text-dark fw-medium">Sales Report</h5>
                        <small class="text-muted">
                            @if($startDate && $endDate)
                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            @else
                                No date range selected
                            @endif
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <form action="{{ route('admin.download-input-sales') }}" method="POST" id="download-form" style="display: none;">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            <input type="hidden" name="end_date" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            <input type="hidden" name="chart_image" id="chart_image">
                            <input type="hidden" name="total_sales" value="{{ $totalSales }}">
                            <input type="hidden" name="reserved_sales" value="{{ $chartData['reserved_sales_total'] }}">
                            <input type="hidden" name="pickedup_sales" value="{{ $chartData['pickedup_sales_total'] }}">
                            <input type="hidden" name="canceled_sales" value="{{ $chartData['canceled_sales_total'] }}">
                        </form>
                    </div>
                </div>
            </div>

            @if($startDate && $endDate)
            <div id="sales-chart" style="min-height: 400px;"></div>
            <div class="card-body p-4">
                <!-- Your existing card body content -->
            </div>
            @else
            <div class="card-body p-5 text-center">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No date range selected</h5>
                <p class="text-muted">Please select a start and end date to view sales data.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endisset
</div>
@endsection

@push('styles')
<style>
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

    .form-control, .form-select {
        border-radius: var(--bs-border-radius-sm);
        border: 1px solid var(--bs-border-color);
    }

    .btn {
        border-radius: var(--bs-border-radius-sm);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-weight: 600;
        color: #adb5bd;
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
    }
</style>
@endpush

@push('scripts')
@if(isset($chartData))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            chart: {
                type: 'area',
                height: 400,
                fontFamily: 'inherit',
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
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return `₱${val.toFixed(2)}`;
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return `₱${val.toFixed(2)}`;
                    }
                }
            },
            title: {
                text: 'Sales Report',
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
        };

        var chart = new ApexCharts(document.querySelector("#sales-chart"), options);
        chart.render();

        document.getElementById('pdf-export-btn').addEventListener('click', function() {
            chart.dataURI().then(({ imgURI }) => {
                document.getElementById('chart_image').value = imgURI;
                document.getElementById('download-form').submit();
            });
        });
    });

    function resetDateFilter() {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        
        const startDateInput = document.getElementById('start_date');
        const originalRequired = startDateInput.hasAttribute('required');
        
        if (originalRequired) {
            startDateInput.removeAttribute('required');
        }
        
        document.getElementById('sales-form').submit();
        

        if (originalRequired) {
            setTimeout(() => {
                startDateInput.setAttribute('required', 'required');
            }, 100);
        }
    }

    
</script>
@endif
@endpush