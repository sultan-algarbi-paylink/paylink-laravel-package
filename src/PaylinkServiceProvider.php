<?php

namespace Paylink;

use Illuminate\Support\ServiceProvider;

class PaylinkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration files
        $this->publishes([
            __DIR__ . '/../config/paylink.php' => config_path('paylink.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration files
        $this->mergeConfigFrom(__DIR__ . '/../config/paylink.php', 'paylink');
    }
}
