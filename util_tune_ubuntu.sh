#!/bin/bash

# util_tune_ubuntu.sh
# Script para actualizar Ubuntu y instalar herramientas esenciales
# Compatible con Ubuntu 24.04.3

set -euo pipefail  # Salir en caso de error

# =============================================================================
# 🎯 SECTION: Variables de Configuración
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
TEMP_PATH_SCRIPT=$(echo "$PATH_SCRIPT" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal basado en el nombre del script.
TEMP_PATH_SCRIPT_SYSTEM=$(echo "${TMPDIR:-/tmp}/${SCRIPT_NAME}" | sed 's/.sh/.tmp/g')  # Ruta para un archivo temporal en /tmp.
ROOT_PATH=$(realpath -m "${CURRENT_DIR}/..")

# Variables para estadísticas
INITIAL_SIZE=0
FINAL_SIZE=0
CLEANED_FILES=0

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
  if [ -n "${SO_SYSTEM:-}" ] && [ "${SO_SYSTEM:-}" = "termux" ]; then
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

# =============================================================================
# 🚀 MAIN SCRIPT: UTIL TUNE UBUNTU
# =============================================================================

# =============================================================================
# 🕐 INICIAR MEDICIÓN DE TIEMPO
# =============================================================================
START_TIME=$(date +%s)
START_TIME_READABLE=$(date "+%Y-%m-%d %H:%M:%S")

# Banner
echo -e "${BBlue}"
cat << 'EOF'
╔═══════════════════════════════════════════════════════════════╗
║                    UTIL TUNE UBUNTU                           ║
║              Actualización y Herramientas Básicas            ║
║                     Ubuntu 24.04.3                           ║
║                                                               ║
║              Ingeniero - Cesar Auris                         ║
║              Teléfono: 937516027                              ║
║              Website: https://solucionessystem.com            ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${Color_Off}"

# Información del sistema y script
msg "Script: ${BWhite}${SCRIPT_NAME}${Color_Off}"
msg "Usuario: ${BWhite}${MY_INFO}${Color_Off}"
msg "Fecha: ${BWhite}${DATE_HOUR_PE}${Color_Off}"
msg "Directorio: ${BWhite}${CURRENT_DIR}${Color_Off}"

# Parámetro para testing rápido (omitir instalaciones)
SKIP_PACKAGES=false
TEST_BASHRC_ONLY=false

if [[ "${1:-}" == "--skip-packages" ]]; then
    SKIP_PACKAGES=true
    msg "🚀 Modo testing: omitiendo instalación de paquetes" "WARNING"
elif [[ "${1:-}" == "--test-bashrc" ]]; then
    TEST_BASHRC_ONLY=true
    msg "🧪 Modo testing: solo configuración bashrc" "WARNING"
fi

# Verificar que se ejecuta como root o con sudo (excepto en modo test-bashrc)
if [[ $EUID -ne 0 && "$TEST_BASHRC_ONLY" != "true" ]]; then
   msg "Este script debe ejecutarse como root (sudo)" "ERROR"
   msg "Para testing de bashrc, usa: $0 --test-bashrc" "INFO"
   exit 1
fi

# Si es solo testing de bashrc, ir directamente a esa función (después de definirla)
TEST_BASHRC_EARLY_EXIT=false
if [[ "$TEST_BASHRC_ONLY" == "true" ]]; then
    TEST_BASHRC_EARLY_EXIT=true
    msg "🔧 Modo testing de bashrc activado" "INFO"
fi

# Skip everything if testing bashrc only
if [[ "$TEST_BASHRC_ONLY" == "true" ]]; then
    msg "🧪 Saltando actualización del sistema en modo testing" "WARNING"
else
    msg "=== INICIANDO ACTUALIZACIÓN DE UBUNTU ===" "INFO"
fi

# 1. ACTUALIZACIÓN DE REPOSITORIOS Y SISTEMA
if [[ "$TEST_BASHRC_ONLY" != "true" ]]; then
msg "Actualizando lista de repositorios..." "INFO"
if apt update > /dev/null 2>&1; then
    msg "Lista de repositorios actualizada correctamente" "SUCCESS"
else
    msg "Error al actualizar repositorios" "ERROR"
    exit 1
fi

msg "Actualizando el sistema completo..." "INFO"
if apt upgrade -y > /dev/null 2>&1; then
    msg "Sistema actualizado correctamente" "SUCCESS"
else
    msg "Error durante la actualización del sistema" "ERROR"
    exit 1
fi

msg "Actualizando distribución (si hay actualizaciones disponibles)..." "INFO"
if apt dist-upgrade -y > /dev/null 2>&1; then
    msg "Distribución actualizada correctamente" "SUCCESS"
else
    msg "Error durante la actualización de distribución" "WARNING"
fi

# 2. INSTALACIÓN DE HERRAMIENTAS BÁSICAS
if [[ "$SKIP_PACKAGES" == "false" ]]; then
    msg "Iniciando instalación de herramientas esenciales..." "INFO"
else
    msg "Omitiendo instalación de paquetes (modo testing)" "WARNING"
fi

# Lista de paquetes a instalar
PACKAGES=(
    "curl"                    # Herramienta para transferir datos
    "wget"                    # Descargador de archivos
    "git"                     # Control de versiones
    "vim"                     # Editor de texto
    "neovim"                  # Editor moderno (nvim)
    "nano"                    # Editor simple
    "htop"                    # Monitor de procesos
    "tree"                    # Visualizador de directorios
    "unzip"                   # Descompresor
    "zip"                     # Compresor
    "build-essential"         # Herramientas de compilación
    "software-properties-common" # Gestión de repositorios
    "apt-transport-https"     # Soporte HTTPS para apt
    "ca-certificates"         # Certificados CA
    "gnupg"                   # Herramientas GPG
    "lsb-release"            # Información de la distribución
    "net-tools"              # Herramientas de red
    "rsync"                  # Sincronización de archivos
    "screen"                 # Multiplexor de terminal
    "tmux"                   # Multiplexor moderno
)

msg "Paquetes a instalar: ${BGray}${PACKAGES[*]}${Color_Off}" "DEBUG"

# Instalar paquetes uno por uno para mejor control
INSTALLED_COUNT=0
ALREADY_INSTALLED_COUNT=0
FAILED_COUNT=0

for package in "${PACKAGES[@]}"; do
    if dpkg -l | grep -q "^ii  $package "; then
        msg "📦 $package ya está instalado" "DEBUG"
        ((ALREADY_INSTALLED_COUNT++))
    else
        msg "📥 Instalando $package..." "INFO"
        echo "Ejecutando: apt install -y $package"
        if timeout 120 apt install -y "$package"; then
            msg "✅ $package instalado correctamente" "SUCCESS"
            ((INSTALLED_COUNT++))
        else
            msg "❌ Error o timeout instalando $package" "ERROR"
            ((FAILED_COUNT++))
        fi
    fi
done

# Resumen de instalación
msg "📊 Resumen de instalación:" "INFO"
echo -e "   ${BGreen}✅ Instalados: $INSTALLED_COUNT${Color_Off}"
echo -e "   ${BYellow}📦 Ya instalados: $ALREADY_INSTALLED_COUNT${Color_Off}"
echo -e "   ${BRed}❌ Fallidos: $FAILED_COUNT${Color_Off}"

# 3. FUNCIÓN DE CONFIGURACIÓN COMPLETA DE NEOVIM
# ==============================================================================
# 📝 Función: configure_neovim
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Configura Neovim completamente con:
#   - Estructura de directorios
#   - Tema onedark
#   - Plugin manager vim-plug
#   - Configuración completa init.vim
#   - Script auxiliar 'vi' para acceso rápido
#
# 🔧 Parámetros: Ninguno
# 💡 Uso: configure_neovim
# ==============================================================================
configure_neovim() {
    local target_user="${SUDO_USER:-$USER}"
    local user_home
    user_home=$(eval echo "~$target_user")

    msg "🚀 Iniciando configuración completa de Neovim..." "INFO"

    # Verificar que Neovim esté instalado
    if ! command -v nvim &> /dev/null; then
        msg "❌ Neovim no está instalado. Saltando configuración." "ERROR"
        return 1
    fi

    # Configurar como editor predeterminado
    msg "⚙️  Configurando Neovim como editor predeterminado..." "INFO"
    update-alternatives --install /usr/bin/editor editor /usr/bin/nvim 60 > /dev/null 2>&1

    # Cambiar al usuario correcto para las configuraciones
    sudo -u "$target_user" bash << 'NVIM_CONFIG_EOF'

    # Crear estructura de directorios para Neovim
    msg() {
        echo -e "\033[1;34m[INFO]\033[0m $1"
    }

    msg "📁 Creando estructura de directorios..."
    mkdir -p ~/.config/nvim
    mkdir -p ~/.config/nvim/colors
    mkdir -p ~/.local/share/nvim/swap
    mkdir -p ~/.local/bin
    touch ~/.config/nvim/init.vim

    # Descargar tema onedark
    msg "🎨 Descargando tema onedark..."
    curl -fLo ~/.config/nvim/colors/onedark.vim --create-dirs \
        https://raw.githubusercontent.com/joshdick/onedark.vim/main/colors/onedark.vim 2>/dev/null

    # Instalar vim-plug (administrador de plugins)
    msg "🔌 Instalando vim-plug..."
    curl -fLo ~/.local/share/nvim/site/autoload/plug.vim --create-dirs \
        https://raw.githubusercontent.com/junegunn/vim-plug/master/plug.vim 2>/dev/null

    # Crear configuración completa init.vim
    msg "📝 Creando configuración init.vim..."
    cat > ~/.config/nvim/init.vim << 'EOF'
" init.vim - Archivo de configuración de Neovim

" -----------------------------------------
" -- Configuración de vim-plug para gestionar plugins
" -----------------------------------------
call plug#begin('~/.local/share/nvim/plugged')

" Esquema de color onedark
Plug 'joshdick/onedark.vim'

" Explorador de archivos NERDTree
Plug 'preservim/nerdtree'

" Fuzzy finder fzf
Plug 'junegunn/fzf', { 'do': { -> fzf#install() } }
Plug 'junegunn/fzf.vim'

" Barra de estado avanzada lightline
Plug 'itchyny/lightline.vim'

" Agregar íconos a NERDTree
Plug 'ryanoasis/vim-devicons'

" Sintaxis múltiple
Plug 'sheerun/vim-polyglot'

" Autocompletado de llaves, corchetes, etc.
Plug 'jiangmiao/auto-pairs'

" Encerrar palabras en paréntesis, corchetes, llaves, etc.
Plug 'tpope/vim-surround'

" Emmet para desarrollo web
Plug 'mattn/emmet-vim'

" Visualiza la indentación en el código
Plug 'Yggdroot/indentLine'

call plug#end()

" -----------------------------------------
" -- Configuración de tabulaciones y espacios
" -----------------------------------------
set expandtab           " Usa espacios en lugar de tabulaciones
set tabstop=4           " Número de espacios que una tabulación representa
set shiftwidth=4        " Tamaño de la indentación
set softtabstop=4       " Número de espacios al usar la tecla de tabulación

" -----------------------------------------
" -- Apariencia
" -----------------------------------------
set cursorline          " Resalta la línea actual
set number              " Muestra número de línea
set relativenumber      " Muestra números de línea relativos
syntax on               " Activa el resaltado de sintaxis
set background=dark     " Usa fondo oscuro
set termguicolors       " Habilita colores verdaderos
colorscheme onedark     " Configura el esquema de color onedark

" Usa una fuente monoespaciada (si la terminal lo soporta)
if has("gui_running")
    set guifont=Monospace\ 12
endif

" -----------------------------------------
" -- Configuración de búsqueda
" -----------------------------------------
set incsearch           " Búsqueda incremental
set hlsearch            " Resalta coincidencias
set ignorecase          " Ignora mayúsculas en la búsqueda
set smartcase           " No ignora mayúsculas si la búsqueda contiene mayúsculas

" -----------------------------------------
" -- Configuración del portapapeles y edición
" -----------------------------------------
set clipboard=unnamedplus   " Usa el portapapeles del sistema
set visualbell              " Desactiva la campana visual

" -----------------------------------------
" -- Autocomandos
" -----------------------------------------
" Restaurar la posición del cursor al abrir un archivo
autocmd BufReadPost *
    \ if line("'\"") > 1 && line("'\"") <= line("$") |
    \   exe "normal! g'\"" |
    \ endif

" -----------------------------------------
" -- Configuración de archivos de intercambio (swap)
" -----------------------------------------
set swapfile                             " Habilita archivos de intercambio
set directory=~/.local/share/nvim/swap// " Directorio para archivos de intercambio

" -----------------------------------------
" -- Mapas de teclas personalizados
" -----------------------------------------
let mapleader=" "          " Configura la tecla líder

" Guardar, salir y guardar y salir
nnoremap <leader>w :w<CR>
nnoremap <leader>q :q<CR>
nnoremap <leader>x :wq<CR>

" Mover líneas de código hacia arriba y abajo
nnoremap <A-j> :m .+1<CR>==
nnoremap <A-k> :m .-2<CR>==
vnoremap <A-j> :m '>+1<CR>gv=gv
vnoremap <A-k> :m '<-2<CR>gv=gv

" Copiar y pegar desde/para el portapapeles del sistema
vnoremap <leader>y "+y
nnoremap <leader>Y gg"+yG
vnoremap <leader>p "+p
nnoremap <leader>P gg"+p

" Abrir y cerrar el explorador de archivos NERDTree (espacio+e)
nnoremap <leader>e :NERDTreeToggle<CR>

" Mostrar archivos ocultos en NERDTree
let NERDTreeShowHidden=1

" Navegar entre buffers
nnoremap <leader>bn :bnext<CR>
nnoremap <leader>bp :bprevious<CR>

" Divisiones de ventana
nnoremap <leader>sv :vsplit<CR>
nnoremap <leader>sh :split<CR>
nnoremap <leader>sc :close<CR>

" Ajustar tamaño de ventanas
nnoremap <C-w><left> :vertical resize -2<CR>
nnoremap <C-w><right> :vertical resize +2<CR>
nnoremap <C-w><up> :resize +2<CR>
nnoremap <C-w><down> :resize -2<CR>

" -----------------------------------------
" -- Configuración de FZF
" -----------------------------------------

" Mapas de teclas para fzf

" Buscar archivos en el proyecto
nnoremap <leader>f :Files<CR>
" Buscar archivos en el repositorio Git
nnoremap <leader>g :GFiles<CR>
" Buscar buffers abiertos
nnoremap <leader>b :Buffers<CR>
" Buscar líneas en el archivo actual
nnoremap <leader>l :Lines<CR>
" Buscar en el historial de comandos
nnoremap <leader>h :History<CR>
" Buscar comandos de Neovim
nnoremap <leader>c :Commands<CR>

" Opciones de fzf
let g:fzf_layout = { 'down': '40%' }  " Muestra fzf en la parte inferior ocupando el 40% de la pantalla

" ------------------------------------
" Abrir en nueva pestaña
" Ctrl-t para abrir en nueva pestaña
" Ctrl-x para abrir en split horizontal
" Ctrl-v para abrir en split vertical
let g:fzf_action = {
    \ 'enter': 'tabedit',
    \ 'ctrl-t': 'tabedit',
    \ 'ctrl-x': 'split',
    \ 'ctrl-v': 'vsplit'
    \ }

" -----------------------------------------
" -- Configuración para Python 3
" -----------------------------------------
let g:python3_host_prog = '/usr/bin/python3'

" -----------------------------------------
" -- Otros ajustes
" -----------------------------------------
set showmode             " Muestra el modo actual en la barra de estado

" Configura el esquema de color
colorscheme onedark

" -----------------------------------------
" -- Configuración de lightline
" -----------------------------------------
let g:lightline = {
      \ 'colorscheme': 'onedark',
      \ 'active': {
      \   'left': [ [ 'mode', 'paste' ],
      \             [ 'readonly', 'filename', 'modified' ] ]
      \ },
      \ 'component_function': {
      \   'filename': 'LightlineFilename'
      \ },
      \ }

function! LightlineFilename()
    return expand('%:t')
endfunction

" Para darle la sintaxis a los ficheros (.cnf, 50-server.cnf , my.cnf)
autocmd BufRead,BufNewFile *.cnf,*.cf,*.local,*.allow,*.deny set filetype=dosini

EOF

    # Crear script auxiliar 'vi' para acceso rápido
    msg "🔗 Creando script auxiliar 'vi'..."
    cat > ~/.local/bin/vi << 'EOF'
#!/bin/bash
# Script auxiliar para acceso rápido a Neovim

# Función para mostrar el uso del script
mostrar_uso() {
  echo "Uso: vi [archivo]"
}

# Comprueba si se proporciona un argumento
if [ $# -eq 0 ]; then
  nvim
else
  if [ -e "$1" ]; then
    nvim "$1"
  else
    echo "Error: El archivo '$1' no existe."
    mostrar_uso
    exit 1
  fi
fi
EOF

    chmod +x ~/.local/bin/vi

    # Agregar ~/.local/bin al PATH si no está
    if ! echo "$PATH" | grep -q "$HOME/.local/bin"; then
        echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc
        msg "📍 Se agregó ~/.local/bin al PATH en ~/.bashrc"
    fi

NVIM_CONFIG_EOF

    # Cambiar propietario de los archivos creados
    chown -R "$target_user:$target_user" "$user_home/.config/nvim" 2>/dev/null || true
    chown -R "$target_user:$target_user" "$user_home/.local" 2>/dev/null || true

    msg "✅ Configuración completa de Neovim terminada" "SUCCESS"
    msg "📚 Para instalar plugins, ejecuta: nvim +PlugInstall +qall" "INFO"
    msg "🎯 Script 'vi' creado para acceso rápido a nvim" "INFO"
}

# 4. FUNCIÓN DE CONFIGURACIÓN COMPLETA DEL BASHRC
# ==============================================================================
# Cerrar el bloque if para testing
fi

# 📝 Función: configure_advanced_bashrc
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Configura un .bashrc avanzado con:
#   - Prompt personalizado con Git y SSH
#   - Funciones avanzadas (Docker, FZF, búsquedas, etc.)
#   - Aliases útiles y optimizados
#   - Menú interactivo
#   - Auto-detección e instalación de paquetes
#   - Configuración de colores y variables
#
# 🔧 Parámetros: Ninguno
# 💡 Uso: configure_advanced_bashrc
# ==============================================================================
configure_advanced_bashrc() {
    local target_user="${SUDO_USER:-${USER:-$(whoami)}}"

    # Validación del usuario objetivo
    if [ -z "$target_user" ]; then
        target_user="$(whoami)"
    fi

    local user_home
    user_home=$(eval echo "~$target_user")

    # Log de debugging
    local debug_log="${TEMP_PATH_SCRIPT}.debug"

    msg "🔧 Iniciando configuración avanzada del .bashrc..." "INFO"

    # Debug: información del usuario y sistema
    {
        echo "=== DEBUG LOG: configure_advanced_bashrc ==="
        echo "Fecha: $(date)"
        echo "SUDO_USER: ${SUDO_USER:-'NO_SET'}"
        echo "USER: ${USER:-'NO_SET'}"
        echo "target_user: $target_user"
        echo "user_home: $user_home"
        echo "Directorio actual: $(pwd)"
        echo "UID: $(id -u)"
        echo "Usuario efectivo: $(whoami)"
        echo "============================================"
    } > "$debug_log"

    msg "📁 Debug log creado en: $debug_log" "DEBUG"
    msg "👤 Usuario objetivo: $target_user" "DEBUG"
    msg "🏠 Directorio home: $user_home" "DEBUG"

    # Hacer backup del .bashrc actual
    if [ -f "$user_home/.bashrc" ]; then
        cp "$user_home/.bashrc" "$user_home/.bashrc.backup.$(date +%Y%m%d_%H%M%S)"
        msg "Backup creado: .bashrc.backup.$(date +%Y%m%d_%H%M%S)" "INFO"
    fi

    # Agregar contenido personalizado al .bashrc
    msg "📝 Ejecutando configuración como usuario: $target_user" "DEBUG"

    # Debugging antes del heredoc
    echo "Intentando escribir en: $user_home/.bashrc" >> "$debug_log"
    echo "Permisos del directorio home: $(ls -ld "$user_home" 2>/dev/null || echo 'NO_ACCESSIBLE')" >> "$debug_log"
    echo "Archivo .bashrc actual: $(ls -la "$user_home/.bashrc" 2>/dev/null || echo 'NO_EXISTE')" >> "$debug_log"

    # En modo testing, ejecutar directamente; en modo normal, usar sudo
    if [[ "${TEST_BASHRC_ONLY:-false}" == "true" ]]; then
        msg "🧪 Modo testing: ejecutando sin sudo" "DEBUG"
        bash << 'BASHRC_CONFIG_EOF'
# Debug interno del heredoc
echo "=== INICIO HEREDOC ===" >> ~/.bashrc.debug.log 2>/dev/null || true
    echo "Usuario dentro del heredoc: $(whoami)" >> ~/.bashrc.debug.log 2>/dev/null || true
    echo "HOME dentro del heredoc: $HOME" >> ~/.bashrc.debug.log 2>/dev/null || true
    echo "Fecha: $(date)" >> ~/.bashrc.debug.log 2>/dev/null || true

    # Agregar la configuración completa de .bashrc
    cat >> ~/.bashrc << 'COMPLETE_BASHRC_EOF'

VERSION_BASHRC=3.0.0
VERSION_PLATFORM='linux-gitbash'

# ::::::::::::: START CONSTANT ::::::::::::::
DATE_HOUR=$(date -u "+%Y-%m-%d %H:%M:%S") # Fecha y hora actual en formato: YYYY-MM-DD_HH:MM:SS (hora local)
DATE_HOUR_PE=$(date -u -d "-5 hours" "+%Y-%m-%d %H:%M:%S") # Fecha y hora actual en Perú (UTC -5)
PATH_BASHRC='~/.bashrc'  # Ruta del archivo .bashrc
# ::::::::::::: END CONSTANT ::::::::::::::

# ==========================================================================
# VERSION: (linux, gitbash)
# START ~/.bashrc - Configuración de Bash por César (version: 1.0.3)
# ==========================================================================

# Este archivo contiene configuraciones personalizadas, alias, funciones,
# y otras optimizaciones para mejorar la experiencia en la terminal.
# Para aplicar cambios después de editar, usa: `source ~/.bashrc`.
# ==========================================================================

# 🧯 Desactiva el cierre automático de la sesión Bash por inactividad.
# TMOUT es una variable especial que cierra la sesión si está inactiva por X segundos.
# Al ponerla en 0, desactivamos ese mecanismo.
export TMOUT=0

# ========================
# 1. Personalización del prompt (PS1)
# ========================
# El prompt es la línea inicial de cada comando en la terminal.
# Esta configuración muestra: usuario@host:directorio_actual (con colores).

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

# Fondo gris oscuro,fondo gris claro
Code_background='\e[7;90;47m'   # Black

# Prompt básico con colores
export PS1='\[\e[32m\]\u@\h:\[\e[34m\]\w\[\e[0m\]\$ '

# Agregar información del branch Git al prompt
parse_git_branch() {
    git branch 2> /dev/null | sed -n -e 's/^\* \(.*\)/(\1)/p'
}

# ========================================
# Configuración del Prompt
# example output: root@server1 /root/curso_vps (master)#
export PS1="\[\e[36m\][\D{%Y-%m-%d %H:%M:%S}]\[\e[0m\] \[\e[35m\]\u@\h\[\e[0m\] \[\e[34m\]\w\[\e[33m\] \$(parse_git_branch)\[\e[0m\]\$( [ \$(id -u) -eq 0 ] && echo '#' || echo ' ) "

# Si la sesión es SSH, cambia el color del prompt
if [ -n "$SSH_CONNECTION" ]; then
    # ========================================
    # Configuración del Prompt
    # example output: root@server1 (SSH) /root/curso_vps (master)#
    export PS1="\[\e[36m\][\D{%Y-%m-%d %H:%M:%S}]\[\e[0m\] \[\e[35m\]\u@\h (SSH):\[\e[0m\] \[\e[34m\]\$(pwd)\[\e[33m\] \$(parse_git_branch)\[\e[0m\]\$( [ \$(id -u) -eq 0 ] && echo '#' || echo ' ) "
fi

# ========================
# 2. Alias útiles
# ========================

# Alias básicos
alias ll='ls -lh --color=auto'        # Lista archivos con tamaños legibles
alias la='ls -lha --color=auto'       # Lista todos los archivos, incluidos ocultos
alias ..='cd ..'                      # Subir un nivel en el árbol de directorios
alias ...='cd ../..'                  # Subir dos niveles
alias cls='clear'                     # Limpiar la pantalla
alias grep='grep --color=auto'        # Resaltar coincidencias
alias df='df -h'                      # Mostrar uso de disco en formato legible
alias free='free -m'                  # Mostrar memoria libre en MB
alias h='history'                     # Mostrar historial de comandos

# Alias avanzados
alias search='find . -iname'          # Buscar archivos por nombre
alias bigfiles='du -ah . | sort -rh | head -n 10' # Archivos más grandes
alias newestfile='ls -Art | tail -n 1' # Archivo más reciente
alias ports='netstat -tulnp | grep LISTEN'   # Mostrar puertos abiertos
alias update='sudo apt update && sudo apt upgrade -y' # Actualizar sistema
alias reload="source $PATH_BASHRC"             # Recargar configuraciones de Bash
alias reload_cat="cat $PATH_BASHRC | less"           #
# alias efectos
alias mm='cmatrix'             # efecto cmatrix

# ========================
# 3. Historial mejorado
# ========================
# Configura el historial para almacenar más comandos y con formato de fecha y hora.
export HISTSIZE=10000               # Número de comandos guardados en memoria
export HISTFILESIZE=20000           # Número de comandos guardados en disco
export HISTTIMEFORMAT="%F %T "      # Formato de fecha y hora (AAAA-MM-DD HH:MM:SS)
export HISTCONTROL=ignoredups:ignorespace # Ignorar duplicados y comandos con espacio inicial

# ========================
# 4. Variables de entorno
# ========================
export PATH=$PATH:/opt/mis-scripts   # Añadir scripts personalizados al PATH

# Editor de texto predeterminado en terminal
if command -v nvim &> /dev/null; then
    export EDITOR=nvim
else
    export EDITOR=vim
fi

# ========================
# 5. Colores para comandos comunes
# ========================
# Mejoras visuales para comandos como `ls` y `grep`.

alias ls='ls --color=auto'
alias grep='grep --color=auto'

# Configuración de `dircolors` si está disponible
force_color_prompt=yes
if [ -x /usr/bin/dircolors ]; then
    test -r ~/.dircolors && eval "$(dircolors ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
fi

# ========================
# 6. Funciones personalizadas
# ========================

# Buscar texto en múltiples archivos
search_text() {
    grep -rin "$1" . 2>/dev/null
}
# Ejemplo de uso: search_text "palabra_clave"

# Función: directory_space
# Descripción:
#   Muestra el tamaño ocupado por cada subdirectorio dentro de una ruta dada,
#   ordenado de mayor a menor. Si no se proporciona una ruta como argumento,
#   usa el directorio actual por defecto.
#
# Uso:
#   directory_space [ruta]
#
# Parámetros:
#   ruta (opcional): Ruta del directorio a analizar. Si no se proporciona,
#                    se usará el directorio actual.
#
# Ejemplos:
#   directory_space            # Analiza el directorio actual
#   directory_space /var/log   # Analiza el directorio /var/log
#
# Notas:
#   - Usa 'du' con --max-depth=1 para listar solo el tamaño de cada subdirectorio.
#   - Ordena los resultados en orden descendente por tamaño.
#   - El tamaño total del directorio también se muestra al final.
directory_space() {
    local path="${1:-.}"
    echo "Analizando: $path"
    du -h --max-depth=1 "$path" | sort -rh
}

# Listar los archivos más pesados
# Muestra los archivos más pesados en un directorio.
find_heaviest_files() {
    local directory=${1:-.}  # Directorio a analizar, por defecto es el actual
    local limit=${2:-10}     # Número de archivos a mostrar, por defecto 10

    echo "Buscando los $limit archivos más pesados en el directorio: $directory"
    echo "-----------------------------------------------"
    find "$directory" -type f -exec du -h {} + | sort -rh | head -n "$limit"
}
# Ejemplo de uso: find_heaviest_files "/var/log" 5

# Buscar archivos por tamaño
# Muestra archivos con un tamaño mayor al especificado.
find_files_by_size() {
    local directory=${1:-.}     # Directorio a analizar, por defecto es el actual.
    local size=${2:-1M}         # Tamaño mínimo de los archivos, por defecto 1 Megabyte.

    # Verificar si el directorio existe
    if [ ! -d "$directory" ]; then
        echo "Error: El directorio '$directory' no existe."
        return 1
    fi

    echo "Buscando archivos en '$directory' con un tamaño mayor a $size:"
    echo "------------------------------------------------------------"
    find "$directory" -type f -size +"$size" -exec du -h {} + 2>/dev/null | sort -rh
}
# Ejemplo de uso: find_files_by_size . 5M

# Iniciar un servidor HTTP simple
# Inicia un servidor HTTP en el puerto especificado.
simple_server() {
    local port=${1:-8000}
    echo "Servidor disponible en http://localhost:$port"
    python3 -m http.server "$port"
}
# Ejemplo de uso: simple_server 8080

# Generar claves SSH
# Genera una clave SSH con una etiqueta específica.
generar_ssh() {
    ssh-keygen -t rsa -b 4096 -C "$1"
    echo "Clave SSH generada para: $1"
}
# Ejemplo de uso: generar_ssh usuario@dominio.com

# Comparar archivos
# Compara dos archivos mostrando las diferencias lado a lado.
comparar() {
    diff -y "$1" "$2"
}
# Ejemplo de uso: comparar archivo1.txt archivo2.txt

# Controlar CyberPanel
# Detiene y deshabilita el servicio de CyberPanel.
stop_cyber_panel() {
    systemctl stop lscpd && systemctl disable lscpd && systemctl status lscpd
}
# Ejemplo de uso: stop_cyber_panel

# Inicia y habilita el servicio de CyberPanel.
start_cyber_panel() {
    systemctl enable lscpd && systemctl start lscpd && systemctl status lscpd
}
# Ejemplo de uso: start_cyber_panel

###############################################
# 📄 FUNCTION: listar_archivos_recientes_modificados
###############################################
# Lists the most recently modified files in a given directory.
#
# @param $1 - Directory path to scan (default: .)
# @param $2 - Number of files to display (default: 10)
#
# @return Prints the most recently modified files with their date and time.
#
# 🧪 Example usage:
#   listar_archivos_recientes_modificados "/var/www/html" 15
#   listar_archivos_recientes_modificados "/home/user"
#   listar_archivos_recientes_modificados
###############################################
listar_archivos_recientes_modificados() {
  local path="${1:-.}"         # Default path: current directory
  local count="${2:-10}"       # Default count: 10 files

  if [ ! -d "$path" ]; then
    echo "❌ Error: '$path' is not a valid directory."
    return 1
  fi

  echo "📁 Showing the last $count modified files in: $path"
  echo "─────────────────────────────────────────────────────────────"

  find "$path" -type f -printf '%TY-%Tm-%Td %TH:%TM:%TS %p\n' \
    | sort \
    | tail -n "$count"
}

# ----------------------------------------
# Function: detect_system
# Detects the operating system distribution.
# Returns:
#   - "termux"  -> If running in Termux
#   - "wsl"     -> If running on Windows Subsystem for Linux
#   - "ubuntu"  -> If running on Ubuntu/Debian-based distributions
#   - "redhat"  -> If running on Red Hat, Fedora, CentOS, Rocky, or AlmaLinux
#   - "gitbash" -> If running on Git Bash
#   - "unknown" -> If the system is not recognized
#
# Example usage:
#   system=$(detect_system)
#   echo "Detected system: $system"
# ----------------------------------------
detect_system() {
    if [ -f /data/data/com.termux/files/usr/bin/pkg ]; then
        echo "termux"
    elif grep -q Microsoft /proc/version; then
        echo "wsl"
    elif [ -f /etc/os-release ]; then
        # Lee el ID de /etc/os-release
        source /etc/os-release
        case $ID in
            ubuntu|debian)
                echo "ubuntu"
                ;;
            rhel|centos|fedora|rocky|almalinux)
                echo "redhat"
                ;;
            *)
                echo "unknown"
                ;;
        esac
    elif [ -n "$MSYSTEM" ]; then
        echo "gitbash"
    else
        echo "unknown"
    fi
}

# ----------------------------------------
# Function: install_package
# Installs a package based on the detected operating system.
#
# Parameters:
#   $1 -> Name of the package to install
#
# Example usage:
#   install_package fzf
#   install_package neovim
#
# Notes:
# - If running on Git Bash, it only supports installing fzf.
# - If the system is unrecognized, manual installation is required.
# ----------------------------------------
install_package() {
    package=$1  # Package name

    case "$system" in
        ubuntu|wsl)
            echo "🟢 Installing $package on Ubuntu/Debian..."
            sudo apt update -y && sudo apt install -y "$package"
            ;;
        redhat)
            echo "🔵 Installing $package on Red Hat/CentOS/Fedora..."
            # Usa dnf si está disponible, sino yum
            if command -v dnf &> /dev/null; then
                sudo dnf install -y "$package"
            else
                sudo yum install -y "$package"
            fi
            ;;
        termux)
            echo "📱 Installing $package on Termux..."
            pkg update -y && pkg install -y "$package"
            ;;
        gitbash)
            if [ "$package" == "fzf" ]; then
                echo "🪟 Installing fzf on Git Bash..."
                git clone --depth 1 https://github.com/junegunn/fzf.git ~/.fzf
                ~/.fzf/install --all
            fi
            ;;
        *)
            echo "❌ Unrecognized system. Please install $package manually."
            ;;
    esac
}

