#!/usr/bin/env bash

set -euo pipefail  # Detener script al primer error, variables no definidas y errores en pipes

# =============================================================================
# 🏆 SECTION: Configuración Inicial
# =============================================================================

# Ruta completa del script actual
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}" 2>/dev/null || realpath "${BASH_SOURCE:-$0}" 2>/dev/null || echo "$0")
SCRIPT_NAME=$(basename "$PATH_SCRIPT")
CURRENT_DIR=$(dirname "$PATH_SCRIPT")



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

# Ejemplo de uso:
# echo -e "${Red}Este texto se mostrará en rojo.${Color_Off}"

# =============================================================================
# ⚙️ SECTION: Core Function
# =============================================================================




# ==============================================================================
# 📝 Función: msg
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Imprime mensajes con colores según el tipo. Formato limpio sin fecha ni etiquetas.
#
# 🔧 Parámetros:
#   $1 - Mensaje a mostrar (texto)
#   $2 - Tipo de mensaje (INFO | WARNING | ERROR | SUCCESS | DEBUG) [opcional, por defecto: INFO]
#
# 💡 Uso:
#   msg "Proceso completado"                  # Por defecto: INFO (azul)
#   msg "Revisar configuración" "WARNING"     # WARNING (amarillo)
#   msg "Conexión fallida" "ERROR"            # ERROR (rojo)
#   msg "Operación exitosa" "SUCCESS"         # SUCCESS (verde)
#   msg "Modo debug activado" "DEBUG"         # DEBUG (púrpura)
#
# 🎨 Requiere:
#   Variables de color: BBlue, BYellow, BRed, BGreen, BPurple, BGray, Color_Off
# ==============================================================================

msg() {
  local message="$1"
  local level="${2:-OTHER}"

  case "$level" in
    INFO)
      echo -e "${BBlue}${message}${Color_Off}"
      ;;
    WARNING)
      echo -e "${BYellow}${message}${Color_Off}"
      ;;
    DEBUG)
      echo -e "${BPurple}${message}${Color_Off}"
      ;;
    ERROR)
      echo -e "${BRed}${message}${Color_Off}"
      ;;
    SUCCESS)
      echo -e "${BGreen}${message}${Color_Off}"
      ;;
    *)
      echo -e "${BGray}${message}${Color_Off}"
      ;;
  esac
}

# ==============================================================================
# 📝 Función: validate_file
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Valida que un archivo exista y sea accesible
#
# 🔧 Parámetros:
#   $1 - Ruta del archivo a validar
#
# 💡 Retorna:
#   0: Archivo válido
#   1: Archivo inválido o no existe
# ==============================================================================
validate_file() {
  local file="$1"
  if [[ ! -f "$file" ]]; then
    msg "Error: El archivo no existe: $file" "ERROR"
    return 1
  fi
  if [[ ! -r "$file" ]]; then
    msg "Error: Sin permisos de lectura en: $file" "ERROR"
    return 1
  fi
  return 0
}

# ==============================================================================
# 📝 Función: validate_directory
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Valida que un directorio exista y sea accesible
#
# 🔧 Parámetros:
#   $1 - Ruta del directorio a validar
#
# 💡 Retorna:
#   0: Directorio válido
#   1: Directorio inválido o no existe
# ==============================================================================
validate_directory() {
  local dir="$1"
  if [[ ! -d "$dir" ]]; then
    msg "Error: El directorio no existe: $dir" "ERROR"
    return 1
  fi
  if [[ ! -w "$dir" ]]; then
    msg "Error: Sin permisos de escritura en: $dir" "ERROR"
    return 1
  fi
  return 0
}



