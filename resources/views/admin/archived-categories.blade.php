@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Archived Categories</h3>
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
                        <a href="{{ route('admin.categories') }}">
                            <div class="text-tiny">Categories</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Archived Categories</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." class="" name="name"
                                    tabindex="2" value="" aria-required="true" required="">
                            </fieldset>
                        </form>
                    </div>
                </div>
                <div class="table-all-user table-responsive">
                    <div class="">
                        @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead class="thead-ligth">
                                <tr>
                                    <th scope="col"style="width: 70%;">Name</th>
                                    <th scope="col"style="width: 30%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($archivedCategories as $category)
                                    <!-- Parent Category -->
                                    <tr data-toggle="collapse" data-target=".children-of-{{ $category->id }}"
                                        class="clickable-row">
                                        <td class="pname" style="width: 100%">
                                            <div class="image">
                                                <img src="{{ asset('uploads/categories') }}/{{ $category->image }}"
                                                    alt="{{ $category->name }}" class="image">
                                            </div>
                                            <div class="name">
                                                <strong>{{ $category->name }}</strong>
                                                @if ($category->archivedChildren && $category->archivedChildren->count() > 0)
                                                    <span class="badge badge-primary ml-2">Parent</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <form
                                                    action="{{ route('admin.category.restore', ['id' => $category->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-lg btn-success restore">
                                                        <i class="icon-rotate-ccw"></i> Restore
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Child Categories (archived with parent) -->
                                    @if ($category->archivedChildren && $category->archivedChildren->count() > 0)
                                        @foreach ($category->archivedChildren as $childCategory)
                                            <tr class="collapse children-of-{{ $category->id }} bg-light text-muted">
                                                <td>{{ $childCategory->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center pl-4">
                                                        <img src="{{ asset('uploads/categories/' . $childCategory->image) }}"
                                                            alt="{{ $childCategory->name }}" class="img-fluid rounded mr-2"
                                                            style="max-height: 30px;">
                                                        <div>
                                                            <strong>{{ $childCategory->name }}</strong>
                                                            <span class="badge badge-warning ml-2">Subcategory</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <form
                                                        action="{{ route('admin.category.restore', $childCategory->id) }}"
                                                        method="POST">
                                                        @csrf @method('PUT')
                                                        <button class="btn btn-lg btn-success restore">
                                                            <i class="icon-rotate-ccw"></i> Restore
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                @if (isset($orphanedChildren) && $orphanedChildren->count() > 0)
                                    <tr class="bg-info-light">
                                        <td colspan="2" class="text-center py-2">
                                            <strong>Detached Archived Subcategories</strong>
                                        </td>
                                    </tr>
                                    @foreach ($orphanedChildren as $orphan)
                                        <tr class="bg-light">
                                            <td>
                                                <div class="d-flex align-items-center pl-4">
                                                    <img src="{{ asset('uploads/categories/' . $orphan->image) }}"
                                                        alt="{{ $orphan->name }}" class="img-fluid rounded mr-2"
                                                        style="max-height: 30px;">
                                                    <div>
                                                        <strong>{{ $orphan->name }}</strong>
                                                        <span class="badge badge-info ml-2">Detached Subcategory</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.category.restore', $orphan->id) }}"
                                                    method="POST">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-lg btn-success restore">
                                                        <i class="icon-rotate-ccw"></i> Restore
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $archivedCategories->links('pagination::bootstrap-5') }}
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
            line-height: 1;
            vertical-align: middle;
        }

        .badge-warning {
            background-color: #ffc107;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .bg-info-light {
            background-color: #d1ecf1;
        }
    </style>
@endpush
@push('scripts')
    <script>
        $(function() {
            $('.restore').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to restore this record?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, restore it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