# ----------------------------------------
# Function: check_and_install
# Checks if a package is installed, if not, installs it.
#
# Parameters:
#   $1 -> Name of the package to check and install
#   $2 -> Command to check in terminal (optional, defaults to $1)
#
# Example usage:
#   check_and_install fzf
#   check_and_install bat batcat
# ----------------------------------------
check_and_install() {
    local package="$1"  # Package name
    local command_to_check="${2:-$1}"  # Command to check (defaults to package name)

    # Primero prueba con 'which' si está disponible, de lo contrario usa 'command -v'
    if command -v which &> /dev/null; then
        if ! which "$command_to_check" &> /dev/null; then
            echo "⚠️ Command '${command_to_check}' (package: ${package}) is not installed. Installing now..."
            install_package "$package"
        fi
    else
        if ! command -v "$command_to_check" &> /dev/null; then
            echo "⚠️ Command '${command_to_check}' (package: ${package}) is not installed. Installing now..."
            install_package "$package"
        fi
    fi
}

mostrar_uso() {
  echo "Uso: vi [archivo]"
}

vi() {
    if [ -f "$1" ]; then
        if command -v nvim &> /dev/null; then
            nvim "$1"
        else
            vim "$1"
        fi

    else
        echo -en "--- ${Red}Error: El archivo '$1' no existe.${Color_Off} \n"
        mostrar_uso
        return 1
    fi
}

