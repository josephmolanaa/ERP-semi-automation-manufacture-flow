#!/usr/bin/env sh
set -e

APP_PORT="${PORT:-8080}"
APP_DIR="${APP_DIR:-/app}"
export CACHE_STORE="${CACHE_STORE:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export LOG_STACK="${LOG_STACK:-stderr}"

cd "${APP_DIR}"

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R ug+rwX storage bootstrap/cache || true

echo "PWD=$(pwd)"
echo "PORT=${APP_PORT}"
echo "PHP=$(php -v | head -n 1)"
echo "Router=$(test -f public/railway-router.php && echo found || echo missing)"
echo "Starting PHP server on 0.0.0.0:${APP_PORT}"
exec php \
    -d opcache.enable_cli=1 \
    -d opcache.memory_consumption=128 \
    -d opcache.max_accelerated_files=20000 \
    -d opcache.interned_strings_buffer=16 \
    -d opcache.validate_timestamps=0 \
    -d realpath_cache_size=4096K \
    -d realpath_cache_ttl=600 \
    -S "0.0.0.0:${APP_PORT}" \
    -t public \
    public/railway-router.php
