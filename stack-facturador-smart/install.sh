#!/bin/bash


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



# =============================================================================
# 🔥 SECTION: Main Code
# =============================================================================




#PARAMETROS
HOST=${1:-'dominio'}
SERVICE_NUMBER=${2:-'1'}
MYSQL_PORT_HOST=${3:-'3306'}

#DOMINIO
if [ "$HOST" = "dominio" ]; then
 echo no ha ingresado dominio, vuelva a ejecutar el script agregando un dominio como primer parametro
 exit 1
fi

#PROYECT='https://gitlab.com/facturaperu/smart.git'
PROYECT='https://gitlab.com/perucaos/smart.git'

#RUTA DE INSTALACION (RUTA ACTUAL DEL SCRIPT)
PATH_INSTALL=$(echo $PWD)
#NOMBRE DE CARPETA
DIR=$(echo $PROYECT | rev | cut -d'/' -f1 | rev | cut -d '.' -f1)$SERVICE_NUMBER
#DATOS DE ACCESO MYSQL
MYSQL_USER=$(echo $DIR)
MYSQL_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 20 ; echo '')
MYSQL_DATABASE=$(echo $DIR)
MYSQL_ROOT_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 20 ; echo '')

msg "================================================" "INFO"
msg "Inicio de la instalacion" "INFO"
msg "Ruta de instalacion: $PATH_INSTALL" "INFO"
msg "Nombre de carpeta: $DIR" "INFO"
msg "Usuario de MySQL: $MYSQL_USER" "INFO"
msg "Contraseña de MySQL: $MYSQL_PASSWORD" "INFO"
msg "Base de datos de MySQL: $MYSQL_DATABASE" "INFO"
msg "Contraseña de root de MySQL: $MYSQL_ROOT_PASSWORD" "INFO"
msg "Puerto de MySQL: $MYSQL_PORT_HOST" "INFO"
msg "Dominio: $HOST" "INFO"
msg "Numero de servicio: $SERVICE_NUMBER" "INFO"
msg "================================================" "INFO"
pause_continue "Continuar con la instalacion"

if [ $SERVICE_NUMBER = '1' ]; then
 echo "Actualizando sistema"
 apt-get -y update
 #apt-get -y upgrade

 echo "Instalando git"
 apt-get -y install git-core

 echo "Instalando docker"
 apt-get -y install apt-transport-https ca-certificates curl gnupg-agent software-properties-common
 curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
 add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
 apt-get -y update
 apt-get -y install docker-ce
 systemctl start docker
 systemctl enable docker

 echo "Instalando docker compose"
 curl -L "https://github.com/docker/compose/releases/download/1.23.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
 chmod +x /usr/local/bin/docker-compose

 echo "Instalando letsencrypt"
 apt-get -y install letsencrypt
 mkdir $PATH_INSTALL/certs/

 echo "Configurando proxy"
 docker network create proxynet
 mkdir $PATH_INSTALL/proxy
 cat << EOF > $PATH_INSTALL/proxy/docker-compose.yml
version: '3'

services:
 proxy:
     image: rash07/nginx-proxy:2.0
     ports:
         - "80:80"
         - "443:443"
     volumes:
         - ./../certs:/etc/nginx/certs
         - /var/run/docker.sock:/tmp/docker.sock:ro
     restart: always
     privileged: true
networks:
 default:
     external:
         name: proxynet
EOF

 cd $PATH_INSTALL/proxy
 docker-compose up -d

 mkdir $PATH_INSTALL/proxy/fpms
fi

echo "Configurando $DIR"

if ! [ -d $PATH_INSTALL/proxy/fpms/$DIR ]; then
 echo "Cloning the repository"
 rm -rf "$PATH_INSTALL/$DIR"