# ================================================
# funcion requiere lnav
# ================================================
log() {
    # 1) Requiere al menos un argumento
    if [ $# -eq 0 ]; then
        echo -e "--- ${Yellow}Advertencia:${Color_Off} no se proporcionaron rutas."
        echo -e "Uso: log <archivo|directorio> [más_rutas...]"
        echo -e "Ej.:  log /var/log/mail.log /var/log/lfd.log /var/log"
        return 2
    fi

    # 2) Verificar que lnav esté disponible
    if ! command -v lnav >/dev/null 2>&1; then
        echo -e "--- ${Red}Error:${Color_Off} 'lnav' no está instalado o no está en el PATH."
        echo -e "     Instálalo con: ${Green}sudo snap install lnav${Color_Off} (o tu método preferido)"
        return 127
    fi

    # 3) Filtrar rutas existentes y avisar de las inexistentes
    local found=()
    local missing=0
    for path in "$@"; do
        if [ -e "$path" ]; then
            found+=("$path")
        else
            echo -e "--- ${Yellow}Aviso:${Color_Off} la ruta '${path}' no existe (omitida)."
            missing=$((missing+1))
        fi
    done

    # 4) Si no quedó ninguna ruta válida, salir con error
    if [ ${#found[@]} -eq 0 ]; then
        echo -e "--- ${Red}Error:${Color_Off} no hay rutas válidas para abrir."
        echo -e "Uso: log <archivo|directorio> [más_rutas...]"
          echo -e "Ej.:  log /var/log/mail.log /var/log/lfd.log /var/log"
        return 1
    fi

    # 5) Ejecutar lnav en zona horaria America/Lima
    #    Nota: no modifica archivos; solo cambia la visualización.
    TZ=America/Lima lnav "${found[@]}"
}

# -----------------------------------------------------------------------------
# Function: show_date
# Description: Displays the current date and time in three formats:
#              - Readable format in Spanish (local system time)
#              - UTC time
#              - Peru time (calculated as UTC -5)
# Usage: Call the function without arguments: show_date
# -----------------------------------------------------------------------------
show_date() {
    # Readable date in Spanish
    readable_date=$(LC_TIME=es_ES.UTF-8 date "+%A %d de %B de %Y, %H:%M:%S")

    # Date in UTC
    utc_date=$(date -u "+%Y-%m-%d %H:%M:%S UTC")

    # Date in Peru (UTC -5)
    peru_date=$(date -u -d "-5 hours" "+%Y-%m-%d %H:%M:%S UTC-5")

    # Display results
    echo "Fecha actual (formato legible): $readable_date"
    echo "Fecha actual en UTC:            $utc_date"
    echo "Fecha actual en Perú (UTC-5):   $peru_date"
}

# ==============================================================================
# 📦 Función: create_file
# ------------------------------------------------------------------------------
# ✅ Descripción:
#   Crea un archivo con contenido ingresado en múltiples líneas desde la terminal
#   (finalizando con Ctrl+D). Si no se pasa un nombre de archivo como parámetro,
#   lo solicita interactivamente. Luego marca el archivo como ejecutable.
#
# 💡 Uso:
#   create_file              # Solicita nombre interactivo
#   create_file fichero.txt  # Usa nombre pasado como parámetro
#
# 🎨 Requiere:
#   - Permiso de escritura en el directorio actual
#   - Variables de color definidas previamente
# ==============================================================================
create_file() {
  local FILE_NAME="$1"

  echo ""

  # Si no se pasa como parámetro, pedirlo al usuario
  if [ -z "$FILE_NAME" ]; then
    echo -e "${BBlue}✏️️  Nombre del archivo a crear (ej. mi_script.sh):${Color_Off}"
    read -rp "> " FILE_NAME
  fi

  if [ -z "$FILE_NAME" ]; then
    echo -e "${BRed}❌ Error: Debes ingresar un nombre de archivo válido.${Color_Off}"
    return 1
  fi

  if [ -f "$FILE_NAME" ]; then
    echo -e "${BYellow}⚠️  El archivo ya existe. ¿Deseas sobrescribirlo? [s/n]${Color_Off}"
    read -rp "> " RESP
    [[ "$RESP" != [sS] ]] && echo -e "${BRed}❌ Cancelado.${Color_Off}" && return 1
  fi

  echo ""
  echo -e "${BPurple}✏️  Escribe el contenido del archivo (Ctrl+D para finalizar):${Color_Off}"
  CONTENT=$(cat)

  echo "$CONTENT" > "$FILE_NAME"
  chmod +x "$FILE_NAME"

  echo ""
  echo -e "${BGreen}✅ Archivo '$FILE_NAME' creado correctamente y marcado como ejecutable.${Color_Off}"
}

# ========================
# 6. Verificar y instalar paquetes necesarios
# ========================

# Detect operating system
system=$(detect_system)

# Check and install fzf if not installed (no message if already installed)
if [[ "$system" == "ubuntu" || "$system" == "wsl" ]]; then
    # echo "🔄 verificaciones para  Debian/WSL..."
    check_and_install fzf fzf
    check_and_install tree tree
elif [[ "$system" == "redhat" ]]; then
    # echo "🔄 Instalando fzf en CentOS/RHEL..."
    check_and_install tree tree
fi

# ========================
# 7. Menú interactivo
# ========================

alias ls='ls --color=auto'

if [ -x /usr/bin/dircolors ]; then
    test -r ~/.dircolors && eval "$(dircolors ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
fi

# Verificar si el sistema operativo es Linux
if [[ "$(uname -s)" == "Linux" ]]; then
    # echo "El sistema operativo es Linux. Configurando ulimit..."
    ulimit -n 4096
fi

menu(){
  echo -e "${Gray}========================${Color_Off}"
  echo -e "${Gray}VERSION_BASHRC: ${VERSION_BASHRC}${Color_Off}"
  echo -e "${Gray}VERSION_PLATFORM: ${VERSION_PLATFORM}${Color_Off}"
  echo -e "${Gray}------------------------${Color_Off}"
  echo -e "${Gray}Fecha UTC:        $DATE_HOUR${Color_Off}"
  echo -e "${Gray}Fecha UTC-5 (PE): $DATE_HOUR_PE${Color_Off}"
  echo -e "${Gray}========================${Color_Off}"
  echo -e ""
  echo -e "${Gray}Seleccione una opción:${Color_Off}"
  echo -e "${Gray}1) Opciones Generales${Color_Off}"
  echo -e "${Gray}2) Navegacion${Color_Off}"
  echo -e "${Gray}3) Docker${Color_Off}"
  echo -e "${Gray}4) Docker Comandos${Color_Off}"
  echo -e "${Gray}5) CyberPanel${Color_Off}"
  echo -e "${Gray}6) FZF${Color_Off}"
  echo -e "${Gray}7) Script Python${Color_Off}"
  echo -e "${Gray}8) Ficheros de configuración${Color_Off}"
  echo -e "${Gray}9) Salir${Color_Off}"
  read -p "Seleccione una opción (Enter para salir): " opt
  case $opt in
    1) submenu_generales ;;
    2) menu_search ;; # esto es del fichero ./libs_shell/gitbash/func_navegacion.sh
    3) submenu_docker ;;
    4) submenu_docker_comandos ;;
    5) submenu_cyberpanel ;;
    6) submenu_fzf ;;
    7) submenu_python_utils ;;
    8) submenu_ficheros_configuracion ;;
    9) return ;;
    "") return ;;  # Si se presiona Enter sin escribir nada, salir
  *) echo -e "${Red}Opción inválida${Color_Off}" ; menu ;;
  esac
}