# ==============================================================================
# 📝 Función: format_file_size
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Formatea el tamaño de un archivo en formato legible (KB, MB, GB)
#
# 🔧 Parámetros:
#   $1 - Ruta del archivo
#
# 💡 Retorna:
#   Tamaño formateado en stdout
# ==============================================================================
format_file_size() {
  local file="$1"
  local size
  if [[ -f "$file" ]]; then
    if command -v stat >/dev/null 2>&1; then
      if [[ "$OSTYPE" == "darwin"* ]]; then
        size=$(stat -f%z "$file" 2>/dev/null || echo "0")
      else
        size=$(stat -c%s "$file" 2>/dev/null || echo "0")
      fi
    else
      size=$(wc -c < "$file" 2>/dev/null || echo "0")
    fi

    # Formatear tamaño usando awk si está disponible, sino usar cálculo básico
    if command -v awk >/dev/null 2>&1; then
      if [[ $size -ge 1073741824 ]]; then
        echo "$(awk "BEGIN {printf \"%.2f\", $size/1073741824}") GB"
      elif [[ $size -ge 1048576 ]]; then
        echo "$(awk "BEGIN {printf \"%.2f\", $size/1048576}") MB"
      elif [[ $size -ge 1024 ]]; then
        echo "$(awk "BEGIN {printf \"%.2f\", $size/1024}") KB"
      else
        echo "${size} bytes"
      fi
    else
      # Fallback sin awk
      if [[ $size -ge 1073741824 ]]; then
        echo "$(( size / 1073741824 )) GB"
      elif [[ $size -ge 1048576 ]]; then
        echo "$(( size / 1048576 )) MB"
      elif [[ $size -ge 1024 ]]; then
        echo "$(( size / 1024 )) KB"
      else
        echo "${size} bytes"
      fi
    fi
  else
    echo "0 bytes"
  fi
}

# ==============================================================================
# 📝 Función: get_file_size_bytes
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Obtiene el tamaño de un archivo en bytes (sin formatear)
#
# 🔧 Parámetros:
#   $1 - Ruta del archivo
#
# 💡 Retorna:
#   Tamaño en bytes (número)
# ==============================================================================
get_file_size_bytes() {
  local file="$1"
  if [[ -f "$file" ]]; then
    if command -v stat >/dev/null 2>&1; then
      if [[ "$OSTYPE" == "darwin"* ]]; then
        stat -f%z "$file" 2>/dev/null || echo "0"
      else
        stat -c%s "$file" 2>/dev/null || echo "0"
      fi
    else
      wc -c < "$file" 2>/dev/null || echo "0"
    fi
  else
    echo "0"
  fi
}

# ==============================================================================
# 📝 Función: show_progress_bar
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Muestra una barra de progreso visual con porcentaje
#
# 🔧 Parámetros:
#   $1 - Porcentaje (0-100)
#   $2 - Información adicional (opcional)
#
# 💡 Uso:
#   show_progress_bar 50 "Extrayendo archivos..."
# ==============================================================================
show_progress_bar() {
  local percentage=$1
  local info="${2:-}"
  local bar_width=40  # Aumentado para que se vea mejor
  
  # Limitar el porcentaje entre 0 y 100
  if [[ $percentage -lt 0 ]]; then
    percentage=0
  elif [[ $percentage -gt 100 ]]; then
    percentage=100
  fi

  # Caracteres para una barra más estética usando bloques Unicode
  local block_full="█"
  local block_partial="▓"
  local block_empty="░"
  
  # Calcular bloques llenos y vacíos
  local filled=$((percentage * bar_width / 100))
  local empty=$((bar_width - filled))
  
  # Construir la barra de progreso con bloques Unicode
  local bar=""
  local i
  
  # Añadir bloques llenos
  for ((i=0; i<filled; i++)); do
    bar="${bar}${block_full}"
  done
  
  # Añadir bloques parciales si es necesario
  if [[ $percentage -gt 0 ]] && [[ $percentage -lt 100 ]]; then
    bar="${bar}${block_partial}"
    empty=$((empty - 1))
  fi
  
  # Añadir bloques vacíos
  for ((i=0; i<empty; i++)); do
    bar="${bar}${block_empty}"
  done
  
  # Preparar el mensaje con colores y emojis
  local progress_emoji="🚀"
  [[ $percentage -eq 100 ]] && progress_emoji="✨"
  
  # Mostrar la barra con información adicional si está disponible
  if [[ -n "$info" ]]; then
    printf "\r${progress_emoji} ${BBlue}│${Color_Off}${BGreen}%s${Color_Off}${BBlue}│${Color_Off} ${BCyan}%3d%%${Color_Off} ${BPurple}%s${Color_Off}" \
      "$bar" "$percentage" "$info"
  else
    printf "\r${progress_emoji} ${BBlue}│${Color_Off}${BGreen}%s${Color_Off}${BBlue}│${Color_Off} ${BCyan}%3d%%${Color_Off}" \
      "$bar" "$percentage"
  fi
}

