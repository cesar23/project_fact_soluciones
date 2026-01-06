#!/bin/bash

# =============================================================================
# SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART
# =============================================================================
# Automatiza el proceso de despliegue del stack facturador
# Incluye: parada de servicios, actualización de código, permisos,
# construcción de contenedores y configuración de Laravel
# Versión: 1.4 - Perú 🇵🇪
# Stack: PHP 7.4 + Laravel 8
# =============================================================================

set -e  # Terminar en caso de error

# =============================================================================
# VARIABLES DEL SISTEMA
# =============================================================================

DATE_HOUR=$(date "+%Y-%m-%d_%H:%M:%S")
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || TZ="America/Lima" date "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || echo "$DATE_HOUR")
CURRENT_USER=$(id -un)
CURRENT_USER_HOME="${HOME:-$USERPROFILE}"
CURRENT_PC_NAME=$(hostname)
MY_INFO="${CURRENT_USER}@${CURRENT_PC_NAME}"
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}")
SCRIPT_NAME=$(basename "$PATH_SCRIPT")
CURRENT_DIR=$(dirname "$PATH_SCRIPT")
NAME_DIR=$(basename "$CURRENT_DIR")

# =============================================================================
# COLORES
# =============================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# =============================================================================
# VARIABLES DE CONFIGURACIÓN
# =============================================================================

PROJECT_PATH="${CURRENT_DIR}/stack-facturador-smart/smart1"
DOCKER_COMPOSE_FILE="${PROJECT_PATH}/docker-compose.yml"
UTILS_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/utils/docker-compose.yml"
CLOUDFLARE_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/cloudflare/docker-compose.yml"
NPM_COMPOSE="${CURRENT_DIR}/stack-facturador-smart/npm/docker-compose.yml"

# Credenciales de base de datos
DB_ROOT_PASSWORD="WPsOd4xPLL4nGRnOAHJp"
DB_NAME="smart1"

# =============================================================================
# FUNCIONES DE UTILIDAD
# =============================================================================

print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

print_section() {
    echo ""
    print_message "$CYAN" "============================================================"
    print_message "$CYAN" "$1"
    print_message "$CYAN" "============================================================"
}

print_error() {
    print_message "$RED" "❌ ERROR: $1"
}

print_success() {
    print_message "$GREEN" "✅ $1"
}

print_warning() {
    print_message "$YELLOW" "⚠️  $1"
}

print_info() {
    print_message "$BLUE" "ℹ️  $1"
}

# =============================================================================
# FUNCIONES DE DOCKER
# =============================================================================

stop_docker_service() {
    local compose_file=$1
    local service_name=$2

    if [ -f "$compose_file" ]; then
        print_info "Deteniendo $service_name..."
        if docker compose -f "$compose_file" down; then
            print_success "$service_name detenido correctamente"
        else
            print_warning "Error al detener $service_name (puede que no esté corriendo)"
        fi
    else
        print_warning "No se encuentra $compose_file, omitiendo..."
    fi
}

start_docker_service() {
    local compose_file=$1
    local service_name=$2

    if [ -f "$compose_file" ]; then
        print_info "Iniciando $service_name..."
        if docker compose -f "$compose_file" up -d; then
            print_success "$service_name iniciado correctamente"
        else
            print_warning "Error al iniciar $service_name"
        fi
    else
        print_warning "No se encuentra $compose_file, omitiendo..."
    fi
}

# =============================================================================
# INICIO DEL SCRIPT
# =============================================================================

clear
print_message "$MAGENTA" "╔═══════════════════════════════════════════════════════════╗"
print_message "$MAGENTA" "║     SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART        ║"
print_message "$MAGENTA" "║                Versión 1.4 - Perú 🇵🇪                     ║"
print_message "$MAGENTA" "║                PHP 7.4 + Laravel 8                       ║"
print_message "$MAGENTA" "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Verificar ubicación del script
if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
    print_error "No se encuentra el archivo docker-compose.yml"
    print_info "Asegúrate de estar en el directorio raíz del proyecto"
    print_info "Ruta esperada: $DOCKER_COMPOSE_FILE"
    exit 1
