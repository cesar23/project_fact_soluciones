#!/usr/bin/env bash


# ----------------------------------------------------------------------
# convert_bytes_to_human
# ----------------------------------------------------------------------
# Descripción:
#   Convierte un valor en bytes a una representación humana (KB, MB, GB, etc.).
#
# Uso:
#   peso_humano=$(convert_bytes_to_human "2048576")
# ----------------------------------------------------------------------
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

# ----------------------------------------------------------------------
# path_file_weight_human
# ----------------------------------------------------------------------
# Descripción:
#   Obtiene el tamaño de un archivo en una representación humana.
#
# Uso:
#   peso_archivo=$(path_file_weight_human "/ruta/al/archivo")
# ----------------------------------------------------------------------
path_file_weight_human() {
  local file=$1
  local unidades=$(stat -c %s "${file}")
  convert_bytes_to_human $unidades
}



# ----------------------------------------------------------------------
# calculate_peso_project
# ----------------------------------------------------------------------
# Descripción:
#   Calcula el peso total del proyecto, respetando las reglas del .gitignore.
#
# Uso:
#   peso=$(calculate_peso_project "/ruta/al/directorio")
# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# mostrar_desglose_peso_git
# ----------------------------------------------------------------------
# Descripción:
#   Muestra un desglose detallado de los archivos considerados para el cálculo de peso,
#   separando archivos trackeados y no trackeados (pero no ignorados).
#
# Variables:
#   DESGLOSE_MAX_ARCHIVOS - Número máximo de archivos a mostrar por categoría (por defecto: 50)
#
# Uso:
#   mostrar_desglose_peso_git
#   DESGLOSE_MAX_ARCHIVOS=20 mostrar_desglose_peso_git  # Mostrar solo 20 archivos
# ----------------------------------------------------------------------
mostrar_desglose_peso_git() {
  # Variable configurable para el número máximo de archivos a mostrar
  local max_archivos=${DESGLOSE_MAX_ARCHIVOS:-50}
  
  if ! git rev-parse --is-inside-work-tree &>/dev/null; then
    msg "❌ Este directorio no es un repositorio Git."
    return 1
  fi

  echo -e "\n${Blue}=== DESGLOSE DETALLADO DEL PESO ===${Color_Off}"
  echo -e "${Gray}Mostrando hasta ${max_archivos} archivos por categoría${Color_Off}"
  
  # Ver lista completa con tamaños de archivos trackeados
  echo -e "\n${Green}📁 ARCHIVOS RASTREADOS:${Color_Off}"
  git ls-files | xargs ls -lh 2>/dev/null | head -${max_archivos}
  if [ $(git ls-files | wc -l) -gt ${max_archivos} ]; then
    echo -e "${Gray}... y $(( $(git ls-files | wc -l) - max_archivos )) archivos más${Color_Off}"
  fi
  
  # Ver archivos no trackeados pero no ignorados
  echo -e "\n${Yellow}📄 ARCHIVOS NO RASTREADOS (no ignorados):${Color_Off}"
  git ls-files --others --exclude-standard | xargs ls -lh 2>/dev/null | head -${max_archivos}
  if [ $(git ls-files --others --exclude-standard | wc -l) -gt ${max_archivos} ]; then
    echo -e "${Gray}... y $(( $(git ls-files --others --exclude-standard | wc -l) - max_archivos )) archivos más${Color_Off}"
  fi
  
  # Calcular tamaño total
  echo -e "\n${Cyan}⚖️ TAMAÑO TOTAL:${Color_Off}"
  peso_total=$((git ls-files && git ls-files --others --exclude-standard) | xargs du -ch 2>/dev/null | tail -1)
  echo -e "${BWhite}${peso_total}${Color_Off}"
  
  echo -e "\n${Gray}Nota: Solo se muestran los primeros ${max_archivos} archivos de cada categoría${Color_Off}"
  echo -e "${Gray}Para cambiar este número, usa: DESGLOSE_MAX_ARCHIVOS=N mostrar_desglose_peso_git${Color_Off}"
}

