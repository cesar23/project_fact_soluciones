# Cambios de ficheros 

## 1. Cambios en ficheros en Apis de productos

Listado de productos por api

```shell
curl --location 'https://tienda-fact.rog.pe/api/document/search-items' \
--header 'Authorization: Bearer ...FdW6rrZO'
```
- se modifico el fichero `app/Http/Controllers/Tenant/Api/AppController.php`

- Pero en el metodo :`searchItems`

```injectablephp
...
// Agregados por cesar
 'apply_store' => $row->apply_store,
 'updated_at' => $row->updated_at->toDateString(),
...
```
se agrego esta columna para obtener la columna si el producto se publicara en el `ecommerce`




