@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/shop/shop.css') }}" rel="stylesheet">

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-4 my-md-5 shop-page">
        <div class="row g-3 g-md-4 align-items-start">
            <div class="col-12 col-lg-3 mb-4 mb-lg-0">
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
                                                    <input class="parent-category" type="checkbox" name="categories"
                                                        value="{{ $category->id }}" id="cat-{{ $category->id }}"
                                                        @if (in_array($category->id, explode(',', $f_categories))) checked @endif>
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
                                                            <input class="child-category" type="checkbox" name="categories"
                                                                value="{{ $subcategory->id }}"
                                                                id="subcat-{{ $subcategory->id }}"
                                                                @if (in_array($subcategory->id, explode(',', $f_categories))) checked @endif>
                                                            <label for="subcat-{{ $subcategory->id }}">
                                                                {{ $subcategory->name }}
                                                            </label>
                                                        </div>
                                                        <span
                                                            class="product-count">{{ $subcategory->products->count() }}</span>
                                                    </li>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9">
                <button class="mobile-filter-toggle" id="mobileFilterToggle">
                    <i class="fas fa-filter"></i>
                    Filters
                </button>

                <div
                    class="sort-container d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 gap-md-4">
                    <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">
                        Filter by:
                    </h5>
                    <div
                        class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2 gap-md-3 w-100 justify-content-md-end">
                        <div class="price-range-filter">
                            <label for="priceRange" class="me-2 text-muted sort-label">Price:</label>
                            <select class="modern-select w-100 w-sm-auto" name="priceRange" id="priceRange">
                                <option value="" {{ empty($priceRange) ? 'selected' : '' }}>All Prices</option>
                                <option value="0-50" {{ $priceRange == '0-50' ? 'selected' : '' }}>₱0 - ₱50</option>
                                <option value="50-100" {{ $priceRange == '50-100' ? 'selected' : '' }}>₱50 - ₱100</option>
                                <option value="100-200" {{ $priceRange == '100-200' ? 'selected' : '' }}>₱100 - ₱200
                                </option>
                                <option value="200-500" {{ $priceRange == '200-500' ? 'selected' : '' }}>₱200 - ₱500
                                </option>
                                <option value="500+" {{ $priceRange == '500+' ? 'selected' : '' }}>₱500+</option>
                            </select>
                        </div>

                        <div class="sort-by-filter">
                            <label for="orderBy" class="me-2 text-muted sort-label">Sort by:</label>
                            <select class="modern-select w-100 w-sm-auto" name="orderBy" id="orderBy">
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

        <div class="pagination-container" id="pagination-container">
            @include('partials._products-pagination', ['products' => $products])
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
                                        <input class="parent-category" type="checkbox" name="categories"
                                            value="{{ $category->id }}" id="mobile-cat-{{ $category->id }}"
                                            @if (in_array($category->id, explode(',', $f_categories))) checked @endif>
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
                                                <input class="child-category" type="checkbox" name="categories"
                                                    value="{{ $subcategory->id }}"
                                                    id="mobile-subcat-{{ $subcategory->id }}"
                                                    @if (in_array($subcategory->id, explode(',', $f_categories))) checked @endif>
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
    </div>

    <form action="{{ route('shop.index') }}" method="get" id="frmfilter">
        <input type="hidden" name="page" value="{{ $products->currentPage() }}">
        <input type="hidden" name="order" id="order" value="{{ $order }}" />
        <input type="hidden" name="categories" id="hdnCategories" />
        <input type="hidden" name="sex" id="hdnSex" value="{{ request('sex') }}" />
        <input type="hidden" name="priceRange" id="hdnPriceRange" value="{{ $priceRange ?? '' }}" />
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var isSubmitting = false;
            var currentPage = {{ $products->currentPage() }};
            var filterTimeout;

            function submitFilterForm() {
                if (isSubmitting) return;
                isSubmitting = true;
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    var categories = [];
                    $("input[name='categories']:checked").each(function() {
                        categories.push($(this).val());
                    });
                    $("#hdnCategories").val(categories.join(","));
                    var priceRange = $('#priceRange').val() || '';
                    $('#hdnPriceRange').val(priceRange);
                    $('input[name="page"]').val(1);
                    currentPage = 1;
                    loadFilteredProducts();
                }, 300);
            }

            function loadFilteredProducts() {
                $.ajax({
                    url: $('#frmfilter').attr('action'),
                    type: 'GET',
                    dataType: 'json',
                    data: $('#frmfilter').serialize(),
                    success: function(data) {
                        $('#product-list').html(data.products);
                        $('#pagination-container').html(data.pagination);
                        updatePagination();
                        isSubmitting = false;
                    },
                    error: function(xhr) {
                        console.error("Error: " + xhr.status + " " + xhr.statusText);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Error loading products.');
                        }
                        isSubmitting = false;
                    }
                });
            }

            function updatePagination() {
                $('.pagination .page-link').off('click').on('click', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var href = $this.attr('href');
                    var page = 1;
                    if (href) {
                        var urlParams = new URLSearchParams(href.split('?')[1]);
                        page = urlParams.get('page') || 1;
                    } else {
                        var linkText = $this.text().trim();
                        if (linkText === '‹' || linkText === '«') {
                            page = currentPage - 1;
                        } else if (linkText === '›' || linkText === '»') {
                            page = currentPage + 1;
                        } else {
                            page = parseInt(linkText) || currentPage;
                        }
                    }
                    if (page !== currentPage) {
                        $('input[name="page"]').val(page);
                        currentPage = parseInt(page);
                        loadFilteredProducts();
                    }
                });
            }

            $('#priceRange').on('change', function() {
                submitFilterForm();
            });

            $("input[name='categories']").on("change", function() {
                var $this = $(this);
                var isParent = $this.hasClass('parent-category');
                if (!isParent) {
                    var $subcategoryItem = $this.closest('li.subcategory');
                    if ($subcategoryItem.length) {
                        var $parentItem = $subcategoryItem.prevAll('li:not(.subcategory)').first();
                        if ($parentItem.length) {
                            var totalSubcategories = $parentItem.nextUntil('li:not(.subcategory)').length;
                            var checkedSubcategories = $parentItem.nextUntil('li:not(.subcategory)').find(
                                "input[name='categories']:checked").length;
                            $parentItem.find("input[name='categories']").prop('checked',
                                totalSubcategories === checkedSubcategories);
                        }
                    }
                }
                submitFilterForm();
            });

            $("#orderBy").on("change", function() {
                $('#order').val($(this).val());
                submitFilterForm();
            });

            updatePagination();

            var $mobileFilterSidebar = $('#mobileFilterSidebar');
            var $filterOverlay = $('#filterOverlay');

            function openMobileFilter() {
                $mobileFilterSidebar.addClass('active');
                $filterOverlay.addClass('active');
                $('body').css('overflow', 'hidden');
            }

            function closeMobileFilter() {
                $mobileFilterSidebar.removeClass('active');
                $filterOverlay.removeClass('active');
                $('body').css('overflow', 'auto');
            }

            $('#mobileFilterToggle').on('click', function(e) {
                e.preventDefault();
                openMobileFilter();
            });

            $('#filterClose, #filterOverlay').on('click', function() {
                closeMobileFilter();
            });

            $(window).on('resize', function() {
                if (window.innerWidth >= 576) {
                    closeMobileFilter();
                }
            });

            @if (session('success'))
                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ session('success') }}');
                }
            @endif
            @if (session('error'))
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ session('error') }}');
                }
            @endif
        });
    </script>
@endpush
