#!/usr/bin/env bash

# =============================================================================
# 🏆 SECTION: Configuración Inicial
# =============================================================================
# Establece la codificación a UTF-8 para evitar problemas con caracteres especiales.
export LC_ALL="es_ES.UTF-8"

# Fecha y hora actual en formato: YYYY-MM-DD_HH:MM:SS (hora local)
DATE_HOUR=$(date "+%Y-%m-%d_%H:%M:%S")
# Fecha y hora actual en Perú (UTC -5)
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d_%H:%M:%S") # Fecha y hora actuales en formato YYYY-MM-DD_HH:MM:SS.
CURRENT_USER=$(id -un)             # Nombre del usuario actual.
CURRENT_PC_NAME=$(hostname)        # Nombre del equipo actual.
MY_INFO="${CURRENT_USER}@${CURRENT_PC_NAME}"  # Información combinada del usuario y del equipo.
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}")  # Ruta completa del script actual.
SCRIPT_NAME=$(basename "$PATH_SCRIPT")           # Nombre del archivo del script.
CURRENT_DIR=$(dirname "$PATH_SCRIPT")            # Ruta del directorio donde se encuentra el script.
NAME_DIR=$(basename "$CURRENT_DIR")              # Nombre del directorio actual.
TEMP_PATH_SCRIPT=$(echo "$PATH_SCRIPT" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal basado en el nombre del script.
TEMP_PATH_SCRIPT_SYSTEM=$(echo "${TMP}/${SCRIPT_NAME}" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal en /tmp.
ROOT_PATH=$(realpath -m "${CURRENT_DIR}/..")


# =============================================================================
# 🎨 SECTION: Colores para su uso
# =============================================================================
# Definición de colores que se pueden usar en la salida del terminal.

# Colores Regulares
Color_Off='\e[0m'       # Reset de color.
Black='\e[0;30m'        # Negro.
Red='\e[0;31m'          # Rojo.
Green='\e[0;32m'        # Verde.
Yellow='\e[0;33m'       # Amarillo.
Blue='\e[0;34m'         # Azul.
Purple='\e[0;35m'       # Púrpura.
Cyan='\e[0;36m'         # Cian.
White='\e[0;37m'        # Blanco.
Gray='\e[0;90m'         # Gris.

# Colores en Negrita
BBlack='\e[1;30m'       # Negro (negrita).
BRed='\e[1;31m'         # Rojo (negrita).
BGreen='\e[1;32m'       # Verde (negrita).
BYellow='\e[1;33m'      # Amarillo (negrita).
BBlue='\e[1;34m'        # Azul (negrita).
BPurple='\e[1;35m'      # Púrpura (negrita).
BCyan='\e[1;36m'        # Cian (negrita).
BWhite='\e[1;37m'       # Blanco (negrita).
BGray='\e[1;90m'        # Gris (negrita).





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
  local level="${2:-INFO}"  # INFO por defecto si no se especifica
  local timestamp
  timestamp=$(date -u -d "-5 hours" "+%Y-%m-%d %H:%M:%S")

  case "$level" in
    INFO)
      echo -e "${BBlue}${timestamp} ${BWhite}- [INFO]${Color_Off} ${message}"
      ;;
    WARNING)
      echo -e "${BYellow}${timestamp} ${BWhite}- [WARNING]${Color_Off} ${message}"
      ;;
    ERROR)
      echo -e "${BRed}${timestamp} ${BWhite}- [ERROR]${Color_Off} ${message}"
      ;;
    *)
      echo -e "${BGray}${timestamp} ${BWhite}- [${level}]${Color_Off} ${message}"
      ;;
  esac
}

pause_continue() {
  # Descripción:
  #   Muestra un mensaje de pausa. Si se pasa un argumento, lo usa como descripción del evento.
  #   Si no se pasa nada, se muestra un mensaje por defecto.
  #
  # Uso:
  #   pause_continue                         # Usa mensaje por defecto
  #   pause_continue "Se instaló MySQL"      # Muestra "Se instaló MySQL. Presiona..."

  if [ -n "$1" ]; then
    local mensaje="🔹 $1. Presiona [ENTER] para continuar..."
  else
    local mensaje="✅  Comando ejecutado. Presiona [ENTER] para continuar..."
  fi

  echo -e "${Gray}${mensaje}${Color_Off}"
  read -p ""
}

# =============================================================================
# 📦 FUNCION: confirm_continue
# =============================================================================
# 🧾 DESCRIPCIÓN:
#   Pregunta al usuario si desea continuar y retorna 0 (sí) o 1 (no).
#
# 🧠 USO:
#   confirm_continue                # Muestra mensaje por defecto: ¿Deseas continuar? [s/n]
#   confirm_continue "¿Borrar todo?" # Muestra mensaje personalizado
#
# ✅ EJEMPLOS:
#   confirm_continue || continue
#   confirm_continue || exit 1
#   confirm_continue "¿Deseas sobrescribirlo? [s/n]" || exit 0
#   confirm_continue "¿Deseas sobrescribir el archivo?" || return
#   if confirm_continue "¿Deseas actualizar el core de WordPress? [s/n]"; then
#     $WP core update
#     echo "yes-----"
#   fi
# =============================================================================
confirm_continue() {
  local mensaje="${1:-¿Deseas continuar? [s/n]}"
  read -rp "$mensaje " respuesta

  case "$respuesta" in
    [sS])
      msg "${Gray}✅ Continuando...${Color_Off}"
      return 0
      ;;
    [nN])
      msg "${Gray}❌ Operación cancelada por el usuario.${Color_Off}"
      return 1
      ;;
    *)
      msg "${Gray}⚠️ Entrada inválida. Usa 's' o 'n'.${Color_Off}"
      return 1
      ;;
  esac
}

