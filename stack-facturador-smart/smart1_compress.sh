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
  if [[ ! -r "$dir" ]]; then
    msg "Error: Sin permisos de lectura en: $dir" "ERROR"
    return 1
  fi
  return 0
}



# ==============================================================================
# 📝 Función: format_dir_size
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Formatea un tamaño en bytes a un formato legible para humanos.
#   Similar a format_file_size() pero diseñada específicamente para trabajar
#   con valores numéricos de bytes directos (no archivos).
#
# 🔧 Parámetros:
#   $1 - Tamaño en bytes (número entero)
#
# 💡 Retorna:
#   Tamaño formateado en stdout con sufijo apropiado:
#   - bytes (para tamaños < 1024)
#   - KB (para tamaños >= 1024 y < 1048576)
#   - MB (para tamaños >= 1048576 y < 1073741824)
#   - GB (para tamaños >= 1073741824)
#
# 🎨 Compatibilidad:
#   - Prefiere usar awk para formato decimal preciso (2 decimales)
#   - Fallback a aritmética entera de bash si awk no está disponible
#
# 🔧 Uso:
#   size_bytes=15728640
#   formatted_size=$(format_dir_size $size_bytes)
#   echo "Tamaño: $formatted_size"  # Output: "Tamaño: 15.00 MB"
#
# 📊 Ejemplos:
#   format_dir_size 512          # Output: "512 bytes"
#   format_dir_size 2048         # Output: "2.00 KB"
#   format_dir_size 3145728      # Output: "3.00 MB"
#   format_dir_size 4294967296   # Output: "4.00 GB"
# ==============================================================================
format_dir_size() {
  local size=$1

  # Verificar si awk está disponible para formato decimal preciso
  if command -v awk >/dev/null 2>&1; then
    # Formato con awk (precisión decimal)
    if [[ $size -ge 1073741824 ]]; then
      # Gigabytes: 1 GB = 1,073,741,824 bytes (2^30)
      echo "$(awk "BEGIN {printf \"%.2f\", $size/1073741824}") GB"
    elif [[ $size -ge 1048576 ]]; then
      # Megabytes: 1 MB = 1,048,576 bytes (2^20)
      echo "$(awk "BEGIN {printf \"%.2f\", $size/1048576}") MB"
    elif [[ $size -ge 1024 ]]; then
      # Kilobytes: 1 KB = 1,024 bytes (2^10)
      echo "$(awk "BEGIN {printf \"%.2f\", $size/1024}") KB"
    else
      # Bytes: tamaño menor a 1 KB
      echo "${size} bytes"
    fi
  else
    # Fallback sin awk (aritmética entera)
    if [[ $size -ge 1073741824 ]]; then
    # Gigabytes con división entera (sin decimales)
      echo "$(( size / 1073741824 )) GB"
    elif [[ $size -ge 1048576 ]]; then
    # Megabytes con división entera (sin decimales)
      echo "$(( size / 1048576 )) MB"
    elif [[ $size -ge 1024 ]]; then
    # Kilobytes con división entera (sin decimales)
      echo "$(( size / 1024 )) KB"
    else
    # Bytes: sin cambios necesarios
      echo "${size} bytes"
    fi
  fi
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
# 📝 Función: get_directory_size_bytes
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Obtiene el tamaño total de un directorio en bytes
#
# 🔧 Parámetros:
#   $1 - Ruta del directorio
#
# 💡 Retorna:
#   Tamaño en bytes (número)
# ==============================================================================
get_directory_size_bytes() {
  local dir="$1"
  if [[ -d "$dir" ]]; then
    if command -v du >/dev/null 2>&1; then
      # Usar du para obtener el tamaño del directorio
      du -sb "$dir" 2>/dev/null | awk '{print $1}' || echo "0"
    else
      # Fallback: sumar todos los archivos (más lento)
      find "$dir" -type f -exec stat -c%s {} + 2>/dev/null | awk '{sum+=$1} END {print sum}' || echo "0"
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
#   $2 - Tamaño actual formateado (opcional)
#   $3 - Tamaño total formateado (opcional)
#
# 💡 Uso:
#   show_progress_bar 50 "10 MB" "20 MB"
# ==============================================================================
show_progress_bar() {
  local percentage=$1
  local current_size="${2:-}"
  local total_size="${3:-}"
  local bar_width=30  # Reducido para que se vea mejor en la terminal
  
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
  
  # Añadir bloques vacíos
  for ((i=0; i<empty; i++)); do
    bar="${bar}${block_empty}"
  done
  
  # Preparar el mensaje con colores y emojis
  local progress_emoji="🚀"
  [[ $percentage -eq 100 ]] && progress_emoji="✨"
  
  # Mostrar la barra con información adicional si está disponible
  if [[ -n "$current_size" ]] && [[ -n "$total_size" ]]; then
    printf "\r${progress_emoji} ${BBlue}│${Color_Off}${BGreen}%s${Color_Off}${BBlue}│${Color_Off} ${BCyan}%3d%%${Color_Off} ${BPurple}%s / %s${Color_Off}" \
      "$bar" "$percentage" "$current_size" "$total_size"
  else
    printf "\r${progress_emoji} ${BBlue}│${Color_Off}${BGreen}%s${Color_Off}${BBlue}│${Color_Off} ${BCyan}%3d%%${Color_Off}" \
      "$bar" "$percentage"
  fi
}

# ==============================================================================
# 📝 Función: monitor_compression_progress
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Monitorea el progreso de la compresión tar comparando el tamaño del
#   archivo de salida con el tamaño estimado del directorio original
#
# 🔧 Parámetros:
#   $1 - PID del proceso tar
#   $2 - Ruta del archivo de salida
#   $3 - Tamaño estimado del directorio original (bytes)
#
# 💡 Nota:
#   Esta función se ejecuta en segundo plano y actualiza la barra de progreso
# ==============================================================================
monitor_compression_progress() {
  local tar_pid=$1
  local output_file="$2"
  local estimated_size=$3
  local last_size=0
  local percentage=0
  local check_interval=0.5  # Verificar cada medio segundo

  # Esperar a que el archivo comience a crearse
  while [[ ! -f "$output_file" ]] && kill -0 "$tar_pid" 2>/dev/null; do
    sleep 0.1
  done

  # Monitorear mientras el proceso esté activo
  while kill -0 "$tar_pid" 2>/dev/null; do
    local current_size
    current_size=$(get_file_size_bytes "$output_file")

    # Solo actualizar si el tamaño cambió
    if [[ $current_size -ne $last_size ]]; then
      # Calcular porcentaje basado en el tamaño comprimido vs tamaño original
      # Nota: El archivo comprimido será más pequeño, así que usamos una estimación
      # Asumimos que la compresión típica es ~50-70% del tamaño original
      # Usamos el tamaño actual del archivo comprimido * 1.5 como estimación del progreso
      if [[ $estimated_size -gt 0 ]]; then
        # Calcular progreso: (tamaño_actual * factor_compresion) / tamaño_original * 100
        # Factor de compresión estimado: 1.6 (asumiendo ~60% de compresión)
        local compressed_estimate=$((current_size * 160 / 100))
        if [[ $compressed_estimate -gt $estimated_size ]]; then
          percentage=100
        else
          percentage=$((compressed_estimate * 100 / estimated_size))
        fi
      else
        # Si no tenemos tamaño estimado, mostrar progreso basado en crecimiento
        percentage=$((current_size * 100 / (estimated_size + 1)))
      fi

      # Mostrar barra de progreso
      local current_size_formatted
      current_size_formatted=$(format_file_size "$output_file")

      # Formatear el tamaño estimado (crear un archivo temporal con el tamaño para formatearlo)
      local estimated_size_formatted=""
      if [[ $estimated_size -gt 0 ]]; then
        # Usar una función auxiliar para formatear bytes directamente
        if command -v awk >/dev/null 2>&1; then
          if [[ $estimated_size -ge 1073741824 ]]; then
            estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1073741824}")" GB"
          elif [[ $estimated_size -ge 1048576 ]]; then
            estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1048576}")" MB"
          elif [[ $estimated_size -ge 1024 ]]; then
            estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1024}")" KB"
          else
            estimated_size_formatted="${estimated_size} bytes"
          fi
        else
          if [[ $estimated_size -ge 1073741824 ]]; then
            estimated_size_formatted="$(( estimated_size / 1073741824 )) GB"
          elif [[ $estimated_size -ge 1048576 ]]; then
            estimated_size_formatted="$(( estimated_size / 1048576 )) MB"
          elif [[ $estimated_size -ge 1024 ]]; then
            estimated_size_formatted="$(( estimated_size / 1024 )) KB"
          else
            estimated_size_formatted="${estimated_size} bytes"
          fi
        fi
      fi

      show_progress_bar "$percentage" "$current_size_formatted" "$estimated_size_formatted"

      last_size=$current_size
    fi

    sleep "$check_interval"
  done

  # Mostrar 100% al finalizar
  local final_size_formatted
  final_size_formatted=$(format_file_size "$output_file")

  # Formatear tamaño estimado para mostrar al final
  local estimated_size_formatted=""
  if [[ $estimated_size -gt 0 ]]; then
    if command -v awk >/dev/null 2>&1; then
      if [[ $estimated_size -ge 1073741824 ]]; then
        estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1073741824}")" GB"
      elif [[ $estimated_size -ge 1048576 ]]; then
        estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1048576}")" MB"
      elif [[ $estimated_size -ge 1024 ]]; then
        estimated_size_formatted=$(awk "BEGIN {printf \"%.2f\", $estimated_size/1024}")" KB"
      else
        estimated_size_formatted="${estimated_size} bytes"
      fi
    else
      if [[ $estimated_size -ge 1073741824 ]]; then
        estimated_size_formatted="$(( estimated_size / 1073741824 )) GB"
      elif [[ $estimated_size -ge 1048576 ]]; then
        estimated_size_formatted="$(( estimated_size / 1048576 )) MB"
      elif [[ $estimated_size -ge 1024 ]]; then
        estimated_size_formatted="$(( estimated_size / 1024 )) KB"
      else
        estimated_size_formatted="${estimated_size} bytes"
      fi
    fi
  fi

  show_progress_bar 100 "$final_size_formatted" "$estimated_size_formatted"
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
  if [[ -n "${DIR_TO_COMPRESS:-}" ]]; then
    msg "Directorio a comprimir: ${DIR_TO_COMPRESS}" "ERROR"
  fi
  if [[ -n "${FILE_PATH_COMPRESS:-}" ]]; then
    msg "Archivo de salida: ${FILE_PATH_COMPRESS}" "ERROR"
  fi

  msg "=================================================" "ERROR"

  # Limpiar archivo parcial si existe
  if [[ -n "${FILE_PATH_COMPRESS:-}" ]] && [[ -f "${FILE_PATH_COMPRESS}" ]]; then
    msg "Limpiando archivo parcial: ${FILE_PATH_COMPRESS}" "WARNING"
    rm -f "${FILE_PATH_COMPRESS}" 2>/dev/null || true
  fi

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

  # Si hay un archivo parcial y el script falló, limpiarlo
  if [[ $exit_code -ne 0 ]] && [[ -n "${FILE_PATH_COMPRESS:-}" ]] && [[ -f "${FILE_PATH_COMPRESS}" ]]; then
    msg "Limpiando archivo parcial debido a error: ${FILE_PATH_COMPRESS}" "WARNING"
    rm -f "${FILE_PATH_COMPRESS}" 2>/dev/null || true
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
DIR_COMPRESS="smart1"
FILE_PATH_COMPRESS="${DIR_ROOT}/smart1.tar.gz"
DIR_TO_COMPRESS="${DIR_ROOT}/${DIR_COMPRESS}"

