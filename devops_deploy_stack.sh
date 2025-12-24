#!/bin/bash

# =============================================================================
# SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART
# =============================================================================
# Este script automatiza el proceso de despliegue del stack facturador
# Incluye: parada de servicios, actualización de código, permisos, 
# construcción de contenedores y configuración de Laravel
# =============================================================================

DATE_HOUR=$(date "+%Y-%m-%d_%H:%M:%S")
# Fecha y hora actual en Perú (UTC -5)
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || TZ="America/Lima" date "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || echo "$DATE_HOUR")
CURRENT_USER=$(id -un)             # Nombre del usuario actual.
CURRENT_USER_HOME="${HOME:-$USERPROFILE}"  # Ruta del perfil del usuario actual.
CURRENT_PC_NAME=$(hostname)        # Nombre del equipo actual.
MY_INFO="${CURRENT_USER}@${CURRENT_PC_NAME}"  # Información combinada del usuario y del equipo.
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}")  # Ruta completa del script actual.
SCRIPT_NAME=$(basename "$PATH_SCRIPT")           # Nombre del archivo del script.
CURRENT_DIR=$(dirname "$PATH_SCRIPT")            # Ruta del directorio donde se encuentra el script.
NAME_DIR=$(basename "$CURRENT_DIR")              # Nombre del directorio actual.


# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variables de configuración
PROJECT_PATH="${CURRENT_DIR}/stack-facturador-smart/smart1"
DOCKER_COMPOSE_FILE="${PROJECT_PATH}/docker-compose.yml"
UTILS_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/utils/docker-compose.yml"
CLOUDFLARE_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/cloudflare/docker-compose.yml"
NPM_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/npm/docker-compose.yml"

# Función para imprimir mensajes con formato
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Función para imprimir secciones
print_section() {
    echo ""
    print_message "$CYAN" "============================================================"
    print_message "$CYAN" "$1"
    print_message "$CYAN" "============================================================"
}

# Función para imprimir errores
print_error() {
    print_message "$RED" "❌ ERROR: $1"
}

# Función para imprimir éxito
print_success() {
    print_message "$GREEN" "✅ $1"
}

# Función para imprimir advertencias
print_warning() {
    print_message "$YELLOW" "⚠️  $1"
}

# Función para imprimir información
print_info() {
    print_message "$BLUE" "ℹ️  $1"
}

# =============================================================================
# INICIO DEL SCRIPT
# =============================================================================

clear
print_message "$MAGENTA" "╔═══════════════════════════════════════════════════════════╗"
print_message "$MAGENTA" "║     SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART        ║"
print_message "$MAGENTA" "║                Versión 1.3 - Perú 🇵🇪                     ║"
print_message "$MAGENTA" "║                PHP 7.4 + Laravel 8                       ║"
print_message "$MAGENTA" "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
    print_error "No se encuentra el archivo docker-compose.yml"
    print_info "Asegúrate de estar en el directorio raíz del proyecto"
    print_info "Ruta esperada: $DOCKER_COMPOSE_FILE"
    exit 1
fi

# =============================================================================
# PASO 0: PARAR TODOS LOS SERVICIOS
# =============================================================================
print_section "PASO 0: DETENIENDO TODOS LOS SERVICIOS DOCKER"

# Detener servicio principal (smart1)
print_info "Deteniendo servicio principal (smart1)..."
if docker compose -f "$DOCKER_COMPOSE_FILE" down; then
    print_success "Servicio smart1 detenido correctamente"
else
    print_warning "Error al detener smart1 (puede que no esté corriendo)"
fi

# Detener utilidades
if [ -f "$UTILS_COMPOSE" ]; then
    print_info "Deteniendo servicios de utilidades..."
    if docker compose -f "$UTILS_COMPOSE" down; then
        print_success "Servicios de utilidades detenidos correctamente"
    else
        print_warning "Error al detener utilidades (puede que no estén corriendo)"
    fi
else
    print_warning "No se encuentra $UTILS_COMPOSE, omitiendo..."
fi

