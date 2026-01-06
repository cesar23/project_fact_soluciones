# Documentación del Stack Docker - Facturador Smart

## Tabla de Contenido
- [Arquitectura General](#arquitectura-general)
- [Servicios del Stack](#servicios-del-stack)
- [Imágenes Docker Utilizadas](#imágenes-docker-utilizadas)
- [Red y Comunicación](#red-y-comunicación)
- [Volúmenes y Persistencia](#volúmenes-y-persistencia)
- [Variables de Entorno](#variables-de-entorno)
- [Puertos Expuestos](#puertos-expuestos)
- [Configuración de Servicios](#configuración-de-servicios)
- [Comandos Útiles](#comandos-útiles)
- [Troubleshooting](#troubleshooting)

---

## Arquitectura General

El Facturador Smart utiliza una arquitectura de **microservicios en contenedores Docker** con los siguientes componentes:

```
┌─────────────────────────────────────────────────────────────┐
│                    Red Externa: proxynet                     │
│                                                               │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │   nginx1     │───▶│    fpm1      │    │  mariadb1    │  │
│  │  (Web Server)│    │ (PHP-FPM 7.4)│    │ (Database)   │  │
│  └──────────────┘    └──────────────┘    └──────────────┘  │
│                                                               │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │   redis1     │    │ scheduling1  │    │ supervisor1  │  │
│  │   (Cache)    │    │  (Cron Jobs) │    │(Queue Worker)│  │
│  └──────────────┘    └──────────────┘    └──────────────┘  │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │              Proxy Nginx (Externo)                  │    │
│  │           Maneja SSL y Virtual Hosts                │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

---

## Servicios del Stack

### 1. **nginx1** - Servidor Web
**Propósito**: Servidor web Nginx que sirve los archivos estáticos y actúa como proxy inverso hacia PHP-FPM.

- **Imagen**: `rash07/nginx`
- **Working Directory**: `/var/www/html`
- **Restart Policy**: `always`
- **Dependencias**: Conecta con `fpm1` para procesar PHP

**Características**:
- No expone puertos directamente (usa red proxynet)
- Configuración de virtual hosts mediante `VIRTUAL_HOST`
- Configuración de sitios cargada desde volumen externo

### 2. **fpm1** - PHP-FPM 7.4
**Propósito**: Procesa código PHP de la aplicación Laravel.

- **Imagen**: `rash07/php-fpm:7.4`
- **Working Directory**: `/var/www/html`
- **Restart Policy**: `always`
- **Extensiones PHP**: soap, zip, gd, pdo_mysql, redis, etc.

**Características**:
- Ejecuta el código Laravel
- Maneja peticiones FastCGI desde Nginx
- Acceso a claves SSH para operaciones git/deploy

### 3. **mariadb1** - Base de Datos
**Propósito**: Base de datos relacional para multi-tenancy.

- **Imagen**: `mariadb:10.5.6`
- **Puerto Expuesto**: `${MYSQL_PORT_HOST}:3306` (configurable, default 3306)
- **Restart Policy**: `always`

**Características**:
- Almacena base de datos del sistema (tenancy)
- Almacena bases de datos de cada tenant (tenancy_*)
- Volumen persistente para datos
- Acceso remoto habilitado por puerto mapeado

### 4. **redis1** - Cache y Sesiones
**Propósito**: Sistema de cache en memoria y gestor de colas.

- **Imagen**: `redis:alpine` (versión ligera)
- **Restart Policy**: `always`

**Características**:
- Cache de aplicación Laravel
- Almacenamiento de sesiones (opcional)
- Gestión de colas Redis (alternativa a database)
- Volumen persistente para datos

### 5. **scheduling1** - Cron Scheduler
**Propósito**: Ejecuta tareas programadas de Laravel (cron jobs).

- **Imagen**: `rash07/scheduling`
- **Working Directory**: `/var/www/html`
- **Restart Policy**: `always`

**Características**:
- Ejecuta `php artisan schedule:run` cada minuto
- Maneja tareas programadas como:
  - Envío de reportes automáticos
  - Limpieza de archivos temporales
  - Sincronización con SUNAT
  - Notificaciones programadas

### 6. **supervisor1** - Queue Workers
**Propósito**: Gestiona procesos en segundo plano (workers de colas).

- **Imagen**: `rash07/php7.4-supervisor`
- **Working Directory**: `/var/www/html`
- **Restart Policy**: `always`
- **Configuración**: Carga `supervisor.conf`

**Características**:
- Ejecuta 8 workers en paralelo (`numprocs=8`)
- Procesa jobs de colas (facturación electrónica, emails, etc.)
- Auto-reinicio de workers en caso de fallo
- Logs en `/var/www/html/storage/logs/supervisor.log`

---

## Imágenes Docker Utilizadas

### Imágenes Oficiales

#### 1. **mariadb:10.5.6**
- **Repositorio**: Docker Hub oficial
- **Tamaño**: ~400 MB
- **Descripción**: Base de datos MySQL/MariaDB
- **Documentación**: https://hub.docker.com/_/mariadb

#### 2. **redis:alpine**
- **Repositorio**: Docker Hub oficial
- **Tamaño**: ~32 MB
- **Descripción**: Redis sobre Alpine Linux (versión ligera)
- **Documentación**: https://hub.docker.com/_/redis

### Imágenes Personalizadas (rash07/*)

Estas imágenes están pre-configuradas para el stack del Facturador Smart:

#### 3. **rash07/nginx**
- **Base**: Nginx oficial
- **Configuraciones**:
  - FastCGI para PHP
  - Configuración de virtual hosts
  - Optimizaciones para Laravel
  - Soporte para archivos grandes (uploads)

#### 4. **rash07/php-fpm:7.4**
- **Base**: PHP 7.4-FPM oficial
- **Extensiones incluidas**:
  - `pdo_mysql`, `mysqli` - Conexión a base de datos
  - `soap` - Integración con web services SUNAT
  - `zip` - Compresión de archivos
  - `gd`, `imagick` - Procesamiento de imágenes
  - `xml`, `dom` - Procesamiento XML
  - `mbstring` - Manejo de caracteres multibyte
  - `redis` - Cliente Redis
  - `bcmath` - Matemáticas de precisión
  - `intl` - Internacionalización
- **Herramientas**:
  - Composer instalado globalmente
  - Git
  - Node.js y NPM (para assets)

#### 5. **rash07/scheduling**
- **Base**: PHP 7.4 con cron
- **Propósito**: Contenedor especializado para Laravel Scheduler
- **Configuración**:
  - Cron ejecuta `php artisan schedule:run` cada minuto
  - Logs de cron en `/var/log/cron.log`

#### 6. **rash07/php7.4-supervisor**
- **Base**: PHP 7.4 con Supervisor
- **Propósito**: Gestión de queue workers
- **Configuración**:
  - Supervisor instalado y configurado
  - Mismas extensiones PHP que php-fpm
  - Manejo de procesos en segundo plano

---

## Red y Comunicación

### Red Externa: `proxynet`

```yaml
networks:
  default:
    external:
      name: proxynet
```

**Características**:
- Red Docker externa pre-existente
- Permite comunicación entre múltiples stacks
- Utilizada por el proxy Nginx principal
- Debe ser creada previamente: `docker network create proxynet`

### Comunicación entre Servicios

Los servicios se comunican usando nombres de servicio como hostname:

```
nginx1 → fpm1:9000 (FastCGI)
fpm1 → mariadb1:3306 (MySQL)
fpm1 → redis1:6379 (Redis)
supervisor1 → mariadb1:3306 (MySQL)
supervisor1 → redis1:6379 (Redis)
```

### Proxy Nginx Externo

El proxy Nginx externo (definido en `install.sh`) maneja:
- **Puerto 80**: HTTP
- **Puerto 443**: HTTPS con SSL
- Virtual hosts basados en variable `VIRTUAL_HOST`
- Certificados SSL en `/etc/nginx/certs`

**Configuración del Proxy** (generada por install.sh):
```yaml
services:
  proxy:
    image: rash07/nginx-proxy:2.0
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./../certs:/etc/nginx/certs
      - /var/run/docker.sock:/tmp/docker.sock:ro
```

---

## Volúmenes y Persistencia

### Volúmenes Docker Nombrados

#### 1. **mysqldata1**
```yaml
volumes:
  mysqldata1:
    driver: "local"
```
- **Montado en**: `/var/lib/mysql` (dentro del contenedor)
- **Contenido**: Bases de datos MariaDB
- **Persistencia**: Los datos sobreviven al reinicio de contenedores
- **Backup**: Crítico - contiene todas las bases de datos

#### 2. **redisdata1**
```yaml
volumes:
  redisdata1:
    driver: "local"
```
- **Montado en**: `/data` (dentro del contenedor)
- **Contenido**: Snapshots RDB de Redis
- **Persistencia**: Cache y datos de sesiones
- **Backup**: Opcional - datos volátiles

### Bind Mounts (Volúmenes del Host)

#### Aplicación Laravel
```yaml
volumes:
  - ./:/var/www/html
```
- **Servicios**: nginx1, fpm1, scheduling1, supervisor1
- **Propósito**: Código fuente de la aplicación
- **Sincronización**: Bidireccional (host ↔ contenedor)
- **Desarrollo**: Cambios en código se reflejan inmediatamente

#### Claves SSH
```yaml
volumes:
  - ./ssh:/root/.ssh
  - ./ssh:/var/www/.ssh
```
- **Servicio**: fpm1
- **Propósito**: Claves SSH para git, deploy, etc.
- **Nota**: Directorio vacío por defecto

#### Configuración Nginx
```yaml
volumes:
  - /home/cesar/stack-facturador-smart/proxy/fpms/smart1:/etc/nginx/sites-available
```
- **Servicio**: nginx1
- **Propósito**: Configuración de virtual hosts
- **Contenido**: Archivo `default` con configuración Nginx

**Contenido del archivo de configuración Nginx** (generado por install.sh):
```nginx
server {
    listen 80 default_server;
    root /var/www/html/public;
    index index.php;
    server_name *._;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass fpm1:9000;
        fastcgi_read_timeout 3600;
    }

    location ~ /storage/.*\.(php|html)$ {
        deny all;
    }
}
```

#### Configuración Supervisor
```yaml
volumes:
  - ./supervisor.conf:/etc/supervisor/conf.d/supervisor.conf
```
- **Servicio**: supervisor1
- **Propósito**: Configuración de workers
- **Sincronización**: Cambios requieren reinicio del contenedor

---

## Variables de Entorno

### Variables Definidas en `.env`

El archivo `.env` define variables utilizadas por docker-compose:

```bash
# Credenciales MySQL
MYSQL_USER=smart1              # Usuario de base de datos
MYSQL_PASSWORD=<generado>      # Contraseña del usuario
MYSQL_DATABASE=smart1          # Nombre de la base de datos
MYSQL_ROOT_PASSWORD=<generado> # Contraseña root
MYSQL_PORT_HOST=3306           # Puerto expuesto en el host
```

### Uso en docker-compose.yml

#### MariaDB
```yaml
environment:
  - MYSQL_USER=${MYSQL_USER}
  - MYSQL_PASSWORD=${MYSQL_PASSWORD}
  - MYSQL_DATABASE=${MYSQL_DATABASE}
  - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
  - MYSQL_PORT_HOST=${MYSQL_PORT_HOST}
```

#### Nginx (Virtual Host)
```yaml
environment:
  VIRTUAL_HOST: fact.rog.pe, *.fact.rog.pe
```
- **Propósito**: El proxy externo detecta esta variable
- **Formato**: Dominio principal, wildcard subdominios
- **Efecto**: Enruta tráfico HTTP a este contenedor

---

## Puertos Expuestos

### Puerto Público

#### MariaDB - Puerto 3306
```yaml
ports:
  - "${MYSQL_PORT_HOST}:3306"
```

**Características**:
- **Puerto Host**: Variable `MYSQL_PORT_HOST` (default: 3306)
- **Puerto Contenedor**: 3306 (MySQL estándar)
- **Propósito**: Acceso remoto a base de datos
- **Seguridad**: Debería estar protegido por firewall

**Conexión desde el host**:
```bash
mysql -h 127.0.0.1 -P 3306 -u root -p
```

**Conexión desde aplicación externa**:
```
Host: IP_DEL_SERVIDOR
Port: 3306
User: root
Password: ${MYSQL_ROOT_PASSWORD}
```

### Puertos Internos (No expuestos al host)

Estos puertos solo son accesibles dentro de la red `proxynet`:

- **nginx1**: Puerto 80 (HTTP interno)
- **fpm1**: Puerto 9000 (FastCGI)
- **redis1**: Puerto 6379 (Redis)
- **mariadb1**: Puerto 3306 (interno, además del puerto mapeado)

### Puertos del Proxy Externo

El proxy Nginx (servicio separado) expone:
- **Puerto 80**: HTTP público
- **Puerto 443**: HTTPS público con SSL

---

## Configuración de Servicios

### Supervisor Configuration (`supervisor.conf`)

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/supervisor.log
stopwaitsecs=3600
```

**Parámetros importantes**:
- `numprocs=8`: 8 workers procesando colas en paralelo
- `--sleep=3`: Espera 3 segundos entre jobs cuando la cola está vacía
- `--tries=3`: Reintenta jobs fallidos hasta 3 veces
- `stopwaitsecs=3600`: Espera 1 hora antes de forzar detención (para jobs largos)
- `autorestart=true`: Reinicia workers automáticamente si fallan

**Logs**:
```bash
# Ver logs de supervisor
docker-compose exec supervisor1 tail -f /var/www/html/storage/logs/supervisor.log

# Estado de workers
docker-compose exec supervisor1 supervisorctl status
```

### Restart Policies

Todos los servicios tienen `restart: always`:
- Se inician automáticamente con Docker
- Se reinician si fallan
- Se reinician después de reiniciar el servidor

---

## Comandos Útiles

### Gestión del Stack

```bash
# Iniciar todos los servicios
docker-compose up -d

# Detener todos los servicios
docker-compose down

# Detener sin eliminar contenedores
docker-compose stop

# Reiniciar todos los servicios
docker-compose restart

# Reiniciar un servicio específico
docker-compose restart fpm1

# Ver estado de servicios
docker-compose ps

# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f fpm1
```

### Ejecutar Comandos en Contenedores

```bash
# Acceder a shell de PHP-FPM
docker-compose exec fpm1 bash

# Ejecutar comandos Artisan
docker-compose exec fpm1 php artisan migrate
docker-compose exec fpm1 php artisan cache:clear
docker-compose exec fpm1 php artisan queue:work

# Instalar dependencias
docker-compose exec fpm1 composer install
docker-compose exec fpm1 npm install

# Acceder a MySQL
docker-compose exec mariadb1 mysql -u root -p

# Acceder a Redis CLI
docker-compose exec redis1 redis-cli

# Ver estado de Supervisor
docker-compose exec supervisor1 supervisorctl status

# Reiniciar workers de Supervisor
docker-compose exec supervisor1 supervisorctl restart all
```

### Gestión de Volúmenes

```bash
# Listar volúmenes
docker volume ls | grep smart1

# Inspeccionar volumen
docker volume inspect smart1_mysqldata1

# Backup de base de datos
docker-compose exec mariadb1 mysqldump -u root -p${MYSQL_ROOT_PASSWORD} --all-databases > backup.sql

# Restaurar base de datos
docker-compose exec -T mariadb1 mysql -u root -p${MYSQL_ROOT_PASSWORD} < backup.sql

# Eliminar volúmenes (¡PELIGROSO!)
docker-compose down -v
```

### Monitoreo

```bash
# Ver uso de recursos
docker stats

# Ver procesos en contenedor
docker-compose exec fpm1 ps aux

# Ver conexiones a MySQL
docker-compose exec mariadb1 mysql -u root -p -e "SHOW PROCESSLIST;"

# Ver información de Redis
docker-compose exec redis1 redis-cli INFO

# Tamaño de volúmenes
docker system df -v
```

---

## Troubleshooting

### Problemas Comunes

#### 1. Error: "network proxynet not found"

**Solución**:
```bash
docker network create proxynet
```

#### 2. Puerto 3306 ya en uso

**Solución**: Cambiar `MYSQL_PORT_HOST` en `.env`:
```bash
MYSQL_PORT_HOST=3307
```
Luego:
```bash
docker-compose down
docker-compose up -d
```

#### 3. Permisos en storage/bootstrap

**Solución**:
```bash
docker-compose exec fpm1 chmod -R 777 storage/ bootstrap/cache/
```

#### 4. Workers no procesan colas

**Verificar estado**:
```bash
docker-compose exec supervisor1 supervisorctl status
```

**Reiniciar workers**:
```bash
docker-compose exec supervisor1 supervisorctl restart all
```

**Ver logs**:
```bash
docker-compose exec supervisor1 tail -f /var/www/html/storage/logs/supervisor.log
```

#### 5. Errores de conexión a MySQL

**Verificar que el contenedor esté corriendo**:
```bash
docker-compose ps mariadb1
```

**Verificar credenciales en .env**:
```bash
cat .env | grep MYSQL
```

**Probar conexión desde FPM**:
```bash
docker-compose exec fpm1 php artisan tinker
>>> DB::connection()->getPdo();
```

#### 6. Nginx retorna 502 Bad Gateway

**Causas comunes**:
- PHP-FPM no está corriendo
- PHP-FPM está sobrecargado
- Error en código PHP

**Verificar logs**:
```bash
docker-compose logs fpm1
docker-compose logs nginx1
```

#### 7. Espacio en disco lleno

**Ver uso de espacio**:
```bash
docker system df
```

**Limpiar imágenes no usadas**:
```bash
docker system prune -a
```

**Limpiar volúmenes no usados**:
```bash
docker volume prune
```

#### 8. Contenedor se reinicia constantemente

**Ver logs del contenedor**:
```bash
docker-compose logs <servicio>
```

**Ver últimos eventos**:
```bash
docker events
```

### Logs Importantes

```bash
# Logs de Laravel
docker-compose exec fpm1 tail -f storage/logs/laravel.log

# Logs de Nginx
docker-compose logs nginx1

# Logs de PHP-FPM
docker-compose logs fpm1

# Logs de Supervisor
docker-compose exec supervisor1 tail -f /var/www/html/storage/logs/supervisor.log

# Logs de MySQL
docker-compose logs mariadb1

# Logs del Scheduler
docker-compose logs scheduling1
```

---

## Arquitectura Multi-Instancia

El sufijo `1` en todos los servicios (`nginx1`, `fpm1`, etc.) permite ejecutar múltiples instancias del Facturador Smart en el mismo servidor:

```yaml
# Instancia 1 (smart1)
services:
  nginx1, fpm1, mariadb1, redis1, scheduling1, supervisor1

# Instancia 2 (smart2) - archivo docker-compose separado
services:
  nginx2, fpm2, mariadb2, redis2, scheduling2, supervisor2
```

**Ventajas**:
- Múltiples clientes en un servidor
- Aislamiento completo entre instancias
- Diferentes versiones o configuraciones
- Facilita testing y staging

**Consideraciones**:
- Cada instancia necesita su propio directorio
- Puertos MySQL diferentes (`MYSQL_PORT_HOST`)
- Nombres de volúmenes únicos
- Virtual hosts diferentes

---

## Seguridad

### Recomendaciones

1. **Firewall**: Limitar acceso al puerto MySQL (3306)
   ```bash
   ufw allow from IP_CONFIABLE to any port 3306
   ```

2. **Contraseñas fuertes**: Las contraseñas en `.env` deben ser robustas

3. **SSL/TLS**: Usar certificados SSL en producción (Let's Encrypt)

4. **Actualizaciones**: Mantener imágenes actualizadas
   ```bash
   docker-compose pull
   docker-compose up -d
   ```

5. **Backups**: Automatizar backups de volúmenes

6. **No exponer Redis**: Redis no debe ser accesible desde fuera

7. **Logs**: Monitorear logs regularmente para detectar problemas

---

## Mantenimiento

### Backups Automatizados

```bash
#!/bin/bash
# Script de backup diario

BACKUP_DIR="/backups/smart1"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup de base de datos
docker-compose exec -T mariadb1 mysqldump -u root -p${MYSQL_ROOT_PASSWORD} \
  --all-databases > ${BACKUP_DIR}/db_${DATE}.sql

# Backup de archivos
tar -czf ${BACKUP_DIR}/files_${DATE}.tar.gz storage/

# Eliminar backups antiguos (más de 7 días)
find ${BACKUP_DIR} -mtime +7 -delete
```

### Actualización del Sistema

```bash
# 1. Backup
./backup.sh

# 2. Detener servicios
docker-compose down

# 3. Actualizar código
git pull

# 4. Actualizar imágenes
docker-compose pull

# 5. Actualizar dependencias
docker-compose run --rm fpm1 composer install --no-dev

# 6. Ejecutar migraciones
docker-compose run --rm fpm1 php artisan migrate --force

# 7. Limpiar cache
docker-compose run --rm fpm1 php artisan cache:clear
docker-compose run --rm fpm1 php artisan config:cache
docker-compose run --rm fpm1 php artisan route:cache

# 8. Iniciar servicios
docker-compose up -d

# 9. Verificar estado
docker-compose ps
```

---

## Referencias

- Docker Compose: https://docs.docker.com/compose/
- Laravel Deployment: https://laravel.com/docs/8.x/deployment
- Supervisor: http://supervisord.org/
- Nginx: https://nginx.org/en/docs/
- MariaDB: https://mariadb.com/kb/en/documentation/
