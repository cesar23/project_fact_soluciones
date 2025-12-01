## Asi activaremos las cuentas de terceros

```shell
docker exec smart1-fpm1-1 php artisan tinker --execute="echo 'Token: ' . config('configuration.api_service_token');"
```


Cmabiar estas variables:
```shell
 API_SERVICE_TOKEN=tu_token_aqui
```