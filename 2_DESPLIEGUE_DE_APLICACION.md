
```shell
cd /home/cesar
mkdir "docker-stacks" && cd "docker-stacks"

```

## 1. Crear Red 


```shell
# Variables
NETWORK_NAME="proxynet"
SUBNET="172.11.0.0/16"
GATEWAY="172.11.0.1"

docker network create \
    --driver bridge \
    --subnet="$SUBNET" \
    --gateway="$GATEWAY" \
    --opt "com.docker.network.bridge.name=proxynet" \
    --label "description=Red compartida para todos los stacks Docker" \
    "$NETWORK_NAME"
    
docker network inspect "$NETWORK_NAME" --format='{{json .}}' 
```
## 2. modificar ficheros de configuraicon
fichero de configuracion `stack-facturador-smart/smart1/.env` con las configuraciones

```shell
APP_URL_BASE=fact.solucionessystem.com
...
MYSQL_USER=smart1
MYSQL_PASSWORD=dJj3vgAx6Ra4tOjAODp9
MYSQL_DATABASE=smart1
MYSQL_ROOT_PASSWORD=WPsOd4xPLL4nGRnOAHJp
MYSQL_PORT_HOST=3306
```
fichero de configuracion `stack-facturador-smart/smart1/docker-compose.yml` con las configuraciones

```yaml
services:
  nginx1:
    image: rash07/nginx
    working_dir: /var/www/html
    # 🧪 Agregado por cesar
    ports:
      - "8080:80"
    environment: # 👇👇
      VIRTUAL_HOST: fact.solucionessystem.com,*.fact.solucionessystem.com
```

## 3. Correr el stack del facturador
```shell
cd stack-facturador-smart/smart1/

```
luego de eso corer comandos

```shell
# este seria el path: stack-facturador-smart/smart1
PATH_INSTALL=$(echo $PWD)

SERVICE_NUMBER=1
 docker compose up -d
 # ingresar al contenedor
 docker exec -it smart1-fpm1-1 bash
 # Aqui ejecutar los comandos dentro del contenedor
     apt-get update
     rm composer.lock
     composer self-update
     composer install
     php artisan migrate:refresh --seed
     php artisan key:generate
     php artisan storage:link
 # =============================================================
 # Correr en el servidro
 # =============================================================
 sudo chmod -R 777 "$PATH_INSTALL/storage/" "$PATH_INSTALL/bootstrap/" "$PATH_INSTALL/vendor/"
 
 # =============================================================
 # Correr en el Supervisor
 # =============================================================
 # echo "configurando Supervisor"
 # smart1-supervisor1-1
 docker compose exec -T smart1-supervisor1-1 service supervisor start
 docker compose exec -T smart1-supervisor1-1 supervisorctl reread
 docker compose exec -T smart1-supervisor1-1 supervisorctl update
 docker compose exec -T smart1-supervisor1-1 supervisorctl start all
 

```



## 4. Despues del Despliegue de Stak `Utils`
- Levantar utilitarios para phpmyadmin `stack-facturador-smart/utils/docker compose.yml`
- y la confiuracion de `stack-facturador-smart/utils/.env`
```shell
...
MYSQL_USER=smart1
MYSQL_PASSWORD=dJj3vgAx6Ra4tOjAODp9
MYSQL_DATABASE=smart1
MYSQL_ROOT_PASSWORD=WPsOd4xPLL4nGRnOAHJp
MYSQL_PORT_HOST=3306
```
Desplegar aplicacion
```shell
cd stack-facturador-smart/utils
docker compose up -d
```

## 5. Despliegue de Cloudflare new
editar el fichero `stack-facturador-smart/cloudflare/.env`

despues eso desplegar el stack

```shell

cd stack-facturador-smart/cloudflare
docker compose up -d
```

## 6. Despliegue de  ProxyManagger
editar el fichero `stack-facturador-smart/npm/.env`

despues eso desplegar el stack

```shell

cd stack-facturador-smart/npm
docker compose up -d
```
ingresar a la url: http://192.168.0.65:81

