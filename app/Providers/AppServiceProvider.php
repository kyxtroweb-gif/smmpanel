<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share site settings globally
        try {
            $settings = Setting::pluck('value', 'key')->toArray();
            View::share('siteSettings', $settings);
        } catch (\Exception $e) {
            View::share('siteSettings', []);
        }
    }
}
