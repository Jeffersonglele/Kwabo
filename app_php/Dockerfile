FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y unzip libzip-dev && \
    docker-php-ext-install pdo pdo_mysql zip

# Activer le mod_rewrite
RUN a2enmod rewrite

# Copier les fichiers de l'app
COPY . /var/www/html/

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
