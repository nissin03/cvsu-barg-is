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
                        </tr>
                        <tr>
                            <th>Rental Name</th>
                        </tr>
                        <tr id="pool_quantity">
                            <th>Pool Quantity</th>
                        </tr>
                        <tr>
                            <th>Reservation Date</th>
                        </tr>
                        
                        <tr>
                            <th>Status</th>
                        </tr>
                      
                            <tr>
                                <th>Room Number</th>
                            </tr>
                            <tr>
                                <th>Room Capacity</th>                               
                            </tr>
                        
                        <tr>
                            <th>Total Price</th>
                            
                        </tr>
                        
                        <tr>
                            <th>Time Slot</th>
                            
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
                            <td></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td></td>
                        </tr>
                     
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Update Status -->
        <div class="wg-box mt-5">
            <h5>Update Status</h5>
            <div class="table-responsive">
                <form action="" method="POST">
                    @csrf
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <th>Rent Status</th>
                                <td>
                                    
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
