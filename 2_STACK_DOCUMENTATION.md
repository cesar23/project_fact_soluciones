# 📚 Documentación Completa del Stack Facturador Smart

## 🎯 Resumen del Sistema

Este documento describe la arquitectura completa del **Stack Facturador Smart**, un sistema de facturación electrónica multi-tenant construido con Laravel 8 y desplegado usando Docker. El sistema está compuesto por **4 stacks principales** que trabajan en conjunto para proporcionar una solución robusta y escalable.

## 🏗️ Arquitectura General



## 📋 Stacks del Sistema

### 1. 🚀 Stack Principal - Aplicación Laravel (`smart1/`)
### 2. 🌐 Stack Nginx Proxy Manager (`npm/`)
### 3. 🔒 Stack Cloudflare Tunnel (`cloudflare/`)
### 4. 🛠️ Stack Utilidades (`utils/`)

---

## 🚀 Stack 1: Aplicación Principal Laravel

**Ubicación:** `stack-facturador-smart/smart1/docker-compose.yml`

### 📝 Descripción
Stack principal que contiene la aplicación Laravel de facturación electrónica con todos sus servicios de soporte.

### 🏗️ Servicios Incluidos

#### 🌐 **nginx1** - Servidor Web
```yaml
nginx1:
    image: rash07/nginx
    working_dir: /var/www/html
    ports:
        - "8080:80"
    environment:
        VIRTUAL_HOST: fact.rog.pe, *.fact.rog.pe
    volumes:
        - ./:/var/www/html
        - /home/cesar/stack-facturador-smart/proxy/fpms/smart1:/etc/nginx/sites-available
    restart: always
```

**🔧 Características:**
- **Imagen:** `rash07/nginx` (Nginx personalizado)
- **Puerto:** 8080 (mapeado al 80 interno)
- **Dominio:** `fact.rog.pe` y subdominios
- **Volúmenes:** Código de la aplicación y configuración de Nginx
- **Propósito:** Servidor web que maneja las peticiones HTTP/HTTPS

#### ⚡ **fpm1** - Procesador PHP
```yaml
fpm1:
    image: rash07/php-fpm:7.4
    working_dir: /var/www/html
    volumes:
        - ./ssh:/root/.ssh
        - ./ssh:/var/www/.ssh
        - ./:/var/www/html
    restart: always
```

**🔧 Características:**
- **Imagen:** `rash07/php-fpm:7.4` (PHP 7.4 con FPM)
- **Volúmenes:** SSH keys y código de la aplicación
- **Propósito:** Procesa las peticiones PHP de Laravel

#### 🗄️ **mariadb1** - Base de Datos
```yaml
mariadb1:
    image: mariadb:10.5.6
    environment:
        - MYSQL_USER=${MYSQL_USER}
        - MYSQL_PASSWORD=${MYSQL_PASSWORD}
        - MYSQL_DATABASE=${MYSQL_DATABASE}
        - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
        - MYSQL_PORT_HOST=${MYSQL_PORT_HOST}
    volumes:
        - mysqldata1:/var/lib/mysql
    ports:
        - "${MYSQL_PORT_HOST}:3306"
    restart: always
```

**🔧 Características:**
- **Imagen:** `mariadb:10.5.6`
- **Variables de entorno:** Configuración de usuario, contraseña y base de datos
- **Puerto:** Configurable via `MYSQL_PORT_HOST`
- **Persistencia:** Volumen `mysqldata1` para datos
- **Propósito:** Base de datos principal del sistema

#### 🚀 **redis1** - Cache y Sesiones
```yaml
redis1:
    image: redis:alpine
    volumes:
        - redisdata1:/data
    restart: always
```

**🔧 Características:**
- **Imagen:** `redis:alpine` (ligero)
- **Persistencia:** Volumen `redisdata1`
- **Propósito:** Cache de Laravel, sesiones y colas

#### ⏰ **scheduling1** - Programador de Tareas
```yaml
scheduling1:
    image: rash07/scheduling
    working_dir: /var/www/html
    volumes:
        - ./:/var/www/html
    restart: always
```

**🔧 Características:**
- **Imagen:** `rash07/scheduling` (Laravel Scheduler)
- **Propósito:** Ejecuta tareas programadas de Laravel (cron jobs)

#### 👥 **supervisor1** - Gestor de Colas
```yaml
supervisor1:
    image: rash07/php7.4-supervisor
    working_dir: /var/www/html
    volumes:
        - ./:/var/www/html
        - ./supervisor.conf:/etc/supervisor/conf.d/supervisor.conf
    restart: always
```

