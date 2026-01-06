# DOCUMENTACION COMPLETA: ARQUITECTURA MULTI-TENANT CON CLOUDFLARE TUNNEL

## INDICE
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura General](#arquitectura-general)
3. [Flujo Completo de una Peticion](#flujo-completo-de-una-peticion)
4. [Componentes del Sistema](#componentes-del-sistema)
5. [Configuracion de Docker](#configuracion-de-docker)
6. [Sistema Multi-Tenant (hyn/multi-tenant)](#sistema-multi-tenant)
7. [Configuracion de Cloudflare Tunnel](#configuracion-de-cloudflare-tunnel)
8. [Configuracion de Nginx Proxy Manager](#configuracion-de-nginx-proxy-manager)
9. [Como Funciona el Enrutamiento de Subdominios](#como-funciona-el-enrutamiento-de-subdominios)
10. [Diagnostico del Problema con Subdominios](#diagnostico-del-problema-con-subdominios)
11. [Solucion Propuesta](#solucion-propuesta)

---

## RESUMEN EJECUTIVO

**Facturador Smart** es una aplicacion Laravel 8 multi-tenant que permite a multiples clientes (tenants) usar la misma instalacion, cada uno con su propio subdominio y base de datos aislada.

### Tecnologias Clave:
- **Backend:** Laravel 8 + hyn/multi-tenant
- **Base de datos:** MariaDB 10.5.6 (1 BD sistema + N bases de datos por tenant)
- **Servidor web:** Nginx (dentro de Docker)
- **Infraestructura:** Docker Compose
- **Proxy inverso:** Nginx Proxy Manager (NPM)
- **Tunel seguro:** Cloudflare Tunnel (expone servidor local a Internet)

### Dominios Configurados:
- **Dominio base:** `fact.rog.pe` (funciona correctamente)
- **Subdominios tenants:** `demo1.fact.rog.pe`, `demo2.fact.rog.pe`, `demo3.fact.rog.pe` (NO funcionan actualmente)

---

## ARQUITECTURA GENERAL

```
Internet (Usuario)
     |
     | HTTPS
     v
[Cloudflare CDN + DNS]
     |
     | Cloudflare Tunnel (tunel-rog.pe)
     v
[Servidor Local: 192.168.1.100:8080]
     |
     | HTTP
     v
[Nginx Proxy Manager] (opcional en tu caso)
     |
     | HTTP (puerto 8080)
     v
[Docker Container: nginx1]
     |
     | FastCGI
     v
[Docker Container: fpm1 - PHP-FPM 7.4]
     |
     | Laravel Multi-Tenant
     v
[hyn/multi-tenant Package]
     |
     |---> Identifica hostname (fact.rog.pe, demo1.fact.rog.pe, etc.)
     |---> Busca en BD sistema (tenancy.hostnames)
     |---> Conecta a BD especifica del tenant
     |---> Ejecuta logica de negocio
     |
     v
[MariaDB Container: mariadb1]
     |
     |---> Base de datos sistema: "smart1" (tabla hostnames, websites, clients)
     |---> Base de datos tenant 1: "tenancy_XXXXX"
     |---> Base de datos tenant 2: "tenancy_YYYYY"
     |---> Base de datos tenant 3: "tenancy_ZZZZZ"
```

---

## FLUJO COMPLETO DE UNA PETICION

### ESCENARIO: Usuario accede a `https://demo1.fact.rog.pe`

#### FASE 1: DNS Y CLOUDFLARE
```
1. Usuario escribe en navegador: https://demo1.fact.rog.pe
2. Navegador consulta DNS de Cloudflare
3. Cloudflare resuelve a su red CDN (IP publica de Cloudflare)
4. Cloudflare Tunnel intercepta la peticion
   - Tunel configurado: "tunel-rog.pe"
   - Hostname: demo1.fact.rog.pe
   - Destino: http://192.168.1.100:8080
```

#### FASE 2: TRANSPORTE LOCAL
```
5. Cloudflare Tunnel envia la peticion a traves del tunel seguro
   - Origen: Cloudflare Edge Server
   - Destino: Agente de Cloudflare corriendo en tu servidor local
   - Protocolo: HTTPS encriptado (dentro del tunel)

6. Agente de Cloudflare en servidor local recibe peticion
   - Desencripta
   - Reenvia a http://192.168.1.100:8080
```

#### FASE 3: NGINX PROXY MANAGER (CRÍTICO - CONFIGURACIÓN FUNCIONANDO)
```
7. Nginx Proxy Manager recibe la peticion:
   - Recibe: http://192.168.1.100:8080
   - Aplica headers críticos (configuración probada):
     * Host: demo1.fact.rog.pe
     * X-Real-IP: IP_REAL_USUARIO
     * X-Forwarded-For: IP_USUARIO, IP_CLOUDFLARE, IP_NPM
     * X-Forwarded-Proto: https (CRÍTICO para evitar redirect loops)
     * X-Forwarded-Ssl: on (CRÍTICO para Laravel)
     * X-Forwarded-Host: demo1.fact.rog.pe
     * X-Forwarded-Port: 443
   - Reenvia a contenedor Docker nginx1
```

#### FASE 4: DOCKER NGINX
```
8. Contenedor nginx1 recibe peticion
   - Puerto: 8080 (mapeado desde host)
   - Configuracion Nginx lee:
     * server_name (debe aceptar *.fact.rog.pe)
     * fastcgi_param HTTP_HOST (debe pasar demo1.fact.rog.pe)
   - Pasa a PHP-FPM via socket/puerto 9000
```

#### FASE 5: PHP-FPM Y LARAVEL
```
9. Contenedor fpm1 (PHP-FPM 7.4) recibe
   - Ejecuta: public/index.php
   - Laravel arranca

10. Middleware de Laravel procesa:
    - app/Http/Kernel.php
    - Middleware global no tiene tenancy
    - Middleware web tiene LockedAdmin, LockedUser
```

#### FASE 6: HYN/MULTI-TENANT - IDENTIFICACION
```
11. hyn/multi-tenant se activa ANTES de las rutas
    - Configuracion: config/tenancy.php
      * early-identification: true
      * auto-identification: true

12. Middleware: Hyn\Tenancy\Middleware\EagerIdentification
    - Lee $_SERVER['HTTP_HOST'] = "demo1.fact.rog.pe"
    - Busca en BD sistema (smart1):

      SELECT * FROM hostnames WHERE fqdn = 'demo1.fact.rog.pe'

    - Si encuentra:
      * Obtiene website_id asociado
      * Conecta a base de datos del tenant
      * Cambia conexion DB de "system" a "tenant"

    - Si NO encuentra:
      * FALLA AQUI (esto es tu problema actual)
      * Puede retornar 404 o usar dominio default
```

#### FASE 7: RUTAS DE LARAVEL
```
13. routes/web.php linea 63-65:

    $hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);
    if ($hostname) {
        Route::domain($hostname->fqdn)->group(function () use ($hostname) {
            // TODAS las rutas del tenant
        });
    }

    - Si $hostname es NULL = NO SE REGISTRAN RUTAS = 404
    - Si $hostname existe = Rutas disponibles
```

#### FASE 8: CONTROLADOR Y RESPUESTA
```
14. Laravel ejecuta controlador
15. Controlador accede a BD del tenant especifico
16. Genera respuesta (HTML, JSON, etc.)
17. Retorna a traves de toda la cadena en reversa
18. Usuario recibe respuesta en navegador
```

---

## COMPONENTES DEL SISTEMA

### 1. BASE DE DATOS (MariaDB)

#### Base de Datos Sistema: `smart1`
```sql
-- Tabla hostnames (gestionada por hyn/multi-tenant)
CREATE TABLE hostnames (
                          id INT PRIMARY KEY AUTO_INCREMENT,
                          fqdn VARCHAR(255) UNIQUE,  -- Ej: "fact.rog.pe", "demo1.fact.rog.pe"
                          website_id INT,            -- FK a tabla websites
                          created_at TIMESTAMP,
                          updated_at TIMESTAMP
);

-- Tabla websites (gestionada por hyn/multi-tenant)
CREATE TABLE websites (
                         id INT PRIMARY KEY AUTO_INCREMENT,
                         uuid VARCHAR(32) UNIQUE,   -- Identificador unico del tenant
                         created_at TIMESTAMP,
                         updated_at TIMESTAMP
);

-- Tabla clients (especifica de Facturador Smart)
CREATE TABLE clients (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        hostname_id INT,           -- FK a hostnames
                        number VARCHAR(50),
                        name VARCHAR(255),
                        email VARCHAR(255),
                        plan_id INT,
                        locked BOOLEAN,
                        locked_tenant BOOLEAN,
                        created_at TIMESTAMP,
                        updated_at TIMESTAMP
);
```

#### Bases de Datos Tenants: `tenancy_XXXXXX`
- Cada tenant tiene su propia base de datos
- Nombre: `PREFIX_DATABASE` + `_` + `uuid`
- Ejemplo: `tenancy_abc123def456`
- Contiene todas las tablas de negocio:
   - `documents` (facturas, boletas)
   - `items` (productos)
   - `persons` (clientes)
   - `companies` (datos empresa)
   - `inventories` (stock)
   - etc.

### 2. DOCKER COMPOSE

#### Archivo: `docker-compose.yml` (stack-facturador-smart/smart1/docker-compose.yml)

```yaml
# stack-facturador-smart/smart1/docker-compose.yml  v2.2
services:
  # NGINX - Servidor Web
  nginx1:
    build:
      context: .
      dockerfile: ../.docker/nginx/Dockerfile.nginx
    #image: rash07/nginx
    container_name: nginx1
    working_dir: /var/www/html
    ports:
      - "8080:80"
    environment:
      VIRTUAL_HOST: fact.solucionessystem.com,*.fact.solucionessystem.com
    volumes:
      - ./:/var/www/html
      - ../.docker/nginx/conf.d:/etc/nginx/conf.d
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'
      - 'com.docker.compose.service=ngnix'
    healthcheck:
      test: ["CMD", "nginx", "-t"]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 10s

  # PHP-FPM - Procesador PHP
  fpm1:
    container_name: fpm1
    build:
      context: .
      dockerfile: ../.docker/php/Dockerfile.fpm74
    # image: rash07/php-fpm:7.4
    working_dir: /var/www/html
    volumes:
      - ./ssh:/root/.ssh
      - ./ssh:/var/www/.ssh
      - ./:/var/www/html
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'
      - 'com.docker.compose.service=fpmphp'
    healthcheck:
      test: ["CMD", "php", "-r", "echo 'OK';"]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 10s

  # MariaDB - Base de Datos
  mariadb1:
    container_name: mariadb1
    image: mariadb:10.5.6
    environment:
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_PORT_HOST: ${MYSQL_PORT_HOST}
      MYSQL_ALLOW_EMPTY_PASSWORD: "no"
    volumes:
      - ./healthcheck-my.cnf:/root/.my.cnf:ro
      - mysqldata1:/var/lib/mysql
    ports:
      - "${MYSQL_PORT_HOST}:3306"
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'
      - 'com.docker.compose.service=mariadb'
    healthcheck:
      test: [ "CMD", "mysqladmin", "ping", "-h", "localhost", "--silent" ]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 10s

  # Redis - Cache
  redis1:
    container_name: redis1
    image: redis:alpine
    volumes:
      - redisdata1:/data
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'

  # Scheduling - Tareas programadas Laravel
  scheduling1:
    container_name: scheduling1
    image: rash07/scheduling
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'
      - 'com.docker.compose.service=scheduling'

  # Supervisor - Colas de trabajo
  supervisor1:
    container_name: supervisor1
    image: rash07/php7.4-supervisor
    #build:
    #    context: .
    #    dockerfile: ../.docker/php_supervisor/Dockerfile.supervisor81
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ../.docker/php_supervisor/supervisor.7.4.conf:/etc/supervisor/conf.d/supervisor.conf
    restart: always
    labels:
      - 'com.docker.compose.stack=principal'
      - 'com.docker.compose.project=facturador'
      - 'com.docker.compose.network=proxynet'
      - 'com.docker.compose.service=supervisor'

networks:
  default:
    name: proxynet
    external: true

volumes:
  redisdata1:
    driver: local
  mysqldata1:
    driver: local
```

#### Características y Configuración:

##### **Servicio nginx1:**
- **Build:** Usa Dockerfile customizado (`../.docker/nginx/Dockerfile.nginx`) para construir imagen con librerías específicas.
- **Container name:** `nginx1` (nombre fijo para referencias).
- **Puerto 8080:** Expone puerto 8080 del host (mapeado a puerto 80 dentro del contenedor).
- **VIRTUAL_HOST:** Define dominios reconocidos (`fact.solucionessystem.com`, `*.fact.solucionessystem.com`).
- **Volumen conf.d:** Monta configuración desde `../.docker/nginx/conf.d` (contiene `nginx.conf` con `server_name`, PHP-FPM routing, y optimizaciones).
- **Healthcheck:** Valida configuración Nginx cada 30s usando `nginx -t`.

##### **Servicio fpm1:**
- **Build:** Usa Dockerfile customizado (`../.docker/php/Dockerfile.fpm74`) para PHP 7.4 con extensiones necesarias.
- **SSH volumes:** Monta claves SSH (`./ssh`) para operaciones que requieren autenticación (ej: git, conexiones remotas).
- **Healthcheck:** Verifica que PHP-FPM responde cada 30s.

##### **Servicio mariadb1:**
- **Variables de entorno:** Lee credenciales desde `.env` (`${MYSQL_USER}`, `${MYSQL_PASSWORD}`, etc.) en lugar de valores hardcodeados.
- **Healthcheck:** Usa `mysqladmin ping` para validar estado de base de datos.
- **Puerto dinámico:** Monta desde `${MYSQL_PORT_HOST}` (configurable en `.env`).

##### **Servicios scheduling1 y supervisor1:**
- **Colas y tareas programadas:** Se ejecutan en contenedores separados para procesar jobs de Laravel en background.
- **Volúmenes compartidos:** Acceden a la misma raíz de la aplicación para evitar sincronización.

##### **Red proxynet:**
- **Externa:** `external: true` significa que debe crear la red manualmente o está compartida con otros stacks (NPM, Cloudflare Tunnel).
- **Comando para crear:** `docker network create proxynet` (si no existe).

##### **Labels:**
- Metadatos para identificar stack (`principal`), proyecto (`facturador`), red y servicio.
- Útiles para scripts de automatización, monitoreo y orquestación.

---

## SISTEMA MULTI-TENANT

### Archivo: `config/tenancy.php`

```php
return [
    'hostname' => [
        // Hostname por defecto si no se encuentra
        'default' => env('TENANCY_DEFAULT_HOSTNAME'),  // null en tu caso

        // Identificacion automatica del tenant
        'auto-identification' => env('TENANCY_AUTO_HOSTNAME_IDENTIFICATION', true),

        // Identificacion temprana (antes de rutas)
        'early-identification' => env('TENANCY_EARLY_IDENTIFICATION', true),

        // Abortar si no se identifica hostname
        'abort-without-identified-hostname' => env('TENANCY_ABORT_WITHOUT_HOSTNAME', false),

        // Cache de hostnames (10 minutos)
        'cache' => 10,
    ],

    'db' => [
        // Conexion por defecto
        'default' => env('TENANCY_DEFAULT_CONNECTION'),  // null

        // Nombres de conexiones
        'system-connection-name' => 'system',  // BD sistema
        'tenant-connection-name' => 'tenant',  // BD tenant

        // Modo de division (database = 1 BD por tenant)
        'tenant-division-mode' => 'database',

        // Prefijo para BDs tenants
        'PREFIX_DATABASE' => 'tenancy',  // desde .env
    ],
];
```

### Archivo: `routes/web.php` (lineas 63-65)

```php
use Hyn\Tenancy\Contracts\CurrentHostname;

$hostname = app(CurrentHostname::class);

if ($hostname) {
    // Si se identifico un tenant, registrar rutas dentro del dominio
    Route::domain($hostname->fqdn)->group(function () use ($hostname) {

        Auth::routes(['register' => false, 'verify' => false]);

        // Dashboard
        Route::middleware(['auth', 'locked.tenant'])->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('/dashboard', [DashboardController::class, 'index']);
            // ... todas las rutas tenant
        });
    });
}
// Si $hostname es NULL, NO se registran rutas = 404
```

### Variables de Entorno: `.env`

```ini
APP_URL_BASE=fact.rog.pe
APP_URL=http://fact.rog.pe

DB_CONNECTION=system
DB_HOST=mariadb1
DB_DATABASE=smart1          # BD sistema

PREFIX_DATABASE=tenancy     # Prefijo para BDs tenants

LIMIT_UUID_LENGTH_32=true
TENANCY_DATABASE_AUTO_DELETE=true
TENANCY_DATABASE_AUTO_DELETE_USER=true

# IMPORTANTE: No hay variables para subdominios especificos
```

---

## CONFIGURACION DE CLOUDFLARE TUNNEL

### Panel de Cloudflare Tunnel

**Nombre del Tunel:** `tunel-rog.pe`

**Estado:** CORRECTO (Online)

### Configuracion Actual

| Subdominio | Dominio  | Tipo | URL                         | Status | Problema          |
|------------|----------|------|-----------------------------|--------|-------------------|
| fact       | rog.pe   | HTTP | http://192.168.1.100:8080   | Online | Funciona          |

### Configuracion Esperada (Lo que falta)

Para que funcionen los subdominios, necesitas agregar:

| Subdominio | Dominio  | Tipo | URL                         | Status |
|------------|----------|------|------------------------------|--------|
| fact       | rog.pe   | HTTP | http://192.168.1.100:8080    | Online |
| demo1      | fact.rog.pe | HTTP | http://192.168.1.100:8080 | Falta  |
| demo2      | fact.rog.pe | HTTP | http://192.168.1.100:8080 | Falta  |
| demo3      | fact.rog.pe | HTTP | http://192.168.1.100:8080 | Falta  |

**O MEJOR AUN (Wildcard):**

| Subdominio | Dominio  | Tipo | URL                         | Status |
|------------|----------|------|------------------------------|--------|
| *          | fact.rog.pe | HTTP | http://192.168.1.100:8080 | Falta  |

### Configuracion de Headers (Advanced Settings)

**Custom Nginx Configuration:**

```nginx
# Headers para identificar el protocolo HTTPS original
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Ssl on;
proxy_set_header X-Forwarded-Host $host;
proxy_set_header X-Forwarded-Port $server_port;
```

### Analisis de la Configuracion Actual

**PROBLEMA IDENTIFICADO:**

Cloudflare Tunnel solo esta configurado para:
- `fact.rog.pe` → `http://192.168.1.100:8080`

**Pero NO para:**
- `demo1.fact.rog.pe` → Sin configuracion
- `demo2.fact.rog.pe` → Sin configuracion
- `demo3.fact.rog.pe` → Sin configuracion

**RESULTADO:**
Cuando alguien intenta acceder a `demo1.fact.rog.pe`:
1. DNS de Cloudflare resuelve correctamente (si existe registro DNS)
2. Cloudflare CDN recibe la peticion
3. Cloudflare Tunnel NO encuentra configuracion para `demo1.fact.rog.pe`
4. Cloudflare retorna error o pagina de Cloudflare

**La peticion NUNCA llega a tu servidor local.**

---

## CONFIGURACION DE NGINX PROXY MANAGER

### Configuracion Recomendada

Si estas usando Nginx Proxy Manager entre Cloudflare y Docker:

**Proxy Host:**
- **Domain Names:** `fact.rog.pe`, `*.fact.rog.pe`
- **Scheme:** `http`
- **Forward Hostname/IP:** IP del contenedor nginx1 o `192.168.1.100`
- **Forward Port:** `8080`

**Advanced (CONFIGURACION COMPLETA FUNCIONANDO):**

```nginx
# ============================================================================
# HEADERS DE PROXY - CONFIGURACIÓN COMPLETA PARA WORDPRESS/LARAVEL
# ============================================================================

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 1. HEADERS BÁSICOS DE IDENTIFICACIÓN                                    │
# └─────────────────────────────────────────────────────────────────────────┘

# Host: Dominio real que el usuario escribió en el navegador
# Sin esto: Laravel ve "litespeed:8000"
# Con esto: Laravel ve "fact.rog.pe"
proxy_set_header Host $host;

# X-Real-IP: IP real del visitante (no la del proxy)
# Sin esto: Laravel ve "172.20.0.9" (IP de NPM)
# Con esto: Laravel ve "187.189.200.100" (IP real del usuario)
# ► Usado por: Analytics, seguridad, geolocalización, logs
proxy_set_header X-Real-IP $remote_addr;

# X-Forwarded-For: Lista completa de IPs en la cadena de proxies
# Mantiene trazabilidad: Usuario → Cloudflare → NPM → Docker
# Valor: "187.189.200.100, 104.21.50.25, 172.20.0.9"
# ► Usado por: CDN, debugging, compliance, auditorías
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 2. HEADERS DE PROTOCOLO HTTPS (CRÍTICOS PARA EVITAR REDIRECT LOOPS)    │
# └─────────────────────────────────────────────────────────────────────────┘

# X-Forwarded-Proto: Protocolo original (http o https)
# ► CRÍTICO: Sin esto → Redirect Loop infinito 🔄
# Le dice a Laravel: "El usuario se conectó por HTTPS"
# Aunque NPM → Docker sea HTTP, Laravel sabe que es HTTPS al final
proxy_set_header X-Forwarded-Proto $scheme;

# X-Forwarded-Ssl: Específico para aplicaciones PHP
# Alternativa/complemento a X-Forwarded-Proto
# Valores: "on" si es HTTPS, vacío si es HTTP
# ► Laravel lo chequea específicamente para detectar HTTPS
proxy_set_header X-Forwarded-Ssl on;

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 3. HEADERS DE HOST Y PUERTO                                             │
# └─────────────────────────────────────────────────────────────────────────┘

# X-Forwarded-Host: Host original (útil con múltiples proxies)
# Similar a Host, pero mantiene el valor original en cadenas largas
# ► Usado por: Configuraciones multi-dominio, subdominios
proxy_set_header X-Forwarded-Host $host;

# X-Forwarded-Port: Puerto original donde el usuario se conectó
# Sin esto: Laravel genera URLs como "https://fact.rog.pe:8000" ❌
# Con esto: Laravel genera URLs como "https://fact.rog.pe" ✅
# ► Evita: Puertos extraños en URLs, errores en redirects
proxy_set_header X-Forwarded-Port $server_port;
```

**NOTA IMPORTANTE:**

Esta configuración ha sido **PROBADA Y FUNCIONA CORRECTAMENTE** en el servidor. Los headers son críticos para:

1. **Evitar redirect loops infinitos** (X-Forwarded-Proto, X-Forwarded-Ssl)
2. **Identificar correctamente el hostname** para multi-tenant (Host, X-Forwarded-Host)
3. **Mantener la IP real del usuario** para logs y seguridad (X-Real-IP, X-Forwarded-For)
4. **Generar URLs correctas** sin puertos extraños (X-Forwarded-Port)

---

## COMO FUNCIONA EL ENRUTAMIENTO DE SUBDOMINIOS

### Configuracion Necesaria en Cloudflare DNS

Para que los subdominios funcionen, necesitas registros DNS:

```
Tipo    Nombre              Contenido           Proxy   TTL
----    ------              ---------           -----   ---
CNAME   fact.rog.pe         tunel-rog.pe        Si      Auto
CNAME   *.fact.rog.pe       tunel-rog.pe        Si      Auto
```

O especificos:

```
CNAME   fact                tunel-rog.pe        Si      Auto
CNAME   demo1.fact          tunel-rog.pe        Si      Auto
CNAME   demo2.fact          tunel-rog.pe        Si      Auto
CNAME   demo3.fact          tunel-rog.pe        Si      Auto
```

### Configuracion Necesaria en Cloudflare Tunnel

#### Opcion 1: Wildcard (RECOMENDADO)

```yaml
# En configuracion del tunel (archivo .yml o interfaz web)

ingress:
  - hostname: "*.fact.rog.pe"
    service: http://192.168.1.100:8080

  - hostname: "fact.rog.pe"
    service: http://192.168.1.100:8080

  - service: http_status:404
```

#### Opcion 2: Subdominios Especificos

```yaml
ingress:
  - hostname: "fact.rog.pe"
    service: http://192.168.1.100:8080

  - hostname: "demo1.fact.rog.pe"
    service: http://192.168.1.100:8080

  - hostname: "demo2.fact.rog.pe"
    service: http://192.168.1.100:8080

  - hostname: "demo3.fact.rog.pe"
    service: http://192.168.1.100:8080

  - service: http_status:404
```

### Configuracion Nginx (dentro del contenedor)

**Ubicación:** `stack-facturador-smart/.docker/nginx/conf.d/nginx.conf`

**Montado en contenedor:** `/etc/nginx/conf.d/nginx.conf` (volumen `../.docker/nginx/conf.d:/etc/nginx/conf.d` en docker-compose.yml)

```nginx
# =============================================================================
# CONFIGURACIÓN NGINX PARA LARAVEL - SMART FACTURADOR
# =============================================================================
#
# Este archivo configura un servidor Nginx optimizado para aplicaciones Laravel
# con PHP-FPM en entornos Docker. Proporciona seguridad, rendimiento y
# compatibilidad con el stack de facturación.

server {
    # -------------------------------------------------------------------------
    # CONFIGURACIÓN BÁSICA DEL SERVIDOR
    # -------------------------------------------------------------------------

    # Escucha en el puerto 80 (HTTP) como servidor por defecto
    listen 80 default_server;

    # Directorio raíz de la aplicación Laravel
    root /var/www/html/public;

    # Archivos de índice en orden de preferencia
    index index.html index.htm index.php;

    # Acepta nombres de servidor (wildcard) para multi-tenant
    # Ajustar al dominio base usado por la aplicación
    server_name fact.solucionessystem.com *.fact.solucionessystem.com;

    # Codificación de caracteres UTF-8 para soporte internacional
    charset utf-8;

    # Oculta la versión de Nginx en las cabeceras (seguridad)
    server_tokens off;

    # -------------------------------------------------------------------------
    # GESTIÓN DE ARCHIVOS ESTATICOS
    # -------------------------------------------------------------------------

    # Favicons: archivos pequeños, no registrar accesos para mejorar performance
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    # Archivo robots.txt: no registrar accesos
    location = /robots.txt {
        log_not_found off;
        access_log off;
    }

    # -------------------------------------------------------------------------
    # RUTEO LARAVEL (Front Controller Pattern)
    # -------------------------------------------------------------------------

    # Ruta principal: implementa el patrón Front Controller de Laravel
    # Intenta servir archivos estáticos, si no existen, enruta a index.php
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # -------------------------------------------------------------------------
    # CONFIGURACIÓN PHP-FPM
    # -------------------------------------------------------------------------

    # Procesamiento de archivos PHP mediante PHP-FPM
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;

        # Conexión al contenedor PHP-FPM
        fastcgi_pass fpm1:9000;
        fastcgi_index index.php;

        # Parámetros FastCGI
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Parámetros básicos
        include fastcgi_params;

        # CRÍTICO: Pasar el host original al backend PHP-FPM (necesario para hyn/multi-tenant)
        # Sin estos parámetros, Laravel no identificará el hostname del tenant
        fastcgi_param HTTP_HOST $host;
        fastcgi_param SERVER_NAME $host;

        # Timeout extendido para procesos largos (1 hora)
        # Ideal para facturación electrónica y procesos pesados
        fastcgi_read_timeout 3600;

        # Buffers y timeouts adicionales para mejor rendimiento
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 60;
    }

    # -------------------------------------------------------------------------
    # SEGURIDAD - PROTECCIÓN DE DIRECTORIOS
    # -------------------------------------------------------------------------

    # Bloquea acceso a archivos PHP/HTML en el directorio storage
    # Previene ejecución no autorizada de código
    location ~ /storage/.*\.(php|html)$ {
        deny all;
        return 403;
    }

    # Protege archivos ocultos (.htaccess, .env, etc.)
    location ~ /\.ht {
        deny all;
    }

    # -------------------------------------------------------------------------
    # MANEJO DE ERRORES
    # -------------------------------------------------------------------------

    # Página de error 404: enrutar a Laravel para manejo centralizado
    error_page 404 /index.php;

    # -------------------------------------------------------------------------
    # OPTIMIZACIONES ADICIONALES
    # -------------------------------------------------------------------------

    # Cacheo de archivos estáticos (imágenes, CSS, JS)
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Compresión Gzip (mejora performance)
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;
}
```

**Puntos críticos de la configuración:**

1. **`server_name fact.solucionessystem.com *.fact.solucionessystem.com;`**
   - Acepta el dominio base y todos los subdominios (wildcard).
   - Cambiar según tu dominio real.

2. **`root /var/www/html/public;`**
   - Apunta a la carpeta `public/` de Laravel (necesario para seguridad).

3. **`try_files $uri $uri/ /index.php$is_args$args;`**
   - Implementa el patrón Front Controller de Laravel.
   - Todas las peticiones que no sean archivos/directorios se enrutan a `index.php`.

4. **`fastcgi_pass fpm1:9000;`**
   - Conecta con el contenedor PHP-FPM llamado `fpm1` en el puerto 9000 (comunicación interna Docker).

5. **`fastcgi_param HTTP_HOST $host;` y `fastcgi_param SERVER_NAME $host;`**
   - **CRÍTICO para multi-tenant:** Pasa el hostname original (ej: `demo1.fact.solucionessystem.com`) a PHP-FPM.
   - Sin estos parámetros, `hyn/multi-tenant` no puede identificar el tenant.
   - Laravel recibe el valor en `$_SERVER['HTTP_HOST']`.

6. **`fastcgi_read_timeout 3600;`**
   - Timeout de 1 hora para procesos pesados (facturación electrónica, reportes grandes, etc.).

7. **Protección de directorios:**
   - Bloquea ejecución de PHP en `storage/`.
   - Protege archivos ocultos (`.env`, `.htaccess`).

8. **Cacheo y compresión:**
   - Cachea archivos estáticos por 1 año.
   - Compresión Gzip para optimizar transferencias.

### Base de Datos: Registros de Hostnames

Para que Laravel identifique los subdominios, deben existir en la BD:

```sql
-- Conectar a BD sistema
USE smart1;

-- Ver hostnames registrados
SELECT h.id, h.fqdn, h.website_id, w.uuid, c.name as client_name
FROM hostnames h
JOIN websites w ON h.website_id = w.id
JOIN clients c ON c.hostname_id = h.id;
```

**Resultado esperado:**

```
id  | fqdn                | website_id | uuid             | client_name
----+---------------------+------------+------------------+-------------
1   | fact.rog.pe         | 1          | abc123def456     | Cliente Demo
2   | demo1.fact.rog.pe   | 2          | xyz789uvw012     | Demo 1
3   | demo2.fact.rog.pe   | 3          | mno345pqr678     | Demo 2
4   | demo3.fact.rog.pe   | 4          | stu901vwx234     | Demo 3
```

**Si los subdominios NO existen en esta tabla, Laravel NO los reconocera.**

---

## DIAGNOSTICO DEL PROBLEMA CON SUBDOMINIOS

### Sintomas Actuales

- `https://fact.rog.pe/` → **FUNCIONA**
- `https://demo1.fact.rog.pe/` → **NO FUNCIONA**
- `https://demo2.fact.rog.pe/` → **NO FUNCIONA**
- `https://demo3.fact.rog.pe/` → **NO FUNCIONA**

### Posibles Causas (en orden de probabilidad)

#### CAUSA 1: Cloudflare Tunnel NO configurado para subdominios

**Verificacion:**

Panel de Cloudflare Tunnel > tunel-rog.pe > Public Hostname

Si solo ves:a
```
fact.rog.pe → http://192.168.1.100:8080
```

Y NO ves:
```
*.fact.rog.pe → http://192.168.1.100:8080
```

**ESTE ES EL PROBLEMA PRINCIPAL.**

**Como verificar:**
```bash
# Desde cualquier PC con internet
curl -I https://demo1.fact.rog.pe

# Si retorna error de Cloudflare = Problema en Cloudflare Tunnel
# Si retorna error 404 de Laravel = Problema en BD/Laravel
```

#### CAUSA 2: DNS de Cloudflare no configurado

**Verificacion:**

Panel de Cloudflare > DNS > Records

Busca:
```
CNAME   *.fact   o   CNAME   demo1.fact
```

Si no existe, las peticiones no llegan a Cloudflare Tunnel.

**Como verificar:**
```bash
# Desde cualquier PC
nslookup demo1.fact.rog.pe

# Debe retornar IP de Cloudflare
```

#### CAUSA 3: Registros faltantes en BD sistema

**Verificacion:**

Conectar a MariaDB:
```bash
docker exec -it [container_mariadb] mysql -u root -p
```

```sql
USE smart1;
SELECT * FROM hostnames WHERE fqdn LIKE '%.fact.rog.pe';
```

Si solo retorna `fact.rog.pe` y NO `demo1.fact.rog.pe`, Laravel no reconocera los subdominios.

#### CAUSA 4: Nginx no acepta subdominios

**Verificacion:**

Ver logs de Nginx:
```bash
docker logs nginx1 --tail 100
```

Buscar errores relacionados con `demo1.fact.rog.pe`.

**Revisar configuracion:**
```bash
docker exec nginx1 cat /etc/nginx/sites-available/default
```

Buscar linea `server_name`. Debe incluir:
```nginx
server_name fact.rog.pe *.fact.rog.pe;
```

#### CAUSA 5: Configuracion early-identification deshabilitada

**Verificacion:**

Archivo `config/tenancy.php`, linea 144:
```php
'early-identification' => env('TENANCY_EARLY_IDENTIFICATION', true),
```

Archivo `.env`:
```
TENANCY_EARLY_IDENTIFICATION=true  # Debe ser true o no estar (usa default)
```

---

## SOLUCION IMPLEMENTADA Y FUNCIONANDO

### ✅ CONFIGURACIÓN DE HEADERS EN NGINX PROXY MANAGER

**PROBLEMA RESUELTO:** Los subdominios no funcionaban debido a headers incorrectos entre Nginx Proxy Manager y Laravel.

**SOLUCIÓN APLICADA:** Configuración completa de headers en la sección "Advanced" de Nginx Proxy Manager.

#### Configuración Final (FUNCIONANDO):

```nginx
# ============================================================================
# HEADERS DE PROXY - CONFIGURACIÓN COMPLETA PARA LARAVEL MULTI-TENANT
# ============================================================================

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 1. HEADERS BÁSICOS DE IDENTIFICACIÓN                                    │
# └─────────────────────────────────────────────────────────────────────────┘

# Host: Dominio real que el usuario escribió en el navegador
# Sin esto: Laravel ve "litespeed:8000"
# Con esto: Laravel ve "fact.rog.pe"
proxy_set_header Host $host;

# X-Real-IP: IP real del visitante (no la del proxy)
# Sin esto: Laravel ve "172.20.0.9" (IP de NPM)
# Con esto: Laravel ve "187.189.200.100" (IP real del usuario)
# ► Usado por: Analytics, seguridad, geolocalización, logs
proxy_set_header X-Real-IP $remote_addr;

# X-Forwarded-For: Lista completa de IPs en la cadena de proxies
# Mantiene trazabilidad: Usuario → Cloudflare → NPM → Docker
# Valor: "187.189.200.100, 104.21.50.25, 172.20.0.9"
# ► Usado por: CDN, debugging, compliance, auditorías
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 2. HEADERS DE PROTOCOLO HTTPS (CRÍTICOS PARA EVITAR REDIRECT LOOPS)    │
# └─────────────────────────────────────────────────────────────────────────┘

# X-Forwarded-Proto: Protocolo original (http o https)
# ► CRÍTICO: Sin esto → Redirect Loop infinito 🔄
# Le dice a Laravel: "El usuario se conectó por HTTPS"
# Aunque NPM → Docker sea HTTP, Laravel sabe que es HTTPS al final
proxy_set_header X-Forwarded-Proto $scheme;

# X-Forwarded-Ssl: Específico para aplicaciones PHP
# Alternativa/complemento a X-Forwarded-Proto
# Valores: "on" si es HTTPS, vacío si es HTTP
# ► Laravel lo chequea específicamente para detectar HTTPS
proxy_set_header X-Forwarded-Ssl on;

# ┌─────────────────────────────────────────────────────────────────────────┐
# │ 3. HEADERS DE HOST Y PUERTO                                             │
# └─────────────────────────────────────────────────────────────────────────┘

# X-Forwarded-Host: Host original (útil con múltiples proxies)
# Similar a Host, pero mantiene el valor original en cadenas largas
# ► Usado por: Configuraciones multi-dominio, subdominios
proxy_set_header X-Forwarded-Host $host;

# X-Forwarded-Port: Puerto original donde el usuario se conectó
# Sin esto: Laravel genera URLs como "https://fact.rog.pe:8000" ❌
# Con esto: Laravel genera URLs como "https://fact.rog.pe" ✅
# ► Evita: Puertos extraños en URLs, errores en redirects
proxy_set_header X-Forwarded-Port $server_port;
```

#### Resultados Obtenidos:

✅ **Subdominios funcionando:** `demo1.fact.rog.pe`, `demo2.fact.rog.pe`, `demo3.fact.rog.pe`
✅ **Sin redirect loops:** Laravel detecta correctamente HTTPS
✅ **Multi-tenant funcionando:** hyn/multi-tenant identifica correctamente el hostname
✅ **URLs limpias:** Sin puertos extraños en las URLs generadas
✅ **IPs reales:** Logs muestran la IP real del usuario, no la del proxy

#### Cómo Aplicar Esta Configuración:

1. **Acceder a Nginx Proxy Manager:**
   - URL: `http://192.168.1.100:81` (o la IP de tu NPM)
   - Login con credenciales de administrador

2. **Editar Proxy Host:**
   - Ir a "Hosts" → Seleccionar el proxy host existente
   - Click en "Edit" (icono de lápiz)

3. **Configurar Advanced:**
   - Ir a la pestaña "Advanced"
   - Pegar la configuración completa de headers mostrada arriba
   - Click "Save"

4. **Verificar Funcionamiento:**
   - Probar acceso a `https://demo1.fact.rog.pe`
   - Verificar que no hay redirect loops
   - Comprobar que Laravel identifica correctamente el hostname

---

## SOLUCION PROPUESTA (PARA NUEVAS INSTALACIONES)

### PASO 1: Configurar Cloudflare Tunnel para Wildcard

#### Opcion A: Via Interfaz Web (Recomendado)

1. Ir a: **Cloudflare Dashboard** > **Zero Trust** > **Access** > **Tunnels**
2. Seleccionar tunel: `tunel-rog.pe`
3. Click en **Configure**
4. En seccion **Public Hostname**, click **Add a public hostname**
5. Configurar:
   - **Subdomain:** `*`
   - **Domain:** `fact.rog.pe`
   - **Type:** `HTTP`
   - **URL:** `192.168.1.100:8080`
6. En **Advanced settings**, pegar:
   ```nginx
   proxy_set_header Host $host;
   proxy_set_header X-Real-IP $remote_addr;
   proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
   proxy_set_header X-Forwarded-Proto https;
   proxy_set_header X-Forwarded-Host $host;
   ```
7. Click **Save hostname**

#### Opcion B: Via Archivo de Configuracion (Avanzado)

Si instalaste el tunel via CLI:

```bash
# Editar configuracion del tunel
nano ~/.cloudflared/config.yml
```

Agregar:
```yaml
tunnel: tunel-rog.pe
credentials-file: /path/to/credentials.json

ingress:
  # Wildcard para todos los subdominios
  - hostname: "*.fact.rog.pe"
    service: http://192.168.1.100:8080
    originRequest:
      noTLSVerify: true

  # Dominio principal
  - hostname: "fact.rog.pe"
    service: http://192.168.1.100:8080
    originRequest:
      noTLSVerify: true

  # Fallback
  - service: http_status:404
```

Reiniciar tunel:
```bash
sudo systemctl restart cloudflared
# o
cloudflared tunnel run tunel-rog.pe
```

### PASO 2: Configurar DNS en Cloudflare

1. Ir a: **Cloudflare Dashboard** > **DNS** > **Records**
2. Agregar registro wildcard:
   - **Type:** `CNAME`
   - **Name:** `*.fact`
   - **Target:** `tunel-rog.pe.cfargotunnel.com` (o la URL del tunel)
   - **Proxy status:** Proxied (nube naranja)
   - **TTL:** Auto
3. Click **Save**

### PASO 3: Verificar/Crear Registros en Base de Datos

Conectar a MariaDB:
```bash
docker exec -it smart1-mariadb1-1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp
```

```sql
USE smart1;

-- Verificar hostnames existentes
SELECT h.id, h.fqdn, h.website_id, w.uuid
FROM hostnames h
        JOIN websites w ON h.website_id = w.id;

-- Si los subdominios NO existen, necesitas crearlos via:
-- 1. Panel de administracion de Facturador Smart
-- 2. O crear manualmente (NO recomendado, mejor usar panel)
```

**IMPORTANTE:** NO crear registros manualmente. Usa el panel de administracion del sistema porque:
- Crea el registro en `websites`
- Crea el registro en `hostnames`
- Crea el registro en `clients`
- Crea la base de datos del tenant
- Ejecuta migraciones
- Ejecuta seeders

### PASO 4: Verificar Configuracion Nginx en Docker

Verificar que Nginx acepte wildcards:

```bash
# Ver configuracion actual
docker exec nginx1 cat /etc/nginx/sites-available/default

# Buscar linea server_name
```

Debe contener:
```nginx
server_name fact.rog.pe *.fact.rog.pe;
```

Si NO lo tiene, editar:
```bash
# Crear/editar configuracion
nano /home/cesar/stack-facturador-smart/proxy/fpms/smart1/default
```

Agregar:
```nginx
server {
    listen 80;
    server_name fact.rog.pe *.fact.rog.pe;

    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass fpm1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTP_HOST $host;
        fastcgi_param SERVER_NAME $host;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Reiniciar Nginx:
```bash
docker restart nginx1
```

### PASO 5: Limpiar Cache de Laravel

```bash
# Ejecutar dentro del contenedor fpm1
docker exec fpm1 php artisan config:clear
docker exec fpm1 php artisan cache:clear
docker exec fpm1 php artisan route:clear
docker exec fpm1 php artisan view:clear
```

### PASO 6: Verificar Funcionamiento

#### Prueba 1: DNS
```bash
nslookup demo1.fact.rog.pe
# Debe retornar IP de Cloudflare
```

#### Prueba 2: Acceso HTTP
```bash
curl -I https://demo1.fact.rog.pe
# Debe retornar 200 OK o 302 (redirect a login)
```

#### Prueba 3: Logs de Laravel
```bash
# Ver logs en tiempo real
docker exec fpm1 tail -f storage/logs/laravel.log
```

Acceder a `https://demo1.fact.rog.pe` y ver si aparecen errores.

#### Prueba 4: Verificar que Laravel identifica el hostname
```bash
# Crear ruta de prueba temporal
docker exec fpm1 php artisan tinker
```

En tinker:
```php
$hostname = app(\Hyn\Tenancy\Contracts\CurrentHostname::class);
dd($hostname);
```

### PASO 7: Solucion de Problemas

#### Si aun no funciona:

**A. Verificar que el tunel este corriendo:**
```bash
# En servidor local
ps aux | grep cloudflared
# Debe mostrar proceso activo
```

**B. Ver logs del tunel:**
```bash
journalctl -u cloudflared -f
# o
cat /var/log/cloudflared.log
```

**C. Verificar conectividad interna:**
```bash
# Desde servidor local
curl -H "Host: demo1.fact.rog.pe" http://localhost:8080
# Debe retornar HTML de Laravel
```

**D. Verificar Docker:**
```bash
docker-compose ps
# Todos los servicios deben estar "Up"
```

**E. Verificar logs de Nginx:**
```bash
docker logs nginx1 --tail 100 -f
```

---

## RESUMEN DE CONFIGURACIONES NECESARIAS

### Cloudflare DNS
```
CNAME   fact        tunel-rog.pe.cfargotunnel.com   Proxied
CNAME   *.fact      tunel-rog.pe.cfargotunnel.com   Proxied
```

### Cloudflare Tunnel (ingress)
```yaml
- hostname: "*.fact.rog.pe"
  service: http://192.168.1.100:8080

- hostname: "fact.rog.pe"
  service: http://192.168.1.100:8080
```

### Nginx (docker container)
```nginx
server_name fact.rog.pe *.fact.rog.pe;
fastcgi_param HTTP_HOST $host;
```

### Laravel (.env)
```ini
APP_URL_BASE=fact.rog.pe
PREFIX_DATABASE=tenancy
TENANCY_EARLY_IDENTIFICATION=true
TENANCY_AUTO_HOSTNAME_IDENTIFICATION=true
```

### MariaDB (tabla hostnames)
```sql
-- Debe contener registros para cada subdominio
INSERT INTO hostnames (fqdn, website_id) VALUES
                                            ('fact.rog.pe', 1),
                                            ('demo1.fact.rog.pe', 2),
                                            ('demo2.fact.rog.pe', 3);
```

---

## ARQUITECTURA FINAL FUNCIONANDO

```
Usuario escribe: https://demo1.fact.rog.pe
     ↓
[Cloudflare DNS] Resuelve a Cloudflare CDN
     ↓
[Cloudflare Tunnel] Detecta *.fact.rog.pe → 192.168.1.100:8080
     ↓
[Tunel Encriptado] Envia peticion a servidor local
     ↓
[Agente Cloudflare Local] Recibe y reenvia a puerto 8080
     ↓
[Nginx Proxy Manager] Recibe en puerto 8080
     Aplica headers críticos:
     - Host: demo1.fact.rog.pe
     - X-Forwarded-Proto: https
     - X-Forwarded-Ssl: on
     - X-Real-IP: IP_USUARIO
     ↓
[Docker: nginx1] Recibe en puerto 80 (mapeado desde 8080)
     server_name *.fact.rog.pe → Acepta demo1.fact.rog.pe
     ↓
[Docker: fpm1] Recibe via FastCGI
     Laravel arranca con $_SERVER['HTTP_HOST'] = "demo1.fact.rog.pe"
     ↓
[hyn/multi-tenant] Identifica hostname
     1. Busca en BD: SELECT * FROM hostnames WHERE fqdn='demo1.fact.rog.pe'
     2. Encuentra website_id = 2
     3. Conecta a BD: tenancy_xyz789uvw012
     ↓
[Laravel Router] Registra rutas dentro de Route::domain('demo1.fact.rog.pe')
     ↓
[Controlador] Ejecuta logica de negocio
     ↓
[Respuesta] HTML/JSON retorna al usuario
```

---

## COMANDOS UTILES PARA DIAGNOSTICO

### Ver configuracion actual de Cloudflare Tunnel
```bash
cloudflared tunnel info tunel-rog.pe
```

### Probar conectividad local
```bash
# Desde servidor local
curl -H "Host: demo1.fact.rog.pe" http://localhost:8080

# Desde internet
curl -I https://demo1.fact.rog.pe
```

### Ver logs en tiempo real
```bash
# Nginx
docker logs nginx1 -f

# PHP-FPM
docker logs fpm1 -f

# MariaDB
docker logs mariadb1 -f

# Laravel
docker exec fpm1 tail -f storage/logs/laravel.log
```

### Verificar BDs tenants
```bash
docker exec -it smart1-mariadb1-1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e "SHOW DATABASES LIKE 'tenancy%';"
```

### Limpiar todo el cache
```bash
docker exec fpm1 php artisan optimize:clear
docker exec fpm1 php artisan config:cache
docker restart nginx1
```

---

## Proceso al crear un tenant (qué hace el sistema)

Cuando se crea un nuevo tenant desde el panel administrativo (o via comandos de `hyn/multi-tenant`), el sistema realiza una serie de acciones automáticas para provisionar el tenant. Documentar estas acciones ayuda a entender los cambios en la base de datos y en el sistema de ficheros.

- 1) Crear registro en la tabla `websites` (entidad tenant) con `uuid`.
- 2) Crear registro en la tabla `hostnames` con el `fqdn` (ej: `demo1.fact.solucionessystem.com`) y referenciar el `website_id`.
- 3) Crear la base de datos del tenant (si `auto-create-tenant-database` = true). Nombre: `PREFIX_DATABASE_uuid` (ej: `tenancy_ventas`).
- 4) Crear el usuario de base de datos específico del tenant (si `auto-create-tenant-database-user` = true).
- 5) Ejecutar migraciones y seeders en la base de datos del tenant (`tenant-migrations-path` y `tenant-seed-class`).
- 6) Crear directorios locales específicos del tenant (si `auto-create-tenant-directory` = true). Por defecto se crean bajo `storage/app/tenancy/tenants/<database>` o similar según la configuración del paquete.
- 7) Ajustar permisos y ownership de los ficheros/directorios recién creados para que PHP-FPM pueda escribirlos.
- 8) Registrar acciones en logs de Laravel (y en logs del sistema) indicando creación de directorios y recursos.

Ejemplo de eventos en disco observados en `storage/app` al crear un tenant (timestamp relativo a tu entorno):

```
2026-01-03 04:40:35 /home/cesar/docker-stacks/project_fact_soluciones/stack-facturador-smart/smart1/storage/app/tenancy CREATE,ISDIR
2026-01-03 04:40:35 /home/cesar/docker-stacks/project_fact_soluciones/stack-facturador-smart/smart1/storage/app/tenancy/tenants/tenancy_ventas CREATE,ISDIR
```

Notas prácticas:

- Verifica en la BD `smart1` que existe la fila en `hostnames` para el `fqdn` del tenant.
- Comprueba que la nueva base de datos `tenancy_<uuid>` aparece en MariaDB.
- Revisa `storage/logs/laravel.log` para entradas relacionadas con la creación y migraciones del tenant.
- Si los directorios no aparecen, revisa la configuración `tenancy.php` (`auto-create-tenant-directory`, `tenant-migrations-path`) y permisos del volumen montado.

Si quieres, puedo añadir un pequeño script/chequeo para listar automáticamente los recursos creados tras la creación de un tenant y guardar un resumen en `docs/tenant_provisioning.md`.

---

## CONCLUSION

### ✅ PROBLEMA RESUELTO

El problema principal era que **Nginx Proxy Manager no estaba configurado correctamente** para pasar los headers necesarios a Laravel, causando que:

1. **Laravel no detectara HTTPS** → Redirect loops infinitos
2. **hyn/multi-tenant no identificara el hostname** → Subdominios no funcionaban
3. **URLs generadas incorrectas** → Puertos extraños en las URLs

### ✅ SOLUCIÓN IMPLEMENTADA

**Configuración de headers en Nginx Proxy Manager (Advanced):**

```nginx
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Ssl on;
proxy_set_header X-Forwarded-Host $host;
proxy_set_header X-Forwarded-Port $server_port;
```

### ✅ RESULTADOS OBTENIDOS

- **Subdominios funcionando:** `demo1.fact.rog.pe`, `demo2.fact.rog.pe`, `demo3.fact.rog.pe`
- **Sin redirect loops:** Laravel detecta correctamente HTTPS
- **Multi-tenant funcionando:** hyn/multi-tenant identifica correctamente el hostname
- **URLs limpias:** Sin puertos extraños en las URLs generadas
- **IPs reales:** Logs muestran la IP real del usuario

### 📋 CONFIGURACIÓN COMPLETA NECESARIA

Para nuevas instalaciones, asegurar:

1. **Cloudflare Tunnel:** Wildcard `*.fact.rog.pe` → `192.168.1.100:8080`
2. **Cloudflare DNS:** `CNAME *.fact` → `tunel-rog.pe.cfargotunnel.com`
3. **Nginx Proxy Manager:** Headers completos (configuración mostrada arriba)
4. **Base de datos:** Registros en tabla `hostnames` para cada subdominio
5. **Docker Nginx:** `server_name *.fact.rog.pe` para aceptar wildcards

**El sistema multi-tenant está funcionando correctamente con esta configuración.**

## Cambios aplicados (automático)

He aplicado los cambios necesarios en el repositorio para alinear la configuración con el entorno Docker y el proxy:

- `stack-facturador-smart/smart1/.env`: `REDIS_HOST` cambiado de `127.0.0.1` a `redis1` para que la aplicación use el servicio Redis del compose.
- `stack-facturador-smart/.docker/nginx/conf.d/nginx.conf`: `server_name` corregido a `fact.solucionessystem.com *.fact.solucionessystem.com` y añadidos `fastcgi_param HTTP_HOST $host;` y `fastcgi_param SERVER_NAME $host;` en el bloque `location ~ \\.php$`.
- `stack-facturador-smart/config/nginx/sites-available/default`: `server_name` corregido y añadidos `fastcgi_param HTTP_HOST $host;` y `fastcgi_param SERVER_NAME $host;` en el bloque PHP.

Estos cambios facilitan que `hyn/multi-tenant` identifique correctamente el `hostname` pasado por el proxy y que la aplicación use Redis dentro de la red `proxynet`.

Recomendaciones siguientes:

- Reiniciar los stacks afectados para aplicar los cambios:

```bash
docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d
docker compose -f stack-facturador-smart/npm/docker-compose.yml up -d
docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml up -d
docker compose -f stack-facturador-smart/utils/docker-compose.yml up -d
```

- Verificar estado de contenedores:

```bash
docker compose -f stack-facturador-smart/smart1/docker-compose.yml ps
docker logs nginx1 --tail 100
docker logs fpm1 --tail 100
```

- Probar localmente que el host llega correctamente (desde el servidor):

```bash
curl -H "Host: demo1.fact.solucionessystem.com" http://localhost:8080
```

Si quieres, puedo reiniciar los contenedores y ejecutar las comprobaciones anteriores ahora. Indica si autorizas la ejecución de comandos Docker en este entorno.
