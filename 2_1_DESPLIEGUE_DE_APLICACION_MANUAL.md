
## 📋 COMANDOS MANUALES DE DESPLIEGUE

crear network
```shell
NETWORK_NAME="proxynet"
SUBNET="172.50.0.0/16"
GATEWAY="172.50.0.1"
IP_RANGE="172.50.1.0/24"  # Rango específico para contenedores


docker network create \
    --driver bridge \
    --subnet="$SUBNET" \
    --gateway="$GATEWAY" \
    --ip-range="$IP_RANGE" \
    --opt "com.docker.network.bridge.name=${NETWORK_NAME}" \
    --opt "com.docker.network.bridge.enable_icc=true" \
    --opt "com.docker.network.bridge.enable_ip_masquerade=true" \
    --label "project=facturador" \
    --label "environment=production" \
    --label "description=Red compartida para servicios de facturación" \
    "${NETWORK_NAME}"
```

### PASO 0: DETENER TODOS LOS SERVICIOS

```bash
# Detener servicio principal (smart1)
cd /home/cesar/docker-stacks/project_fact_soluciones
docker compose -f stack-facturador-smart/smart1/docker-compose.yml down

# Detener utilidades (si existen)
docker compose -f stack-facturador-smart/utils/docker-compose.yml down

# Detener Cloudflare (si existe)
docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml down

# Detener Nginx Proxy Manager (si existe)
docker compose -f stack-facturador-smart/npm/docker-compose.yml down
```

### PASO 1: ACTUALIZAR CÓDIGO DESDE GIT

```bash
cd /home/cesar/docker-stacks/project_fact_soluciones

# Obtener cambios del repositorio
git fetch origin master

# Aplicar cambios locales (⚠️ CUIDADO: esto sobrescribe cambios locales)
git reset --hard origin/master
```

### PASO 2: PERMISOS DE SCRIPTS

```bash
# Dar permisos de ejecución
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh
chmod +x devops_deploy_stack.sh
chmod +x devops_upload_changes.sh
```

### PASO 3: DESCOMPRIMIR ARCHIVO

```bash
# Ejecutar script de descompresión (si existe smart1.tar.gz)
./stack-facturador-smart/smart1_decompress.sh
```

### PASO 4: PERMISOS DE CARPETAS

```bash
# Crear carpetas si no existen
mkdir -p stack-facturador-smart/smart1/storage
mkdir -p stack-facturador-smart/smart1/bootstrap/cache
mkdir -p stack-facturador-smart/smart1/vendor

# Establecer permisos
sudo chmod -R 777 stack-facturador-smart/smart1/storage/
sudo chmod -R 777 stack-facturador-smart/smart1/bootstrap/
sudo chmod -R 777 stack-facturador-smart/smart1/vendor/
```

### PASO 5: CONSTRUIR Y LEVANTAR CONTENEDORES

```bash
# Construir imágenes Docker (con --no-cache para forzar reconstrucción)
docker compose -f stack-facturador-smart/smart1/docker-compose.yml build --no-cache

# Iniciar contenedores en segundo plano
docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d

# Esperar que los servicios se estabilicen
sleep 10
```

### PASO 6: CONFIGURAR LARAVEL Y DEPENDENCIAS

