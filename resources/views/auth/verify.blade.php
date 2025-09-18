@extends('layouts.app')

@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <div class="container" style="padding-top: 100px;">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header fw-bold text-center">
                            {{ __('Verify Your Email Address') }}
                        </div>

                        <div class="card-body text-center">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            <p>
                                {{ __('Before proceeding, please check your email for a verification link.') }}
                                <br>
                                {{ __('If you did not receive the email') }},
                            </p>

                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-dark">
                                    {{ __('Click here to request another') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
