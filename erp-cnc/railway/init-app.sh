#!/usr/bin/env sh
set -e

export CACHE_STORE="${CACHE_STORE:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"

php artisan optimize:clear || true
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
