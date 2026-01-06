#!/bin/bash

# =============================================================================
# 📋 SCRIPT: backup_db_facturador.sh
# =============================================================================
# 📝 Descripción:
#   Script para hacer backup de múltiples bases de datos (MariaDB) del stack
#   facturador.
#   - Extrae dumps de múltiples BDs desde el contenedor de MariaDB
#   - Crea backups comprimidos individuales con fecha y hora
#   - Muestra barra de progreso si pv está instalado
#   - Rota automáticamente backups antiguos (mantiene los últimos 10 por BD)
#   - Soporte para backup de múltiples bases de datos simultáneamente
#
# 🔧 Uso:
#   chmod +x backup_db_facturador.sh
#   ./backup_db_facturador.sh
#
# 📁 Ubicación de backups:
#   /home/cesar/backup-bd-facturador/
#
# 🗄️ Bases de datos configuradas:
#   - smart1 (base de datos principal)
#   - tenancy (base de datos de multi-tenancy)
#   - Agregar más bases de datos en el array DB_NAMES
# =============================================================================


# Fecha y hora actual en formato: YYYY-MM-DD_HH:MM:SS (hora local de Perú UTC-5)
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S")

# =============================================================================
# 🎨 SECTION: Colores para su uso (Copiado de tu script original)
# =============================================================================
# Definición de colores que se pueden usar en la salida del terminal.

# Colores Regulares
Color_Off='\033[0m'       # Reset de color.
Red='\033[0;31m'          # Rojo.
Green='\033[0;32m'        # Verde.
Yellow='\033[0;33m'       # Amarillo.
Blue='\033[0;34m'         # Azul.
Purple='\033[0;35m'       # Púrpura.
Cyan='\033[0;36m'         # Cian.
Gray='\033[0;90m'         # Gris.

# Colores en Negrita
BRed='\033[1;31m'         # Rojo (negrita).
BGreen='\033[1;32m'       # Verde (negrita).
BYellow='\033[1;33m'      # Amarillo (negrita).
BBlue='\033[1;34m'        # Azul (negrita).
BPurple='\033[1;35m'      # Púrpura (negrita).
BCyan='\033[1;36m'        # Cian (negrita).
BWhite='\033[1;37m'       # Blanco (negrita).
BGray='\033[1;90m'        # Gris (negrita).


# =============================================================================
# ⚙️ SECTION: Core Function (Copiado de tu script original)
# =============================================================================

# 📝 Función: msg (Copiado de tu script original)
msg() {
  local message="$1"
  local level="${2:-INFO}"
  local timestamp
  timestamp=$(date -u -d "-5 hours" "+%Y-%m-%d %H:%M:%S")

  local SHOW_DETAIL=1
  # La lógica para Termux se mantiene, aunque menos relevante para un servidor Linux típico.
  if [ -n "$SO_SYSTEM" ] && [ "$SO_SYSTEM" = "termux" ]; then
    SHOW_DETAIL=0
  fi

  case "$level" in
    INFO)
        if [ "$SHOW_DETAIL" -eq 0 ]; then
          echo -e "${BBlue}[INFO]${Color_Off} ${message}"
        else
          echo -e "${BBlue}${timestamp} - [INFO]${Color_Off} ${message}"
        fi
        ;;
    WARNING)
        if [ "$SHOW_DETAIL" -eq 0 ]; then
          echo -e "${BYellow}[WARNING]${Color_Off} ${message}"
        else
          echo -e "${BYellow}${timestamp} - [WARNING]${Color_Off} ${message}"
        fi
        ;;
    DEBUG)
        if [ "$SHOW_DETAIL" -eq 0 ]; then
          echo -e "${BPurple}[DEBUG]${Color_Off} ${message}"
        else
          echo -e "${BPurple}${timestamp} - [DEBUG]${Color_Off} ${message}"
        fi
        ;;
    ERROR)
        if [ "$SHOW_DETAIL" -eq 0 ]; then
          echo -e "${BRed}[ERROR]${Color_Off} ${message}"
        else
          echo -e "${BRed}${timestamp} - [ERROR]${Color_Off} ${message}"
        fi
        ;;
    SUCCESS)
        if [ "$SHOW_DETAIL" -eq 0 ]; then
          echo -e "${BGreen}[SUCCESS]${Color_Off} ${message}"
        else
          echo -e "${BGreen}${timestamp} - ${BGreen}[SUCCESS]${Color_Off} ${message}"
        fi
        ;;
    *)
          echo -e "${BGray}[OTHER]${Color_Off} ${message}"
        ;;
  esac
}

