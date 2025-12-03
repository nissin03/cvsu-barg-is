<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->everyMinute();

Schedule::command('orders:cancel-unpaid-orders')->everyMinute();
Schedule::command('reservations:cancel-unpaid-reservations')->everyMinute();
// Schedule::command('orders:cancel-unpaid-orders')->dailyAt('02:00');
