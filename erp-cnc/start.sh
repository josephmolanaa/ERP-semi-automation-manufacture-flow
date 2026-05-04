#!/usr/bin/env sh
set -e

APP_PORT="${PORT:-8080}"

php artisan package:discover --ansi || true
php artisan filament:assets || true

echo "Starting Laravel on 0.0.0.0:${APP_PORT}"
exec php artisan serve --host=0.0.0.0 --port="${APP_PORT}"
