@extends('layouts.admin')
@section('content')
    <style>
        #product-form textarea,
        #product-form textarea.form-control {
            font-size: 14px;
            line-height: 20px;
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

        .stock-status-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stock-status-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stock-status-item label {
            width: 150px;
        }

        .stock-status-item input {
            flex: 1;
        }

        .tf-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Scrollable container for variants */
        .variant-scroll-container {
            max-height: 500px;
            /* Adjust this value based on your needs */
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 10px;
            /* Prevents content from hiding behind scrollbar */
        }

        /* Optional: Custom scrollbar styling */
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

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Add Product</h3>
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
                        <div class="text-tiny">Add Product</div>
                    </li>
                </ul>
            </div>

            <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data"
                action="{{ route('admin.product.store') }}" novalidate>
                @csrf

                <div class="wg-box">
                    <fieldset class="name">
                        <div class="body-title mb-10">Product Name <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Enter product name" name="name" tabindex="0"
                            value="{{ old('name') }}" aria-required="true" required="" id="product-name">
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <div class="gap22 cols">
                        <fieldset class="category">
                            <div class="body-title mb-10">Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="category_id" required id="category-select">
                                    <option value="">Choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                        @if ($category->children)
                                            @foreach ($category->children as $child)
                                                <option value="{{ $child->id }}"
                                                    {{ old('category_id') == $child->id ? 'selected' : '' }}>&nbsp; &nbsp;
                                                    &rarrhk;
                                                    {{ $child->name }}</option>
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
                            <div class="body-title mb-10">Sex Category <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" name="sex" required id="sex-select">
                                    <option value="">Choose Sex category</option>
                                    <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="all" {{ old('sex') === 'all' ? 'selected' : '' }}>All</option>
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
                            aria-required="true" required="" id="short-description">{{ old('short_description') }}</textarea>
                    </fieldset>
                    @error('short_description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <fieldset class="description">
                        <div class="body-title mb-10">Description <span class="tf-color-1">*</span></div>
                        <textarea class="mb-10" name="description" placeholder="Description" tabindex="0" aria-required="true" required=""
                            id="description">{{ old('description') }}</textarea>
                    </fieldset>
                    @error('description')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                </div>

                <div class="wg-box">
                    <fieldset>
                        <div class="body-title">Upload Main Image <span class="tf-color-1">*</span></div>
                        <div class="upload-image flex-grow">
                            <div class="item" id="imgpreview" style="{{ old('image') ? '' : 'display:none' }}">
                                <img src="../../../localhost_8000/images/upload/upload-1.png" id="preview-img"
                                    class="effect8" alt="">
                                <button type="button" class="remove-upload"
                                    onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
                            </div>
                            <div id="upload-file" class="item up-load">
                                <label class="uploadfile" for="myFile">
                                    <span class="icon">
                                        <i class="icon-upload-cloud"></i>
                                    </span>
                                    <span class="body-text">Select your images here or click to browse</span>
                                    <input type="file" id="myFile" name="image" accept="image/*" required>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    @error('image')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <fieldset>
                        <div class="body-title mb-10">Upload Gallery Images (Max 5 images)</div>
                        <div class="upload-image mb-16 flex-grow" id="gallery-container">
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
                    <div id="gallery-error" class="error-message"></div>
                    @error('images')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <!-- **Added Stock Status Fields Above Featured Field** -->
                    <div class="cols gap22">
                        <fieldset class="stock_status">
                            <div class="body-title mb-10">Stock Status <span class="tf-color-1">*</span></div>
                            <div class="stock-status-container">
                                <div class="stock-status-item">
                                    <label for="reorder_quantity">Reorder Quantity:</label>
                                    <input type="number" id="reorder_quantity" name="reorder_quantity" min="0"
                                        value="{{ old('reorder_quantity', 0) }}" required>
                                </div>
                                {{-- <div class="stock-status-item">
                                    <label for="outofstock_quantity">Out of Stock Quantity:</label>
                                    <input type="number" id="outofstock_quantity" name="outofstock_quantity"
                                        min="0" value="{{ old('outofstock_quantity', 0) }}" required>
                                </div> --}}
                            </div>
                        </fieldset>
                        {{-- @error('instock_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror --}}
                        @error('reorder_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                        {{-- @error('outofstock_quantity')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror --}}
                    </div>
                    <!-- **End of Stock Status Fields** -->

                    <div class="main-fields">
                        <div class="cols gap22">
                            <fieldset class="name">
                                <div class="body-title mb-10">Price <span class="tf-color-1 price-required">*</span></div>
                                <input class="mb-10" type="text" placeholder="Enter price" name="price"
                                    tabindex="0" value="{{ old('price') }}" aria-required="true" id="product-price">
                            </fieldset>
                            @error('price')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror

                            <fieldset class="name">
                                <div class="body-title mb-10">Quantity <span class="tf-color-1 quantity-required">*</span>
                                </div>
                                <input class="mb-10" type="text" placeholder="Enter quantity" name="quantity"
                                    tabindex="0" value="{{ old('quantity') }}" aria-required="true"
                                    id="product-quantity">
                            </fieldset>
                            @error('quantity')
                                <span class="alert alert-danger text-center">{{ $message }} </span>
                            @enderror
                        </div>
                    </div>

                    <!-- **Featured Field (Moved Below Stock Status Fields)** -->
                    <div class="cols gap22">
                        <fieldset class="name">
                            <div class="body-title mb-10">Featured</div>
                            <div class="select mb-10">
                                <select class="" name="featured">
                                    <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </fieldset>
                        @error('featured')
                            <span class="alert alert-danger text-center">{{ $message }} </span>
                        @enderror
                    </div>
                    <!-- **End of Featured Field** -->

                    <!-- Submit Button -->
                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit" id="submit-btn" disabled>Add Product</button>
                    </div>
                </div>

                <div class="wg-box">
                    <div class="gap22 cols">
                        <fieldset class="product-attribute">
                            <div class="body-title mb-10">Product Variant <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" id="product-select">
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

                <div class="wg-box" id="container-box" style="display: none">
                    <div id="variant-fields-container" class="variant-scroll-container"></div>
                </div>

                {{-- <div class="wg-box">
                    <div class="gap22 cols">
                        <fieldset class="product-attribute">
                            <div class="body-title mb-10">Product Variant <span class="tf-color-1">*</span></div>
                            <div class="select">
                                <select class="" id="product-select">
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

                <div class="wg-box" id="container-box" style="display: none">
                    <div id="variant-fields-container"></div>
                </div> --}}
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let hasVariants = false;

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML =
                        '<div class="loading-spinner me-2"></div>Processing...';
                    submitBtn.disabled = true;
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                }
            });
        });

        // Function to validate required fields and enable/disable submit button
        function validateForm() {
            const productName = document.getElementById('product-name').value.trim();
            const category = document.getElementById('category-select').value;
            // const sex = document.getElementById('sex-select').value;
            const mainImage = document.getElementById('myFile').files.length > 0;
            const reorderQuantity = document.getElementById('reorder_quantity').value;
            // const outofstockQuantity = document.getElementById('outofstock_quantity').value;

            // let isValid = productName && category && sex && mainImage && reorderQuantity && outofstockQuantity;
            let isValid = productName && category && mainImage && reorderQuantity;

            // If no variants, check price and quantity
            if (!hasVariants) {
                const price = document.getElementById('product-price').value.trim();
                const quantity = document.getElementById('product-quantity').value.trim();
                isValid = isValid && price && quantity;
            } else {
                // If has variants, check all variant fields
                const variants = document.querySelectorAll('.variant-fields');
                let allVariantsValid = variants.length > 0;

                variants.forEach(variant => {
                    const variantName = variant.querySelector('input[name="variant_name[]"]').value.trim();
                    const variantPrice = variant.querySelector('input[name="variant_price[]"]').value.trim();
                    const variantQuantity = variant.querySelector('input[name="variant_quantity[]"]').value.trim();

                    if (!variantName || !variantPrice || !variantQuantity) {
                        allVariantsValid = false;
                    }
                });

                isValid = isValid && allVariantsValid;
            }

            document.getElementById('submit-btn').disabled = !isValid;
        }

        // Function to update required indicators based on variant status
        function updateRequiredIndicators() {
            const priceRequired = document.querySelector('.price-required');
            const quantityRequired = document.querySelector('.quantity-required');

            if (hasVariants) {
                priceRequired.style.display = 'none';
                quantityRequired.style.display = 'none';
            } else {
                priceRequired.style.display = 'inline';
                quantityRequired.style.display = 'inline';
            }
        }

        $(function() {
            // Add event listeners to all form fields for validation
            // $('#product-name, #category-select, #sex-select, #reorder_quantity, #outofstock_quantity, #product-price, #product-quantity')
            //     .on('input change', validateForm);
            $('#product-name, #category-select, #reorder_quantity, #outofstock_quantity, #product-price, #product-quantity')
                .on('input change', validateForm);

            $("#myFile").on("change", function(e) {
                const [file] = this.files;
                if (file) {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                    $("#imgpreview .remove-upload").show();
                }
                validateForm();
            });

            $("#gFile").on("change", function(e) {
                const maxImages = 5;
                const existingImages = $('.gitems').length;
                const newFiles = this.files;
                const totalImages = existingImages + newFiles.length;

                if (totalImages > maxImages) {
                    alert(
                        `You can only upload a maximum of ${maxImages} images. You already have ${existingImages} images and trying to add ${newFiles.length} more.`
                    );
                    this.value = '';
                    return;
                }

                $("#galUpload").removeClass('up-load');
                let imgCount = 0;

                $.each(newFiles, function(key, val) {
                    if (val.size > 5 * 1024 * 1024) { // 5MB check
                        alert(`File ${val.name} exceeds 5MB limit!`);
                        return false;
                    }

                    imgCount++;
                    const fileName = val.name;
                    $('#galUpload').before(`
                        <div class="item gitems">
                            <img src="${URL.createObjectURL(val)}" class="effect8" alt="Gallery Image" style="width: 100px; height: 100px; object-fit: cover;" />
                            <p class="file-name-overlay">${fileName}</p>
                            <button type="button" class="remove-upload" onclick="removeGalleryImage(this)">Remove</button>
                        </div>
                    `);
                });

                if (totalImages >= maxImages) {
                    $('#galUpload').hide();
                } else {
                    $('#galUpload').show();
                }
            });

            $("input[name='name']").on("input", function() {
                $("input[name='slug']").val(StringToSlug($(this).val()));
            });

            // Initial validation
            validateForm();
        });

        function removeUpload(previewId, inputId) {
            $('#' + previewId).hide();
            $('#' + previewId + ' img').attr('src', '');
            $('#' + previewId + ' p.file-name-overlay').remove();
            $('#' + previewId + ' button').off();
            $('#' + previewId + ' button').css('display', 'none');
            $('#' + inputId).val('');
            validateForm();
        }

        function removeGalleryImage(button) {
            const galleryItem = $(button).closest('.gitems');
            galleryItem.remove();

            // Show upload button if we're below the limit
            if ($('.gitems').length < 5) {
                $('#galUpload').show();
            }
        }

        $(document).ready(function() {
            const addVariantBtn = document.getElementById('add-variant-btn');
            const boxFields = document.getElementById('container-box');
            const mainFields = document.querySelector('.main-fields');
            const variantsContainer = document.getElementById('variant-fields-container');

            function createVariantFields() {
                const selectedAttributeId = $("#product-select").val();
                const selectedAttributeName = $("#product-select option:selected").text();

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
                    validateForm();
                });

                const variantInputs = variantDiv.querySelectorAll(
                    'input[name="variant_name[]"], input[name="variant_price[]"], input[name="variant_quantity[]"]'
                );
                variantInputs.forEach(input => {
                    input.addEventListener('input', validateForm);
                });

                hasVariants = true;
                updateMainFieldsVisibility();
                updateRequiredIndicators();
                showContainer();
                validateForm();
            }

            function updateVariantNumbers() {
                const variants = variantsContainer.querySelectorAll('.variant-fields');
                variants.forEach((variant, index) => {
                    const variantHeader = variant.querySelector('.variant-header h3');
                    variantHeader.textContent = `Variant ${index + 1}`;
                    variant.setAttribute('data-variant-index', index + 1);
                    const removeBtn = variant.querySelector('.remove-variant-btn');
                    removeBtn.setAttribute('data-variant-index', index + 1);
                });
            }

            function showContainer() {
                if (variantsContainer.children.length > 0) {
                    boxFields.style.display = 'block';
                } else {
                    boxFields.style.display = 'none';
                    hasVariants = false;
                    updateRequiredIndicators();
                }
            }

            function updateMainFieldsVisibility() {
                if (variantsContainer.children.length > 0) {
                    mainFields.style.display = 'none';
                } else {
                    mainFields.style.display = 'block';
                }
            }

            addVariantBtn.addEventListener('click', createVariantFields);
        });

        function StringToSlug(Text) {
            return Text.toLowerCase()
                .replace(/[^\w ]+/g, "")
                .replace(/ +/g, "-");
        }
    </script>
@endpush