# ==============================================================================
# 📝 Función: monitor_decompression_progress
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Monitorea el progreso de la descompresión contando archivos extraídos
#
# 🔧 Parámetros:
#   $1 - PID del proceso tar
#   $2 - Directorio de destino
#   $3 - Número total de archivos estimado (opcional)
#
# 💡 Nota:
#   Esta función se ejecuta en segundo plano y actualiza la barra de progreso
# ==============================================================================
monitor_decompression_progress() {
  local tar_pid=$1
  local output_dir="$2"
  local total_files="${3:-0}"
  local last_count=0
  local percentage=0
  local check_interval=0.5  # Verificar cada medio segundo

  # Esperar a que el directorio comience a poblarse
  while [[ ! -d "$output_dir" ]] && kill -0 "$tar_pid" 2>/dev/null; do
    sleep 0.1
  done

  # Monitorear mientras el proceso esté activo
  while kill -0 "$tar_pid" 2>/dev/null; do
    local current_count=0

    # Contar archivos extraídos (solo archivos, no directorios)
    if [[ -d "$output_dir" ]]; then
      current_count=$(find "$output_dir" -type f 2>/dev/null | wc -l)
    fi

    # Solo actualizar si el conteo cambió
    if [[ $current_count -ne $last_count ]]; then
      # Calcular porcentaje si conocemos el total
      if [[ $total_files -gt 0 ]]; then
        percentage=$((current_count * 100 / total_files))
        show_progress_bar "$percentage" "Archivos extraídos: $current_count/$total_files"
      else
        # Mostrar solo el conteo si no conocemos el total
        show_progress_bar 50 "Archivos extraídos: $current_count"
      fi

      last_count=$current_count
    fi

    sleep "$check_interval"
  done

  # Mostrar 100% al finalizar
  if [[ -d "$output_dir" ]]; then
    local final_count=$(find "$output_dir" -type f 2>/dev/null | wc -l)
    show_progress_bar 100 "Archivos extraídos: $final_count"
  fi
  echo ""  # Nueva línea al finalizar
}

# =============================================================================
# 🛡️ SECTION: Manejador Global de Errores
# =============================================================================

# ==============================================================================
# 📝 Función: handle_error
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Captura cualquier error no manejado y muestra información detallada
#
# 🔧 Parámetros:
#   $1 - Código de salida del comando que falló
#   $2 - Número de línea donde ocurrió el error
# ==============================================================================
handle_error() {
  local exit_code=$1
  local line_number=$2

  msg "=================================================" "ERROR"
  msg "💥 ERROR CRÍTICO NO MANEJADO" "ERROR"
  msg "=================================================" "ERROR"
  msg "Código de salida: ${exit_code}" "ERROR"
  msg "Línea del error: ${line_number}" "ERROR"
  msg "Comando: ${BASH_COMMAND:-N/A}" "ERROR"
  msg "Script: ${PATH_SCRIPT}" "ERROR"
  msg "Función: ${FUNCNAME[1]:-main}" "ERROR"
  msg "Usuario: ${USER:-$(id -un 2>/dev/null || echo 'N/A')}" "ERROR"
  msg "Directorio: ${CURRENT_DIR:-N/A}" "ERROR"

  # Información adicional si está disponible
  if [[ -n "${FILE_PATH_DECOMPRESS:-}" ]]; then
    msg "Archivo a descomprimir: ${FILE_PATH_DECOMPRESS}" "ERROR"
  fi
  if [[ -n "${DIR_OUTPUT:-}" ]]; then
    msg "Directorio de salida: ${DIR_OUTPUT}" "ERROR"
  fi

  msg "=================================================" "ERROR"

  exit "${exit_code}"
}

