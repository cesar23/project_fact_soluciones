
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

# 4. Configurar tracking
git lfs track "stack-facturador-smart/smart1.tar.gz"
git lfs track "*.tar.gz"

# 5. Agregar configuración
git add .gitignore .gitattributes
git commit -m "Configure gitignore and LFS tracking"

# 6. Agregar proyecto completo
git add .
git status

# 7. Commit y push
git commit -m "Add project files with LFS for large files"
git push origin master
```

## 2. Clonar repositorio en otro Servidor

```shell
# 1. Instalación de Git LFS
winget install GitHub.GitLFS
# 0
sudo apt install git-lfs

# Clonar
git clone git@github.com:cesar23/project_fact_soluciones.git

# 2. Navegar al proyecto
cd project_fact_soluciones


# 2. Inicializar LFS
git lfs install

# 3. Descargar todos los archivos LFS
git lfs pull

```