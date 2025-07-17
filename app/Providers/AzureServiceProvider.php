<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AzureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Socialite::extend('azure', function ($app) {
            $config = $app['config']['services.azure'];
            
            return Socialite::buildProvider(
                \App\Services\AzureProvider::class, $config
            );
        });
    }
}
