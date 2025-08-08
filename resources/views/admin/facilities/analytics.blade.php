@extends('layouts.app')

@section('content')
<div class="tf-section analytics-dashboard mb-30">
    <div class="section-title">
        <h2>Reservation Analytics</h2>
    </div>
    
    <div class="row">
        <!-- Gender Distribution Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Reservations by Gender</h5>
                </div>
                <div class="card-body">
                    <div id="genderChart"></div>
                </div>
            </div>
        </div>
        
        <!-- Department Distribution Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Reservations by Department</h5>
                </div>
                <div class="card-body">
                    <div id="departmentChart"></div>
                </div>
            </div>
        </div>
        
        <!-- College Distribution Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Reservations by College</h5>
                </div>
                <div class="card-body">
                    <div id="collegeChart"></div>
                </div>
            </div>
        </div>
        
        <!-- Role Distribution Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Reservations by User Role</h5>
                </div>
                <div class="card-body">
                    <div id="roleChart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gender Distribution Chart
    const genderChart = new ApexCharts(document.querySelector("#genderChart"), {
        series: @json($gender['series']),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: @json($gender['labels']),
        colors: ['#3B82F6', '#EC4899'],
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });
    genderChart.render();

    // Department Distribution Chart
    const departmentChart = new ApexCharts(document.querySelector("#departmentChart"), {
        series: @json($department['series']),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: @json($department['labels']),
        colors: ['#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#8B5CF6'],
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });
    departmentChart.render();

    // College Distribution Chart
    const collegeChart = new ApexCharts(document.querySelector("#collegeChart"), {
        series: @json($college['series']),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: @json($college['labels']),
        colors: ['#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#8B5CF6', '#6366F1'],
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });
    collegeChart.render();

    // Role Distribution Chart
    const roleChart = new ApexCharts(document.querySelector("#roleChart"), {
        series: @json($role['series']),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: @json($role['labels']),
        colors: ['#10B981', '#F59E0B', '#3B82F6'],
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });
    roleChart.render();
});
</script>
@endpush

@push('styles')
    <style>
/* Analytics Dashboard Styles */
.analytics-dashboard .card {
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.analytics-dashboard .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.analytics-dashboard .card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.analytics-dashboard .card-body {
    padding: 20px;
}
        </style>
@endpush