SALTO_LINE=" \\
"

# Validar que el directorio a comprimir existe
if ! validate_directory "${DIR_TO_COMPRESS}"; then
  exit 1
fi

# =============================================================================
# 📦 PASO 1: Limpiar backup anterior (si existe)
# =============================================================================
if [[ -f "${FILE_PATH_COMPRESS}" ]]; then
  msg "Eliminando backup anterior: ${FILE_PATH_COMPRESS}" "INFO"
  rm -f "${FILE_PATH_COMPRESS}" || true  # || true para evitar que active trap ERR si falla
fi

# =============================================================================
# 📋 PASO 2: Definir qué archivos y carpetas EXCLUIR de la compresión
# =============================================================================
# Estos son directorios que no necesitamos en el backup porque:
# - Son archivos temporales o de caché
# - Se regeneran automáticamente
# - Ocupan mucho espacio innecesariamente
msg "Configurando exclusiones de compresión..." "INFO"
EXCLUDE_PATTERNS=(
  "${DIR_COMPRESS}/.git"                                              # Repositorio Git (pesado y no necesario)
  "${DIR_COMPRESS}/.gitlab"                                           # Configuración de GitLab CI/CD
  "${DIR_COMPRESS}/node_modules"                                      # Dependencias de Node.js (se regeneran con npm install)
  "${DIR_COMPRESS}/vendor"                                            # Dependencias de Composer (se regeneran con composer install)
  "${DIR_COMPRESS}/tmp/*"                                             # Archivos temporales generales
  "${DIR_COMPRESS}/*.log"                                             # Archivos de logs
  "${DIR_COMPRESS}/backup*"                                            # Backups anteriores (evitar duplicados)
)

