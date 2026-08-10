<?php

namespace App\Providers;

use App\Models\SalesOrder;
use App\Observers\SalesOrderObserver;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\SteadfastClient;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One client per request, built from config, so the courier driver can be
        // swapped (log vs api) or faked in tests without touching call sites.
        $this->app->singleton(SteadfastClient::class, function ($app) {
            return new SteadfastClient($app['config']->get('services.steadfast', []));
        });

        $this->app->singleton(CourierDispatcher::class, function ($app) {
            return new CourierDispatcher($app->make(SteadfastClient::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        SalesOrder::observe(SalesOrderObserver::class);
    }
}
