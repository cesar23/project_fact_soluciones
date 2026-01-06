#  video : https://www.youtube.com/watch?v=wPuCsre30S0

nano install.sh

chmod +x install.sh
# coamndo para ingresar el dominio
./install.sh facturadorsmart.pe

# ya  despues de esa  instalacion entrar al contendor
docker exec -ti smart1_fpm1_1 /bin/bash

docker exec smart1-fpm1-1 php -m | grep -i soap


# Actualizar sources.list a archive.debian.org
echo "deb http://archive.debian.org/debian buster main" > /etc/apt/sources.list
echo "deb http://archive.debian.org/debian-security buster/updates main" >> /etc/apt/sources.list
echo "deb http://archive.debian.org/debian buster-updates main" >> /etc/apt/sources.list



apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

apt-get update && apt-get install -y \
    libxml2-dev \
    && docker-php-ext-install soap

apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

exit
docker restart smart1_fpm1_1
