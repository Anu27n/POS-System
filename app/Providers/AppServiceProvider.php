<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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
        // Share app settings with all views
        View::composer('*', function ($view) {
            // Only load if the table exists (for installation process)
            if (Schema::hasTable('system_settings')) {
                $appSettings = [
                    'app_name' => SystemSetting::get('app_name', 'POS System'),
                    'app_phone' => SystemSetting::get('app_phone', ''),
                    'app_email' => SystemSetting::get('app_email', ''),
                    'app_address' => SystemSetting::get('app_address', ''),
                    'app_logo' => SystemSetting::get('app_logo', ''),
                    'app_favicon' => SystemSetting::get('app_favicon', ''),
                    'app_tagline' => SystemSetting::get('app_tagline', ''),
                    'footer_text' => SystemSetting::get('footer_text', ''),
                ];
                $view->with('appSettings', $appSettings);
            }
        });
    }
}