submenu_generales(){
  cls
  echo -e "${Yellow}Submenú Opciones disponibles:${Color_Off}"
  echo -e "${Gray}   - create_file : ${Cyan}Crear un fichero de manera manual${Color_Off}"
  echo -e "${Gray}   - listar_archivos_recientes_modificados : ${Cyan} ficheros recientes y modificados  Ejemplo: listar_archivos_recientes_modificados '/var/www/html' 15${Color_Off}"
  echo -e "${Gray}   - generar_ssh : ${Cyan}Generar claves SSH. Ejemplo: generar_ssh usuario@dominio.com${Color_Off}"
  echo -e "${Gray}   - comparar : ${Cyan}Comparar dos archivos. Ejemplo: comparar archivo1.txt archivo2.txt${Color_Off}"
  echo -e "${Gray}   - search_text : ${Cyan}Buscar texto en múltiples archivos del directorio actual. Ejemplo: search_text 'texto_a_buscar'${Color_Off}"
  echo -e "${Gray}   - directory_space : ${Cyan}Ver peso de sus directorios pasar el path opcional . Ejemplo: directory_space '/var/www'${Color_Off}"
  echo -e "${Gray}   - find_files_by_size : ${Cyan}Archivos por tamaño. Ejemplo: find_files_by_size . 5M${Color_Off}"
  echo -e "${Gray}   - find_heaviest_files : ${Cyan}Listar los archivos más pesados en un directorio. Ejemplo: find_heaviest_files /ruta/al/directorio 10${Color_Off}"
  echo -e "${Gray}   - simple_server : ${Cyan}Iniciar un servidor HTTP simple en el puerto especificado (por defecto 8000). Ejemplo: simple_server 8080${Color_Off}"
  echo -e "${Gray}Utilidades Red:${Color_Off}"
  echo -e "${Gray}   - Obtener Ip Publica : ${Cyan}curl checkip.amazonaws.com${Color_Off}"
  echo -e "${Gray}Alias básicos disponibles:${Color_Off}"
  echo -e "${Gray}   - ll : ${Cyan}Lista archivos con tamaños legibles (ls -lh).${Color_Off}"
  echo -e "${Gray}   - la : ${Cyan}Lista todos los archivos, incluidos ocultos (ls -lha).${Color_Off}"
  echo -e "${Gray}   - rm : ${Cyan}Borrar archivos con confirmación.${Color_Off}"
  echo -e "${Gray}   - cp : ${Cyan}Copiar archivos con confirmación.${Color_Off}"
  echo -e "${Gray}   - mm : ${Cyan}Efecto Hacker${Color_Off}"
  echo -e "${Gray}   - mv : ${Cyan}Mover archivos con confirmación.${Color_Off}"
  echo -e "${Gray}   - cls : ${Cyan}Limpiar la pantalla.${Color_Off}"
  echo -e "${Gray}Alias avanzados disponibles:${Color_Off}"
  echo -e "${Gray}   - search : ${Cyan}Buscar archivos por nombre. Ejemplo: search '*.log'${Color_Off}"
  echo -e "${Gray}   - bigfiles : ${Cyan}Mostrar los 10 archivos más grandes en el directorio actual.${Color_Off}"
  echo -e "${Gray}   - newestfile : ${Cyan}Mostrar el archivo más reciente del directorio actual.${Color_Off}"
  echo -e "${Gray}Configuraciones adicionales:${Color_Off}"
  echo -e "${Gray}   - ulimit -n 4096 : ${Cyan}Incrementa el límite de archivos abiertos.${Color_Off}"
  echo -e "${Gray}   - history : ${Cyan}Historial extendido con fecha y hora.${Color_Off}"
  echo -e "${Gray}   - PATH : ${Cyan}Incluye scripts personalizados en /opt/mis-scripts.${Color_Off}"
}