# ----------------------------------------------------------------------
# ❌ check_error
# ----------------------------------------------------------------------
# Descripción:
#   Verifica el código de salida del último comando ejecutado y muestra un
#   mensaje de error personalizado si ocurrió una falla.
#
# Uso:
#   check_error "Mensaje de error personalizado"
# ----------------------------------------------------------------------
check_error() {
  local exit_code=$?  # Captura el código de salida del último comando.
  local error_message=$1  # Mensaje de error personalizado.

  if [ $exit_code -ne 0 ]; then
    msg "${BRed}❌ Error: ${error_message}${Color_Off}"
    exit $exit_code
  fi
}

# ----------------------------------------------------------------------
# confirmar_salida
# ----------------------------------------------------------------------
# Descripción:
#   Función auxiliar para confirmar si el usuario desea continuar con una operación.
#
# Uso:
#   confirmar_salida
# ----------------------------------------------------------------------
confirmar_salida() {
  confirm_continue "¿Deseas continuar con la operación?"
}

# ----------------------------------------------------------------------
# exit_custom
# ----------------------------------------------------------------------
# Descripción:
#   Función para salir del script con un mensaje personalizado.
#
# Uso:
#   exit_custom
# ----------------------------------------------------------------------
exit_custom() {
  msg "No se encontraron elementos para procesar."
  exit 0
}

# =============================================================================
# 🔥 SECTION: Main Code
# =============================================================================

# ::: importar librerias
for f in "${CURRENT_DIR}"/libs_shell/*.sh; do
  [ -e "$f" ] && source "$f"
done

# Tamaño máximo para alertas en rojo
MAX_ALERT_MB=100M
MAX_MB=5M

cd $ROOT_PATH || check_error "No se encontro :${ROOT_PATH}"
# :: nombre del directorio
NAME_DIR=$(basename "$ROOT_PATH")

# :::::::::::::::::::::::::::::: Menú Mejorado ::::::::::::::::::::::::::::::
show_menu() {
  options=(
    "📦 1) Listar ficheros grandes (+${MAX_MB})"
    "🧹 2) Borrar ficheros grandes (+${MAX_MB})"
    "🗂️  3) Eliminar subdirectorios .git"
    "⚖️  4) Ver peso total del proyecto"
    "🔍 5) Buscar ficheros con acentos"
    "📝 6) Renombrar ficheros con acentos"
    "🙈 7) Ver archivos ignorados por .gitignore"
    "❌ 8) Salir"
  )

  while true; do
    clear
    echo -e "${Blue}╔═══════════════════════════════════════════════════════╗${Color_Off}"
    echo -e "${Blue}║        🌐 Utilitarios para Repositorios: ${BWhite}${NAME_DIR}${Blue}        ║${Color_Off}"
    echo -e "${Blue}╠═══════════════════════════════════════════════════════╣${Color_Off}"

    for opt in "${options[@]}"; do
      printf "${Yellow}║${Color_Off}  %s\n" "$opt"
    done

    echo -e "${Blue}╚═══════════════════════════════════════════════════════╝${Color_Off}"
    printf "${Cyan}➡️  Seleccione una opción [1-8 | x para salir]: ${Color_Off}"

    read -r opt
    case $opt in
      1) clear; echo -e "${Green}📦 Listar ficheros grandes${Color_Off}"; listar_ficheros; pause_continue ;;
      2) clear; echo -e "${Green}🧹 Borrar ficheros grandes${Color_Off}"; listar_ficheros_eliminar; pause_continue ;;
      3) clear; echo -e "${Green}🗂️ Eliminar subdirectorios .git${Color_Off}"; search_dirs_git_in_repository; pause_continue ;;
      4) clear; echo -e "${Green}⚖️ Ver peso del proyecto${Color_Off}"; ver_peso_proyecto; pause_continue ;;
      5) clear; echo -e "${Green}🔍 Buscar ficheros con acentos${Color_Off}"; listar_ficheros_acentos; pause_continue ;;
      6) clear; echo -e "${Green}📝 Renombrar ficheros con acentos${Color_Off}"; renombrar_ficheros_acentos; pause_continue ;;
      7) clear; echo -e "${Green}🙈 Ver archivos ignorados por .gitignore${Color_Off}"; ficheros_omitidos_gitignore; pause_continue ;;
      8 | x | X) clear; echo -e "${Red}🚪 Saliendo del programa...${Color_Off}"; exit 0 ;;
      *) echo -e "${Red}❌ Opción inválida. Intente nuevamente.${Color_Off}"; sleep 1 ;;
    esac
  done
}

# :::::::::::::::::::::::::::::: Ejecución ::::::::::::::::::::::::::::::
clear
show_menu