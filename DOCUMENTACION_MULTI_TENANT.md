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

#### Archivo: `docker-compose.yml`

```yaml
version: '3'

services:
  # NGINX - Servidor Web
  nginx1:
    image: rash07/nginx
    working_dir: /var/www/html
    ports:
      - "8080:80"  # CRITICO: Expone puerto 8080 al host
    environment:
      VIRTUAL_HOST: fact.rog.pe, *.fact.rog.pe  # Acepta wildcards
    volumes:
      - ./:/var/www/html
      - /home/cesar/stack-facturador-smart/proxy/fpms/smart1:/etc/nginx/sites-available
    restart: always

  # PHP-FPM - Procesador PHP
  fpm1:
    image: rash07/php-fpm:7.4
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    restart: always

  # MariaDB - Base de Datos
  mariadb1:
    image: mariadb:10.5.6
    environment:
      - MYSQL_DATABASE=smart1      # BD sistema
      - MYSQL_ROOT_PASSWORD=WPsOd4xPLL4nGRnOAHJp
    volumes:
      - mysqldata1:/var/lib/mysql
    ports:
      - "3306:3306"
    restart: always

  # Redis - Cache
  redis1:
    image: redis:alpine
    volumes:
      - redisdata1:/data
    restart: always

  # Scheduling - Tareas programadas Laravel
  scheduling1:
    image: rash07/scheduling
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    restart: always

  # Supervisor - Colas de trabajo
  supervisor1:
    image: rash07/php7.4-supervisor
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./supervisor.conf:/etc/supervisor/conf.d/supervisor.conf
    restart: always

networks:
  default:
    external:
      name: proxynet  # Red compartida (si usas Nginx Proxy Manager)

volumes:
  redisdata1:
    driver: "local"
  mysqldata1:
    driver: "local"
```

#### Puntos Criticos:
1. **Puerto 8080:** Nginx expone el puerto 8080 al host
2. **VIRTUAL_HOST:** Acepta wildcards `*.fact.rog.pe`
3. **Volumen Nginx:** Monta configuracion desde `/home/cesar/stack-facturador-smart/proxy/fpms/smart1`
4. **Red proxynet:** Si usas NPM, ambos deben estar en misma red Docker

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

**Archivo:** `/home/cesar/stack-facturador-smart/proxy/fpms/smart1/default` (o similar)

```nginx
server {
    listen 80;

    # IMPORTANTE: Aceptar todos los subdominios
    server_name fact.rog.pe *.fact.rog.pe;

    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass fpm1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        # CRITICO: Pasar el HOST original
        fastcgi_param HTTP_HOST $host;
        fastcgi_param SERVER_NAME $host;

        include fastcgi_params;
    }
}
```

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

Si solo ves:
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
