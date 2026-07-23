FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install redis pcov && docker-php-ext-enable redis pcov \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-progress --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts

RUN groupmod -o -g ${GID} www-data \
 && usermod  -o -u ${UID} -g ${GID} www-data \
 && chown -R www-data:www-data /var/www

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
