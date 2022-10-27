FROM php:8.1-rc-fpm-alpine AS php_fpm
#
## Stage: All basic dependencies and settings used accross workers and apis (fpm)
##

RUN apk add \
        libxml2-dev \
        # for gd:
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        # for pdo_pgsql:
        postgresql-dev

# `mbstring` is not needed, already configured in base image for php-alpine
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install opcache \
        gd xml \
        pdo pdo_pgsql pcntl \
        bcmath \
        intl \
    && docker-php-source delete

RUN mkdir /tmp/php-opcache && chown -R www-data /tmp/php-opcache

# todo: add supervisor only to worker, but currently this then gets reinstalled every build
RUN apk add supervisor

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN sed -i '/^;pm.status_path/s/^;//' /usr/local/etc/php-fpm.d/www.conf
#RUN sed -i '/^;clear_env/s/^;//' /usr/local/etc/php-fpm.d/www.conf

RUN sed -i -e 's/expose_php = On/expose_php = Off/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^max_execution_time .*$/max_execution_time = 60/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^max_input_time .*$/max_input_time = 60/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^memory_limit .*$/memory_limit = 256M/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^upload_max_filesize .*$/upload_max_filesize = 256M/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^max_file_uploads .*$/max_file_uploads = 20/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/^variables_order .*$/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"

COPY ./_docker/docker-opcache.ini /usr/local/etc/php/conf.d/custom-opcache.ini

#
## Stage: composer dependencies
##
FROM composer as php_vendor

#
## Stage: Make Bundled App
##
FROM php_fpm as php_build

#
## Stage: Crontab Worker Image - Final
##
FROM php_build as php_worker

RUN rm /usr/local/etc/php-fpm.conf && rm /usr/local/etc/php-fpm.conf.default \
    && rm -rf /usr/local/etc/php-fpm.d

COPY ./_docker/docker_crontab_worker docker_crontab_worker
RUN /usr/bin/crontab docker_crontab_worker
COPY ./_docker/docker_supervisor.conf /etc/supervisor.conf

WORKDIR /var/www/html
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor.conf"]

#
## Stage: PHP-FPM Image - Final
##
FROM php_build as php_api


