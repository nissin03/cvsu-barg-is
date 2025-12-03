@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Archived Product Attributes</h3>
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
                        <a href="{{ route('admin.product-attributes') }}">
                            <div class="text-tiny">Product Attributes</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Archived Product Attributes</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    {{-- <div class="wg-filter flex-grow">
                        <form class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." class="" name="name"
                                    tabindex="2" value="" aria-required="true" required="">
                            </fieldset>
                        </form>
                    </div> --}}
                </div>
                <div class="table-all-user table-responsive">
                    <div class="">
                        @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" style="width: 50%;">Name</th>
                                    <th scope="col" style="width: 40%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($archivedAttributes as $attribute)
                                    <tr>
                                        <td>
                                            <div class="name">
                                                <strong>{{ $attribute->name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <form
                                                    action="{{ route('admin.product-attribute.restore', ['id' => $attribute->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-lg btn-success restore">
                                                        <i class="icon-rotate-ccw"></i> Restore
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted">No archived product attributes found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $archivedAttributes->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .list-icon-function {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .alert-success {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .text-muted {
            color: #6c757d;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const restoreButtons = document.querySelectorAll('.restore');

            restoreButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to restore this record?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, restore it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
