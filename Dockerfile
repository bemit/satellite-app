
FROM php:7.3-apache

RUN apt-get update && apt-get install -yq --no-install-recommends \
      # Basics
      wget \
      curl \
      # Text Utils for console debug
      vim \
      nano \
      # Tools
      git \
      openssl \
      # enable ping
      iputils-ping \
      # enable netstat
      net-tools \
      # Install ppa Utils / https
      apt-utils \
      build-essential \
      software-properties-common \
      apt-transport-https \
      ca-certificates \
      # Others
      gnupg2 \
      ssh-client \
      ssh \
      rsync \
      lftp \
      unzip \
      zip \
      locales \
      ghostscript

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install -j5 mbstring mysqli pdo pdo_mysql opcache

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8 de_DE.UTF-8 es_ES.UTF-8 fr_FR.UTF-8 it_IT.UTF-8

RUN a2enmod rewrite deflate brotli

# Default to UTF-8 file.encoding
ENV LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    LANGUAGE=C.UTF-8

ENV APACHE_DOCUMENT_ROOT /var/www/html/web

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker-opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY docker-vhost.conf /etc/apache2/sites-available/000-default.conf
