@extends('layouts.admin')
@section('content')

    <main class="container">
        <div class="main-content-inner">
            <div class="container mt-6">

                <div class="container my-4">
                    <div class="row row-cols-1 row-cols-md-3 g-4">

                        <!-- Total Payment Amount -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2 d-flex align-items-center justify-center">
                                        <i class="icon-shopping-bag me-2"></i>
                                      Total Reservation
                                    </div>
                                    <h4>{{$dashboardDatas[0]->Total}}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Reserve Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2">Total Amount of Reserve Items</div>
                                    <h4>₱  {{ number_format($dashboardDatas[0]->TotalReservedAmount, 2, '.', ',') }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Claimed Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2 d-flex align-items-center justify-center">
                                        <i class="icon-shopping-bag me-2"></i>
                                        Total Claimed Orders
                                    </div>
                                    <h4>{{$dashboardDatas[0]->TotalPickedUp}}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Claimed Items -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2">Total Amount of Claimed Items</div>
                                    <h4>₱ {{ number_format($dashboardDatas[0]->TotalPickedUpAmount, 2, '.', ',') }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Canceled Orders -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2 d-flex align-items-center justify-center">
                                        <i class="icon-shopping-bag me-2"></i>
                                        Total Canceled Orders
                                    </div>
                                    <h4>{{$dashboardDatas[0]->TotalCanceled}}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Amount of Cancelled Orders -->
                        <div class="col">
                            <div class="card p-4 text-center shadow-sm border-0 ">
                                <div>
                                    <div class="body-text mb-2">Total Amount of Cancelled Orders</div>
                                    <h4>₱ {{ number_format($dashboardDatas[0]->TotalCanceledAmount, 2, '.', ',') }}</h4>
                                </div>
                            </div>
                        </div>
                </div>
                </div>
            </div>

             <div class="main-content-wrap">
                <div class="tf-section-2 mb-30">
                    <div class="flex gap20 flex-wrap-mobile">

                        <div class="wg-box p-4 bg-light shadow-sm rounded-lg w-100" style="max-width: 100%;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="dropdown">
                                    <!-- Button for Earnings Revenue Dropdown -->
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center w-100" type="button"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left;">
                                        <h5 class="mb-0 me-2">Monthly Report</h5>
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <!-- Dropdown menu with same width as the button -->
                                    {{-- <ul class="dropdown-menu dropdown-menu-end w-100 shadow-sm">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center p-3" href="{{ route('admin.index-weekly') }}">
                                                <i class="fas fa-calendar-week me-2"></i> Weekly Earnings Revenue
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center p-3" href="{{ route('admin.index-daily') }}">
                                                <i class="fas fa-calendar-day me-2"></i> Daily Earnings Revenue
                                            </a>
                                        </li>
                                    </ul> --}}
                                </div>

                                <form action="{{ route('admin.index') }}" method="GET" class="d-flex flex-grow-1 align-items-center">
                                    <select name="year" class="form-select me-2 w-100">
                                        @foreach($yearRange as $year)
                                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-primary w-100">Confirm</button>
                                </form>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t1"></div>
                                            <div class="text-tiny">Total</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4>₱{{ number_format($TotalAmount, 2) }}</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t2"></div>
                                            <div class="text-tiny">Reservation Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4>₱{{ number_format($TotalReservedAmount, 2) }}</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t2"></div>
                                            <div class="text-tiny">Received Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4>₱{{ number_format($TotalPickedUpAmount, 2) }}</h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="block-legend">
                                            <div class="dot t2"></div>
                                            <div class="text-tiny">Cancelled Orders Amount</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h4>₱{{ number_format($TotalCanceledAmount, 2) }}</h4>
                                    </div>
                                </div>

                            </div>

                            <div id="line-chart-8"></div>


                        </div>

                    </div>


                    <div class="container p-4" style="background-color: #f8f9fc; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex justify-content-between align-items-center mb-3">

                        </div>
                        <div style="overflow-x: auto;">

                            <table class="table table-bordered table-hover" style="table-layout: auto;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">Product ID</th>
                                        <th class="text-center">Name</th>
                                        {{-- <th class="text-center">Category</th>
                                        <th class="text-center">Price</th> --}}
                                        <th class="text-center">Stock Status</th>
                                        <th class="text-center">Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        @php
                                            $currentStock = $product->attributeValues->isNotEmpty()
                                                ? $product->attributeValues->sum('quantity')
                                                : $product->current_stock;
                                        @endphp

                                        @if($currentStock == 0)
                                            <tr>
                                                <td class="text-center">{{ $product->id }}</td>
                                                <td class="text-center">{{ $product->name }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                </td>
                                                <td class="text-center">{{ $currentStock }}</td>
                                            </tr>
                                        @elseif($currentStock <= $product->reorder_quantity)
                                            <tr>
                                                <td class="text-center">{{ $product->id }}</td>
                                                <td class="text-center">{{ $product->name }}</td>
                                                <td class="text-center">
                                                    @if($currentStock <= $product->outofstock_quantity)
                                                        <span class="badge bg-danger">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-warning">Reorder Level</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $currentStock }}</td>
                                            </tr>
                                        @endif

                                    @endforeach
                                </tbody>

                            </table>

                        </div>
                    </div>


            </div>
        </div>


<div class="tf-section mb-30">
    <div class="wg-box shadow">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <h5 class="m-0">Recent Orders</h5>
            <a class="btn btn-secondary" href="{{route('admin.orders')}}">
                <span>View all</span>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Course</th>
                        <th>Reservation</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Pickup Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr>
                        <td>
                            <div>
                                <div>{{$order->name}}</div>
                                <div class="text-muted" style="font-size: 15px;">{{$order->phone_number}}</div>
                            </div>
                        </td>
                        <td>{{$order->course}}</td>
                        <td>
                            <div>
                                <div>{{$order->reservation_date}}</div>
                                <div class="text-muted"  style="font-size: 15px;">{{$order->time_slot}}</div>
                            </div>
                        </td>
                        <td>{{$order->total}}</td>
                        <td>
                            <span class="badge 
                                @if ($order->status == 'pickedup') bg-success 
                                @elseif($order->status == 'canceled') bg-danger 
                                @else bg-warning @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="text-center">{{$order->orderItems->count()}}</td>
                        <td>{{$order->picked_up_date}}</td>
                        <td>
                            <a href="{{route('admin.order.details', ['order_id' => $order->id]) }}">
                                <i class="icon-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

        </div>

    </main>

@endsection

@push('styles')
<style>

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 20px;
        text-align: left;
    }

    .table td {
        white-space: nowrap;
    }

    .row-container {
        display: flex;
        flex-direction: row;
        gap: 20px; /* Adjust gap as needed */
        flex-wrap: wrap; /* Allows wrapping if items don't fit in one row */
    }

    .wg-chart-default {
        flex: 1; /* Distributes available space equally */
        min-width: 200px; /* Ensures a minimum width for each item */
    }

    .container {
        max-width: 100%;
    }

    .table {
        font-size: 1rem;
        table-layout: auto;
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

    .badge {
        font-size: 0.9rem;
    }

    .text-muted {
        font-size: 0.9rem;
        text-decoration: none;
    }

    .text-muted:hover {
        text-decoration: underline;
    }
</style>

@endpush

@push('scripts')
<script>
        (function($) {

            var tfLineChart = (function() {

                var chartBar = function() {

                    var options = {
                        series: [{
                                name: 'Total',
                                data: [{{$AmountM}}]
                            }, {
                                name: 'Reserved',
                                data: [{{$ReservationAmountM}}]
                            },
                            {
                                name: 'Pickedup',
                                data: [{{$PickedUpAmountM}}]
                            }, {
                                name: 'Canceled',
                                data: [{{$CanceledAmountM}}]
                            }
                        ],
                        chart: {
                            type: 'bar',
                            height: 325,
                            toolbar: {
                                show: false,
                            },
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '10px',
                                endingShape: 'rounded'
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },


                        xaxis: {
                            labels: {
                                style: {
                                    colors: '#212529',
                                },
                            },
                            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                                'Oct', 'Nov', 'Dec'
                            ],
                        },
                        yaxis: {
                            show: false,
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return "$ " + val + ""
                                }
                            }
                        }
                    };

                    chart = new ApexCharts(
                        document.querySelector("#line-chart-8"),
                        options
                    );
                    if ($("#line-chart-8").length > 0) {
                        chart.render();
                    }
                };

                return {
                    init: function() {},

                    load: function() {
                        chartBar();
                    },
                    resize: function() {},
                };
            })();

            jQuery(document).ready(function() {});

            jQuery(window).on("load", function() {
                tfLineChart.load();
            });

            jQuery(window).on("resize", function() {});
        })(jQuery);
    </script>
@endpush

@push('styles')
<style>

</style>
@endpush
