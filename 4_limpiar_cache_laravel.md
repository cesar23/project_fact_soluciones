## limpiando cache


```shell
docker exec smart1-fpm1-1 php artisan config:clear
docker exec smart1-fpm1-1 php artisan config:cache 
```