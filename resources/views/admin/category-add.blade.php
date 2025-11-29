    @extends('layouts.admin')
    @section('content')
        <div class="main-content-inner">
            <!-- main-content-wrap -->
            <div class="main-content-wrap">
                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                    <h3>Add Category</h3>
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
                            <div class="text-tiny">New Category</div>
                        </li>
                    </ul>
                </div>
                <x-category-form :action="route('admin.category.store')" method="POST" :parentCategories="$parentCategories" buttonText="Create Category" />
            </div>
        </div>
    @endsection
