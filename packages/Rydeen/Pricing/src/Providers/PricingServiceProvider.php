<?php

namespace Rydeen\Pricing\Providers;

use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'rydeen-pricing');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'rydeen-pricing');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
    }
}
