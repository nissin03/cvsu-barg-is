@extends('layouts.admin')

@section('content')

@push('styles')
<style>
    .dashboard-card {
        transition: transform 0.2s ease-in-out;
        border: none;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
    }

    .stat-card {
        border-left: 4px solid;
        background: linear-gradient(45deg, rgba(255,255,255,0.9), rgba(255,255,255,1));
    }

    .chart-container {
        min-height: 400px;
    }

    .filter-form select {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }

    .custom-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .filter-form {
        transition: all 0.3s ease;
    }

    .filter-form select:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        border-color: #86b7fe;
    }

    .filter-form .btn {
        transition: all 0.2s ease;
    }

    .filter-form .btn:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 576px) {
        .filter-form .row > div {
            margin-bottom: 0.5rem;
        }

        .filter-form .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-12">
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
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0">User Analytics Dashboard</h2>
                <form id="pdfForm" action="{{ route('admin.report-user.pdf') }}" method="POST">
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
                    <button id="pdfDownloadButton" type="submit" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Export Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-primary h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Total Users</h6>
                            <h4 class="mb-0">{{ number_format($totalUsers ?? 0) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-success h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">New This Month</h6>
                            <h4 class="mb-0">{{ number_format($newUsersThisMonth ?? 0) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-person-plus fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-info h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Active Users</h6>
                            <h4 class="mb-0">{{ number_format($activeUsers ?? 0) }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-warning h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Growth Rate</h6>
                            <h4 class="mb-0">{{ number_format($growthRate ?? 0, 1) }}%</h4>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-graph-up fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report Card -->
    <div class="card shadow-sm mb-4 dashboard-card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-month me-2"></i>
                        Monthly Registration Reports {{ $selectedYear }}
                    </h5>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('admin.report-user') }}" method="GET" class="filter-form d-flex justify-content-md-end gap-2">
                        <div class="input-group">
                            <select name="year" class="form-select">
                                @foreach($yearRange as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body chart-container">
            <div id="user-registration-chart-monthly" class="h-100"></div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Weekly Report Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 dashboard-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-week me-2"></i>
                            Weekly Registration Analysis
                        </h5>
                    </div>
                    <form action="{{ route('admin.report-user') }}" method="GET" class="filter-form">
                        <div class="row g-2">
                            <div class="col-sm-5">
                                <select name="month" class="form-select form-select-sm">
                                    @foreach($availableMonths as $month)
                                        <option value="{{ $month->id }}" {{ $month->id == $selectedMonth ? 'selected' : '' }}>
                                            {{ $month->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select name="year" class="form-select form-select-sm">
                                    @foreach($yearRange as $year)
                                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body chart-container">
                    <div id="user-registration-chart-weekly" class="h-100"></div>
                </div>
            </div>
        </div>

        <!-- Daily Report Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 dashboard-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-day me-2"></i>
                            Daily Registration Patterns (Week {{ $selectedWeekId }})
                        </h5>
                    </div>
                    <form action="{{ route('admin.report-user') }}" method="GET" class="filter-form">
                        <div class="row g-2">
                            <div class="col-sm-3">
                                <select name="week" class="form-select form-select-sm">
                                    @foreach($availableWeeks as $week)
                                        <option value="{{ $week->week_number }}" {{ $week->week_number == $selectedWeekId ? 'selected' : '' }}>
                                            Week {{ $week->week_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select name="month" class="form-select form-select-sm">
                                    @foreach($availableMonths as $month)
                                        <option value="{{ $month->id }}" {{ $month->id == $selectedMonth ? 'selected' : '' }}>
                                            {{ $month->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="year" class="form-select form-select-sm">
                                    @foreach($yearRange as $year)
                                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body chart-container">
                    <div id="user-registration-chart-daily" class="h-100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Registered Users Card -->
    <div class="card shadow-sm dashboard-card">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Recent Registrations
                </h5>
                <button class="btn btn-light btn-sm" onclick="refreshRecentUsers()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle custom-table mb-0">
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
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $user->name }}</div>
                                            <small class="text-muted">ID: #{{ $user->id }}</small>
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

<!-- Chart Loading Overlay -->
<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingOverlay" style="background: rgba(255,255,255,0.8); z-index: 1050;">
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading data...</div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            fontFamily: 'inherit',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 6,
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
        },
        responsive: [{
            breakpoint: 576,
            options: {
                chart: {
                    height: 300
                }
            }
        }]
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
            text: 'Monthly Registration Trends',
            align: 'left',
            style: {
                fontSize: '14px',
                fontWeight: 600
            }
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
            text: 'Weekly Registration Distribution',
            align: 'left',
            style: {
                fontSize: '14px',
                fontWeight: 600
            }
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
            text: 'Daily Registration Patterns',
            align: 'left',
            style: {
                fontSize: '14px',
                fontWeight: 600
            }
        }
    });
    dailyChart.render();

    // Handle PDF download with loading state.
    document.querySelector('#pdfDownloadButton').addEventListener('click', async function(e) {
        e.preventDefault();

        // Show loading overlay.
        document.getElementById('loadingOverlay').classList.remove('d-none');

        try {
            const chartImages = await Promise.all([
                monthlyChart.dataURI(),
                weeklyChart.dataURI(),
                dailyChart.dataURI()
            ]);

            document.querySelector('input[name="monthlyChartImage"]').value = chartImages[0].imgURI;
            document.querySelector('input[name="weeklyChartImage"]').value = chartImages[1].imgURI;
            document.querySelector('input[name="dailyChartImage"]').value = chartImages[2].imgURI;

            document.querySelector('#pdfForm').submit();
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('There was an error generating the PDF. Please try again.');
        } finally {
            // Hide loading overlay.
            document.getElementById('loadingOverlay').classList.add('d-none');
        }
    });

    // Utility functions for chart interactions.
    window.downloadWeeklyChart = function() {
        weeklyChart.exportToCSV();
    };

    window.downloadDailyChart = function() {
        dailyChart.exportToCSV();
    };

    window.showWeeklyDetails = function() {
        alert('Weekly details view coming soon');
    };

    window.showDailyDetails = function() {
        alert('Daily details view coming soon');
    };

    window.refreshRecentUsers = function() {
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
        button.disabled = true;

        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
            showToast('Recent users list has been refreshed');
        }, 1000);
    };

    // Toast notification function.
    function showToast(message) {
        const toastHtml = `
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
                <div class="toast align-items-center text-white bg-success border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i>${message}
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

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.2rem;
}

.custom-table td {
    padding: 1rem;
}

.toast {
    opacity: 0.9;
}

.chart-container {
    position: relative;
    min-height: 400px;
}

@media (max-width: 768px) {
    .chart-container {
        min-height: 300px;
    }
}
</style>
@endpush
