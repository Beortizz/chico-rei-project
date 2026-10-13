FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# O servidor embutido do PHP roda em modo CLI, onde o OPcache vem desligado.
RUN printf 'opcache.enable_cli=1\nopcache.validate_timestamps=1\nopcache.revalidate_freq=2\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\n' \
    > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Dependências antes do código para aproveitar o cache de camadas.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
