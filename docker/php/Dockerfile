FROM php:8.4-apache

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

# Apache moduly
RUN a2enmod rewrite headers

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache vhost — document root na public/
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Pracovní adresář
WORKDIR /var/www/html

# Oprávnění pro WORKDIR (volumes se připojí až za běhu — viz docker-compose entrypoint)
RUN chown www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
