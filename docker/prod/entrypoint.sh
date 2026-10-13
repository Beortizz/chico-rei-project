#!/bin/sh
set -e

if [ "$1" = "frankenphp" ]; then
    php artisan storage:link --force

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force
    fi

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

exec "$@"