# 📝 Función: rotar_backups
# Rota backups tar.gz manteniendo los más recientes
rotar_backups() {
  local dir_backup="$1"
  local max_backups="${2:-10}"

  # Contar cuántos backups tar.gz hay
  local num_backups=$(ls -1 "$dir_backup"/facturador_db_*.tar.gz 2>/dev/null | wc -l)

  if [ "$num_backups" -gt "$max_backups" ]; then
    msg "Rotando backups antiguos (manteniendo los últimos $max_backups)" "INFO"

    # Obtener lista de archivos a eliminar (los más antiguos)
    local archivos_eliminar=$(ls -1t "$dir_backup"/facturador_db_*.tar.gz | tail -n +$((max_backups + 1)))

    for archivo in $archivos_eliminar; do
      rm -f "$archivo"
      msg "  ${Red}✗${Color_Off} Eliminado: $(basename "$archivo")" "INFO"
    done
  fi
}

# 📝 Función: verificar_base_datos_existe
# Verifica si una base de datos existe en el contenedor
verificar_base_datos_existe() {
  local container="$1"
  local db_name="$2"
  local db_user="$3"
  local db_password="$4"

  # Ejecutar comando para listar bases de datos y buscar la específica
  local existe=$(docker exec -i "$container" sh -c "mysql -u${db_user} -p${db_password} -e 'SHOW DATABASES;'" 2>/dev/null | grep -w "$db_name")

  if [ -n "$existe" ]; then
    return 0  # Existe
  else
    return 1  # No existe
  fi
}

# 📝 Función: backup_base_datos
# Realiza el backup de una base de datos específica en formato SQL sin comprimir
backup_base_datos() {
  local container="$1"
  local db_name="$2"
  local db_user="$3"
  local db_password="$4"
  local dir_temporal="$5"

  # Generar nombre del archivo de backup (sin comprimir)
  local archivo_backup="${db_name}.sql"
  local path_archivo_backup="$dir_temporal/$archivo_backup"

  echo -e "${BYellow}┌─────────────────────────────────────────────────────────────┐${Color_Off}"
  echo -e "${BYellow}│  💾 Procesando: ${White}${db_name}${BYellow}${Color_Off}"
  echo -e "${BYellow}└─────────────────────────────────────────────────────────────┘${Color_Off}"
  echo -e "  ${Cyan}▶${Color_Off} Base de Datos: ${White}$db_name${Color_Off}"
  echo -e "  ${Cyan}▶${Color_Off} Archivo: ${White}$archivo_backup${Color_Off}"
  echo ""

  # Verificar si la base de datos existe
  if ! verificar_base_datos_existe "$container" "$db_name" "$db_user" "$db_password"; then
    msg "La base de datos '$db_name' no existe en el contenedor. Saltando..." "WARNING"
    echo ""
    return 1
  fi

  # Comando mysqldump dentro del contenedor
  local dump_command="mysqldump -u${db_user} -p${db_password} ${db_name}"

  # Iniciar tiempo
  local tiempo_inicio=$(date +%s)

  echo -ne "  ${Yellow}⏳${Color_Off} Extrayendo base de datos"

  # Ejecutar mysqldump y guardar en archivo SQL sin comprimir
  docker exec -i "$container" sh -c "$dump_command" > "$path_archivo_backup" &
  local pid=$!
  local contador=0

  while kill -0 $pid 2>/dev/null; do
      echo -n "."
      sleep 1
      ((contador++))
      # Mostrar tiempo cada 10 segundos
      if [ $((contador % 10)) -eq 0 ]; then
          echo -ne " ${Gray}[${contador}s]${Color_Off}"
      fi
  done
  echo ""

  wait $pid
  local exit_code=$?

  # Calcular tiempo transcurrido
  local tiempo_fin=$(date +%s)
  local tiempo_total=$((tiempo_fin - tiempo_inicio))

  if [ $tiempo_total -lt 60 ]; then
      echo -e "  ${Green}⏱${Color_Off} Tiempo: ${White}${tiempo_total}s${Color_Off}"
  else
      local minutos=$((tiempo_total / 60))
      local segundos=$((tiempo_total % 60))
      echo -e "  ${Green}⏱${Color_Off} Tiempo: ${White}${minutos}m ${segundos}s${Color_Off}"
  fi

  # Verificar si el backup se creó correctamente
  if [ "$exit_code" -eq 0 ] && [ -f "$path_archivo_backup" ]; then
    local tamano=$(du -h "$path_archivo_backup" | cut -f1)
    msg "Backup de '$db_name' creado exitosamente (Tamaño: $tamano)" "SUCCESS"
    return 0
  else
    msg "Error al crear el backup de '$db_name'" "ERROR"
    return 1
  fi
}

