
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
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh

# 6. Compando para descomprimir el fichero (stack-facturador-smart/smart1.tar.gz) 
./stack-facturador-smart/smart1_compress.sh

# 7. Configurar tracking
git lfs track "stack-facturador-smart/smart1.tar.gz"
git lfs track "*.tar.gz"

# 8. Agregar configuración
git add .gitignore .gitattributes
git commit -m "Configure gitignore and LFS tracking"
git lfs push origin master

# 9. Agregar proyecto completo
git add .
git status

# 10. Commit y push
git commit -m "Add project files with LFS for large files"
git push origin master
```

## 2. Clonar repositorio en otro Servidor

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

# 4. Descargar todos los archivos LFS
git lfs pull

# 5. Dar Permisos a los ficheros de compresion (stack-facturador-smart/smart1.tar.gz)
chmod +x stack-facturador-smart/smart1_compress.sh
chmod +x stack-facturador-smart/smart1_decompress.sh

# 6. Compando para descomprimir el fichero (stack-facturador-smart/smart1.tar.gz) 
./stack-facturador-smart/smart1_decompress.sh
```

## 3. Descargar Cambios de Repositorio en servidor de produccion
Descartar cambios locales  ya que  lo que nos importa
- quitar cambios locales
- descarga los bnuevos cambios ene l servidor
```shell
git reset --hard origin/master
```

AHora puedo descargar los cambios

```shell
git pull origin master
```