submenu_docker(){
  cls
  echo -e "${Yellow}Submenú Docker:${Color_Off}"
  echo -e "${Gray}   - d : ${Cyan}docker${Color_Off}"
  echo -e "${Gray}   - dps : ${Cyan}docker ps${Color_Off}"
  echo -e "${Gray}   - di : ${Cyan}docker images${Color_Off}"
  echo -e "${Gray}   - drm : ${Cyan}docker rm -f${Color_Off}"
  echo -e "${Gray}   - drmi : ${Cyan}docker rmi${Color_Off}"
  echo -e "${Gray}   - dlog : ${Cyan}docker logs -f${Color_Off}"
  echo ""
  echo -e "${Gray}   - dc : ${Cyan}docker-compose ${Color_Off}"
  echo -e "${Gray}   - dcu : ${Cyan}docker-compose up -d ${Color_Off}"
  echo -e "${Gray}   - dcd : ${Cyan}docker-compose down ${Color_Off}"
  echo -e "${Gray}   - dcb : ${Cyan}docker-compose build ${Color_Off}"
  echo -e "${Gray}   - dcr : ${Cyan}docker-compose restart ${Color_Off}"
  echo ""
  echo -e "${Gray}   - dinspect : ${Cyan}Inspecionar contenedor - Uso: dinspect <nombre_contenedor> ${Color_Off}"
  echo -e "${Gray}   - dlogin : ${Cyan}Listar e Ingresar a contenedor - Uso: dit ${Color_Off}"
  echo -e "${Gray}   - droot : ${Cyan}Listar e Ingresar a contenedor MODO : ROOT- Uso: dit ${Color_Off}"
  echo -e "${Gray}   - dcrestart : ${Cyan}docker-compose down && docker-compose up -d ${Color_Off}"
}

submenu_docker_comandos(){
  cls
  curl -sSL https://raw.githubusercontent.com/cesar23/utils_dev/master/binarios/linux/util/docker_info.sh | bash

}

submenu_cyberpanel(){
  cls
  echo -e "${Yellow}Submenú Configuraciones CyberPanel:${Color_Off}"
  echo -e "${Gray}   - stop_cyber_panel : ${Cyan}Detener CyberPanel.${Color_Off}"
  echo -e "${Gray}   - start_cyber_panel : ${Cyan}Iniciar CyberPanel.${Color_Off}"
}

submenu_fzf(){
  cls
  echo -e "${Yellow}Submenú FZF:${Color_Off}"
  echo -e "${Gray}   - sd : ${Cyan}Buscar y cambiar de directorio.${Color_Off}"
  echo -e "${Gray}   - sde : ${Cyan}Navegación estilo explorador de Windows.${Color_Off}"
  echo -e "${Gray}   - sf : ${Cyan}Buscar archivos excluyendo carpetas y tipos de archivos.${Color_Off}"
  echo -e "${Gray}   - sff : ${Cyan}Buscar archivos sin exclusiones.${Color_Off}"
}

submenu_python_utils(){
  cls
  echo -e "${Yellow}Submenú Comandos Python:${Color_Off}"
  echo -e "${Gray}   - run_server_py : ${Cyan}Crea un servidor de explorador de ficheros.${Color_Off}"
  echo -e "${Gray}        ${Yellow}Ejemplos de uso:${Color_Off}"
  echo -e "${Gray}            ${Purple}run_server_py  :${Cyan}directorio actual.${Color_Off}"
  echo -e "${Gray}            ${Purple}run_server_py 9090 : ${Cyan}directorio actual y con puerto 9090.${Color_Off}"
  echo -e "${Gray}            ${Purple}run_server_py 9090 /d/repos : ${Cyan}puerto y directorio pasado por parametro.${Color_Off}"
  echo -e "${Gray}   - optimize_img_dir : ${Cyan} ${Yellow}(solo Linux)${Color_Off} ${Cyan}Comprime Recursivamente imagenes tipo (jpg,png) en el directorio actual o pasandole un path ejemplo: optimize_img_dir '/mnt/e/imgs'  ${Color_Off}"

}

submenu_ficheros_configuracion(){
  cls
  echo -e "${Yellow}Submenú Ficheros Configuracion:${Color_Off}"
  echo -e "${Code_background} ~/.bashrc ${Color_Off}${Cyan} → Configuración del shell interactivo (para Bash).${Color_Off}"
  echo -e "${Code_background} /etc/network/interfaces ${Cyan} → Configuración de la red (Debian/Ubuntu).${Color_Off}"
  echo -e "${Code_background} /etc/sysconfig/network-scripts/ifcfg-eth0 ${Cyan} → Configuración de la red (RHEL/CentOS)..${Color_Off}"
  echo -e "${Code_background} /etc/resolv.conf ${Cyan} → Configuración de servidores DNS..${Color_Off}"
  echo -e "${Code_background} /etc/hosts.allow y /etc/hosts.deny ${Cyan} → Control de acceso a servicios..${Color_Off}"
  echo -e "${Code_background} /etc/nsswitch.conf ${Cyan} → Orden de búsqueda de nombres de host..${Color_Off}"
  echo -e "${Code_background} /etc/hostname ${Cyan} → Nombre del host del sistema..${Color_Off}"
  echo -e "${Code_background} /etc/iptables/rules.v4 y /etc/iptables/rules.v6 ${Cyan} → Configuración de reglas de firewall (si usa iptables)..${Color_Off}"

}