# Detener Cloudflare
if [ -f "$CLOUDFLARE_COMPOSE" ]; then
    print_info "Deteniendo túnel de Cloudflare..."
    if docker compose -f "$CLOUDFLARE_COMPOSE" down; then
        print_success "Túnel de Cloudflare detenido correctamente"
    else
        print_warning "Error al detener Cloudflare (puede que no esté corriendo)"
    fi
else
    print_warning "No se encuentra $CLOUDFLARE_COMPOSE, omitiendo..."
fi

# Detener Nginx Proxy Manager
if [ -f "$NPM_COMPOSE" ]; then
    print_info "Deteniendo Nginx Proxy Manager..."
    if docker compose -f "$NPM_COMPOSE" down; then
        print_success "Nginx Proxy Manager detenido correctamente"
    else
        print_warning "Error al detener NPM (puede que no esté corriendo)"
    fi
else
    print_warning "No se encuentra $NPM_COMPOSE, omitiendo..."
fi

print_success "Todos los servicios han sido detenidos"

# =============================================================================
# PASO 1: ACTUALIZAR CÓDIGO DESDE GIT
# =============================================================================
print_section "PASO 1: ACTUALIZANDO CÓDIGO DESDE REPOSITORIO"

print_info "Obteniendo cambios desde el repositorio..."
if git fetch origin master; then
    print_success "Cambios obtenidos correctamente"
else
    print_error "Error al obtener cambios del repositorio"
    exit 1
fi

print_info "Aplicando cambios locales..."
if git reset --hard origin/master; then
    print_success "Cambios aplicados correctamente"
else
    print_error "Error al aplicar cambios locales"
    exit 1
fi

# =============================================================================
# PASO 2: PERMISOS DE SCRIPTS
# =============================================================================
print_section "PASO 2: CONFIGURANDO PERMISOS DE SCRIPTS"

print_info "Dando permisos de ejecución a scripts..."
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh
chmod +x devops_deploy_stack.sh
chmod +x devops_upload_changes.sh
print_success "Permisos configurados correctamente"

# =============================================================================
# PASO 3: DESCOMPRIMIR ARCHIVO
# =============================================================================
print_section "PASO 3: DESCOMPRIMIENDO ARCHIVO smart1.tar.gz"

print_info "Ejecutando script de descompresión..."
if [ -f "stack-facturador-smart/smart1.tar.gz" ]; then
    if ./stack-facturador-smart/smart1_decompress.sh; then
        print_success "Archivo descomprimido correctamente"
    else
        print_error "Error al descomprimir el archivo"
        exit 1
    fi
else
    print_warning "No se encuentra smart1.tar.gz, omitiendo descompresión"
fi

# =============================================================================
# PASO 4: PERMISOS DE CARPETAS
# =============================================================================
print_section "PASO 4: CONFIGURANDO PERMISOS DE CARPETAS"

print_info "Estableciendo permisos para storage, bootstrap y vendor..."

mkdir -p "./${PROJECT_PATH}/storage" \
         "./${PROJECT_PATH}/bootstrap/cache" \
         "./${PROJECT_PATH}/vendor"

sudo chmod -R 777 "./${PROJECT_PATH}/storage/" \
    "./${PROJECT_PATH}/bootstrap/" \
    "./${PROJECT_PATH}/vendor/"

if [ $? -eq 0 ]; then
    print_success "Permisos de carpetas configurados correctamente"
else
    print_error "Error al configurar permisos de carpetas"
    exit 1
fi

# =============================================================================
# PASO 5: CONSTRUIR Y LEVANTAR CONTENEDORES
# =============================================================================
print_section "PASO 5: CONSTRUYENDO Y LEVANTANDO CONTENEDORES"

print_info "Construyendo imágenes Docker (con --no-cache)..."
if docker compose -f "$DOCKER_COMPOSE_FILE" build --no-cache; then
    print_success "Imágenes construidas correctamente"
else
    print_error "Error al construir las imágenes Docker"
    exit 1
fi

print_info "Iniciando contenedores en segundo plano..."
if docker compose -f "$DOCKER_COMPOSE_FILE" up -d; then
    print_success "Contenedores iniciados correctamente"
