#!/bin/bash

# ============================================
# POS System - Quick Cache Optimization
# ============================================
# Run this script to rebuild all caches
# without running migrations or maintenance mode
# Usage: chmod +x cache_optimize.sh && ./cache_optimize.sh
# ============================================

set -e

echo "============================================"
echo "  POS System Cache Optimization"
echo "============================================"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP not found. Please ensure PHP is installed."
    exit 1
fi

# Clear all caches first
echo "[1/4] Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration
echo "[2/4] Caching configuration..."
php artisan config:cache

# Cache routes
echo "[3/4] Caching routes..."
php artisan route:cache

# Cache views
echo "[4/4] Caching views..."
php artisan view:cache

echo ""
echo "============================================"
echo "  Cache optimization completed!"
echo "============================================"
echo ""