# 📝 Función: comprimir_carpeta_temporal
# Comprime la carpeta temporal con tar.gz usando pv
comprimir_carpeta_temporal() {
  local dir_temporal="$1"
  local dir_backup="$2"
  local fecha_hora="$3"

  local nombre_carpeta=$(basename "$dir_temporal")
  local archivo_comprimido="${nombre_carpeta}.tar.gz"
  local path_archivo_comprimido="$dir_backup/$archivo_comprimido"

  echo -e "${BYellow}📦 Comprimiendo carpeta temporal con tar.gz${Color_Off}"
  echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
  echo -e "  ${Cyan}▶${Color_Off} Carpeta: ${White}$nombre_carpeta${Color_Off}"
  echo -e "  ${Cyan}▶${Color_Off} Archivo de salida: ${White}$archivo_comprimido${Color_Off}"
  echo ""

  # Calcular tamaño de la carpeta temporal para pv
  local tamano_carpeta=$(du -sb "$dir_temporal" | cut -f1)

  # Iniciar tiempo
  local tiempo_inicio=$(date +%s)

  # Comprimir con tar y pv
  if command -v pv &> /dev/null; then
    echo -e "  ${Yellow}⏳${Color_Off} Comprimiendo con barra de progreso:"
    echo -e "${Gray}  ─────────────────────────────────────────────────────────${Color_Off}"

    tar -cf - -C "$(dirname "$dir_temporal")" "$nombre_carpeta" | \
        pv -s "$tamano_carpeta" -petr | \
        gzip > "$path_archivo_comprimido"

    local exit_code=$?

    echo -e "${Gray}  ─────────────────────────────────────────────────────────${Color_Off}"
  else
    # Sin pv
    echo -ne "  ${Yellow}⏳${Color_Off} Comprimiendo"
    tar -czf "$path_archivo_comprimido" -C "$(dirname "$dir_temporal")" "$nombre_carpeta" &
    local pid=$!
    local contador=0

    while kill -0 $pid 2>/dev/null; do
        echo -n "."
        sleep 1
        ((contador++))
        if [ $((contador % 10)) -eq 0 ]; then
            echo -ne " ${Gray}[${contador}s]${Color_Off}"
        fi
    done
    echo ""

    wait $pid
    local exit_code=$?
  fi

  # Calcular tiempo transcurrido
  local tiempo_fin=$(date +%s)
  local tiempo_total=$((tiempo_fin - tiempo_inicio))

  if [ $tiempo_total -lt 60 ]; then
      echo -e "  ${Green}⏱${Color_Off} Tiempo de compresión: ${White}${tiempo_total}s${Color_Off}"
  else
      local minutos=$((tiempo_total / 60))
      local segundos=$((tiempo_total % 60))
      echo -e "  ${Green}⏱${Color_Off} Tiempo de compresión: ${White}${minutos}m ${segundos}s${Color_Off}"
  fi

  # Verificar si la compresión fue exitosa
  if [ "$exit_code" -eq 0 ] && [ -f "$path_archivo_comprimido" ]; then
    local tamano_comprimido=$(du -h "$path_archivo_comprimido" | cut -f1)
    msg "Compresión completada exitosamente (Tamaño: $tamano_comprimido)" "SUCCESS"
    echo "$path_archivo_comprimido|$tamano_comprimido"
    return 0
  else
    msg "Error al comprimir la carpeta temporal" "ERROR"
    return 1
  fi
}


