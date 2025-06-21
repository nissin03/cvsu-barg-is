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

        switch ($currentRoute) {
            case 'shop.index':
                $breadcrumbs[] = ['url' => null, 'label' => 'Shop'];
                break;
            case 'shop.product.details':
                $breadcrumbs[] = ['url' => route('shop.index'), 'label' => 'Shop'];
                $breadcrumbs[] = ['url' => null, 'label' => 'Product Details'];
                break;
            case 'about.index':
                $breadcrumbs[] = ['url' => null, 'label' => 'About Us'];
                break;
            case 'contact.index':
                $breadcrumbs[] = ['url' => null, 'label' => 'Contact Us'];
                break;
            default:
                $breadcrumbs[] = ['url' => null, 'label' => ucwords(str_replace('.', ' ', $currentRoute))];
        }

        $view->with('breadcrumbs', $breadcrumbs);
    }
}
