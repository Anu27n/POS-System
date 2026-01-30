@echo off
REM ============================================
REM POS System - Quick Cache Optimization
REM ============================================
REM Run this script to rebuild all caches
REM without running migrations or maintenance mode
REM ============================================

echo ============================================
echo   POS System Cache Optimization
echo ============================================
echo.

REM Check if PHP is available
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP not found in PATH. Please ensure PHP is installed and in PATH.
    exit /b 1
)

REM Clear all caches first
echo [1/4] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

REM Cache configuration
echo [2/4] Caching configuration...
php artisan config:cache

REM Cache routes
echo [3/4] Caching routes...
php artisan route:cache

REM Cache views
echo [4/4] Caching views...
php artisan view:cache

echo.
echo ============================================
echo   Cache optimization completed!
echo ============================================
echo.

pause