# =============================================================================
# 🔥 SECTION: Main Code - Configuración
# =============================================================================

#:::::::::::::::::::::::::::
# Obtenemos el env de utils
source ./stack-facturador-smart/utils/.env

# Formatear fecha/hora sin dos puntos para nombres de archivo
DATE_HOUR_PE=$(echo "$DATE_HOUR_PE" | sed 's/://g')

# =============================================================================
# 🗄️ CONFIGURACIÓN DE BASES DE DATOS
# =============================================================================
# IMPORTANTE: Aquí se definen las bases de datos a respaldar
# Para agregar más bases de datos, simplemente añádelas al array DB_NAMES

# Array de nombres de bases de datos a respaldar
# Puedes agregar o quitar bases de datos según necesites
DB_NAMES=("smart1" "tenancy_demo" "tenancy_tienda")

# Configuración del contenedor y credenciales
CONTAINER_NAME="${MYSQL_CONTAINER}"                       # Nombre del servicio/contenedor de la BD
DB_USER="root"                                            # Usuario para mysqldump
DB_PASSWORD="${MYSQL_ROOT_PASSWORD}"                      # Contraseña del usuario 'root'

# Configuración del backup
DIR_BACKUP="/home/cesar/backup-bd-facturador"            # Directorio de destino
MAX_BACKUPS=10                                            # Número máximo de backups a mantener por BD