# ==============================================================================
# 📝 Función: cleanup_on_exit
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Limpia archivos temporales al salir del script
# ==============================================================================
cleanup_on_exit() {
  local exit_code=$?

  # Limpiar archivos temporales si existen
  if [[ -n "${TEMP_FILE_OK:-}" ]] && [[ -f "${TEMP_FILE_OK}" ]]; then
    rm -f "${TEMP_FILE_OK}" 2>/dev/null || true
  fi

  if [[ -n "${TEMP_FILE_ERR:-}" ]] && [[ -f "${TEMP_FILE_ERR}" ]]; then
    rm -f "${TEMP_FILE_ERR}" 2>/dev/null || true
  fi
}

# Configurar traps para manejo de errores
# Captura cualquier error no manejado y lo procesa
trap 'handle_error $? $LINENO' ERR

# Capturar EXIT para limpiar archivos temporales
trap 'cleanup_on_exit' EXIT

# =============================================================================
# 🔥 SECTION: Main Code
# =============================================================================

# Cambiar al directorio del script
cd "${CURRENT_DIR}" || {
  msg "Error: No se pudo cambiar al directorio: ${CURRENT_DIR}" "ERROR"
  exit 1
}

# Configuración de directorios y archivos
DIR_ROOT="${CURRENT_DIR}"
FILE_PATH_DECOMPRESS="${DIR_ROOT}/smart1.tar.gz"
DIR_OUTPUT="${DIR_ROOT}"

# Mostrar banner informativo
echo ""
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
msg "🔓 SCRIPT DE DESCOMPRESIÓN - SMART1" "INFO"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
echo ""

# =============================================================================
# 📦 PASO 1: Validar que el archivo existe
# =============================================================================
msg "📋 Validando archivo a descomprimir..." "INFO"
if ! validate_file "${FILE_PATH_DECOMPRESS}"; then
  msg "Archivo esperado: ${FILE_PATH_DECOMPRESS}" "ERROR"
  exit 1
fi

msg "✅ Archivo encontrado: ${FILE_PATH_DECOMPRESS}" "SUCCESS"

# Mostrar información del archivo
FILE_SIZE=$(format_file_size "${FILE_PATH_DECOMPRESS}")
msg "📊 Tamaño del archivo: ${FILE_SIZE}" "INFO"

# =============================================================================
# 📦 PASO 2: Validar que el directorio de salida existe y tiene permisos
# =============================================================================
msg "📂 Validando directorio de salida..." "INFO"
if ! validate_directory "${DIR_OUTPUT}"; then
  msg "Directorio de salida: ${DIR_OUTPUT}" "ERROR"
  exit 1
fi

msg "✅ Directorio de salida válido: ${DIR_OUTPUT}" "SUCCESS"

# =============================================================================
# 🔧 PASO 3: Verificar que la herramienta 'tar' está disponible
# =============================================================================
if ! command -v tar >/dev/null 2>&1; then
  msg "Error: El comando 'tar' no está disponible en el sistema" "ERROR"
  msg "Por favor, instale 'tar' para poder descomprimir el archivo" "ERROR"
  exit 1
fi

# =============================================================================
# 📋 PASO 4: Listar contenido del archivo (opcional - para información)
# =============================================================================
msg "📝 Analizando contenido del archivo comprimido..." "INFO"
# Desactivar temporalmente el manejo de errores de pipe para evitar SIGPIPE
set +o pipefail
TOTAL_FILES=$(tar -tzf "${FILE_PATH_DECOMPRESS}" 2>/dev/null | wc -l)
set -o pipefail
msg "📦 Archivos y directorios en el archivo: ${TOTAL_FILES}" "INFO"

