#!/usr/bin/env sh
set -e

APP_PORT="${PORT:-8080}"

echo "Starting Laravel on 0.0.0.0:${APP_PORT}"
exec php artisan serve --host=0.0.0.0 --port="${APP_PORT}"
