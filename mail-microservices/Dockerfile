FROM php:8.2-cli

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y unzip libzip-dev && \
    docker-php-ext-install zip pdo pdo_mysql

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
