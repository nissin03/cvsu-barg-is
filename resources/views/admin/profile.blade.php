@extends('layouts.admin')

@section('content')
    <style>
        .pt-90 {
            padding-top: 90px !important;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .text-danger {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .profile-image-container {
            position: relative;
            display: inline-block;
        }

        .profile-image-container:hover .image-overlay {
            opacity: 1 !important;
        }

        .profile-image-container:hover .profile-image {
            filter: brightness(0.7);
        }

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Profile</h3>
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
                        <div class="text-tiny">Profile</div>
                    </li>
                </ul>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="wg-box">
                <div class="row">
                    <div class="col-md-6">
                        <form>
                            <div class="form-group">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ $user->name }}" readonly>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ $user->email }}" readonly>
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                        <form action="{{ route('admin.phone.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                                        id="phone_number" name="phone_number"
                                        value="{{ old('phone_number', ltrim($user->phone_number, '+63')) }}"
                                        placeholder="9XXXXXXXXX" pattern="^9\d{9}$" maxlength="10" inputmode="numeric"
                                        required>
                                </div>
                                @error('phone_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    Save
                                </button>
                            </div>

                        </form>


                        <form action="{{ route('admin.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <hr class="my-4">
                            <div class="form-group">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                    id="position" name="position" value="{{ old('position', auth()->user()->position) }}">
                                @error('position')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>


                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <div class="text-center">
                            <h4 class="mb-4">Profile Image</h4>

                            <div class="profile-image-container mb-4">
                                <img id="profile-image" src="{{ $user->profile_image_url }}" alt="Profile Image"
                                    class="rounded-circle profile-image"
                                    style="width: 200px; height: 200px; object-fit: cover; border: 3px solid #007bff; cursor: pointer;">

                                <div class="image-overlay"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0; transition: opacity 0.3s;     background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(0,0,0,0.5));">
                                    <i class="fas fa-camera" style="font-size: 2rem; color: white;"></i>
                                </div>
                            </div>

                            <form id="profile-image-form" enctype="multipart/form-data" style="display: none;">
                                @csrf
                                <input type="file" id="profile_image" name="profile_image" accept="image/*"
                                    style="display: none;">
                            </form>

                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="document.getElementById('profile_image').click();">
                                    <i class="fas fa-upload"></i> Change Profile Image
                                </button>
                            </div>

                            <div id="image-upload-status" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-spinner fa-spin"></i> Uploading image...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            document.getElementById('phone_number').addEventListener('input', (e) => {
                let input = e.target.value;

                input = input.replace(/\D/g, '');
                if (!input.startsWith('9')) {
                    input = '';
                } else if (input.length > 10) {
                    input = input.slice(0, 10);
                }

                e.target.value = input;
            });
            const profileImage = document.getElementById('profile-image');
            const fileInput = document.getElementById('profile_image');
            const uploadStatus = document.getElementById('image-upload-status');
            const form = document.getElementById('profile-image-form');

            profileImage.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const file = e.target.files[0];

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    uploadImage(file);
                }
            });

            function uploadImage(file) {
                const formData = new FormData();
                formData.append('profile_image', file);
                formData.append('_token', '{{ csrf_token() }}');

                uploadStatus.style.display = 'block';
                uploadStatus.innerHTML =
                    '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Uploading image...</div>';

                fetch('{{ route('admin.profile.update-image') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            uploadStatus.innerHTML =
                                '<div class="alert alert-success"><i class="fas fa-check"></i> ' + data
                                .message + '</div>';
                            profileImage.src = data.image_url;

                            setTimeout(() => {
                                uploadStatus.style.display = 'none';
                            }, 3000);
                        } else {
                            uploadStatus.innerHTML =
                                '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Upload failed!</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        uploadStatus.innerHTML =
                            '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Upload failed!</div>';
                    });
            }
        });
    </script>
@endsection
