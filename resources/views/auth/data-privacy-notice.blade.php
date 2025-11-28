@extends('layouts.app')

@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <div class="container" style="padding-top: 100px;">

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center justify-content-center mb-4 mt-5">
                        <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ asset('images/logo.png') }}" alt="CvSU Logo"
                                style="height: 80px; aspect-ratio: 1/1; object-fit: contain;">
                        </div>

                        <h1 class="logo text-center mx-4 m-0">CvSU BaRG</h1>

                        <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ asset('images/logo/BaRG-logo.png') }}" alt="CvSU Logo"
                                style="height: 100px; aspect-ratio: 1/1; object-fit: contain;">
                        </div>
                    </div>


                    <div class="agreement-container">
                        <div class="agreement-box">
                            <h2 class="header-title">Data Privacy Notice</h2>
                            <p class="header-subtitle">
                                Please review and accept our policy to continue using the service.
                            </p>
                            <div class="privacy-content">
                                @if (isset($policy) && $policy->content)
                                    {!! $policy->content !!}
                                @else
                                    <p>
                                        We are committed to protecting your personal data and respecting your privacy. This
                                        Data
                                        Privacy Notice explains how we collect, use, and safeguard your information when you
                                        use
                                        our services.
                                    </p>
                                    <h6>Information We Collect</h6>
                                    <p>
                                        We may collect personal information such as your name, email address, contact
                                        details,
                                        and payment information when you register for an account or make a purchase.
                                    </p>
                                    <h6>How We Use Your Information</h6>
                                    <p>
                                        Your information is used to provide and improve our services, process transactions,
                                        communicate with you, and comply with legal obligations. We do not sell or rent your
                                        personal data to third parties.
                                    </p>
                                    <h6>Data Security</h6>
                                    <p>
                                        We implement appropriate security measures to protect your personal data from
                                        unauthorized access, alteration, disclosure, or destruction. However, no method of
                                        transmission over the internet is completely secure.
                                    </p>
                                    <h6>Your Rights</h6>
                                    <p>
                                        You have the right to access, correct, or delete your personal data. You may also
                                        object
                                        to or restrict certain processing of your data. To exercise these rights, please
                                        contact
                                        us.
                                    </p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('data-privacy.accept') }}">
                                @csrf
                                <div class="form-check agreement-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="accept_terms" id="accept_terms"
                                        required>
                                    <label class="form-check-label" for="accept_terms">
                                        I agree to MySite's <a href="#" target="_blank">Terms of Service</a> and the
                                        <a href="{{ route('data-privacy.notice') }}" target="_blank">Privacy Policy</a>.
                                    </label>
                                </div>

                                @error('accept_terms')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="btn btn-dark btn-continue">
                                    Accept and Continue
                                </button>
                            </form>
                        </div>
                    </div>
                </div> <!-- col-lg-8 -->
            </div> <!-- row -->
        </div> <!-- container -->
    </main>
@endsection

@push('styles')
    <style>
        .agreement-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .agreement-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 650px;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #212529;
            margin-bottom: 1.5rem;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        .header-subtitle {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .privacy-content {
            text-align: left;
            padding: 1.5rem;
            background-color: #f1f3f5;
            /* Slightly darker background for the content box */
            border-radius: 8px;
            margin-bottom: 2rem;
            height: 400px;
            /* Fixed height for scrolling */
            overflow-y: auto;
            /* Enable vertical scrolling */
        }

        .privacy-content h6 {
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #343a40;
        }

        .form-check.agreement-check {
            text-align: left;
            margin-bottom: 1rem;
            cursor: pointer;
        }

        .form-check-label a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .form-check-label a:hover {
            text-decoration: underline;
        }

        .btn-continue {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 1rem;
        }
    </style>
@endpush
