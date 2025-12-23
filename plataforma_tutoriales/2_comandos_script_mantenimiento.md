## 1. verificar conexion DB


verificamos la conexiona la  db `comando basico php`
```shell
docker-compose exec -T fpm1 php -r \
 'try { 
      $pdo = new PDO("mysql:host=mariadb1;port=3306;dbname=smart1", "root", "WPsOd4xPLL4nGRnOAHJp"); 
      echo "Connection successful!\n"; 
    } catch (Exception $e) { 
      echo "Error: " . $e->getMessage() . "\n"; 
    }'
```

verificamos con laravel

```shell
docker-compose exec -T fpm1 php -r '
try {
    include "vendor/autoload.php";
    include "bootstrap/app.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    DB::connection()->getPdo();
    echo "DB Connected!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
'
```

## 2. limpiar cache de laravel

```shell
docker-compose exec -T fpm1 php artisan config:clear && docker-compose exec -T fpm1 php artisan cache:clear
```