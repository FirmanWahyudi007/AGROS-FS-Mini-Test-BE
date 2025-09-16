# Dockerfile - Laravel PHP-FPM (tanpa web server)
FROM php:8.2-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash git curl unzip icu-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    libzip-dev autoconf g++ make netcat-openbsd

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) pdo_mysql bcmath gd exif intl zip opcache

# (opsional) Redis
RUN pecl install redis && docker-php-ext-enable redis

# OPCache (disarankan)
RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.enable_cli=1'; \
  echo 'opcache.jit_buffer_size=64M'; \
  echo 'opcache.jit=1235'; \
  echo 'opcache.memory_consumption=192'; \
  echo 'opcache.max_accelerated_files=100000'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Entrypoint auto-bootstrap (tanpa .env)
COPY docker/entrypoint.sh /usr/local/bin/laravel-entrypoint
RUN chmod +x /usr/local/bin/laravel-entrypoint

WORKDIR /site/www/agros
EXPOSE 9000

ENTRYPOINT ["laravel-entrypoint"]
CMD ["php-fpm"]
