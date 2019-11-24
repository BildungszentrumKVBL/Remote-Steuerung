FROM php:7.3-apache
LABEL maintainer="dominik.mueller@jkweb.ch"
WORKDIR /var/www/app

# Copy config files and scripts.
COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/php.ini

# install software
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git zip unzip libicu-dev zlib1g-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install iconv posix intl pdo pdo_mysql mbstring zip exif gd opcache bcmath

# install composer
RUN curl --silent --show-error https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

EXPOSE 80
