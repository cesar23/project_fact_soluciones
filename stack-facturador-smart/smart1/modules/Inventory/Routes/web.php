<?php

use Illuminate\Support\Facades\Route;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'redirect.module', 'locked.tenant'])->group(function () {

            Route::get('advanced-items-search', 'ItemController@advancedItemsSearch');
            Route::post('validate-current-item-stock', 'ItemController@validateCurrentItemStock');


            Route::prefix('items')->group(function () {
                Route::post('import-item-lots-group', 'ItemController@importItemLotsGroup');
                Route::post('import-item-lots', 'ItemController@importItemLots');
            });

            Route::prefix('item-cost-history')->group(function () {
                Route::get('/', 'ItemCostHistoryController@index')->name('tenant.item-cost-history.index');
                Route::get('/filter', 'ItemCostHistoryController@filter');
                Route::get('create-history-by-item-id/{item_id}/{warehouse_id}', 'ItemCostHistoryController@createHistoryByItemId');
                Route::get('calculate-history-by-item-id/{item_id}/{warehouse_id}', 'ItemCostHistoryController@calculateHistoryByItemId');
                Route::get('records', 'ItemCostHistoryController@records');
                Route::get('filter-by-warehouse/{warehouse_id}', 'ItemCostHistoryController@filterByWarehouse');
                Route::get('search-items', 'ItemCostHistoryController@searchItems');
                Route::get('excel_margin', 'ItemCostHistoryController@excelGain');
                Route::get('excel', 'ItemCostHistoryController@excel');
            });
            // Config inventory

            Route::prefix('warehouses')->group(function () {
                Route::get('/', 'WarehouseController@index')->name('warehouses.index');
                Route::get('records', 'WarehouseController@records');
                Route::get('all-records', 'WarehouseController@allRecords');
                Route::get('columns', 'WarehouseController@columns');
                Route::get('tables', 'WarehouseController@tables');
                Route::get('record/{warehouse}', 'WarehouseController@record');
                Route::post('/', 'WarehouseController@store');
                Route::get('initialize', 'WarehouseController@initialize');
            });
            /**
             * extra_info_items
             * extra_info_items/colors
             * extra_info_items/colors/records
             * extra_info_items/colors/record/{id}
             * extra_info_items/colors/save/{id}
             * extra_info_items/item-units-per-package
             * extra_info_items/item-units-per-package/records
             * extra_info_items/item-units-per-package/record/{id}
             * extra_info_items/item-units-per-package/save/{id}
             * extra_info_items/item-units-business
             * extra_info_items/item-units-business/records
             * extra_info_items/item-units-business/record/{id}
             * extra_info_items/item-units-business/save/{id}
             * extra_info_items/item-status
             * extra_info_items/item-status/records
             * extra_info_items/item-status/record/{id}
             * extra_info_items/item-status/save/{id}
             * extra_info_items/item-package-measurements
             * extra_info_items/item-package-measurements/records
             * extra_info_items/item-package-measurements/record/{id}
             * extra_info_items/item-package-measurements/save/{id}
             * extra_info_items/item-mold-cavities
             * extra_info_items/item-mold-cavities/records
             * extra_info_items/item-mold-cavities/record/{id}
             * extra_info_items/item-mold-cavities/save/{id}
             * extra_info_items/item-mold-property
             * extra_info_items/item-mold-property/records
             * extra_info_items/item-mold-property/record/{id}
             * extra_info_items/item-mold-property/save/{id}
             * extra_info_items/item-prodcut-family
             * extra_info_items/item-prodcut-family/records
             * extra_info_items/item-prodcut-family/record/{id}
             * extra_info_items/item-prodcut-family/save/{id}
             */
            Route::prefix('extra_info_items')->group(function () {

                Route::get('', 'InventoryController@ExtraDataList')->name('extra_info_items.index');

                Route::prefix('colors')->group(function () {
                    Route::get('/', 'ColorController@index')->name('colors.index');
                    Route::get('records', 'ColorController@records');
                    Route::post('record/{id}', 'ColorController@record');
                    Route::post('save/{id}', 'ColorController@store');
                });
                Route::prefix('item-units-per-package')->group(function () {
                    Route::get('/', 'ItemUnitPerPackageController@index')->name('item-units-per-package.index');
                    Route::get('records', 'ItemUnitPerPackageController@records');
                    Route::post('record/{id}', 'ItemUnitPerPackageController@record');
                    Route::post('save/{id}', 'ItemUnitPerPackageController@store');
                });
                Route::prefix('item-units-business')->group(function () {
                    Route::get('/', 'ItemUnitBusinessController@index')->name('item-units-business.index');
                    Route::get('records', 'ItemUnitBusinessController@records');
                    Route::post('record/{id}', 'ItemUnitBusinessController@record');
                    Route::post('save/{id}', 'ItemUnitBusinessController@store');
                });
                Route::prefix('item-status')->group(function () {
                    Route::get('/', 'ItemStatusController@index')->name('item-status.index');
                    Route::get('records', 'ItemStatusController@records');
                    Route::post('record/{id}', 'ItemStatusController@record');
                    Route::post('save/{id}', 'ItemStatusController@store');
                });

                Route::prefix('item-package-measurements')->group(function () {
                    Route::get('/', 'ItemPackageMeasurementsController@index')->name('item-package-measurements.index');
                    Route::get('records', 'ItemPackageMeasurementsController@records');
                    Route::post('record/{id}', 'ItemPackageMeasurementsController@record');
                    Route::post('save/{id}', 'ItemPackageMeasurementsController@store');
                });
                Route::prefix('item-mold-cavities')->group(function () {
                    Route::get('/', 'ItemMoldCavitiesController@index')->name('item-mold-cavities.index');
                    Route::get('records', 'ItemMoldCavitiesController@records');
                    Route::post('record/{id}', 'ItemMoldCavitiesController@record');
                    Route::post('save/{id}', 'ItemMoldCavitiesController@store');
                });
                Route::prefix('item-mold-property')->group(function () {
                    Route::get('/', 'ItemMoldPropertyController@index');
                    Route::get('records', 'ItemMoldPropertyController@records');
                    Route::post('record/{id}', 'ItemMoldPropertyController@record');
                    Route::post('save/{id}', 'ItemMoldPropertyController@store');
                });

                Route::prefix('item-prodcut-family')->group(function () {
                    Route::get('/', 'ItemProductFamilyController@index');
                    Route::get('records', 'ItemProductFamilyController@records');
                    Route::post('record/{id}', 'ItemProductFamilyController@record');
                    Route::post('save/{id}', 'ItemProductFamilyController@store');
                });
                Route::prefix('item-size')->group(function () {
                    Route::get('/', 'ItemSizeController@index');
                    Route::get('records', 'ItemSizeController@records');
                    Route::post('record/{id}', 'ItemSizeController@record');
                    Route::post('save/{id}', 'ItemSizeController@store');
                });
            });

            Route::prefix('inventory')->group(function () {
                Route::get('search-items', 'InventoryController@searchItemsInventory');

                Route::prefix('validate')->group(function () {
                    Route::get('/', 'InventoryController@validateIndex')->name('inventory.validate.index');
                    Route::get('/validations/{warehouse_id}', 'InventoryController@getValidations');
                    Route::get('/tables', 'InventoryController@validateTables');
                    Route::get('/search-items-validate', 'InventoryController@searchItemsValidate');
                    Route::get('/search-series-validate', 'InventoryController@searchSeriesValidate');
                    Route::post('/validate-items', 'InventoryController@validateItems');
                    Route::post('/save-validation', 'InventoryController@saveValidation');
                    Route::get('/report-validation/{id}', 'InventoryController@reportValidation');
                });
                /**
                 * inventory/
                 * inventory/records
                 * inventory/columns
                 * inventory/tables
                 * inventory/tables/transaction/{type}
                 * inventory/record/{inventory}
                 * inventory/
                 * inventory/transaction
                 * inventory/move
                 * inventory/move-multilple
                 */
                Route::get('/', 'InventoryController@index')->name('inventory.index');
                Route::get('records', 'InventoryController@records');
                Route::get('columns', 'InventoryController@columns');
                Route::get('tables', 'InventoryController@tables');
                Route::get('tables/transaction/{type}', 'InventoryController@tables_transaction');
                Route::get('tables/transaction-input', 'InventoryController@tables_transaction_input');
                Route::get('tables/transaction-input/records', 'InventoryController@tables_transaction_input_records');
                Route::get('record/{inventory}', 'InventoryController@record');
                Route::get('record-item/{id}/{warehouse_id}', 'InventoryController@recordItem');
                Route::post('/', 'InventoryController@store');
                Route::post('/transaction', 'InventoryController@store_transaction');
                Route::post('/transaction-import', 'InventoryController@store_transaction_import');
                Route::post('move', 'InventoryController@move');
                Route::post('move-multilple', 'InventoryController@moveMultiples');
                Route::post('stock', 'InventoryController@stock');
                Route::post('stock-multilple', 'InventoryController@stockMultiples');
                Route::post('import', 'InventoryController@import');
                Route::get('excel', 'InventoryController@excel');
                Route::get('pdf', 'InventoryController@pdf');
                /**
                 * inventory/moves
                 * inventory/remove
                 * inventory/initialize
                 * inventory/regularize_stock
                 * inventory/search_items
                 */
                Route::get('moves', 'MovesController@index')->name('inventory.moves.index');

                Route::post('remove', 'InventoryController@remove');
                Route::get('initialize', 'InventoryController@initialize');
                Route::get('regularize_stock', 'InventoryController@regularize_stock');

                Route::post('search_items', 'InventoryController@searchItems');
                /**
                 * inventory/report/tables
                 * inventory/report/records
                 * inventory/report/export
                 */
                Route::prefix('report')->group(function () {
                    Route::get('tables', 'ReportInventoryController@tables');
                    Route::get('records', 'ReportInventoryController@records');
                    Route::get('totals', 'ReportInventoryController@totals');
                    Route::post('export', 'ReportInventoryController@export');
                    Route::get('excel-manual', 'ReportInventoryController@exportExcelManual');
                    Route::get('export-ticket', 'ReportInventoryController@exportTicket');
                });
            });

            Route::prefix('reports')->group(function () {
                Route::get('inventory', 'ReportInventoryController@index')->name('reports.inventory.index');
                Route::post('inventory/search', 'ReportInventoryController@search')->name('reports.inventory.search');
                Route::post('inventory/pdf', 'ReportInventoryController@pdf')->name('reports.inventory.pdf');
                Route::post('inventory/excel', 'ReportInventoryController@excel')->name('reports.inventory.report_excel');
                // Route::get('kardex', 'ReportKardexController@index')->name('reports.kardex.index');
                // Route::get('kardex/search', 'ReportKardexController@search')->name('reports.kardex.search');
                // Route::post('kardex/pdf', 'ReportKardexController@pdf')->name('reports.kardex.pdf');
                // Route::post('kardex/excel', 'ReportKardexController@excel')->name('reports.kardex.report_excel');


                /**
                 * reports/kardex/
                 * reports/kardex/pdf
                 * reports/kardex/excel
                 * reports/kardex/filter
                 * reports/kardex/records
                 * reports/kardex/lots/filter
                 **/

                Route::get('/stock_date', 'ReportValuedKardexController@stock_date')->name('tenant.stock-date.index');
                Route::prefix('kardexaverage')->group(function () {
                    Route::get('', 'ReportAverageKardexController@index')->name('reports.kardexaverage.index');
                    Route::get('/pdf', 'ReportAverageKardexController@pdf');
                    Route::get('/excel', 'ReportAverageKardexController@excel');
                    Route::get('/filter', 'ReportAverageKardexController@filter');
                    Route::get('/records', 'ReportAverageKardexController@records');
                    Route::get('/lots/filter', 'ReportAverageKardexController@records_lots');
                });
                Route::prefix('kardex')->group(function () {

                    Route::get('', 'ReportKardexController@index')->name('reports.kardex.index');
                    Route::get('/pdf', 'ReportKardexController@pdf')->name('reports.kardex.pdf');
                    Route::get('/avg', 'ReportKardexController@avg');
                    Route::delete('/delete/{id}', 'ReportKardexController@delete');
                    Route::get('/excel', 'ReportKardexController@excel')->name('reports.kardex.excel');
                    Route::get('/filter', 'ReportKardexController@filter')->name('reports.kardex.filter');
                    Route::get('/filter_by_warehouse/{warehouse}', 'ReportKardexController@filterByWarehouse');
                    Route::get('/records', 'ReportKardexController@records')->name('reports.kardex.records');
                    Route::get('/lots/filter', 'ReportKardexController@records_lots');
                    Route::get('/get_init_stock', 'ReportKardexController@get_all_init_stock_by_month');
                    Route::get('/stock_adjustment', 'ReportKardexController@stock_adjustment');
                    Route::get('/item_adjustment', 'ReportKardexController@item_adjustment');
                    Route::get('/item_adjustment_optimized', 'ReportKardexController@item_adjustment_optimized');
                    Route::get('get_pdf_guide/{guide}', 'ReportKardexController@getPdfGuide');
                    Route::get('/get_items/{inventoryKardexId}', 'ReportKardexController@getItems');
                });
                Route::get('kardex_lots/filter', 'ReportKardexController@filter');
                Route::get('kardex_lots/filter_by_warehouse/{warehouse}', 'ReportKardexController@filterByWarehouse');
                Route::get('kardex_series/filter', 'ReportKardexController@filter');
                Route::get('kardex_series/filter_by_warehouse/{warehouse}', 'ReportKardexController@filterByWarehouse');

                Route::get('kardex_lots/records', 'ReportKardexController@records_lots_kardex')->name('reports.kardex_lots.records');
                Route::get('kardex_lots/pdf', 'ReportKardexLotsController@pdf');
                Route::get('kardex_lots/excel', 'ReportKardexLotsController@excel');

                Route::get('kardex_series/records', 'ReportKardexController@records_series_kardex')->name('reports.kardex_series.records');
                Route::get('kardex_series/pdf', 'ReportKardexSeriesController@pdf');
                Route::get('kardex_series/excel', 'ReportKardexSeriesController@excel');
                
                Route::get('kardex_attributes/filter_by_warehouse/{warehouse}', 'ReportKardexController@filterByWarehouse');
                Route::get('kardex_attributes/filter', 'ReportKardexController@filter');
                Route::get('kardex_attributes/records', 'ReportKardexController@records_attributes_kardex')->name('reports.kardex_attributes.records');
                Route::get('kardex_attributes/pdf', 'ReportKardexController@export_attributes_pdf');
                Route::get('kardex_attributes/excel', 'ReportKardexController@export_attributes_excel');
                /**
                 * reports/valued-kardex/
                 * reports/valued-kardex/excel
                 * reports/valued-kardex/filter
                 * reports/valued-kardex/records
                 **/


                Route::prefix('valued-kardex')->group(function () {
                    Route::get('', 'ReportValuedKardexController@index')->name('reports.valued_kardex.index');
                    Route::get('/excel', 'ReportValuedKardexController@excel');
                    Route::get('/general_stock', 'ReportValuedKardexController@excel_stock_months');
                    Route::get('/general_stock_excel', 'ReportValuedKardexController@excel_stock_months_export');
                    Route::get('/excel-format-sunat', 'ReportValuedKardexController@excelFormatSunat');
                    Route::get('/pdf-format-sunat', 'ReportValuedKardexController@pdfFormatSunat');
                    Route::get('/filter', 'ReportValuedKardexController@filter');
                    Route::get('/records', 'ReportValuedKardexController@records');
                });

                // reporte movimientos
                Route::prefix('inventory-movements')->group(function () {
                    Route::get('pdf', 'ReportMovementController@pdf');
                    Route::get('excel', 'ReportMovementController@excel');
                    Route::get('filter', 'ReportMovementController@filter');
                    Route::get('records', 'ReportMovementController@records');

                    Route::get('stock/records', 'ReportMovementController@stockRecords');
                    // Route::get('stock/excel/excel', 'ReportMovementController@stockExcel');

                    Route::get('stock/format-stock-fit/{type}', 'ReportMovementController@formatStockFit');
                    Route::get('filter-stock-fit', 'ReportMovementController@filterStockFit');
                });
            });


            Route::prefix('inventories')->group(function () {

                Route::get('configuration', 'InventoryConfigurationController@index')->name('tenant.inventories.configuration.index')->middleware('redirect.level');
                Route::get('configuration/record', 'InventoryConfigurationController@record');
                Route::post('configuration', 'InventoryConfigurationController@store');
            });

            Route::prefix('moves')->group(function () {

                Route::get('/', 'MovesController@index')->name('moves.index');
                Route::get('records', 'MovesController@records');
                Route::get('columns', 'MovesController@columns');
            });

            /**
             * transfers/
             * transfers/records
             * transfers/columns
             * transfers/tables
             * transfers/record/{inventory}
             * transfers/{inventory}
             * transfers/create
             * transfers/stock/{item_id}/{warehouse_id}
             * transfers/items/{warehouse_id}
             */

            Route::prefix('transfers')->group(function () {
                Route::get('/', 'TransferController@index')->name('transfers.index');
                Route::get('records', 'TransferController@records');
                Route::get('columns', 'TransferController@columns');
                Route::post('import', 'TransferController@import');
                Route::get('tables', 'TransferController@tables');
                Route::get('record/{inventory}', 'TransferController@record');
                Route::post('/', 'TransferController@store');
                Route::post('/to-accept', 'TransferController@storeToAccept');
                Route::post('/massive', 'TransferController@storeMassive');
                Route::post('/massive-purchase', 'TransferController@storeMassivePurchase');
                Route::delete('{inventory}', 'TransferController@destroy');
                Route::get('create', 'TransferController@create')->name('transfer.create');
                Route::get('stock/{item_id}/{warehouse_id}', 'TransferController@stock');
                Route::get('items/{warehouse_id}', 'TransferController@items');
                Route::get('accept-transfer/{id}', 'TransferController@acceptTransfer');
                Route::get('reject-transfer/{id}', 'TransferController@rejectTransfer');
                Route::post('search-items', 'TransferController@searchItems');
                Route::get('export', 'TransferController@exportPdf');
                Route::get('/download/pdf/{inventoryTransfer}', 'TransferController@getPdf');
                Route::get('/download/pdf-ticket/{inventoryTransfer}', 'TransferController@getTicket');
                // Route::get('info/{inventoryTransfer}', 'TransferController@getInventoryTransferData');

            });


            Route::prefix('devolutions')->group(function () {

                Route::get('/', 'DevolutionController@index')->name('devolutions.index');
                Route::get('records', 'DevolutionController@records');
                Route::get('columns', 'DevolutionController@columns');
                Route::get('item/tables', 'DevolutionController@item_tables');
                Route::get('tables', 'DevolutionController@tables');
                Route::get('record/{id}', 'DevolutionController@record');
                Route::post('/', 'DevolutionController@store');
                Route::get('create', 'DevolutionController@create')->name('devolutions.create');
                Route::get('search-items', 'DevolutionController@searchItems');
                Route::get('download/{external_id}/{format?}', 'DevolutionController@download');
            });


            Route::prefix('inventory-review')->group(function () {

                Route::get('', 'InventoryReviewController@index')->name('tenant.inventory-review.index');
                Route::get('filters', 'InventoryReviewController@filters');
                Route::get('records', 'InventoryReviewController@records');
                Route::get('records-paginate', 'InventoryReviewController@recordsPaginate');
                Route::get('records-paginate-items-id', 'InventoryReviewController@recordsPaginateItemIds');
                Route::post('export', 'InventoryReviewController@export');
                Route::prefix('download')->group(function () {
                    Route::get('pdf', 'InventoryReviewController@downloadPdf');
                    Route::get('pdf-landscape', 'InventoryReviewController@downloadPdfLandscape');
                    Route::get('pdf-ticket', 'InventoryReviewController@downloadPdfTicket');
                    Route::get('xlsx', 'InventoryReviewController@downloadExcel');
                });
            });
        });
    });
}
