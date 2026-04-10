<?php

namespace App\Providers;

use App\Contracts\ISeedDataService;
use App\Services\SeedDataService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind ISeedDataService interface to SeedDataService implementation
        $this->app->bind(ISeedDataService::class, SeedDataService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
