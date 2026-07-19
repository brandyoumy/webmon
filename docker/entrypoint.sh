#!/usr/bin/env sh
set -e

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is missing. Generate one with: php artisan key:generate --show"
    exit 1
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public

php artisan package:discover --ansi
php artisan optimize:clear

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_OPTIMIZE:-true}" != "false" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
