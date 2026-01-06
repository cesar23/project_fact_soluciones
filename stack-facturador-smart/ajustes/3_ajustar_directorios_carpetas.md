## ajusdtar permisos de  directorios

problema que tenia  es  cuando creaba los directorios sin tener los permisos

`smart1/storage/logs/laravel-2025-10-22.log`

```shell
[2025-10-22 12:20:25] local.ERROR: Line: 112 - Message: Impossible to create the root directory "/var/www/html/storage/app/tenancy/tenants/tenancy_tienda/unsigned".  - File: /var/www/html/vendor/league/flysystem/src/Adapter/Local.php  
[2025-10-22 12:20:25] local.ERROR: Impossible to create the root directory "/var/www/html/storage/app/tenancy/tenants/tenancy_tienda/unsigned".  /var/www/html/vendor/league/flysystem/src/Adapter/Local.php 112  
[2025-10-22 12:21:34] local.ERROR: Line: 112 - Message: Impossible to create the root directory "/var/www/html/storage/app/tenancy/tenants/tenancy_tienda/unsigned".  - File: /var/www/html/vendor/league/flysystem/src/Adapter/Local.php  
[2025-10-22 12:21:34] local.ERROR: Impossible to create the root directory "/var/www/html/storage/app/tenancy/tenants/tenancy_tienda/unsigned".  /var/www/html/vendor/league/flysystem/src/Adapter/Local.php 112  

```
## Como solucionarlo en este caso:
```shell
# 1. darle los permisos
docker exec smart1-fpm1-1 chmod -R 755 /var/www/html/storage/app/tenancy/tenants/tenancy_tienda/
# 2. tambien el propietario
docker exec smart1-fpm1-1 chown -R www-data:www-data /var/www/html/storage/app/tenancy/tenants/tenancy_tienda/

```


## Ahora  como  hacerlo y dejarlo para que no suceda estos errores

```shell
docker exec smart1-fpm1-1 chown -R www-data:www-data /var/www/html/storage/
docker exec smart1-fpm1-1 chmod -R 755 /var/www/html/storage/
docker exec smart1-fpm1-1 chmod -R 777 /var/www/html/storage/
```