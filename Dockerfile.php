FROM php:7.1-fpm

ENV TZ=Asia/Shanghai

COPY sources.list /etc/apt/sources.list

RUN set -xe \
    && echo "构建依赖" \
    && buildDeps=" \
        build-essential \
        php5-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev \
        re2c \
        libpcre3-dev \
    " \
    && echo "运行依赖" \
    && runtimeDeps=" \
        git \
        libfreetype6 \
        libjpeg62-turbo \
        libmcrypt4 \
        libpng12-0 \
        libsasl2-dev \
        libssl-dev \
        libmemcached-dev \
        libpq-dev \
        zlib1g-dev \
        gcc \
        make \
    " \
    && echo "安装 php 以及编译构建组件所需包" \
    && DEBIAN_FRONTEND=noninteractive \
    && apt-get update \
    && apt-get install -y ${runtimeDeps} ${buildDeps} --no-install-recommends \
    && echo "编译安装 php 组件" \
    && docker-php-ext-install iconv mcrypt mysqli pdo pdo_mysql zip soap \
    && pecl install redis-3.1.2 \
    && pecl install mongodb-1.3.0 \
    && pecl install memcached-3.0.3 \
    && docker-php-ext-enable mongodb memcached redis \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && git clone -b 3.2.x --depth=1 https://github.com/phalcon/cphalcon.git ~/cphalcon \
    && cd ~/cphalcon/build \
    && ./install \
    && docker-php-ext-enable phalcon \
    && echo "清理" \
    && apt-get purge -y --auto-remove \
        -o APT::AutoRemove::RecommendsImportant=false \
        -o APT::AutoRemove::SuggestsImportant=false \
        $buildDeps \
    && rm -rf /var/cache/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && cd / \
    && rm -rf ~/cphalcon

COPY ./php.conf /usr/local/etc/php/conf.d/php.conf
COPY ./phplog.conf /usr/local/etc/php-fpm.d/phplog.conf