else
    print_error "Error al iniciar los contenedores"
    exit 1
fi

# Esperar unos segundos para que los servicios se estabilicen
print_info "Esperando que los servicios se estabilicen..."
sleep 10

# =============================================================================
# PASO 6: INSTALAR COMPOSER Y CONFIGURAR LARAVEL
# =============================================================================
print_section "PASO 6: CONFIGURANDO LARAVEL Y DEPENDENCIAS"

print_info "Configurando permisos de Git en el contenedor..."
docker exec fpm1 bash -c "git config --global --add safe.directory /var/www/html" 2>/dev/null || true


print_info "Instalando dependencias de Composer..."
if docker exec fpm1 bash -c "composer install"; then
    print_success "Dependencias de Composer instaladas correctamente"
else
    print_error "Error al instalar dependencias de Composer"
    exit 1
fi

#print_info "Regenerando autoload de Composer..."
#if docker exec fpm1 bash -c "composer dump-autoload --optimize"; then
#    print_success "Autoload regenerado correctamente"
#else
#    print_warning "Error al regenerar autoload"
#fi

print_info "Limpiando todas las cachés de Laravel..."
docker exec fpm1 bash -c "php artisan storage:link"
docker exec fpm1 bash -c "php artisan cache:clear"
docker exec fpm1 bash -c "php artisan config:cache"
docker exec fpm1 bash -c "php artisan view:clear"
docker exec fpm1 bash -c "php artisan route:clear"
print_success "Cachés de Laravel limpiadas"

print_info "Cacheando configuración de Laravel..."
if docker exec fpm1 bash -c "php artisan config:cache"; then
    print_success "Configuración cacheada correctamente"
else
    print_error "Error al cachear la configuración"
    exit 1
fi

print_info "Cacheando rutas de Laravel..."
if docker exec fpm1 bash -c "php artisan route:cache"; then
    print_success "Rutas cacheadas correctamente"
else
    print_warning "Error al cachear rutas (puede ser normal si usas closures)"
fi

print_info "Creando enlace silicosis de storage..."
if docker exec fpm1 bash -c "php artisan storage:link"; then
    print_success "Enlace de storage creado correctamente"
else
    print_warning "No se pudo crear el enlace de storage (puede que ya exista)"
fi

print_info "Configurando permisos para mPDF..."
docker exec fpm1 bash -c "chmod -R 777 vendor/mpdf/mpdf 2>/dev/null || true"
print_success "Permisos de mPDF configurados"

print_info "Verificando versión de PHP y Laravel..."
docker exec fpm1 bash -c "php -v | head -n 1"
docker exec fpm1 bash -c "php artisan --version"

# =============================================================================
# PASO 7: INICIAR SUPERVISOR
# =============================================================================
print_section "PASO 7: INICIANDO SUPERVISOR"

print_info "Reiniciando Supervisor..."
if docker exec supervisor1 supervisorctl restart all; then
    print_success "Supervisor reiniciado correctamente"
else
    print_warning "Intentando iniciar procesos de Supervisor..."
    docker exec supervisor1 supervisorctl start all
fi

print_info "Verificando estado de Supervisor..."
docker exec supervisor1 supervisorctl status

# =============================================================================
# PASO 8: INICIAR SERVICIOS AUXILIARES
# =============================================================================
print_section "PASO 8: INICIANDO SERVICIOS AUXILIARES"

# Iniciar utilidades (phpMyAdmin, etc)
if [ -f "$UTILS_COMPOSE" ]; then
    print_info "Iniciando servicios de utilidades (phpMyAdmin)..."
    if docker compose -f "$UTILS_COMPOSE" up -d; then
        print_success "Servicios de utilidades iniciados correctamente"
    else
        print_warning "Error al iniciar utilidades"
    fi
else
    print_warning "No se encuentra $UTILS_COMPOSE, omitiendo..."
fi

# Iniciar túnel de Cloudflare
if [ -f "$CLOUDFLARE_COMPOSE" ]; then
    print_info "Iniciando túnel de Cloudflare..."
    if docker compose -f "$CLOUDFLARE_COMPOSE" up -d; then
        print_success "Túnel de Cloudflare iniciado correctamente"
    else
        print_warning "Error al iniciar Cloudflare"
    fi
