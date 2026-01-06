#!/bin/bash

# =============================================================================
# 📋 SCRIPT: backup_stack-completo.sh
# =============================================================================
# 📝 Descripción:
#   Script para hacer backup completo del stack facturador con las siguientes
#   características:
#   - Detiene temporalmente los servicios Docker-Compose
#   - Crea un backup comprimido con fecha y hora
#   - Muestra barra de progreso si pv está instalado
#   - Rota automáticamente backups antiguos (mantiene los últimos 10)
#   - Reinicia los servicios automáticamente al finalizar
#
# 🔧 Uso:
#   chmod +x backup_stack-completo.sh
#   ./backup_stack-completo.sh
#
# 📁 Ubicación de backups:
#   /home/cesar/backup-stackfacturador/
# =============================================================================


# Fecha y hora actual en formato: YYYY-MM-DD_HH:MM:SS (hora local)
DATE_HOUR=$(date "+%Y-%m-%d_%H:%M:%S")
# Fecha y hora actual en Perú (UTC -5)
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S") # Fecha y hora actuales en formato YYYY-MM-DD_HH:MM:SS.
CURRENT_USER=$(id -un)             # Nombre del usuario actual.
CURRENT_USER_HOME="${HOME:-$USERPROFILE}"  # Ruta del perfil del usuario actual.
CURRENT_PC_NAME=$(hostname)        # Nombre del equipo actual.
MY_INFO="${CURRENT_USER}@${CURRENT_PC_NAME}"  # Información combinada del usuario y del equipo.
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}")  # Ruta completa del script actual.
SCRIPT_NAME=$(basename "$PATH_SCRIPT")           # Nombre del archivo del script.
CURRENT_DIR=$(dirname "$PATH_SCRIPT")            # Ruta del directorio donde se encuentra el script.
NAME_DIR=$(basename "$CURRENT_DIR")              # Nombre del directorio actual.


# =============================================================================
# 🎨 SECTION: Colores para su uso
# =============================================================================
# Definición de colores que se pueden usar en la salida del terminal.

# Colores Regulares
Color_Off='\033[0m'       # Reset de color.
Black='\033[0;30m'        # Negro.
Red='\033[0;31m'          # Rojo.
Green='\033[0;32m'        # Verde.
Yellow='\033[0;33m'       # Amarillo.
Blue='\033[0;34m'         # Azul.
Purple='\033[0;35m'       # Púrpura.
Cyan='\033[0;36m'         # Cian.
White='\033[0;37m'        # Blanco.
Gray='\033[0;90m'         # Gris.

# Colores en Negrita
BBlack='\033[1;30m'       # Negro (negrita).
BRed='\033[1;31m'         # Rojo (negrita).
BGreen='\033[1;32m'       # Verde (negrita).
BYellow='\033[1;33m'      # Amarillo (negrita).
BBlue='\033[1;34m'        # Azul (negrita).
BPurple='\033[1;35m'      # Púrpura (negrita).
BCyan='\033[1;36m'        # Cian (negrita).
BWhite='\033[1;37m'       # Blanco (negrita).
BGray='\033[1;90m'        # Gris (negrita).

# =============================================================================
# ⚙️ SECTION: Core Function
# =============================================================================

# ==============================================================================
# 📝 Función: msg
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Imprime un mensaje con formato estándar, incluyendo:
#   - Marca de tiempo en UTC-5 (Perú)
#   - Tipo de mensaje (INFO, WARNING, ERROR, o personalizado)
#   - Colores para terminal (si están definidos previamente)
#
# 🔧 Parámetros:
#   $1 - Mensaje a mostrar (texto)
#   $2 - Tipo de mensaje (INFO | WARNING | ERROR | otro) [opcional, por defecto: INFO]
#
# 💡 Uso:
#   msg "Inicio del proceso"               # Por defecto: INFO
#   msg "Plugin no instalado" "WARNING"
#   msg "Error de conexión" "ERROR"
#   msg "Mensaje personalizado" "DEBUG"
#
# 🎨 Requiere:
#   Variables de color: BBlue, BYellow, BRed, BWhite, BGray, Color_Off
# ==============================================================================

