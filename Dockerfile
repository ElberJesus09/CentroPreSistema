FROM node:22-bookworm AS assets

WORKDIR /app

COPY package*.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm ci && npm run build

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi

FROM php:8.3-fpm-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        gettext-base \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        nginx \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
COPY docker/nginx.conf /etc/nginx/templates/default.conf.template
COPY docker/start.sh /usr/local/bin/start.sh

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/start.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV PORT=10000

EXPOSE 10000

CMD ["/usr/local/bin/start.sh"]
