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
print_message "$MAGENTA" "╔════════════════════════════════════════════════════════════╗"
print_message "$MAGENTA" "║     SCRIPT DE DESPLIEGUE - STACK FACTURADOR SMART         ║"
print_message "$MAGENTA" "║                Versión 1.0 - Perú 🇵🇪                      ║"
print_message "$MAGENTA" "╚════════════════════════════════════════════════════════════╝"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "stack-facturador-smart/smart1/docker-compose.yml" ]; then
    print_error "No se encuentra el archivo docker-compose.yml"
    print_info "Asegúrate de estar en el directorio raíz del proyecto"
    exit 1
fi

# =============================================================================
# PASO 0: PARAR SERVICIOS
# =============================================================================
print_section "PASO 0: DETENIENDO SERVICIOS DOCKER"

print_info "Deteniendo todos los contenedores del stack..."
if docker compose -f stack-facturador-smart/smart1/docker-compose.yml down; then
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
sudo chmod -R 777 "./stack-facturador-smart/smart1/storage/" \
    "./stack-facturador-smart/smart1/bootstrap/" \
    "./stack-facturador-smart/smart1/vendor/"

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
if docker compose -f stack-facturador-smart/smart1/docker-compose.yml build --no-cache; then
    print_success "Imágenes construidas correctamente"
else
    print_error "Error al construir las imágenes Docker"
    exit 1
fi

print_info "Iniciando contenedores en segundo plano..."
if docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d; then
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

print_info "Instalando dependencias de Composer..."
if docker exec fpm1 bash -c "composer install"; then
    print_success "Dependencias de Composer instaladas correctamente"
else
    print_error "Error al instalar dependencias de Composer"
    exit 1
fi

print_info "Creando enlace simbólico de storage..."
if docker exec fpm1 bash -c "php artisan storage:link"; then
    print_success "Enlace de storage creado correctamente"
else
    print_warning "No se pudo crear el enlace de storage (puede que ya exista)"
fi

print_info "Limpiando caché de Laravel..."
docker exec fpm1 bash -c "php artisan cache:clear"
print_success "Caché limpiada"

print_info "Cacheando configuración de Laravel..."
if docker exec fpm1 bash -c "php artisan config:cache"; then
    print_success "Configuración cacheada correctamente"
else
    print_error "Error al cachear la configuración"
    exit 1
fi

print_info "Configurando permisos para mPDF..."
docker exec fpm1 bash -c "chmod -R 777 vendor/mpdf/mpdf"
print_success "Permisos de mPDF configurados"

# =============================================================================
# PASO 7: INICIAR SUPERVISOR
# =============================================================================
print_section "PASO 7: INICIANDO SUPERVISOR"

print_info "Iniciando todos los procesos de Supervisor..."
if docker exec supervisor1 supervisorctl start all; then
    print_success "Procesos de Supervisor iniciados correctamente"
else
    print_warning "No se pudieron iniciar algunos procesos de Supervisor"
fi

print_info "Verificando estado de Supervisor..."
docker exec supervisor1 supervisorctl status

# =============================================================================
# VERIFICACIÓN FINAL
# =============================================================================
print_section "VERIFICACIÓN FINAL"

print_info "Verificando estado de los contenedores..."
docker compose -f stack-facturador-smart/smart1/docker-compose.yml ps

echo ""
print_message "$GREEN" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
print_message "$GREEN" "   🎉 DESPLIEGUE COMPLETADO EXITOSAMENTE 🎉"
print_message "$GREEN" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

print_info "Puedes acceder a tu aplicación en:"
print_info "  🌐 http://localhost:8080"
print_info "  🌐 http://fact.solucionessystem.com"
echo ""

print_info "Comandos útiles:"
print_info "  📊 Ver logs: docker compose -f stack-facturador-smart/smart1/docker-compose.yml logs -f"
print_info "  🛑 Detener: docker compose -f stack-facturador-smart/smart1/docker-compose.yml down"
print_info "  🔄 Reiniciar: docker compose -f stack-facturador-smart/smart1/docker-compose.yml restart"
echo ""

print_success "¡El stack facturador smart está listo para usar! 🇵🇪"
echo ""

# Registrar el despliegue
echo "$(date): Despliegue completado exitosamente" >> deploy.log
print_info "Registro de despliegue guardado en deploy.log"