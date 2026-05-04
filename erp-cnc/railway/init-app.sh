#!/usr/bin/env sh
set -e

php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan storage:link || true
php artisan filament:assets
php artisan config:cache
php artisan view:cache