calculate_peso_project() {
  local dir=${1:-.}
  local pesoTotal
  
  # Si estamos en un repositorio git, usar git para calcular el peso respetando .gitignore
  if git rev-parse --is-inside-work-tree &>/dev/null; then
    # Combinar archivos trackeados y no trackeados (pero no ignorados)
    # Esto respeta completamente el .gitignore
    pesoTotal=$((git ls-files && git ls-files --others --exclude-standard) | xargs du -ch 2>/dev/null | tail -1 | cut -f1)
    
    # Si no hay archivos, mostrar 0
    if [ -z "$pesoTotal" ] || [ "$pesoTotal" = "0" ]; then
      pesoTotal="0B"
    fi
  else
    # Si no estamos en git, excluir directorios comunes manualmente
    pesoTotal=$(du -sh --exclude=.git --exclude='./node_modules' --exclude='./target' --exclude='./data' --exclude='./ssh_data/logs' --exclude='./__pycache__' --exclude='./build' --exclude='./dist' --exclude='./.vscode' --exclude='./.idea' "$dir" 2>/dev/null | cut -f1)
  fi
  
  echo $pesoTotal
}

# ----------------------------------------------------------------------
# convert_bytes_to_human
# ----------------------------------------------------------------------
# Descripción:
#   Convierte un valor en bytes a una representación humana (KB, MB, GB, etc.).
#
# Uso:
#   peso_humano=$(convert_bytes_to_human "2048576")
# ----------------------------------------------------------------------
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

# ----------------------------------------------------------------------
# path_file_weight_human
# ----------------------------------------------------------------------
# Descripción:
#   Obtiene el tamaño de un archivo en una representación humana.
#
# Uso:
#   peso_archivo=$(path_file_weight_human "/ruta/al/archivo")
# ----------------------------------------------------------------------
path_file_weight_human() {
  local file=$1
  local unidades=$(stat -c %s "${file}")
  convert_bytes_to_human $unidades
}


# ----------------------------------------------------------------------
# listar_ficheros
# ----------------------------------------------------------------------
# Descripción:
#   Busca los ficheros con un peso mayor a ${MAX_MB} en el directorio actual y
#   sus subdirectorios, respetando las reglas del .gitignore.
#
#   Muestra el peso de cada archivo en una representación humana (KB, MB, GB, etc.).
#   El peso se muestra en azul y se resaltan en rojo los archivos que pesan más de 100MB.
#
# Uso:
#   listar_ficheros
listar_ficheros() {
    IFS=$'\n'
    echo -e "\n${Blue}Buscando ficheros con peso mayor a: ${MAX_MB}${Color_Off}"
    echo -e "${Gray}Respetando reglas de .gitignore${Color_Off}\n"

    local files_count=0
    
    # Si estamos en un repositorio git, usar git para obtener archivos no ignorados
    if git rev-parse --is-inside-work-tree &>/dev/null; then
        echo -e "${Green}📁 Repositorio Git detectado - Respetando .gitignore${Color_Off}"
        
        # Buscar archivos grandes usando find
        files=$(find . -type f -size +"$MAX_MB" 2>/dev/null)
        
        # Filtrar archivos que NO están siendo ignorados por git
        for file in $files; do
            # Verificar si el archivo está siendo ignorado por git
            if ! git check-ignore "$file" &>/dev/null; then
                # El archivo NO está siendo ignorado, procesarlo
                if [ -f "$file" ]; then
                    WEIGHT_FILE=$(path_file_weight_human "$file")
                    WEIGHT_BYTES=$(stat -c %s "$file")
                    WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
                    WEIGHT_INT=${WEIGHT_MB%.*}

                    echo "----------------------------------------------------"

                    if (( WEIGHT_INT > 100 )); then
                        echo -e "${BRed}⚠️  Archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
                    else
                        echo -e "${Cyan}file: ${file} - ${WEIGHT_FILE}${Color_Off}"
                    fi

                    files_count=$((files_count + 1))
                fi
            else
                # El archivo está siendo ignorado, mostrarlo en debug
                echo -e "${Gray}🔇 Ignorado por .gitignore: ${file}${Color_Off}"
            fi
        done
    else
        echo -e "${Yellow}📁 Directorio normal - Excluyendo directorios comunes${Color_Off}"
        
        # Si no estamos en git, usar find normal pero excluir directorios comunes
        files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" -o -path "*/data" -o -path "*/ssh_data/logs" -o -path "*/__pycache__" -o -path "*/build" -o -path "*/dist" -o -path "*/.vscode" -o -path "*/.idea" \) -prune -o -type f -size +"$MAX_MB" -print 2>/dev/null)
        
        for file in $files; do
            if [ -f "$file" ]; then
                WEIGHT_FILE=$(path_file_weight_human "$file")
                WEIGHT_BYTES=$(stat -c %s "$file")
                WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
                WEIGHT_INT=${WEIGHT_MB%.*}

                echo "----------------------------------------------------"

                if (( WEIGHT_INT > 100 )); then
                    echo -e "${BRed}⚠️  Archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
                else
                    echo -e "${Cyan}file: ${file} - ${WEIGHT_FILE}${Color_Off}"
                fi

                files_count=$((files_count + 1))
            fi
        done
    fi

    echo -e "\n${Blue}Total encontrados: ${files_count}${Color_Off}\n"
}

