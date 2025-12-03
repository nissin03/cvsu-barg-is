@extends('layouts.admin')
@section('content')
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .variant-button {
            margin-top: 10px;
        }

        .fields-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .fields-container .field {
            flex: 1;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .file-name-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            font-size: 12px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .item {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .upload-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }

        .upload-image {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }

        .uploadfile {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .remove-upload {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(20, 19, 20, 0.2);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Scrollable container for variants */
        .variant-scroll-container {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 10px;
        }

        .variant-scroll-container::-webkit-scrollbar {
            width: 8px;
        }

        .variant-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .variant-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .variant-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .variant-fields {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fff;
        }

        .variant-fields:last-child {
            margin-bottom: 0;
        }

        .variant-scroll-container {
            max-height: 400px;
        }

        @media (min-width: 768px) {
            .variant-scroll-container {
                max-height: 500px;
            }
        }

        @media (min-width: 1024px) {
            .variant-scroll-container {
                max-height: 600px;
            }
        }
    </style>
    <!-- main-content-wrap -->
    <div class="main-content-inner">
        <!-- main-content-wrap -->
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Edit Product</h3>
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
                        <a href="{{ route('admin.products') }}">
                            <div class="text-tiny">Products</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Edit Product</div>
                    </li>
                </ul>
            </div>

            <!-- form-add-product -->
            <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data"
                action="{{ route('admin.product.update') }}">
                <input type="hidden" name="id" value="{{ $product->id }}" />
                @csrf
                @method('PUT')
                <!-- Product Basic Information -->
                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Product name <span class="tf-color-1">*</span>
                        </div>
                        <input class="mb-10" type="text" placeholder="Enter product name" name="name" tabindex="0"
                            value="{{ $product->name }}" aria-required="true" required="">
                        <div class="text-tiny">Do not exceed 100 characters when entering the
                            product name.</div>
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <!-- Category Dropdown -->
                    <div class="gap22 cols">
                        <fieldset class="category">
                            <div class="body-title mb-10">Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="category_id" required>
                                    <option value="">Choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @if ($category->children)
                                            @foreach ($category->children as $child)
                                                <option value="{{ $child->id }}"
                                                    {{ $product->category_id == $child->id ? 'selected' : '' }}>
                                                    -- {{ $child->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>


                    @error('category_id')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    {{-- <div class="gap22 cols">
                        <fieldset class="sex">
                            <div class="body-title mb-10">Gender Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="sex" required>
                                    <option value="">Choose gender category</option>
                                    <option value="male"
                                        {{ old('sex', $product->sex ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female"
                                        {{ old('sex', $product->sex ?? '') === 'female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="all"
                                        {{ old('sex', $product->sex ?? '') === 'all' ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                        </fieldset>
                    </div>

                    @error('sex')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror --}}

                    <fieldset class="shortdescription">
                        <div class="body-title mb-10">Short Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10 ht-150" name="short_description" placeholder="Short Description" tabindex="0"
                            aria-required="true" required="">{{ $product->short_description }}</textarea>
                        <div class="text-tiny">Do not exceed 100 characters when entering the
                            product name.</div>
                    </fieldset>
                    @error('short_description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror


                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span>
                        </div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true"
                            required="">{{ $product->description }}</textarea>
                        <div class="text-tiny">Do not exceed 100 characters when entering the
                            product name.</div>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                </div>

                <!-- Product Images and Attributes -->
                <div class="wg-box">
                    <fieldset>
                        <div class="body-title">Upload images <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            @if ($product->image)
                                <div class="item" id="imgpreview">
                                    <img src="{{ asset('uploads/products') }}/{{ $product->image }}" id="preview-img"
                                        class="effect8" alt="{{ $product->name }}">
                                    <button type="button" class="remove-upload" onclick="removeMainImage()">Remove</button>
                                </div>
                            @else
                                <div class="item" id="imgpreview" style="display:none">
                                    <img src="" id="preview-img" class="effect8" alt="">
                                    <button type="button" class="remove-upload" onclick="removeMainImage()">Remove</button>
                                </div>
                            @endif
                            <div id="upload-file" class="item up-load">
                                <label class="uploadfile" for="myFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Drop your images here or select <span class="tf-color">click
                                            to
                                            browse</span></span>
                                    <input type="file" id="myFile" name="image" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('image')
                        <span class="alert alert-danger text-center d-block mt-2">{{ $message }}</span>
                    @enderror

                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images (Max 5 images)</div>
                        <div class="upload-image mb-16" id="gallery-container">
                            @if (!empty($product->images))
                                @php
                                    $existingImages = explode(',', $product->images);
                                    $existingImages = array_filter($existingImages, function ($img) {
                                        return file_exists(public_path('uploads/products/' . trim($img)));
                                    });
                                @endphp
                                @foreach ($existingImages as $img)
                                    <div class="item gitems" data-image="{{ trim($img) }}">
                                        <img src="{{ asset('uploads/products/' . trim($img)) }}" class="effect8"
                                            alt="Gallery Image" style="width: 100px; height: 100px; object-fit: cover;" />
                                        <button type="button" class="remove-upload"
                                            onclick="removeGalleryImage(this, '{{ trim($img) }}')">Remove</button>
                                    </div>
                                @endforeach
                            @endif
                            <div id="galUpload" class="item up-load">
                                <label class="uploadfile" for="gFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="text-tiny">Drop your images here or select <span class="tf-color">click
                                            to browse</span></span>
                                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('images')
                        <span class="alert alert-danger text-center d-block mt-2">{{ $message }}</span>
                    @enderror
                    @error('images.*')
                        <span class="alert alert-danger text-center d-block mt-2">{{ $message }}</span>
                    @enderror
                    <div id="gallery-error" class="alert alert-danger text-center mt-2" style="display:none;"></div>



                    <div class="main-fields" id="main-fields" style="{{ $hasVariant ? 'display: none;' : '' }}">
                        <div class="cols gap22">
                            <fieldset class="name">
                                <div class="body-title mb-10">Price <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter price" name="price"
                                    tabindex="0" value="{{ $product->price }}" aria-required="true">
                            </fieldset>
                            @error('price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror


                            <fieldset class="name">
                                <div class="body-title mb-10">Quantity <span class="tf-color-1">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter quantity" name="quantity"
                                    tabindex="0" value="{{ $product->quantity }}" aria-required="true">
                            </fieldset>
                            @error('quantity')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                        </div>


                    </div>


                    <!-- New Stock Status Fields -->
                    <fieldset class="stock-status">
                        <div class="body-title mb-10">Stock Status Quantities <span class="tf-color-1">*</span></div>
                        <div class="fields-container">
                            <div class="field">
                                <label>Reorder Quantity</label>
                                <input type="number" name="reorder_quantity" value="{{ $product->reorder_quantity }}"
                                    required>
                            </div>
                            {{-- <div class="field">
                                <label>Out of Stock Quantity</label>
                                <input type="number" name="outofstock_quantity"
                                    value="{{ $product->outofstock_quantity }}" required>
                            </div> --}}
                        </div>
                    </fieldset>
                    {{-- @error('instock_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror --}}
                    @error('reorder_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror
                    {{-- @error('outofstock_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror --}}


                    <fieldset class="name">
                        <div class="body-title mb-10">Featured</div>
                        <div class="select mb-10">
                            <select class="" name="featured">
                                <option value="0" {{ $product->featured == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $product->featured == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('featured')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Edit Product</button>
                    </div>
                </div>

                <div class="wg-box">
                    <div class="gap22 cols">
                        <fieldset class="product-attribute">
                            <div class="body-title mb-10">Product Variant <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" id="product_attribute_select">
                                    <option value="">Select Attribute</option>
                                    @foreach ($productAttributes as $attribute)
                                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    @error('product_attribute_id')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                    <div class="cols gap22">
                        <button type="button" id="add-variant-btn" class="btn btn-primary">Add Variant</button>
                    </div>
                </div>

                <div class="wg-box" id="container-box"
                    style="{{ $hasVariant && $product->attributeValues->count() > 0 ? 'display: block;' : 'display: none;' }}">
                    <div id="variant-fields-container" class="variant-scroll-container">
                        @foreach ($product->attributeValues as $variant)
                            <div class="variant-fields" data-variant-index="{{ $loop->index }}"
                                data-existing-variant-id="{{ $variant->id }}">
                                <div class="variant-header flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Variant {{ $loop->iteration }}</h3>
                                    <button type="button" class="archive-variant-btn btn btn-danger btn-sm"
                                        data-variant-index="{{ $loop->iteration }}">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </div>
                                <div>
                                    <input type="hidden" name="product_attribute_id[]"
                                        value="{{ $variant->product_attribute_id }}">
                                    <input type="hidden" name="existing_variant_ids[]" value="{{ $variant->id }}">
                                    <fieldset class="name">
                                        <div class="body-title mb-10 my-4">Variant Name:</div>
                                        <input type="text" name="variant_name[]" class="form-control"
                                            value="{{ $variant->value }}" placeholder="Variant Name" required>
                                    </fieldset>
                                    <fieldset class="name">
                                        <div class="body-title mb-10 my-4">Variant Description (Optional):</div>
                                        <textarea class="mb-10" style="font-size: 14px;" name="variant_description[]"
                                            placeholder="Enter variant description (optional)" rows="3">{{ $variant->description }}</textarea>
                                    </fieldset>
                                    <fieldset class="name">
                                        <div class="body-title mb-10 my-4">Variant Price <span class="tf-color-1">*</span>
                                        </div>
                                        <input class="mb-10" type="text" placeholder="Enter price"
                                            name="variant_price[]" value="{{ $variant->price }}"
                                            placeholder="Variant Price" tabindex="0" aria-required="true" required>
                                    </fieldset>
                                    <fieldset class="name">
                                        <div class="body-title mb-10 my-4">Variant Quantity <span
                                                class="tf-color-1">*</span></div>
                                        <input type="number" name="variant_quantity[]" class="form-control"
                                            value="{{ $variant->quantity }}" placeholder="Variant Quantity" required>
                                    </fieldset>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Edit Product</button>
                    </div> --}}
                </div>

                <!-- Submit Button -->
        </div>
        </form>
        <!-- /form-add-product -->
    </div>
    <!-- /main-content-wrap -->
    </div>
    <!-- /main-content-wrap -->
@endsection

@push('scripts')
    <script>
        $(function() {
            $('form').on('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    return false;
                }
            });

            $("#myFile").on("change", function(e) {
                e.preventDefault();
                e.stopPropagation();
                const photofile = this.files[0];
                if (photofile) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        $("#preview-img").attr('src', event.target.result);
                        $("#imgpreview").show();
                    };
                    reader.readAsDataURL(photofile);
                }
            });

            $("#gFile").on("change", function(e) {
                e.preventDefault();
                e.stopPropagation();

                const maxImages = 5;
                const existingImages = $('.gitems').length;
                const newFiles = Array.from(this.files);
                const totalImages = existingImages + newFiles.length;

                if (totalImages > maxImages) {
                    alert(
                        `You can only upload a maximum of ${maxImages} images. You already have ${existingImages} images.`
                    );
                    this.value = '';
                    return;
                }

                newFiles.forEach((file, index) => {
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} exceeds 5MB limit!`);
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const newImageHtml = `
                    <div class="item gitems gitems-new">
                        <img src="${event.target.result}" class="effect8" alt="Gallery Image"
                            style="width: 100px; height: 100px; object-fit: cover;" />
                        <p class="file-name-overlay">${file.name}</p>
                        <button type="button" class="remove-upload" onclick="removeNewGalleryImage(this)">Remove</button>
                    </div>
                `;
                        $('#galUpload').before(newImageHtml);
                    };
                    reader.readAsDataURL(file);
                });

                setTimeout(() => {
                    if ($('.gitems').length >= maxImages) {
                        $('#galUpload').hide();
                    }
                }, 100);
            });

            $("input[name='name']").on("input", function() {
                $("input[name='slug']").val(StringToSlug($(this).val()));
            });
        });

        function removeMainImage() {
            $('#imgpreview').hide();
            $('#preview-img').attr('src', '');
            $('#myFile').val('');
        }

        function removeGalleryImage(button, existingImage) {
            const galleryItem = $(button).closest('.gitems');

            $('<input>').attr({
                type: 'hidden',
                name: 'removed_images[]',
                value: existingImage
            }).appendTo('form');

            galleryItem.remove();

            if ($('.gitems').length < 5) {
                $('#galUpload').show();
            }
        }

        function removeNewGalleryImage(button) {
            const galleryItem = $(button).closest('.gitems-new');
            const fileName = galleryItem.find('.file-name-overlay').text();

            const fileInput = document.getElementById('gFile');
            const dt = new DataTransfer();
            const files = fileInput.files;

            for (let i = 0; i < files.length; i++) {
                if (files[i].name !== fileName) {
                    dt.items.add(files[i]);
                }
            }
            fileInput.files = dt.files;

            galleryItem.remove();

            if ($('.gitems').length < 5) {
                $('#galUpload').show();
            }
        }

        function StringToSlug(Text) {
            return Text.toLowerCase()
                .replace(/[^\w ]+/g, "")
                .replace(/ +/g, "-");
        }

        $(document).ready(function() {
            const addVariantBtn = document.getElementById('add-variant-btn');
            const boxFields = document.getElementById('container-box');
            const mainFields = document.getElementById('main-fields');
            const variantsContainer = document.getElementById('variant-fields-container');

            function createVariantFields() {
                const selectedAttributeId = $("#product_attribute_select").val();
                const selectedAttributeName = $("#product_attribute_select option:selected").text();

                if (!selectedAttributeId) {
                    alert("Please select a product attribute before adding a variant.");
                    return;
                }

                const variantCount = variantsContainer.children.length + 1;

                const variantDiv = document.createElement('div');
                variantDiv.className = 'variant-fields';
                variantDiv.setAttribute('data-variant-index', variantCount);
                variantDiv.innerHTML = `
            <div class="variant-header flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Variant ${variantCount}</h3>
                <button type="button" class="remove-variant-btn btn btn-danger btn-sm"
                        data-variant-index="${variantCount}">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
            <div>
                <input type="hidden" name="product_attribute_id[]" value="${selectedAttributeId}">
                <fieldset class="name">
                    <div class="body-title mb-10 my-4">Variant for: ${selectedAttributeName}</div>
                    <input type="text" name="variant_name[]" placeholder="Variant Name" required>
                </fieldset>
                <fieldset class="name">
                    <div class="body-title mb-10 my-4">Variant Description (Optional)</div>
                    <textarea class="mb-10" style="font-size: 14px;" name="variant_description[]" placeholder="Enter variant description (optional)" rows="3"></textarea>
                </fieldset>
                <fieldset class="name">
                    <div class="body-title mb-10 my-4">Variant Price <span class="tf-color-1">*</span></div>
                    <input class="mb-10" type="text" placeholder="Enter price" name="variant_price[]"
                        tabindex="0" aria-required="true" required>
                </fieldset>
                <fieldset class="name">
                    <div class="body-title mb-10 my-4">Variant Quantity <span class="tf-color-1">*</span></div>
                    <input type="number" name="variant_quantity[]" placeholder="Variant Quantity" required>
                </fieldset>
            </div>
        `;

                // Insert at the beginning (top) of the container
                variantsContainer.insertBefore(variantDiv, variantsContainer.firstChild);

                const removeBtn = variantDiv.querySelector('.remove-variant-btn');
                removeBtn.addEventListener('click', () => {
                    variantsContainer.removeChild(variantDiv);
                    updateMainFieldsVisibility();
                    showContainer();
                    updateVariantNumbers();
                });

                updateMainFieldsVisibility();
                showContainer();
                updateVariantNumbers();
            }

            function updateVariantNumbers() {
                const variants = variantsContainer.querySelectorAll('.variant-fields');
                variants.forEach((variant, index) => {
                    const variantHeader = variant.querySelector('.variant-header h3');
                    if (variantHeader) {
                        variantHeader.textContent = `Variant ${index + 1}`;
                    }
                    variant.setAttribute('data-variant-index', index + 1);
                    const removeBtn = variant.querySelector('.remove-variant-btn');
                    if (removeBtn) {
                        removeBtn.setAttribute('data-variant-index', index + 1);
                    }
                });
            }

            function showContainer() {
                if (variantsContainer.children.length > 0) {
                    boxFields.style.display = 'block';
                } else {
                    boxFields.style.display = 'none';
                }
            }

            function updateMainFieldsVisibility() {
                if (variantsContainer.children.length > 0) {
                    mainFields.style.display = 'none';
                } else {
                    mainFields.style.display = 'block';
                }
            }

            // Handle existing variant removal/archive
            $(document).on('click', '.remove-variant-btn, .archive-variant-btn', function() {
                const variantDiv = $(this).closest('.variant-fields');
                const existingVariantId = variantDiv.data('existing-variant-id');

                if (existingVariantId) {
                    // This is an existing variant - archive it
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'archived_variant_ids[]',
                        value: existingVariantId
                    }).appendTo('form');
                }

                variantDiv.remove();
                updateMainFieldsVisibility();
                showContainer();
                updateVariantNumbers();
            });

            addVariantBtn.addEventListener('click', createVariantFields);
        });
    </script>
@endpush
