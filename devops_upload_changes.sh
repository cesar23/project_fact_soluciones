#!/bin/bash

# =============================================================================
# SCRIPT DE SUBIDA DE CAMBIOS - STACK FACTURADOR SMART
# =============================================================================
# Automatiza el proceso de subida de cambios al repositorio
# Incluye: permisos, limpieza, configuración de LFS y push
# Versión: 1.0 - Perú 🇵🇪
# =============================================================================

set -e  # Terminar en caso de error

# =============================================================================
# VARIABLES DEL SISTEMA
# =============================================================================

DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || \
               TZ="America/Lima" date "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || \
               date '+%Y-%m-%d_%H:%M:%S')
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
VENDOR_PATH="${PROJECT_PATH}/vendor"
COMPRESS_SCRIPT="${CURRENT_DIR}/stack-facturador-smart/smart1_compress.sh"
DECOMPRESS_SCRIPT="${CURRENT_DIR}/stack-facturador-smart/smart1_decompress.sh"

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
# FUNCIONES DE VALIDACIÓN
# =============================================================================

validate_project_structure() {
    if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
        print_error "No se encuentra el archivo docker-compose.yml"
        print_info "Asegúrate de estar en el directorio raíz del proyecto"
        print_info "Ruta esperada: $DOCKER_COMPOSE_FILE"
        exit 1
    fi
}

validate_git_repository() {
    print_info "Verificando estado del repositorio Git..."
    if ! git status > /dev/null 2>&1; then
        print_error "No se puede acceder al repositorio Git"
        print_info "Asegúrate de estar en un repositorio Git válido"
        exit 1
    fi
    print_success "Repositorio Git válido"
}

# =============================================================================
# FUNCIONES DE GIT LFS
# =============================================================================

install_git_lfs() {
    print_warning "Git LFS no está instalado"
    print_info "Instalando Git LFS..."

    if command -v apt-get &> /dev/null; then
        sudo apt-get update && sudo apt-get install -y git-lfs
    elif command -v yum &> /dev/null; then
        sudo yum install -y git-lfs
    else
        print_error "No se pudo instalar Git LFS automáticamente"
        print_info "Por favor instala Git LFS manualmente desde: https://git-lfs.github.com"
        exit 1
    fi

    print_success "Git LFS instalado correctamente"
}

configure_git_lfs() {
    # Verificar si Git LFS está instalado
    if ! command -v git-lfs &> /dev/null; then
        install_git_lfs
    fi

    # Inicializar Git LFS si no está inicializado
    if ! git lfs env &> /dev/null; then
        print_info "Inicializando Git LFS..."
        git lfs install
        print_success "Git LFS inicializado correctamente"
    fi

    # Configurar tracking para archivos .tar.gz
    print_info "Configurando tracking para archivos .tar.gz..."
    git lfs track "*.tar.gz"
    print_success "Tracking de Git LFS configurado correctamente"
}

# =============================================================================
# FUNCIONES DE LIMPIEZA
# =============================================================================

