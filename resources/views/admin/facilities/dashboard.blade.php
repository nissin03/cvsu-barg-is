@extends('layouts.admin')
@section('content')
    <main class="container">
        <div class="main-content-inner">
            <div class="container mt-6">
                <div class="container my-5">
                    <div class="row g-4">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-calendar-check text-blue-500"></i>
                                        </div>
                                        <div class="trend-indicator positive">
                                            <i class="fas fa-trending-up"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Total Reservations</p>
                                        <h3 class="stats-value mb-0">{{ $dashboardData['total_reservations'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-peso-sign text-green-500"></i>
                                        </div>
                                        <div class="trend-indicator positive">
                                            <i class="fas fa-trending-up"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Total Revenue</p>
                                        <h3 class="stats-value mb-0">
                                            ₱{{ number_format($dashboardData['total_revenue'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-check-circle text-purple-500"></i>
                                        </div>
                                        <div class="trend-indicator positive">
                                            <i class="fas fa-trending-up"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Completed</p>
                                        <h3 class="stats-value mb-0">{{ $dashboardData['completed_reservations'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-clock text-yellow-500"></i>
                                        </div>
                                        <div class="trend-indicator neutral">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Pending</p>
                                        <h3 class="stats-value mb-0">{{ $dashboardData['pending_reservations'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-times-circle text-red-500"></i>
                                        </div>
                                        <div class="trend-indicator negative">
                                            <i class="fas fa-trending-down"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Canceled</p>
                                        <h3 class="stats-value mb-0">{{ $dashboardData['canceled_reservations'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="stats-card card border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="icon-wrapper">
                                            <i class="fas fa-home text-gray-500"></i>
                                        </div>
                                        <div class="trend-indicator positive">
                                            <i class="fas fa-trending-up"></i>
                                        </div>
                                    </div>
                                    <div class="stats-content">
                                        <p class="stats-label mb-2">Active Facilities</p>
                                        <h3 class="stats-value mb-0">{{ $dashboardData['active_facilities'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $hasGenderData = !empty($gender['series']) && array_sum($gender['series']) > 0;
                    $hasDepartmentData = !empty($department['series']) && array_sum($department['series']) > 0;
                    $hasCollegeData = !empty($college['series']) && array_sum($college['series']) > 0;
                    $hasRoleData = !empty($role['series']) && array_sum($role['series']) > 0;
                @endphp

                <div class="tf-section analytics-dashboard mb-5">
                    <div class="section-title mb-2">
                        <h2 class="fw-bold fs-3 text-gray-800">Reservation Analytics</h2>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div
                                    class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-semibold text-gray-700">
                                        <i class="fas fa-venus-mars me-2 text-primary"></i>Reservations by Sex
                                    </h5>
                                    @if ($hasGenderData)
                                        <button class="btn btn-sm btn-outline-primary fullscreen-btn"
                                            data-target="genderChart">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body p-3">
                                    @if ($hasGenderData)
                                        <div id="genderChart" class="apex-chart"></div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center py-5">
                                            <span class="text-muted">No reservation data available for sex.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div
                                    class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-semibold text-gray-700">
                                        <i class="fas fa-building me-2 text-success"></i>Reservations by College
                                    </h5>
                                    @if ($hasDepartmentData)
                                        <button class="btn btn-sm btn-outline-primary fullscreen-btn"
                                            data-target="departmentChart">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body p-3">
                                    @if ($hasDepartmentData)
                                        <div id="departmentChart" class="apex-chart"></div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center py-5">
                                            <span class="text-muted">No reservation data available for colleges.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div
                                    class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-semibold text-gray-700">
                                        <i class="fas fa-graduation-cap me-2 text-purple"></i>Reservations by Courses
                                    </h5>
                                    @if ($hasCollegeData)
                                        <button class="btn btn-sm btn-outline-primary fullscreen-btn"
                                            data-target="collegeChart">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body p-3">
                                    @if ($hasCollegeData)
                                        <div id="collegeChart" class="apex-chart"></div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center py-5">
                                            <span class="text-muted">No reservation data available for courses.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div
                                    class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-semibold text-gray-700">
                                        <i class="fas fa-users me-2 text-warning"></i>User Reservation
                                    </h5>
                                    @if ($hasRoleData)
                                        <button class="btn btn-sm btn-outline-primary fullscreen-btn"
                                            data-target="roleChart">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body p-3">
                                    @if ($hasRoleData)
                                        <div id="roleChart" class="apex-chart"></div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center py-5">
                                            <span class="text-muted">No reservation data available for user roles.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="chartModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-right">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="chartModalTitle"></h5>
                                <button type="button" class="btn btn-secondary ms-2" id="closeModalBtn">
                                    <i class="fas fa-times me-1"></i>
                                </button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="fullscreenChart" style="height: 70vh;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tf-section mb-30 mt-5">
                    <div class="wg-box">
                        <div class="flex items-center justify-content-between mb-4">
                            <h5><i class="fas fa-clock me-2"></i>Recent Reservations</h5>
                            <div class="dropdown default">
                                <a class="btn btn-secondary dropdown-toggle"
                                    href="{{ route('admin.facilities.reservations') }}">
                                    <span class="view-all">View all</span>
                                </a>
                            </div>
                        </div>
                        <div class="wg-table table-all-user">
                            <div class="table-responsive">
                                <table class="table table-modern" style="table-layout: auto;">
                                    <thead class="table-header-modern">
                                        <tr>
                                            <th class="text-start name-column">User</th>
                                            <th class="text-center facility-column">Facility</th>
                                            <th class="text-center type-column">Type</th>
                                            <th class="text-center date-column">Reservation Dates</th>
                                            <th class="text-center price-column">Total Price</th>
                                            <th class="text-center status-column">Status</th>
                                            <th class="text-center action-column">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reservations as $payment)
                                            @php
                                                $availability = $payment->availability;
                                                $facility = $availability->facility ?? null;
                                                $facilityAttribute = $availability->facilityAttribute ?? null;

                                                $groupedDates = [];
                                                if (
                                                    $payment->grouped_availabilities &&
                                                    $payment->grouped_availabilities->isNotEmpty()
                                                ) {
                                                    $sortedAvailabilities = $payment->grouped_availabilities->sortBy(
                                                        'date_from',
                                                    );
                                                    $currentGroup = [];

                                                    foreach ($sortedAvailabilities as $avail) {
                                                        if (empty($currentGroup)) {
                                                            $currentGroup = [
                                                                'start' => $avail->date_from,
                                                                'end' => $avail->date_to,
                                                            ];
                                                        } elseif (
                                                            Carbon\Carbon::parse($currentGroup['end'])
                                                                ->addDay()
                                                                ->format('Y-m-d') === $avail->date_from
                                                        ) {
                                                            $currentGroup['end'] = $avail->date_to;
                                                        } else {
                                                            $groupedDates[] = $currentGroup;
                                                            $currentGroup = [
                                                                'start' => $avail->date_from,
                                                                'end' => $avail->date_to,
                                                            ];
                                                        }
                                                    }

                                                    if (!empty($currentGroup)) {
                                                        $groupedDates[] = $currentGroup;
                                                    }
                                                } elseif ($availability) {
                                                    $groupedDates[] = [
                                                        'start' => $availability->date_from,
                                                        'end' => $availability->date_to,
                                                    ];
                                                }
                                            @endphp
                                            <tr class="table-row-modern">
                                                <td class="name-cell">
                                                    <div class="customer-info">
                                                        <div class="customer-name" style="font-size: 1.3rem;">
                                                            {{ $payment->user->name ?? '--' }}
                                                            @if (optional($payment->user)->utype === 'ADM')
                                                                <span class="badge badge-admin ms-1"
                                                                    style="font-size: 0.9rem;">Admin</span>
                                                            @endif
                                                        </div>
                                                        <div class="customer-email" style="font-size: 1.1rem;">
                                                            {{ $payment->user->email ?? '--' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center facility-cell" style="font-size: 1.2rem;">
                                                    @if ($facility)
                                                        <span class="facility-text">{{ $facility->name }}</span>
                                                        @if ($facilityAttribute && $facilityAttribute->room_name)
                                                            <div class="room-name" style="font-size: 1rem;">
                                                                {{ $facilityAttribute->room_name }}
                                                                (Capacity: {{ $facilityAttribute->capacity }})
                                                            </div>
                                                        @endif
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td class="text-center type-cell" style="font-size: 1.2rem;">
                                                    @if ($facility)
                                                        @php
                                                            $type = $facility->facility_type;
                                                            $typeLabels = [
                                                                'individual' => 'Individual',
                                                                'whole_place' => 'Whole Place',
                                                                'both' => 'Both',
                                                            ];
                                                        @endphp
                                                        <span class="badge badge-{{ str_replace('_', '-', $type) }}"
                                                            style="font-size: 1rem;">
                                                            {{ $typeLabels[$type] ?? ucfirst($type) }}
                                                        </span>
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td class="text-center date-cell" style="font-size: 1.2rem;">
                                                    @if (!empty($groupedDates))
                                                        <div class="date-ranges">
                                                            @foreach ($groupedDates as $range)
                                                                <div class="date-range"
                                                                    style="font-size: 1.1rem; margin-bottom: 2px;">
                                                                    @if ($range['start'] === $range['end'])
                                                                        {{ \Carbon\Carbon::parse($range['start'])->format('M j, Y') }}
                                                                    @else
                                                                        @php
                                                                            $startDate = \Carbon\Carbon::parse(
                                                                                $range['start'],
                                                                            );
                                                                            $endDate = \Carbon\Carbon::parse(
                                                                                $range['end'],
                                                                            );
                                                                        @endphp
                                                                        @if ($startDate->format('M') === $endDate->format('M'))
                                                                            {{ $startDate->format('M j') }} -
                                                                            {{ $endDate->format('j, Y') }}
                                                                        @else
                                                                            {{ $startDate->format('M j') }} -
                                                                            {{ $endDate->format('M j, Y') }}
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td class="text-center price-cell" style="font-size: 1.3rem;">
                                                    <span
                                                        class="total-price">₱{{ number_format($payment->total_price, 2) }}</span>
                                                </td>
                                                <td class="text-center status-cell" style="font-size: 1.2rem;">
                                                    @if ($payment->status == 'completed')
                                                        <span class="badge badge-completed"
                                                            style="font-size: 1rem;">Completed</span>
                                                    @elseif($payment->status == 'canceled')
                                                        <span class="badge badge-canceled"
                                                            style="font-size: 1rem;">Canceled</span>
                                                    @elseif($payment->status == 'reserved')
                                                        <span class="badge badge-reserved"
                                                            style="font-size: 1rem;">Reserved</span>
                                                    @else
                                                        <span class="badge badge-pending"
                                                            style="font-size: 1rem;">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="text-center action-cell">
                                                    <div class="d-flex justify-content-center">
                                                        <a href="{{ route('admin.facilities.reservations.show', $payment->id) }}"
                                                            class="btn btn-sm btn-primary" title="View Details"
                                                            data-bs-toggle="tooltip" style="font-size: 1.2rem;">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="table-row-modern">
                                                <td colspan="7" class="text-center py-4">
                                                    No recent reservations found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('styles')
    <style>
        .container {
            max-width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1rem;
            table-layout: auto;
        }

        .table th,
        .table td {
            padding: 20px;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table th {
            font-weight: bold;
            text-align: center;
        }

        .table thead {
            background-color: #f1f3f5;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table-header-modern {
            background: #f8f9fa;
            color: #333;
        }

        .table-header-modern th {
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 18px 16px;
            border: none;
            white-space: nowrap;
            color: #333;
        }

        .table-row-modern {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f3f5;
        }

        .table-row-modern:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table-row-modern:last-child {
            border-bottom: none;
        }

        .table-row-modern td {
            padding: 16px;
            border: none;
            vertical-align: middle;
            font-size: 1.2rem;
        }

        .name-column {
            min-width: 200px;
            width: 20%;
        }

        .facility-column {
            min-width: 180px;
            width: 20%;
        }

        .type-column {
            min-width: 100px;
            width: 10%;
        }

        .date-column {
            min-width: 150px;
            width: 15%;
        }

        .price-column {
            min-width: 100px;
            width: 10%;
        }

        .status-column {
            min-width: 120px;
            width: 10%;
        }

        .action-column {
            min-width: 80px;
            width: 5%;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .customer-name {
            font-weight: 600;
            font-size: 1.3rem;
            color: #333;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }

        .customer-email {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .facility-text {
            color: #333;
            font-weight: 500;
            font-size: 1.2rem;
        }

        .room-name {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 4px;
        }

        .date-range {
            padding: 4px 8px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 1.2rem;
        }

        .total-price {
            color: #333;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .stats-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.25s ease;
            background: rgba(0, 0, 0, 0.05);
        }

        .stats-card:hover .icon-wrapper {
            transform: scale(1.05);
            background: rgba(0, 0, 0, 0.08);
        }

        .stats-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }

        .stats-value {
            color: #333;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .trend-indicator {
            display: flex;
            align-items: center;
            font-size: 1rem;
        }

        .trend-indicator.positive {
            color: #10b981;
        }

        .trend-indicator.negative {
            color: #ef4444;
        }

        .trend-indicator.neutral {
            color: #6b7280;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-individual {
            background: linear-gradient(45deg, #8b5cf6, #7c3aed);
            color: white;
        }

        .badge-whole-place {
            background: linear-gradient(45deg, #f97316, #ea580c);
            color: white;
        }

        .badge-both {
            background: linear-gradient(45deg, #facc15, #eab308);
            color: white;
        }

        .badge-completed {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
        }

        .badge-canceled {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
        }

        .badge-reserved {
            background: linear-gradient(45deg, #3b82f6, #1e40af);
            color: white;
        }

        .badge-pending {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            color: white;
        }

        .badge-admin {
            background: #f31a1a;
            color: white;
            font-size: 0.8rem;
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .text-blue-500 {
            color: #3b82f6 !important;
        }

        .text-green-500 {
            color: #10b981 !important;
        }

        .text-purple-500 {
            color: #8b5cf6 !important;
        }

        .text-yellow-500 {
            color: #f59e0b !important;
        }

        .text-red-500 {
            color: #ef4444 !important;
        }

        .text-gray-500 {
            color: #6b7280 !important;
        }

        .text-purple {
            color: #8b5cf6;
        }

        .analytics-dashboard .section-title h2 {
            color: #1a202c;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .analytics-dashboard .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .analytics-dashboard .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .analytics-dashboard .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background-color: #f8fafc;
        }

        .analytics-dashboard .card-header h5 {
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }

        .analytics-dashboard .card-header h5 i {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin-right: 8px;
        }

        .analytics-dashboard .apex-chart {
            min-height: 300px;
        }

        .analytics-card .card-body {
            padding: 25px;
            background: #fff;
            position: relative;
        }

        .apexcharts-toolbar {
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e5e7eb !important;
            padding: 8px !important;
        }

        .apexcharts-menu {
            background: white !important;
            border-radius: 8px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #e5e7eb !important;
            padding: 8px 0 !important;
        }

        .apexcharts-menu-item {
            padding: 10px 16px !important;
            font-size: 14px !important;
            color: #374151 !important;
            transition: all 0.2s ease !important;
            border-radius: 4px !important;
            margin: 2px 8px !important;
        }

        .apexcharts-menu-item:hover {
            background-color: #f3f4f6 !important;
            color: #111827 !important;
        }

        .apexcharts-toolbar-custom-icon svg {
            width: 18px !important;
            height: 18px !important;
        }

        .apexcharts-menu-icon svg {
            width: 16px !important;
            height: 16px !important;
            margin-right: 8px !important;
        }

        .action-cell .btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 50%;
        }

        .action-cell .btn i {
            font-size: 1.2rem;
        }

        .action-cell .btn-primary {
            background: #6c757d;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-cell .btn-primary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .date-ranges {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        #chartModal .modal-content {
            height: 90vh;
        }

        #fullscreenChart {
            width: 100%;
            height: 100%;
            min-height: 500px;
        }

        .fullscreen-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .fullscreen-btn i {
            transition: transform 0.2s;
        }

        .fullscreen-btn:hover i {
            transform: scale(1.1);
        }

        .modal-dialog-right {
            margin: 0;
            margin-left: 500px;
            height: 100%;
        }

        .modal-dialog-right .modal-content {
            height: 100%;
            border-radius: 0;
        }

        #closeModalBtn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        #closeModalBtn:hover {
            background-color: #5c636a;
            transform: translateY(-1px);
        }

        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .modal-title {
            font-weight: 600;
            color: #333;
        }

        @media (max-width: 768px) {
            .table-header-modern th {
                padding: 12px 8px;
                font-size: 1rem;
            }

            .table-row-modern td {
                padding: 12px 8px;
            }

            .customer-name {
                font-size: 1.1rem;
            }

            .customer-email {
                font-size: 0.9rem;
            }

            .facility-text {
                font-size: 1.1rem;
            }

            .date-range {
                font-size: 1rem;
            }

            .total-price {
                font-size: 1.3rem;
            }

            .badge {
                font-size: 1rem;
                padding: 0.4em 0.7em;
            }

            .badge-admin {
                font-size: 0.7rem;
                padding: 0.2em 0.5em;
            }

            .stats-value {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {

            .name-column,
            .facility-column,
            .date-column,
            .type-column,
            .price-column,
            .status-column,
            .action-column {
                min-width: auto;
                width: auto;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .customer-name {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasGenderData = @json($hasGenderData);
            const hasDepartmentData = @json($hasDepartmentData);
            const hasCollegeData = @json($hasCollegeData);
            const hasRoleData = @json($hasRoleData);

            const genderSeries = @json($gender['series']);
            const genderLabels = @json($gender['labels']);

            const departmentSeries = @json($department['series']);
            const departmentLabels = @json($department['labels']);

            const collegeSeries = @json($college['series']);
            const collegeLabels = @json($college['labels']);

            const roleSeries = @json($role['series']);
            const roleLabels = @json($role['labels']);

            let genderChart = null;
            let departmentChart = null;
            let collegeChart = null;
            let roleChart = null;

            if (hasGenderData) {
                genderChart = new ApexCharts(document.querySelector("#genderChart"), getGenderChartOptions());
                genderChart.render();
            }

            if (hasDepartmentData) {
                departmentChart = new ApexCharts(document.querySelector("#departmentChart"),
                    getDepartmentChartOptions());
                departmentChart.render();
            }

            if (hasCollegeData) {
                collegeChart = new ApexCharts(document.querySelector("#collegeChart"), getCollegeChartOptions());
                collegeChart.render();
            }

            if (hasRoleData) {
                roleChart = new ApexCharts(document.querySelector("#roleChart"), getRoleChartOptions());
                roleChart.render();
            }

            const chartModal = new bootstrap.Modal(document.getElementById('chartModal'));
            const fullscreenChartEl = document.getElementById('fullscreenChart');
            const chartModalTitle = document.getElementById('chartModalTitle');

            document.getElementById('closeModalBtn').addEventListener('click', function() {
                chartModal.hide();
            });

            const chartConfigs = {
                genderChart: {
                    options: getGenderChartOptions(),
                    type: 'pie'
                },
                departmentChart: {
                    options: getDepartmentChartOptions(),
                    type: 'bar'
                },
                collegeChart: {
                    options: getCollegeChartOptions(),
                    type: 'bar'
                },
                roleChart: {
                    options: getRoleChartOptions(),
                    type: 'pie'
                }
            };

            let fullscreenChart = null;

            document.querySelectorAll('.fullscreen-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    const config = chartConfigs[target];

                    const cardHeader = this.closest('.card-header');
                    const title = cardHeader.querySelector('h5').textContent.trim();
                    chartModalTitle.textContent = title;

                    if (fullscreenChart) {
                        fullscreenChart.destroy();
                    }

                    const options = JSON.parse(JSON.stringify(config.options));
                    options.chart = {
                        ...options.chart,
                        type: config.type,
                        height: '100%',
                        width: '100%'
                    };

                    fullscreenChart = new ApexCharts(fullscreenChartEl, options);
                    fullscreenChart.render();

                    chartModal.show();
                });
            });

            chartModal._element.addEventListener('hidden.bs.modal', function() {
                if (fullscreenChart) {
                    fullscreenChart.destroy();
                    fullscreenChart = null;
                }
                fullscreenChartEl.innerHTML = '';
            });

            function getGenderChartOptions() {
                return {
                    series: genderSeries,
                    chart: {
                        type: 'pie',
                        height: 350,
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
                        },
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
                            },
                            export: {
                                csv: {
                                    filename: 'gender_reservation'
                                },
                                svg: {
                                    filename: 'gender_reservation'
                                },
                                png: {
                                    filename: 'gender_reservation'
                                }
                            }
                        }
                    },
                    title: {
                        text: 'Sex Reservation',
                        align: 'center',
                        style: {
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#2c5282'
                        },
                        margin: 20
                    },
                    labels: genderLabels,
                    colors: ['#3B82F6', '#EC4899'],
                    legend: {
                        position: 'bottom',
                        fontSize: '14px',
                        fontFamily: 'Arial, sans-serif'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '0%'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex] + " (" + val.toFixed(1) + "%)"
                        }
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
                };
            }

            function getDepartmentChartOptions() {
                return {
                    series: [{
                        name: 'Reservations',
                        data: departmentSeries
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
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
                        },
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
                            },
                            export: {
                                csv: {
                                    filename: 'College_reservation'
                                },
                                svg: {
                                    filename: 'College_reservation'
                                },
                                png: {
                                    filename: 'College_reservation'
                                }
                            }
                        }
                    },
                    title: {
                        text: 'College Reservation',
                        align: 'center',
                        style: {
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#2c5282'
                        },
                        margin: 20
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            distributed: true,
                            dataLabels: {
                                position: 'center'
                            }
                        }
                    },
                    colors: ['#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#8B5CF6', '#EF4444', '#06B6D4', '#84CC16',
                        '#F97316'
                    ],
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val;
                        },
                        style: {
                            colors: ['#fff'],
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    xaxis: {
                        categories: departmentLabels,
                        title: {
                            text: 'Number of Reservations',
                            style: {
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#374151'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            style: {
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#374151'
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        show: true,
                        borderColor: '#e5e7eb',
                        strokeDashArray: 0,
                        position: 'back',
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: false
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + " reservations";
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 300
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: true
                                }
                            }
                        }
                    }]
                };
            }

            function getCollegeChartOptions() {
                return {
                    series: [{
                        name: 'Reservations',
                        data: collegeSeries
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
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
                        },
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
                            },
                            export: {
                                csv: {
                                    filename: 'Courses_reservation'
                                },
                                svg: {
                                    filename: 'Courses_reservation'
                                },
                                png: {
                                    filename: 'Courses_reservation'
                                }
                            }
                        }
                    },
                    title: {
                        text: 'Courses Reservation',
                        align: 'center',
                        style: {
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#2c5282'
                        },
                        margin: 20
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            distributed: true,
                            dataLabels: {
                                position: 'center'
                            }
                        }
                    },
                    colors: ['#10B981', '#F59E0B', '#3B82F6', '#EC4899', '#8B5CF6', '#6366F1', '#EF4444', '#06B6D4',
                        '#84CC16'
                    ],
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val;
                        },
                        style: {
                            colors: ['#fff'],
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    xaxis: {
                        categories: collegeLabels,
                        title: {
                            text: 'Number of Reservations',
                            style: {
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#374151'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            style: {
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#374151'
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        show: true,
                        borderColor: '#e5e7eb',
                        strokeDashArray: 0,
                        position: 'back',
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: false
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + " reservations";
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 300
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: true
                                }
                            }
                        }
                    }]
                };
            }

            function getRoleChartOptions() {
                return {
                    series: roleSeries,
                    chart: {
                        type: 'pie',
                        height: 350,
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
                        },
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
                            },
                            export: {
                                csv: {
                                    filename: 'role_reservation'
                                },
                                svg: {
                                    filename: 'role_reservation'
                                },
                                png: {
                                    filename: 'role_reservation'
                                }
                            }
                        }
                    },
                    title: {
                        text: 'User Reservation',
                        align: 'center',
                        style: {
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#2c5282'
                        },
                        margin: 20
                    },
                    labels: roleLabels,
                    colors: ['#10B981', '#F59E0B', '#3B82F6'],
                    legend: {
                        position: 'bottom',
                        fontSize: '14px',
                        fontFamily: 'Arial, sans-serif'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '0%'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex] + " (" + val.toFixed(1) + "%)"
                        }
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
                };
            }

            document.querySelectorAll('.approve-btn, .reject-btn, .complete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const reservationId = this.getAttribute('data-id');
                    const action = this.classList.contains('approve-btn') ? 'approve' :
                        this.classList.contains('reject-btn') ? 'reject' : 'complete';

                    if (confirm(`Are you sure you want to ${action} this reservation?`)) {
                        fetch(`/admin/reservations/${reservationId}/${action}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    alert(data.message || 'An error occurred');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while processing your request');
                            });
                    }
                });
            });
        });
    </script>
@endpush
