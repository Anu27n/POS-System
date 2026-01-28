<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Pre-Bootstrap Environment Check
|--------------------------------------------------------------------------
|
| Before Laravel loads, we ensure .env file exists with a valid APP_KEY.
| This prevents crashes on fresh installations where .env is missing.
|
*/

$envPath = __DIR__ . '/../.env';
$envExamplePath = __DIR__ . '/../.env.example';

if (!file_exists($envPath)) {
    // Create .env from .env.example
    if (file_exists($envExamplePath)) {
        copy($envExamplePath, $envPath);
    } else {
        // Create minimal .env file
        $minimalEnv = "APP_NAME=\"POS System\"\n";
        $minimalEnv .= "APP_ENV=production\n";
        $minimalEnv .= "APP_KEY=\n";
        $minimalEnv .= "APP_DEBUG=true\n";
        $minimalEnv .= "APP_TIMEZONE=Asia/Kolkata\n";
        $minimalEnv .= "APP_URL=http://localhost\n\n";
        $minimalEnv .= "DB_CONNECTION=mysql\n";
        $minimalEnv .= "DB_HOST=127.0.0.1\n";
        $minimalEnv .= "DB_PORT=3306\n";
        $minimalEnv .= "DB_DATABASE=pos_system\n";
        $minimalEnv .= "DB_USERNAME=root\n";
        $minimalEnv .= "DB_PASSWORD=\n\n";
        $minimalEnv .= "SESSION_DRIVER=file\n";
        $minimalEnv .= "CACHE_STORE=file\n";
        $minimalEnv .= "QUEUE_CONNECTION=sync\n";
        
        file_put_contents($envPath, $minimalEnv);
    }
}

// Check if APP_KEY is missing or empty and generate one
$envContent = file_get_contents($envPath);
if (preg_match('/^APP_KEY=\s*$/m', $envContent) || preg_match('/^APP_KEY=$/m', $envContent)) {
    // Generate a random APP_KEY
    $key = 'base64:' . base64_encode(random_bytes(32));
    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
    file_put_contents($envPath, $envContent);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
