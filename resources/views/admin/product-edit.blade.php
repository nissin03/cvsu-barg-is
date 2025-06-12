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

                    <div class="gap22 cols">
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
                    @enderror

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
                                    <button type="button" class="remove-upload" id="remove-upload"
                                        onclick="removeUpload('imgpreview')">Remove</button>
                                </div>
                            @else
                                <div class="item" id="imgpreview" style="display:none">
                                    <img src="" id="preview-img" class="effect8" alt="">
                                    <button type="button" class="remove-upload" id="remove-upload"
                                        onclick="removeUpload('imgpreview')">Remove</button>
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
                        <span class="alert alert-danger text-center">{{ $message }} </span>
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
                                    <div class="item gitems">
                                        <img src="{{ asset('uploads/products/' . trim($img)) }}" class="effect8"
                                            alt="Gallery Image" style="width: 100px; height: 100px; object-fit: cover;" />
                                        <input type="hidden" name="existing_images[]" value="{{ trim($img) }}">
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
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror
                    <div id="gallery-error" class="error-message"></div>


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
                            <div class="field">
                                <label>Out of Stock Quantity</label>
                                <input type="number" name="outofstock_quantity"
                                    value="{{ $product->outofstock_quantity }}" required>
                            </div>
                        </div>
                    </fieldset>
                    @error('instock_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror
                    @error('reorder_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror
                    @error('outofstock_quantity')
                        <span class="alert alert-danger">{{ $message }} </span>
                    @enderror


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
                    {{-- <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Edit Product</button>
                    </div> --}}
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
                    <input type="checkbox" id="use-variants-checkbox" {{ $hasVariant ? 'checked' : '' }}>
                    <label for="use-variants-checkbox">Use Variants</label>


                    @error('product_attribute_id')
                        <span class="alert alert-danger text-center">{{ $message }} </span>
                    @enderror

                    <!-- Button to Add Variant -->
                    <div class="cols gap22">
                        <button type="button" id="add-variant-btn" class="btn btn-primary">Add Variant</button>
                    </div>
                </div>

                <div class="wg-box">
                    <!-- Variant Fields Container for multiple variants -->
                    <div id="variant-fields-container" style="{{ $hasVariant ? 'display: block;' : 'display: none;' }}">
                        @foreach ($product->attributeValues as $variant)
                            <div class="variant-fields" data-variant-index="{{ $loop->index }}">
                                <input type="hidden" name="product_attribute_id[]"
                                    value="{{ $variant->product_attribute_id }}">
                                <fieldset class="name">
                                    <div class="body-title mb-10">Variant Name:</div>
                                    <input type="text" name="variant_name[]" class="form-control"
                                        value="{{ $variant->value }}" placeholder="Variant Name">
                                </fieldset>
                                <fieldset class="name">
                                    <div class="body-title mb-10">Variant Price:</div>
                                    <input type="text" name="variant_price[]" class="form-control"
                                        value="{{ $variant->price }}" placeholder="Variant Price">
                                </fieldset>

                                <fieldset class="name">
                                    <div class="body-title mb-10">Variant Quantity:</div>
                                    <input type="number" name="variant_quantity[]" class="form-control"
                                        value="{{ $variant->quantity }}" placeholder="Variant Quantity">
                                </fieldset>
                                <button type="button" class="remove-variant-btn btn btn-danger my-4">Remove
                                    Variant</button>
                            </div>
                        @endforeach
                    </div>
                    <div class="cols gap10">
                        <button class="tf-button w-full" type="submit">Edit Product</button>
                    </div>
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
            $("#myFile").on("change", function(e) {
                const [file] = this.files;
                if (file) {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                    $("#imgpreview .remove-upload").show();
                }
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

        });

        function removeUpload(previewId, inputId) {
            $('#' + previewId).hide();
            $('#' + previewId + ' img').attr('src', '');
            $('#' + previewId + ' p.file-name-overlay').remove();
            $('#' + previewId + ' button').off();
            $('#' + previewId + ' button').css('display', 'none');
            $('#' + inputId).val('');
        }

        function removeGalleryImage(button, existingImage = null) {
            const galleryItem = $(button).closest('.gitems');

            if (existingImage) {
                // Add hidden input for removed existing image
                $('<input>').attr({
                    type: 'hidden',
                    name: 'removed_images[]',
                    value: existingImage
                }).appendTo('form');
            }

            galleryItem.remove();

            // Show upload button if we're below the limit
            if ($('.gitems').length < 5) {
                $('#galUpload').show();
            }
        }

        function StringToSlug(Text) {
            return Text.toLowerCase()
                .replace(/[^\w ]+/g, "")
                .replace(/ +/g, "-");
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const imgPreview = document.getElementById('imgpreview');
            const previewImg = document.getElementById('preview-img');
            const removeBtn = document.getElementById('remove-btn');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imgPreview.style.display = 'block';
                    removeBtn.style.display = 'inline-block'; // Show the remove button
                }
                reader.readAsDataURL(file);
            }
        }

        // document.getElementById('remove-btn').addEventListener('click', function() {
        //     const imgPreview = document.getElementById('imgpreview');
        //     const previewImg = document.getElementById('preview-img');
        //     const removeBtn = document.getElementById('remove-btn');
        //     const fileInput = document.getElementById('myFile');

        //     // Hide the preview and remove the selected file
        //     imgPreview.style.display = 'none';
        //     previewImg.src = '';
        //     fileInput.value = ''; // Clear the file input
        //     removeBtn.style.display = 'none'; // Hide the remove button
        // });
    </script>
@endpush
