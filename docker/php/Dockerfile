FROM php:8.3-fpm-alpine

ENV SRC_MODULES "/usr/lib/php83/modules"
ENV DST_MODULES "/usr/local/lib/php/extensions/no-debug-non-zts-20230831"
ENV SRC_CONFIG "/etc/php83/conf.d"
ENV DST_CONFIG "/usr/local/etc/php/conf.d"

RUN apk update

RUN apk add build-base php83-dev

RUN pecl install apcu && \
    echo 'extension=apcu.so' > $DST_CONFIG/00_apcu.ini

RUN docker-php-ext-configure opcache && \
    docker-php-ext-install opcache

RUN apk add php83-pdo_pgsql && \
    ln -s $SRC_MODULES/pdo_pgsql.so $DST_MODULES/pdo_pgsql.so && \
    ln -s $SRC_CONFIG/01_pdo_pgsql.ini $DST_CONFIG/01_pdo_pgsql.ini

RUN apk add php83-pecl-xdebug && \
    ln -s $SRC_MODULES/xdebug.so $DST_MODULES/xdebug.so && \
    ln -s $SRC_CONFIG/xdebug.ini $DST_CONFIG/50_xdebug.ini

RUN apk del build-base php83-dev

COPY install-composer.sh /tmp
RUN /tmp/install-composer.sh
