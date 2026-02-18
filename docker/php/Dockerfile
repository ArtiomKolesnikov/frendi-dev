FROM php:8.2-fpm-bullseye

# System deps
RUN apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git curl zip unzip \
    libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    libicu-dev libxml2-dev libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) gd intl zip xml pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# Opcache for performance
RUN docker-php-ext-install opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/Frendi

# PHP config tweaks
RUN { \
  echo "memory_limit=${PHP_MEMORY_LIMIT:-512M}"; \
  echo "upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE:-20M}"; \
  echo "post_max_size=${PHP_POST_MAX_SIZE:-24M}"; \
  echo "expose_php=0"; \
} > /usr/local/etc/php/conf.d/99-overrides.ini

CMD ["php-fpm"]


