
## 1. Resumen de Comandos para Preparar. Repositorio para  subir ficheros  grandes

```shell
# 1. Instalación de Git LFS
winget install GitHub.GitLFS
# 0
sudo apt install git-lfs

# 2. Navegar al proyecto
cd D:\repos\project_fact_soluciones

# 3. Inicializar Git LFS
git lfs install


# 4. Dar Permisos a los ficheros de compresion (stack-facturador-smart/smart1.tar.gz)
# chmod +x stack-facturador-smart/smart1_compress.sh
# chmod +x stack-facturador-smart/smart1_decompress.sh


#  eliminar vendor
rm -rf stack-facturador-smart/smart1/vendor/*

# 6. Compando para descomprimir el fichero (stack-facturador-smart/smart1.tar.gz) 
./stack-facturador-smart/smart1_compress.sh

# 7. Configurar tracking (la primera vez)
git lfs track "stack-facturador-smart/smart1/.git.zip"
git lfs track "stack-facturador-smart/smart1/public/js/app.js.map"
git lfs track "*.tar.gz"

# 8. Agregar configuración
git add .
git commit -m "Configure gitignore and LFS tracking 3"
git lfs push origin master && git push origin master

```

## 2. Clonar repositorio en otro Servidor Primera vez

```shell
# 1. Instalación de Git LFS
winget install GitHub.GitLFS
# 0
sudo apt install git-lfs

# =============
# Clonar
git clone git@github.com:cesar23/project_fact_soluciones.git

# 2. Navegar al proyecto
cd project_fact_soluciones

# 3. Inicializar LFS
git lfs install

# 3.2 deshacer cambios locales
git fetch origin master && git reset --hard origin/master

# 4. Descargar todos los archivos LFS
git lfs pull

# 5. Dar Permisos a los ficheros de compresion (stack-facturador-smart/smart1.tar.gz)

unzip stack-facturador-smart/smart1/.git.zip -d stack-facturador-smart/smart1/
#chmod +x stack-facturador-smart/smart1_compress.sh
#chmod +x stack-facturador-smart/smart1_decompress.sh

# 6. Compando para descomprimir el fichero (stack-facturador-smart/smart1.tar.gz) 
# ./stack-facturador-smart/smart1_decompress.sh
```

## 3. Descargar Cambios de Repositorio en servidor de produccion
Descartar cambios locales  ya que  lo que nos importa
- quitar cambios locales
- descarga los bnuevos cambios ene l servidor
```shell
# 0. parar los servicios
docker compose -f stack-facturador-smart/smart1/docker-compose.yml down
# 1. deshacer cambios locales y descargar los cambios
git fetch origin master && git reset --hard origin/master
# 2. permisos
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh
# 3. descomprimir el fichero (stack-facturador-smart/smart1.tar.gz) 
./stack-facturador-smart/smart1_decompress.sh

# 4. dar permisos para carpetas
sudo chmod -R 777 "./stack-facturador-smart/smart1/storage/" \
 "./stack-facturador-smart/smart1/bootstrap/" \
  "./stack-facturador-smart/smart1/vendor/"
  
# 5. Levantando contenedores Usando -f para especificar archivo 
# :::::::: si se hizo cambio enel docker file
docker compose -f stack-facturador-smart/smart1/docker-compose.yml build --no-cache
docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d

# 6. instalar compose
# ============================================
# ============================================
# docker exec fpm1 composer install
docker exec fpm1 bash -c "composer install"
docker exec fpm1 bash -c "php artisan storage:link"
docker exec fpm1 bash -c "php artisan cache:clear"
docker exec fpm1 bash -c "php artisan config:cache"
# docker exec fpm1 bash -c "ls -lsha vendor/mpdf/mpdf"
docker exec fpm1 bash -c "chmod -R 777 vendor/mpdf/mpdf"

# 5. arancar supervisor
docker exec supervisor1 supervisorctl start all
docker exec supervisor1 supervisorctl status


```

AHora puedo descargar los cambios

```shell
git pull origin master
```