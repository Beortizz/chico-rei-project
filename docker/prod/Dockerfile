FROM node:24-alpine AS web

WORKDIR /web

COPY chico-rei-web/package.json chico-rei-web/package-lock.json ./
RUN npm ci

COPY chico-rei-web/ ./
ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL
RUN npm run build


FROM dunglas/frankenphp:1-php8.4 AS base

RUN install-php-extensions gd pdo_mysql zip opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/prod/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app


FROM base AS vendor

COPY chico-rei-api/composer.json chico-rei-api/composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY chico-rei-api/ ./
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts


FROM base AS app

COPY --from=vendor /app /app
COPY --from=web /web/dist /srv/web
COPY docker/prod/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && php artisan package:discover --ansi \
    && setcap CAP_NET_BIND_SERVICE=+ep /usr/local/bin/frankenphp \
    && chown -R www-data:www-data storage bootstrap/cache public /data/caddy /config/caddy

USER www-data

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
