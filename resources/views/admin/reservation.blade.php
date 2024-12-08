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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif


        

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="{{ route('admin.reservation') }}">
                        <fieldset class="name">
                            <input type="text" placeholder="Search here..." class="" name="search"
                                tabindex="2" value="{{ request('search') }}" aria-required="true">
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
                                <th>Rental Name</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Reservation Date</th>
                                <th>Available Room</th> <!-- New Column -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservations as $reservation)
                            <tr>
                                <td class="text-center">
                                    <div>{{ $reservation->user->name }}</div>
                                    <div style="font-size: 0.9rem; color: #808080;">  <!-- This is a greyish color -->
                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $reservation->user->email }}" target="_blank" style="text-decoration: none; color: inherit;">
                                            {{ $reservation->user->email }}
                                        </a>
                                    </div>
                                </td>
                                
                                <td class="text-center">{{ $reservation->rental->name }}</td>
                                

                                <td>
                                    <select 
                                        name="rent_status" 
                                        class="form-select rent-status" 
                                        required 
                                        data-id="{{ $reservation->id }}" 
                                        @if(in_array($reservation->rent_status, ['completed', 'canceled']) || $reservation->status_locked) disabled @endif
                                    >
                                        <option value="pending" {{ $reservation->rent_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="reserved" {{ $reservation->rent_status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                        <option value="completed" {{ $reservation->rent_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="canceled" {{ $reservation->rent_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </td>
                                <meta name="csrf-token" content="{{ csrf_token() }}">


                                <td>
                                    <select name="payment_status" class="form-select payment_status" required data id="payment-status" data-reservation-id="{{ $reservation->id }}">
                                        @if($reservation->type == 'International Convention Center' || 'Rolle Hall' || 'Swimming Pool')  <!-- Check if reservation type is dormitories -->
                                            <!-- Show only these options for dormitories -->
                                            <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
                                        @else
                                            <!-- Show all other options for other reservation types -->
                                            <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="advance/deposit complete" {{ $reservation->payment_status == 'advance/deposit complete' ? 'selected' : '' }}>Advance/Deposit Complete</option>
                                            <option value="1st month complete" {{ $reservation->payment_status == '1st month complete' ? 'selected' : '' }}>1st Month Complete</option>
                                            <option value="2nd month complete" {{ $reservation->payment_status == '2nd month complete' ? 'selected' : '' }}>2nd Month Complete</option>
                                            <option value="3rd month complete" {{ $reservation->payment_status == '3rd month complete' ? 'selected' : '' }}>3rd Month Complete</option>
                                            <option value="4th month complete" {{ $reservation->payment_status == '4th month complete' ? 'selected' : '' }}>4th Month Complete</option>
                                            <option value="5th month complete" {{ $reservation->payment_status == '5th month complete' ? 'selected' : '' }}>5th Month Complete</option>
                                            <option value="6th month complete" {{ $reservation->payment_status == '6th month complete' ? 'selected' : '' }}>6th Month Complete</option>
                                            <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
                                            <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                        @endif
                                    </select>
                                </td>

                                


                          
                                <td class="text-center">
                                    @if($reservation->rental->name === 'International House II')
                                        @if($reservation->dormitoryRoom)
                                            {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date)->format('Y-m-d') }}
                                            to
                                            {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date)->format('Y-m-d') }}
                                        @else
                                            N/A
                                        @endif
                                    @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->start_date)->format('Y-m-d') }}
                                        to
                                        {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->end_date)->format('Y-m-d') }}
                                    @else
                                        {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('Y-m-d') : 'N/A' }}
                                    @endif
                                </td>
                                
                                
                                <td class="text-center">
                                    @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
                                        @php
                                            // Fetch available rooms for the rental ID
                                            $availableRoomsForRental = $availableRooms->get($reservation->rental->id) ?? collect();
                                        @endphp
                                
                                        @if($availableRoomsForRental->count() > 0)
                                            <ul style="list-style: none; padding: 0; margin: 0;">
                                                @foreach($availableRoomsForRental as $room)
                                                    <li class="room-number-container" data-room-id="{{ $room->id }}">
                                                        <a href="#" class="room-number-link">
                                                            Room {{ $room->room_number }}
                                                        </a>
                                                        <div class="tooltip" id="room-tooltip-{{ $room->id }}">
                                                            <strong>Room Capacity:</strong> {{ $room->room_capacity }}<br>
                                                            <strong>Currently Reserved:</strong> {{ $room->reservations_count ?? 0 }}
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span>No available rooms</span>
                                        @endif
                                    @else
                                        <span>N/A</span>
                                    @endif
                                </td>
                                
                                
                                
                                <td>
                                    <div class="list-icon-function-wrapper">
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.reservation-events', ['reservation_id' => $reservation->id]) }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.reservation-history', ['reservation_id' => $reservation->id]) }}">
                                                <div class="item eye">
                                                    <i class="icon-eye"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="list-icon-function">
                                            <a href="{{ asset('uploads/rentals/files/' . $reservation->rental->qualification) }}" download>
                                                <div class="item download">
                                                    <i class="icon-download"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
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
    
    $(document).on('change', '.payment_status', function() {
        // Retrieve the reservation ID from the data attribute
        var reservationId = $(this).data('reservation-id');  // Get the reservation ID from the data attribute
        var paymentStatus = $(this).val();  // Get the new payment status value
        
        // Log reservation ID and payment status for debugging
        console.log("Reservation ID: " + reservationId);
        console.log("New Payment Status: " + paymentStatus);

        // Show the confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the payment status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with AJAX request to update payment status
                var token = '{{ csrf_token() }}'; // CSRF token for security

                // Log AJAX data to ensure it's correct
                console.log("Sending AJAX request with data:", {
                    id: reservationId,
                    payment_status: paymentStatus,
                    _token: token
                });

                $.ajax({
                    url: '{{ route("admin.updatePaymentStatus") }}',
                    method: 'POST',
                    data: {
                        id: reservationId,  // The ID of the reservation
                        payment_status: paymentStatus,  // New payment status
                        _token: token  // CSRF token
                    },
                    success: function(response) {
                        // Log the response for debugging
                        console.log("AJAX Success Response: ", response);
                        
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log error details for debugging
                        console.log("AJAX Error: ", error);
                        console.log("XHR Response: ", xhr);
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                });
            } else {
                // If canceled, revert the change in the dropdown
                console.log("Change canceled. Reverting the dropdown value.");
                $(this).val($(this).data('previous'));
            }
        });

        // Store the current value to revert if the user cancels
        $(this).data('previous', $(this).val());
    });


    $(function() {
        // Tooltip functionality for available rooms
        $(document).on('mouseenter', '.room-number-link', function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('room-id');
            $(tooltipId).addClass('show');
        });

        $(document).on('mouseleave', '.room-number-link', function() {
            var tooltipId = '#room-tooltip-' + $(this).parent().data('room-id');
            $(tooltipId).removeClass('show');
        });
    });






    $(document).ready(function () {
    // Include CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Listen for change events on the status dropdown
    $('.rent-status').on('change', function () {
        let reservationId = $(this).data('id'); // Get reservation ID
        let newStatus = $(this).val(); // Get selected value
        let dropdown = $(this); // Save reference to the dropdown
        let previousValue = dropdown.data('previous-value'); // Get the previously stored value

        // Show SweetAlert confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the reservation status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, send AJAX request to update the status
                $.ajax({
                    url: "/admin/update-reservation-status", // Replace with the actual route
                    type: "POST",
                    data: {
                        reservation_id: reservationId,
                        rent_status: newStatus
                    },
                    success: function (response) {
                        // Show a success alert or notification
                        Swal.fire('Success!', response.message || 'Reservation status updated successfully!', 'success');

                        // Update the dropdown's previous value
                        dropdown.data('previous-value', newStatus);

                        // Disable the dropdown if the status is locked or matches specific conditions
                        if (['completed', 'canceled'].includes(newStatus)) {
                            dropdown.prop('disabled', true);
                        }
                    },
                    error: function (xhr) {
                        console.log('AJAX Error:', xhr); // Debugging log
                        let errorMessage = xhr.responseJSON?.message || 'Failed to update reservation status. Please try again.';
                        Swal.fire('Error!', errorMessage, 'error');

                        // Revert dropdown to previous value
                        dropdown.val(previousValue);
                    }
                });
            } else {
                // If cancelled, revert the selection to the previous value
                dropdown.val(previousValue);
            }
        });
    });

    // Store the previous value to revert on cancel
    $('.rent-status').on('focus', function () {
        $(this).data('previous-value', $(this).val()); // Store the current value when focus
    });
});






