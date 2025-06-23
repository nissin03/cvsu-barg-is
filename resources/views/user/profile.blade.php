@extends('layouts.app')

<x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
    :breadcrumbs="$breadcrumbs" />

<style>
    /* General styles */
    .profile-sidebar {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .profile-image img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
    }

    .profile-content {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-content h2 {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }

    .profile-content p {
        font-size: 16px;
        color: #555;
    }

    .alert {
        margin-top: 20px;
    }

    /* Button styles */
    .btn-black {
        background-color: #343a40;
        color: #fff;
    }

    .btn-black:hover {
        background-color: #23272b;
    }
</style>

@section('content')
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="row h-100">
                    <x-profile-sidebar />

                    <!-- Profile Content -->
                    <div class="col-lg-8 mb-4">
                        <div class="profile-content">
                            <h2>My Profile</h2>
                            <p class="text-muted">Manage and protect your account</p>
                            <hr>
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if (request()->query('swal') && session('message'))
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            title: 'Incomplete Profile',
                                            text: "{{ session('message') }}",
                                            icon: 'warning',
                                            confirmButtonText: 'Complete Profile',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                        });
                                    });
                                </script>
                            @endif

                            @if ($user->role === 'student')
                                <!-- Profile Details for Student -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> {{ $user->name }}</p>
                                        <p><strong>Email:</strong> {{ $user->email }}</p>
                                        <p><strong>Phone:</strong> +63 {{ $user->phone_number ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Year Level:</strong> {{ $user->year_level ?? 'Not provided' }}</p>
                                        <p><strong>Department:</strong> {{ $user->department ?? 'Not provided' }}</p>
                                        <p><strong>Course:</strong> {{ $user->course ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Sex:</strong> {{ $user->sex ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Role:</strong> {{ $user->role ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            @elseif ($user->role === 'employee')
                                <!-- Profile Details for Employee -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> {{ $user->name }}</p>
                                        <p><strong>Email:</strong> {{ $user->email }}</p>
                                        <p><strong>Phone:</strong> +63 {{ $user->phone_number ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Sex:</strong> {{ $user->sex ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Role:</strong> {{ $user->role ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            @elseif ($user->role === 'non-employee')
                                <!-- Profile Details for Non-Employee -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> {{ $user->name }}</p>
                                        <p><strong>Email:</strong> {{ $user->email }}</p>
                                        <p><strong>Phone:</strong> +63 {{ $user->phone_number ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Sex:</strong> {{ $user->sex ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Role:</strong> {{ $user->role ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="alert alert-warning" role="alert">
                                <strong>Note:</strong> Update your academic details if there are any changes.
                            </div>

                            <!-- Edit Button -->
                            <div class="text-end mb-3">
                                <a href="{{ route('user.profile.edit', ['id' => $user->id]) }}" class="btn btn-black">Edit
                                    Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        if (window.location.hash === '#swal-done') {
            history.replaceState(null, null, ' ');
        }
    </script>
@endpush