else
    print_warning "No se encuentra $CLOUDFLARE_COMPOSE, omitiendo..."
fi

# Iniciar Nginx Proxy Manager
if [ -f "$NPM_COMPOSE" ]; then
    print_info "Iniciando Nginx Proxy Manager..."
    if docker compose -f "$NPM_COMPOSE" up -d; then
        print_success "Nginx Proxy Manager iniciado correctamente"
    else
        print_warning "Error al iniciar NPM"
    fi
else
    print_warning "No se encuentra $NPM_COMPOSE, omitiendo..."
fi

print_success "Todos los servicios auxiliares han sido iniciados"

# Esperar a que los servicios se estabilicen
print_info "Esperando que los servicios auxiliares se estabilicen..."
sleep 5

# =============================================================================
# VERIFICACIÓN FINAL
# =============================================================================
print_section "VERIFICACIÓN FINAL"

print_info "Verificando estado de TODOS los contenedores..."
echo ""
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

print_info "Verificando conectividad de base de datos..."
if docker exec fpm1 bash -c "php artisan migrate:status" > /dev/null 2>&1; then
    print_success "Conexión a base de datos verificada"
else
    print_warning "No se pudo verificar la conexión a base de datos"
fi

echo ""
print_message "$GREEN" "┌──────────────────────────────────────────────────────────┐"
print_message "$GREEN" "│   🎉 DESPLIEGUE COMPLETADO EXITOSAMENTE 🎉                │"
print_message "$GREEN" "└──────────────────────────────────────────────────────────┘"
echo ""

print_info "Información del sistema:"
print_info "  📦 PHP: 7.4"
print_info "  🚀 Laravel: 8.x"
print_info "  🐳 Docker: Activo"
echo ""

print_info "Servicios iniciados:"
print_info "  ✅ Stack principal (smart1)"
print_info "  ✅ Servicios auxiliares (utils)"
print_info "  ✅ Túnel Cloudflare"
print_info "  ✅ Nginx Proxy Manager"
echo ""

print_info "Puedes acceder a tus aplicaciones en:"
print_info "  🌐 Aplicación principal: http://localhost:8080"
print_info "  🌐 Sitio público: https://fact.solucionessystem.com"
print_info "  🗄️  phpMyAdmin: http://localhost:8081"
print_info "  🔧 Nginx Proxy Manager: http://localhost:81"
echo ""

print_info "Comandos útiles:"
print_info "  📊 Ver logs smart1: docker compose -f $DOCKER_COMPOSE_FILE logs -f"
print_info "  📊 Ver todos los contenedores: docker ps -a"
print_info "  🛑 Detener todo: docker stop \$(docker ps -q)"
print_info "  🔄 Reiniciar smart1: docker compose -f $DOCKER_COMPOSE_FILE restart"
print_info "  🔍 Entrar al contenedor: docker exec -it fpm1 bash"
print_info "  🗄️  MySQL: docker exec -it mariadb1 mysql -uroot -p"
echo ""