// document.querySelectorAll('.form-select').forEach(select => {
//     select.addEventListener('change', function () {
//         const reservationType = document.getElementById('filter-reservation-type').value;
//         const rentStatus = document.getElementById('filter-rent-status').value;
//         const paymentStatus = document.getElementById('filter-payment-status').value;

//         fetch(`/admin/filter-reservations?reservation_type=${reservationType}&rent_status=${rentStatus}&payment_status=${paymentStatus}`)
//             .then(response => response.json())
//             .then(data => {
//                 const tableBody = document.querySelector('.wg-table table tbody'); // Target the existing tbody
//                 tableBody.innerHTML = ''; // Clear the existing rows

//                 if (data.length > 0) {
//                     data.forEach(reservation => {
//                         tableBody.innerHTML += `
                        
                            
//                             <tr>
//                                 <td class="text-center">
//                                     <div>{{ $reservation->user->name }}</div>
//                                     <div style="font-size: 0.9rem; color: #808080;">  <!-- This is a greyish color -->
//                                         <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $reservation->user->email }}" target="_blank" style="text-decoration: none; color: inherit;">
//                                             {{ $reservation->user->email }}
//                                         </a>
//                                     </div>
//                                 </td>
                                
//                                 <td class="text-center">{{ $reservation->rental->name }}</td>
                                

