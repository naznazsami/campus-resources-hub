<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
        // Outside production, throw an error whenever a relationship is lazy
        // loaded, so N+1 query problems are caught during development.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
