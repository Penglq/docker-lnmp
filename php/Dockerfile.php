FROM php:7.0-fpm-jessie

ENV TZ=Asia/Shanghai

COPY sources.list /etc/apt/sources.list

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
    && pecl install redis-3.1.2 \
    #&& pecl install mongodb-1.3.0 \
    && pecl install memcached-3.0.3 \
    && pecl install xdebug-2.5.0 \
    && pecl install solr \
    && docker-php-ext-enable redis memcached xdebug solr \
    && docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && git clone -b 3.2.x --depth=1 https://github.com/phalcon/cphalcon.git ~/cphalcon \
    && cd ~/cphalcon/build \
    && ./install \
    && docker-php-ext-enable phalcon \
    # amqp
    && cd ~ && wget https://pecl.php.net/get/amqp-1.9.3.tgz && tar -xvf amqp-1.9.3.tgz \
    && cd amqp-1.9.3 \
    && phpize && ./configure --with-php-config=php-config && make && make install \
    && docker-php-ext-enable amqp && cd - && rm -rf amqp-1.9.3.tgz amqp-1.9.3 \
    # stomp
    && cd ~ && wget https://pecl.php.net/get/stomp-2.0.1.tgz && tar -xvf stomp-2.0.1.tgz \
    && cd stomp-2.0.1 \
    && phpize && ./configure --with-php-config=php-config && make && make install \
    && docker-php-ext-enable stomp && cd - && rm -rf stomp-2.0.1.tgz stomp-2.0.1 \
    && echo "清理" \
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
