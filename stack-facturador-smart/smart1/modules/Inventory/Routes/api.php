<?php

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        
        Route::middleware(['auth:api', 'redirect.module', 'locked.tenant'])->group(function() {


            Route::prefix('inventory')->group(function () {

                Route::post('/transaction', 'Api\InventoryController@store_transaction');

                //agregado para app modulo inventario/movimiento
                Route::post('/transactions', 'InventoryController@store_transaction');
                Route::get('/tables/{type}', 'InventoryController@tables_transaction');
                Route::get('records', 'InventoryController@records');
                Route::get('record/{inventory}', 'InventoryController@record');
                Route::post('move', 'InventoryController@move');
                Route::get('warehouses', 'WarehouseController@records');
            });

        });
    });
}
