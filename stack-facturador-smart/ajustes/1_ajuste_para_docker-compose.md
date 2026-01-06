El problema es que **Debian Buster llegó a su fin de vida (EOL)** y los repositorios se movieron a archive. Además, **cualquier cambio que hagas dentro del contenedor se perderá** cuando lo reinicies.

## ⚠️ Problema: Debian Buster EOL

La imagen `rash07/php-fpm:7.4` usa Debian Buster que ya no tiene soporte. Los repositorios se movieron a `archive.debian.org`.

### Solución Temporal (dentro del contenedor):

```bash
# Actualizar sources.list a archive.debian.org
echo "deb http://archive.debian.org/debian buster main" > /etc/apt/sources.list
echo "deb http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list
echo "deb http://archive.debian.org/debian buster-updates main" >> /etc/apt/sources.list

# Ahora sí funcionará
apt-get update
apt-get install -y libzip-dev
docker-php-ext-configure zip
docker-php-ext-install zip
```

## 🚨 IMPORTANTE: Estos cambios se PERDERÁN

Los cambios dentro del contenedor son temporales. Cuando hagas `docker-compose restart`, todo se perderá.

## ✅ Solución Permanente: Crear un Dockerfile

### Opción 1: Dockerfile personalizado

Crea `/home/cesar/stack-facturador-smart/smart1/Dockerfile.fpm`:

```dockerfile
FROM rash07/php-fpm:7.4

# Actualizar sources a archive
RUN echo "deb http://archive.debian.org/debian buster main" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian buster-updates main" >> /etc/apt/sources.list

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip soap gd pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
```

Luego modifica `docker-compose.yml`:

```yaml
 fpm1:
     build: 
       context: .
       dockerfile: Dockerfile.fpm74
     # image: rash07/php-fpm:7.4  # Comentar esta línea
     working_dir: /var/www/html
     # resto igual...
```

Reconstruir:
```bash
cd /home/cesar/stack-facturador-smart/smart1
docker-compose build fpm1
docker-compose up -d fpm1
```

### Opción 2: Usar imagen PHP oficial actualizada

Cambiar en `docker-compose.yml`:

```yaml
 fpm1:
     image: php:7.4-fpm-bullseye  # Debian 11, con soporte
     working_dir: /var/www/html
     # resto igual...
```

### Opción 3: Script de inicialización

Crear `/home/cesar/stack-facturador-smart/smart1/init-fpm.sh`:

```bash
#!/bin/bash
# Actualizar sources
echo "deb http://archive.debian.org/debian buster main" > /etc/apt/sources.list
echo "deb http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list

# Instalar extensiones
apt-get update
apt-get install -y libzip-dev libxml2-dev
docker-php-ext-install zip soap pdo_mysql

# Continuar con el comando original
docker-php-entrypoint php-fpm
```

Y montar en docker-compose:
```yaml
 fpm1:
     image: rash07/php-fpm:7.4
     volumes:
       - ./init-fpm.sh:/init-fpm.sh
     command: /init-fpm.sh
     # resto igual...
```

## 📝 Verificar extensiones instaladas

```bash
# Ver qué extensiones PHP están instaladas
docker exec smart1_fpm1_1 php -m

# Verificar específicamente zip
docker exec smart1_fpm1_1 php -m | grep zip
```

**Recomiendo la Opción 1** (Dockerfile personalizado) porque es la más limpia y mantenible.