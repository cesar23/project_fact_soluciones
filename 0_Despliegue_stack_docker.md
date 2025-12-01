
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

## 2. Correr el stack del facturador
```shell
cd smart1
docker compose up -d 
```



## 3. Para `development`

usar el fichero `docker-compose.yml` para llos despliegue. este fichwero tiene que usar la network `proxynet`

## 4. Para `Pre Production`

para modo pruebas en producion usar `docker-compose.prod_db.yml` y `docker-compose.prod.yml`

- `docker-compose.prod_db.yml` =  es para solo lebantar la db de simulacion
- `docker-compose.prod.yml` = donde esta el servicio mongo y el app

```shell

# ::::::  si queremos levantar los dos fihceros email
docker compose -f docker-compose.prod_db.yml -f docker-compose.prod.yml  --env-file .env.production up -d

# ::::::  si queremos parar los dos fihceros email
docker compose -f docker-compose.prod_db.yml -f docker-compose.prod.yml --env-file .env.production down


```


## 5. Para `Production`

en este caso en el servidor ya tenemos instalado la base de datos implementada . por tanto ya no necesitamos el fichero `docker-compose.prod_db.yml`


- `docker-compose.prod.yml` = donde esta el servicio mongo y el app


