# Исходный образ
FROM php:7.2-fpm

# RUN выполняет идущую за ней команду в контексте нашего образа.
# В данном случае мы установим некоторые зависимости и модули PHP.
# Для установки модулей используем команду docker-php-ext-install.
# На каждый RUN создается новый слой в образе, поэтому рекомендуется объединять команды.

RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libicu-dev \
    libxml2-dev \
    libpq-dev \
    && pecl install mcrypt-1.0.1 \
    && docker-php-ext-install -j$(nproc) intl bcmath soap pgsql pdo_pgsql zip \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable mcrypt

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Добавим свой php.ini, можем в нем определять свои значения конфига
ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

ENV TZ=Europe/Moscow

# Указываем рабочую директорию для PHP
WORKDIR /app

CMD ["php-fpm"]
