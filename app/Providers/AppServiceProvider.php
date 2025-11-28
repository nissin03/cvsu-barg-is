<?php

namespace App\Providers;

use App\Models\User;
use App\Models\AddonPayment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Events\ContactMessageReceived;
use App\Observers\AddonPaymentObserver;
use App\Listeners\ContactMessageListener;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AddonPayment::observe(AddonPaymentObserver::class);
    }
}
