#!/bin/bash

# =============================================================================
# SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART
# =============================================================================
# Este script automatiza el proceso de despliegue del stack facturador
# Incluye: parada de servicios, actualización de código, permisos, 
# construcción de contenedores y configuración de Laravel
# =============================================================================

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variables de configuración
PROJECT_PATH="stack-facturador-smart/smart1"
DOCKER_COMPOSE_FILE="${PROJECT_PATH}/docker-compose.yml"

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
print_message "$MAGENTA" "╔══════════════════════════════════════════════════════════╗"
print_message "$MAGENTA" "║     SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART         ║"
print_message "$MAGENTA" "║                Versión 1.1 - Perú 🇵🇪                      ║"
print_message "$MAGENTA" "║                PHP 8.1 + Laravel 8                        ║"
print_message "$MAGENTA" "╚══════════════════════════════════════════════════════════╝"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
    print_error "No se encuentra el archivo docker-compose.yml"
    print_info "Asegúrate de estar en el directorio raíz del proyecto"
    print_info "Ruta esperada: $DOCKER_COMPOSE_FILE"
    exit 1
fi

# =============================================================================
# PASO 0: PARAR SERVICIOS
# =============================================================================
print_section "PASO 0: DETENIENDO SERVICIOS DOCKER"

print_info "Deteniendo todos los contenedores del stack..."
if docker compose -f "$DOCKER_COMPOSE_FILE" down; then
    print_success "Servicios detenidos correctamente"
else
    print_error "Error al detener los servicios"
    exit 1
fi

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

print_info "Limpiando caché de Composer..."
if docker exec fpm1 bash -c "composer clear-cache 2>/dev/null || true"; then
    print_success "Caché de Composer limpiada"
else
    print_warning "Error al limpiar caché de Composer (puede ser normal)"
fi

print_info "Instalando dependencias de Composer..."
if docker exec fpm1 bash -c "composer install --optimize-autoloader --no-dev"; then
    print_success "Dependencias de Composer instaladas correctamente"
else
    print_error "Error al instalar dependencias de Composer"
    exit 1
fi

print_info "Regenerando autoload de Composer..."
if docker exec fpm1 bash -c "composer dump-autoload --optimize"; then
    print_success "Autoload regenerado correctamente"
else
    print_warning "Error al regenerar autoload"
fi

print_info "Limpiando todas las cachés de Laravel..."
docker exec fpm1 bash -c "php artisan config:clear"
docker exec fpm1 bash -c "php artisan cache:clear"
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

print_info "Creando enlace simbólico de storage..."
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
# VERIFICACIÓN FINAL
# =============================================================================
print_section "VERIFICACIÓN FINAL"

print_info "Verificando estado de los contenedores..."
docker compose -f "$DOCKER_COMPOSE_FILE" ps

print_info "Verificando conectividad de base de datos..."
if docker exec fpm1 bash -c "php artisan migrate:status" > /dev/null 2>&1; then
    print_success "Conexión a base de datos verificada"
else
    print_warning "No se pudo verificar la conexión a base de datos"
fi

echo ""
print_message "$GREEN" "┌────────────────────────────────────────────────────────────┐"
print_message "$GREEN" "│   🎉 DESPLIEGUE COMPLETADO EXITOSAMENTE 🎉                 │"
print_message "$GREEN" "└────────────────────────────────────────────────────────────┘"
echo ""

print_info "Información del sistema:"
print_info "  📦 PHP: 8.1"
print_info "  🚀 Laravel: 8.x"
print_info "  🐳 Docker: Activo"
echo ""

print_info "Puedes acceder a tu aplicación en:"
print_info "  🌐 http://localhost:8080"
print_info "  🌐 https://fact.solucionessystem.com"
echo ""

print_info "Comandos útiles:"
print_info "  📊 Ver logs: docker compose -f $DOCKER_COMPOSE_FILE logs -f"
print_info "  🛑 Detener: docker compose -f $DOCKER_COMPOSE_FILE down"
print_info "  🔄 Reiniciar: docker compose -f $DOCKER_COMPOSE_FILE restart"
print_info "  🔍 Entrar al contenedor: docker exec -it fpm1 bash"
print_info "  🗄️  MySQL: docker exec -it mariadb1 mysql -uroot -p"
echo ""

print_success "¡El stack facturador smart está listo para usar! 🇵🇪"
echo ""

# Registrar el despliegue
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
echo "$TIMESTAMP: Despliegue completado exitosamente (PHP 8.1 + Laravel 8)" >> deploy.log
print_info "Registro de despliegue guardado en deploy.log"

# Mostrar últimos logs de deploy
echo ""
print_info "Últimos 5 despliegues:"
tail -n 5 deploy.log 2>/dev/null || echo "  No hay registros previos"
echo ""