#!/bin/bash
set -e

echo "Configurando puerto Apache..."
sed -i "s/80/${PORT:-10000}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Ejecutando seeders..."
php artisan db:seed --class=DatabaseSeeder --force || echo "Seeder ya ejecutado o falló (esto es normal si el usuario ya existe)"

echo "Iniciando Apache..."
exec apache2-foreground