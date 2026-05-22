#!/usr/bin/env sh
set -e

export CACHE_STORE="${CACHE_STORE:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export LOG_STACK="${LOG_STACK:-stderr}"

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

php artisan optimize:clear || true
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
