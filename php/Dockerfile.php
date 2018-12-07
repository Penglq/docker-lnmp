FROM php:7.0-fpm-jessie

ENV TZ=Asia/Shanghai

COPY sources.list /etc/apt/sources.list
COPY ./redis-3.1.5.tgz /tmp/
COPY ./amqp-1.9.3.tgz /tmp/
COPY ./memcached-3.0.3.tgz /tmp/
COPY ./mongodb-1.3.0.tgz /tmp/
COPY ./stomp-2.0.1.tgz /tmp/
COPY ./xdebug-2.6.1.tgz /tmp/
COPY ./solr-2.4.0.tgz /tmp/
RUN set -xe \
    && echo "构建依赖" \
    && buildDeps=" \
        build-essential \
        dh-php5 \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        gcc \
        make \
        pkg-config \
        libssl-dev \
    " \
    && echo "运行依赖" \
    && runtimeDeps=" \
        git \
        libfreetype6 \
        libjpeg62-turbo \
        libmcrypt4 \
        libpng12-0 \
        libsasl2-dev \
        libmemcached-dev \
        libpq-dev \
        zlib1g-dev \
        curl \
        libcurl4-gnutls-dev \
        libssl-dev \
        pkg-config \
        librabbitmq-dev \
        openssl \
        wget \
        libpng12-dev \
        libxml2-dev \
        re2c \
    " \
    && echo "安装 php 以及编译构建组件所需包" \
    && DEBIAN_FRONTEND=noninteractive \
    && apt-get update \
    && apt-get install -y ${runtimeDeps} ${buildDeps} --no-install-recommends \
    && echo "编译安装 php 组件" \
    && docker-php-ext-install iconv mcrypt mysqli pdo pdo_mysql zip soap \
    && cd /tmp \
    && ln -s /usr/lib64/libssl.so /usr/lib/libssl.so \
    && ln -s /usr/lib64/libcrypto.so /usr/lib/libcrypto.so \
    && pecl install redis-3.1.5.tgz \
    && pecl install mongodb-1.3.0.tgz \
    && pecl install memcached-3.0.3.tgz \
    && pecl install xdebug-2.6.1.tgz \
    && pecl install solr-2.4.0.tgz \
    && pecl install amqp-1.9.3.tgz \
    && tar -xvf stomp-2.0.1.tgz \
    && cd stomp-2.0.1 \
    && phpize && ./configure --with-php-config=php-config && make && make install \
    && docker-php-ext-enable mongodb redis memcached xdebug solr amqp stomp \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    # cphalcon
    && git clone -b 3.2.x --depth=1 https://github.com/phalcon/cphalcon.git ~/cphalcon \
    && cd ~/cphalcon/build \
    && ./install \
    && docker-php-ext-enable phalcon \

    && echo "清理" \
    && rm -rf redis-3.1.5.tgz \ && rm -rf amqp-1.9.3.tgz \ && rm -rf memcached-3.0.3.tgz \ && rm -rf mongodb-1.3.0.tgz \
    && rm -rf stomp-2.0.1.tgz \ && rm -rf xdebug-2.6.1.tgz \ && rm -rf solr-2.4.0.tgz \ && rm -rf stomp-2.0.1 \
    && apt-get purge -y --auto-remove \
        -o APT::AutoRemove::RecommendsImportant=false \
        -o APT::AutoRemove::SuggestsImportant=false \
        $buildDeps \
    #&& apt-get remove wget make \
    && rm -rf /var/cache/apt/* \
    && rm -rf /var/lib/apt/lists/* \
    && cd / \
    && rm -rf ~/cphalcon

COPY ./php.ini /usr/local/etc/php/
COPY ./php.conf /usr/local/etc/php/conf.d
COPY ./www.conf /usr/local/etc/php-fpm.d