scriptPath2=${0%/*}
CURRENT_USER=$(id -un)
CURRENT_PC_NAME=$(exec /usr/bin/hostname)
INFO_PC="${CURRENT_USER}@${CURRENT_PC_NAME}"

# :::::::: Importanmos las librerias
if [ -f "${HOME}/libs_shell/init.sh" ]; then
 source "${HOME}/libs_shell/init.sh"
fi

# ================== Aliases ==================
# Alias para usar 'batcat' como 'bat' en lugar de 'batcat'
alias bat="batcat"

# ------------------------- Funciones útiles -------------------------

# sd - Función para buscar y cambiar directorios recursivamente usando fzf
# Ejemplo de uso:
#   sd         # Busca directorios en el directorio actual y navega entre ellos.
#   sd /path   # Busca directorios dentro de /path.
function sd() {
  local dir
  # Busca directorios en el directorio actual o en el especificado, luego usa fzf para seleccionar uno.
  dir=$(find "${1:-.}" -type d 2> /dev/null | fzf +m) && cd "$dir"
}

# sde - Función que permite navegar entre directorios como un explorador de Windows
# Ejemplo de uso:
#   sde       # Navega entre directorios, incluyendo opción para retroceder con ".."
function sde() {
  # Configuración de fzf
  export FZF_DEFAULT_OPTS="--height 40% --layout=reverse --border"
  bind '"\C-r": " \C-a\C-k\C-r\C-y\ey\C-m"'

  while true; do
    # Usa un array para manejar correctamente directorios con espacios
    dirs=("..")

    # Añade los directorios a la lista
    while IFS= read -r -d \0' dir; do
        dirs+=("$dir")
    done < <(find . -maxdepth 1 -type d -print0)

    # Usa fzf para seleccionar un directorio, manejando correctamente los nombres con espacios
    dir=$(printf "%s\n" "${dirs[@]}" | fzf --header "Selecciona un directorio ('..' para retroceder)")

    if [[ -n "$dir" ]]; then
      if [[ "$dir" == ".." ]]; then
        cd ..
      else
        cd "$dir"
      fi
     echo -e "${Gray}Estás en: $(pwd)" # Muestra la ruta actual
    else
      break  # Sale del bucle si no se selecciona nada
    fi
  done

}

# sf - Función para buscar archivos excluyendo carpetas y tipos de ficheros específicos
# Ejemplo de uso:
#   sf        # Busca archivos excluyendo ficheros no deseados y los abre en nvim
function sf() {
  export FZF_DEFAULT_OPTS="--height 100% --layout=reverse --border"
  bind '"\C-r": " \C-a\C-k\C-r\C-y\ey\C-m"'

  # Verificar si fzf está instalado
  if which fzf > /dev/null; then
    # Encuentra archivos, excluyendo carpetas y tipos de ficheros no deseados
         # Verificamos si existe bactcat
     if command -v batcat &> /dev/null; then
         # Busca todos los archivos sin restricciones
        find . -type d \( -iname '$RECYCLE.BIN' -o \
                      -iname '.git' -o \
                      -iname 'node_modules' -o \
                      -iname 'dist' \) -prune -o -type f \( -not -iname '*.dll' -a \
                                                            -not -iname '*.exe' \) -print \
            | fzf --preview 'batcat --style=numbers --color=always --line-range :500 {}' \
            | xargs -r nvim  # Abre el archivo seleccionado en nvim
     else
         find . -type d \( -iname '$RECYCLE.BIN' -o \
                      -iname '.git' -o \
                      -iname 'node_modules' -o \
                      -iname 'dist' \) -prune -o -type f \( -not -iname '*.dll' -a \
                                                            -not -iname '*.exe' \) -print \
            | fzf --preview 'cat {}' \
            | xargs -r nvim  # Abre el archivo seleccionado en nvim
     fi


  else
    echo "fzf no está instalado."
  fi
}

# sff - Función para buscar archivos sin omitir ningún fichero o carpeta
# Ejemplo de uso:
#   sff       # Busca cualquier archivo en el directorio actual y lo abre en nvim
function sff() {
  export FZF_DEFAULT_OPTS="--height 100% --layout=reverse --border"
  bind '"\C-r": " \C-a\C-k\C-r\C-y\ey\C-m"'

  # Verificar si fzf está instalado
  if which fzf > /dev/null; then

     # Verificamos si existe bactcat
     if command -v batcat &> /dev/null; then
         # Busca todos los archivos sin restricciones
        find . -print \
        | fzf --preview 'batcat --style=numbers --color=always --line-range :500 {}' \
        | xargs -r nvim  # Abre el archivo seleccionado en nvim
     else
         find . -print \
        | fzf --preview 'cat {}' \
        | xargs -r nvim  # Abre el archivo seleccionado en nvim
     fi



  else
    echo "fzf no está instalado."
  fi
}

# ================================================
# ====================== docker ==================
# ================================================
# Alias básicos para Docker
alias d="docker"              # Abreviatura para Docker
alias dps="docker ps"         # Mostrar contenedores en ejecución
alias di="docker images"      # Listar imágenes
alias drm="docker rm -f"      # Eliminar contenedor forzadamente
alias drmi="docker rmi"       # Eliminar imagen
alias dlog="docker logs -f"   # Ver logs en tiempo real

# Alias básicos para Docker Compose
alias dc="docker-compose"     # Abreviatura para Docker Compose
alias dcu="docker-compose up -d"   # Iniciar servicios en segundo plano
alias dcd="docker-compose down"    # Detener y eliminar servicios
alias dcb="docker-compose build"   # Construir servicios
alias dcr="docker-compose restart" # Reiniciar servicios

# ----------- Funciones

dinspect() {
    if [ -z "$1" ]; then
        echo "Uso: dinspect <nombre_contenedor>"
    else
        docker inspect "$1"
    fi
}

# Función para listar contenedores en ejecución
listar_contenedores() {
    echo -e "${Cyan}Contenedores en ejecución:${Color_Off}"
    docker ps --format "table {{.ID}}\t{{.Names}}\t{{.Status}}"
}
    # Función para entrar al contenedor
entrar_contenedor() {
    local CONTAINER=$1

    # Intenta usar bash, si no existe, usa sh (/bin/bash  o bash)
    echo -e "\${Green}Entrando al contenedor '\${Yellow}\$CONTAINER\${Green}'...\${Color_Off}"
    echo -e "\${Gray}docker exec -it \"\$CONTAINER\" bash \${Color_Off}"
    if docker exec -it "$CONTAINER" bash 2>/dev/null; then
        return 0
    else
        echo -e "\${Yellow}bash no está disponible en el contenedor, intentando con sh...\${Color_Off}"
        docker exec -it "$CONTAINER" /bin/sh
        return $?
    fi
}

dlogin(){
  cls 2>/dev/null || clear

    # Flujo principal del script
    listar_contenedores

    echo -e "\${Yellow}"
    read -p "Ingrese el nombre o ID del contenedor: " CONTAINER
    echo -e "\${Color_Off}"

    # Validar entrada del usuario
    if [ -z "$CONTAINER" ]; then
        echo -e "\${Red}Error: No se ingresó un nombre o ID de contenedor.\${Color_Off}"
        return 1
    fi

    # Intentar entrar al contenedor
    entrar_contenedor "$CONTAINER"
    RET=$?

    # Mensaje final según el resultado
    if [ $RET -eq 0 ]; then
        echo -e "\${Green}Sesión del contenedor finalizada correctamente.\${Color_Off}"
    else
        echo -e "\${Red}Hubo un problema al intentar acceder al contenedor.\${Color_Off}"
    fi


}
droot() {
    listar_contenedores
    echo -e "\${Yellow}"
    read -p "Ingrese el nombre o ID del contenedor: " CONTAINER
    echo -e "\${Color_Off}"

    docker exec -it --user root "$CONTAINER" bash

}

dcrestart() {
    docker-compose down && docker-compose up -d
}
# ==========================================================================
# END ~/.bashrc - Configuración de Bash por César
# ==========================================================================

COMPLETE_BASHRC_EOF

    # Debug final del heredoc
    echo "=== FIN HEREDOC ===" >> ~/.bashrc.debug.log 2>/dev/null || true
    echo "Configuración agregada exitosamente" >> ~/.bashrc.debug.log 2>/dev/null || true

BASHRC_CONFIG_EOF
    else
        msg "🔒 Modo normal: ejecutando con sudo -u $target_user" "DEBUG"
        sudo -u "$target_user" bash << 'BASHRC_CONFIG_EOF'
# Debug interno del heredoc
echo "=== INICIO HEREDOC ===" >> ~/.bashrc.debug.log 2>/dev/null || true
echo "Usuario dentro del heredoc: $(whoami)" >> ~/.bashrc.debug.log 2>/dev/null || true
echo "HOME dentro del heredoc: $HOME" >> ~/.bashrc.debug.log 2>/dev/null || true
echo "Fecha: $(date)" >> ~/.bashrc.debug.log 2>/dev/null || true

# Agregar la configuración completa de .bashrc
cat >> ~/.bashrc << 'COMPLETE_BASHRC_EOF'

VERSION_BASHRC=3.0.0
VERSION_PLATFORM='linux-gitbash'

# Sistema de prompt con Git y SSH Detection
if [ -n "$SSH_CLIENT" ] || [ -n "$SSH_TTY" ]; then
    SESSION_TYPE="ssh"
else
    case $(ps -o comm= -p $PPID) in
        sshd|*/sshd) SESSION_TYPE="ssh";;
    esac
fi

if [ "$SESSION_TYPE" = "ssh" ]; then
    COLOR_USER='\[\033[01;31m\]'
    COLOR_HOST='\[\033[01;31m\]'
    SSH_INDICATOR=' (SSH)'
else
    COLOR_USER='\[\033[01;32m\]'
    COLOR_HOST='\[\033[01;32m\]'
    SSH_INDICATOR=''
fi

COLOR_PATH='\[\033[01;34m\]'
COLOR_GIT='\[\033[01;33m\]'
COLOR_RESET='\[\033[00m\]'

parse_git_branch() {
    git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/(\1)/'
}

if command -v git >/dev/null 2>&1; then
    PS1="${COLOR_USER}\u${COLOR_RESET}@${COLOR_HOST}\h${COLOR_RESET}${SSH_INDICATOR}:${COLOR_PATH}\w${COLOR_RESET} ${COLOR_GIT}\$(parse_git_branch)${COLOR_RESET}\$ "
else
    PS1="${COLOR_USER}\u${COLOR_RESET}@${COLOR_HOST}\h${COLOR_RESET}${SSH_INDICATOR}:${COLOR_PATH}\w${COLOR_RESET}\$ "
fi

# Configuración de historia
HISTCONTROL=ignoredups:ignorespace
HISTSIZE=5000
HISTFILESIZE=10000
shopt -s histappend
shopt -s checkwinsize

# Autocompletado mejorado
if ! shopt -oq posix; then
    if [ -f /usr/share/bash-completion/bash_completion ]; then
        . /usr/share/bash-completion/bash_completion
    elif [ -f /etc/bash_completion ]; then
        . /etc/bash_completion
    fi
fi

# Colores para ls
if [ -x /usr/bin/dircolors ]; then
    test -r ~/.dircolors && eval "$(dircolors -b ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
    alias grep='grep --color=auto'
    alias fgrep='fgrep --color=auto'
    alias egrep='egrep --color=auto'
fi