# =============================================================================
# 🔧 PASO 3: Verificar que la herramienta 'tar' está disponible
# =============================================================================
if ! command -v tar >/dev/null 2>&1; then
  msg "Error: El comando 'tar' no está disponible en el sistema" "ERROR"
  msg "Por favor, instale 'tar' para poder crear el backup" "ERROR"
  exit 1
fi

# =============================================================================
# 🏗️ PASO 4: Construir el comando de compresión paso a paso
# =============================================================================
msg "Preparando comando de compresión..." "INFO"

# Construir el comando tar como una cadena legible
# Empezar con el comando base: tar -czf (crear, comprimir con gzip, especificar archivo)
SHELL_COMMAND="tar"

# Agregar todas las exclusiones al comando
# Cada patrón se agrega como --exclude="patrón"
msg "Agregando ${#EXCLUDE_PATTERNS[@]} patrones de exclusión..." "INFO"

# Verificar si hay patrones de exclusión antes de agregarlos
if [[ ${#EXCLUDE_PATTERNS[@]} -gt 0 ]]; then
  # Inicializar la variable EXCLUDE
  EXCLUDE=""

  # Agregar cada patrón de exclusión al comando
  for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    EXCLUDE="${EXCLUDE} --exclude=\"${pattern}\" ${SALTO_LINE}"
  done

  # Agregar las exclusiones al comando principal
  SHELL_COMMAND="${SHELL_COMMAND} ${EXCLUDE}"
fi

# Agregar las opciones y argumentos finales
# -czf = crear archivo comprimido con gzip
# -C = cambiar al directorio antes de comprimir
SHELL_COMMAND="${SHELL_COMMAND} -czf \"${FILE_PATH_COMPRESS}\" ${SALTO_LINE} -C \"${DIR_ROOT}\" \"${DIR_COMPRESS}\""

# =============================================================================
# 🚀 PASO 5: Calcular tamaño y ejecutar la compresión con progreso
# =============================================================================
msg "Iniciando compresión de: ${DIR_TO_COMPRESS}" "INFO"
msg "Archivo de salida: ${FILE_PATH_COMPRESS}" "INFO"

# Calcular el tamaño del directorio a comprimir (para mostrar progreso)
msg "Calculando tamaño del directorio..." "INFO"



# Mostrar el comando que se ejecutará
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
msg "Comando que se ejecutará:" "INFO"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
echo -e "${BCyan}${SHELL_COMMAND}${Color_Off}"
msg "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" "INFO"
msg "" "INFO"


# Calcular el tamaño del directorio a comprimir para la barra de progreso
msg "Calculando tamaño del directorio para la barra de progreso..." "INFO"
DIR_SIZE_BYTES=$(get_directory_size_bytes "${DIR_TO_COMPRESS}")
DIR_SIZE_FORMATTED=$(format_dir_size ${DIR_SIZE_BYTES})

# Mostrar información del proceso
echo ""
msg "📦 Iniciando compresión con barra de progreso..." "INFO"
echo -e "  ${Cyan}▶${Color_Off} Directorio: ${White}$(basename ${DIR_TO_COMPRESS})${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Tamaño estimado: ${White}${DIR_SIZE_FORMATTED}${Color_Off}"
echo -e "  ${Cyan}▶${Color_Off} Archivo de salida: ${White}$(basename ${FILE_PATH_COMPRESS})${Color_Off}"
echo ""

# Iniciar el proceso de compresión en segundo plano
msg "🚀 Iniciando proceso de compresión..." "INFO"
eval "${SHELL_COMMAND} &"
TAR_PID=$!

# Iniciar monitoreo del progreso en paralelo
monitor_compression_progress ${TAR_PID} "${FILE_PATH_COMPRESS}" ${DIR_SIZE_BYTES} &

# Esperar a que el proceso de compresión termine
wait ${TAR_PID}
COMPRESSION_EXIT_CODE=$?

# Detener el monitoreo de progreso
wait

# Verificar el resultado de la compresión
if [ ${COMPRESSION_EXIT_CODE} -eq 0 ]; then
    msg "✅ Compresión completada exitosamente" "SUCCESS"
else
    msg "❌ Error durante la compresión (código: ${COMPRESSION_EXIT_CODE})" "ERROR"
    exit ${COMPRESSION_EXIT_CODE}
fi

# Si llegamos aquí, la compresión fue exitosa
# Verificar que el archivo se creó correctamente
if [[ -f "${FILE_PATH_COMPRESS}" ]]; then
  FILE_SIZE=$(format_file_size "${FILE_PATH_COMPRESS}")
  msg "Tamaño del archivo comprimido: ${FILE_SIZE}" "INFO"
  msg "Ubicación: ${FILE_PATH_COMPRESS}" "INFO"

  # Calcular y mostrar ratio de compresión
  if [ ${DIR_SIZE_BYTES} -gt 0 ]; then
    COMPRESSED_SIZE_BYTES=$(get_file_size_bytes "${FILE_PATH_COMPRESS}")
    COMPRESSION_RATIO=$(( (DIR_SIZE_BYTES - COMPRESSED_SIZE_BYTES) * 100 / DIR_SIZE_BYTES ))
    msg "📊 Ratio de compresión: ${COMPRESSION_RATIO}%" "SUCCESS"
  fi

  exit 0
else
  msg "Error: La compresión aparentó ser exitosa pero el archivo no se creó" "ERROR"
  exit 1
fi
