#!/bin/bash

# ============================================
# POS System - Production Deployment Script
# ============================================
# Run this script after pulling new code changes
# Usage: chmod +x deploy.sh && ./deploy.sh
# ============================================

set -e

echo "============================================"
echo "  POS System Deployment Script"
echo "============================================"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP not found. Please ensure PHP is installed."
    exit 1
fi

# Put application in maintenance mode
echo "[1/7] Enabling maintenance mode..."
php artisan down --refresh=15

# Clear all caches first
echo "[2/7] Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run any pending migrations
echo "[3/7] Running database migrations..."
php artisan migrate --force

# Install/update composer dependencies (production mode)
echo "[4/7] Installing composer dependencies..."
if [ -f "composer.phar" ]; then
    php composer.phar install --no-dev --optimize-autoloader
else
    composer install --no-dev --optimize-autoloader
fi

# Cache configuration for optimal performance
echo "[5/7] Caching configuration..."
php artisan config:cache

# Cache routes for optimal performance
echo "[6/7] Caching routes..."
php artisan route:cache

# Cache views for optimal performance
echo "[7/7] Caching views..."
php artisan view:cache

# Bring application back online
echo ""
echo "[DONE] Bringing application online..."
php artisan up

echo ""
echo "============================================"
echo "  Deployment completed successfully!"
echo "============================================"
echo ""
