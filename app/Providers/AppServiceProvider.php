<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\SystemSetting;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
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
     * Check if the application has been installed.
     * This uses a file-based check to avoid database access.
     */
    protected function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // CRITICAL: Use file sessions during installation to avoid database dependency
        // This must run BEFORE any session access
        if (!$this->isInstalled() || str_starts_with(request()->path(), 'install')) {
            config(['session.driver' => 'file']);
        }

        // Force HTTPS in production (when behind proxy or load balancer)
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
        
        // Auto-detect APP_URL from request if not properly set
        if (request()->getHost() && request()->getHost() !== 'localhost') {
            $scheme = request()->secure() ? 'https' : 'http';
            $host = request()->getHost();
            $port = request()->getPort();
            
            // Build the URL (exclude default ports)
            $url = $scheme . '://' . $host;
            if ($port && !in_array($port, [80, 443])) {
                $url .= ':' . $port;
            }
            
            // Set the root URL for assets and routes
            URL::forceRootUrl($url);
            config(['app.url' => $url]);
        }

        // Register Order observer
        Order::observe(OrderObserver::class);

        // CRITICAL: Always share default settings first
        // This ensures views can render even during errors
        View::share('appSettings', $this->getDefaultSettings());

        // Only try to get real settings if installed
        if ($this->isInstalled()) {
            // Override with real settings from database
            View::composer('*', function ($view) {
                try {
                    // Double-check installation status inside the closure
                    if (!$this->isInstalled()) {
                        return;
                    }
                    
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
                } catch (\Exception $e) {
                    // Silently fail - defaults already shared
                }
            });
        }
    }

    /**
     * Get default app settings when database is not available.
     */
    protected function getDefaultSettings(): array
    {
        return [
            'app_name' => 'POS System',
            'app_phone' => '',
            'app_email' => '',
            'app_address' => '',
            'app_logo' => '',
            'app_favicon' => '',
            'app_tagline' => '',
            'footer_text' => '',
        ];
    }
}