```bash
# ruta dodne esta el facturador
PROJECT_PATH="/home/cesar/docker-stacks/project_fact_soluciones/stack-facturador-smart/smart1"
DB_ROOT_PASSWORD="WPsOd4xPLL4nGRnOAHJp"
DB_NAME="smart1"
# ═══════════════════════════════════════════════════════════════
# PASO 1: PREPARAR BASE DE DATOS
# ═══════════════════════════════════════════════════════════════
docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME;"
docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "DROP DATABASE IF EXISTS tenancy_ventas;"
# verificar si hay tablas tenancy
docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "
SELECT SCHEMA_NAME 
FROM information_schema.SCHEMATA 
WHERE SCHEMA_NAME LIKE 'tenancy_%';
"
# docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME;"
# docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# ═══════════════════════════════════════════════════════════════
# PASO 2: CONFIGURAR APLICACIÓN
# ═══════════════════════════════════════════════════════════════
docker exec fpm1 apt-get update
docker exec fpm1 composer self-update
# ::: Optional
docker exec fpm1 bash -c "git config --global --add safe.directory /var/www/html"

docker exec fpm1 composer install
docker exec fpm1 php artisan migrate:refresh --seed
docker exec fpm1 php artisan key:generate
docker exec fpm1 php artisan storage:link

# ═══════════════════════════════════════════════════════════════
# PASO 3: LIMPIAR Y OPTIMIZAR CACHÉS
# ═══════════════════════════════════════════════════════════════
docker exec fpm1 php artisan config:clear
docker exec fpm1 php artisan cache:clear
docker exec fpm1 php artisan route:clear
docker exec fpm1 php artisan view:clear
docker exec fpm1 php artisan optimize:clear
docker exec fpm1 php artisan config:cache

# ═══════════════════════════════════════════════════════════════
# PASO 4: CONFIGURAR PERMISOS
# ═══════════════════════════════════════════════════════════════
cd "${PROJECT_PATH}"
sudo chmod -R 777 "./storage/" "./bootstrap/" "./vendor/"  "./public/"
unzip .git.zip
# ═══════════════════════════════════════════════════════════════
# PASO 5: Entrar a la web
# ═══════════════════════════════════════════════════════════════

https://fact.solucionessystem.com

# ═══════════════════════════════════════════════════════════════
# PASO 6: abre una terminal y verifica los cambios
# ═══════════════════════════════════════════════════════════════

inotifywait -m -r -e modify,create,delete --format '%T %w%f %e' \
 --timefmt '%Y-%m-%d %H:%M:%S' \
  /home/cesar/docker-stacks/project_fact_soluciones/stack-facturador-smart/smart1







# Configurar Git en el contenedor










# Instalar dependencias de Composer
docker exec fpm1 bash -c "composer install"

docker exec fpm1 bash -c "php artisan migrate:refresh --seed"
docker exec fpm1 bash -c "php artisan key:generate"
docker exec fpm1 bash -c "php artisan storage:link"

#docker exec fpm1 bash -c "php artisan config:clear"
#docker exec fpm1 bash -c "php artisan config:cache"



# Limpiar todas las cachés de Laravel
docker exec fpm1 bash -c "php artisan storage:link"
docker exec fpm1 bash -c "php artisan cache:clear"
docker exec fpm1 bash -c "php artisan config:clear"
docker exec fpm1 bash -c "php artisan view:clear"
docker exec fpm1 bash -c "php artisan route:clear"

# Cachear configuración
docker exec fpm1 bash -c "php artisan config:cache"

# Cachear rutas
docker exec fpm1 bash -c "php artisan route:cache"

# Crear enlace simbólico de storage
docker exec fpm1 bash -c "php artisan storage:link"

# Configurar permisos para mPDF
docker exec fpm1 bash -c "chmod -R 777 vendor/mpdf/mpdf"

# Verificar versiones
docker exec fpm1 bash -c "php -v | head -n 1"
docker exec fpm1 bash -c "php artisan --version"

# ===============================================
# Importante dar los permisos
# ===============================================
cd "/home/cesar/docker-stacks/project_fact_soluciones/stack-facturador-smart/smart1"
sudo chmod -R 777 "./storage/" "./bootstrap/" "./vendor/"
```

### PASO 7: INICIAR SUPERVISOR

```bash

docker compose exec -T supervisor1 service supervisor start
docker compose exec -T supervisor1 supervisorctl reread
docker compose exec -T supervisor1 supervisorctl update
docker compose exec -T supervisor1 supervisorctl start all
# verifica status
docker compose exec -T supervisor1 supervisorctl status


# Reiniciar Supervisor
docker exec supervisor1 supervisorctl restart all

# Verificar estado
docker exec supervisor1 supervisorctl status
```

