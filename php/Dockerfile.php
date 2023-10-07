FROM php:7.2-fpm

ENV TZ=Asia/Shanghai

#COPY sources.list /etc/apt/sources.list
RUN set -xe \
    && apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        wget \
    && wget http://pecl.php.net/get/yaf-2.3.3.tgz \
    && pecl install yaf-3.0.8 && docker-php-ext-enable yaf \
#    && pecl install mcrypt-1.0.5 \
#    && docker-php-ext-enable mcrypt \
#    && docker-php-ext-configure mcrypt \
    && docker-php-ext-install iconv mysqli pdo pdo_mysql \
    && pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis \
    && cd /

COPY ./php.ini /usr/local/etc/php/
COPY ./php-fpm.conf /usr/local/etc/php/
COPY ./php.conf /usr/local/etc/php/conf.d
COPY ./www.conf /usr/local/etc/php-fpm.d
