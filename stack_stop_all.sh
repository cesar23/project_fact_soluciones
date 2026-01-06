#!/bin/bash

# =============================================================================
# SCRIPT DE DETENCIÓN - SERVICIOS FACTURADOR SMART
# =============================================================================
# Script simple para detener todos los servicios del stack
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
print_message "$RED" "╔════════════════════════════════════════════════════════════╗"
print_message "$RED" "║          DETENIENDO SERVICIOS FACTURADOR SMART         ║"
print_message "$RED" "╚════════════════════════════════════════════════════════════╝"
echo ""

# Servicios a detener (archivo compose y nombre descriptivo)
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

# Detener cada servicio
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

    # Intentar detener el servicio
    print_info "Deteniendo servicio..."
    if docker compose -f "$compose_file" down; then
        print_success "$service_name detenido correctamente"
        SUCCESS=$((SUCCESS + 1))
    else
        print_error "Falló al detener $service_name (puede que no esté corriendo)"
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
print_success "Detenidos correctamente: $SUCCESS"

if [ $FAILED -gt 0 ]; then
    print_message "$YELLOW" "⚠️  Con advertencias: $FAILED"
fi

if [ $SKIPPED -gt 0 ]; then
    print_message "$YELLOW" "⚠️  Omitidos (archivo no encontrado): $SKIPPED"
fi

# Mostrar contenedores que aún están corriendo
echo ""
print_info "Contenedores aún en ejecución:"
echo ""
RUNNING=$(docker ps -q | wc -l)
if [ $RUNNING -eq 0 ]; then
    print_success "No hay contenedores en ejecución"
else
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
fi

# Mensaje final
echo ""
if [ $FAILED -eq 0 ] && [ $SUCCESS -gt 0 ]; then
    print_message "$GREEN" "╔════════════════════════════════════════════════════════════╗"
    print_message "$GREEN" "║        🛑 TODOS LOS SERVICIOS DETENIDOS! 🛑           ║"
    print_message "$GREEN" "╚════════════════════════════════════════════════════════════╝"
elif [ $SUCCESS -gt 0 ]; then
    print_message "$YELLOW" "⚠️  Algunos servicios se detuvieron, pero hubo advertencias"
else
    print_message "$YELLOW" "⚠️  No se pudo detener ningún servicio"
    print_info "Es posible que los servicios no estuvieran corriendo"
fi

echo ""
print_info "Para volver a iniciar los servicios, ejecuta: ./start_services.sh"
echo ""



# docker compose -f stack-facturador-smart/smart1/docker-compose.yml down
# docker compose -f stack-facturador-smart/utils/docker-compose.yml down
# docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml down
# docker compose -f stack-facturador-smart/npm/docker-compose.yml down