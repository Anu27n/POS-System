@echo off
REM ============================================
REM POS System - Production Deployment Script
REM ============================================
REM Run this script after pulling new code changes
REM ============================================

echo ============================================
echo   POS System Deployment Script
echo ============================================
echo.

REM Check if PHP is available
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP not found in PATH. Please ensure PHP is installed and in PATH.
    exit /b 1
)

REM Put application in maintenance mode
echo [1/7] Enabling maintenance mode...
php artisan down --refresh=15

REM Clear all caches first
echo [2/7] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Run any pending migrations
echo [3/7] Running database migrations...
php artisan migrate --force

REM Install/update composer dependencies (production mode)
echo [4/7] Installing composer dependencies...
if exist "composer.phar" (
    php composer.phar install --no-dev --optimize-autoloader
) else (
    composer install --no-dev --optimize-autoloader
)

REM Cache configuration for optimal performance
echo [5/7] Caching configuration...
php artisan config:cache

REM Cache routes for optimal performance
echo [6/7] Caching routes...
php artisan route:cache

REM Cache views for optimal performance
echo [7/7] Caching views...
php artisan view:cache

REM Bring application back online
echo.
echo [DONE] Bringing application online...
php artisan up

echo.
echo ============================================
echo   Deployment completed successfully!
echo ============================================
echo.

pause
