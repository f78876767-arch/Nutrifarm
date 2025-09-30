<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Shipping\JntService::class, function ($app) {
            return new \App\Services\Shipping\JntService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Order Observer if Order model exists
        if (class_exists(\App\Models\Order::class)) {
            \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        }
    }
}
