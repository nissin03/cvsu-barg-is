@extends('layouts.app')

@section('content')
    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">

        {{-- University Mission and Vision --}}
        <section class="mb-5">
            <h2 class="text-center fw-bold mb-4">University Mission & Vision</h2>
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <img src="{{ asset('images/cvsu-banner.jpg') }}" class="img-fluid rounded shadow-sm"
                                alt="">
                        </div>
                        <div class="col-6">
                            <img src="{{ asset('images/cvsu-banner.jpg') }}" class="img-fluid rounded shadow-sm"
                                alt="">
                        </div>
                        <div class="col-12">
                            <img src="{{ asset('images/cvsu-banner.jpg') }}" class="img-fluid rounded shadow-sm"
                                alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 rounded shadow-sm bg-light h-100">
                        <h4 class="fw-semibold">Vision</h4>
                        <p class="text-muted">The Premier University in historic Cavite globally recognized for excellence
                            in character development, academics, research, innovation and sustainable community engagement.
                        </p>
                        <h4 class="fw-semibold mt-4">Mission</h4>
                        <p class="text-muted">Cavite State University shall provide excellent, equitable, and relevant
                            educational opportunities in the arts, sciences and technology through quality instruction and
                            responsive research and development activities.<br><br>
                            It shall produce professional, skilled and morally upright individuals for global
                            competitiveness.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- BaRG Mission and Vision --}}
        <section class="mb-5">
            <h2 class="text-center fw-bold mb-4">BaRG Mission & Vision</h2>
            <div class="row g-4 align-items-center">
                <div class="col-lg-6 order-lg-2">
                    <div class="row g-3">
                        <div class="col-6">
                            <img src="{{ asset('images/OVPEBA.jpg') }}" class="img-fluid rounded shadow-sm" alt="">
                        </div>
                        <div class="col-6">
                            <img src="{{ asset('images/ovp.jpg') }}" class="img-fluid rounded shadow-sm" alt="">
                        </div>
                        <div class="col-12">
                            <img src="{{ asset('images/ovp.jpg') }}" class="img-fluid rounded shadow-sm" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="p-4 rounded shadow-sm bg-light h-100">
                        <h4 class="fw-semibold">BaRG Vision</h4>
                        <p class="text-muted">The office of Production and Resource Generation of Cavite State University
                            as an aggressive arm of the University in resource generation and an entrepreneurship model of
                            SUCs in this country.</p>
                        <h4 class="fw-semibold mt-4">BaRG Mission</h4>
                        <p class="text-muted">To vigorously pursue a sustainable resource generation program using the
                            University's resources while developing the capabilities of its staff, faculty, students and
                            other partners.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Quality Policy and Core Values --}}
        <section>
            <h2 class="text-center fw-bold mb-4">Quality Policy & Core Values</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-4 rounded shadow-sm bg-light h-100">
                        <h4 class="fw-semibold">Quality Policy</h4>
                        <p class="text-muted">We commit to the highest standards of education. Value our stakeholder.
                            Strive for continual improvement of our products and services, and Uphold the University's
                            tenets
                            of Truth, Excellence, and Service to produce globally competitive and morally upright
                            individuals.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 rounded shadow-sm bg-light h-100">
                        <h4 class="fw-semibold">Core Values</h4>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><span class="fw-semibold">Truth</span></li>
                            <li class="mb-2"><span class="fw-semibold">Excellence</span></li>
                            <li class="mb-2"><span class="fw-semibold">Service</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection
