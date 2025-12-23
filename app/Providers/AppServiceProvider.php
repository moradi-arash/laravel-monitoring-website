<?php

namespace App\Providers;

use App\Models\SiteSettingSimple;
use App\Models\Website;
use App\Services\FaviconService;
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

        // Share site settings and favicon URLs with all views (with fallback)
        View::composer('*', function ($view) {
            try {
                $siteSettings = SiteSettingSimple::getInstance();
                $faviconService = new FaviconService();
                $faviconUrls = $faviconService->getFaviconUrls();
                
                $view->with('siteSettings', $siteSettings);
                $view->with('faviconUrls', $faviconUrls);
            } catch (\Exception $e) {
                // Fallback to null if there's any issue
                $view->with('siteSettings', null);
                $view->with('faviconUrls', [
                    'favicon' => asset('favicon.ico'),
                    'favicon_16' => null,
                    'favicon_32' => null,
                    'apple_touch' => null,
                    'android_192' => null,
                    'android_512' => null,
                ]);
            }
        });
    }
}
