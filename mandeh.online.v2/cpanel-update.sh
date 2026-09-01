#!/bin/bash

# Mandeh.Online v2 - cPanel Update & Migration Script
# Run this script inside the application directory on cPanel Terminal

set -e

echo "=================================================="
echo "  Mandeh.Online v2: Starting cPanel Update"
echo "=================================================="

# 1. Ensure storage folders have write permissions
echo "[1/3] Setting directory permissions for storage & bootstrap/cache..."
find storage bootstrap/cache -type d -exec chmod 755 {} \;
find storage bootstrap/cache -type f -exec chmod 644 {} \;

# 2. Clear cached config and run database migrations
echo "[2/3] Clearing config cache and running database migrations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

# 2.5. Re-create storage symlink to prevent broken paths / 404s
echo "Re-creating storage symlink..."
rm -rf public/storage
php artisan storage:link || true

# 3. Clear and cache Laravel configuration for production performance
echo "[3/3] Caching configurations, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "=================================================="
echo "  Update Completed Successfully!"
echo "=================================================="