msg "================================================" "INFO"
msg "Clonando el repositorio" "INFO"
msg "Ruta del repositorio: $PROYECT" "INFO"
msg "Ruta de instalacion del proyecto: $PATH_INSTALL/$DIR" "INFO"
msg "================================================" "INFO"
pause_continue "Continuar con la instalacion"



 git clone --depth 1 "$PROYECT" "$PATH_INSTALL/$DIR"

 cp $PATH_INSTALL/$DIR/supervisor.conf.example $PATH_INSTALL/$DIR/supervisor.conf

 mkdir $PATH_INSTALL/proxy/fpms/$DIR

 cat << EOF > $PATH_INSTALL/proxy/fpms/$DIR/default
# Configuración de PHP para Nginx
server {
 listen 80 default_server;
 root /var/www/html/public;
 index index.html index.htm index.php;
 server_name *._;
 charset utf-8;
 server_tokens off;
 location = /favicon.ico {
     log_not_found off;
     access_log off;
 }
 location = /robots.txt {
     log_not_found off;
     access_log off;
 }
 location / {
     try_files \$uri \$uri/ /index.php\$is_args\$args;
 }
 location ~ \.php\$ {
     internal;
     include snippets/fastcgi-php.conf;
     fastcgi_pass fpm$SERVICE_NUMBER:9000;
     fastcgi_read_timeout 3600;
 }
 location ~ /storage/.*\.(php|html)$ {
     deny all;
     return 403;
 }
 error_page 404 /index.php;
 location ~ /\.ht {
     deny all;
 }
}
EOF

 cat << EOF > $PATH_INSTALL/$DIR/docker-compose.yml
version: '3'

services:
 nginx$SERVICE_NUMBER:
     image: rash07/nginx
     working_dir: /var/www/html
     environment:
         VIRTUAL_HOST: $HOST, *.$HOST
     volumes:
         - ./:/var/www/html
         - $PATH_INSTALL/proxy/fpms/$DIR:/etc/nginx/sites-available
     restart: always
 fpm$SERVICE_NUMBER:
     image: rash07/php-fpm:7.4
     working_dir: /var/www/html
     volumes:
         - ./ssh:/root/.ssh
         - ./ssh:/var/www/.ssh
         - ./:/var/www/html
     restart: always
 mariadb$SERVICE_NUMBER:
     image: mariadb:10.5.6
     environment:
         - MYSQL_USER=\${MYSQL_USER}
         - MYSQL_PASSWORD=\${MYSQL_PASSWORD}
         - MYSQL_DATABASE=\${MYSQL_DATABASE}
         - MYSQL_ROOT_PASSWORD=\${MYSQL_ROOT_PASSWORD}
         - MYSQL_PORT_HOST=\${MYSQL_PORT_HOST}
     volumes:
         - mysqldata$SERVICE_NUMBER:/var/lib/mysql
     ports:
         - "\${MYSQL_PORT_HOST}:3306"
     restart: always
 redis$SERVICE_NUMBER:
     image: redis:alpine
     volumes:
         - redisdata$SERVICE_NUMBER:/data
     restart: always
 scheduling$SERVICE_NUMBER:
     image: rash07/scheduling
     working_dir: /var/www/html
     volumes:
         - ./:/var/www/html
     restart: always
 supervisor$SERVICE_NUMBER:
     image: rash07/php7.4-supervisor
     working_dir: /var/www/html
     volumes:
         - ./:/var/www/html
         - ./supervisor.conf:/etc/supervisor/conf.d/supervisor.conf
     restart: always

networks:
 default:
     external:
         name: proxynet

volumes:
 redisdata$SERVICE_NUMBER:
     driver: "local"
 mysqldata$SERVICE_NUMBER:
     driver: "local"
EOF

 cp $PATH_INSTALL/$DIR/.env.example $PATH_INSTALL/$DIR/.env

 cat << EOF >> $PATH_INSTALL/$DIR/.env