**🔧 Características:**
- **Imagen:** `rash07/php7.4-supervisor`
- **Configuración:** `supervisor.conf` para workers
- **Propósito:** Procesa trabajos en cola de Laravel

### 🌐 Redes y Volúmenes

#### Red Externa
```yaml
networks:
    default:
        external:
            name: proxynet
```

#### Volúmenes Persistentes
```yaml
volumes:
    redisdata1:
        driver: "local"
    mysqldata1:
        driver: "local"
```

### 🚀 Comandos de Gestión

```bash
# Iniciar el stack principal
cd stack-facturador-smart/smart1
docker-compose up -d

# Ver logs
docker-compose logs -f

# Ejecutar comandos Laravel
docker-compose exec fpm1 php artisan migrate
docker-compose exec fpm1 php artisan cache:clear

# Acceder al contenedor
docker-compose exec fpm1 bash
```

---

## 🌐 Stack 2: Nginx Proxy Manager

**Ubicación:** `stack-facturador-smart/npm/docker-compose.yml`

### 📝 Descripción
Proxy reverso con gestión SSL automática para manejar múltiples dominios y certificados.

### 🏗️ Servicio Principal

#### 🔧 **npm** - Nginx Proxy Manager
```yaml
npm:
    build:
        context: ./.docker/bin/
        dockerfile: Dockerfile
    container_name: nginx-proxy-manager
    restart: unless-stopped
    environment:
        TZ: ${TZ:-America/Lima}
        DB_SQLITE_FILE: ${DB_SQLITE_FILE:-/data/database.sqlite}
    volumes:
        - ./data:/data
        - ./letsencrypt:/etc/letsencrypt
    ports:
        - "${HTTP_PORT:-80}:80"       # HTTP público
        - "${HTTPS_PORT:-443}:443"    # HTTPS público
        - "${ADMIN_PORT:-81}:81"      # Panel admin
    networks:
        - proxynet
    healthcheck:
        test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost:81"]
        interval: 30s
        timeout: 5s
        retries: 3
        start_period: 10s
```

**🔧 Características:**
- **Build personalizado:** Con plugins certbot-dns-duckdns y certbot-dns-cloudflare
- **Puertos:**
  - `80`: HTTP público
  - `443`: HTTPS público  
  - `81`: Panel de administración
- **Persistencia:** Base de datos SQLite y certificados Let's Encrypt
- **Health Check:** Verificación automática del estado
- **Zona horaria:** America/Lima

### 🚀 Comandos de Gestión

```bash
# Iniciar NPM
cd stack-facturador-smart/npm
docker-compose up -d

# Ver logs
docker-compose logs -f npm

# Acceder al panel admin
# http://TU_IP:81
```

### 🔧 Configuración de Dominios

1. **Acceder al panel:** `http://TU_IP:81`
2. **Credenciales por defecto:**
   - Email: `admin@example.com`
   - Password: `changeme`
3. **Configurar proxy host:**
   - Domain: `fact.rog.pe`
   - Forward Hostname/IP: `nginx1` (nombre del servicio)
   - Forward Port: `80`
   - SSL: Let's Encrypt automático

---

## 🔒 Stack 3: Cloudflare Tunnel

**Ubicación:** `stack-facturador-smart/cloudflare/docker-compose.yml`

### 📝 Descripción
Túnel seguro de Cloudflare para exponer servicios locales sin abrir puertos en el firewall.

### 🏗️ Servicio Principal

#### 🌐 **cloudflared** - Cloudflare Tunnel
```yaml
cloudflared:
    image: cloudflare/cloudflared:latest
    container_name: cloudflared-tunnel
    restart: unless-stopped
    command: tunnel --no-autoupdate run
    environment:
        TUNNEL_TOKEN: ${TUNNEL_TOKEN}
    networks:
        - proxynet
    healthcheck:
        test: ["CMD", "cloudflared", "tunnel", "info"]
        interval: 30s
        timeout: 10s
        retries: 3
        start_period: 10s
```

**🔧 Características:**
- **Imagen:** `cloudflare/cloudflared:latest`
- **Token:** Configurado via `TUNNEL_TOKEN`
- **Comando:** `tunnel --no-autoupdate run`
- **Health Check:** Verificación del estado del túnel
- **Red:** Conectado a `proxynet`

### 🔧 Configuración del Túnel

#### 1. Crear Túnel en Cloudflare Dashboard
```bash
# Instalar cloudflared localmente (una sola vez)
# Descargar desde: https://github.com/cloudflare/cloudflared/releases

# Autenticarse
cloudflared tunnel login

# Crear túnel
cloudflared tunnel create facturador-smart

# Obtener el token del túnel
cloudflared tunnel token facturador-smart
```