//                                 <td>
//                                     <select 
//                                         name="rent_status" 
//                                         class="form-select rent-status" 
//                                         required 
//                                         data-id="{{ $reservation->id }}" 
//                                         @if(in_array($reservation->rent_status, ['completed', 'canceled']) || $reservation->status_locked) disabled @endif
//                                     >
//                                         <option value="pending" {{ $reservation->rent_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                         <option value="reserved" {{ $reservation->rent_status == 'reserved' ? 'selected' : '' }}>Reserved</option>
//                                         <option value="completed" {{ $reservation->rent_status == 'completed' ? 'selected' : '' }}>Completed</option>
//                                         <option value="canceled" {{ $reservation->rent_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                     </select>
//                                 </td>
//                                 <meta name="csrf-token" content="{{ csrf_token() }}">


//                                 <td>
//                                     <select name="payment_status" class="form-select payment_status" required data id="payment-status" data-reservation-id="{{ $reservation->id }}">
//                                         @if($reservation->type == 'International Convention Center' || 'Rolle Hall' || 'Swimming Pool')  <!-- Check if reservation type is dormitories -->
//                                             <!-- Show only these options for dormitories -->
//                                             <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                             <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                             <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
//                                         @else
//                                             <!-- Show all other options for other reservation types -->
//                                             <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                             <option value="advance/deposit complete" {{ $reservation->payment_status == 'advance/deposit complete' ? 'selected' : '' }}>Advance/Deposit Complete</option>
//                                             <option value="1st month complete" {{ $reservation->payment_status == '1st month complete' ? 'selected' : '' }}>1st Month Complete</option>
//                                             <option value="2nd month complete" {{ $reservation->payment_status == '2nd month complete' ? 'selected' : '' }}>2nd Month Complete</option>
//                                             <option value="3rd month complete" {{ $reservation->payment_status == '3rd month complete' ? 'selected' : '' }}>3rd Month Complete</option>
//                                             <option value="4th month complete" {{ $reservation->payment_status == '4th month complete' ? 'selected' : '' }}>4th Month Complete</option>
//                                             <option value="5th month complete" {{ $reservation->payment_status == '5th month complete' ? 'selected' : '' }}>5th Month Complete</option>
//                                             <option value="6th month complete" {{ $reservation->payment_status == '6th month complete' ? 'selected' : '' }}>6th Month Complete</option>
//                                             <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
//                                             <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                         @endif
//                                     </select>
//                                 </td>

                                


                          
//                                 <td class="text-center">
//                                     @if($reservation->rental->name === 'International House II')
//                                         @if($reservation->dormitoryRoom)
//                                             {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date)->format('Y-m-d') }}
//                                             to
//                                             {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date)->format('Y-m-d') }}
//                                         @else
//                                             N/A
//                                         @endif
//                                     @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
//                                         {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->start_date)->format('Y-m-d') }}
//                                         to
//                                         {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->end_date)->format('Y-m-d') }}
//                                     @else
//                                         {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('Y-m-d') : 'N/A' }}
//                                     @endif
//                                 </td>
                                
                                
//                                 <td class="text-center">
//                                     @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
//                                         @php
//                                             // Fetch available rooms for the rental ID
//                                             $availableRoomsForRental = $availableRooms->get($reservation->rental->id) ?? collect();
//                                         @endphp
                                
