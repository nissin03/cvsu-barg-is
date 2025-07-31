@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Facility Reports</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Facility Reports</div></li>
            </ul>
        </div>

        <div class="container mx-auto">
            <div class="row row-cols-1 row-cols-md-3 g-4 my-4" id="summary-cards">
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-shopping-bag"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Reservation Transactions</div>
                            <h4 id="total-reservations">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-wallet"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Amount Reserved</div>
                            <h4 id="total-amount-reserved">₱0.00</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-check-circle"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Completed Transactions</div>
                            <h4 id="total-completed">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-cancel"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Canceled Reservations</div>
                            <h4 id="total-canceled">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-clock"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Pending Reservations</div>
                            <h4 id="total-pending">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 text-center shadow-sm border-0">
                        <div class="image ic-bg me-3">
                            <i class="icon-building"></i>
                        </div>
                        <div>
                            <div class="body-text mb-2">Total Facilities Reserved</div>
                            <h4 id="total-facilities-reserved">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wg-box">
              <div class="d-flex align-items-end mb-4">
                    <div class="mb-3">
                        <a href="{{ route('admin.facility.reports.downloadFacilityPdf') }}" class="btn btn-success" id="downloadFacilityPdfBtn">
                            <i class="icon-download"></i> Download Facility PDF
                        </a>
                    </div>
              </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filter" class="form-label">Report Type:</label>
                    <select id="filter" class="form-control">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected>Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                
                <!-- Daily Filters -->
                <div id="dailyFilters" class="filter-group col-md-9" style="display: none;">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="dailyWeek" class="form-label">Week:</label>
                            <select id="dailyWeek" class="form-control">
                                <option value="">Select Week</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dailyMonth" class="form-label">Month:</label>
                            <select id="dailyMonth" class="form-control">
                                <option value="">Select Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dailyYear" class="form-label">Year:</label>
                            <select id="dailyYear" class="form-control">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Weekly Filters -->
                <div id="weeklyFilters" class="filter-group col-md-9" style="display: none;">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="weeklyMonth" class="form-label">Month:</label>
                            <select id="weeklyMonth" class="form-control">
                                <option value="">Select Month</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="weeklyYear" class="form-label">Year:</label>
                            <select id="weeklyYear" class="form-control">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly Filters -->
                <div id="monthlyFilters" class="filter-group col-md-9">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="monthlyYear" class="form-label">Year:</label>
                            <select id="monthlyYear" class="form-control">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Yearly Filters -->
                <div id="yearlyFilters" class="filter-group col-md-9" style="display: none;">
                    <!-- No additional filters for yearly -->
                </div>
            </div>

            <div id="revenueChart" style="min-height: 400px;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    console.log('Script loaded');
    
    const chartEl = document.querySelector("#revenueChart");
    console.log('Chart element:', chartEl);
    
    let chart;
    let filterOptions = {};
    

    async function loadFilterOptions() {
        try {
            const response = await fetch('{{ route("admin.facility.reports.filter-options") }}');
            filterOptions = await response.json();
            console.log('Filter options loaded:', filterOptions);

            populateFilterDropdowns();
        } catch (error) {
            console.error('Error loading filter options:', error);
        }
    }
    

    function populateFilterDropdowns() {

        const dailyWeekSelect = document.getElementById('dailyWeek');
        if (dailyWeekSelect && filterOptions.weekly) {
            filterOptions.weekly.forEach(week => {
                const option = document.createElement('option');
                option.value = week.week_number;
                option.textContent = week.name;
                dailyWeekSelect.appendChild(option);
            });
        }
        
        const dailyMonthSelect = document.getElementById('dailyMonth');
        const weeklyMonthSelect = document.getElementById('weeklyMonth');
        
        if (filterOptions.monthly) {
            filterOptions.monthly.forEach(month => {

                if (dailyMonthSelect) {
                    const option = document.createElement('option');
                    option.value = getMonthNumber(month.name);
                    option.textContent = month.name;
                    dailyMonthSelect.appendChild(option);
                }
                

                if (weeklyMonthSelect) {
                    const option = document.createElement('option');
                    option.value = getMonthNumber(month.name);
                    option.textContent = month.name;
                    weeklyMonthSelect.appendChild(option);
                }
            });
        }
        const yearSelects = ['dailyYear', 'weeklyYear', 'monthlyYear'];
        if (filterOptions.years) {
            yearSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    filterOptions.years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.value;
                        option.textContent = year.label;
                        select.appendChild(option);
                    });
                }
            });
        }
    }
    
    function getMonthNumber(monthName) {
        const months = {
            'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6,
            'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12
        };
        return months[monthName] || 1;
    }

    function toggleFilters() {
        console.log('Toggling filters...');
        const filterType = document.getElementById('filter').value;
        const filterGroups = ['dailyFilters', 'weeklyFilters', 'monthlyFilters', 'yearlyFilters'];
        
        filterGroups.forEach(groupId => {
            const group = document.getElementById(groupId);
            if (group) {
                group.style.display = groupId === filterType + 'Filters' ? 'block' : 'none';
            }
        });
    }

    function getFilterParams() {
        const filterType = document.getElementById('filter').value;
        let params = `filter=${filterType}`;
        
        switch (filterType) {
            case 'daily':
                const dailyWeek = document.getElementById('dailyWeek').value;
                const dailyMonth = document.getElementById('dailyMonth').value;
                const dailyYear = document.getElementById('dailyYear').value;
                if (dailyWeek) params += `&week=${dailyWeek}`;
                if (dailyMonth) params += `&month=${dailyMonth}`;
                if (dailyYear) params += `&year=${dailyYear}`;
                break;
            case 'weekly':
                const weeklyMonth = document.getElementById('weeklyMonth').value;
                const weeklyYear = document.getElementById('weeklyYear').value;
                if (weeklyMonth) params += `&month=${weeklyMonth}`;
                if (weeklyYear) params += `&year=${weeklyYear}`;
                break;
            case 'monthly':
                const monthlyYear = document.getElementById('monthlyYear').value;
                if (monthlyYear) params += `&year=${monthlyYear}`;
                break;
        }
        
        console.log('Filter params:', params);
        return params;
    }

    async function renderChart() {
        console.log('Rendering chart...');
        try {
            const params = getFilterParams();
            const url = `{{ route('admin.facility.reports.data') }}?${params}`;
            console.log('Fetching from:', url);
            
            const res = await fetch(url);
            console.log('Response status:', res.status);
            
            const json = await res.json();
            console.log('Response data:', json);

            const filterType = json.filter;
            const reportData = json.data;

            const labels = [];
            const values = [];

            if (filterType === 'daily') {
                const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                daysOfWeek.forEach(day => {
                    const dataPoint = reportData.find(item => item.label === day);
                    labels.push(day);
                    values.push(dataPoint ? parseFloat(dataPoint.total) : 0);
                });
            } else if (filterType === 'weekly') {
                reportData.forEach(item => {
                    labels.push(`Week ${item.week}`);
                    values.push(parseFloat(item.total));
                });
            } else if (filterType === 'monthly') {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                               'July', 'August', 'September', 'October', 'November', 'December'];
                months.forEach(month => {
                    const dataPoint = reportData.find(item => item.label === month);
                    labels.push(month);
                    values.push(dataPoint ? parseFloat(dataPoint.total) : 0);
                });
            } else {
                reportData.forEach(item => {
                    labels.push(item.label);
                    values.push(parseFloat(item.total));
                });
            }

            console.log('Chart labels:', labels);
            console.log('Chart values:', values);

            const options = {
                chart: {
                    type: 'bar',
                    height: 400
                },
                series: [{
                    name: 'Revenue',
                    data: values
                }],
                xaxis: {
                    categories: labels,
                    title: { text: filterType.charAt(0).toUpperCase() + filterType.slice(1) }
                },
                yaxis: {
                    title: { text: '₱ Revenue' }
                },
                tooltip: {
                    y: {
                        formatter: val => `₱ ${val.toFixed(2)}`
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                fill: {
                    opacity: 1
                }
            };

            console.log('Chart options:', options);

            if (chart) {
                console.log('Updating existing chart...');
                chart.updateOptions(options);
            } else {
                console.log('Creating new chart...');
                chart = new ApexCharts(chartEl, options);
                chart.render();
            }
        } catch (error) {
            console.error('Error rendering chart:', error);
        }
    }

    document.getElementById('filter').addEventListener('change', function () {
        console.log('Filter changed to:', this.value);
        toggleFilters();
        renderChart();
    });
    
    ['dailyWeek', 'dailyMonth', 'dailyYear', 'weeklyMonth', 'weeklyYear', 'monthlyYear'].forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.addEventListener('change', renderChart);
        }
    });

    async function loadSummary() {
        try {
            const response = await fetch('{{ route('admin.facility.reports.summary') }}');
            const summary = await response.json();
            document.getElementById('total-reservations').textContent = summary.total_reservations;
            document.getElementById('total-amount-reserved').textContent = `₱${parseFloat(summary.total_amount_reserved).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('total-completed').textContent = summary.total_completed;
            document.getElementById('total-canceled').textContent = summary.total_canceled;
            document.getElementById('total-pending').textContent = summary.total_pending;
            document.getElementById('total-facilities-reserved').textContent = summary.total_facilities_reserved;
        } catch (error) {
            console.error('Error loading summary:', error);
        }
    }

    console.log('Initializing...');
    loadFilterOptions().then(() => {
        toggleFilters();
        renderChart();
    });
    loadSummary();

    function getDownloadFacilityPdfUrl() {
        const filterType = document.getElementById('filter').value;
        let url = `{{ route('admin.facility.reports.downloadFacilityPdf') }}?filter=${filterType}`;
        if (filterType === 'daily') {
            const week = document.getElementById('dailyWeek').value;
            const month = document.getElementById('dailyMonth').value;
            const year = document.getElementById('dailyYear').value;
            if (week) url += `&week=${week}`;
            if (month) url += `&month=${month}`;
            if (year) url += `&year=${year}`;
        } else if (filterType === 'weekly') {
            const month = document.getElementById('weeklyMonth').value;
            const year = document.getElementById('weeklyYear').value;
            if (month) url += `&month=${month}`;
            if (year) url += `&year=${year}`;
        } else if (filterType === 'monthly') {
            const year = document.getElementById('monthlyYear').value;
            if (year) url += `&year=${year}`;
        } else if (filterType === 'yearly') {
            const year = document.getElementById('monthlyYear').value;
            if (year) url += `&year=${year}`;
        }
        return url;
    }

    document.getElementById('downloadFacilityPdfBtn').addEventListener('click', function(e) {
        e.preventDefault();
        window.open(getDownloadFacilityPdfUrl(), '_blank');
    });
</script>
@endpush
