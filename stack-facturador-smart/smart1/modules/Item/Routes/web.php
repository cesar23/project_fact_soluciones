<?php

use Illuminate\Support\Facades\Route;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'locked.tenant'])->group(function () {



            Route::post('items/{id}/update-stock-item', 'ItemController@updateStockItem');
            Route::post('items/{id}/update-purchase-unit-price', 'ItemController@updatePurchaseUnitPrice');
            Route::get('items/items-document-error/{hash}', 'ItemController@ItemsDocumentErrors');
            Route::post('items/items-document', 'ItemController@uploadItemsDocument');
            Route::post('items/items-dispatch', 'ItemController@uploadItemsDispatch');
            Route::post('items/items-document-news', 'ItemController@uploadItemsDocumentNews');
            Route::get('items/codes-digemid', 'ItemController@codes_digemid');
            Route::post('items/{id}/update-info-item', 'ItemController@updateInfoItem');

            Route::get('categories', 'CategoryController@index')->name('tenant.categories.index')->middleware('redirect.level');
            Route::get('categories/records', 'CategoryController@records');
            Route::get('categories/columns', 'CategoryController@columns');
            Route::get('categories/record/{category}', 'CategoryController@record');
            Route::post('categories', 'CategoryController@store');
            Route::delete('categories/{category}', 'CategoryController@destroy');

            Route::get('brands', 'BrandController@index')->name('tenant.brands.index')->middleware('redirect.level');
            Route::get('brands/records', 'BrandController@records');
            Route::get('brands/record/{brand}', 'BrandController@record');
            Route::post('brands', 'BrandController@store');
            Route::get('brands/columns', 'BrandController@columns');
            Route::delete('brands/{brand}', 'BrandController@destroy');

            Route::get('ingredient-attributes', 'IngredientAttributeItemController@index')->name('tenant.ingredient-attributes.index')->middleware('redirect.level');
            Route::get('ingredient-attributes/records', 'IngredientAttributeItemController@records');
            Route::get('ingredient-attributes/record/{ingredient}', 'IngredientAttributeItemController@record');
            Route::post('ingredient-attributes', 'IngredientAttributeItemController@store');
            Route::get('ingredient-attributes/columns', 'IngredientAttributeItemController@columns');
            Route::delete('ingredient-attributes/{ingredient}', 'IngredientAttributeItemController@destroy');

            Route::get('line-attributes', 'LineAttributeItemController@index')->name('tenant.line-attributes.index')->middleware('redirect.level');
            Route::get('line-attributes/records', 'LineAttributeItemController@records');
            Route::get('line-attributes/record/{line}', 'LineAttributeItemController@record');
            Route::post('line-attributes', 'LineAttributeItemController@store');
            Route::get('line-attributes/columns', 'LineAttributeItemController@columns');
            Route::delete('line-attributes/{line}', 'LineAttributeItemController@destroy');

            Route::get('item-attributes', 'ItemAttributeController@index')->name('tenant.item-attributes.index')->middleware('redirect.level');
            Route::get('item-attributes/records', 'ItemAttributeController@records');
            Route::get('item-attributes/record/{itemAttribute}', 'ItemAttributeController@record');
            Route::post('item-attributes', 'ItemAttributeController@store');
            Route::get('item-attributes/columns', 'ItemAttributeController@columns');
            Route::delete('item-attributes/{itemAttribute}', 'ItemAttributeController@destroy');
            Route::get('item-attributes/ingredients', 'ItemAttributeController@getIngredients');
            Route::get('item-attributes/lines', 'ItemAttributeController@getLines');
            Route::get('item-attributes/item/{item_id}', 'ItemAttributeController@getItemAttributes');



            Route::prefix('zones')->group(function () {

                Route::get('', 'ZoneController@index')->name('tenant.zone.index');
                Route::post('', 'ZoneController@store');
                Route::get('/records', 'ZoneController@records');
                Route::get('/record/{brand}', 'ZoneController@record');
                Route::get('/columns', 'ZoneController@columns');
                Route::delete('/{brand}', 'ZoneController@destroy');
            });


            Route::get('incentives', 'IncentiveController@index')->name('tenant.incentives.index')->middleware('redirect.level');
            Route::get('incentives/records', 'IncentiveController@records');
            Route::get('incentives/record/{incentive}', 'IncentiveController@record');
            Route::post('incentives', 'IncentiveController@store');
            Route::get('incentives/columns', 'IncentiveController@columns');
            Route::delete('incentives/{incentive}', 'IncentiveController@destroy');

            Route::get('items/barcode/{item}', 'ItemController@generateBarcode');

            Route::post('items/import/item-price-lists', 'ItemController@importItemPriceLists');
            Route::post('items/import/item-size-lists', 'ItemController@importItemSizeLists');
            Route::post('items/import/item-with-extra-data', 'ItemController@importItemWithExtraData');

            //history
            Route::get('items/data-history/{item}', 'ItemController@getDataHistory');
            Route::get('items/available-series/records', 'ItemController@availableSeriesRecords');
            Route::get('items/history-sales/records', 'ItemController@itemHistorySales');
            Route::get('items/history-purchases/records', 'ItemController@itemHistoryPurchases');
            Route::get('items/last-sale', 'ItemController@itemtLastSale');

            // Apportionment Items Stock routes
            Route::get('apportionment-items-stock/{item_id}', 'ItemController@getApportionmentItemsStock');
            Route::delete('apportionment-items-stock/{id}', 'ItemController@deleteApportionmentItemsStock');

            Route::get("items/set_internal_code", "ItemController@set_internal_code");
            //history

            Route::prefix('item-lots')->group(function () {

                Route::get('', 'ItemLotController@index')->name('tenant.item-lots.index');
                Route::get('/records', 'ItemLotController@records');
                Route::get('/record/{record}', 'ItemLotController@record');
                Route::post('', 'ItemLotController@store');
                Route::get('/history-lots/{record}', 'ItemLotController@historyLots');
                Route::get('/columns', 'ItemLotController@columns');
                Route::get('/export', 'ItemLotController@export');
                Route::post('/check', 'ItemLotController@checkSeries');
            });

            Route::post('items/import/item-sets', 'ItemSetController@importItemSets');
            Route::post('items/import/item-sets-individual', 'ItemSetController@importItemSetsIndividual');
            Route::prefix('state-deliveries')->group(function () {

                Route::get('', 'StatesDeliveryController@index');
                Route::get('/records', 'StatesDeliveryController@records');
                Route::get('/record/{brand}', 'StatesDeliveryController@record');
                Route::post('', 'StatesDeliveryController@store');
                Route::delete('/{record}', 'StatesDeliveryController@destroy');
            });

            Route::prefix('web-platforms')->group(function () {

                Route::get('', 'WebPlatformController@index');
                Route::get('/records', 'WebPlatformController@records');
                Route::get('/record/{brand}', 'WebPlatformController@record');
                Route::post('', 'WebPlatformController@store');
                Route::delete('/{record}', 'WebPlatformController@destroy');
            });

            Route::post('items/import/items-update-prices', 'ItemController@importItemUpdatePrices');
            Route::post('items/import/items-update-prices-warehouses', 'ItemController@importItemUpdatePricesWarehouses');
            Route::post('items/import/items-update-prices-presentation', 'ItemController@importItemUpdatePricesPresentation');
            Route::post('items/import/items-update-prices-person-type', 'ItemController@importItemUpdatePricesPersonType');


            Route::prefix('item-lots-group')->group(function () {
                Route::get('', 'ItemLotsGroupController@index')->name('tenant.item-lots-group.index');
                Route::post('/', 'ItemLotsGroupController@store');
                Route::get('/records', 'ItemLotsGroupController@records');
                Route::get('/export', 'ItemLotsGroupController@export');
                Route::get('/tables', 'ItemLotsGroupController@tables');
                Route::get('/update_state', 'ItemLotsGroupController@update_state');
                Route::get('/update_warehouse', 'ItemLotsGroupController@update_warehouse');
                Route::get('/columns', 'ItemLotsGroupController@columns');
                Route::get('/re-store-warehouse', 'ItemLotsGroupController@reStoreWarehouse');
                Route::get('/record/{record}', 'ItemLotsGroupController@record');
                Route::post('/upload', 'ItemLotsGroupController@upload');
                Route::get('/remove-file', 'ItemLotsGroupController@removeFile');
                Route::get('available-data/{item_id}', 'ItemLotsGroupController@getAvailableItemLotsGroup');
            });

            Route::prefix('item-sizes')->group(function () {
                Route::get('', 'ItemSizeStockController@index')->name('tenant.item-sizes.index');
                // Route::post('/', 'ItemSizeStockController@store');
                Route::get('/records', 'ItemSizeStockController@records');
                Route::put('/size/{id}', 'ItemSizeStockController@updateSize');
                // Route::get('/tables', 'ItemSizeStockController@tables');
                // Route::get('/update_state', 'ItemSizeStockController@update_state');
                Route::get('/columns', 'ItemSizeStockController@columns');
                Route::get('/export', 'ItemSizeStockController@export');
                // Route::get('/record/{record}', 'ItemSizeStockController@record');
                // Route::get('available-data/{item_id}', 'ItemSizeStockController@getAvailableItemLotsGroup');
            });
        });
    });
}