//                                         @if($availableRoomsForRental->count() > 0)
//                                             <ul style="list-style: none; padding: 0; margin: 0;">
//                                                 @foreach($availableRoomsForRental as $room)
//                                                     <li class="room-number-container" data-room-id="{{ $room->id }}">
//                                                         <a href="#" class="room-number-link">
//                                                             Room {{ $room->room_number }}
//                                                         </a>
//                                                         <div class="tooltip" id="room-tooltip-{{ $room->id }}">
//                                                             <strong>Room Capacity:</strong> {{ $room->room_capacity }}<br>
//                                                             <strong>Currently Reserved:</strong> {{ $room->reservations_count ?? 0 }}
//                                                         </div>
//                                                     </li>
//                                                 @endforeach
//                                             </ul>
//                                         @else
//                                             <span>No available rooms</span>
//                                         @endif
//                                     @else
//                                         <span>N/A</span>
//                                     @endif
//                                 </td>
                                
                                
                                
//                                 <td>
//                                     <div class="list-icon-function-wrapper">
//                                         <div class="list-icon-function">
//                                             <a href="{{ route('admin.reservation-events', ['reservation_id' => $reservation->id]) }}">
//                                                 <div class="item edit">
//                                                     <i class="icon-edit-3"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                         <div class="list-icon-function">
//                                             <a href="{{ route('admin.reservation-history', ['reservation_id' => $reservation->id]) }}">
//                                                 <div class="item eye">
//                                                     <i class="icon-eye"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                         <div class="list-icon-function">
//                                             <a href="{{ asset('uploads/rentals/files/' . $reservation->rental->qualification) }}" download>
//                                                 <div class="item download">
//                                                     <i class="icon-download"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                     </div>
//                                 </td>
//                             </tr>
                           
                        
//                         `;
//                     });
//                 } else {
//                     tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No reservations found.</td></tr>';
//                 }
//             })
//             .catch(error => console.error('Error fetching data:', error));
//     });
// });



// document.querySelectorAll('.form-select').forEach(select => {
//     select.addEventListener('change', function () {
//         // Get filter values
//         const reservationType = document.getElementById('filter-reservation-type').value;
//         const rentStatus = document.getElementById('filter-rent-status').value;
//         const paymentStatus = document.getElementById('filter-payment-status').value;

//         // Fetch filtered data
//         fetch(`/admin/filter-reservations?reservation_type=${encodeURIComponent(reservationType)}&rent_status=${encodeURIComponent(rentStatus)}&payment_status=${encodeURIComponent(paymentStatus)}`)
//             .then(response => response.json())
//             .then(data => {
//                 const tableBody = document.querySelector('.wg-table table tbody');
//                 tableBody.innerHTML = ''; // Clear the table

//                 if (data.length > 0) {
//                     // Populate rows dynamically
//                     data.forEach(reservation => {
//                         const row = `
//                             <tr>
//                                 <td class="text-center">
//                                     <div>{{ $reservation->user->name }}</div>
//                                     <div style="font-size: 0.9rem; color: #808080;">  <!-- This is a greyish color -->
//                                         <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $reservation->user->email }}" target="_blank" style="text-decoration: none; color: inherit;">
//                                             {{ $reservation->user->email }}
//                                         </a>
//                                     </div>
//                                 </td>
                                
//                                 <td class="text-center">{{ $reservation->rental->name }}</td>
                                

//                                 <td>
//                                     <select 
//                                         name="rent_status" 
//                                         class="form-select rent-status" 
//                                         required 
//                                         data-id="{{ $reservation->id }}" 
//                                         @if(in_array($reservation->rent_status, ['completed', 'canceled']) || $reservation->status_locked) disabled @endif
//                                     >
//                                         <option value="pending" {{ $reservation->rent_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                         <option value="reserved" {{ $reservation->rent_status == 'reserved' ? 'selected' : '' }}>Reserved</option>
//                                         <option value="completed" {{ $reservation->rent_status == 'completed' ? 'selected' : '' }}>Completed</option>
//                                         <option value="canceled" {{ $reservation->rent_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                     </select>
//                                 </td>
//                                 <meta name="csrf-token" content="{{ csrf_token() }}">