# Aliases básicos y avanzados
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'
alias ..='cd ..'
alias ...='cd ../..'
alias ....='cd ../../..'
alias h='history'
alias c='clear'
alias df='df -h'
alias du='du -h'
alias free='free -h'
alias grep='grep --color=auto'
alias less='less -R'
alias nano='nano -T4'
alias reload='source ~/.bashrc'

# Aliases de Git
alias gs='git status'
alias gst='git status'
alias ga='git add'
alias gaa='git add .'
alias gc='git commit'
alias gcm='git commit -m'
alias gca='git commit -a'
alias gcam='git commit -am'
alias gp='git push'
alias gpl='git pull'
alias gl='git log --oneline'
alias glo='git log --oneline --graph --decorate'
alias gb='git branch'
alias gco='git checkout'
alias gd='git diff'
alias gds='git diff --staged'
alias gr='git remote -v'
alias gf='git fetch'

# Aliases de Docker
alias dps='docker ps'
alias dpsa='docker ps -a'
alias di='docker images'
alias drmf='docker rm -f'
alias drmi='docker rmi'
alias dex='docker exec -it'
alias dlogs='docker logs -f'
alias dcp='docker-compose'
alias dcup='docker-compose up'
alias dcupd='docker-compose up -d'
alias dcdown='docker-compose down'
alias dcbuild='docker-compose build'
alias dcrestart='docker-compose restart'
alias dclogs='docker-compose logs -f'

# Sistema y red
alias ports='netstat -tulanp'
alias listening='netstat -tlnp'
alias meminfo='free -h && echo && ps aux --sort=-%mem | head'
alias cpuinfo='lscpu'
alias diskusage='df -h'
alias biggest='du -sh * | sort -hr | head -10'
alias myip='curl -s http://whatismyip.akamai.com/'
alias localip="ip route get 1 | awk '{print \$NF;exit}'"

# Funciones útiles
extract() {
    if [ -f $1 ]; then
        case $1 in
            *.tar.bz2)   tar xjf $1     ;;
            *.tar.gz)    tar xzf $1     ;;
            *.bz2)       bunzip2 $1     ;;
            *.rar)       unrar e $1     ;;
            *.gz)        gunzip $1      ;;
            *.tar)       tar xf $1      ;;
            *.tbz2)      tar xjf $1     ;;
            *.tgz)       tar xzf $1     ;;
            *.zip)       unzip $1       ;;
            *.Z)         uncompress $1  ;;
            *.7z)        7z x $1        ;;
            *)           echo "'$1' no puede ser extraído por extract()" ;;
        esac
    else
        echo "'$1' no es un archivo válido"
    fi
}

mkcd() {
    mkdir -p "$1" && cd "$1"
}

backup() {
    cp "$1"{,.backup-$(date +%Y%m%d-%H%M%S)}
}

weather() {
    curl -s "wttr.in/$1"
}

# Función de búsqueda avanzada
search() {
    if [ $# -eq 0 ]; then
        echo "Uso: search <patrón> [directorio]"
        return 1
    fi
    local pattern="$1"
    local directory="${2:-.}"
    find "$directory" -type f -name "*$pattern*" 2>/dev/null
}

searchin() {
    if [ $# -lt 1 ]; then
        echo "Uso: searchin <patrón> [directorio]"
        return 1
    fi
    local pattern="$1"
    local directory="${2:-.}"
    grep -r "$pattern" "$directory" 2>/dev/null
}

# Docker helpers
drun() {
    if [ $# -eq 0 ]; then
        echo "Uso: drun <imagen> [comando]"
        return 1
    fi
    docker run -it --rm "$@"
}

denter() {
    if [ $# -eq 0 ]; then
        echo "Uso: denter <contenedor>"
        return 1
    fi
    docker exec -it "$1" /bin/bash 2>/dev/null || docker exec -it "$1" /bin/sh
}

dclean() {
    echo "Limpiando contenedores parados..."
    docker container prune -f
    echo "Limpiando imágenes sin usar..."
    docker image prune -f
    echo "Limpiando volúmenes sin usar..."
    docker volume prune -f
    echo "Limpiando redes sin usar..."
    docker network prune -f
}

# Navegación con FZF (si está instalado)
if command -v fzf >/dev/null 2>&1; then
    # Búsqueda de archivos con preview
    ff() {
        local file
        file=$(find . -type f 2>/dev/null | fzf --preview 'head -50 {}' --preview-window=right:50%:wrap)
        [ -n "$file" ] && "${EDITOR:-nano}" "$file"
    }

    # Navegación de directorios
    fd() {
        local dir
        dir=$(find . -type d 2>/dev/null | fzf)
        [ -n "$dir" ] && cd "$dir"
    }

    # Búsqueda en historial
    fh() {
        eval $(history | fzf --tac | sed 's/^ *[0-9]* *//')
    }

    # Kill procesos
    fkill() {
        local pids=$(ps -ef | sed 1d | fzf -m | awk '{print $2}')
        [ -n "$pids" ] && echo "$pids" | xargs kill -${1:-9}
    }
fi

# Auto-instalación de paquetes útiles (solo si no están instalados)
install_if_missing() {
    for package in "$@"; do
        if ! command -v "$package" >/dev/null 2>&1; then
            echo "Instalando $package..."
            if command -v apt >/dev/null 2>&1; then
                sudo apt update && sudo apt install -y "$package"
            elif command -v yum >/dev/null 2>&1; then
                sudo yum install -y "$package"
            elif command -v dnf >/dev/null 2>&1; then
                sudo dnf install -y "$package"
            elif command -v pacman >/dev/null 2>&1; then
                sudo pacman -S --noconfirm "$package"
            fi
        fi
    done
}

# Auto-detectar e instalar herramientas útiles
if [ ! -f ~/.tools_checked ]; then
    echo "🔧 Verificando herramientas útiles..."
    install_if_missing curl wget git nano vim htop tree ncdu
    touch ~/.tools_checked
fi

# Función de ayuda personalizada
helpme() {
    cat << 'HELP_EOF'
🚀 BASHRC PERSONALIZADO v3.0.0

📁 NAVEGACIÓN:
  ll, la, l       - Listados mejorados
  .., ..., ....   - Subir directorios
  mkcd <dir>      - Crear y entrar en directorio
  fd              - Navegación con FZF (si disponible)

🔍 BÚSQUEDA:
  search <patrón> [dir]    - Buscar archivos por nombre
  searchin <patrón> [dir]  - Buscar contenido en archivos
  ff                       - Buscar archivos con FZF
  fh                       - Buscar en historial con FZF

🐳 DOCKER:
  dps, dpsa       - Ver contenedores
  di              - Ver imágenes
  drun <img>      - Ejecutar contenedor
  denter <cont>   - Entrar en contenedor
  dclean          - Limpiar Docker

📊 SISTEMA:
  ports           - Ver puertos abiertos
  meminfo         - Información de memoria
  biggest         - Archivos más grandes
  myip, localip   - Ver IPs

🛠 UTILIDADES:
  extract <file>  - Extraer cualquier archivo
  backup <file>   - Backup con timestamp
  weather [city]  - Ver clima
  helpme          - Esta ayuda
  reload          - Recargar bashrc

🎨 GIT (shortcuts):
  gs/gst, ga, gaa, gc, gcm, gp, gpl, gl, gco, gd...

HELP_EOF
}

# Menú interactivo
menu() {
    echo "🚀 MENÚ BASHRC v$VERSION_BASHRC"
    echo "1) Ver información del sistema"
    echo "2) Procesos y memoria"
    echo "3) Espacio en disco"
    echo "4) Red y conectividad"
    echo "5) Docker (si está disponible)"
    echo "6) Git status (si estás en repo)"
    echo "7) Ayuda de comandos"
    echo "0) Salir"

    read -p "Selecciona una opción: " option

    case $option in
        1)
            echo "=== INFORMACIÓN DEL SISTEMA ==="
            uname -a
            echo
            lsb_release -a 2>/dev/null || cat /etc/os-release
            echo
            uptime
            ;;
        2)
            echo "=== PROCESOS Y MEMORIA ==="
            echo "Top 10 procesos por CPU:"
            ps aux --sort=-%cpu | head -11
            echo
            echo "Memoria:"
            free -h
            ;;
        3)
            echo "=== ESPACIO EN DISCO ==="
            df -h
            echo
            echo "Top 10 directorios más grandes:"
            du -sh * 2>/dev/null | sort -hr | head -10
            ;;
        4)
            echo "=== RED Y CONECTIVIDAD ==="
            echo "IP local: $(ip route get 1 | awk '{print $NF;exit}' 2>/dev/null || echo 'No disponible')"
            echo "IP pública: $(curl -s http://whatismyip.akamai.com/ 2>/dev/null || echo 'No disponible')"
            echo
            echo "Puertos abiertos:"
            netstat -tlnp 2>/dev/null | head -10
            ;;
        5)
            if command -v docker >/dev/null 2>&1; then
                echo "=== DOCKER ==="
                echo "Contenedores:"
                docker ps -a
                echo
                echo "Imágenes:"
                docker images
            else
                echo "Docker no está instalado"
            fi
            ;;
        6)
            if git rev-parse --git-dir > /dev/null 2>&1; then
                echo "=== GIT STATUS ==="
                git status
                echo
                echo "Últimos commits:"
                git log --oneline -10
            else
                echo "No estás en un repositorio Git"
            fi
            ;;
        7)
            helpme
            ;;
        0)
            echo "¡Hasta luego!"
            ;;
        *)
            echo "Opción no válida"
            ;;
    esac
}

# Mensaje de bienvenida
if [ "$VERSION_BASHRC" = "3.0.0" ]; then
    echo "✅ Bashrc personalizado v$VERSION_BASHRC cargado $VERSION_PLATFORM"
    echo "💡 Escribe 'helpme' para ver comandos disponibles o 'menu' para el menú interactivo"
fi

COMPLETE_BASHRC_EOF

# Debug final del heredoc
echo "=== FIN HEREDOC ===" >> ~/.bashrc.debug.log 2>/dev/null || true
echo "Configuración agregada exitosamente" >> ~/.bashrc.debug.log 2>/dev/null || true