#### 2. Configurar Variables de Entorno
```bash
# En el archivo .env del directorio cloudflare/
TUNNEL_TOKEN=eyJhIjoi...
```

#### 3. Configurar DNS en Cloudflare
- **Tipo:** CNAME
- **Nombre:** `fact.rog.pe`
- **Contenido:** `{tunnel-id}.cfargotunnel.com`

### 🚀 Comandos de Gestión

```bash
# Iniciar túnel
cd stack-facturador-smart/cloudflare
docker-compose up -d

# Ver logs
docker-compose logs -f cloudflared

# Verificar estado
docker-compose exec cloudflared cloudflared tunnel info
```

---

## 🛠️ Stack 4: Utilidades

**Ubicación:** `stack-facturador-smart/utils/docker-compose.yml`

### 📝 Descripción
Herramientas de administración y utilidades para el stack principal.

### 🏗️ Servicio Principal

#### 🗄️ **phpmyadmin** - Administrador de Base de Datos
```yaml
phpmyadmin:
    image: phpmyadmin:5-apache
    container_name: utils_phpmyadmin
    env_file:
        - .env
    restart: unless-stopped
    environment:
        PUID: ${UID:-1000}
        PGID: ${GID:-1000}
        TZ: ${TZ}
        PMA_HOST: ${MYSQL_CONTAINER}
        PMA_PORT: ${MYSQL_PORT}
        UPLOAD_LIMIT: 128M
    ports:
        - "9090:80"
    networks:
        - internal
        - proxynet
    volumes:
        - ./services/phpmyadmin/config.user.inc.php:/etc/phpmyadmin/config.user.inc.php:ro
```

**🔧 Características:**
- **Imagen:** `phpmyadmin:5-apache`
- **Puerto:** `9090` (acceso web)
- **Configuración:** Archivo `.env` y configuración personalizada
- **Redes:** `internal` y `proxynet`
- **Límite de subida:** 128MB

### 🔧 Variables de Entorno Requeridas

```bash
# En utils/.env
MYSQL_CONTAINER=mariadb1
MYSQL_PORT=3306
TZ=America/Lima
UID=1000
GID=1000
```

### 🚀 Comandos de Gestión

```bash
# Iniciar utilidades
cd stack-facturador-smart/utils
docker-compose up -d

# Acceder a phpMyAdmin
# http://TU_IP:9090
```

---

## 🌐 Red Externa: proxynet

### 📝 Descripción
Red externa compartida entre todos los stacks para comunicación.

### 🔧 Creación de la Red

```bash
# Crear la red externa (una sola vez)
docker network create proxynet
```

### 🔍 Verificar Red

```bash
# Listar redes
docker network ls

# Inspeccionar red
docker network inspect proxynet
```

---

## 🚀 Tutorial de Instalación Completa

### 📋 Prerrequisitos

1. **Docker y Docker Compose** instalados
2. **Dominio configurado** en Cloudflare
3. **Acceso SSH** al servidor
4. **Puertos abiertos:** 80, 443, 8080, 9090

### 🔧 Paso 1: Preparar el Entorno

```bash
# Crear directorio principal
mkdir -p /home/cesar/stack-facturador-smart
cd /home/cesar/stack-facturador-smart

# Crear red externa
docker network create proxynet

# Clonar repositorio (si es necesario)
git clone <tu-repositorio> .
```

### 🔧 Paso 2: Configurar Variables de Entorno

#### Archivo `smart1/.env`
```bash
# Base de datos
MYSQL_USER=facturador
MYSQL_PASSWORD=tu_password_seguro
MYSQL_DATABASE=tenancy
MYSQL_ROOT_PASSWORD=root_password_seguro
MYSQL_PORT_HOST=3306

# Aplicación Laravel
APP_NAME="Facturador Smart"
APP_ENV=production
APP_KEY=base64:tu_app_key_aqui
APP_DEBUG=false
APP_URL=https://fact.rog.pe

# Base de datos Laravel
DB_CONNECTION=mysql
DB_HOST=mariadb1
DB_PORT=3306
DB_DATABASE=tenancy
DB_USERNAME=facturador
DB_PASSWORD=tu_password_seguro
```

#### Archivo `npm/.env`
```bash
# Nginx Proxy Manager
TZ=America/Lima
HTTP_PORT=80
HTTPS_PORT=443
ADMIN_PORT=81
DB_SQLITE_FILE=/data/database.sqlite
```

