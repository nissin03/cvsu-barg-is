@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Product Variant</h3>
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
                        <div class="text-tiny">Product Attributes</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="d-flex align-items-end justify-content-end gap10 flex-wrap">
                    <a class="tf-button w-auto" href="{{ route('admin.product-attribute-add') }}"><i
                            class="icon-plus"></i>Add new</a>

                    <a class="tf-button w-auto" href="{{ route('admin.archived-attributes') }}">
                        <i class="icon-archive"></i> Archived Attributes
                    </a>
                </div>
                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    {{-- <th style="width: 3%">#</th> --}}
                                    <th scope="col" style="width: 30%;">Product Variant</th>
                                    <th scope="col" style="width: 40%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attributes as $attribute)
                                    <tr>
                                        {{-- <td>{{ $attribute->id }}</td> --}}
                                        <td>
                                            <div class="name">
                                                <a href="#" class="body-title-2">{{ $attribute->name }}</a>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <a
                                                    href="{{ route('admin.product.attribute.edit', ['id' => $attribute->id]) }}">
                                                    <div class="item edit">
                                                        <i class="icon-edit-3"></i>
                                                    </div>
                                                </a>
                                                <form
                                                    action="{{ route('admin.product-attribute.archive', ['id' => $attribute->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="item text-danger archive">
                                                        <i class="icon-archive"></i>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $attributes->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const archiveButtons = document.querySelectorAll('.archive');

            archiveButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to archive this record?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, archive it!',
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