# =============================================================================
# 🔍 VALIDACIÓN INICIAL
# =============================================================================
# Verificar que se hayan definido bases de datos
if [ ${#DB_NAMES[@]} -eq 0 ]; then
    echo -e "${BRed}[ERROR]${Color_Off} No se han definido bases de datos para respaldar."
    echo -e "${Yellow}Por favor, agrega bases de datos al array DB_NAMES en el script.${Color_Off}"
    exit 1
fi

# =============================================================================
# 🚀 SECTION: Inicio del proceso
# =============================================================================

clear
echo -e "${BCyan}╔══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BCyan}║        ${BWhite}💾 BACKUP DE BASES DE DATOS FACTURADOR${BCyan}          ║${Color_Off}"
echo -e "${BCyan}╚══════════════════════════════════════════════════════════════╝${Color_Off}"
echo ""

msg "Iniciando proceso de backup de múltiples bases de datos" "INFO"
echo -e "  ${Cyan}▶${Color_Off} Bases de datos a respaldar: ${White}${#DB_NAMES[@]}${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Lista: ${White}${DB_NAMES[*]}${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo ""

# =============================================================================
# 🗂 STEP 1: Preparar directorios (principal y temporal)
# =============================================================================

echo -e "${BYellow}🗂  PASO 1: ${White}Preparando entorno${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

# Crear directorio principal si no existe
if [ ! -d "$DIR_BACKUP" ]; then
    mkdir -p "$DIR_BACKUP"
    msg "Directorio de backup creado: $DIR_BACKUP" "INFO"
else
    msg "Usando directorio existente: $DIR_BACKUP" "INFO"
fi

# Crear carpeta temporal con nombre: facturador_db_fecha
DIR_TEMPORAL="$DIR_BACKUP/facturador_db_${DATE_HOUR_PE}"
mkdir -p "$DIR_TEMPORAL"
msg "Carpeta temporal creada: $(basename $DIR_TEMPORAL)" "INFO"

# Verificar si el contenedor de BD está corriendo
if ! docker inspect -f '{{.State.Running}}' "$CONTAINER_NAME" 2>/dev/null | grep -q "true"; then
    msg "El contenedor '$CONTAINER_NAME' no está corriendo." "ERROR"
    # Limpiar carpeta temporal
    rm -rf "$DIR_TEMPORAL"
    exit 1
fi
msg "Contenedor '$CONTAINER_NAME' está corriendo correctamente" "SUCCESS"

# Verificar si pv está instalado
if ! command -v pv &> /dev/null; then
    msg "La herramienta 'pv' no está instalada, no se mostrará barra de progreso." "WARNING"
fi
echo ""

# =============================================================================
# 💾 STEP 2: Procesar backups de bases de datos en carpeta temporal
# =============================================================================

echo -e "${BYellow}💾 PASO 2: ${White}Extrayendo bases de datos a carpeta temporal${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Contenedor: ${White}$CONTAINER_NAME${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Carpeta temporal: ${White}$(basename $DIR_TEMPORAL)${Color_Off}"
echo ""

# Arrays para rastrear resultados
declare -a BACKUPS_EXITOSOS
declare -a BACKUPS_FALLIDOS

# Procesar cada base de datos
for DB_NAME in "${DB_NAMES[@]}"; do
    # Llamar a la función de backup (ahora guarda en carpeta temporal sin comprimir)
    if backup_base_datos "$CONTAINER_NAME" "$DB_NAME" "$DB_USER" "$DB_PASSWORD" "$DIR_TEMPORAL"; then
        BACKUPS_EXITOSOS+=("$DB_NAME")
    else
        BACKUPS_FALLIDOS+=("$DB_NAME")
    fi

    echo ""
done

# Verificar si se creó al menos un backup
if [ ${#BACKUPS_EXITOSOS[@]} -eq 0 ]; then
    msg "No se pudo crear ningún backup. Limpiando y saliendo..." "ERROR"
    rm -rf "$DIR_TEMPORAL"
    exit 1
fi

# =============================================================================
# 📦 STEP 3: Comprimir carpeta temporal con tar.gz
# =============================================================================

echo -e "${BYellow}📦 PASO 3: ${White}Comprimiendo carpeta temporal${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo ""

# Llamar a la función de compresión
RESULTADO_COMPRESION=$(comprimir_carpeta_temporal "$DIR_TEMPORAL" "$DIR_BACKUP" "$DATE_HOUR_PE")
COMPRESION_EXITOSA=$?

if [ $COMPRESION_EXITOSA -ne 0 ]; then
    msg "Error al comprimir la carpeta temporal" "ERROR"
    rm -rf "$DIR_TEMPORAL"
    exit 1
fi

# Extraer información del resultado
IFS='|' read -r PATH_ARCHIVO_COMPRIMIDO TAMANO_COMPRIMIDO <<< "$RESULTADO_COMPRESION"
NOMBRE_ARCHIVO_COMPRIMIDO=$(basename "$PATH_ARCHIVO_COMPRIMIDO")

echo ""

# =============================================================================
# 🗑️  STEP 4: Eliminar carpeta temporal
# =============================================================================

echo -e "${BYellow}🗑️  PASO 4: ${White}Limpiando carpeta temporal${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

rm -rf "$DIR_TEMPORAL"

if [ ! -d "$DIR_TEMPORAL" ]; then
    msg "Carpeta temporal eliminada correctamente: $(basename $DIR_TEMPORAL)" "SUCCESS"
else
    msg "Advertencia: No se pudo eliminar completamente la carpeta temporal" "WARNING"
fi
echo ""

# =============================================================================
# 🔄 STEP 5: Rotar backups antiguos
# =============================================================================

echo -e "${BYellow}🔄 PASO 5: ${White}Rotación de backups antiguos${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

rotar_backups "$DIR_BACKUP" "$MAX_BACKUPS"

# Contar backups actuales
NUM_BACKUPS_ACTUALES=$(ls -1 "$DIR_BACKUP"/facturador_db_*.tar.gz 2>/dev/null | wc -l)
msg "Backups tar.gz actuales: $NUM_BACKUPS_ACTUALES/$MAX_BACKUPS" "INFO"

echo ""

# =============================================================================
# 🔐 STEP 6: Ajustar permisos
# =============================================================================

echo -e "${BYellow}🔐 PASO 6: ${White}Ajustando permisos${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
sudo chown -R $USER_NAME:$USER_NAME "${DIR_BACKUP}"
msg "Permisos ajustados correctamente para usuario: $USER_NAME" "SUCCESS"
echo ""

# =============================================================================
# ✅ SECTION: Resumen final
# =============================================================================

echo -e "${BCyan}╔══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BCyan}║              ${BGreen}✅ PROCESO DE BACKUP COMPLETADO${BCyan}              ║${Color_Off}"
echo -e "${BCyan}╚══════════════════════════════════════════════════════════════╝${Color_Off}"
echo ""

# Mostrar estadísticas generales
echo -e "${BWhite}📊 ESTADÍSTICAS GENERALES:${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo -e "  ${Green}📁${Color_Off} Ubicación: ${White}$DIR_BACKUP/${Color_Off}"
echo -e "  ${Green}✓${Color_Off} Backups exitosos: ${White}${#BACKUPS_EXITOSOS[@]}${Color_Off}"
echo -e "  ${Red}✗${Color_Off} Backups fallidos: ${White}${#BACKUPS_FALLIDOS[@]}${Color_Off}"
echo -e "  ${Green}🗄️${Color_Off} Bases de datos procesadas: ${White}${#DB_NAMES[@]}${Color_Off}"
echo ""

# Mostrar información del archivo comprimido
echo -e "${BGreen}📦 ARCHIVO COMPRIMIDO CREADO:${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo -e "  ${Green}📦${Color_Off} Archivo: ${White}$NOMBRE_ARCHIVO_COMPRIMIDO${Color_Off}"
echo -e "  ${Green}💾${Color_Off} Tamaño: ${White}$TAMANO_COMPRIMIDO${Color_Off}"
echo -e "  ${Green}📂${Color_Off} Ruta completa: ${White}$PATH_ARCHIVO_COMPRIMIDO${Color_Off}"
echo ""

# Mostrar bases de datos incluidas
echo -e "${BWhite}🗄️  BASES DE DATOS INCLUIDAS EN EL BACKUP:${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
for db_name in "${BACKUPS_EXITOSOS[@]}"; do
    echo -e "  ${Green}✓${Color_Off} ${White}$db_name${Color_Off}"
done

if [ ${#BACKUPS_FALLIDOS[@]} -gt 0 ]; then
    echo ""
    echo -e "${BRed}❌ BASES DE DATOS NO INCLUIDAS (FALLIDAS):${Color_Off}"
    echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
    for db_name in "${BACKUPS_FALLIDOS[@]}"; do
        echo -e "  ${Red}✗${Color_Off} ${White}$db_name${Color_Off}"
    done
fi
echo ""

# Mostrar lista de backups disponibles
echo -e "${BWhite}📚 BACKUPS TAR.GZ DISPONIBLES:${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
ls -1t "$DIR_BACKUP"/facturador_db_*.tar.gz 2>/dev/null | head -n "$MAX_BACKUPS" | while read backup; do
    tamano=$(du -h "$backup" | cut -f1)
    fecha=$(basename "$backup" | sed 's/facturador_db_\(.*\)\.tar\.gz/\1/')
    echo -e "  ${Cyan}•${Color_Off} $(basename "$backup") ${Gray}($tamano)${Color_Off}"
done
echo ""

# Mensaje final según el resultado
if [ ${#BACKUPS_FALLIDOS[@]} -eq 0 ]; then
    msg "Proceso completado exitosamente - Todas las bases de datos respaldadas y comprimidas" "SUCCESS"
else
    msg "Proceso completado con advertencias - Algunas bases de datos no se pudieron respaldar" "WARNING"
fi

echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

