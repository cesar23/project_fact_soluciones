
## 📋 Opcion 1 - Descargar solo los  cambios

```shell
cd /home/cesar/docker-stacks/project_fact_soluciones
```

```shell
# fetch - 1. descargar cambios
git fetch
# fetch - 2. ver cambios descargados
git log --oneline HEAD..origin/master
# fetch - 3. Ver el contenido de los cambios
git diff HEAD origin/master
# fetch - 4. Aplicar cambios locales (⚠️ CUIDADO: esto sobrescribe cambios locales)
git merge origin/master
```

## 📋 Opcion 2 - Descargar Cambios del servidor quitando todos los cambios actuales


```shell
# Obtener cambios del repositorio para no aya  conflictos despues
git fetch origin master
git reset --hard origin/master
```
