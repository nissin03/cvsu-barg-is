<div class="wg-box">
    <fieldset>
        <div class="body-title mb-10">Requirements <span class="tf-color-1">*</span></div>
        <div class="upload-image flex-grow">
            <div class="item" id="requirementsPreview"
                style="{{ isset($facility) && $facility->requirements ? '' : 'display:none' }}">
                @if (isset($facility) && $facility->requirements)
                    <p class="file-name-overlay">Current file: {{ $facility->requirements }}</p>
                @endif
                <img src="{{ asset('images/upload/upload-1.png') }}" id="requirements-preview-img" class="effect8"
                    alt="">
                <button type="button" class="remove-upload"
                    onclick="removeUpload('requirementsPreview', 'requirementsFile')">Remove</button>
            </div>
            <div id="upload-requirements" class="item up-load">
                <label class="uploadfile" for="requirementsFile">
                    <span class="icon">
                        <i class="icon-upload-cloud"></i>
                    </span>
                    <span class="body-text">Select your Requirements file here or click to browse</span>
                    <input type="file" id="requirementsFile" name="requirements" accept=".pdf,.doc,.docx">
                </label>
            </div>
        </div>
    </fieldset>
    @error('requirements')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror

    <!-- Image upload -->
    <fieldset>
        <div class="body-title">Upload main image <span class="tf-color-1">*</span></div>
        <div class="upload-image flex-grow">
            <div class="item" id="imgpreview"
                style="{{ isset($facility) && $facility->image ? '' : 'display:none' }}">
                @if (isset($facility) && $facility->image)
                    {{-- <p class="file-name-overlay">Current file: {{ $facility->image }}</p> --}}
                    <img src="{{ asset('storage/' . $facility->image) }}" id="preview-img" class="effect8"
                        alt="">
                @else
                    <img src="{{ asset('images/upload/upload-1.png') }}" id="preview-img" class="effect8"
                        alt="">
                @endif
                <button type="button" class="remove-upload"
                    onclick="removeUpload('imgpreview', 'myFile')">Remove</button>
            </div>
            <div id="upload-file" class="item up-load">
                <label class="uploadfile" for="myFile">
                    <span class="icon">
                        <i class="icon-upload-cloud"></i>
                    </span>
                    <span class="body-text">Select your main image here or click to browse</span>
                    <input type="file" id="myFile" name="image" accept="image/*">
                </label>
            </div>
        </div>
    </fieldset>
    @error('image')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror

    <!-- Gallery images upload -->
    <fieldset>
        <div class="body-title mb-10">Upload Gallery Images</div>
        <div class="upload-image mb-16 flex-grow" id="gallery-container">
            @if (isset($facility) && $facility->images)
                @foreach (explode(',', $facility->images) as $img)
                    <div class="item gitems">
                        {{-- <p class="file-name-overlay">Current file: {{ $img }}</p> --}}
                        <img src="{{ asset('storage/' . $img) }}"
                            style="width: 100px; height: 100px; object-fit: cover;" />
                        <button type="button" class="remove-upload show"
                            onclick="removeGalleryImage(this, 'gFile')">Remove</button>
                    </div>
                @endforeach
            @endif
            <div id="galUpload" class="item up-load">
                <label class="uploadfile" for="gFile">
                    <span class="icon">
                        <i class="icon-upload-cloud"></i>
                    </span>
                    <span class="text-tiny">Select your images here or click to browse</span>
                    <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                </label>
            </div>
        </div>
    </fieldset>
    @error('images')
        <span class="alert alert-danger text-center">{{ $message }}</span>
    @enderror
</div>
