# syntax=docker/dockerfile:1.7

FROM dunglas/frankenphp:php8.3-bookworm AS php-base

RUN install-php-extensions \
    opcache \
    pdo_mysql

WORKDIR /var/www/html

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN install-php-extensions zip \
    && composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

FROM vendor AS frontend

COPY --from=node:22-bookworm-slim /usr/local /usr/local

RUN npm ci && npm run build

FROM php-base AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    XDG_CONFIG_HOME=/tmp/caddy/config \
    XDG_DATA_HOME=/tmp/caddy/data

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build
COPY docker/Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p \
        bootstrap/cache \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        /tmp/caddy/config \
        /tmp/caddy/data \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache /tmp/caddy \
    && chmod -R u=rwX,g=rwX,o=rX storage bootstrap/cache

USER www-data

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:80/up') === false ? 1 : 0);"
