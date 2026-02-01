<?php

use App\Jobs\AutoCheckoutJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic checkout job daily at 23:59
Schedule::job(new AutoCheckoutJob())
    ->dailyAt('23:59')
    ->name('auto-checkout-users')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('AutoCheckoutJob scheduled execution completed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('AutoCheckoutJob scheduled execution failed');
    });
