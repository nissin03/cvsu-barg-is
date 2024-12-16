<!-- resources/views/admin/reservation.blade.php -->

@extends('layouts.admin')

@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Reservations</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Reservations</div>
                </li>
            </ul>
        </div>

       
            <!-- <div class="alert alert-success alert-dismissible fade show" role="alert">  
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> -->
        
    
        
            <!-- <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> -->
       


        

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="">
                        <fieldset class="name">
                            <input type="text" placeholder="Search here..." class="" name="search"
                                tabindex="2" value="" aria-required="true">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>           
            </div>
        
            
            
        

            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Facility Name</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Reservation Date</th>
                                <th>Available Room</th> <!-- New Column -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                           
                            <tr>
                                <td class="text-center">
                                    <div></div>
                                    <div style="font-size: 0.9rem; color: #808080;">  <!-- This is a greyish color -->
                                        <a href="" target="_blank" style="text-decoration: none; color: inherit;">
                                           
                                        </a>
                                    </div>
                                </td>
                                
                                <td class="text-center"></td>
                                

                                <td>
                                 
                                </td>
                                <meta name="csrf-token" content="{{ csrf_token() }}">


                                <td>
                                    
                                </td>

                                


                          
                                <td class="text-center">
                                    
                                </td>
                                
                                
                                <td class="text-center">
                                       
                                </td>
                                
                                
                                
                                <td>
                                    <div class="list-icon-function-wrapper d-flex justify-content-between" style="flex-direction: row">
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.facilities.reservations-events') }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.facilities.reservations-history') }}">
                                                <div class="item eye">
                                                    <i class="icon-eye"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="list-icon-function">
                                            <a href="" download>
                                                <div class="item download">
                                                    <i class="icon-download"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="divider"></div>
            {{-- <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $reservations->links('pagination::bootstrap-5') }}
            </div> --}}
        </div>
    </div>
</div>

@endsection


@push('styles')
<style>
    /* Make the table responsive */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 15px;
        text-align: left;
        font-size: 14px;
    }

    /* Make the table elements more readable on smaller screens */
    .table td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Styling for the Available Room list */
    .table ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }

    .table li {
        margin-bottom: 5px;
    }

    .room-number-container {
        position: relative;
        cursor: pointer;
    }

    .room-number-link {
        text-decoration: none;
        font-weight: normal;
    }

    .tooltip {
        display: none;
        position: absolute;
        background-color: #333;
        color: white;
        padding: 8px;
        border-radius: 4px;
        z-index: 9999; /* Ensure the tooltip appears above other elements */
        font-size: 12px;
        max-width: 200px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        opacity: 0;  /* Initially hidden */
        transition: opacity 0.3s ease-in-out;  /* Smooth transition */
    }

    .room-number-container:hover .tooltip {
        display: block;
        opacity: 1; /* Fade in effect */
    }

    /* Make form fields and buttons responsive */
    .form-search input[type="text"], .form-search button {
        width: 100%;
        margin: 10px 0;
    }

    .wg-box {
        padding: 15px;
    }

    /* Media Query for smaller devices */
    @media (max-width: 768px) {
        .wg-table {
            font-size: 14px;
        }

        /* Stack table rows into single column for small screens */
        .wg-table td {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
        }

        .wg-table th {
            display: none;
        }

        /* Adjust available room list for smaller screens */
        .room-number-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .room-number-link {
            font-size: 14px;
        }

        /* Stack actions vertically on small screens */
        .list-icon-function-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .list-icon-function {
            width: 100%;
        }
    }

    /* Media Query for very small devices */
    @media (max-width: 480px) {
        .wg-table {
            font-size: 12px;
        }

        .room-number-container {
            font-size: 12px;
        }

        .tooltip {
            font-size: 10px;
            max-width: 150px;
        }
    }
</style>
@endpush


@push('scripts')



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 CDN --> --
<script>

</script>

@endpush

