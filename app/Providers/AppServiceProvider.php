<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Models\Website;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        // Implicit route model binding scoping for websites
        Route::bind('website', function ($value) {
            return auth()->check() 
                ? auth()->user()->websites()->findOrFail($value)
                : Website::findOrFail($value);
        });

        // Share site settings with all views
        View::composer('*', function ($view) {
            $view->with('siteSettings', SiteSetting::getInstance());
        });
    }
}
