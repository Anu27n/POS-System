<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * Check if the application is installed. If not, redirect to the installer.
     * This middleware should be applied to all routes except the installer routes.
     * 
     * IMPORTANT: This middleware uses file-based checks only - NO database access!
     * This allows the installer to run even when the database is not configured.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for installer routes and static assets
        if ($request->is('install', 'install/*', 'build/*', 'storage/*', 'favicon.ico')) {
            return $next($request);
        }

        // Check if application is installed using file-based flag (no DB access)
        if (!$this->isInstalled()) {
            // Use url() instead of route() to avoid potential issues
            return redirect('/install');
        }

        return $next($request);
    }

    /**
     * Check if the application has been installed.
     * Uses a file-based flag to avoid any database connection.
     */
    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }
}
