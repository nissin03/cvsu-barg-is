@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reservation History</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>

                <li>
                    <a href="">
                        <div class="text-tiny">Reservation</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>

                <li>
                    <div class="text-tiny">Reservation History</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <h5>Reservation ID: </h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th class="user-email-column">User Email</th> <!-- Custom class for wider user email -->
                            <th class="admin-email-column">Admin Email</th> <!-- Custom class for wider admin email -->
                            <th>Payment Status</th>
                            <th>Rent Status</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <tr>
                            <td></td>
                            <td class="user-email-column"></td>
                            <td class="admin-email-column"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 10px;
        text-align: left;
        vertical-align: top; /* Align text at the top to avoid overlap */
    }

    /* Specific styling for user and admin email columns */
    .user-email-column, .admin-email-column {
        min-width: 200px; /* Set minimum width */
        max-width: 250px; /* Set maximum width */
        white-space: normal; /* Allow text to wrap to prevent overlap */
        word-wrap: break-word; /* Break long words if necessary */
        overflow-wrap: break-word; /* Additional word wrapping for compatibility */
    }
</style>
@endpush
