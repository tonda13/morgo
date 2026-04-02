FROM php:8.2-fpm

# Systémové závislosti
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP rozšíření
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    gd \
    fileinfo \
    opcache \
    zip \
    exif

# Xdebug (pouze pro dev)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Pracovní adresář
WORKDIR /var/www/html

# Oprávnění
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
