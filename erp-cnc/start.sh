#!/usr/bin/env sh
set -e

APP_PORT="${PORT:-8080}"
export CACHE_STORE="${CACHE_STORE:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"

echo "Starting Laravel on 0.0.0.0:${APP_PORT}"
exec php \
    -d opcache.enable_cli=1 \
    -d opcache.memory_consumption=128 \
    -d opcache.max_accelerated_files=20000 \
    -d opcache.validate_timestamps=0 \
    artisan serve --host=0.0.0.0 --port="${APP_PORT}"
