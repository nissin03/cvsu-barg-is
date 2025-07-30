@extends('layouts.app')
@section('content')

<link href="{{ asset('css/shop/shop.css') }}" rel="stylesheet">

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        if ($currentRoute === 'shop.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
        } elseif ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
            $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
        } elseif ($currentRoute === 'about.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h4 class="section-title">Filters</h4>
                    
                    <div class="accordion" id="categories-list">
                        <div class="accordion-item">
                            <h5 class="accordion-header" id="accordion-heading-1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#accordion-filter-1" aria-expanded="true" 
                                        aria-controls="accordion-filter-1">
                                    Product Categories
                                </button>
                            </h5>
                            <div id="accordion-filter-1" class="accordion-collapse collapse show"
                                 aria-labelledby="accordion-heading-1" data-bs-parent="#categories-list">
                                <div class="accordion-body">
                                    <ul class="filter-list">
                                        @foreach ($categories as $category)
                                            <li class="filter-item">
                                                <div class="modern-checkbox">
                                                    <input class="parent-category" type="checkbox" 
                                                           name="categories" value="{{ $category->id }}"
                                                           id="cat-{{ $category->id }}"
                                                           @if (in_array($category->id, explode(',', $f_categories))) checked @endif
                                                           aria-label="Select {{ $category->name }}">
                                                    <label for="cat-{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                                <span class="product-count">{{ $category->total_products }}</span>
                                            </li>
                                            @if ($category->children->isNotEmpty())
                                                @foreach ($category->children as $subcategory)
                                                    <li class="filter-item subcategory">
                                                        <div class="modern-checkbox">
                                                            <input class="child-category" type="checkbox" 
                                                                   name="categories" value="{{ $subcategory->id }}"
                                                                   id="subcat-{{ $subcategory->id }}"
                                                                   @if (in_array($subcategory->id, explode(',', $f_categories))) checked @endif
                                                                   aria-label="Select {{ $subcategory->name }}">
                                                            <label for="subcat-{{ $subcategory->id }}">
                                                                {{ $subcategory->name }}
                                                            </label>
                                                        </div>
                                                        <span class="product-count">{{ $subcategory->products->count() }}</span>
                                                    </li>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="accordion" id="gender-filter">
                        <div class="accordion-item">
                            <h5 class="accordion-header" id="accordion-gender-heading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#accordion-gender-filter" aria-expanded="true"
                                        aria-controls="accordion-gender-filter">
                                    Product Type
                                </button>
                            </h5>
                            <div id="accordion-gender-filter" class="accordion-collapse collapse show"
                                 aria-labelledby="accordion-gender-heading" data-bs-parent="#gender-filter">
                                <div class="accordion-body">
                                    <ul class="filter-list">
                                        <li class="filter-item">
                                            <div class="modern-checkbox">
                                                <input type="radio" name="sex" id="sex-all" value=""
                                                       {{ request('sex') == '' ? 'checked' : '' }}
                                                       aria-label="Select all genders">
                                                <label for="sex-all">All</label>
                                            </div>
                                        </li>
                                        <li class="filter-item">
                                            <div class="modern-checkbox">
                                                <input type="radio" name="sex" id="sex-male" value="male"
                                                       {{ request('sex') == 'male' ? 'checked' : '' }}
                                                       aria-label="Select male">
                                                <label for="sex-male">Men</label>
                                            </div>
                                        </li>
                                        <li class="filter-item">
                                            <div class="modern-checkbox">
                                                <input type="radio" name="sex" id="sex-female" value="female"
                                                       {{ request('sex') == 'female' ? 'checked' : '' }}
                                                       aria-label="Select female">
                                                <label for="sex-female">Women</label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>

            <div class="col-lg-9">
                <button class="mobile-filter-toggle" id="mobileFilterToggle">
                    <i class="fas fa-filter"></i>
                    Filters
                </button>
                
               <div class="sort-container">
                    <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">
                        Filter by:
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="price-range-filter">
                            <label for="priceRange" class="me-2 text-muted" style="font-size: 0.9rem;">Price:</label>
                            <select class="modern-select" name="priceRange" id="priceRange">
                                <option value="">All Prices</option>
                                <option value="0-50" {{ request('priceRange') == '0-50' ? 'selected' : '' }}>₱0 - ₱50</option>
                                <option value="50-100" {{ request('priceRange') == '50-100' ? 'selected' : '' }}>₱50 - ₱100</option>
                                <option value="100-200" {{ request('priceRange') == '100-200' ? 'selected' : '' }}>₱100 - ₱200</option>
                                <option value="200-500" {{ request('priceRange') == '200-500' ? 'selected' : '' }}>₱200 - ₱500</option>
                                <option value="500+" {{ request('priceRange') == '500+' ? 'selected' : '' }}>₱500+</option>
                            </select>
                        </div>

                        <div class="sort-by-filter">
                            <label for="orderBy" class="me-2 text-muted" style="font-size: 0.9rem;">Sort by:</label>
                            <select class="modern-select" name="orderBy" id="orderBy">
                                <option value="-1" {{ $order == -1 ? 'selected' : '' }}>Default</option>
                                <option value="5" {{ $order == 5 ? 'selected' : '' }}>Best Sellers</option>
                                <option value="1" {{ $order == 1 ? 'selected' : '' }}>Newest First</option>
                                <option value="2" {{ $order == 2 ? 'selected' : '' }}>Oldest First</option>
                                <option value="3" {{ $order == 3 ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="4" {{ $order == 4 ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="products-grid">
                    <div id="product-list">
                        @include('partials.products-list', ['products' => $products])
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pagination-container">
            {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </main>

    <div class="filter-overlay" id="filterOverlay"></div>
    
    <div class="mobile-filter-sidebar" id="mobileFilterSidebar">
        <button class="filter-close" id="filterClose">&times;</button>
        <h4 class="section-title">Filters</h4>
        
        <div class="accordion" id="mobile-categories-list">
            <div class="accordion-item">
                <h5 class="accordion-header" id="mobile-accordion-heading-1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#mobile-accordion-filter-1" aria-expanded="true" 
                            aria-controls="mobile-accordion-filter-1">
                        Product Categories
                    </button>
                </h5>
                <div id="mobile-accordion-filter-1" class="accordion-collapse collapse show"
                     aria-labelledby="mobile-accordion-heading-1" data-bs-parent="#mobile-categories-list">
                    <div class="accordion-body">
                        <ul class="filter-list">
                            @foreach ($categories as $category)
                                <li class="filter-item">
                                    <div class="modern-checkbox">
                                        <input class="parent-category" type="checkbox" 
                                               name="categories" value="{{ $category->id }}"
                                               id="mobile-cat-{{ $category->id }}"
                                               @if (in_array($category->id, explode(',', $f_categories))) checked @endif
                                               aria-label="Select {{ $category->name }}">
                                        <label for="mobile-cat-{{ $category->id }}">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                    <span class="product-count">{{ $category->total_products }}</span>
                                </li>
                                @if ($category->children->isNotEmpty())
                                    @foreach ($category->children as $subcategory)
                                        <li class="filter-item subcategory">
                                            <div class="modern-checkbox">
                                                <input class="child-category" type="checkbox" 
                                                       name="categories" value="{{ $subcategory->id }}"
                                                       id="mobile-subcat-{{ $subcategory->id }}"
                                                       @if (in_array($subcategory->id, explode(',', $f_categories))) checked @endif
                                                       aria-label="Select {{ $subcategory->name }}">
                                                <label for="mobile-subcat-{{ $subcategory->id }}">
                                                    {{ $subcategory->name }}
                                                </label>
                                            </div>
                                            <span class="product-count">{{ $subcategory->products->count() }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="accordion" id="mobile-gender-filter">
            <div class="accordion-item">
                <h5 class="accordion-header" id="mobile-accordion-gender-heading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#mobile-accordion-gender-filter" aria-expanded="true"
                            aria-controls="mobile-accordion-gender-filter">
                        Product Type
                    </button>
                </h5>
                <div id="mobile-accordion-gender-filter" class="accordion-collapse collapse show"
                     aria-labelledby="mobile-accordion-gender-heading" data-bs-parent="#mobile-gender-filter">
                    <div class="accordion-body">
                        <ul class="filter-list">
                            <li class="filter-item">
                                <div class="modern-checkbox">
                                    <input type="radio" name="sex" id="mobile-sex-all" value=""
                                           {{ request('sex') == '' ? 'checked' : '' }}
                                           aria-label="Select all genders">
                                    <label for="mobile-sex-all">All</label>
                                </div>
                            </li>
                            <li class="filter-item">
                                <div class="modern-checkbox">
                                    <input type="radio" name="sex" id="mobile-sex-male" value="male"
                                           {{ request('sex') == 'male' ? 'checked' : '' }}
                                           aria-label="Select male">
                                    <label for="mobile-sex-male">Men</label>
                                </div>
                            </li>
                            <li class="filter-item">
                                <div class="modern-checkbox">
                                    <input type="radio" name="sex" id="mobile-sex-female" value="female"
                                           {{ request('sex') == 'female' ? 'checked' : '' }}
                                           aria-label="Select female">
                                    <label for="mobile-sex-female">Women</label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <form action="{{ route('shop.index') }}" method="get" id="frmfilter">
        <input type="hidden" name="page" value="{{ $products->currentPage() }}">
        <input type="hidden" name="order" id="order" value="{{ $order }}" />
        <input type="hidden" name="categories" id="hdnCategories" />
        <input type="hidden" name="sex" id="hdnSex" value="{{ request('sex') }}" />
        
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var swiper = new Swiper('.js-swiper-slider', {
                slidesPerView: 1,
                effect: 'fade',
                loop: true,
                pagination: {
                    el: '.slideshow-pagination',
                    type: 'bullets',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
            
            const mobileFilterToggle = document.getElementById('mobileFilterToggle');
            const mobileFilterSidebar = document.getElementById('mobileFilterSidebar');
            const filterOverlay = document.getElementById('filterOverlay');
            const filterClose = document.getElementById('filterClose');

            function openMobileFilter() {
                mobileFilterSidebar.classList.add('active');
                filterOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileFilter() {
                mobileFilterSidebar.classList.remove('active');
                filterOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }

            mobileFilterToggle.addEventListener('click', openMobileFilter);
            filterClose.addEventListener('click', closeMobileFilter);
            filterOverlay.addEventListener('click', closeMobileFilter);

            @if (session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            @if (session('error'))
                toastr.error('{{ session('error') }}');
            @endif
        });

        $(function() {
            function submitFilterForm() {
                var categories = [];
                $("input[name='categories']:checked").each(function() {
                    categories.push($(this).val());
                });
                $("#hdnCategories").val(categories.join(","));
                $.ajax({
                    url: $('#frmfilter').attr('action'),
                    type: 'GET',
                    data: $('#frmfilter').serialize(),
                    success: function(data) {
                        $('#product-list').html(data);
                    },
                    error: function(xhr) {
                        console.error("Error: " + xhr.status + " " + xhr.statusText);
                        toastr.error('Error loading products.');
                    }
                });
            }

            $("input[name='categories']").on("change", function() {
                var categoryId = $(this).val();
                var isParent = $(this).closest('li').find('ul').length > 0;
                if (isParent) {
                    if ($(this).is(':checked')) {
                        $(this).closest('li').find("input[name='categories']").prop('checked', true);
                    } else {
                        $(this).closest('li').find("input[name='categories']").prop('checked', false);
                    }
                } else {
                    var parentLi = $(this).closest('ul').closest('li');
                    if (parentLi.length) {
                        var allChildrenChecked = parentLi.find("input[name='categories']").not(':first').length === 
                                               parentLi.find("input[name='categories']").not(':first').filter(':checked').length;
                        parentLi.find("input[name='categories']").first().prop('checked', allChildrenChecked);
                    }
                }
                submitFilterForm();
            });

            $("#orderBy").on("change", function() {
                $('#order').val($(this).val());
                submitFilterForm();
            });

            $("input[name='sex']").on("change", function() {
                $("#hdnSex").val($(this).val());
                submitFilterForm();
            });
        });
    </script>
@endpush