BASHRC_CONFIG_EOF
    fi

    # Cambiar propietario del archivo
    chown "$target_user:$target_user" "$user_home/.bashrc" 2>/dev/null || true

    # Debug final - verificar qué se escribió
    {
        echo "=== POST-CONFIGURACIÓN ==="
        echo "Tamaño final de .bashrc: $(wc -l "$user_home/.bashrc" 2>/dev/null || echo 'ERROR_LEYENDO')"
        echo "Últimas 5 líneas de .bashrc:"
        tail -n 5 "$user_home/.bashrc" 2>/dev/null || echo "ERROR: No se pudo leer .bashrc"
        echo "Archivos debug generados en $user_home:"
        ls -la "$user_home"/.bashrc*.log 2>/dev/null || echo "No hay archivos debug"
        echo "=========================="
    } >> "$debug_log"

    msg "📁 Debug completo disponible en: $debug_log" "INFO"
    msg "✅ Configuración avanzada del .bashrc completada" "SUCCESS"
    msg "📋 Se agregó configuración completa de .bashrc v3.0.0 (linux, gitbash)" "INFO"
    msg "🎨 Incluye: prompt personalizado, aliases, funciones avanzadas, Docker, FZF, menús interactivos" "INFO"
}

# Testing de bashrc si está activado
if [[ "$TEST_BASHRC_EARLY_EXIT" == "true" ]]; then
    msg "🔧 Ejecutando solo configuración de bashrc..." "INFO"
    configure_advanced_bashrc
    msg "✅ Testing de bashrc completado" "SUCCESS"
    exit 0
fi

# 4. VERIFICAR VERSIONES DE HERRAMIENTAS INSTALADAS
msg "Verificando versiones de herramientas instaladas..." "INFO"
echo -e "\n${BBlue}=== VERSIONES INSTALADAS ===${Color_Off}"

check_version() {
    local cmd=$1
    local name=$2
    if command -v "$cmd" &> /dev/null; then
        local version=$($cmd --version 2>/dev/null | head -n1 | sed 's/.*version //g' | sed 's/ .*//g')
        echo -e "${BGreen}✓${Color_Off} ${BWhite}$name${Color_Off}: $version"
    else
        echo -e "${BRed}✗${Color_Off} ${BWhite}$name${Color_Off}: No instalado"
    fi
}

check_version "git" "Git"
check_version "nvim" "Neovim"
check_version "curl" "Curl"
check_version "wget" "Wget"
check_version "vim" "Vim"
check_version "htop" "Htop"
check_version "tmux" "Tmux"
check_version "screen" "Screen"

# 5. INFORMACIÓN DEL SISTEMA
msg "Mostrando información del sistema actualizado..." "INFO"
echo -e "\n${BBlue}=== INFORMACIÓN DEL SISTEMA ===${Color_Off}"
echo -e "${BWhite}Distribución:${Color_Off} $(lsb_release -d | cut -f2)"
echo -e "${BWhite}Kernel:${Color_Off} $(uname -r)"
echo -e "${BWhite}Arquitectura:${Color_Off} $(uname -m)"
echo -e "${BWhite}Usuario ejecutor:${Color_Off} $MY_INFO"
echo -e "${BWhite}Fecha actualización:${Color_Off} $DATE_HOUR_PE"

# 6. LIMPIEZA DEL SISTEMA
msg "Realizando limpieza del sistema..." "INFO"
INITIAL_SIZE=$(df / | tail -1 | awk '{print $3}')

if apt autoremove -y > /dev/null 2>&1; then
    msg "Paquetes obsoletos removidos" "SUCCESS"
else
    msg "Error removiendo paquetes obsoletos" "WARNING"
fi

if apt autoclean > /dev/null 2>&1; then
    msg "Cache de paquetes limpiado" "SUCCESS"
else
    msg "Error limpiando cache de paquetes" "WARNING"
fi

FINAL_SIZE=$(df / | tail -1 | awk '{print $3}')
SPACE_FREED=$((INITIAL_SIZE - FINAL_SIZE))

if [ $SPACE_FREED -gt 0 ]; then
    msg "Espacio liberado: ${BGreen}${SPACE_FREED}KB${Color_Off}" "SUCCESS"
fi

# Actualizar base de datos de locate si existe
if command -v updatedb &> /dev/null; then
    msg "Actualizando base de datos de locate..." "INFO"
    updatedb > /dev/null 2>&1
    msg "Base de datos actualizada" "SUCCESS"
fi

# 7. CREAR ALIAS ÚTILES (OPCIONAL)
msg "Configurando aliases útiles..." "INFO"
BASHRC_ALIASES="
# =============================================================================
# 🔧 Aliases útiles añadidos por ${SCRIPT_NAME} - ${DATE_HOUR_PE}
# =============================================================================

# Navegación y listado
alias ll='ls -alF --color=auto'
alias la='ls -A --color=auto'
alias l='ls -CF --color=auto'
alias ..='cd ..'
alias ...='cd ../..'
alias ....='cd ../../..'

# Herramientas con colores
alias grep='grep --color=auto'
alias fgrep='fgrep --color=auto'
alias egrep='egrep --color=auto'

# Comandos útiles
alias h='history'
alias c='clear'
alias df='df -h'
alias du='du -h'
alias free='free -h'
alias ps='ps auxf'
alias top='htop'

# Comandos seguros
alias mkdir='mkdir -pv'
alias mv='mv -i'
alias cp='cp -i'
alias rm='rm -i'

# Aliases para editores
alias vi='nvim'
alias vim='nvim'
alias edit='nvim'

# Aliases para git
alias gs='git status'
alias ga='git add'
alias gc='git commit'
alias gp='git push'
alias gl='git log --oneline'
alias gd='git diff'
alias gb='git branch'

# Información del sistema
alias sysinfo='echo \"Sistema: \$(lsb_release -d | cut -f2)\"; echo \"Kernel: \$(uname -r)\"; echo \"Uptime: \$(uptime -p)\"; echo \"Memoria: \$(free -h | grep Mem | awk \"{print \\\$3\"/\"\\\$2}\")\"; echo \"Disco: \$(df -h / | tail -1 | awk \"{print \\\$3\"/\"\\\$2\" (\"\\\$5\")}\")'\"
alias ports='netstat -tuln'
alias myip='curl -s ifconfig.me'

# =============================================================================
"

# Añadir aliases al bashrc del usuario que ejecutó sudo
if [[ -n "${SUDO_USER:-}" ]]; then
    USER_HOME=$(eval echo "~$SUDO_USER")
    if [[ -f "$USER_HOME/.bashrc" ]]; then
        if ! grep -q "$SCRIPT_NAME" "$USER_HOME/.bashrc"; then
            echo "$BASHRC_ALIASES" >> "$USER_HOME/.bashrc"
            chown "$SUDO_USER:$SUDO_USER" "$USER_HOME/.bashrc"
            msg "Aliases añadidos a $USER_HOME/.bashrc" "SUCCESS"
        else
            msg "Los aliases ya están configurados" "DEBUG"
        fi
    fi
fi

# LLAMAR A LAS FUNCIONES DE CONFIGURACIÓN
msg "Configurando Neovim completamente..." "INFO"
configure_neovim

msg "Configurando .bashrc avanzado..." "INFO"
configure_advanced_bashrc

# 8. RESUMEN FINAL
echo -e "\n${BGreen}╔═══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BGreen}║                     ACTUALIZACIÓN COMPLETADA                 ║${Color_Off}"
echo -e "${BGreen}╚═══════════════════════════════════════════════════════════════╝${Color_Off}"

msg "📋 Resumen de lo realizado:" "SUCCESS"
echo "   ✅ Sistema Ubuntu actualizado completamente"
echo "   ✅ Repositorios oficiales actualizados"
echo "   ✅ Herramientas básicas instaladas:"
echo "      • Git (control de versiones)"
echo "      • Neovim (editor moderno)"
echo "      • Curl/Wget (descarga de archivos)"
echo "      • Htop (monitor de sistema)"
echo "      • Tmux/Screen (multiplexores)"
echo "      • Build-essential (herramientas de compilación)"
echo "   ✅ Neovim configurado completamente con plugins y temas"
echo "   ✅ .bashrc avanzado v3.0.0 configurado con:"
echo "      • Prompt personalizado con Git y SSH"
echo "      • Funciones avanzadas y aliases útiles"
echo "      • Integración Docker y FZF"
echo "      • Menú interactivo completo"
echo "   ✅ Aliases útiles configurados"
echo "   ✅ Sistema limpio y optimizado"

msg "📝 PASOS SIGUIENTES:" "WARNING"
echo "   • Ejecuta 'source ~/.bashrc' para cargar los nuevos aliases"
echo "   • Configura Git con: git config --global user.name 'Tu Nombre'"
echo "   • Configura Git con: git config --global user.email 'tu@email.com'"
echo "   • El editor predeterminado ahora es Neovim (nvim)"
echo "   • Usa 'sysinfo' para ver información rápida del sistema"

# =============================================================================
# 🕐 CALCULAR TIEMPO TOTAL DE EJECUCIÓN
# =============================================================================
END_TIME=$(date +%s)
END_TIME_READABLE=$(date "+%Y-%m-%d %H:%M:%S")
DURATION=$((END_TIME - START_TIME))
HOURS=$((DURATION / 3600))
MINUTES=$(((DURATION % 3600) / 60))
SECONDS=$((DURATION % 60))


# Formatear tiempo de duración
if [ $HOURS -gt 0 ]; then
    DURATION_TEXT="${HOURS}h ${MINUTES}m ${SECONDS}s"
elif [ $MINUTES -gt 0 ]; then
    DURATION_TEXT="${MINUTES}m ${SECONDS}s"
else
    DURATION_TEXT="${SECONDS}s"
fi

msg "🎉 ¡Ubuntu actualizado y listo para usar!" "SUCCESS"
msg "Script ejecutado exitosamente: ${BGreen}${SCRIPT_NAME}${Color_Off}" "SUCCESS"
msg "⏱️  Tiempo de ejecución total: ${BGreen}${DURATION_TEXT}${Color_Off}" "INFO"
msg "🕐 Inicio: ${BWhite}${START_TIME_READABLE}${Color_Off}" "INFO"
msg "🕐 Final: ${BWhite}${END_TIME_READABLE}${Color_Off}" "INFO"

echo -e "\n${BCyan}╔═══════════════════════════════════════════════════════════════╗${Color_Off}"
echo -e "${BCyan}║                   INFORMACIÓN DE CONTACTO                     ║${Color_Off}"
echo -e "${BCyan}╠═══════════════════════════════════════════════════════════════╣${Color_Off}"
echo -e "${BCyan}║              Ingeniero - Cesar Auris                         ║${Color_Off}"
echo -e "${BCyan}║              Teléfono: 937516027                              ║${Color_Off}"
echo -e "${BCyan}║              Website: https://solucionessystem.com            ║${Color_Off}"
echo -e "${BCyan}║                                                               ║${Color_Off}"
echo -e "${BCyan}║        Gracias por usar UTIL TUNE UBUNTU                     ║${Color_Off}"
echo -e "${BCyan}╚═══════════════════════════════════════════════════════════════╝${Color_Off}"

