<?php

namespace Nikba\Paynet\Providers;

use Illuminate\Support\ServiceProvider;
use Nikba\Paynet\Services\PaynetService;

class PaynetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PaynetService::class, function ($app) {
            return new PaynetService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/paynet.php' => config_path('paynet.php'),
        ], 'config');
    }
}