clean_vendor_directory() {
    if [ -d "$VENDOR_PATH" ]; then
        print_info "Eliminando contenido de la carpeta vendor..."
        rm -rf "${VENDOR_PATH:?}"/*
        print_success "Carpeta vendor limpiada correctamente"
    else
        print_warning "No se encuentra la carpeta vendor, omitiendo..."
    fi
}

# =============================================================================
# FUNCIONES DE GIT
# =============================================================================

commit_changes() {
    print_info "Mostrando cambios pendientes..."
    git status

    echo ""
    print_info "Agregando todos los cambios..."
    git add .
    print_success "Cambios agregados al staging area"

    # Solicitar mensaje de commit
    echo ""
    print_info "Ingrese el mensaje para el commit:"
    print_info "(Presiona Enter para usar el mensaje por defecto)"
    read -p "Mensaje: " COMMIT_MESSAGE

    if [ -z "$COMMIT_MESSAGE" ]; then
        COMMIT_MESSAGE="Actualización de código y configuración - ${DATE_HOUR_PE}"
        print_info "Usando mensaje por defecto: $COMMIT_MESSAGE"
    fi

    print_info "Realizando commit..."
    git commit -m "$COMMIT_MESSAGE"
    print_success "Commit realizado correctamente"

    echo "$COMMIT_MESSAGE"
}

push_changes() {
    print_info "Enviando archivos LFS al repositorio remoto..."
    if git lfs push origin master 2>/dev/null; then
        print_success "Archivos LFS enviados correctamente"
    else
        print_warning "No se pudieron enviar archivos LFS (puede ser normal si no hay archivos LFS)"
    fi

    print_info "Enviando cambios al repositorio remoto..."
    git push origin master
    print_success "Cambios enviados correctamente al repositorio remoto"
}

# =============================================================================
# INICIO DEL SCRIPT
# =============================================================================

clear
print_message "$MAGENTA" "╔═══════════════════════════════════════════════════════════╗"
print_message "$MAGENTA" "║     SCRIPT DE SUBIDA DE CAMBIOS - REPOSITORIO GIT         ║"
print_message "$MAGENTA" "║                Versión 1.0 - Perú 🇵🇪                      ║"
print_message "$MAGENTA" "╚═══════════════════════════════════════════════════════════╝"
echo ""

# =============================================================================
# PASO 0: VALIDACIONES INICIALES
# =============================================================================

print_section "PASO 0: VALIDACIONES INICIALES"
validate_project_structure
validate_git_repository

# =============================================================================
# PASO 1: CONFIGURAR PERMISOS DE SCRIPTS
# =============================================================================

print_section "PASO 1: CONFIGURANDO PERMISOS DE SCRIPTS"

if [ -f "$COMPRESS_SCRIPT" ] && [ -f "$DECOMPRESS_SCRIPT" ]; then
    print_info "Dando permisos de ejecución a scripts de compresión..."
    chmod +x "$COMPRESS_SCRIPT"
    chmod +x "$DECOMPRESS_SCRIPT"
    print_success "Permisos configurados correctamente"
else
    print_warning "No se encontraron scripts de compresión, omitiendo..."
fi

# =============================================================================
# PASO 2: LIMPIEZA DE DIRECTORIOS
# =============================================================================

print_section "PASO 2: LIMPIANDO DIRECTORIOS"
clean_vendor_directory

# =============================================================================
# PASO 3: CONFIGURAR GIT LFS
# =============================================================================

print_section "PASO 3: CONFIGURANDO GIT LFS"
configure_git_lfs

# =============================================================================
# PASO 4: COMMIT DE CAMBIOS
# =============================================================================

print_section "PASO 4: CREANDO COMMIT CON CAMBIOS"
COMMIT_MESSAGE=$(commit_changes)

# =============================================================================
# PASO 5: PUSH AL REPOSITORIO REMOTO
# =============================================================================

print_section "PASO 5: ENVIANDO CAMBIOS AL REPOSITORIO REMOTO"
push_changes

# =============================================================================
# PASO 6: VERIFICACIÓN FINAL
# =============================================================================

print_section "PASO 6: VERIFICACIÓN FINAL"

print_info "Verificando estado final del repositorio..."
git status

echo ""
print_message "$GREEN" "┌──────────────────────────────────────────────────────────┐"
print_message "$GREEN" "│   🎉 CAMBIOS SUBIDOS EXITOSAMENTE 🎉                     │"
print_message "$GREEN" "└──────────────────────────────────────────────────────────┘"
echo ""

print_info "Resumen de la operación:"
print_info "  ✅ Scripts con permisos configurados"
print_info "  ✅ Carpeta vendor limpiada"
print_info "  ✅ Git LFS configurado para archivos .tar.gz"
print_info "  ✅ Cambios commitidos: $COMMIT_MESSAGE"
print_info "  ✅ Cambios enviados al repositorio remoto"
echo ""

print_info "Próximos pasos:"
print_info "  1. En el servidor de producción, ejecuta: ./devops_deploy_stack.sh"
print_info "  2. Verifica que la aplicación funcione correctamente"
print_info "  3. Monitorea los logs si es necesario"
echo ""

print_info "Comandos útiles para monitoreo:"
print_info "  📊 Ver logs: docker compose -f $DOCKER_COMPOSE_FILE logs -f"
print_info "  📊 Ver estado: docker ps -a"
print_info "  🔍 Verificar DB: docker exec fpm1 php artisan migrate:status"
echo ""

print_success "¡Los cambios han sido subidos exitosamente! 🇵🇪"
echo ""

# =============================================================================
# REGISTRO DE OPERACIÓN
# =============================================================================

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
echo "$TIMESTAMP - Cambios subidos exitosamente - $COMMIT_MESSAGE" >> upload.log
print_info "Registro de subida guardado en upload.log"

echo ""
print_info "Últimos 5 registros de subida:"
tail -n 5 upload.log 2>/dev/null || echo "  No hay registros previos"
echo ""