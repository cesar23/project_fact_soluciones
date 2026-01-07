#!/bin/bash
set -e

echo "Iniciando contenedor..."

# Crear directorios necesarios
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/bootstrap/cache

# Configurar permisos

chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache


# Limpiar cachés
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo "Iniciando Supervisor..."

# Iniciar el servicio de supervisor
service supervisor start

# Mantener el contenedor activo
tail -f /dev/null