fi

# =============================================================================
# PASO 0: DETENER SERVICIOS
# =============================================================================

print_section "PASO 0: DETENIENDO TODOS LOS SERVICIOS DOCKER"

stop_docker_service "$DOCKER_COMPOSE_FILE" "servicio principal (smart1)"
stop_docker_service "$UTILS_COMPOSE" "servicios de utilidades"
stop_docker_service "$CLOUDFLARE_COMPOSE" "túnel de Cloudflare"
stop_docker_service "$NPM_COMPOSE" "Nginx Proxy Manager"

print_success "Todos los servicios han sido detenidos"

# =============================================================================
# PASO 1: ACTUALIZAR CÓDIGO DESDE GIT
# =============================================================================

print_section "PASO 1: ACTUALIZANDO CÓDIGO DESDE REPOSITORIO"

print_info "Obteniendo cambios desde el repositorio..."
git fetch origin master
print_success "Cambios obtenidos correctamente"

print_info "Aplicando cambios locales..."
git reset --hard origin/master
print_success "Cambios aplicados correctamente"

# =============================================================================
# PASO 2: CONFIGURAR PERMISOS DE SCRIPTS
# =============================================================================

print_section "PASO 2: CONFIGURANDO PERMISOS DE SCRIPTS"

chmod +x stack_start_all.sh
chmod +x stack_stop_all.sh
chmod +x devops_deploy_stack.sh
chmod +x devops_upload_changes.sh
print_success "Permisos de scripts configurados"

# =============================================================================
# PASO 3: CONFIGURAR PERMISOS DE CARPETAS
# =============================================================================

print_section "PASO 3: CONFIGURANDO PERMISOS DE CARPETAS"

print_info "Estableciendo permisos para storage, bootstrap y vendor..."

# Crear directorios si no existen
mkdir -p "${PROJECT_PATH}/storage" \
         "${PROJECT_PATH}/bootstrap/cache" \
         "${PROJECT_PATH}/vendor"

# Establecer permisos
sudo chmod -R 777 "${PROJECT_PATH}/storage/" \
                  "${PROJECT_PATH}/bootstrap/" \
                  "${PROJECT_PATH}/vendor/"

print_success "Permisos de carpetas configurados correctamente"

# =============================================================================
# PASO 4: CONSTRUIR Y LEVANTAR CONTENEDORES
# =============================================================================

print_section "PASO 4: CONSTRUYENDO Y LEVANTANDO CONTENEDORES"

print_info "Construyendo imágenes Docker (con --no-cache)..."
docker compose -f "$DOCKER_COMPOSE_FILE" build --no-cache
print_success "Imágenes construidas correctamente"

print_info "Iniciando contenedores en segundo plano..."
docker compose -f "$DOCKER_COMPOSE_FILE" up -d
print_success "Contenedores iniciados correctamente"

print_info "Esperando que los servicios se estabilicen..."
sleep 10

# =============================================================================
# PASO 5: CONFIGURAR LARAVEL
# =============================================================================

print_section "PASO 5: CONFIGURANDO LARAVEL Y DEPENDENCIAS"

print_info "Limpiando configuración cacheada..."
docker exec fpm1 bash -c "php artisan config:clear"
print_success "Configuración limpiada correctamente"

print_info "Cacheando configuración..."
docker exec fpm1 bash -c "php artisan config:cache"
print_success "Configuración cacheada correctamente"

print_info "Verificando versión de PHP y Laravel..."
docker exec fpm1 bash -c "php -v | head -n 1"
docker exec fpm1 bash -c "php artisan --version"

print_info "Aplicando permisos de escritura a directorios de Laravel..."
sudo chmod -R 777 "${PROJECT_PATH}/storage/" \
                  "${PROJECT_PATH}/bootstrap/" \
                  "${PROJECT_PATH}/vendor/"
print_success "Permisos aplicados correctamente"

# =============================================================================
# PASO 6: INICIAR SUPERVISOR
# =============================================================================

print_section "PASO 6: INICIANDO SUPERVISOR"

