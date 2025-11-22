@if ($categories->count() > 0)
    @if (Session::has('status'))
        <p class="alert alert-success">{{ Session::get('status') }}</p>
    @endif
    <table class="table table-striped table-bordered category-table">
        <thead class="thead-ligth">
            <tr>
                <th scope="col" style="width: 80%;">Category</th>
                <th scope="col" style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <!-- Parent Category -->
                <tr class="parent-row" data-category-id="{{ $category->id }}">
                    <td class="pname">
                        <div class="category-content">
                            <div class="category-toggle">
                                @if ($category->children && $category->children->count() > 0)
                                    <i class="icon-chevron-right toggle-icon"></i>
                                @else
                                    <span class="toggle-placeholder"></span>
                                @endif
                            </div>
                            <div class="image">
                                <img src="{{ asset('uploads/categories') }}/{{ $category->image }}"
                                    alt="{{ $category->name }}" class="image">
                            </div>
                            <div class="name">
                                <div class="category-name-wrapper">
                                    <strong>{{ $category->name }}</strong>
                                    @if ($category->children && $category->children->count() > 0)
                                        <span class="badge badge-primary">
                                            {{ $category->children->count() }}
                                            {{ $category->children->count() > 1 ? 'subcategories' : 'subcategory' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="list-icon-function">
                            <a href="{{ route('admin.category.edit', ['id' => $category->id]) }}" class="action-link"
                                title="Edit">
                                <div class="item edit">
                                    <i class="icon-edit-3"></i>
                                </div>
                            </a>
                            <form action="{{ route('admin.category.archive', ['id' => $category->id]) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="item text-danger delete" title="Archive">
                                    <i class="icon-archive"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Child Categories -->
                @if ($category->children && $category->children->count() > 0)
                    @foreach ($category->children as $childCategory)
                        <tr class="child-row children-of-{{ $category->id }}" style="display: none;">
                            <td>
                                <div class="category-content child-content">
                                    <div class="child-indicator">
                                        <div class="child-line"></div>
                                        <i class="icon-corner-down-right"></i>
                                    </div>
                                    <div class="image">
                                        <img src="{{ asset('uploads/categories/' . $childCategory->image) }}"
                                            alt="{{ $childCategory->name }}" class="image">
                                    </div>
                                    <div class="name">
                                        <div class="category-name-wrapper">
                                            <strong>{{ $childCategory->name }}</strong>
                                            <span class="badge badge-secondary">Subcategory</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="list-icon-function">
                                    <a href="{{ route('admin.category.edit', ['id' => $childCategory->id]) }}"
                                        class="action-link" title="Edit">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>
                                    <form action="{{ route('admin.category.archive', ['id' => $childCategory->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="item text-danger delete" title="Archive">
                                            <i class="icon-archive"></i>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
@else
    <table class="table table-striped table-bordered category-table">
        <thead class="thead-ligth">
            <tr>
                <th scope="col" style="width: 80%;">Category</th>
                <th scope="col" style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr id="no-results-message">
                <td colspan="2" class="text-center p-5">
                    <div class="alert alert-info mb-0">
                        <i class="icon-info-circle" style="font-size: 24px;"></i>
                        <p class="mb-0 mt-2"><strong>No categories found.</strong></p>
                        <small class="text-muted">Try a different search term or add a new category.</small>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
@endif
