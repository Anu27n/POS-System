<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\DebugSession;
use App\Http\Middleware\CheckInstallation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*', headers: 
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );
        $middleware->encryptCookies(except: []);
        $middleware->web(append: [DebugSession::class, CheckInstallation::class]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle ALL database connection errors - redirect to installer if not installed
        $exceptions->render(function (\Throwable $e, $request) {
            // Check if this is a database-related error
            $isDatabaseError = $e instanceof QueryException
                || $e instanceof \PDOException
                || $e instanceof \Illuminate\Database\SQLiteDatabaseDoesNotExistException
                || str_contains(get_class($e), 'Database')
                || str_contains($e->getMessage(), 'database')
                || str_contains($e->getMessage(), 'SQLSTATE')
                || str_contains($e->getMessage(), 'sqlite');
            
            if ($isDatabaseError && !File::exists(storage_path('installed'))) {
                // Skip redirect if already on install route
                if (!$request->is('install', 'install/*')) {
                    return redirect('/install');
                }
            }
            
            return null; // Let Laravel handle it normally
        });
    })->create();
