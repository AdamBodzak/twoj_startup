FROM php:8.4-fpm-alpine

# Composer w obrazie (wygodne do 'composer update')
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Zależności systemowe
RUN apk add --no-cache \
    bash git unzip icu-dev libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev oniguruma-dev \
    freetype-dev autoconf g++ make linux-headers zlib-dev \
    # Zależności dla ImageMagick
    imagemagick-dev imagemagick ghostscript

# PHP Tidy (required by phpdocx HTML -> DOCX)
RUN apk add --no-cache tidyhtml tidyhtml-dev \
    && docker-php-ext-install tidy

# Instalacja rozszerzeń PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" \
    bcmath gd intl opcache pcntl pdo_mysql zip sockets \
 && pecl install imagick \
 && docker-php-ext-enable imagick

# Konfiguracja uprawnień dla ImageMagick
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/upload-limit.ini \
    && echo "post_max_size=101M" >> /usr/local/etc/php/conf.d/upload-limit.ini

# Instalacja rozszerzeń PHP + Xdebug
#RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
# && docker-php-ext-install -j"$(nproc)" \
#    bcmath gd intl opcache pcntl pdo_mysql zip sockets \
# && pecl install imagick xdebug \
# && docker-php-ext-enable imagick xdebug

# Konfiguracja Xdebug
#RUN echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/xdebug.ini \
# && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
# && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
# && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
# && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN #apk add --no-cache libreoffice

WORKDIR /application
