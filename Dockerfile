FROM php:8.2.4-apache
# install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# install needed software
RUN DEBIAN_FRONTEND=noninteractive apt-get -y update && \
    apt-get -y install libx11-dev unzip npm

# allow mod rewrite for Apache
RUN a2enmod rewrite

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && rm composer-setup.php \
    && mv composer.phar /usr/local/bin/composer

COPY data/docker-startup.sh /usr/local/bin/docker-startup.sh
RUN chmod +x /usr/local/bin/docker-startup.sh
CMD ["docker-startup.sh"]
