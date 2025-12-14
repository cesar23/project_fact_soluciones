#!/bin/bash

# =============================================================================
# SCRIPT DE SUBIDA DE CAMBIOS - STACK FACTURADOR SMART
# =============================================================================
# Este script automatiza el proceso de subida de cambios al repositorio
# Incluye: permisos, compresión, configuración de LFS y push
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
print_message "$MAGENTA" "║     SCRIPT DE SUBIDA DE CAMBIOS - REPOSITORIO GIT         ║"
print_message "$MAGENTA" "║                Versión 1.0 - Perú 🇵🇪                      ║"
print_message "$MAGENTA" "╚════════════════════════════════════════════════════════════╝"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "stack-facturador-smart/smart1/docker-compose.yml" ]; then
    print_error "No se encuentra el archivo docker-compose.yml"
    print_info "Asegúrate de estar en el directorio raíz del proyecto"
    exit 1
fi

# Verificar estado de git
print_info "Verificando estado del repositorio Git..."
if ! git status > /dev/null 2>&1; then
    print_error "No se puede acceder al repositorio Git"
    print_info "Asegúrate de estar en un repositorio Git válido"
    exit 1
fi

# =============================================================================
# PASO 1: PERMISOS DE SCRIPTS
# =============================================================================
print_section "PASO 1: CONFIGURANDO PERMISOS DE SCRIPTS"

print_info "Dando permisos de ejecución a scripts de compresión..."
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh
print_success "Permisos configurados correctamente"

# =============================================================================
# PASO 2: LIMPIAR CARPETA VENDOR
# =============================================================================
print_section "PASO 2: LIMPIANDO CARPETA VENDOR"

print_info "Eliminando contenido de la carpeta vendor..."
if [ -d "stack-facturador-smart/smart1/vendor" ]; then
    rm -rf stack-facturador-smart/smart1/vendor/*
    print_success "Carpeta vendor limpiada correctamente"
else
    print_warning "No se encuentra la carpeta vendor, omitiendo..."
fi

# =============================================================================
# PASO 3: COMPRIMIR ARCHIVO
# =============================================================================
print_section "PASO 3: COMPRIMIENDO ARCHIVO smart1.tar.gz"

print_info "Ejecutando script de compresión..."
if [ -f "stack-facturador-smart/smart1_compress.sh" ]; then
    if ./stack-facturador-smart/smart1_compress.sh; then
        print_success "Archivo comprimido correctamente"
        
        # Verificar que el archivo se creó
        if [ -f "stack-facturador-smart/smart1.tar.gz" ]; then
            # Mostrar tamaño del archivo
            FILE_SIZE=$(du -h stack-facturador-smart/smart1.tar.gz | cut -f1)
            print_info "Tamaño del archivo comprimido: $FILE_SIZE"
        else
            print_error "No se encontró el archivo comprimido después de la compresión"
            exit 1
        fi
    else
        print_error "Error al comprimir el archivo"
        exit 1
    fi
else
    print_error "No se encuentra el script de compresión"
    exit 1
fi

# =============================================================================
# PASO 4: CONFIGURAR GIT LFS
# =============================================================================
print_section "PASO 4: CONFIGURANDO GIT LFS"

# Verificar si Git LFS está instalado
if ! command -v git-lfs &> /dev/null; then
    print_warning "Git LFS no está instalado"
    print_info "Instalando Git LFS..."
    
    # Intentar instalar Git LFS
    if command -v apt-get &> /dev/null; then
        sudo apt-get update && sudo apt-get install -y git-lfs
    elif command -v yum &> /dev/null; then
        sudo yum install -y git-lfs
    else
        print_error "No se pudo instalar Git LFS automáticamente"
        print_info "Por favor instala Git LFS manualmente"
        exit 1
    fi
fi

# Inicializar Git LFS si no está inicializado
if ! git lfs env | grep -q "git-lfs"; then
    print_info "Inicializando Git LFS..."
    git lfs install
    print_success "Git LFS inicializado correctamente"
fi

print_info "Configurando tracking para archivos .tar.gz..."
git lfs track "stack-facturador-smart/smart1.tar.gz"
git lfs track "*.tar.gz"
print_success "Tracking de Git LFS configurado correctamente"

# =============================================================================
# PASO 5: AGREGAR Y COMMIT CAMBIOS
# =============================================================================
print_section "PASO 5: SUBIENDO CAMBIOS AL REPOSITORIO"

print_info "Mostrando cambios pendientes..."
git status

echo ""
print_info "Agregando todos los cambios..."
if git add .; then
    print_success "Cambios agregados al staging area"
else
    print_error "Error al agregar cambios"
    exit 1
fi

# Solicitar mensaje de commit
echo ""
print_info "Ingrese el mensaje para el commit:"
print_info "(Presiona Enter para usar el mensaje por defecto)"
read -p "Mensaje: " COMMIT_MESSAGE

if [ -z "$COMMIT_MESSAGE" ]; then
    DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || TZ="America/Lima" date "+%Y-%m-%d_%H:%M:%S" 2>/dev/null || date '+%Y-%m-%d_%H:%M:%S')
    COMMIT_MESSAGE="Actualización de código y configuración - ${DATE_HOUR_PE}"
    print_info "Usando mensaje por defecto: $COMMIT_MESSAGE"
fi

print_info "Realizando commit..."
if git commit -m "$COMMIT_MESSAGE"; then
    print_success "Commit realizado correctamente"
else
    print_error "Error al realizar el commit"
    exit 1
fi

# =============================================================================
# PASO 6: PUSH A REPOSITORIO REMOTO
# =============================================================================
print_section "PASO 6: ENVIANDO CAMBIOS AL REPOSITORIO REMOTO"

print_info "Enviando cambios con Git LFS..."
if git lfs push origin master; then
    print_success "Archivos LFS enviados correctamente"
else
    print_warning "No se pudieron enviar los archivos LFS (puede ser normal si es la primera vez)"
fi

print_info "Enviando cambios al repositorio remoto..."
if git push origin master; then
    print_success "Cambios enviados correctamente al repositorio remoto"
else
    print_error "Error al enviar cambios al repositorio remoto"
    print_info "Intenta manualmente: git push origin master"
    exit 1
fi

# =============================================================================
# VERIFICACIÓN FINAL
# =============================================================================
print_section "VERIFICACIÓN FINAL"

print_info "Verificando estado final del repositorio..."
git status

echo ""
print_message "$GREEN" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
print_message "$GREEN" "   🎉 CAMBIOS SUBIDOS EXITOSAMENTE 🎉"
print_message "$GREEN" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

print_info "Resumen de la operación:"
print_info "  ✅ Scripts con permisos configurados"
print_info "  ✅ Carpeta vendor limpiada"
print_info "  ✅ Archivo smart1.tar.gz comprimido"
print_info "  ✅ Git LFS configurado para archivos .tar.gz"
print_info "  ✅ Cambios commitidos con mensaje: $COMMIT_MESSAGE"
print_info "  ✅ Cambios enviados al repositorio remoto"
echo ""

print_info "Próximos pasos:"
print_info "  1. En el servidor de producción, ejecuta: ./deploy_stack.sh"
print_info "  2. Verifica que la aplicación funcione correctamente"
print_info "  3. Monitorea los logs si es necesario"
echo ""

print_success "¡Los cambios han sido subidos exitosamente! 🇵🇪"
echo ""

# Registrar la subida
echo "$(date): Cambios subidos exitosamente - $COMMIT_MESSAGE" >> upload.log
print_info "Registro de subida guardado en upload.log"