### PASO 8: INICIAR SERVICIOS AUXILIARES

```bash
# Iniciar utilidades (phpMyAdmin)
docker compose -f stack-facturador-smart/utils/docker-compose.yml up -d

# Iniciar túnel de Cloudflare
docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml up -d

# Iniciar Nginx Proxy Manager
docker compose -f stack-facturador-smart/npm/docker-compose.yml up -d

# Esperar estabilización
sleep 5
```

### VERIFICACIÓN FINAL

```bash
# Ver estado de todos los contenedores
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Verificar conectividad de base de datos
docker exec fpm1 bash -c "php artisan migrate:status"

# Ver logs si hay problemas
docker compose -f stack-facturador-smart/smart1/docker-compose.yml logs -f
```

Probar conexion entre contenedores

```shell
# ===============================================
# ============== con NETCAT =====================
# ===============================================
# probar conectividad del conteendor (nginx1 al fpm1 con su puerto 9000)
docker exec nginx1 nc -zv fpm1 9000

# probar conectividad del conteendor (fpm1 al nginx1 con su puerto 80)
docker exec fpm1 nc -zv nginx1 80

# ===============================================
# ============== con Curl =====================
# ===============================================
docker compose exec nginx1 curl -v telnet://fpm1:9000
docker compose exec fpm1 curl -v telnet://nginx1:80

# =============================================
# verificar los requerimeintos php
# =============================================

```

---

## 🔴 COMANDOS PARA PRIMERA INSTALACIÓN

**⚠️ SOLO ejecutar estos comandos la PRIMERA VEZ que instalas el sistema:**

```bash
# PASO 0: PREPARAR BASE DE DATOS
docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e "DROP DATABASE IF EXISTS smart1;"
docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e "CREATE DATABASE IF NOT EXISTS smart1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# PASO 1: CONFIGURAR APLICACIÓN
docker exec fpm1 apt-get update
docker exec fpm1 composer self-update
docker exec fpm1 composer install
docker exec fpm1 php artisan migrate:refresh --seed
docker exec fpm1 php artisan key:generate
docker exec fpm1 php artisan storage:link

# PASO 2: LIMPIAR Y OPTIMIZAR
docker exec fpm1 php artisan config:clear
docker exec fpm1 php artisan cache:clear
docker exec fpm1 php artisan route:clear
docker exec fpm1 php artisan view:clear
docker exec fpm1 php artisan optimize:clear
docker exec fpm1 php artisan config:cache

# PASO 3: PERMISOS
sudo chmod -R 777 stack-facturador-smart/smart1/storage/
sudo chmod -R 777 stack-facturador-smart/smart1/bootstrap/
sudo chmod -R 777 stack-facturador-smart/smart1/vendor/
```

---

## 🛠️ COMANDOS ÚTILES DE MANTENIMIENTO

```bash
# Ver logs del servicio principal
docker compose -f stack-facturador-smart/smart1/docker-compose.yml logs -f

# Ver todos los contenedores
docker ps -a

# Detener todos los contenedores
docker stop $(docker ps -q)

# Reiniciar solo el stack principal
docker compose -f stack-facturador-smart/smart1/docker-compose.yml restart

# Entrar al contenedor PHP
docker exec -it fpm1 bash

# Acceder a MySQL
docker exec -it mariadb1 mysql -uroot -pWPsOd4xPLL4nGRnOAHJp

# Ver uso de recursos
docker stats

# Limpiar imágenes no usadas
docker system prune -a
```

---

## 📍 ACCESOS

- **Aplicación principal:** http://localhost:8080
- **Sitio público:** https://fact.solucionessystem.com
- **phpMyAdmin:** http://localhost:8081
- **Nginx Proxy Manager:** http://localhost:81

