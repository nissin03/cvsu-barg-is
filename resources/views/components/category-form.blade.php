{{-- Component: resources/views/components/category-form.blade.php --}}

@props(['action', 'method' => 'POST', 'category' => null, 'parentCategories' => [], 'buttonText' => 'Save'])

<div class="wg-box">
    <form class="form-new-product form-style-1" action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @if ($category)
            <input type="hidden" name="id" value="{{ $category->id }}" />
        @endif

        {{-- Category Name Field --}}
        <fieldset class="name">
            <div class="body-title">Category Name <span class="tf-color-1">*</span></div>
            <input class="flex-grow" type="text" placeholder="Category name" name="name" tabindex="0"
                value="{{ old('name', $category->name ?? '') }}" aria-required="true" required>
        </fieldset>
        @error('name')
            <span class="alert alert-danger text-center">{{ $message }}</span>
        @enderror

        {{-- Parent Category Field --}}
        <fieldset class="name">
            <div class="body-title">Parent Category<span class="tf-color-1"> (optional)</span></div>
            <div class="select w-100">
                <select name="parent_id" class="d-block">
                    <option value="">Select Parent Category</option>
                    @foreach ($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}"
                            {{ old('parent_id', $category->parent_id ?? '') == $parentCategory->id ? 'selected' : '' }}>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </fieldset>

        {{-- Image Upload Field --}}
        <fieldset>
            <div class="body-title">Upload images <span class="tf-color-1">*</span></div>
            <div class="upload-image flex-grow">
                <div class="item" id="imgpreview"
                    style="{{ $category && $category->image ? 'display:block; position:relative;' : 'display:none; position:relative;' }}">
                    <img src="{{ $category && $category->image ? asset('uploads/categories/' . $category->image) : '' }}"
                        id="preview-img" class="effect8" alt="">
                    <button type="button" class="remove-upload" id="remove-btn"
                        style="position: absolute; top: 10px; right: 10px; z-index: 10;">Remove</button>
                </div>
                <div id="upload-file" class="item up-load">
                    <label class="uploadfile" for="myFile">
                        <span class="icon">
                            <i class="icon-upload-cloud"></i>
                        </span>
                        <span class="body-text">Select your images here or click to browse</span>
                        <input type="file" id="myFile" name="image" accept="image/*"
                            {{ !$category ? 'required' : '' }}>
                    </label>
                </div>
            </div>
        </fieldset>
        @error('image')
            <span class="alert alert-danger text-center">{{ $message }}</span>
        @enderror

        {{-- Submit Button --}}
        <div class="bot">
            <div></div>
            <button class="tf-button w208" type="submit">{{ $buttonText }}</button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        $(function() {
            // Image preview on file selection
            $("#myFile").on("change", function(e) {
                const [file] = this.files;
                if (file) {
                    $("#imgpreview img").attr('src', URL.createObjectURL(file));
                    $("#imgpreview").show();
                    $("#imgpreview").css('position', 'relative');
                    $("#remove-btn").show();
                }
            });

            // Remove image preview
            $("#remove-btn").on("click", function() {
                $("#imgpreview").hide();
                $("#imgpreview img").attr('src', '');
                $("#myFile").val('');
                $(this).hide();
            });
        });
    </script>
@endpush

<style>
    #imgpreview {
        position: relative;
    }

    .remove-upload {
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        z-index: 10 !important;
        background: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .remove-upload:hover {
        background: rgba(255, 0, 0, 1);
    }
</style>
