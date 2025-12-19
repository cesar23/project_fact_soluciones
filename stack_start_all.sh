#!/bin/bash

# =============================================================================
# SCRIPT DE INICIO - SERVICIOS FACTURADOR SMART
# =============================================================================
# Script simple para levantar todos los servicios del stack
# =============================================================================

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Función para imprimir mensajes
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

print_error() {
    print_message "$RED" "❌ ERROR: $1"
}

print_success() {
    print_message "$GREEN" "✅ $1"
}

print_info() {
    print_message "$CYAN" "ℹ️  $1"
}

# Banner
clear
print_message "$CYAN" "╔════════════════════════════════════════════════════════════╗"
print_message "$CYAN" "║          INICIANDO SERVICIOS FACTURADOR SMART          ║"
print_message "$CYAN" "╚════════════════════════════════════════════════════════════╝"
echo ""

# Servicios a iniciar (archivo compose y nombre descriptivo)
declare -A SERVICES=(
    ["stack-facturador-smart/smart1/docker-compose.yml"]="Smart1 (Principal)"
    ["stack-facturador-smart/utils/docker-compose.yml"]="Utilidades (phpMyAdmin)"
    ["stack-facturador-smart/cloudflare/docker-compose.yml"]="Cloudflare Tunnel"
    ["stack-facturador-smart/npm/docker-compose.yml"]="Nginx Proxy Manager"
)

# Contadores
TOTAL=0
SUCCESS=0
FAILED=0
SKIPPED=0

# Verificar e iniciar cada servicio
for compose_file in "${!SERVICES[@]}"; do
    TOTAL=$((TOTAL + 1))
    service_name="${SERVICES[$compose_file]}"

    echo ""
    print_info "Verificando: $service_name"
    print_info "Archivo: $compose_file"

    # Verificar si existe el archivo
    if [ ! -f "$compose_file" ]; then
        print_error "Archivo no encontrado, omitiendo..."
        SKIPPED=$((SKIPPED + 1))
        continue
    fi

    # Intentar levantar el servicio
    print_info "Iniciando servicio..."
    if docker compose -f "$compose_file" up -d; then
        print_success "$service_name iniciado correctamente"
        SUCCESS=$((SUCCESS + 1))
    else
        print_error "Falló al iniciar $service_name"
        FAILED=$((FAILED + 1))
    fi
done

# Resumen final
echo ""
print_message "$CYAN" "════════════════════════════════════════════════════════════"
print_message "$CYAN" "                    RESUMEN DE EJECUCIÓN"
print_message "$CYAN" "════════════════════════════════════════════════════════════"
echo ""
print_info "Total de servicios: $TOTAL"
print_success "Iniciados correctamente: $SUCCESS"

if [ $FAILED -gt 0 ]; then
    print_error "Fallidos: $FAILED"
fi

if [ $SKIPPED -gt 0 ]; then
    print_message "$YELLOW" "⚠️  Omitidos (archivo no encontrado): $SKIPPED"
fi

# Mostrar estado de contenedores
echo ""
print_info "Estado actual de los contenedores:"
echo ""
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || docker ps

# Mensaje final
echo ""
if [ $FAILED -eq 0 ] && [ $SUCCESS -gt 0 ]; then
    print_message "$GREEN" "╔════════════════════════════════════════════════════════════╗"
    print_message "$GREEN" "║        🎉 TODOS LOS SERVICIOS INICIADOS! 🎉            ║"
    print_message "$GREEN" "╚════════════════════════════════════════════════════════════╝"
    echo ""
    print_info "Accesos:"
    print_info "  🌐 Aplicación: http://localhost:8080"
    print_info "  🗄️  phpMyAdmin: http://localhost:8081"
    print_info "  🔧 NPM: http://localhost:81"
elif [ $SUCCESS -gt 0 ]; then
    print_message "$YELLOW" "⚠️  Algunos servicios se iniciaron, pero hubo fallos"
    print_info "Revisa los logs con: docker compose -f <archivo> logs"
else
    print_error "No se pudo iniciar ningún servicio"
    print_info "Verifica que Docker esté corriendo: systemctl status docker"
    exit 1
fi

echo ""

#
# docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d
# docker compose -f stack-facturador-smart/utils/docker-compose.yml up -d
# docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml up -d
# docker compose -f stack-facturador-smart/npm/docker-compose.yml up -d