//                                 <td>
//                                     <select name="payment_status" class="form-select payment_status" required data id="payment-status" data-reservation-id="{{ $reservation->id }}">
//                                         @if($reservation->type == 'International Convention Center' || 'Rolle Hall' || 'Swimming Pool')  <!-- Check if reservation type is dormitories -->
//                                             <!-- Show only these options for dormitories -->
//                                             <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                             <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                             <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
//                                         @else
//                                             <!-- Show all other options for other reservation types -->
//                                             <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
//                                             <option value="advance/deposit complete" {{ $reservation->payment_status == 'advance/deposit complete' ? 'selected' : '' }}>Advance/Deposit Complete</option>
//                                             <option value="1st month complete" {{ $reservation->payment_status == '1st month complete' ? 'selected' : '' }}>1st Month Complete</option>
//                                             <option value="2nd month complete" {{ $reservation->payment_status == '2nd month complete' ? 'selected' : '' }}>2nd Month Complete</option>
//                                             <option value="3rd month complete" {{ $reservation->payment_status == '3rd month complete' ? 'selected' : '' }}>3rd Month Complete</option>
//                                             <option value="4th month complete" {{ $reservation->payment_status == '4th month complete' ? 'selected' : '' }}>4th Month Complete</option>
//                                             <option value="5th month complete" {{ $reservation->payment_status == '5th month complete' ? 'selected' : '' }}>5th Month Complete</option>
//                                             <option value="6th month complete" {{ $reservation->payment_status == '6th month complete' ? 'selected' : '' }}>6th Month Complete</option>
//                                             <option value="full payment complete" {{ $reservation->payment_status == 'full payment complete' ? 'selected' : '' }}>Full Payment Complete</option>
//                                             <option value="canceled" {{ $reservation->payment_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
//                                         @endif
//                                     </select>
//                                 </td>

                                


                          
//                                 <td class="text-center">
//                                     @if($reservation->rental->name === 'International House II')
//                                         @if($reservation->dormitoryRoom)
//                                             {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_start_date)->format('Y-m-d') }}
//                                             to
//                                             {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->ih_end_date)->format('Y-m-d') }}
//                                         @else
//                                             N/A
//                                         @endif
//                                     @elseif(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory']) && $reservation->dormitoryRoom)
//                                         {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->start_date)->format('Y-m-d') }}
//                                         to
//                                         {{ \Carbon\Carbon::parse($reservation->dormitoryRoom->end_date)->format('Y-m-d') }}
//                                     @else
//                                         {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('Y-m-d') : 'N/A' }}
//                                     @endif
//                                 </td>
                                
                                
//                                 <td class="text-center">
//                                     @if(in_array($reservation->rental->name, ['Male Dormitory', 'Female Dormitory', 'International House II']))
//                                         @php
//                                             // Fetch available rooms for the rental ID
//                                             $availableRoomsForRental = $availableRooms->get($reservation->rental->id) ?? collect();
//                                         @endphp
                                
//                                         @if($availableRoomsForRental->count() > 0)
//                                             <ul style="list-style: none; padding: 0; margin: 0;">
//                                                 @foreach($availableRoomsForRental as $room)
//                                                     <li class="room-number-container" data-room-id="{{ $room->id }}">
//                                                         <a href="#" class="room-number-link">
//                                                             Room {{ $room->room_number }}
//                                                         </a>
//                                                         <div class="tooltip" id="room-tooltip-{{ $room->id }}">
//                                                             <strong>Room Capacity:</strong> {{ $room->room_capacity }}<br>
//                                                             <strong>Currently Reserved:</strong> {{ $room->reservations_count ?? 0 }}
//                                                         </div>
//                                                     </li>
//                                                 @endforeach
//                                             </ul>
//                                         @else
//                                             <span>No available rooms</span>
//                                         @endif
//                                     @else
//                                         <span>N/A</span>
//                                     @endif
//                                 </td>
                                
                                
                                
//                                 <td>
//                                     <div class="list-icon-function-wrapper">
//                                         <div class="list-icon-function">
//                                             <a href="{{ route('admin.reservation-events', ['reservation_id' => $reservation->id]) }}">
//                                                 <div class="item edit">
//                                                     <i class="icon-edit-3"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                         <div class="list-icon-function">
//                                             <a href="{{ route('admin.reservation-history', ['reservation_id' => $reservation->id]) }}">
//                                                 <div class="item eye">
//                                                     <i class="icon-eye"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                         <div class="list-icon-function">
//                                             <a href="{{ asset('uploads/rentals/files/' . $reservation->rental->qualification) }}" download>
//                                                 <div class="item download">
//                                                     <i class="icon-download"></i>
//                                                 </div>
//                                             </a>
//                                         </div>
//                                     </div>
//                                 </td>
//                             </tr>
//                         `;
//                         tableBody.innerHTML += row;
//                     });
//                 } else {
//                     tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No reservations found.</td></tr>';
//                 }
//             })
//             .catch(error => console.error('Error fetching data:', error));
//     });
// });

</script>

@endpush