# =============================================================================
# 🚨 COMANDOS DE PRIMERA INSTALACIÓN
# =============================================================================
echo ""
print_message "$YELLOW" "╔══════════════════════════════════════════════════════════════════╗"
print_message "$YELLOW" "║  ⚠️  IMPORTANTE: PRIMERA INSTALACIÓN DEL SISTEMA ⚠️               ║"
print_message "$YELLOW" "╚══════════════════════════════════════════════════════════════════╝"
echo ""
print_message "$RED" "┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓"
print_message "$RED" "┃  🔴 SI ESTA ES LA PRIMERA VEZ QUE INSTALAS EL SISTEMA 🔴      ┃"
print_message "$RED" "┃                                                               ┃"
print_message "$RED" "┃  DEBES EJECUTAR LOS SIGUIENTES COMANDOS MANUALMENTE :         ┃"
print_message "$RED" "┃  LUEGO REINICIAR                                              ┃"
print_message "$RED" "┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛"
echo ""
print_message "$CYAN" "===================================================="
print_message "$CYAN" "0️⃣  PREPARAR DB:"
print_message "$CYAN" " - borrar la base de datos smart1"
print_message "$GREEN" "   docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e \"DROP DATABASE IF EXISTS smart1;\""
echo ""
print_message "$CYAN" " - crear la base de datos smart1"
print_message "$GREEN" "   docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e \"CREATE DATABASE IF NOT EXISTS smart1;\""
echo ""
print_message "$CYAN" " - verificar si hay db tenancy si hay eliminarlos"
print_message "$GREEN" "   docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e \"SHOW DATABASES LIKE 'tenancy_%';\""
echo ""
echo ""
print_message "$CYAN" "===================================================="
print_message "$CYAN" "1️⃣  Accede al contenedor PHP:"
print_message "$GREEN" "   docker exec -it fpm1 bash"
echo ""
print_message "$CYAN" "2️⃣  Ejecuta los siguientes comandos EN ORDEN:"
echo ""
print_message "$YELLOW" "   📌 composer self-update"
print_message "$BLUE" "      └─ Actualiza Composer a la última versión"
echo ""
print_message "$YELLOW" "   📌 composer install"
print_message "$BLUE" "      └─ Instala todas las dependencias del proyecto"
echo ""
print_message "$YELLOW" "   📌 php artisan migrate:refresh --seed"
print_message "$BLUE" "      └─ Crea las tablas de la BD y carga datos iniciales"
print_message "$RED" "      ⚠️  ADVERTENCIA: Esto BORRARÁ todos los datos existentes"
echo ""
print_message "$YELLOW" "   📌 php artisan key:generate"
print_message "$BLUE" "      └─ Genera la clave de encriptación de la aplicación"
echo ""
print_message "$YELLOW" "   📌 php artisan storage:link"
print_message "$BLUE" "      └─ Crea enlace simbólico para archivos públicos"
echo ""
print_message "$CYAN" "3️⃣  Sal del contenedor:"
print_message "$GREEN" "   exit"
echo ""
print_message "$MAGENTA" "═══════════════════════════════════════════════════════════════════"
print_message "$MAGENTA" "  💡 SCRIPT RÁPIDO PARA COPIAR Y PEGAR:"
print_message "$MAGENTA" "═══════════════════════════════════════════════════════════════════"
echo ""
print_message "$GREEN" "docker exec fpm1 apt-get update"
print_message "$GREEN" "docker exec fpm1 composer self-update"
print_message "$GREEN" "docker exec fpm1 composer install"
print_message "$GREEN" "docker exec fpm1 php artisan migrate:refresh --seed"
print_message "$GREEN" "docker exec fpm1 php artisan key:generate"
print_message "$GREEN" "docker exec fpm1 php artisan storage:link"
echo ""
print_message "$MAGENTA" "═══════════════════════════════════════════════════════════════════"
echo ""
print_message "$CYAN" "===================================================="
print_message "$CYAN" "5️⃣  Setear permisos de carpetas:"
echo ""
print_message "$GREEN" "   PATH_INSTALL=\"${PROJECT_PATH}\""
print_message "$GREEN" "   sudo chmod -R 777 \"$PATH_INSTALL/storage/\" \"$PATH_INSTALL/bootstrap/\" \"$PATH_INSTALL/vendor/\""
echo ""
print_message "$RED" "⚠️  NOTA: Solo ejecuta estos comandos en la PRIMERA instalación"
print_message "$RED" "⚠️  En despliegues posteriores, estos comandos NO son necesarios"
echo ""

print_success "¡El stack facturador smart está listo para usar! 🇵🇪"
echo ""

# Registrar el despliegue
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
echo "$TIMESTAMP: Despliegue completado exitosamente (PHP 7.4 + Laravel 8 + Servicios auxiliares)" >> deploy.log
print_info "Registro de despliegue guardado en deploy.log"

# Mostrar últimos logs de deploy
echo ""
print_info "Últimos 5 despliegues:"
tail -n 5 deploy.log 2>/dev/null || echo "  No hay registros previos"
echo ""