FROM php:8.0-fpm-alpine

ENV SRC_MODULES "/usr/lib/php8/modules"
ENV DST_MODULES "/usr/local/lib/php/extensions/no-debug-non-zts-20200930"
ENV SRC_CONFIG "/etc/php8/conf.d"
ENV DST_CONFIG "/usr/local/etc/php/conf.d"

RUN apk update

RUN apk add build-base php8-dev

RUN pecl install apcu && \
    echo 'extension=apcu.so' > $DST_CONFIG/00_apcu.ini

RUN docker-php-ext-configure opcache && \
    docker-php-ext-install opcache

RUN apk add php8-pdo_pgsql && \
    ln -s $SRC_MODULES/pdo_pgsql.so $DST_MODULES/pdo_pgsql.so && \
    ln -s $SRC_CONFIG/01_pdo_pgsql.ini $DST_CONFIG/01_pdo_pgsql.ini

RUN apk add php8-pecl-xdebug && \
    ln -s $SRC_MODULES/xdebug.so $DST_MODULES/xdebug.so

RUN apk del build-base php8-dev

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"
