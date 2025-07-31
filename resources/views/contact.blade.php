@extends('layouts.app')
@section('content')
    <style>
        .text-danger {
            color: #e72010 !important;
        }
        .btn-black:disabled {
            cursor: not-allowed;
            background: #888 !important;
        }
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
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
        } elseif ($currentRoute === 'home.contact') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
        $todaysMessagesCount = $user ? $user->contacts()->whereDate('created_at', today())->count() : 0;
        $messageLimitReached = $todaysMessagesCount >= 3;
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}" :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        <section class="contact-us">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    @if ($errors->has('user_info'))
                        <div class="alert alert-danger">
                            {{ $errors->first('user_info') }}
                        </div>
                    @endif

                    @if (Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                        </div>
                    @endif

                    @if (Session::has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ Session::get('error') }}
                        </div>
                    @endif

                    <form name="contact-us-form" class="needs-validation" novalidate action="{{ route('home.contact.store') }}" method="POST">
                        @csrf
                        <h3 class="mb-5 text-center">Get In Touch</h3>

                        @if(auth()->check())
                            <div class="text-center mb-4">
                                Messages today: {{ $todaysMessagesCount }}/3
                            </div>
                        @endif

                        @if($messageLimitReached)
                            <div class="alert alert-warning">
                                You have reached your daily limit of 3 messages. Please try again tomorrow.
                            </div>
                        @endif

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="contact_us_name" name="name" placeholder="Your Full Name" value="{{ old('name', $user->name ?? '') }}" readonly required>
                            <label for="contact_us_name">Name *</label>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="contact_us_phone" name="phone" placeholder="Your Phone Number" value="{{ old('phone', $user->phone_number ?? '') }}" {{ $user ? 'readonly' : '' }} required>
                            <label for="contact_us_phone">Phone *</label>
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="contact_us_email" name="email" placeholder="Your Email Address" value="{{ old('email', $user->email ?? '') }}" {{ $user ? 'readonly' : '' }} required>
                            <label for="contact_us_email">Email address *</label>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-floating mb-4">
                            <textarea class="form-control" name="message" id="contact_us_message" placeholder="Write your message here" style="height: 150px;" required {{ $messageLimitReached ? 'disabled' : '' }}>{{ old('message') }}</textarea>
                            <label for="contact_us_message">Your Message *</label>
                            @error('message')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="submit_button" class="btn btn-black btn-lg" {{ $messageLimitReached ? 'disabled' : '' }}>
                                {{ $messageLimitReached ? 'Daily Limit Reached' : 'Submit' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[name="contact-us-form"]');
            
            form.addEventListener('submit', function(e) {
                @if($messageLimitReached)
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Message Limit Reached',
                        text: 'You can only send 3 messages per day. Please try again tomorrow.',
                        confirmButtonText: 'Okay'
                    });
                    return false;
                @endif
            });

            @if (Session::has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ Session::get('success') }}',
                    confirmButtonText: 'Okay'
                });
            @endif

            @if (Session::has('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ Session::get('error') }}',
                    confirmButtonText: 'Try Again'
                });
            @endif

            @if ($errors->has('no_account'))
                Swal.fire({
                    icon: 'warning',
                    title: 'You Don\'t Have an Account!',
                    text: '{{ $errors->first('no_account') }}',
                    showCancelButton: false,
                    confirmButtonText: 'Login'
                }).then(() => {
                    window.location.href = '{{ route('login') }}';
                });
            @endif

            @if ($errors->has('user_info'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Profile!',
                    text: '{{ $errors->first('user_info') }}',
                    showCancelButton: true,
                    confirmButtonText: 'Fill Up',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('user.profile.edit', ['id' => auth()->user()->id]) }}';
                    }
                });
            @endif

            @if ($errors->any() && !$errors->has('user_info') && !$errors->has('no_account'))
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error!',
                    text: 'Please fill in all required fields.',
                    confirmButtonText: 'Okay'
                }).then(() => {
                    let firstInvalidField = document.querySelector('.is-invalid');
                    if (firstInvalidField) {
                        firstInvalidField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalidField.focus();
                    }
                });
            @endif
        });
    </script>
@endsection