FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm

RUN docker-php-ext-install pdo pdo_mysql zip

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copiar primero composer files (cache optimizado)
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

# copiar resto del proyecto
COPY . .

RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD php artisan migrate --force && apache2-foreground