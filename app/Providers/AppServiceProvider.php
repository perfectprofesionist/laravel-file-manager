<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Content;
use App\Models\AccessControl;
use App\Observers\LogObserver;

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
        Content::observe(LogObserver::class);
        AccessControl::observe(LogObserver::class);
    }
}
