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
    -d opcache.interned_strings_buffer=16 \
    -d opcache.validate_timestamps=0 \
    -d realpath_cache_size=4096K \
    -d realpath_cache_ttl=600 \
    -S "0.0.0.0:${APP_PORT}" \
    -t public \
    public/railway-router.php