# ----------------------------------------------------------------------
# listar_ficheros_eliminar
# ----------------------------------------------------------------------
# Descripción:
#   Busca los ficheros con un peso mayor a ${MAX_MB} en el directorio actual y
#   sus subdirectorios, respetando las reglas del .gitignore.
#
#   Muestra el peso de cada archivo en una representación humana (KB, MB, GB, etc.).
#   El peso se muestra en azul y se resaltan en rojo los archivos que pesan más de 100MB.
#
#   Elimina los archivos encontrados.
#
# Uso:
#   listar_ficheros_eliminar
listar_ficheros_eliminar() {
    echo -e "\n${Blue}Buscando y eliminando ficheros con peso mayor a: ${MAX_MB}${Color_Off}"
    echo -e "${Gray}Respetando reglas de .gitignore${Color_Off}\n"

    local files_count=0
    
    # Si estamos en un repositorio git, usar git para obtener archivos no ignorados
    if git rev-parse --is-inside-work-tree &>/dev/null; then
        echo -e "${Green}📁 Repositorio Git detectado - Respetando .gitignore${Color_Off}"
        
        # Buscar archivos grandes usando find
        files=$(find . -type f -size +"$MAX_MB" 2>/dev/null)
        
        # Filtrar archivos que NO están siendo ignorados por git
        for file in $files; do
            # Verificar si el archivo está siendo ignorado por git
            if ! git check-ignore "$file" &>/dev/null; then
                # El archivo NO está siendo ignorado, procesarlo
                if [ -f "$file" ]; then
                    WEIGHT_FILE=$(path_file_weight_human "$file")
                    WEIGHT_BYTES=$(stat -c %s "$file")
                    WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
                    WEIGHT_INT=${WEIGHT_MB%.*}

                    echo "----------------------------------------------------"

                    if (( WEIGHT_INT > 100 )); then
                        echo -e "${BRed}⚠️  Eliminando archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
                    else
                        echo -e "${Cyan}Eliminando: ${file} - ${WEIGHT_FILE}${Color_Off}"
                    fi

                    rm "$file"
                    files_count=$((files_count + 1))
                fi
            else
                # El archivo está siendo ignorado, mostrarlo en debug
                echo -e "${Gray}🔇 Ignorado por .gitignore: ${file}${Color_Off}"
            fi
        done
    else
        echo -e "${Yellow}📁 Directorio normal - Excluyendo directorios comunes${Color_Off}"
        
        # Si no estamos en git, usar find normal pero excluir directorios comunes
        files=$(find . -type d \( -path "*/.git" -o -path "*/.angular" -o -path "*/node_modules" -o -path "*/vendor" -o -path "*/data" -o -path "*/ssh_data/logs" -o -path "*/__pycache__" -o -path "*/build" -o -path "*/dist" -o -path "*/.vscode" -o -path "*/.idea" \) -prune -o -type f -size +"$MAX_MB" -print 2>/dev/null)
        
        for file in $files; do
            if [ -f "$file" ]; then
                WEIGHT_FILE=$(path_file_weight_human "$file")
                WEIGHT_BYTES=$(stat -c %s "$file")
                WEIGHT_MB=$(echo "$WEIGHT_BYTES" | awk '{ printf "%.2f", $1 / (1024 * 1024) }')
                WEIGHT_INT=${WEIGHT_MB%.*}

                echo "----------------------------------------------------"

                if (( WEIGHT_INT > 100 )); then
                    echo -e "${BRed}⚠️  Eliminando archivo grande: ${file} - ${WEIGHT_FILE}${Color_Off}"
                else
                    echo -e "${Cyan}Eliminando: ${file} - ${WEIGHT_FILE}${Color_Off}"
                fi

                rm "$file"
                files_count=$((files_count + 1))
            fi
        done
    fi

    echo -e "\n${Blue}Total eliminados: ${files_count}${Color_Off}\n"
}