#### Archivo `cloudflare/.env`
```bash
# Cloudflare Tunnel
TUNNEL_TOKEN=eyJhIjoi...
```

#### Archivo `utils/.env`
```bash
# Utilidades
MYSQL_CONTAINER=mariadb1
MYSQL_PORT=3306
TZ=America/Lima
UID=1000
GID=1000
```

### 🔧 Paso 3: Desplegar Stacks

#### 1. Stack Principal (Laravel)
```bash
cd stack-facturador-smart/smart1
docker-compose up -d
```

#### 2. Nginx Proxy Manager
```bash
cd stack-facturador-smart/npm
docker-compose up -d
```

#### 3. Cloudflare Tunnel
```bash
cd stack-facturador-smart/cloudflare
docker-compose up -d
```

#### 4. Utilidades
```bash
cd stack-facturador-smart/utils
docker-compose up -d
```

### 🔧 Paso 4: Configurar Aplicación Laravel

```bash
# Acceder al contenedor FPM
docker-compose exec fpm1 bash

# Instalar dependencias
composer install
npm install

# Configurar aplicación
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Compilar assets
npm run prod
```

### 🔧 Paso 5: Configurar Proxy y SSL

1. **Acceder a NPM:** `http://TU_IP:81`
2. **Configurar proxy host:**
   - Domain: `fact.rog.pe`
   - Forward Hostname/IP: `nginx1`
   - Forward Port: `80`
   - SSL: Let's Encrypt
3. **Configurar Cloudflare Tunnel** (opcional)

---

## 🔍 Comandos de Monitoreo y Mantenimiento

### 📊 Estado de los Servicios

```bash
# Ver todos los contenedores
docker ps

# Ver logs de un servicio específico
docker-compose logs -f nginx1
docker-compose logs -f fpm1
docker-compose logs -f mariadb1

# Ver uso de recursos
docker stats
```

### 🔄 Backup y Restauración

```bash
# Backup de base de datos
docker-compose exec mariadb1 mysqldump -u root -p tenancy > backup_$(date +%Y%m%d).sql

# Backup de archivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz ./

# Restaurar base de datos
docker-compose exec -T mariadb1 mysql -u root -p tenancy < backup_20231201.sql
```

### 🧹 Limpieza y Mantenimiento

```bash
# Limpiar contenedores parados
docker container prune

# Limpiar imágenes no utilizadas
docker image prune

# Limpiar volúmenes no utilizados
docker volume prune

# Limpiar todo (¡CUIDADO!)
docker system prune -a
```

---

## 🚨 Solución de Problemas Comunes

### ❌ Error: Red proxynet no existe
```bash
docker network create proxynet
```

### ❌ Error: Puerto ya en uso
```bash
# Verificar qué proceso usa el puerto
sudo netstat -tulpn | grep :80
sudo lsof -i :80

# Cambiar puerto en docker-compose.yml
```

### ❌ Error: Base de datos no conecta
```bash
# Verificar variables de entorno
docker-compose exec fpm1 env | grep DB_

# Verificar conectividad
docker-compose exec fpm1 ping mariadb1
```

### ❌ Error: SSL no funciona
```bash
# Verificar certificados en NPM
docker-compose exec npm ls /etc/letsencrypt/

# Regenerar certificado
# En panel NPM: SSL Certificates > Add SSL Certificate
```

---

## 📈 Escalabilidad y Optimización

### 🚀 Optimizaciones de Rendimiento

1. **Nginx:** Configurar cache y compresión
2. **PHP-FPM:** Ajustar pool de procesos
3. **MariaDB:** Optimizar configuración
4. **Redis:** Configurar persistencia

### 📊 Monitoreo

1. **Logs centralizados:** ELK Stack
2. **Métricas:** Prometheus + Grafana
3. **Alertas:** AlertManager
4. **Health checks:** Automáticos

---

## 📚 Referencias y Enlaces Útiles

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Nginx Proxy Manager](https://nginxproxymanager.com/)
- [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [Laravel Documentation](https://laravel.com/docs)
- [MariaDB Documentation](https://mariadb.org/documentation/)

---

## 🏷️ Versiones y Compatibilidad

- **Docker:** 20.10+
- **Docker Compose:** 3.7+
- **PHP:** 7.4
- **Laravel:** 8.x
- **MariaDB:** 10.5.6
- **Redis:** Alpine
- **Nginx:** rash07/nginx
- **Cloudflare:** cloudflared:latest

---

*Documentación generada automáticamente para el Stack Facturador Smart*  
*Última actualización: $(date)*