print_info "Reiniciando Supervisor..."
if docker compose exec -T supervisor1 supervisorctl start all; then
    print_success "Supervisor reiniciado correctamente"
else
    print_warning "Intentando iniciar procesos de Supervisor..."
    docker compose exec -T supervisor1 supervisorctl start all || true
fi

print_info "Verificando estado de Supervisor..."
docker compose exec -T supervisor1 supervisorctl status

# =============================================================================
# PASO 7: INICIAR SERVICIOS AUXILIARES
# =============================================================================

print_section "PASO 7: INICIANDO SERVICIOS AUXILIARES"

start_docker_service "$UTILS_COMPOSE" "servicios de utilidades (phpMyAdmin)"
start_docker_service "$CLOUDFLARE_COMPOSE" "túnel de Cloudflare"
start_docker_service "$NPM_COMPOSE" "Nginx Proxy Manager"

print_success "Todos los servicios auxiliares han sido iniciados"

print_info "Esperando que los servicios auxiliares se estabilicen..."
sleep 5

# =============================================================================
# PASO 8: VERIFICACIÓN FINAL
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

# =============================================================================
# RESUMEN FINAL
# =============================================================================

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
print_info "  📝 Entrar al contenedor: docker exec -it fpm1 bash"
print_info "  🗄️  MySQL: docker exec -it mariadb1 mysql -uroot -p$DB_ROOT_PASSWORD"
echo ""

# =============================================================================
# INSTRUCCIONES DE PRIMERA INSTALACIÓN
# =============================================================================

echo ""
print_message "$YELLOW" "╔═════════════════════════════════════════════════════════════════╗"
print_message "$YELLOW" "║  ⚠️  IMPORTANTE: PRIMERA INSTALACIÓN DEL SISTEMA ⚠️               ║"
print_message "$YELLOW" "╚═════════════════════════════════════════════════════════════════╝"
echo ""
print_message "$RED" "┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓"
print_message "$RED" "┃  🔴 SI ESTA ES LA PRIMERA VEZ QUE INSTALAS EL SISTEMA 🔴      ┃"
print_message "$RED" "┃                                                               ┃"
print_message "$RED" "┃  DEBES EJECUTAR LOS SIGUIENTES COMANDOS MANUALMENTE          ┃"
print_message "$RED" "┃  Y LUEGO REINICIAR EL STACK                                   ┃"
print_message "$RED" "┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛"
echo ""

print_message "$MAGENTA" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
print_message "$MAGENTA" "  💡 SCRIPT COMPLETO - COPIAR Y EJECUTAR TODO:"
print_message "$MAGENTA" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

cat << EOF
# ═══════════════════════════════════════════════════════════════
# PASO 1: PREPARAR BASE DE DATOS
# ═══════════════════════════════════════════════════════════════
docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME;"
docker exec mariadb1 mysql -u root -p$DB_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# ═══════════════════════════════════════════════════════════════
# PASO 2: CONFIGURAR APLICACIÓN
# ═══════════════════════════════════════════════════════════════
docker exec fpm1 apt-get update
docker exec fpm1 composer self-update
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
sudo chmod -R 777 "${PROJECT_PATH}/storage/" "${PROJECT_PATH}/bootstrap/" "${PROJECT_PATH}/vendor/"
EOF

echo ""
print_message "$RED" "⚠️  IMPORTANTE:"
print_message "$RED" "   • Estos comandos SOLO se ejecutan en la PRIMERA instalación"
print_message "$RED" "   • En despliegues posteriores NO son necesarios"
print_message "$RED" "   • Después de ejecutar, reinicia el stack con: ./devops_deploy_stack.sh"
echo ""

print_success "¡El stack facturador smart está listo para usar! 🇵🇪"
echo ""

# =============================================================================
# REGISTRO DE DESPLIEGUE
# =============================================================================

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
echo "$TIMESTAMP - $MY_INFO: Despliegue completado exitosamente (PHP 7.4 + Laravel 8)" >> deploy.log
print_info "Registro de despliegue guardado en deploy.log"

echo ""
print_info "Últimos 5 despliegues:"
tail -n 5 deploy.log 2>/dev/null || echo "  No hay registros previos"
echo ""