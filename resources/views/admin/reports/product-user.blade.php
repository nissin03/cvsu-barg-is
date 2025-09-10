@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-dark fw-semibold">User Analytics</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}" class="text-decoration-none text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">User Analytics</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    {{-- <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2 small">Total Users</h6>
                            <h4 class="mb-0">{{ number_format($totalUsers ?? 0) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2 small">New This Month</h6>
                            <h4 class="mb-0">{{ number_format($newUsersThisMonth ?? 0) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-user-plus fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2 small">Active Users</h6>
                            <h4 class="mb-0">{{ number_format($activeUsers ?? 0) }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-user-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2 small">Growth Rate</h6>
                            <h4 class="mb-0">{{ number_format($growthRate ?? 0, 1) }}%</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-chart-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3 text-dark fw-medium">Date Range Filter</h5>
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
                                    <button type="submit" class="btn btn-dark btn-lg flex-fill" style="min-width: 120px;">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetDateFilter()" style="min-width: 120px;">
                                        <i class="fas fa-times-circle me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report -->
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

                                <form action="{{ route('admin.report-user.pdf') }}" method="POST" id="pdfForm" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="monthlyChartImage">
                                    <input type="hidden" name="weeklyChartImage">
                                    <input type="hidden" name="dailyChartImage">
                                    <input type="hidden" name="selectedMonth" value="{{ $selectedMonth }}">
                                    <input type="hidden" name="selectedYear" value="{{ $selectedYear }}">
                                    <input type="hidden" name="userRegistrationsByMonth" value="{{ $userRegistrationsByMonth }}">
                                    <input type="hidden" name="weeklyChartData" value="{{ $weeklyChartData }}">
                                    <input type="hidden" name="dailyChartData" value="{{ $dailyChartData }}">
                                    <input type="hidden" name="week" value="{{ $selectedWeekId }}">
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
                        <div class="chart-container" id="monthly-chart-container">
                            <div id="user-registration-chart-monthly" class="chart-height"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Report -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0 text-dark fw-medium">Weekly Registration Analysis</h5>
                    <small class="text-muted">Weekly user registration breakdown for {{ \Carbon\Carbon::createFromFormat('m', $selectedMonth)->format('F') }}</small>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div id="loading-weekly" class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading weekly data...</p>
                        </div>
                        <div class="chart-container" id="weekly-chart-container">
                            <div id="user-registration-chart-weekly" class="chart-height"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Report -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0 text-dark fw-medium">Daily Registration Patterns</h5>
                    <small class="text-muted">Daily user registration analysis for Week {{ $selectedWeekId }}</small>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div id="loading-daily" class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading daily data...</p>
                        </div>
                        <div class="chart-container" id="daily-chart-container">
                            <div id="user-registration-chart-daily" class="chart-height"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark fw-medium fs-3">Recent Registrations</h5>
                        {{-- <button type="button" class="btn btn-outline-secondary btn-lg" onclick="refreshRecentUsers()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button> --}}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-4">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">USER</th>
                                    <th>EMAIL</th>
                                    <th>REGISTRATION DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2 fs-4">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium fs-4">{{ $user->name }}</div>
                                                    <small class="text-muted fs-5">ID: #{{ $user->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Loading Overlay -->
<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingOverlay" style="background: rgba(255,255,255,0.8); z-index: 1050;">
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading data...</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chart-height {
        min-height: 300px;
        height: 400px;
    }

    .chart-container {
        background: #ffffff;
        border-radius: var(--bs-border-radius);
        padding: 1.5rem;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .card {
        border-radius: var(--bs-border-radius);
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
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
</style>
@endpush

@push('scripts')
<script>
function resetDateFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('user-form').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // Hide loading indicators initially (they'll be shown if needed)
    document.getElementById('loading-monthly').style.display = 'none';
    document.getElementById('loading-weekly').style.display = 'none';
    document.getElementById('loading-daily').style.display = 'none';

    const chartTheme = {
        chart: {
            type: 'bar',
            height: '100%',
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
            fontFamily: 'inherit'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val;
            },
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#304758"]
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        grid: {
            borderColor: '#f1f1f1',
            padding: {
                top: 30
            }
        },
        xaxis: {
            labels: {
                style: {
                    fontSize: '12px'
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
            labels: {
                style: {
                    fontSize: '12px'
                },
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        fill: {
            opacity: 0.85,
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: "vertical",
                shadeIntensity: 0.5,
                opacityFrom: 0.9,
                opacityTo: 0.6,
                stops: [0, 90, 100]
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " Users";
                }
            },
            theme: 'light',
            style: {
                fontSize: '12px'
            }
        }
    };

    // Monthly Chart
    const monthlyChart = new ApexCharts(document.querySelector("#user-registration-chart-monthly"), {
        ...chartTheme,
        series: [{
            name: 'Users Registered',
            data: [{{ $userRegistrationsByMonth }}]
        }],
        xaxis: {
            ...chartTheme.xaxis,
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        colors: ['#0d6efd'],
        title: {
            text: 'Monthly Registration Trends for {{ $selectedYear }}',
            align: 'center',
            style: { 
                fontSize: '16px', 
                fontWeight: 600, 
                color: '#2c5282'
            },
            margin: 20
        }
    });
    monthlyChart.render();

    // Weekly Chart
    const weeklyChart = new ApexCharts(document.querySelector("#user-registration-chart-weekly"), {
        ...chartTheme,
        series: [{
            name: 'Users Registered',
            data: [{{ $weeklyChartData }}]
        }],
        xaxis: {
            ...chartTheme.xaxis,
            categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6']
        },
        colors: ['#198754'],
        title: {
            text: 'Weekly Registration Distribution for {{ \Carbon\Carbon::createFromFormat('m', $selectedMonth)->format('F') }} {{ $selectedYear }}',
            align: 'center',
            style: { 
                fontSize: '16px', 
                fontWeight: 600, 
                color: '#2c5282'
            },
            margin: 20
        }
    });
    weeklyChart.render();

    // Daily Chart
    const dailyChart = new ApexCharts(document.querySelector("#user-registration-chart-daily"), {
        ...chartTheme,
        series: [{
            name: 'Users Registered',
            data: [{{ $dailyChartData }}]
        }],
        xaxis: {
            ...chartTheme.xaxis,
            categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
        },
        colors: ['#6610f2'],
        title: {
            text: 'Daily Registration Patterns for Week {{ $selectedWeekId }}',
            align: 'center',
            style: { 
                fontSize: '16px', 
                fontWeight: 600, 
                color: '#2c5282'
            },
            margin: 20
        }
    });
    dailyChart.render();

    // Handle PDF download with loading state
    document.querySelector('#pdfForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;

        // Show loading overlay
        document.getElementById('loadingOverlay').classList.remove('d-none');

        try {
            const chartImages = await Promise.all([
                monthlyChart.dataURI(),
                weeklyChart.dataURI(),
                dailyChart.dataURI()
            ]);

            form.querySelector('input[name="monthlyChartImage"]').value = chartImages[0].imgURI;
            form.querySelector('input[name="weeklyChartImage"]').value = chartImages[1].imgURI;
            form.querySelector('input[name="dailyChartImage"]').value = chartImages[2].imgURI;

            form.submit();
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('There was an error generating the PDF. Please try again.');
        } finally {
            // Hide loading overlay
            document.getElementById('loadingOverlay').classList.add('d-none');
        }
    });

    // Refresh recent users function
    // window.refreshRecentUsers = function() {
    //     const button = event.target;
    //     const originalContent = button.innerHTML;
    //     button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
    //     button.disabled = true;

    //     // Simulate refresh (in a real app, this would be an AJAX call)
    //     setTimeout(() => {
    //         button.innerHTML = originalContent;
    //         button.disabled = false;
    //         showToast('Recent users list has been refreshed');
    //     }, 1000);
    // };

    // Toast notification function
    function showToast(message) {
        const toastHtml = `
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
                <div class="toast align-items-center text-white bg-success border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = document.querySelector('.toast:last-child');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', function() {
            this.parentElement.remove();
        });
    }
});
</script>
@endpush