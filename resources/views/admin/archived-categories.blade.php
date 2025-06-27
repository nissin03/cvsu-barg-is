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
                <div class="table-all-user">
                    <div class="">
                        @if (Session::has('status'))
                            <p class="alert alert-success">{{ Session::get('status') }}</p>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead class="thead-ligth">
                                <tr>
                                    <th scope="col"style="width: 5%;">#</th>
                                    <th scope="col"style="width: 20%;">Name</th>
                                    <th scope="col"style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($archivedCategories as $category)
                                    <!-- Parent Category -->
                                    <tr data-toggle="collapse" data-target=".children-of-{{ $category->id }}" class="clickable-row">
                                        <td style="width: 7%;">{{ $category->id }}</td>
                                        <td class="pname" style="width: 100%">
                                            <div class="image">
                                                <img src="{{ asset('uploads/categories') }}/{{ $category->image }}"
                                                    alt="{{ $category->name }}" class="image">
                                            </div>
                                                <div class="name">
                                                      <strong>{{ $category->name }}</strong>
                                                      @if ($category->children && $category->children->count() > 0)
                                                      <span class="badge badge-primary ml-2">Parent</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="width: 20%">
                                            <div class="list-icon-function">
                                                <form action="{{ route('admin.category.restore', ['id' => $category->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-lg btn-success restore">
                                                      <i class="icon-rotate-ccw"></i> Restore
                                                  </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Child Categories -->
                                    @if ($category->children && $category->children->count() > 0)
                                        @foreach ($category->children as $childCategory)
                                        <tr class="collapse children-of-{{ $category->id }} bg-light text-muted">
                                          <td>{{ $childCategory->id }}</td>
                                          <td>
                                              <div class="d-flex align-items-center pl-4">
                                                  <img src="{{ asset('uploads/categories/' . $childCategory->image) }}" alt="{{ $childCategory->name }}" class="img-fluid rounded mr-2" style="max-height: 30px;">
                                                  <div>
                                                      <strong>{{ $childCategory->name }}</strong>
                                                      <span class="badge badge-warning ml-2">Child</span>
                                                  </div>
                                              </div>
                                          </td>
                                          <td>
                                              <form action="{{ route('admin.category.restore', $childCategory->id) }}" method="POST">
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
      </style>
@endpush
@push('scripts')
    <script>
        $(function() {
            $('.restore').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    text: "You want to archive this record?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dcd435',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, restore it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
