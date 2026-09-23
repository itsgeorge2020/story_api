FROM php:8.4-fpm

# Системные зависимости и утилиты
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install \
    pdo_mysql \
    bcmath \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Пользователь с UID/GID хоста (1000 — твой юзер)
RUN useradd -m -u 1000 -s /bin/bash george

WORKDIR /var/www/html

USER george
