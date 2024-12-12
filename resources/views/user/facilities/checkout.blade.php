@extends('layouts.app')
@section('content')
    <style>
       
    </style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();

        // Determine the base home route based on user type
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'DIR' => route('director.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        // Initialize breadcrumbs array with the Home link
        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];

        // Handle different pages
        if ($currentRoute === 'rentals.checkout') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Checkout Page'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-8 col-lg-6">
            <form name="checkout-form" action="#" method="POST">
                @csrf
                <div class="mb-4">
                    <div class="my-account__coursedept-list">
                        <div class="my-account__coursedept-list-item">
                            <div class="my-account__coursedept-list__detail">
                                <div class="mb-3">
                                    <p><strong>Full Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Requirements</h5>
                    @if($facility && $facility->requirements) 
                        @php
                            $fileExtension = strtolower(pathinfo($facility->requirements, PATHINFO_EXTENSION));
                            $isImageReq = in_array($fileExtension, ['jpg', 'jpeg', 'png']);
                        @endphp
                
                        @if(!$isImageReq) 
                            <p>
                                <a href="{{ asset('/app/pulic/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Document
                                </a>
                            </p>
                        @else 
                            <p>
                                <a href="{{ asset('/app/public/storage/facilities/' . $facility->requirements) }}" download>
                                    Download Requirements Image
                                </a>
                            </p>
                        @endif
                    @else
                        <p>No Requirements document uploaded.</p>
                    @endif
                    @error('requirements')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                
    
                <div class="mb-3">
                    <label for="qualification">Qualification Document (PDF/DOC)</label>
                    <input type="file" id="qualification" name="qualification" class="form-control" accept=".pdf,.doc,.docx" required>
                    @error('qualification')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                
    
    
                <div class="order-summary mb-4">
                    <h3 class="text-center">Reservation Details</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ $reservationData['facility_attributes_name'] }}</strong></td>
                                <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Total Price</strong></td>
                                <td class="text-end"><strong>{{ $reservationData['total_price'] ?? 'N/A' }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
    
                <button type="submit" class="btn btn-warning w-100">Place Reservation</button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
    
    </script>
@endpush
