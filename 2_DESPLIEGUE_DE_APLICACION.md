
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
PATH_INSTALL=$(echo $PWD)

SERVICE_NUMBER=1
 docker compose up -d
 # @TODO revisar
 docker compose exec -T fpm$SERVICE_NUMBER apt-get update
 docker compose exec -T fpm$SERVICE_NUMBER apt-get install -y libxml2-dev
 docker compose exec -T fpm$SERVICE_NUMBER docker-php-ext-install soap
 docker compose exec -T fpm$SERVICE_NUMBER apt-get update
 docker compose exec -T fpm$SERVICE_NUMBER apt-get install -y libzip-dev
 docker compose exec -T fpm$SERVICE_NUMBER docker-php-ext-configure zip
 docker compose exec -T fpm$SERVICE_NUMBER docker-php-ext-install zip
 docker compose exec -T fpm$SERVICE_NUMBER rm composer.lock
 docker compose exec -T fpm$SERVICE_NUMBER composer self-update
 docker compose exec -T fpm$SERVICE_NUMBER composer install
 docker compose exec -T fpm$SERVICE_NUMBER php artisan migrate:refresh --seed
 docker compose exec -T fpm$SERVICE_NUMBER php artisan key:generate
 docker compose exec -T fpm$SERVICE_NUMBER php artisan storage:link
 docker compose exec -T fpm$SERVICE_NUMBER git checkout .

 rm $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php
 mv $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php.bk $PATH_INSTALL/$DIR/database/seeders/DatabaseSeeder.php

 echo "configurando permisos"
 chmod -R 777 "$PATH_INSTALL/$DIR/storage/" "$PATH_INSTALL/$DIR/bootstrap/" "$PATH_INSTALL/$DIR/vendor/"

 echo "configurando Supervisor"
 docker compose exec -T supervisor$SERVICE_NUMBER service supervisor start
 docker compose exec -T supervisor$SERVICE_NUMBER supervisorctl reread
 docker compose exec -T supervisor$SERVICE_NUMBER supervisorctl update
 docker compose exec -T supervisor$SERVICE_NUMBER supervisorctl start all
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

## 5. Despliegue de Cloudflare
editar el fichero `stack-facturador-smart/cloudflare/.env`

despues eso desplegar el stack

```shell

cd stack-facturador-smart/cloudflare
docker compose up -d
```


