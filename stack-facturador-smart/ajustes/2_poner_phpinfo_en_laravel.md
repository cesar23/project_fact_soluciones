En Laravel 10, puedes crear una ruta que muestre la información de `phpinfo()` de forma muy sencilla. Solo necesitas agregar una ruta en tu archivo de rutas (por ejemplo, `routes/web.php`) que ejecute la función `phpinfo()`.

### Paso a paso:

1. Abre el archivo `routes/web.php`.
2. Agrega la siguiente ruta:


```php
Route::get('/phpinfo', function () {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();
    return response($phpinfo)->header('Content-Type', 'text/html');
});
```

3. Guarda el archivo.
4. Accede desde tu navegador a: `http://tu-dominio.local/phpinfo` (o `http://localhost/phpinfo` si estás en local).



