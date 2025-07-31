<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


class BreadcrumbComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        $currentRoute = Route::currentRouteName();

        $homeRoute = match ($user->utype ?? 'guest') {
            'USR' => route('user.index'),
            'ADM' => route('admin.index'),
            default => route('home.index'),
        };

        $breadcrumbs = [['url' => $homeRoute, 'label' => 'Home']];
        $namedRoutes = [
            'shop.index' => 'Shop',
            'shop.product.details' => 'Product Details',
            'about.index' => 'About Us',
            'contact.index' => 'Contact Us',

            'user.profile' => 'My Profile',
            'user.profile.edit' => 'Edit Profile',
            'user.profile.update' => 'Update Profile',
            'user.profile.image.edit' => 'Edit Profile Image',
            'user.profile.image.update' => 'Update Profile Image',
            'user.profile.image.delete' => 'Delete Profile Image',

            'user.facilities.index' => 'Facilities',
            'user.facilities.details' => 'Facility Details',
            'user.facilities.placeReservation' => 'Place Reservation',

            'user.reservations' => 'My Reservations',
            'user.reservations_history' => 'Reservation History',
            'user.reservation.history' => 'Reservation History',
            'user.reservation' => 'My Reservation',
            'user.reservation-details' => 'Reservation Details',
            'user.account_cancel_reservation' => 'Cancel Reservation',

            'facility.reserve' => 'Reserve Facility',
            'facility.checkout' => 'Checkout',

            'cart.index' => 'Cart',
            'cart.add' => 'Add to Cart',
            'cart.item.updateVariant' => 'Update Cart Item',
            'cart.item.remove' => 'Remove Item',
            'cart.empty' => 'Clear Cart',
            'cart.checkout' => 'Checkout',
            'cart.place.an.order' => 'Place Order',
            'cart.order.confirmation' => 'Order Confirmation',

            'slots.available' => 'Time Slots',
            'preorders.accept' => 'Accept Pre-Order',
            'preorders.cancel' => 'Cancel Pre-Order',

            'google-auth' => 'Login with Google',
            'google-auth-callback' => 'Google Callback',

            'user.order.history' => 'Order History',
            'user.account.rentals' => 'My Rentals',
        ];

        if ($currentRoute === 'shop.product.details') {
            $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
        }

        $label = $namedRoutes[$currentRoute] ?? ucwords(str_replace(['.', '-'], ' ', $currentRoute));
        $breadcrumbs[] = ['url' => null, 'label' => $label];

        $view->with('breadcrumbs', $breadcrumbs);
    }
}
