<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use LemonSqueezy\Laravel\Events\OrderCreated;
use App\Listeners\HandleLemonSqueezyOrderCreated;

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
        // Production ortamında tüm URL'leri HTTPS olarak zorla
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Lemon Squeezy event listener
        Event::listen(
            OrderCreated::class,
            HandleLemonSqueezyOrderCreated::class
        );
    }
}