MYSQL_USER=$MYSQL_USER
MYSQL_PASSWORD=$MYSQL_PASSWORD
MYSQL_DATABASE=$MYSQL_DATABASE
MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD
MYSQL_PORT_HOST=$MYSQL_PORT_HOST
EOF

 echo $MYSQL_USER
 echo $MYSQL_PASSWORD
 echo $MYSQL_DATABASE
 echo $MYSQL_ROOT_PASSWORD
 echo $MYSQL_PORT_HOST
 echo "Configurando env"
 cd "$PATH_INSTALL/$DIR"

 sed -i "/DB_DATABASE=/c\DB_DATABASE=$MYSQL_DATABASE" .env
 sed -i "/DB_PASSWORD=/c\DB_PASSWORD=$MYSQL_ROOT_PASSWORD" .env
 sed -i "/DB_HOST=/c\DB_HOST=mariadb$SERVICE_NUMBER" .env
 sed -i "/DB_USERNAME=/c\DB_USERNAME=root" .env
 sed -i "/APP_URL_BASE=/c\APP_URL_BASE=$HOST" .env
 sed -i '/APP_URL=/c\APP_URL=http://${APP_URL_BASE}' .env
 sed -i '/FORCE_HTTPS=/c\FORCE_HTTPS=false' .env
 sed -i '/APP_DEBUG=/c\APP_DEBUG=false' .env
 sed -i '/QUEUE_CONNECTION=/c\QUEUE_CONNECTION=database' .env

 ADMIN_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 10 ; echo '')
 echo "Configurando archivo para usuario administrador"
 mv "$PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php" "$PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php.bk"
 cat << EOF > $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php
<?php

namespace Database\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\System\Plan;
use App\Models\System\User;

class DatabaseSeeder extends Seeder
{
 /**
  * Seed the application's database.
  *
  * @return void
  */
 public function run()
 {
     User::query()->create([
         'name' => 'Administrador',
         'email' => 'admin@$HOST',
         'password' => bcrypt('$ADMIN_PASSWORD'),
     ]);

     DB::table('plan_documents')->insert([
         ['id' => 1, 'description' => 'Facturas, boletas, notas de débito y crédito, resúmenes y anulaciones' ],
         ['id' => 2, 'description' => 'Guias de remisión' ],
         ['id' => 3, 'description' => 'Retenciones'],
         ['id' => 4, 'description' => 'Percepciones']
     ]);

     Plan::query()->create([
         'name' => 'Ilimitado',
         'pricing' =>  99,
         'limit_users' => 0,
         'limit_documents' =>  0,
         'plan_documents' => [1,2,3,4],
         'locked' => true
     ]);
 }
}