search_dirs_git_in_repository() {

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

# ::::::::: Peso del proyecto

calculate_peso_git() {
  local dir=${1:-.}

  # Calcular el peso total del directorio .git.
  local pesoTotal
  pesoTotal=$(du -sh "$dir" | cut -f1)

  echo $pesoTotal
}


ver_peso_proyecto() {
  # Muestra información sobre el proyecto.
  msg "${Green}####################################################### "
  msg "Peso del proyecto : ${NAME_DIR} "
  msg "ROOT_PATH : ${ROOT_PATH} "
  msg "Power by: Cesar Auris "
  msg "${Green}####################################################### "
  echo ""
  
  # Verificar si estamos en un repositorio git
  if git rev-parse --is-inside-work-tree &>/dev/null; then
    msg "📁 Repositorio Git detectado - Respetando reglas de .gitignore"
  else
    msg "📁 Directorio normal - Excluyendo directorios comunes"
  fi
  
  # Calcula y muestra el peso del directorio .git.
  if [ -d ".git" ]; then
    msg "Calculando peso del directorio .git... "
    PESO=$(calculate_peso_git ".git")
    msg "${Green}Solo el peso del directorio: .git - ${PESO} "
    echo ""
  fi
  
  echo ""
  # Calcula y muestra el peso total del proyecto.
  PESO=$(calculate_peso_project "${ROOT_PATH}")
  msg "${Blue}Calculando peso del proyecto... "
  msg "${Blue}Peso completo del directorio - ${PESO} "
  
  if git rev-parse --is-inside-work-tree &>/dev/null; then
    msg "${Gray}Se respetaron las reglas del .gitignore "
    
    # Preguntar si desea ver el desglose detallado
    echo ""
    if confirm_continue "¿Deseas ver el desglose detallado de archivos? [s/n]"; then
      echo ""
      msg "¿Cuántos archivos deseas mostrar por categoría?"
      msg "${Gray}Valor por defecto: 20 (presiona ENTER para usar el valor por defecto)${Color_Off}"
      read -rp "Número de archivos [20]: " num_archivos
      
      # Usar valor por defecto si no se ingresa nada
      if [ -z "$num_archivos" ]; then
        num_archivos=20
      fi
      
      # Validar que sea un número positivo
      if ! [[ "$num_archivos" =~ ^[0-9]+$ ]] || [ "$num_archivos" -le 0 ]; then
        msg "⚠️ Número inválido. Usando valor por defecto: 20" "WARNING"
        num_archivos=20
      fi
      
      msg "Mostrando desglose con ${num_archivos} archivos por categoría..."
      DESGLOSE_MAX_ARCHIVOS=$num_archivos mostrar_desglose_peso_git
    fi
  else
    msg "${Gray}Se omitieron directorios: (.git, node_modules, target, data, ssh_data/logs, __pycache__, build, dist, .vscode, .idea) "
  fi
  
  echo ""
}


function listar_ficheros_acentos(){
    path_log="${CURRENT_DIR}/salida.log"
    find . -type f -regex '.*[áéíóúüñ()].*' > "${path_log}"

    files_count=0
    # Recorrer la variable
    while IFS= read -r archivo || [[ -n "$archivo" ]]; do

        # Extraer el nombre de archivo y la extensión
        nombre_archivo=$(basename "$archivo")
        directorio=$(dirname "$archivo")
        extension="${nombre_archivo##*.}"
        nombre_sin_acentos=$(echo "$nombre_archivo" | sed  'y/áéíóúüñÁÉÍÓÚÜÑ()!\*/aeiouunaeiouun____/')

        # Construir el nuevo nombre de archivo sin acentos
        nuevo_nombre="$directorio/${nombre_sin_acentos%.*}.$extension"

        # Imprimir mensaje de renombrado
        msg "---------------------------------------------------"
        msg "${Cyan}Nombre actual a:: ${archivo}"
        msg "${Yellow}Sera Renombrado a: ${nuevo_nombre} "


         # Renombrar el archivo
        # mv "$archivo" "$nuevo_nombre"
        files_count=$(( files_count + 1 ))
    done < "${path_log}"
    echo ""
    msg "${Blue}encontrados: ${files_count} ${Color_Off}"
    msg "${Color_Off}"
}

function renombrar_ficheros_acentos(){
    path_log="${CURRENT_DIR}/salida.log"
    find . -type f -regex '.*[áéíóúüñ()].*' > "${path_log}"

    files_count=0
    # Recorrer la variable
    while IFS= read -r archivo || [[ -n "$archivo" ]]; do

        # Extraer el nombre de archivo y la extensión
        nombre_archivo=$(basename "$archivo")
        directorio=$(dirname "$archivo")
        extension="${nombre_archivo##*.}"
        nombre_sin_acentos=$(echo "$nombre_archivo" | sed  'y/áéíóúüñÁÉÍÓÚÜÑ()!\*/aeiouunaeiouun____/')

        # Construir el nuevo nombre de archivo sin acentos
        nuevo_nombre="$directorio/${nombre_sin_acentos%.*}.$extension"

        # Imprimir mensaje de renombrado
        msg "---------------------------------------------------"
        msg "${Cyan}Nombre actual a:: ${archivo}"
        msg "${Yellow}Sera Renombrado a: ${nuevo_nombre} "

         # Renombrar el archivo
        mv "$archivo" "$nuevo_nombre"
        files_count=$(( files_count + 1 ))
    done < "${path_log}"
    echo ""
    msg "${Blue}Modificados: ${files_count} ${Color_Off}"

}


function ficheros_omitidos_gitignore(){
  # Verifica si estás en un repositorio Git
  if ! git rev-parse --is-inside-work-tree &>/dev/null; then
    msg "❌ Este directorio no es un repositorio Git."
    exit 1
  fi

  # Verifica si existe el archivo .gitignore
  if [ ! -f .gitignore ]; then
    msg "❌ No se encontró el archivo .gitignore en este directorio."
    exit 1
  fi

  msg "🔍 Buscando archivos ignorados según el .gitignore..."

  # Lista archivos que están siendo ignorados por Git, usando lo declarado en .gitignore
  git ls-files --others --ignored --exclude-standard

}
