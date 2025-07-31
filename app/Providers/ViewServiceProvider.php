<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\View\Composers\BreadcrumbComposer;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', BreadcrumbComposer::class);
    }
}
