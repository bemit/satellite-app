FROM php:7.4.14-apache

RUN apt-get update && apt-get install -yq --no-install-recommends \
      wget \
      curl \
      git \
      openssl \
      zip \
      rsync \
      libmcrypt-dev \
      lftp \
      unzip \
      locales \
      libmcrypt-dev \
      libxml2-dev \
      libzip-dev \
      zlib1g-dev libpng-dev libonig-dev \
      mycli \
      postgresql-client \
      # for web-push:
      libgmp-dev \
      # for pdo_pgsql:
      libpq-dev

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install -j$(nproc) mbstring mysqli \
    pdo pdo_mysql \
    opcache \
    gd zip tokenizer xml \
    pdo_pgsql pcntl \
    # for web-push:
    gmp \
    && docker-php-source delete

#INSTALL APCU
ARG APCU_VERSION=5.1.17
RUN pecl install apcu-${APCU_VERSION} && docker-php-ext-enable apcu \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/php.ini \
    #&& echo "extension=apcu.so" >> /usr/local/etc/php/php.ini \
    && echo "apc.enable=1" >> /usr/local/etc/php/php.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8 de_DE.UTF-8 es_ES.UTF-8 fr_FR.UTF-8 it_IT.UTF-8

RUN a2enmod rewrite deflate brotli

# Default to UTF-8 file.encoding
ENV LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    LANGUAGE=C.UTF-8

ENV APACHE_DOCUMENT_ROOT /var/www/html

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN mkdir /var/www/.composer && chown -R www-data /var/www/.composer

# docker tmpfs mount target for opcache in ram under unix
RUN mkdir /tmp/php-opcache && chown -R www-data /tmp/php-opcache

COPY docker-opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY docker-vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apachectl", "-D", "FOREGROUND"]
