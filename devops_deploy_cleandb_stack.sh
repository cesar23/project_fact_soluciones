#!/bin/bash

# Configuración
CONTAINER_NAME="mariadb1"
MYSQL_USER="root"
MYSQL_PASSWORD="WPsOd4xPLL4nGRnOAHJp"
DB_PATTERN="tenancy_%"

# Bases de datos adicionales a eliminar (separadas por espacio)
ADDITIONAL_DBS="smart1"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}Script de eliminación de bases de datos${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Obtener lista de bases de datos
echo -e "${YELLOW}Buscando bases de datos con patrón: ${DB_PATTERN}${NC}"
DBS=$(docker exec $CONTAINER_NAME mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -Nse "SHOW DATABASES LIKE '$DB_PATTERN';")

# Verificar si hay bases de datos
if [ -z "$DBS" ]; then
    echo -e "${GREEN}No se encontraron bases de datos con el patrón '$DB_PATTERN'${NC}"
    exit 0
fi

# Mostrar bases de datos encontradas
echo -e "${YELLOW}Bases de datos encontradas:${NC}"
echo "$DBS" | nl
echo ""

# Contar bases de datos
DB_COUNT=$(echo "$DBS" | wc -l)
echo -e "${YELLOW}Total: $DB_COUNT base(s) de datos${NC}"
echo ""

# Confirmar eliminación
read -p "¿Deseas eliminar todas estas bases de datos? (si/no): " CONFIRM

if [ "$CONFIRM" != "si" ] && [ "$CONFIRM" != "SI" ] && [ "$CONFIRM" != "s" ]; then
    echo -e "${RED}Operación cancelada${NC}"
    exit 0
fi

echo ""
echo -e "${YELLOW}Iniciando eliminación...${NC}"
echo ""

# Eliminar bases de datos
DELETED=0
FAILED=0

while IFS= read -r db; do
    echo -n "Eliminando: $db ... "
    if docker exec $CONTAINER_NAME mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -e "DROP DATABASE \`$db\`;" 2>/dev/null; then
        echo -e "${GREEN}✓ Eliminada${NC}"
        ((DELETED++))
    else
        echo -e "${RED}✗ Error${NC}"
        ((FAILED++))
    fi
done <<< "$DBS"

echo ""

# Eliminar bases de datos adicionales
docker exec mariadb1 mysql -u $MYSQL_USER -p$MYSQL_PASSWORD -e "DROP DATABASE IF EXISTS $ADDITIONAL_DBS;"


echo -e "${YELLOW}========================================${NC}"
echo -e "${GREEN}Bases de datos eliminadas: $DELETED${NC}"
if [ $FAILED -gt 0 ]; then
    echo -e "${RED}Bases de datos con error: $FAILED${NC}"
fi
echo -e "${YELLOW}========================================${NC}"