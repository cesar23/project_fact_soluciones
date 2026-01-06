## limpiando cache


```shell
docker exec smart1-fpm1-1 php artisan config:clear
docker exec smart1-fpm1-1 php artisan config:cache 

docker exec fpm1 php artisan config:clear
docker exec fpm1 php artisan cache:clear
docker exec fpm1 php artisan route:clear
docker exec fpm1 php artisan view:clear
docker exec fpm1 php artisan optimize:clear
docker exec fpm1 php artisan config:cache
```