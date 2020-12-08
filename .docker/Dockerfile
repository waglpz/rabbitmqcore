FROM php:7.4-buster

ARG APPUID
ARG APPUGID

# core Debian packages which includes of create PHP environment
RUN apt-get update;                 \
    apt-get install                 \
            -y                      \
            --no-install-recommends \
        libonig-dev                 \    
        libzip-dev                  \
        locales                     \
        unzip                       \
        wget                        \
        zlib1g-dev                  \
    ;                               \
    rm -rf /var/lib/apt/lists/*;    \
    rm -rf /var/cache/apt;          \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false

## core PHP extensions
RUN docker-php-ext-enable opcache;       \
    docker-php-ext-install                       \
        mbstring                        \
        sockets                                             

RUN groupadd -g ${APPUGID} nutzer;    \
    useradd -r -l -u ${APPUID} -m -g nutzer nutzer

RUN pecl install xdebug-2.9.0

# composer
ENV COMPOSER_HOME /home/nutzer

RUN curl -sS https://getcomposer.org/installer |                \
       php -- --install-dir=/usr/local/bin --filename=composer  \
    && composer global require maglnet/composer-require-checker \
    && chown -R nutzer:nutzer $COMPOSER_HOME

WORKDIR /app

