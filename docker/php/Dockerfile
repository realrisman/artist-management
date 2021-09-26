ARG PHP_VERSION

FROM php:$PHP_VERSION-fpm

ARG user
ARG uid

RUN pecl install apcu

RUN apt-get update && \
  apt-get install -y \
  zlib1g-dev \
  libicu-dev \
  libpng-dev \
  libzip-dev \
  libxml2-dev \
  g++ \
  unzip \
  curl \
  npm

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure intl
RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu
RUN docker-php-ext-install intl
RUN docker-php-ext-install gd
RUN docker-php-ext-install xmlrpc
RUN docker-php-ext-install bcmath

WORKDIR /var/www/artist-management-app

RUN npm i npm@latest -g
RUN npm i @angular/cli -g
