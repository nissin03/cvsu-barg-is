@extends('layouts.app')
<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" />
<style>
    .upload-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    .upload-image .item {
        margin: 10px 0;
        position: relative;
        width: 100%;
    }

    #preview-img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    #upload-file {
        text-align: center;
        width: 100%;
    }

    .uploadfile {
        display: block;
        padding: 20px;
        background-color: #f8f9fa;
        border: 2px dashed #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, border-color 0.3s;
        text-align: center;
    }

    .uploadfile:hover {
        background-color: #e9ecef;
        border-color: #ccc;
    }

    .uploadfile .icon {
        font-size: 32px;
        color: #007bff;
        margin-bottom: 10px;
    }

    .uploadfile .body-text {
        font-size: 18px;
        color: #333;
    }

    .uploadfile .tf-color {
        color: #007bff;
        font-weight: bold;
        font-size: 13px;
    }

    /* Profile Sidebar */
    .profile-sidebar {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .profile-sidebar .profile-image img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-sidebar p {
        font-weight: bold;
        font-size: 18px;
        margin-top: 15px;
        color: #333;
    }

    .profile-content h2 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .profile-content p {
        font-size: 16px;
        color: #555;
    }

    .error {
        color: red;
        font-size: 14px;
        margin-top: 5px;
    }
</style>
@section('content')
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-5">
                <div class="row h-100">
                    <x-profile-sidebar />

                    <!-- Profile Content -->
                    <div class="col-md-9 mb-4">
                        <div class="bg-white p-4 border rounded shadow-sm profile-content h-100">
                            <h2>Update Profile</h2>
                            <p>Edit your personal information and profile image</p>
                            <hr>

                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="alert alert-info">
                                    {{ session('info') }}
                                </div>
                            @endif

                            <!-- Display validation errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Form to update profile image -->
                            <form action="{{ route('user.profile.image.update') }}" method="POST"
                                enctype="multipart/form-data" id="profileImageForm">
                                @csrf
                                @method('PUT')

                                <fieldset>
                                    <div class="upload-image flex-grow">
                                        <div class="item" id="imgpreview" style="display:none; text-align: center;">
                                            <img id="preview-img"
                                                class="img-thumbnail img-fluid rounded-circle mx-auto d-block"
                                                style="width: 200px; height: 200px; object-fit: cover;"
                                                src="{{ $user->profile_image ? Storage::url($user->profile_image) : asset('images/default-avatar.png') }}"
                                                alt="Profile Image">
                                        </div>
                                        <div id="upload-file" class="item up-load">
                                            <label class="uploadfile" for="myFile">
                                                <span class="icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </span>
                                                <span class="body-text" style="font-size: 13px">
                                                    Upload your image here
                                                    <span class="tf-color">click here to browse</span>
                                                </span>
                                                <input type="file" id="myFile" name="profile_image"
                                                    accept="image/png,image/jpeg,image/jpg"
                                                    style="opacity: 0; height: 1px; display:none;" required>
                                            </label>
                                            @error('profile_image')
                                                <div class="error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-black" id="submitBtn">Update Profile
                                        Image</button>
                                    @if ($user->profile_image)
                                        <a href="{{ route('user.profile.image.delete') }}" class="btn btn-danger ml-2"
                                            onclick="return confirm('Are you sure you want to delete your profile image?')">
                                            Delete Image
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Check if user has existing profile image
            const currentImage =
                '{{ $user->profile_image ? Storage::url($user->profile_image) : asset('images/default-avatar.png') }}';
            const defaultImage = '{{ asset('images/default-avatar.png') }}';

            // Show preview if image exists and is not default
            if (currentImage !== defaultImage) {
                $('#imgpreview').show();
                $('#preview-img').attr('src', currentImage);
            }

            // Handle file input change
            $("#myFile").on("change", function() {
                const file = this.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a valid image file (PNG, JPG, or JPEG).');
                        this.value = '';
                        return;
                    }

                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB.');
                        this.value = '';
                        return;
                    }

                    // Show preview
                    const imgPreview = $("#imgpreview");
                    const previewImg = $("#preview-img");

                    imgPreview.show();
                    previewImg.attr('src', URL.createObjectURL(file));
                } else {
                    // Hide preview if no file selected
                    $("#imgpreview").hide();
                }
            });

            // Handle form submission
            $("#profileImageForm").on("submit", function(e) {
                const fileInput = document.getElementById('myFile');
                if (!fileInput.files || fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Please select an image file to upload.');
                    return false;
                }

                // Disable submit button to prevent double submission
                $("#submitBtn").prop('disabled', true).text('Updating...');
            });
        });
    </script>
@endpush
