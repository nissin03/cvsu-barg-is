@extends('layouts.app')
@section('content')
<style>
    p{
        margin: 5px 0 5px 0;
    }
</style>

    @php
        $user = auth()->user();
        $currentRoute = request()->route()->getName();
        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
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
        } elseif ($currentRoute === 'contact.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
        } elseif ($currentRoute === 'rentals.index') {
            $breadcrumbs[] = ['url' => null, 'label' => 'Rentals'];
        } else {
            $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }
    @endphp

    <x-header backgroundImage="{{ asset('images/cvsu-banner.jpg') }}" title="{{ last($breadcrumbs)['label'] }}"
        :breadcrumbs="$breadcrumbs" />

    <main class="container my-5">
        @foreach ($facilities as $facility)
        <div class="product-item d-flex justify-content-between" style="align-items: center; flex-direction: row; padding: 15px; margin-bottom: 20px;" onclick="window.location.href='{{ route('user.facilities.details', ['slug' => $facility->slug]) }}'">
            <div class="image" style="width: 30%;">
                <img src="{{ asset('storage/' . $facility->image) }}" alt="" style="border-radius: 5px; width: 100%; height: 250px;">
            </div>
            <div class="rental-info" style="width: 70%">
                <h1 style="margin-left: 1.2rem">{{ $facility->name }}  <hr></h1>
               
                <div style="margin-left: 50px;">
                <div class="product-single__description" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; ">
                    <p>{{ $facility->description }}</p>  
                </div>    
                    @if ($facility->prices->isNotEmpty())
                            @foreach ($facility->prices as $price)
                                    <p><strong>{{ $price->name }}: </strong> <span class="product-type text-primary">&#8369; {{ number_format($price->value, 2) }}</span></p>
                                    <p>@if ($price->is_based_on_days)
                                            <span class="badge">Per Day</span>
                                        @endif</p>
                            @endforeach
                    @else
                        <p>No prices available for this facility.</p>
                    @endif
                </div>
                <button type="submit" class="btn btn-shop btn-addtocart" id="reserve-btn"
                    style="padding: 15px 30px; font-size: 18px; ">
                    Show Available Dates
                </button>
            </div>  
        </div>
        @endforeach
    </main>

    

    
@endsection

@push('scripts')
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script>
              
       
    </script>
@endpush
