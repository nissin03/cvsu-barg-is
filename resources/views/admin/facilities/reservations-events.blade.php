@extends('layouts.admin')

@section('content')
<style>
    .table-transaction > tbody > tr:nth-of-type(odd) {
        --bs-table-accent-bg: #fff !important;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 10px; /* Reduced padding for a more compact layout */
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .wg-box {
        margin-top: 20px;
        padding: 15px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .text-tiny {
        font-size: 12px;
        color: #999;
    }

    .alert {
        margin-bottom: 20px;
    }

    /* Shorten the width of table header cells */
    .table th {
        width: 150px; /* Set a fixed width for table headers */
        white-space: nowrap; /* Prevent text wrapping */
    }

    .table td {
        white-space: nowrap; /* Prevent text wrapping for table data cells */
    }

    /* Custom styles for select dropdowns */
    .form-select {
        width: 150px; /* Set a fixed width for the dropdowns */
    }

    /* Style for pool quantity input */
    #poolQuantityField {
        display: none; /* Hidden by default */
    }
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reservation Events</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.facilities.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="{{ route('admin.facilities.reservations') }}">
                        <div class="text-tiny">Reservation</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Reservation Events</div>
                </li>
            </ul>
        </div>

        <!-- Display success or error messages -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Reservation Details -->
        <div class="wg-box">
            <h5>Reservation Details</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Reservation ID</th>
                            <td>{{ $availability->id }}</td>
                        </tr>
                        <tr>
                            <th>Rental Name</th>
                            <td>{{ $availability->facility->name }}</td>
                        </tr>
                        <tr id="pool_quantity">
                            <th>Pool Quantity</th>
                        </tr>
                        <tr>
                            <th>Reservation Date</th>
                            <td>{{ $availability->date_from }} - {{ $availability->date_to }}</td>
                        </tr>
                        
                        <tr>
                            <th>Status</th>
                            <td>{{ $availability->status }}</td>
                        </tr>
                      
                            <tr>
                                <th>Room Number</th>
                            </tr>
                            <tr>
                                <th>Room Capacity</th>                               
                            </tr>
                        
                        <tr>
                            <th>Total Price</th>
                            <td>{{ number_format($availability->total_price, 2) }}</td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Details -->
        <div class="wg-box">
            <h5>User Details</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $availability->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $availability->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ $availability->user->phone_number }}</td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td>{{ $availability->user->year_level }}</td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td>{{ $availability->user->department }}</td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td>{{ $availability->user->course }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Status/payment status-->
        <div class="wg-box mt-5">
            <h5>Update Status</h5>
            <div class="table-responsive">
                <form action="{{ route('admin.facilities.reservation.updateStatus', $availability->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Specify that this is a PUT request -->
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    <select name="payment_status" class="form-select" required>
                                        <option value="advance/deposit complete" {{ $availability->payment_status == 'advance/deposit complete' ? 'selected' : '' }}>Advance/Deposit Complete</option>
                                        <option value="1st month complete" {{ $availability->payment_status == '1st month complete' ? 'selected' : '' }}>1st Month Complete</option>
                                        <option value="2nd month complete" {{ $availability->payment_status == '2nd month complete' ? 'selected' : '' }}>2nd Month Complete</option>
                                        <option value="3rd month complete" {{ $availability->payment_status == '3rd month complete' ? 'selected' : '' }}>3rd Month Complete</option>
                                        <option value="4th month complete" {{ $availability->payment_status == '4th month complete' ? 'selected' : '' }}>4th Month Complete</option>
                                        <option value="5th month complete" {{ $availability->payment_status == '5th month complete' ? 'selected' : '' }}>5th Month Complete</option>
                                        <option value="6th month complete" {{ $availability->payment_status == '6th month complete' ? 'selected' : '' }}>6th Month Complete</option>    
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select name="rent_status" class="form-select" required>
                                        <option value="pending" {{ $availability->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="reserved" {{ $availability->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                        <option value="completed" {{ $availability->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="canceled" {{ $availability->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="tf-button btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    
</script>
@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 10px; /* Reduced padding for a more compact layout */
        text-align: left;
    }

    .table th {
        width: 150px; /* Set a fixed width for table headers */
        white-space: nowrap; /* Prevent text wrapping */
    }

    .table td {
        white-space: nowrap; /* Prevent text wrapping for table data cells */
    }

    /* Shorten dropdown widths */
    .form-select {
        width: 150px; /* Set a fixed width for the dropdowns */
    }
</style>
@endpush