msg() {
  local message="$1"
  local level="${2:-INFO}"
  local timestamp
  timestamp=$(date -u -d "-5 hours" "+%Y-%m-%d %H:%M:%S")

  local SHOW_DETAIL=1
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

# ------------------------------------------------------------------------------
# pause_continue
#
# Pausa la ejecución del script mostrando un mensaje en consola y espera que el
# usuario presione [ENTER] para continuar.
#
# @param $1: (opcional) Mensaje descriptivo del evento. Si no se indica, se usa
#            "Comando ejecutado" como mensaje por defecto.
# @return: No retorna valor. Pausa hasta que el usuario presione [ENTER].
# @example: pause_continue
#           # Muestra: "✅ Comando ejecutado. Presiona [ENTER] para continuar..."
# @example: pause_continue "Se instaló MySQL"
#           # Muestra: "🔹 Se instaló MySQL. Presiona [ENTER] para continuar..."
# ------------------------------------------------------------------------------
pause_continue() {
  # Determina el mensaje a mostrar según si se recibe argumento
  if [ -n "$1" ]; then
    local mensaje="🔹 $1. Presiona [ENTER] para continuar..."
  else
    local mensaje="✅ Comando ejecutado. Presiona [ENTER] para continuar..."
  fi

  # Muestra el mensaje en gris y espera la entrada del usuario
  echo -en "${Gray}"
  read -p "$mensaje"
  echo -en "${Color_Off}"
}

# ==============================================================================
# 📝 Función: rotar_backups
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Mantiene solo los últimos N backups, eliminando los más antiguos
#
# 🔧 Parámetros:
#   $1 - Directorio donde están los backups
#   $2 - Número máximo de backups a mantener (por defecto 10)
# ==============================================================================
rotar_backups() {
  local dir_backup="$1"
  local max_backups="${2:-10}"

  # Contar cuántos backups hay
  local num_backups=$(ls -1 "$dir_backup"/backup-stack-facturador_*.tar.gz 2>/dev/null | wc -l)

  if [ "$num_backups" -gt "$max_backups" ]; then
    msg "Rotando backups antiguos (manteniendo los últimos $max_backups)" "INFO"

    # Obtener lista de archivos a eliminar (los más antiguos)
    local archivos_eliminar=$(ls -1t "$dir_backup"/backup-stack-facturador_*.tar.gz | tail -n +$((max_backups + 1)))

    for archivo in $archivos_eliminar; do
      rm -f "$archivo"
      msg "  ${Red}✗${Color_Off} Eliminado: $(basename "$archivo")" "INFO"
    done
  fi
}

# =============================================================================
# 🔥 SECTION: Main Code - Configuración
# =============================================================================

#:::::::::::::::::::::::::::
# Obtenemos el env de utils
source ./stack-facturador-smart/utils/.env


DATE_HOUR_PE=$(echo "$DATE_HOUR_PE" | sed 's/://g')
# Directorios del stack
DIRS_STACK=(
    "/home/cesar/stack-facturador-smart/cloudflare"
    "/home/cesar/stack-facturador-smart/npm"
    "/home/cesar/stack-facturador-smart/smart1"
)

# Configuración del backup
DIR_ORIGEN="/home/cesar/stack-facturador-smart"
DIR_BACKUP="/home/cesar/backup-stack-facturador"
ARCHIVO_BACKUP="backup-stack-facturador_${DATE_HOUR_PE}.tar.gz"
MAX_BACKUPS=10  # Número máximo de backups a mantener

# =============================================================================
# 🚀 SECTION: Inicio del proceso
# =============================================================================

clear
echo -e "${BCyan}╔══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BCyan}║           ${BWhite}🔒 BACKUP COMPLETO DEL STACK FACTURADOR${BCyan}           ║${Color_Off}"
echo -e "${BCyan}╚══════════════════════════════════════════════════════════════╝${Color_Off}"
echo ""

msg "Iniciando proceso de backup del stack facturador" "INFO"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo ""

# =============================================================================
# 📦 STEP 1: Detener servicios Docker
# =============================================================================

echo -e "${BYellow}📦 PASO 1: ${White}Deteniendo servicios Docker-Compose${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

for dir in "${DIRS_STACK[@]}"; do
    if [ -d "$dir" ]; then
        nombre_servicio=$(basename "$dir")
        echo -e "  ${Cyan}▶${Color_Off} Deteniendo: ${White}$nombre_servicio${Color_Off}"
        cd "$dir"
        docker compose stop 2>/dev/null

        if [ $? -eq 0 ]; then
            echo -e "    ${Green}✓${Color_Off} ${Gray}Servicio detenido correctamente${Color_Off}"
        else
            echo -e "    ${Yellow}⚠${Color_Off} ${Gray}Posible error al detener (puede estar ya detenido)${Color_Off}"
        fi
    else
        msg "Directorio no encontrado: $dir" "WARNING"
    fi
done

msg "Todos los servicios han sido detenidos" "SUCCESS"
echo ""

# =============================================================================
# 🗂 STEP 2: Preparar directorio de backup
# =============================================================================

echo -e "${BYellow}🗂  PASO 2: ${White}Preparando directorio de backup${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

if [ ! -d "$DIR_BACKUP" ]; then
    mkdir -p "$DIR_BACKUP"
    msg "Directorio de backup creado: $DIR_BACKUP" "INFO"
else
    msg "Usando directorio existente: $DIR_BACKUP" "INFO"
fi

# Mostrar información de backups existentes
num_backups_actual=$(ls -1 "$DIR_BACKUP"/backup-stack-facturador_*.tar.gz 2>/dev/null | wc -l)
if [ "$num_backups_actual" -gt 0 ]; then
    echo -e "  ${Blue}ℹ${Color_Off} Backups existentes: ${White}$num_backups_actual${Color_Off}"
    echo -e "  ${Blue}ℹ${Color_Off} Límite configurado: ${White}$MAX_BACKUPS${Color_Off}"
fi
echo ""

# =============================================================================
# 💾 STEP 3: Crear backup
# =============================================================================

echo -e "${BYellow}💾 PASO 3: ${White}Creando archivo de backup${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Archivo: ${White}$ARCHIVO_BACKUP${Color_Off}"

# Verificar si pv está instalado
if ! command -v pv &> /dev/null; then
    msg "La herramienta 'pv' no está instalada" "WARNING"
    echo -e "  ${Yellow}ℹ${Color_Off} pv permite mostrar una barra de progreso durante la compresión"
    echo -ne "  ${Cyan}?${Color_Off} ¿Deseas instalar pv? (s/n): "
    read -r respuesta

    if [[ "$respuesta" =~ ^[Ss]$ ]]; then
        echo -e "  ${Blue}⚙${Color_Off} Instalando pv..."
        sudo apt-get update > /dev/null 2>&1
        sudo apt-get install -y pv > /dev/null 2>&1

        if command -v pv &> /dev/null; then
            msg "pv instalado correctamente" "SUCCESS"
        else
            msg "No se pudo instalar pv, continuando sin barra de progreso" "WARNING"
        fi
    else
        echo -e "  ${Gray}ℹ Continuando sin barra de progreso${Color_Off}"
    fi
fi

cd /home/cesar

# Calcular el tamaño total para la barra de progreso
echo -e "  ${Blue}ℹ${Color_Off} Calculando tamaño del directorio..."
TAMANO_BYTES=$(du -sb stack-facturador-smart/ | cut -f1)
TAMANO_HUMAN=$(du -sh stack-facturador-smart/ | cut -f1)
echo -e "  ${Blue}ℹ${Color_Off} Tamaño a comprimir: ${White}$TAMANO_HUMAN${Color_Off}"

# Estimar tiempo aproximado (muy aproximado: ~50MB/s para SSD, ~10MB/s para HDD)
TAMANO_MB=$((TAMANO_BYTES / 1024 / 1024))
if [ $TAMANO_MB -lt 100 ]; then
    echo -e "  ${Blue}⏱${Color_Off} Tiempo estimado: ${White}< 1 minuto${Color_Off}"
elif [ $TAMANO_MB -lt 1000 ]; then
    echo -e "  ${Blue}⏱${Color_Off} Tiempo estimado: ${White}1-3 minutos${Color_Off}"
else
    echo -e "  ${Blue}⏱${Color_Off} Tiempo estimado: ${White}3-10 minutos${Color_Off}"
fi
echo ""

# Crear backup con barra de progreso si pv está disponible
if command -v pv &> /dev/null; then
    echo -e "  ${Yellow}⏳${Color_Off} Comprimiendo con barra de progreso:"
    echo -e "${Gray}  ─────────────────────────────────────────────────────────${Color_Off}"

    # Iniciar tiempo
    TIEMPO_INICIO=$(date +%s)

    # Usar pv con opciones básicas para mostrar progreso
    # -p: porcentaje, -t: tiempo transcurrido, -e: ETA, -r: velocidad
    tar -cf - stack-facturador-smart/ 2>/dev/null | \
        pv -petrs "$TAMANO_BYTES" | \
        gzip > "$DIR_BACKUP/$ARCHIVO_BACKUP"

    echo -e "${Gray}  ─────────────────────────────────────────────────────────${Color_Off}"

    # Calcular tiempo transcurrido
    TIEMPO_FIN=$(date +%s)
    TIEMPO_TOTAL=$((TIEMPO_FIN - TIEMPO_INICIO))

    if [ $TIEMPO_TOTAL -lt 60 ]; then
        echo -e "  ${Green}⏱${Color_Off} Tiempo total: ${White}${TIEMPO_TOTAL} segundos${Color_Off}"
    else
        MINUTOS=$((TIEMPO_TOTAL / 60))
        SEGUNDOS=$((TIEMPO_TOTAL % 60))
        echo -e "  ${Green}⏱${Color_Off} Tiempo total: ${White}${MINUTOS}m ${SEGUNDOS}s${Color_Off}"
    fi

    # Calcular velocidad promedio
    if [ $TIEMPO_TOTAL -gt 0 ]; then
        VELOCIDAD_MB=$(awk -v bytes="$TAMANO_BYTES" -v tiempo="$TIEMPO_TOTAL" 'BEGIN{printf "%.1f", bytes/1024/1024/tiempo}')
        echo -e "  ${Green}⚡${Color_Off} Velocidad promedio: ${White}${VELOCIDAD_MB} MB/s${Color_Off}"
    fi
else
    # Método alternativo sin pv
    echo -ne "  ${Yellow}⏳${Color_Off} Comprimiendo"
    TIEMPO_INICIO=$(date +%s)
    tar -czf "$DIR_BACKUP/$ARCHIVO_BACKUP" stack-facturador-smart/ 2>/dev/null &
    PID=$!
    CONTADOR=0
    while kill -0 $PID 2>/dev/null; do
        echo -n "."
        sleep 1
        ((CONTADOR++))
        # Mostrar tiempo cada 10 segundos
        if [ $((CONTADOR % 10)) -eq 0 ]; then
            echo -ne " ${Gray}[${CONTADOR}s]${Color_Off}"
        fi
    done
    echo ""

    # Calcular tiempo transcurrido
    TIEMPO_FIN=$(date +%s)
    TIEMPO_TOTAL=$((TIEMPO_FIN - TIEMPO_INICIO))

    if [ $TIEMPO_TOTAL -lt 60 ]; then
        echo -e "  ${Green}⏱${Color_Off} Tiempo total: ${White}${TIEMPO_TOTAL} segundos${Color_Off}"
    else
        MINUTOS=$((TIEMPO_TOTAL / 60))
        SEGUNDOS=$((TIEMPO_TOTAL % 60))
        echo -e "  ${Green}⏱${Color_Off} Tiempo total: ${White}${MINUTOS}m ${SEGUNDOS}s${Color_Off}"
    fi
fi

# Verificar si el backup se creó correctamente
if [ -f "$DIR_BACKUP/$ARCHIVO_BACKUP" ]; then
    TAMANO=$(du -h "$DIR_BACKUP/$ARCHIVO_BACKUP" | cut -f1)
    msg "Backup creado exitosamente (Tamaño comprimido: $TAMANO)" "SUCCESS"

    # Mostrar ratio de compresión si tenemos el tamaño original
    if [ -n "$TAMANO_BYTES" ]; then
        TAMANO_FINAL_BYTES=$(stat -c%s "$DIR_BACKUP/$ARCHIVO_BACKUP")
        RATIO=$(awk -v final="$TAMANO_FINAL_BYTES" -v orig="$TAMANO_BYTES" 'BEGIN{printf "%.1f", (1-final/orig)*100}')
        echo -e "  ${Green}📊${Color_Off} Ratio de compresión: ${White}${RATIO}%${Color_Off}"
    fi
else
    msg "Error al crear el backup" "ERROR"
    exit 1
fi
echo ""

# =============================================================================
# 🔄 STEP 4: Rotar backups antiguos
# =============================================================================

echo -e "${BYellow}🔄 PASO 4: ${White}Rotación de backups${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

rotar_backups "$DIR_BACKUP" "$MAX_BACKUPS"

# Mostrar lista de backups actuales
echo -e "  ${Blue}ℹ${Color_Off} Backups actuales (más recientes primero):"
ls -1t "$DIR_BACKUP"/backup-stack-facturador_*.tar.gz 2>/dev/null | head -n "$MAX_BACKUPS" | while read backup; do
    tamano=$(du -h "$backup" | cut -f1)
    fecha_backup=$(basename "$backup" | sed 's/backup-stack-facturador_//;s/.tar.gz//')
    echo -e "    ${Green}•${Color_Off} $(basename "$backup") ${Gray}($tamano)${Color_Off}"
done
echo ""


# =============================================================================
# 🔄 STEP 4.2: Dar los permisos al usuario cesar
# =============================================================================
sudo chown -R $USER_NAME:$USER_NAME "${DIR_BACKUP}"


# =============================================================================
# 🚀 STEP 5: Reiniciar servicios Docker
# =============================================================================

echo -e "${BYellow}🚀 PASO 5: ${White}Reiniciando servicios Docker-Compose${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

for dir in "${DIRS_STACK[@]}"; do
    if [ -d "$dir" ]; then
        nombre_servicio=$(basename "$dir")
        echo -e "  ${Cyan}▶${Color_Off} Iniciando: ${White}$nombre_servicio${Color_Off}"
        cd "$dir"
        docker compose start 2>/dev/null

        if [ $? -eq 0 ]; then
            echo -e "    ${Green}✓${Color_Off} ${Gray}Servicio iniciado correctamente${Color_Off}"
        else
            echo -e "    ${Red}✗${Color_Off} ${Gray}Error al iniciar el servicio${Color_Off}"
        fi
    fi
done

msg "Todos los servicios han sido reiniciados" "SUCCESS"
echo ""

# =============================================================================
# ✅ SECTION: Resumen final
# =============================================================================

echo -e "${BCyan}╔══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BCyan}║                  ${BGreen}✅ BACKUP COMPLETADO${BCyan}                      ║${Color_Off}"
echo -e "${BCyan}╚══════════════════════════════════════════════════════════════╝${Color_Off}"
echo ""
echo -e "  ${Green}📁${Color_Off} Ubicación: ${White}$DIR_BACKUP/${Color_Off}"
echo -e "  ${Green}📦${Color_Off} Archivo:   ${White}$ARCHIVO_BACKUP${Color_Off}"
echo -e "  ${Green}💾${Color_Off} Tamaño:    ${White}$TAMANO${Color_Off}"
if [ -n "$RATIO" ]; then
    echo -e "  ${Green}📊${Color_Off} Compresión: ${White}${RATIO}% reducido${Color_Off}"
fi
echo -e "  ${Green}🔄${Color_Off} Backups mantenidos: ${White}$(ls -1 "$DIR_BACKUP"/backup-stack-facturador_*.tar.gz 2>/dev/null | wc -l)/$MAX_BACKUPS${Color_Off}"
echo ""

# Mostrar lista de backups tar.gz disponibles
echo -e "${BWhite}📚 BACKUPS TAR.GZ DISPONIBLES:${Color_Off}"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"
ls -1t "$DIR_BACKUP"/backup-stack-facturador_*.tar.gz 2>/dev/null | head -n "$MAX_BACKUPS" | while read backup; do
    tamano=$(du -h "$backup" | cut -f1)
    fecha=$(basename "$backup" | sed 's/backup-stack-facturador_\(.*\)\.tar\.gz/\1/')
    echo -e "  ${Cyan}•${Color_Off} $(basename "$backup") ${Gray}→${Color_Off} ${White}$tamano${Color_Off}"
done
echo ""

msg "Proceso completado exitosamente" "SUCCESS"
echo -e "${Gray}────────────────────────────────────────────────────────────────${Color_Off}"

