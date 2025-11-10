@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Discounts</h3>
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
                        <div class="text-tiny">Discounts</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form method="GET" action="{{ route('discounts.index') }}" class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." name="name"
                                    value="{{ request('name') }}">
                            </fieldset>
                        </form>
                    </div>

                    <a class="tf-button w-auto" href="{{ route('discounts.create') }}">
                        <i class="icon-plus"></i> Add new
                    </a>
                    <a class="tf-button w-auto" href="{{ route('discounts.archived') }}">
                        <i class="icon-archive"></i> Archived Discounts
                    </a>
                </div>

                <div class="table-all-user g-table">
                    <div class="table-responsive">
                        @if (session('status'))
                            <p class="alert alert-success">{{ session('status') }}</p>
                        @endif

                        <table class="table table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 10%;">Percent</th>
                                    {{-- <th style="width: 15%;">Applies To</th> --}}
                                    <th style="width: 12%;">Requires Proof</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($discounts as $discount)
                                    <tr>
                                        <td>{{ $discount->id }}</td>
                                        <td><strong>{{ $discount->name }}</strong></td>
                                        <td>{{ rtrim(rtrim(number_format($discount->percent, 2, '.', ''), '0'), '.') }}%
                                        </td>
                                        {{-- <td>
                                            @if ($discount->applies_to === 'venue_only')
                                                <span class="badge badge-primary">Venue Only</span>
                                            @else
                                                <span class="badge badge-info">All Charges</span>
                                            @endif
                                        </td> --}}
                                        <td>
                                            @if ($discount->requires_proof)
                                                <span class="badge badge-warning">Yes</span>
                                            @else
                                                <span class="badge badge-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($discount->active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <a href="{{ route('discounts.edit', $discount->id) }}">
                                                    <div class="item edit"><i class="icon-edit-3"></i></div>
                                                </a>
                                                @if ($discount->active)
                                                    <form action="{{ route('discounts.archive', $discount->id) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="item text-danger delete"
                                                            style="background: none; border: none; padding: 0; cursor: pointer;">
                                                            <i class="icon-archive"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No discounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $discounts->links('pagination::bootstrap-5') }}
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

        .badge-primary {
            background-color: #007bff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            $('.delete').closest('form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to archive this discount?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit();
                    }
                });
            });
        });
    </script>
@endpush