# =============================================================================
# 🚨 PASO 5: Información sobre sobrescritura
# =============================================================================
msg "ℹ️  Si el directorio 'smart1' ya existe, se sobrescribirá" "INFO"

# =============================================================================
# 🏗️ PASO 6: Construir el comando de descompresión
# =============================================================================
msg "Preparando comando de descompresión..." "INFO"

# Construir el comando tar
# -xzf = extraer, descomprimir con gzip, especificar archivo
# -C = cambiar al directorio de destino antes de extraer
# -v = verbose (mostrar archivos extraídos)
SHELL_COMMAND="tar -xzf \"${FILE_PATH_DECOMPRESS}\" -C \"${DIR_OUTPUT}\""

# Mostrar el comando que se ejecutará
echo ""
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
msg "Comando que se ejecutará:" "INFO"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
echo -e "${BCyan}${SHELL_COMMAND}${Color_Off}"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
echo ""

# =============================================================================
# 🚀 PASO 7: Ejecutar la descompresión con progreso
# =============================================================================
# Mostrar información del proceso
msg "📦 Iniciando descompresión con barra de progreso..." "INFO"
echo -e "  ${Cyan}▶${Color_Off} Archivo: ${White}$(basename ${FILE_PATH_DECOMPRESS})${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Tamaño: ${White}${FILE_SIZE}${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Destino: ${White}${DIR_OUTPUT}${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Total de elementos: ${White}${TOTAL_FILES}${Color_Off}"
echo ""

# Iniciar el proceso de descompresión en segundo plano
msg "🚀 Iniciando proceso de descompresión..." "INFO"
eval "${SHELL_COMMAND} &"
TAR_PID=$!

# Iniciar monitoreo del progreso en paralelo
EXTRACT_PATH="${DIR_OUTPUT}/smart1"
monitor_decompression_progress ${TAR_PID} "${EXTRACT_PATH}" ${TOTAL_FILES} &

# Esperar a que el proceso de descompresión termine
wait ${TAR_PID}
DECOMPRESSION_EXIT_CODE=$?

# Detener el monitoreo de progreso
wait

# Verificar el resultado de la descompresión
if [ ${DECOMPRESSION_EXIT_CODE} -eq 0 ]; then
    msg "✅ Descompresión completada exitosamente" "SUCCESS"
else
    msg "❌ Error durante la descompresión (código: ${DECOMPRESSION_EXIT_CODE})" "ERROR"
    exit ${DECOMPRESSION_EXIT_CODE}
fi

# =============================================================================
# ✅ PASO 8: Verificación final
# =============================================================================
echo ""
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "SUCCESS"
msg "📊 RESUMEN DE LA OPERACIÓN" "SUCCESS"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "SUCCESS"

# Verificar que el directorio se extrajo correctamente
if [[ -d "${EXTRACT_PATH}" ]]; then
  msg "✅ Directorio extraído: ${EXTRACT_PATH}" "SUCCESS"

  # Contar archivos extraídos
  EXTRACTED_FILES=$(find "${EXTRACT_PATH}" -type f 2>/dev/null | wc -l)
  EXTRACTED_DIRS=$(find "${EXTRACT_PATH}" -type d 2>/dev/null | wc -l)

  msg "📁 Directorios: ${EXTRACTED_DIRS}" "INFO"
  msg "📄 Archivos: ${EXTRACTED_FILES}" "INFO"
  msg "📍 Ubicación: ${EXTRACT_PATH}" "INFO"

  echo ""
  msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "SUCCESS"
  msg "🎉 DESCOMPRESIÓN FINALIZADA CON ÉXITO" "SUCCESS"
  msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "SUCCESS"
  echo ""

  exit 0
else
  msg "Error: La descompresión aparentó ser exitosa pero el directorio no se creó" "ERROR"
  exit 1
fi
