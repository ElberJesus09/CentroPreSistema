#!/usr/bin/env bash
set -e

if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
fi

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
if [ "${RESET_DATABASE_ON_DEPLOY:-false}" = "true" ]; then
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
    php artisan db:seed --force
fi

php-fpm -D
nginx -g 'daemon off;'
