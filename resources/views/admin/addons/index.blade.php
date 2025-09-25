@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Addons</h3>
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
                        <div class="text-tiny">Addons</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form method="GET" action="{{ route('admin.addons') }}" class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." name="name"
                                    value="{{ request('name') }}">
                            </fieldset>
                        </form>
                    </div>

                    <a class="tf-button w-auto" href="{{ route('admin.addons.create') }}">
                        <i class="icon-plus"></i> Add new
                    </a>
                    {{-- <a class="tf-button w-auto" href="{{ route('addons.archived') }}">
                        <i class="icon-archive"></i> Archived Addons
                    </a> --}}
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (Session::has('success'))
                            <p class="alert alert-success">{{ Session::get('success') }}</p>
                        @endif

                        <table class="table table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 15%;">Price Type</th>
                                    <th style="width: 10%;">Base Price</th>
                                    <th style="width: 10%;">Capacity</th>
                                    <th style="width: 10%;">Available</th>
                                    <th style="width: 10%;">Refundable</th>
                                    <th style="width: 10%;">Show</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($addons as $addon)
                                    <tr>
                                        <td>{{ $addon->id }}</td>
                                        <td><strong>{{ $addon->name }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $addon->price_type)) }}</td>
                                        <td>{{ number_format($addon->base_price, 2) }}</td>
                                        <td>{{ $addon->capacity ?? '-' }}</td>
                                        <td>
                                            @if ($addon->is_available)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-danger">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($addon->is_refundable)
                                                <span class="badge badge-info">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-dark">{{ ucfirst($addon->show) }}</span>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <a href="{{ route('admin.addons.edit', $addon->id) }}">
                                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                                </a>
                                                {{-- <form action="{{ route('addons.archive', $addon->id) }}" method="POST"> --}}
                                                <form action="#" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="item text-danger delete">
                                                        <i class="icon-archive"></i>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No addons found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $addons->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            border-radius: 12px;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .badge-dark {
            background-color: #343a40;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to archive this addon?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
