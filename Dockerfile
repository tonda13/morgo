FROM php:8.4-apache

# Systémové závislosti
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP rozšíření
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache mod_rewrite (potřebné pro Slim routing)
RUN a2enmod rewrite

# Apache konfigurace
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# PHP konfigurace
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Zkopírovat zdrojový kód
COPY . .

# Nainstalovat Composer závislosti
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Oprávnění
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/public

EXPOSE 80
