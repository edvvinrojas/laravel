FROM php:8.4-apache  

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm

RUN docker-php-ext-install pdo pdo_mysql zip

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD sed -i "s/80/${PORT:-10000}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && \
    php artisan migrate --force ; \
    apache2-foreground