EOF

 echo "Configurando proyecto"
 docker-compose up -d
 docker-compose exec -T fpm$SERVICE_NUMBER apt-get update
 docker-compose exec -T fpm$SERVICE_NUMBER apt-get install -y libxml2-dev
 docker-compose exec -T fpm$SERVICE_NUMBER docker-php-ext-install soap
 docker-compose exec -T fpm$SERVICE_NUMBER apt-get update
 docker-compose exec -T fpm$SERVICE_NUMBER apt-get install -y libzip-dev
 docker-compose exec -T fpm$SERVICE_NUMBER docker-php-ext-configure zip
 docker-compose exec -T fpm$SERVICE_NUMBER docker-php-ext-install zip
 docker-compose exec -T fpm$SERVICE_NUMBER rm composer.lock
 docker-compose exec -T fpm$SERVICE_NUMBER composer self-update
 docker-compose exec -T fpm$SERVICE_NUMBER composer install
 docker-compose exec -T fpm$SERVICE_NUMBER php artisan migrate:refresh --seed
 docker-compose exec -T fpm$SERVICE_NUMBER php artisan key:generate
 docker-compose exec -T fpm$SERVICE_NUMBER php artisan storage:link
 docker-compose exec -T fpm$SERVICE_NUMBER git checkout .

 rm $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php
 mv $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php.bk $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php

 echo "configurando permisos"
 chmod -R 777 "$PATH_INSTALL/$DIR/storage/" "$PATH_INSTALL/$DIR/bootstrap/" "$PATH_INSTALL/$DIR/vendor/"

 echo "configurando Supervisor"
 docker-compose exec -T supervisor$SERVICE_NUMBER service supervisor start
 docker-compose exec -T supervisor$SERVICE_NUMBER supervisorctl reread
 docker-compose exec -T supervisor$SERVICE_NUMBER supervisorctl update
 docker-compose exec -T supervisor$SERVICE_NUMBER supervisorctl start all

 echo "Ruta del proyecto dentro del servidor: $PATH_INSTALL/$DIR"
 echo "----------------------------------------------"
 echo "URL: $HOST"
 echo "Correo para administrador: admin@$HOST"
 echo "Contraseña para administrador: $ADMIN_PASSWORD"
 echo "----------------------------------------------"
 echo "Acceso remoto a Mysql"
 echo "Contraseña para root: $MYSQL_ROOT_PASSWORD"
 echo "Puerto: $MYSQL_PORT_HOST"

 #SSL
 read -p "instalar SSL gratuito? si[s] no[n]: " ssl
 if [ "$ssl" = "s" ]; then

     echo "--IMPORTANTE--"
     echo "--------------"
     echo "Copiar los TXT sin usar [ctrl+c] ya que cancelara el proceso"
     echo "Ingresar correo electronico y aceptar las preguntas"
     echo "--------------"

     certbot certonly --manual -d *.$HOST -d $HOST --agree-tos --no-bootstrap --manual-public-ip-logging-ok --preferred-challenges dns-01 --server https://acme-v02.api.letsencrypt.org/directory

     echo "Configurando certbot"

     if ! [ -f /etc/letsencrypt/live/$HOST/privkey.pem ]; then
         echo "No se ha generado el certificado gratuito"
     else
         sed -i '/APP_URL=/c\APP_URL=https://${APP_URL_BASE}' .env
         sed -i '/FORCE_HTTPS=/c\FORCE_HTTPS=true' .env

         cp /etc/letsencrypt/live/$HOST/privkey.pem $PATH_INSTALL/certs/$HOST.key
         cp /etc/letsencrypt/live/$HOST/fullchain.pem $PATH_INSTALL/certs/$HOST.crt

         docker-compose exec -T fpm$SERVICE_NUMBER php artisan config:cache
         docker-compose exec -T fpm$SERVICE_NUMBER php artisan cache:clear

         docker restart proxy_proxy_1
     fi
 fi

 echo "Ruta del proyecto dentro del servidor: $PATH_INSTALL/$DIR"
 echo "----------------------------------------------"
 echo "URL: $HOST"
 echo "Correo para administrador: admin@$HOST"
 echo "Contraseña para administrador: $ADMIN_PASSWORD"
 echo "----------------------------------------------"
 echo "Acceso remoto a Mysql"
 echo "Contraseña para root: $MYSQL_ROOT_PASSWORD"
 echo "Puerto: $MYSQL_PORT_HOST"

 cat << EOF > $PATH_INSTALL/$DIR.txt
Ruta del proyecto dentro del servidor: $PATH_INSTALL/$DIR
----------------------------------------------
URL: $HOST
Correo para administrador: admin@$HOST
Contraseña para administrador: $ADMIN_PASSWORD
----------------------------------------------
Acceso remoto a Mysql
Contraseña para root: $MYSQL_ROOT_PASSWORD

----------------------------------------------

Rutas de todos los ficheros de configuracion
stack proxy: $PATH_INSTALL/proxy/docker-compose.yml
nginx.conf: $PATH_INSTALL/proxy/fpms/$DIR/default
stack proyecto: $PATH_INSTALL/$DIR/docker-compose.yml
.env: $PATH_INSTALL/$DIR/.env
supervisor.conf: $PATH_INSTALL/$DIR/supervisor.conf
DatabaseSeeder.php: $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php


EOF

else
 echo "La carpeta $PATH_INSTALL/proxy/fpms/$DIR ya existe"
fi

