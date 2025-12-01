#!/usr/bin/env bash

# =============================================================================
# 🏆 SECTION: Configuración Inicial
# =============================================================================
# Establece la codificación a UTF-8 para evitar problemas con caracteres especiales.
export LC_ALL="es_ES.UTF-8"

# Variables de configuración iniciales
DATE_HOUR="$(date +%Y)-$(date +%m)-$(date +%d)_$(date +%H):$(date +%M):$(date +%S)"  # Fecha y hora actuales en formato YYYY-MM-DD_HH:MM:SS.
CURRENT_USER=$(id -un)             # Nombre del usuario actual.
CURRENT_PC_NAME=$(hostname)        # Nombre del equipo actual.
MY_INFO="${CURRENT_USER}@${CURRENT_PC_NAME}"  # Información combinada del usuario y del equipo.
PATH_SCRIPT=$(readlink -f "${BASH_SOURCE:-$0}")  # Ruta completa del script actual.
SCRIPT_NAME=$(basename "$PATH_SCRIPT")           # Nombre del archivo del script.
CURRENT_DIR=$(dirname "$PATH_SCRIPT")            # Ruta del directorio donde se encuentra el script.
NAME_DIR=$(basename "$CURRENT_DIR")              # Nombre del directorio actual.
TEMP_PATH_SCRIPT=$(echo "$PATH_SCRIPT" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal basado en el nombre del script.
TEMP_PATH_SCRIPT_SYSTEM=$(echo "${TMP}/${SCRIPT_NAME}" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal en /tmp.

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

# Ejemplo de uso:
# echo -e "${Red}Este texto se mostrará en rojo.${Color_Off}"

# =============================================================================
# ⚙️ SECTION: Core Function
# =============================================================================

# ----------------------------------------------------------------------
# 🗂️ get_rootPath
# ----------------------------------------------------------------------
# Descripción:
#   Obtiene la ruta raíz del proyecto eliminando el nombre del directorio
#   actual de la ruta completa del script.
#
# Uso:
#   root_path=$(get_rootPath)
#
# Ejemplo:
#   Si la ruta completa del script es:
#     /home/usuario/proyectos/mi_proyecto/scripts/mis_script.sh
#   y el directorio actual es:
#     /home/usuario/proyectos/mi_proyecto/scripts
#   entonces:
#     root_path=$(get_rootPath)
#   resultará en:
#     /home/usuario/proyectos/mi_proyecto
#
# Retorna:
#   La ruta raíz del proyecto como una cadena de texto.
# ----------------------------------------------------------------------
get_rootPath() {
  local regex="s/\/${NAME_DIR}//"  # Expresión regular para eliminar el nombre del directorio actual.
  local root_path=$(echo "$CURRENT_DIR" | sed -e "$regex")  # Aplica la expresión regular a la ruta actual.
  echo "$root_path"  # Imprime la ruta raíz.
}

# ----------------------------------------------------------------------
# 📋 view_vars_config
# ----------------------------------------------------------------------
# Descripción:
#   Muestra todas las variables de configuración actuales en formato legible.
#
# Uso:
#   view_vars_config
# ----------------------------------------------------------------------
view_vars_config() {
  echo -e "${Gray}"
  echo -e "╔═══════════════════════════════════════════════════════════"
  echo -e "║             🛠️  CONFIGURACIÓN ACTUAL 🛠️"
  echo -e "║"
  echo -e "║ 📅 DATE_HOUR:                ${DATE_HOUR}"
  echo -e "║ 👤 CURRENT_USER:             ${CURRENT_USER}"
  echo -e "║ 🖥️ CURRENT_PC_NAME:         ${CURRENT_PC_NAME}"
  echo -e "║ ℹ️ MY_INFO:                  ${MY_INFO}"
  echo -e "║ 📄 PATH_SCRIPT:              ${PATH_SCRIPT}"
  echo -e "║ 📜 SCRIPT_NAME:              ${SCRIPT_NAME}"
  echo -e "║ 📁 CURRENT_DIR:              ${CURRENT_DIR}"
  echo -e "║ 🗂️ NAME_DIR:                 ${NAME_DIR}"
  echo -e "║ 🗃️ TEMP_PATH_SCRIPT:        ${TEMP_PATH_SCRIPT}"
  echo -e "║ 📂 TEMP_PATH_SCRIPT_SYSTEM:  ${TEMP_PATH_SCRIPT_SYSTEM}"

  # Si ROOT_PATH está definido, lo muestra.
  local ROOT_PATH=$(get_rootPath)
  if [ -n "$ROOT_PATH" ]; then
    echo -e "║ 🏡 ROOT_PATH:                ${ROOT_PATH}"
  fi

  echo -e "╚═══════════════════════════════════════════════════════════"
  echo -e "${Color_Off}"
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
    echo -e "${BRed}❌ Error: ${error_message}${Color_Off}"
    exit $exit_code
  fi
}

# ----------------------------------------------------------------------
# ✅ message_ok
# ----------------------------------------------------------------------
# Descripción:
#   Muestra un mensaje de éxito si el último comando se ejecutó correctamente.
#
# Uso:
#   message_ok "Operación completada con éxito"
# ----------------------------------------------------------------------
message_ok() {
  local exit_code=$?  # Captura el código de salida del último comando.
  local success_message=$1  # Mensaje de éxito personalizado.

  if [ $exit_code -eq 0 ]; then
    echo -e "${BGreen}✅ ${success_message}${Color_Off}"
  fi
}

# ----------------------------------------------------------------------
# 🚪 exit_custom
# ----------------------------------------------------------------------
# Descripción:
#   Finaliza la ejecución del script mostrando un mensaje personalizado y
#   esperando que el usuario presione Enter.
#
# Uso:
#   exit_custom "Mensaje de salida personalizado"
#   # Si no se proporciona un mensaje, se utilizará uno por defecto.
# ----------------------------------------------------------------------
# shellcheck disable=SC2120
exit_custom() {
  local msg=${1:-"Presiona Enter para salir..."}  # Mensaje de salida por defecto si no se proporciona uno.

  # Muestra el mensaje y espera la entrada del usuario.
  read -p "$(echo -e "${Gray}${msg}${Color_Off}")"
  exit 0
}

# =============================================================================
# 🔧 SECTION: Functions Utils
# =============================================================================

calculate_peso_project() {
  local dir=${1:-.}
  local pesoTotal
  pesoTotal=$(du -sh --exclude=.git --exclude='./node_modules' "$dir" | cut -f1)
  echo $pesoTotal
}

convert_bytes_to_human() {
  local bytes=$1
  if [ $bytes -lt 1024 ]; then
    echo "${bytes} B"
  elif [ $bytes -lt 1048576 ]; then
    echo "$(echo "$bytes" | awk '{ printf "%.2f", $1 / 1024 }') KB"
  elif [ $bytes -lt 1073741824 ]; then
    echo "$(echo "$bytes" | awk '{ printf "%.2f", $1 / (1024 * 1024) }') MB"
  elif [ $bytes -lt 1099511627776 ]; then
    echo "$(echo "$bytes" | awk '{ printf "%.2f", $1 / (1024 * 1024 * 1024) }') GB"
  else
    echo "$(echo "$bytes" | awk '{ printf "%.2f", $1 / (1024 * 1024 * 1024 * 1024) }') TB"
  fi
}

path_file_weight_human() {
  local file=$1
  local unidades=$(stat -c %s "${file}")
  convert_bytes_to_human $unidades
}



#listar_ficheros() {
#    IFS=$'\n'
#    echo ""
#    echo -en "${Blue}Buscando Ficheros con peso mayor a: ${MAX_MB}  ${Color_Off}\n"
#    echo -en "${Gray}Directorios excluidos:(node_modules, vendor, .git, .angular) ${Color_Off}\n"
#    echo ""
#    files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" \) -prune -o -type f -size +$MAX_MB -print 2>/dev/null)
#    local files_count=0
#
#    for file in $files; do
#        echo "----------------------------------------------------"
#        WEIGHT_FILE=$(path_file_weight_human "${file}")
#        echo -en "${Cyan}file: ${file} - ${WEIGHT_FILE}\n"
#        files_count=$((files_count + 1))
#    done
#    echo ""
#    echo -en "${Blue}Total Encontrados: ${files_count} ${Color_Off}\n"
#
#    echo "................"
#    weight_files=$(du -ch $(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" \) -prune -o -type f -print 2>/dev/null) | grep total$ | cut -f1)
#    echo -en "${Blue}Peso de la carpeta como repositorio: ${weight_files} ${Color_Off}\n"
#    echo -en "${Gray}Directorios excluidos:(node_modules, vendor, .git) ${Color_Off}\n"
#}
# =============================================================================
# 📂 SECTION: Listar Ficheros con Peso Mayor al Límite
# =============================================================================
listar_ficheros() {
    IFS=$'\n'
    echo -e "\n${Blue}Buscando ficheros con peso mayor a: ${MAX_MB}${Color_Off}"
    echo -e "${Gray}Excluyendo directorios: (node_modules, vendor, .git, .angular)${Color_Off}\n"

    files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" \) -prune -o -type f -size +"$MAX_MB" -print 2>/dev/null)
    local files_count=0

    for file in $files; do
        WEIGHT_FILE=$(path_file_weight_human "$file")
        WEIGHT_BYTES=$(stat -c %s "$file")
        WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
        WEIGHT_INT=${WEIGHT_MB%.*}  # Elimina la parte decimal, a entero antes de la comparación

        echo "----------------------------------------------------"

        # Resaltar en rojo si el archivo es mayor a 100MB
        if (( WEIGHT_INT > 100 )); then
            echo -e "${BRed}⚠️  Archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
        else
            echo -e "${Cyan}file: ${file} - ${WEIGHT_FILE}${Color_Off}"
        fi

        files_count=$((files_count + 1))
    done

    echo -e "\n${Blue}Total encontrados: ${files_count}${Color_Off}\n"
}



confirmar_salida() {
  read -p "¿Estás seguro de que quieres continuar? (s/n): " confirm
  confirm=${confirm,,}
  if [[ "$confirm" == "s" || "$confirm" == "si" ]]; then
      echo "Continuando con la operación..."
  else
      exit 0
  fi
}

#listar_ficheros_eliminar() {
#    confirmar_salida
#    IFS=$'\n'
#    echo ""
#    echo -en "${Blue}Buscando Ficheros para eliminar con peso mayor a: ${MAX_MB}  ${Color_Off}\n"
#    echo ""
#    files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" \) -prune -o -type f -size +$MAX_MB -print 2>/dev/null)
#    local files_count=0
#
#    for file in $files; do
#        WEIGHT_FILE=$(path_file_weight_human "${file}")
#        echo "----------------------------------------------------"
#        echo -en "${Cyan}Fichero: ${file} - ${WEIGHT_FILE}\n"
#        echo -en "${Yellow}Eliminando: ${file}  ${Color_Off}\n"
#        sleep 2
#        rm "${file}"
#        files_count=$((files_count + 1))
#    done
#    echo ""
#    echo -en "${Blue}Total Encontrados: ${files_count} ${Color_Off}\n"
#}

# =============================================================================
# 🗑️ SECTION: Listar y Eliminar Ficheros con Peso Mayor al Límite
# =============================================================================
listar_ficheros_eliminar() {
    echo -e "\n${Blue}Buscando y eliminando ficheros con peso mayor a: ${MAX_MB}${Color_Off}"
    echo -e "${Gray}Excluyendo directorios: (node_modules, vendor, .git, .angular)${Color_Off}\n"

    files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" \) -prune -o -type f -size +"$MAX_MB" -print 2>/dev/null)
    local files_count=0

    for file in $files; do
        WEIGHT_FILE=$(path_file_weight_human "$file")
        WEIGHT_BYTES=$(stat -c %s "$file")
        WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
        WEIGHT_INT=${WEIGHT_MB%.*}  # Elimina la parte decimal, a entero antes de la comparación

        echo "----------------------------------------------------"

        # Resaltar en rojo si el archivo es mayor a 100MB
        if (( WEIGHT_INT > 100 )); then
            echo -e "${BRed}⚠️  Eliminando archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
        else
            echo -e "${Cyan}Eliminando: ${file} - ${WEIGHT_FILE}${Color_Off}"
        fi

        rm "$file"
        files_count=$((files_count + 1))
    done

    echo -e "\n${Blue}Total eliminados: ${files_count}${Color_Off}\n"
}


search_dirs_git_in_repository() {
    # =====================================================================
    # 1. Proceso para listar ficheros
    # =====================================================================
    IFS=$'\n'
    echo ""
    echo -en "${Blue}Buscando directoriso git en repositorio ${Color_Off}\n"
    echo ""
    files=$(find .  -mindepth 2 -type d -name ".git" -print 2>/dev/null)
    local files_count=0

    for file in $files; do
        echo "----------------------------------------------------"
        echo -en "${Cyan}Fichero: ${file} \n"
        sleep 1
        files_count=$((files_count + 1))
    done
    echo ""
    echo -en "${Blue}Total Encontrados: ${files_count} ${Color_Off}\n"

    # Validar si la variable no es mayor que 0
    if [ ! "$files_count" -gt 0 ]; then
        sleep 3 && exit_custom
    fi



    # =====================================================================
    # 2. Proceso para eliminar ficheros
    # =====================================================================
    echo ""
    echo ""
    echo -en "${Blue}Deseas eliminar ficheros... ${Color_Off}\n"
    confirmar_salida
    files_count=0
    for file in $files; do
           echo "----------------------------------------------------"
           echo -en "${Cyan}Fichero: ${file}  ${Color_Off} \n"
           echo -en "${Yellow}Eliminando: ${file}  ${Color_Off}\n"
           sleep 1
           rm -rf "${file}"
           files_count=$((files_count + 1))
    done
    echo ""
    echo -en "${Blue}Total Encontrados: ${files_count} ${Color_Off}\n"
}


# =============================================================================
# 🔥 SECTION: Main Code
# =============================================================================

ROOT_PATH=$(get_rootPath)
cd "$ROOT_PATH" || exit 1

# Tamaño máximo para alertas en rojo
MAX_ALERT_MB=100M
MAX_MB=5M


echo -en "${Green}####################################################### \n"
echo -en "Github Admite como maximos subida de archivos 100M \n"
echo -en "Power by: Cesar Auris \n"
echo -en "${Green}####################################################### \n"

mostrar_menu() {
    echo -en "${Green} \n"
    echo "======= Menú: =========="
    echo "1. Listar ficheros con peso mayor a: ${MAX_MB}."
    echo "2. Listar y borrar ficheros con peso mayor a: ${MAX_MB}."
    echo "3. Buscar directorios [.git] en subdirectorios."
    echo "4. Salir"
    echo "Ingrese su opción:"
}

while true; do
    mostrar_menu
    read -r opcion
    case $opcion in
        1)
            echo "Listar ficheros con peso mayor a: ${MAX_MB}." && clear && sleep 2
            listar_ficheros
            ;;
        2)
            echo "Listar y borrar ficheros con peso mayor a: ${MAX_MB}." && clear && sleep 2
            listar_ficheros_eliminar
            ;;
        3)
            echo "Listar y eliminar subdirecotiors [.git]"
            search_dirs_git_in_repository
            ;;
        4)
            echo "Salir del programa."
            break
            ;;

        *)
            echo "Opción no válida. Por favor, seleccione una opción válida."
            ;;
    esac
done
echo -en "${Color_Off}\n"
