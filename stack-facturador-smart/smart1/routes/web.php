<?php

// Ruta temporal para phpinfo() - Solo en desarrollo
Route::get('/phpinfo', function () {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_clean();
    return response($phpinfo)->header('Content-Type', 'text/html');
});
use App\Http\Controllers\Tenant\AdvancesController;
use App\Http\Controllers\Tenant\BillOfExchangeController;
use App\Http\Controllers\Tenant\BillOfExchangePayController;
use App\Http\Controllers\Tenant\MultiCompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\PersonController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\CanchaController;
use App\Http\Controllers\Tenant\ComandaController;
use App\Http\Controllers\Tenant\ComandaMessageController;
use App\Http\Controllers\Tenant\CuponesController;
use App\Http\Controllers\System\ErrorsController;
use App\Http\Controllers\Tenant\ComercioController;
use App\Http\Controllers\Tenant\Reporttopcliente;
use App\Http\Controllers\Tenant\Reportitemstop;
use App\Http\Controllers\System\ClientPaymentController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\ConditionBlockPaymentMethodController;
use App\Http\Controllers\Tenant\MallController;
use App\Http\Controllers\Tenant\NameDocumentController;
use App\Http\Controllers\Tenant\NameQuotationsController;
use App\Http\Controllers\Tenant\PersonDispatcherPackerController;
use App\Http\Controllers\Tenant\PurchaseResponsibleLicenseController;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboard\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\ConfigurationController;
use App\Http\Controllers\Tenant\DiscountTypeController;
use App\Http\Controllers\Tenant\ChargeTypeController;
use App\Http\Controllers\Tenant\PriceAdjustmentController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\DocumentRecurrenceController;
use App\Http\Controllers\Tenant\ItemController;
use App\Http\Controllers\Tenant\MassiveMessageController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\SaleNoteController;
use App\Http\Controllers\Tenant\SupplyController;
use App\Http\Controllers\Tenant\SupplyPlanController;
use App\Http\Controllers\Tenant\SupplyStateController;
use App\Http\Controllers\Tenant\SupplyViaController;
use App\Http\Controllers\Tenant\SectorController;
use App\Http\Controllers\Tenant\SupplyPlanRegisteredController;
use App\Http\Controllers\Tenant\SupplySolicitudeController;
use App\Http\Controllers\Tenant\SupplyConceptController;
use App\Http\Controllers\Tenant\SupplyContractController;
use App\Http\Controllers\Tenant\SupplyOfficeController;
use App\Http\Controllers\Tenant\SupplyProcessController;
use App\Http\Controllers\Tenant\SupplyTypeDebtController;
use App\Http\Controllers\Tenant\SupplyDebtController;
use App\Http\Controllers\Tenant\SupplyReceiptController;
use App\Http\Controllers\Tenant\SupplyAdvancePaymentController;
use App\Http\Controllers\Tenant\SupplyOutageController;
use App\Http\Controllers\Tenant\SupplyPaymentConsumptionController;
use App\Http\Controllers\Tenant\SupplyPaymentOtherController;
use App\Http\Controllers\Tenant\SupplyServiceController;
use App\Http\Controllers\Tenant\WeaponTrackingController;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);
if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () use ($hostname) {

        Auth::routes([
            'register' => false,
            'verify'   => false
        ]);







        Route::get('/combined_pdf', [DocumentController::class, 'combined']);
        Route::get('/store', [ComercioController::class, 'records'])->name('tenant.comercios.records');
        Route::get('/producto/{id}', [ComercioController::class, 'detalles'])->name('producto.detalles');
        Route::get('/store/buscar', [ComercioController::class, 'search'])->name('comercio.buscar');
        Route::get('/store/tag/{id}', [ComercioController::class, 'filterByTag'])->name('comercio.filtrar');

        // Cached images endpoint
        Route::get('/cached-images/items/{filename}', [\App\Http\Controllers\Tenant\ImageCacheController::class, 'item'])
            ->where('filename', '.*')
            ->name('cached.item.image');

        // Batch cached images endpoint
        Route::post('/cached-images/batch', [\App\Http\Controllers\Tenant\ImageCacheController::class, 'batchItems'])
            ->name('cached.batch.images');

        Route::prefix('comanda-inicio')->group(function () {
            Route::get('/login', [ComandaController::class, 'showLoginForm'])->name('comanda.login');
            Route::post('/login', [ComandaController::class, 'login'])->name('comanda.login.post');
            Route::post('/logout', [ComandaController::class, 'logout'])->name('comanda.logout');

            Route::middleware('auth:personal')->group(function () {
                Route::get('/admin', [ComandaController::class, 'adminIndex'])->name('tenant.comanda.admin');
                Route::get('/restaurante', [ComandaController::class, 'restauranteIndex'])->name('tenant.comanda.restaurante');
                Route::get('/mesero', [ComandaController::class, 'meseroIndex'])->name('tenant.comanda.mesero');
                Route::get('/cocinero', [ComandaController::class, 'cocineroIndex'])->name('tenant.comanda.cocinero');


                Route::get('/messages', [ComandaMessageController::class, 'index'])->name('tenant.comanda.messages.index');
                Route::post('/messages/store', [ComandaMessageController::class, 'store'])->name('tenant.comanda.messages.store');
                Route::get('/messages/fetch', [ComandaMessageController::class, 'fetchMessages'])->name('tenant.comanda.messages.fetch');
            });
        });

        Route::get('/ajust-document-fee/{id}', [DocumentController::class, 'ajustDocumentFee'])->name('tenant.document.ajust_document_fee');



        Route::post('/api/configuration/update-show-edit-button', [ConfigurationController::class, 'updateShowEditButton']);
        Route::get('/api/configuration', [ConfigurationController::class, 'getConfiguration']);
        Route::get('/reservaciones', [CanchaController::class, 'reservaciones'])->name('tenant.canchas.reservaciones');
        Route::get('/canchas/reserva/{id}', [CanchaController::class, 'reservas'])->name('tenant.canchas.reservas');
        Route::post('/reservaciones/public', [CanchaController::class, 'publicStore']);


        Route::post('/cambiar_contrasena', [UserController::class, 'cambiarContrasena'])->name('cambiar_contrasena');

        Route::get('restaurant/worker/print-ticket/{id}', '\Modules\Restaurant\Http\Controllers\OrdenController@printTicket');





        //Route::post('restaurant/login', '\Modules\Restaurant\Http\Controllers\RestaurantController@login');
        Route::get('search', 'Tenant\SearchController@index')->name('search.index');
        Route::get('buscar/{external_id?}', 'Tenant\SearchController@index')->name('search.index');
        Route::get('search/tables', 'Tenant\SearchController@tables');
        Route::post('search', 'Tenant\SearchController@store');

        Route::get('downloads/{model}/{type}/{external_id}/{format?}', 'Tenant\DownloadController@downloadExternal')->name('tenant.download.external_id');
        Route::get('print/{model}/{external_id}/{format?}', 'Tenant\DownloadController@toPrint');
        Route::get('print-split/{code}/{format}', 'Tenant\DocumentController@toPrintSplit');
        Route::get('printticket/{model}/{external_id}/{format?}', 'Tenant\DownloadController@toTicket');
        Route::get('/exchange_rate/ecommence/{date}', 'Tenant\Api\ServiceController@exchangeRateTest');

        Route::get('production-order/print/{external_id}/{format?}', 'Tenant\ProductionOrderController@toPrint');
        Route::get('production-order/manyPdfs', 'Tenant\ProductionOrderController@manyPdfs');
        Route::get('dispatch-order/print/{external_id}/{format?}', 'Tenant\DispatchOrderController@toPrint');
        Route::get('sale-notes/change-state-delivery/{id}/{state_delivery_id}', 'Tenant\SaleNoteController@changeStateDelivery');
        Route::get('sale-notes/tables-company/{company_id}', 'Tenant\SaleNoteController@tablesCompany');
        Route::get('sale-notes/print/{external_id}/{format?}', 'Tenant\SaleNoteController@toPrint');
        Route::get('sale-notes/report-integrate-system', 'Tenant\SaleNoteController@reportIntegrateSystem');

        Route::get('sale-notes/ticket/{id}/{format?}', 'Tenant\SaleNoteController@toTicket');
        Route::get('sale-notes-states/records-states', 'Tenant\StateSaleNoteOrderController@recordsStates');
        Route::get('sale-notes-states/store', 'Tenant\StateSaleNoteOrderController@store');
        Route::get('sale-notes/change-state-sale-note-order/{id}/{state_sale_note_order_id}', 'Tenant\SaleNoteController@changeStateSaleNoteOrder');
        Route::get('purchases/print/{external_id}/{format?}', 'Tenant\PurchaseController@toPrint');
        Route::prefix('quickaccess')->group(function(){
            Route::get('/columns','Tenant\QuickAccessController@columns');
            Route::get('','Tenant\QuickAccessController@index')->name('tenant.quickaccess.index');
            Route::get('/records','Tenant\QuickAccessController@records');
            Route::get('/record/{id}','Tenant\QuickAccessController@record');
            Route::delete('/{id}','Tenant\QuickAccessController@destroy');
            Route::post('','Tenant\QuickAccessController@store');
        });
        Route::get('quotations/print/{external_id}/{format?}', 'Tenant\QuotationController@toPrint');
        Route::get('users/download-qr-virtual-store/{id?}', 'Tenant\UserController@downloadQrVirtualStore');
        Route::get('users/download-qr-store', 'Tenant\UserController@downloadQrStore');

        Route::middleware(['auth', 'redirect.module', 'locked.tenant', 'locked.user'])->group(function () {
            Route::get('info-fix/{random_string}', 'Tenant\CompanyController@downloadAllInfoFixed');

            Route::prefix('weapon-tracking')->group(function () {
                Route::get('', [WeaponTrackingController::class, 'index'])->name('tenant.weapon_tracking.index');
                Route::get('columns', [WeaponTrackingController::class, 'columns']);
                Route::get('records', [WeaponTrackingController::class, 'records']);
                Route::get('record/{id}', [WeaponTrackingController::class, 'record']);
                Route::get('export/pdf', [WeaponTrackingController::class, 'export_pdf'])->name('tenant.weapon_tracking.export_pdf');
                Route::post('', [WeaponTrackingController::class, 'store']);
                Route::delete('{id}', [WeaponTrackingController::class, 'destroy']);

            });

            Route::get('users/download-qr/{id?}/{virtual_store?}', 'Tenant\UserController@downloadQr');
            Route::prefix('mall')->group(function () {
                Route::get('', [MallController::class, 'index'])->name('tenant.mall.index');
                Route::get('columns', [MallController::class, 'columns']);
                Route::get('records', [MallController::class, 'records']);
                Route::get('filter', [MallController::class, 'filter']);
                Route::post('set_config', [MallController::class, 'set_config']);
                Route::get('get_config', [MallController::class, 'get_config']);
                Route::get('export-csv-company', [MallController::class, 'export_csv_company']);
                Route::get('export-csv-sales', [MallController::class, 'export_csv_sales']);
                Route::get('export-csv-sellers', [MallController::class, 'export_csv_sellers']);
                Route::get('export-csv-sellers-by-id', [MallController::class, 'export_csv_sellers_by_id']);
                Route::get('users', [MallController::class, 'users']);
            });

            Route::get('/users-by-warehouse/{warehouse_id}', [UserController::class, 'usersByWarehouse']);
            Route::get('/inventory-kardex/item_adjustment', [ItemController::class, 'itemAdjustment']);
            Route::get('/msg-to-pay', [CompanyController::class, 'msgToPay']);
            Route::get('update-current-company/{company_id}', function ($company_id) {
                User::setCompanyActiveId($company_id);
                return response()->json(['success' => true]);
            });
            Route::get('items/fix-descriptions-with-html-tags', [ItemController::class, 'fixDescriptionsWithHtmlTags']);
            Route::post('/test/agency/agency-dispatch', function (Request $request) {
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $path = $file->store('public/images');
                    return response()->json(['success' => true, 'path' => $path]);
                }
                return response()->json(['success' => false]);
            });
            Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
            Route::prefix('package-handler')->group(function () {
                Route::get('/', 'Tenant\PackageHandlerController@index')->name('tenant.package_handler.index');
                Route::post('/', 'Tenant\PackageHandlerController@store');
                Route::get('create/{packagehandler?}', 'Tenant\PackageHandlerController@create')->name('tenant.package_handler.create');
                Route::get('/records', 'Tenant\PackageHandlerController@records');
                Route::get('/export/excel', 'Tenant\SaleNoteController@excel');
                Route::get('/export_packages/excel', 'Tenant\PackageHandlerController@excelPackages');
                Route::get('/ticket/{id}', 'Tenant\PackageHandlerController@ticket');
                Route::get('/record/{id}', 'Tenant\PackageHandlerController@record');
                Route::get('/columns', 'Tenant\PackageHandlerController@columns');
                Route::get('/tables', 'Tenant\PackageHandlerController@tables');
                Route::get('/search/customer/{id}', 'Tenant\PackageHandlerController@searchCustomerById');
            });
            Route::prefix('supply-type-vias')->group(function () {
                Route::get('/all-records', [SupplyViaController::class, 'supplyTypeViasAllRecords']);
            });
            Route::prefix('supplies')->group(function () {
                Route::get('/', [SupplyController::class, 'index'])->name('tenant.supplies.index');
                Route::get('/persons/{type}', 'Tenant\PersonController@index')->name('tenant.supplies.persons.index');
                Route::get('/columns', [SupplyController::class, 'columns']);
                Route::get('/records', [SupplyController::class, 'records']);
                Route::get('/records-by-customer-id/{id}', [SupplyController::class, 'recordsByCustomerId']);
                Route::get('/search', [SupplyController::class, 'search']);
                Route::post('/generate-code', [SupplyController::class, 'generateCode']);
                Route::post('/', [SupplyController::class, 'store']);
                Route::get('/record/{id}', [SupplyController::class, 'show']);
                Route::put('/record/{id}', [SupplyController::class, 'update']);
                Route::delete('/record/{id}', [SupplyController::class, 'destroy']);
                Route::prefix('receipts')->group(function () {
                    Route::get('/', [SupplyReceiptController::class, 'index'])->name('tenant.supplies.receipts.index');
                    Route::get('/columns', [SupplyReceiptController::class, 'columns']);
                    Route::get('/records', [SupplyReceiptController::class, 'records']);
                    Route::get('/sectors', [SupplyReceiptController::class, 'getSectors']);
                    Route::get('/vias', [SupplyReceiptController::class, 'getSupplyVias']);
                    Route::get('/vias-by-sector/{sectorId}', [SupplyReceiptController::class, 'getSupplyViasBySector']);
                    Route::get('/export', [SupplyReceiptController::class, 'exportToExcel']);
                    Route::get('/print-massive', [SupplyReceiptController::class, 'printMassiveReceipts']);
                    Route::get('/print-individual/{id}', [SupplyReceiptController::class, 'printIndividualReceipt']);
                    Route::get('/debug', [SupplyReceiptController::class, 'debugData']);
                    Route::post('/clear-cache', [SupplyReceiptController::class, 'clearPdfCache']);
                    Route::get('/cache-stats', [SupplyReceiptController::class, 'getCacheStats']);
                    Route::post('/', [SupplyReceiptController::class, 'store']);
                    Route::get('/record/{id}', [SupplyReceiptController::class, 'show']);
                });

                Route::prefix('payments')->group(function () {

                    Route::prefix('consumption')->group(function () {
                        Route::get('/', [SupplyPaymentConsumptionController::class, 'index'])->name('tenant.supplies.payments.consumption.index');
                        Route::get('/columns', [SupplyPaymentConsumptionController::class, 'columns']);
                        Route::get('/records', [SupplyPaymentConsumptionController::class, 'records']);
                        Route::post('/', [SupplyPaymentConsumptionController::class, 'store']);
                        Route::get('/record/{id}', [SupplyPaymentConsumptionController::class, 'show']);
                    });

                    Route::prefix('others')->group(function () {
                        Route::get('/', [SupplyPaymentOtherController::class, 'index'])->name('tenant.supplies.payments.others.index');
                        Route::get('/columns', [SupplyPaymentOtherController::class, 'columns']);
                        Route::get('/records', [SupplyPaymentOtherController::class, 'records']);
                        Route::post('/', [SupplyPaymentOtherController::class, 'store']);
                        Route::get('/record/{id}', [SupplyPaymentOtherController::class, 'show']);
                    });
                });

                Route::prefix('plans')->group(function () {
                    Route::get('/', [SupplyPlanController::class, 'index'])->name('tenant.supplies.plans.index');
                    Route::get('/columns', [SupplyPlanController::class, 'columns']);
                    Route::get('/tables', [SupplyPlanController::class, 'tables']);
                    Route::get('/all-records', [SupplyPlanController::class, 'allRecords']);
                    Route::get('/records', [SupplyPlanController::class, 'records']);
                    Route::post('/', [SupplyPlanController::class, 'store']);
                    Route::get('/record/{id}', [SupplyPlanController::class, 'show']);
                    Route::put('/record/{id}', [SupplyPlanController::class, 'update']);
                    Route::delete('/record/{id}', [SupplyPlanController::class, 'destroy']);
                });

                Route::prefix('states')->group(function () {
                    Route::get('/', [SupplyStateController::class, 'index'])->name('tenant.supplies.states.index');
                    Route::get('/columns', [SupplyStateController::class, 'columns']);
                    Route::get('/records', [SupplyStateController::class, 'records']);
                    Route::post('/', [SupplyStateController::class, 'store']);
                    Route::get('/{id}', [SupplyStateController::class, 'show']);
                    Route::put('/{id}', [SupplyStateController::class, 'update']);
                    Route::delete('/{id}', [SupplyStateController::class, 'destroy']);
                });

                Route::prefix('supply-vias')->group(function () {
                    Route::get('/', [SupplyViaController::class, 'index'])->name('tenant.supplies.supply_vias.index');
                    Route::get('/columns', [SupplyViaController::class, 'columns']);
                    Route::get('/records', [SupplyViaController::class, 'records']);
                    Route::get('/all-records', [SupplyViaController::class, 'allRecords']);
                    Route::get('/search', [SupplyViaController::class, 'search']);
                    Route::get('/by-sector/{sectorId}', [SupplyViaController::class, 'getBySector']);
                    Route::post('/', [SupplyViaController::class, 'store']);
                    Route::get('/{id}', [SupplyViaController::class, 'show']);
                    Route::put('/{id}', [SupplyViaController::class, 'update']);
                    Route::delete('/{id}', [SupplyViaController::class, 'destroy']);
                });

                Route::prefix('sectors')->group(function () {
                    Route::get('/', [SectorController::class, 'index'])->name('tenant.supplies.sectors.index');
                    Route::get('/columns', [SectorController::class, 'columns']);
                    Route::get('/all-records', [SectorController::class, 'allRecords']);
                    Route::get('/records', [SectorController::class, 'records']);
                    Route::get('/search', [SectorController::class, 'search']);
                    Route::post('/', [SectorController::class, 'store']);
                    Route::get('/{id}', [SectorController::class, 'show']);
                    Route::put('/{id}', [SectorController::class, 'update']);
                    Route::delete('/{id}', [SectorController::class, 'destroy']);
                });

                Route::prefix('registers')->group(function () {
                    Route::get('/', [SupplyPlanRegisteredController::class, 'index'])->name('tenant.supplies.registers.index');
                    Route::get('/columns', [SupplyPlanRegisteredController::class, 'columns']);
                    Route::get('/records', [SupplyPlanRegisteredController::class, 'records']);
                    Route::post('/', [SupplyPlanRegisteredController::class, 'store']);
                    Route::get('/{id}', [SupplyPlanRegisteredController::class, 'show']);

                    Route::put('/{id}', [SupplyPlanRegisteredController::class, 'update']);
                    Route::delete('/{id}', [SupplyPlanRegisteredController::class, 'destroy']);
                    Route::get('/{id}/documents', [SupplyPlanRegisteredController::class, 'getDocuments']);
                    Route::post('/{id}/generate-next-document', [SupplyPlanRegisteredController::class, 'generateNextDocument']);
                    Route::get('/{id}/next-document-info', [SupplyPlanRegisteredController::class, 'getNextDocumentInfo']);
                    Route::post('/documents/{id}/complete-voided', [SupplyPlanRegisteredController::class, 'completeVoidedStatus']);
                    Route::post('/{id}/change-plan', [SupplyPlanRegisteredController::class, 'changePlan']);
                    Route::post('/generate-debt-document', [SupplyPlanRegisteredController::class, 'generateDebtDocument']);
                });

                Route::prefix('documents')->group(function () {
                    Route::get('/{id}/print', [SupplyPlanRegisteredController::class, 'printDocumentById'])->name('tenant.supplies.documents.print');
                });

                Route::prefix('solicitudes')->group(function () {
                    Route::get('/', [SupplySolicitudeController::class, 'index'])->name('tenant.supplies.solicitudes.index');
                    Route::get('/columns', [SupplySolicitudeController::class, 'columns']);
                    Route::get('/records', [SupplySolicitudeController::class, 'records']);
                    Route::get('/no-contract/records', [SupplySolicitudeController::class, 'noContractRecords']);
                    Route::post('/', [SupplySolicitudeController::class, 'store']);
                    Route::get('/record/{id}', [SupplySolicitudeController::class, 'show']);
                    Route::put('/record/{id}', [SupplySolicitudeController::class, 'update']);
                    Route::delete('/record/{id}', [SupplySolicitudeController::class, 'destroy']);
                    Route::get('/tables', [SupplySolicitudeController::class, 'tables']);
                    Route::get('/print/{id}', [SupplySolicitudeController::class, 'printSolicitude'])->name('tenant.supplies.solicitudes.print');
                    Route::get('/ticket/{id}', [SupplySolicitudeController::class, 'printTicket'])->name('tenant.supplies.solicitudes.ticket');
                    Route::post('/detail', [SupplySolicitudeController::class, 'storeDetail']);
                    Route::put('/detail-update/{id}', [SupplySolicitudeController::class, 'updateDetail']);
                    Route::get('/record/{id}', [SupplySolicitudeController::class, 'showDetail']);
                    Route::post('/consolidate/{id}', [SupplySolicitudeController::class, 'consolidate']);
                });



                Route::prefix('concepts')->group(function () {
                    Route::get('/', [SupplyConceptController::class, 'index'])->name('tenant.supplies.concepts.index');
                    Route::get('/columns', [SupplyConceptController::class, 'columns']);
                    Route::get('/records', [SupplyConceptController::class, 'records']);
                    Route::get('/all-records', [SupplyConceptController::class, 'allRecords']);
                    Route::post('/', [SupplyConceptController::class, 'store']);
                    Route::get('/record/{id}', [SupplyConceptController::class, 'show']);
                    Route::put('/record/{id}', [SupplyConceptController::class, 'update']);
                    Route::delete('/record/{id}', [SupplyConceptController::class, 'destroy']);
                    Route::get('/tables', [SupplyConceptController::class, 'tables']);
                });

                Route::prefix('contracts')->group(function () {
                    Route::get('/', [SupplyContractController::class, 'index'])->name('tenant.supplies.contracts.index');
                    Route::get('/columns', [SupplyContractController::class, 'columns']);
                    Route::get('/records', [SupplyContractController::class, 'records']);
                    Route::get('/print/{id}', [SupplyContractController::class, 'printContract'])->name('tenant.supplies.contracts.print');
                    Route::post('/', [SupplyContractController::class, 'store']);
                    Route::get('/record/{id}', [SupplyContractController::class, 'show']);
                    Route::put('/record/{id}', [SupplyContractController::class, 'update']);
                    Route::post('/record/{id}', [SupplyContractController::class, 'update']);
                    Route::delete('/record/{id}', [SupplyContractController::class, 'destroy']);
                });

                Route::prefix('offices')->group(function () {
                    Route::get('/', [SupplyOfficeController::class, 'index'])->name('tenant.supplies.offices.index');
                    Route::get('/columns', [SupplyOfficeController::class, 'columns']);
                    Route::get('/records', [SupplyOfficeController::class, 'records']);
                    Route::post('/', [SupplyOfficeController::class, 'store']);
                    Route::get('/{id}', [SupplyOfficeController::class, 'show']);
                    Route::put('/{id}', [SupplyOfficeController::class, 'update']);
                    Route::delete('/{id}', [SupplyOfficeController::class, 'destroy']);
                });

                Route::prefix('processes')->group(function () {
                    Route::get('/', [SupplyProcessController::class, 'index'])->name('tenant.supplies.processes.index');
                    Route::get('/columns', [SupplyProcessController::class, 'columns']);
                    Route::get('/records', [SupplyProcessController::class, 'records']);
                    Route::post('/', [SupplyProcessController::class, 'store']);
                    Route::get('/{id}', [SupplyProcessController::class, 'show']);
                    Route::put('/{id}', [SupplyProcessController::class, 'update']);
                    Route::delete('/{id}', [SupplyProcessController::class, 'destroy']);
                });

                Route::prefix('type-debts')->group(function () {
                    Route::get('/', [SupplyTypeDebtController::class, 'index'])->name('tenant.supplies.type_debts.index');
                    Route::get('/columns', [SupplyTypeDebtController::class, 'columns']);
                    Route::get('/records', [SupplyTypeDebtController::class, 'records']);
                    Route::post('/', [SupplyTypeDebtController::class, 'store']);
                    Route::get('/{id}', [SupplyTypeDebtController::class, 'show']);
                    Route::put('/{id}', [SupplyTypeDebtController::class, 'update']);
                    Route::delete('/{id}', [SupplyTypeDebtController::class, 'destroy']);
                });

                Route::prefix('debts')->group(function () {
                    Route::get('/excel', [SupplyDebtController::class, 'excel']);
                    Route::get('/', [SupplyDebtController::class, 'index'])->name('tenant.supplies.debts.index');
                    Route::get('/columns', [SupplyDebtController::class, 'columns']);
                    Route::get('/records', [SupplyDebtController::class, 'records']);
                    Route::get('/by-supply/{id}', [SupplyDebtController::class, 'getDebtsBySupply']);
                    Route::post('/', [SupplyDebtController::class, 'store']);
                    Route::get('/record/{id}', [SupplyDebtController::class, 'show']);
                    Route::put('/record/{id}', [SupplyDebtController::class, 'update']);
                    Route::delete('/record/{id}', [SupplyDebtController::class, 'destroy']);
                    Route::get('/print/{id}', [SupplyDebtController::class, 'printReceipt'])->name('tenant.supplies.debts.print');
                    Route::post('/consumption-previous', [SupplyDebtController::class, 'storeConsumptionPrevious']);
                    Route::post('/accumulated', [SupplyDebtController::class, 'storeAccumulated']);
                    Route::post('/colateral', [SupplyDebtController::class, 'storeColateral']);
                    Route::post('/check-duplicate', [SupplyDebtController::class, 'checkDuplicate']);
                });

                Route::prefix('advance-payments')->group(function () {
                    Route::get('/', [SupplyAdvancePaymentController::class, 'index'])->name('tenant.supplies.advance_payments.index');
                    Route::get('/columns', [SupplyAdvancePaymentController::class, 'columns']);
                    Route::get('/records', [SupplyAdvancePaymentController::class, 'records']);
                    Route::get('/search-supplies', [SupplyAdvancePaymentController::class, 'searchSupplies']);
                    Route::get('/by-supply/{id}', [SupplyAdvancePaymentController::class, 'getBySupply']);
                    Route::get('/by-period', [SupplyAdvancePaymentController::class, 'getByPeriod']);
                    Route::post('/', [SupplyAdvancePaymentController::class, 'store']);
                    Route::get('/{id}', [SupplyAdvancePaymentController::class, 'show']);
                    Route::put('/{id}', [SupplyAdvancePaymentController::class, 'update']);
                    Route::delete('/{id}', [SupplyAdvancePaymentController::class, 'destroy']);
                    Route::post('/{id}/deactivate', [SupplyAdvancePaymentController::class, 'deactivate']);
                    Route::post('/{id}/activate', [SupplyAdvancePaymentController::class, 'activate']);
                });

                Route::prefix('outages')->group(function () {
                    Route::get('/', [SupplyOutageController::class, 'index'])->name('tenant.supplies.outages.index');
                    Route::get('/columns', [SupplyOutageController::class, 'columns']);
                    Route::get('/records', [SupplyOutageController::class, 'records']);
                    Route::get('/search-supplies', [SupplyOutageController::class, 'searchSupplies']);
                    Route::post('/', [SupplyOutageController::class, 'store']);
                    Route::post('/{id}/cut', [SupplyOutageController::class, 'cut']);
                    Route::post('/{id}/reconnect', [SupplyOutageController::class, 'reconnect']);
                    Route::get('/{id}', [SupplyOutageController::class, 'show']);
                    Route::put('/{id}', [SupplyOutageController::class, 'update']);
                    Route::delete('/{id}', [SupplyOutageController::class, 'destroy']);
                });

                Route::prefix('services')->group(function () {
                    Route::get('/', [SupplyServiceController::class, 'index'])->name('tenant.supplies.services.index');
                    Route::get('/columns', [SupplyServiceController::class, 'columns']);
                    Route::get('/records', [SupplyServiceController::class, 'records']);
                    Route::post('/', [SupplyServiceController::class, 'store']);
                    Route::get('/{id}', [SupplyServiceController::class, 'show']);
                    Route::put('/{id}', [SupplyServiceController::class, 'update']);
                    Route::delete('/{id}', [SupplyServiceController::class, 'destroy']);
                });

                Route::prefix('supplies-history')->group(function () {
                    Route::get('/{supplyId}', [SupplyPlanRegisteredController::class, 'getPlanHistory']);
                });
            });
            Route::prefix('person-packers')->group(function () {
                Route::get('/', [PersonDispatcherPackerController::class, 'index_packers'])->name('tenant.person_packers.index');
                Route::post('/', [PersonDispatcherPackerController::class, 'savePacker']);
                Route::get('/columns', [PersonDispatcherPackerController::class, 'columnsPackers']);
                Route::get('/records', [PersonDispatcherPackerController::class, 'recordsPackers']);
                Route::get('/record/{id}', [PersonDispatcherPackerController::class, 'packerRecord']);
                Route::delete('/record/{id}', [PersonDispatcherPackerController::class, 'deletePacker']);
            });
            Route::prefix('person-dispatchers')->group(function () {
                Route::get('/', [PersonDispatcherPackerController::class, 'index_dispatchers'])->name('tenant.person_dispatchers.index');
                Route::post('/', [PersonDispatcherPackerController::class, 'saveDispatcher']);
                Route::get('/columns', [PersonDispatcherPackerController::class, 'columnsDispatchers']);
                Route::get('/records', [PersonDispatcherPackerController::class, 'recordsDispatchers']);
                Route::get('/record/{id}', [PersonDispatcherPackerController::class, 'dispatcherRecord']);
                Route::delete('/record/{id}', [PersonDispatcherPackerController::class, 'deleteDispatcher']);
            });
            Route::prefix('multi-companies')->group(function () {
                Route::get('/', [MultiCompanyController::class, 'index'])->name('tenant.multi_companies.index');
                Route::post('/save-companies', [MultiCompanyController::class, 'saveCompanies']);
                Route::get('/get-companies', [MultiCompanyController::class, 'getCompanies']);
                Route::get('/save-company/{company_id}', [MultiCompanyController::class, 'saveCompany']);
                Route::get('/remove-company/{website_id}', [MultiCompanyController::class, 'removeCompany']);
                Route::post('/change-default/{website_id}', [MultiCompanyController::class, 'changeDefaultCompany']);
                Route::post('/save-configuration', [MultiCompanyController::class, 'saveConfiguration']);
                Route::post('/login', [MultiCompanyController::class, 'login']);
            });
            Route::prefix('admin-keys')->group(function () {
                Route::get('/', 'Tenant\AdminKeyController@index');
                Route::post('/', 'Tenant\AdminKeyController@store');
                Route::get('/{adminKey}', 'Tenant\AdminKeyController@show');
                Route::put('/{adminKey}', 'Tenant\AdminKeyController@update');
                Route::patch('/{adminKey}/toggle-status', 'Tenant\AdminKeyController@toggleStatus');
                Route::post('/validate-key', 'Tenant\AdminKeyController@validateKey');
                Route::post('/use-key', 'Tenant\AdminKeyController@useKey');
                Route::get('/current-user/active-key/{id}', 'Tenant\AdminKeyController@getCurrentUserActiveKey');
                Route::get('/usage-logs/records', 'Tenant\AdminKeyController@getUsageLogs');
            });

            Route::prefix('document-columns')->group(function () {
                Route::get('/', 'Tenant\DocumentColumnController@index')->name('tenant.document_columns.index');
                Route::get('/records', 'Tenant\DocumentColumnController@records');
                Route::get('/record/{id}', 'Tenant\DocumentColumnController@record');
                Route::post('/', 'Tenant\DocumentColumnController@store');
                Route::post('/update-two-records', 'Tenant\DocumentColumnController@updateTwoRecords');
                Route::post('/update-two-properties', 'Tenant\DocumentColumnController@updateTwoProperties');
                Route::get('/get-font-size', 'Tenant\DocumentColumnController@getFontSize');
                Route::post('/update-font-size', 'Tenant\DocumentColumnController@updateFontSize');
            });
            Route::prefix('bill-of-exchange-pay')->group(function () {
                Route::get('/', [BillOfExchangePayController::class, 'index'])->name('tenant.bill_of_exchange_pay.index');
                Route::post('/', [BillOfExchangePayController::class, 'store']);
                // Route::get('create/{packagehandler?}', [BillOfExchangeController::class,'create'])->name('tenant.package_handler.create');
                Route::delete('/{id}', [BillOfExchangePayController::class, 'delete']);
                Route::delete('/payment/{id}', [BillOfExchangePayController::class, 'delete_payment']);
                Route::get('/records', [BillOfExchangePayController::class, 'records']);
                Route::get('/pdf/{id}', [BillOfExchangePayController::class, 'pdf']);
                Route::get('/payments/{id}', [BillOfExchangePayController::class, 'payments']);
                Route::post('/payments', [BillOfExchangePayController::class, 'store_payment']);
                Route::get('/record/{id}', [BillOfExchangePayController::class, 'record']);
                Route::post('/edit', [BillOfExchangePayController::class, 'edit']);
                Route::get('/document/{id}', [BillOfExchangePayController::class, 'document']);
                Route::get('/columns', [BillOfExchangePayController::class, 'columns']);
                Route::get('/columns', [BillOfExchangePayController::class, 'columns']);
                Route::get('/list-by-client', [BillOfExchangePayController::class, 'documentsCreditByClient']);
                Route::get('/purchases-by-client', [BillOfExchangePayController::class, 'purchasesByClient']);
                Route::get('/purchase-detail/{id}', [BillOfExchangePayController::class, 'purchaseDetail']);
                Route::post('/generate-multiple', [BillOfExchangePayController::class, 'generateMultiple']);
                Route::get('/tables', [BillOfExchangePayController::class, 'tables']);

                // Route::get('/search/customer/{id}', [BillOfExchangeController::class,'searchCustomerById']);

            });
            Route::prefix('bill-of-exchange')->group(function () {

                Route::get('/', [BillOfExchangeController::class, 'index'])->name('tenant.bill_of_exchange.index');
                Route::post('/', [BillOfExchangeController::class, 'store']);
                // Route::get('create/{packagehandler?}', [BillOfExchangeController::class,'create'])->name('tenant.package_handler.create');
                Route::delete('/{id}', [BillOfExchangeController::class, 'delete']);
                Route::delete('/payment/{id}', [BillOfExchangeController::class, 'delete_payment']);
                Route::get('/records', [BillOfExchangeController::class, 'records']);
                Route::get('/pdf/{id}', [BillOfExchangeController::class, 'pdf']);
                Route::get('/revert/{id}', [BillOfExchangeController::class, 'revert']);
                Route::get('/payments/{id}', [BillOfExchangeController::class, 'payments']);
                Route::post('/payments', [BillOfExchangeController::class, 'store_payment']);
                Route::post('/edit', [BillOfExchangeController::class, 'edit']);
                Route::get('/record/{id}', [BillOfExchangeController::class, 'record']);
                Route::get('/document/{id}', [BillOfExchangeController::class, 'document']);
                Route::get('/columns', [BillOfExchangeController::class, 'columns']);
                Route::get('/columns', [BillOfExchangeController::class, 'columns']);
                Route::get('/list-by-client', [BillOfExchangeController::class, 'documentsCreditByClient']);
                Route::get('/list-by-document/{id}', [BillOfExchangeController::class, 'documentsCreditByDocument']);
                Route::get('/tables', [BillOfExchangeController::class, 'tables']);
                // Route::get('/search/customer/{id}', [BillOfExchangeController::class,'searchCustomerById']);
                Route::get('/excel', [BillOfExchangeController::class, 'exportExcel']);
                Route::get('/pdf', [BillOfExchangeController::class, 'exportPdf']);
            });

            Route::get('condition-block-payment-methods', [ConditionBlockPaymentMethodController::class, 'records']);
            Route::post('condition-block-payment-methods', [ConditionBlockPaymentMethodController::class, 'store']);

            Route::get('sunat_purchase_sale/records/{year}', [DashboardController::class, 'sunat_purchase_sale']);
            Route::post('sunat_purchase_sale', [DashboardController::class, 'save_sunat_purchase_sale']);

            Route::post('name_document', [NameDocumentController::class, 'store']);
            Route::post('ubigeo', [PersonController::class, 'ubigeo']);
            Route::get('name_document/record', [NameDocumentController::class, 'record']);

            Route::post('name_quotations', [NameQuotationsController::class, 'store']);
            Route::get('name_quotations/record', [NameQuotationsController::class, 'record']);
            //'Tenant\SettingController@document_quotations'
            Route::get('document-quotations', [SettingController::class, 'document_quotations'])->name('tenant.document_quotations.index');


            //Route::post('login', '\Modules\Restaurant\Http\Controllers\RestaurantController@login');
            Route::post('whatsapp', 'Tenant\WhatsappController@sendwhatsapp');
            Route::post('whatsapp/pos', 'Tenant\WhatsappController@sendwhatsapppos');
            // Route::get('catalogs', 'Tenant\CatalogController@index')->name('tenant.catalogs.index');
            Route::get('list-reports', 'Tenant\SettingController@listReports');
            Route::get('list-extras', 'Tenant\SettingController@listExtras');
            Route::get('list-settings', 'Tenant\SettingController@indexSettings')->name('tenant.general_configuration.index');
            Route::get('list-banks', 'Tenant\SettingController@listBanks');
            Route::get('list-bank-accounts', 'Tenant\SettingController@listAccountBanks');
            Route::get('list-currencies', 'Tenant\SettingController@listCurrencies');
            Route::get('list-cards', 'Tenant\SettingController@listCards');
            Route::get('list-platforms', 'Tenant\SettingController@listPlatforms');
            Route::get('list-state-deliveries', 'Tenant\SettingController@listStateDeliveries');
            Route::get('list-state-technical-services', 'Tenant\SettingController@listStateTechnicalServices');
            Route::get('list-agencies', 'Tenant\SettingController@listAgenciesTransport');

            Route::get('document-names', 'Tenant\SettingController@documentNames')->name('tenant.document_names.index');
            Route::get('yape-plin-qr', 'Tenant\SettingController@YaplePlinQr')->name('tenant.yape_plin_qr.index');
            Route::get('pdf-additional-info', 'Tenant\SettingController@pdfAdditionalInfo')->name('tenant.pdf_additional_info.index');

            // PDF Additional Info API Routes
            Route::get('pdf-additional-info/records', 'Tenant\InformationAdditionalPdfController@index');
            Route::post('pdf-additional-info', 'Tenant\InformationAdditionalPdfController@store');
            Route::post('pdf-additional-info/upload-image', 'Tenant\InformationAdditionalPdfController@uploadImage');
            Route::get('pdf-additional-info/active', 'Tenant\InformationAdditionalPdfController@getActiveRecords');
            Route::get('pdf-additional-info/{id}', 'Tenant\InformationAdditionalPdfController@show');
            Route::put('pdf-additional-info/{id}', 'Tenant\InformationAdditionalPdfController@update');
            Route::delete('pdf-additional-info/{id}', 'Tenant\InformationAdditionalPdfController@destroy');
            Route::patch('pdf-additional-info/{id}/toggle', 'Tenant\InformationAdditionalPdfController@toggleActive');

            //Route::get('document-quotations', 'Tenant\SettingController@document_quotations')->name('tenant.document_quotations.index');

            Route::get('inventory-references', 'Tenant\InventoryReferenceController@index')->name('tenant.inventory_references.index');
            Route::get('inventory-references/columns', 'Tenant\InventoryReferenceController@columns');
            Route::get('inventory-references/records', 'Tenant\InventoryReferenceController@records');
            Route::get('inventory-references/record/{id}', 'Tenant\InventoryReferenceController@record');
            Route::post('inventory-references', 'Tenant\InventoryReferenceController@store');
            Route::delete('inventory-references/{id}', 'Tenant\InventoryReferenceController@destroy');


            Route::get('list-attributes', 'Tenant\SettingController@listAttributes');
            Route::get('list-detractions', 'Tenant\SettingController@listDetractions');
            Route::get('list-units', 'Tenant\SettingController@listUnits');
            Route::post('list-units/store', 'Tenant\SettingController@storeUnits');
            Route::get('list-units/pdf', 'Tenant\SettingController@listUnitsPdf');
            Route::get('list-payment-methods', 'Tenant\SettingController@listPaymentMethods');
            Route::get('list-incomes', 'Tenant\SettingController@listIncomes');
            Route::get('list-payments', 'Tenant\SettingController@listPayments');
            Route::get('list-vouchers-type', 'Tenant\SettingController@listVouchersType');
            Route::get('list-transfer-reason-types', 'Tenant\SettingController@listTransferReasonTypes');

            Route::get('advanced', 'Tenant\AdvancedController@index')->name('tenant.advanced.index')->middleware('redirect.level');

            Route::get('tasks', 'Tenant\TaskController@index')->name('tenant.tasks.index')->middleware('redirect.level');
            Route::post('tasks/commands', 'Tenant\TaskController@listsCommand');
            Route::post('tasks/tables', 'Tenant\TaskController@tables');
            Route::post('tasks', 'Tenant\TaskController@store');
            Route::delete('tasks/{task}', 'Tenant\TaskController@destroy');

            //Orders
            Route::get('orders', 'Tenant\OrderController@index')->name('tenant_orders_index');
            Route::get('orders/columns', 'Tenant\OrderController@columns');
            Route::get('orders/records', 'Tenant\OrderController@records');
            Route::get('orders/record/{order}', 'Tenant\OrderController@record');
            Route::post('orders/{order}/update', 'Tenant\OrderController@edit');
            //Route::get('orders/print/{external_id}/{format?}', 'Tenant\OrderController@toPrint');
            Route::post('statusOrder/update/', 'Tenant\OrderController@updateStatusOrders');
            Route::get('orders/pdf/{id}', 'Tenant\OrderController@pdf');

            //warehouse
            Route::post('orders/warehouse', 'Tenant\OrderController@searchWarehouse');
            Route::get('orders/tables', 'Tenant\OrderController@tables');

            Route::get('orders/tables/item/{internal_id}', 'Tenant\OrderController@item');

            //Status Orders
            Route::get('statusOrder/records', 'Tenant\StatusOrdersController@records');

            //Company
            Route::get('companies/create', 'Tenant\CompanyController@create')->name('tenant.companies.create')->middleware('redirect.level');
            Route::get('companies/tables', 'Tenant\CompanyController@tables');
            Route::get('companies/record', 'Tenant\CompanyController@record');
            Route::post('companies', 'Tenant\CompanyController@store');
            Route::post('companies/uploads', 'Tenant\CompanyController@uploadFile');
            Route::get('companies/get-send-pse', 'Tenant\CompanyController@getSendPse');
            Route::get('companies/remove-get-send-pse', 'Tenant\CompanyController@removeGetSendPse');

            Route::get('companies/info/{random_string}', 'Tenant\CompanyController@downloadAllInfo');
            Route::get('companies/info-fix/{random_string}', 'Tenant\CompanyController@downloadAllInfoFixed');
            Route::get('companies/download-all-info', 'Tenant\CompanyController@downloadAllInfoIndex')->name('tenant.companies.download_all_info');
            Route::get('companies/download-files', 'Tenant\CompanyController@downloadFiles');
            //configuracion envio documento a ""

            Route::prefix('order-concrete')->group(function () {
                Route::get('/', 'Tenant\OrderConcreteController@index')->name('tenant.order_concrete.index');
                Route::get('/records', 'Tenant\OrderConcreteController@records');
                Route::get('/create', 'Tenant\OrderConcreteController@create');
                Route::post('/prepare-data', 'Tenant\OrderConcreteController@prepareData');
                Route::post('/', 'Tenant\OrderConcreteController@store');
                Route::delete('/{id}', 'Tenant\OrderConcreteController@destroy');
                Route::get('/{id}', 'Tenant\OrderConcreteController@getData');
                Route::get('/{id}/pdf', 'Tenant\OrderConcreteController@pdf');
            });


            Route::post('companies/store-send-pse', 'Tenant\CompanyController@storeSendPse');
            Route::get('companies/record-send-pse', 'Tenant\CompanyController@recordSendPse');
            Route::post('companies/pse', 'Tenant\CompanyController@storePse');
            Route::get('companies/pse', 'Tenant\CompanyController@recordPse');

            //configuracion WhatsApp Api
            Route::post('companies/store-whatsapp-api', 'Tenant\CompanyController@storeWhatsAppApi');
            Route::get('companies/record-whatsapp-api', 'Tenant\CompanyController@recordWhatsAppApi');


            //Card Brands
            Route::get('card_brands/records', 'Tenant\CardBrandController@records');
            Route::get('card_brands/record/{card_brand}', 'Tenant\CardBrandController@record');
            Route::post('card_brands', 'Tenant\CardBrandController@store');
            Route::delete('card_brands/{card_brand}', 'Tenant\CardBrandController@destroy');

            //Configurations
            Route::post('configurations/restore-default', 'Tenant\ConfigurationController@restore_default');
            Route::post('configurations/whatsapp-document-message', 'Tenant\ConfigurationController@storeWhatsappDocumentMessage');
            Route::get('configurations/sale-notes', 'Tenant\SaleNoteController@SetAdvanceConfiguration')->name('tenant.sale_notes.configuration')->middleware('redirect.level');
            Route::post('configurations/save-bill-of-exchange-template', 'Tenant\ConfigurationController@saveBillOfExchangeTemplate');
            Route::get('configurations/get-bill-of-exchange-template', 'Tenant\ConfigurationController@getBillOfExchangeTemplate');
            Route::post('configurations/sale-notes', 'Tenant\SaleNoteController@SaveSetAdvanceConfiguration');
            Route::post('configurations/save-establishment', 'Tenant\ConfigurationController@saveEstablishment');
            Route::post('configurations/save-establishment-ticket', 'Tenant\ConfigurationController@saveEstablishmentTicket');
            Route::get('configurations/shortcuts', 'Tenant\ConfigurationController@shortcuts')->name('tenant.shortcuts.index');
            Route::post('configurations/shortcuts', 'Tenant\ConfigurationController@store_shortcuts');
            Route::get('configurations/addSeeder', 'Tenant\ConfigurationController@addSeeder');
            Route::get('configurations/preprinted/addSeeder', 'Tenant\ConfigurationController@addPreprintedSeeder');
            Route::get('configurations/getFormats', 'Tenant\ConfigurationController@getFormats');
            Route::get('configurations/preprinted/getFormats', 'Tenant\ConfigurationController@getPreprintedFormats');
            Route::get('configurations/create', 'Tenant\ConfigurationController@create')->name('tenant.configurations.create');
            Route::get('configurations/record', 'Tenant\ConfigurationController@record');
            Route::post('configurations', 'Tenant\ConfigurationController@store');
            Route::post('configurations/apiruc', 'Tenant\ConfigurationController@storeApiRuc');
            Route::post('configurations/icbper', 'Tenant\ConfigurationController@icbper');
            Route::post('configurations/changeFormat', 'Tenant\ConfigurationController@changeFormat');
            Route::get('configurations/tables', 'Tenant\ConfigurationController@tables');
            Route::get('configurations/visual_defaults', 'Tenant\ConfigurationController@visualDefaults')->name('visual_defaults');
            Route::get('configurations/visual/get_menu', 'Tenant\ConfigurationController@visualGetMenu')->name('visual_get_menu');
            Route::post('configurations/visual/set_menu', 'Tenant\ConfigurationController@visualSetMenu')->name('visual_set_menu');
            Route::post('configurations/visual_settings', 'Tenant\ConfigurationController@visualSettings')->name('visual-settings');
            Route::post('configurations/visual/upload_skin', 'Tenant\ConfigurationController@visualUploadSkin')->name('visual_upload_skin');
            Route::post('configurations/visual/delete_skin', 'Tenant\ConfigurationController@visualDeleteSkin')->name('visual_delete_skin');

            // Custom Color Themes
            Route::get('configurations/themes/custom/list', 'Tenant\ConfigurationController@getCustomThemes')->name('themes.custom.list');
            Route::post('configurations/themes/custom/save', 'Tenant\ConfigurationController@saveCustomTheme')->name('themes.custom.save');
            Route::delete('configurations/themes/custom/{id}', 'Tenant\ConfigurationController@deleteCustomTheme')->name('themes.custom.delete');
            Route::post('configurations/themes/custom/{id}/apply', 'Tenant\ConfigurationController@applyCustomTheme')->name('themes.custom.apply');

            Route::get('configurations/pdf_templates', 'Tenant\ConfigurationController@pdfTemplates')->name('tenant.advanced.pdf_templates');
            Route::get('configurations/pdf_guide_templates', 'Tenant\ConfigurationController@pdfGuideTemplates')->name('tenant.advanced.pdf_guide_templates');
            Route::get('configurations/pdf_preprinted_templates', 'Tenant\ConfigurationController@pdfPreprintedTemplates')->name('tenant.advanced.pdf_preprinted_templates');
            Route::post('configurations/uploads', 'Tenant\ConfigurationController@uploadFile');
            Route::post('configurations/upload_background', 'Tenant\ConfigurationController@uploadBackground');
            Route::post('configurations/upload_order_purchase_logo', 'Tenant\ConfigurationController@uploadOrderPurchaseLogo');
            Route::post('configurations/preprinted/generateDispatch', 'Tenant\ConfigurationController@generateDispatch');
            Route::get('configurations/preprinted/{template}', 'Tenant\ConfigurationController@show');
            Route::get('configurations/change-mode', 'Tenant\ConfigurationController@changeMode')->name('settings.change_mode');

            Route::get('configurations/templates/ticket/refresh', 'Tenant\ConfigurationController@refreshTickets');
            Route::get('configurations/pdf_templates/ticket', 'Tenant\ConfigurationController@pdfTicketTemplates')->name('tenant.advanced.pdf_ticket_templates');
            Route::get('configurations/templates/ticket/records', 'Tenant\ConfigurationController@getTicketFormats');
            Route::post('configurations/templates/ticket/update', 'Tenant\ConfigurationController@changeTicketFormat');
            Route::get('configurations/apiruc', 'Tenant\ConfigurationController@apiruc');

            Route::post('configurations/pdf-footer-images', 'Tenant\ConfigurationController@pdfFooterImages');
            Route::get('configurations/get-pdf-footer-images', 'Tenant\ConfigurationController@getPdfFooterImages');
            Route::post('configurations/update-with-igv-product-report-cash', 'Tenant\ConfigurationController@updateWithIgvProductReportCash');
            Route::get('configurations/taxo-role', 'Tenant\ConfigurationController@getAppConfigurationTaxoRole');
            Route::post('configurations/taxo', 'Tenant\ConfigurationController@updateAppConfigurationTaxo');
            Route::post('configurations/taxo-role', 'Tenant\ConfigurationController@updateAppConfigurationTaxoRole');
            //Certificates
            Route::get('certificates/record', 'Tenant\CertificateController@record');
            Route::post('certificates/uploads', 'Tenant\CertificateController@uploadFile');
            Route::delete('certificates', 'Tenant\CertificateController@destroy');

            //Establishments
            Route::get('establishments', 'Tenant\EstablishmentController@index')->name('tenant.establishments.index');
            Route::get('establishments/create', 'Tenant\EstablishmentController@create');
            Route::get('establishments/tables', 'Tenant\EstablishmentController@tables');
            Route::get('establishments/record/{establishment}', 'Tenant\EstablishmentController@record');
            Route::post('establishments', 'Tenant\EstablishmentController@store');
            Route::post('establishments/remove-image/{type}', 'Tenant\EstablishmentController@removeImage');
            Route::get('establishments/records', 'Tenant\EstablishmentController@records');
            Route::get('establishments/records-all', 'Tenant\EstablishmentController@recordsAll');
            Route::post('establishments/active', 'Tenant\EstablishmentController@active');
            Route::delete('establishments/{establishment}', 'Tenant\EstablishmentController@destroy');

            //Bank Accounts
            Route::get('bank_accounts', 'Tenant\BankAccountController@index')->name('tenant.bank_accounts.index');
            Route::get('bank_accounts/records', 'Tenant\BankAccountController@records');
            Route::get('bank_accounts/create', 'Tenant\BankAccountController@create');
            Route::get('bank_accounts/tables', 'Tenant\BankAccountController@tables');
            Route::get('bank_accounts/record/{bank_account}', 'Tenant\BankAccountController@record');
            Route::post('bank_accounts', 'Tenant\BankAccountController@store');
            Route::delete('bank_accounts/{bank_account}', 'Tenant\BankAccountController@destroy');
            Route::post('bank_accounts/change-show-in-pos', 'Tenant\BankAccountController@changeShowInPos');

            //Series
            Route::get('series/records/{establishment}/{document_type?}', 'Tenant\SeriesController@records');
            Route::get('series/records-without-establishment/{document_type?}', 'Tenant\SeriesController@recordsWithoutEstablishment');
            Route::get('series/create', 'Tenant\SeriesController@create');
            Route::get('series/tables', 'Tenant\SeriesController@tables');
            Route::post('series', 'Tenant\SeriesController@store');
            Route::delete('series/{series}', 'Tenant\SeriesController@destroy');

            //Users
            Route::get('users', 'Tenant\UserController@index')->name('tenant.users.index');
            Route::get('users/create', 'Tenant\UserController@create')->name('tenant.users.create');
            Route::get('users/tables', 'Tenant\UserController@tables');
            Route::post('users/logout-user', 'Tenant\UserController@logoutUser');
            // get-users-open-cash
            Route::get('users/get-users-open-cash', 'Tenant\UserController@getUsersOpenCash');
            Route::post('users/change-cash', 'Tenant\UserController@changeCash');
            Route::get('users/record/{user}', 'Tenant\UserController@record');
            Route::post('users', 'Tenant\UserController@store');
            Route::post('users/token/{user}', 'Tenant\UserController@regenerateToken');
            Route::get('users/records', 'Tenant\UserController@records');
            Route::post('users/lock', 'Tenant\UserController@lock');
            Route::post('users/change-password', 'Tenant\UserController@changePassword');
            Route::post('users/change-establishment', 'Tenant\UserController@changeEstablishment');
            Route::post('users/unlock', 'Tenant\UserController@unlock');
            Route::post('users/change-filters-set-items', 'Tenant\UserController@changeUserFiltersSetItems');
            Route::post('users/change-filters-set-items-type', 'Tenant\UserController@changeUserFiltersSetItemsType');
            Route::get('users/get-filters-set-items', 'Tenant\UserController@getUserFiltersSetItems');
            Route::get('users/records-lite', 'Tenant\UserController@records_lite');
            Route::delete('users/{user}', 'Tenant\UserController@destroy');
            Route::get('/sellers', 'Tenant\UserController@getSellers');
            Route::get('users/getCustomers', 'Tenant\UserController@getCustomers');
            Route::get('/users/sellers-and-admins', 'Tenant\UserController@getSellersAndAdmins')->name('tenant.users.sellers_and_admins');
            Route::get('users/technicians', 'Tenant\UserController@technicians');






            //ChargeDiscounts
            Route::get('charge_discounts/{type}', 'Tenant\ChargeDiscountController@index')->name('tenant.charge_discounts.index');
            Route::get('charge_discounts/records/{type}', 'Tenant\ChargeDiscountController@records');
            Route::get('charge_discounts/create', 'Tenant\ChargeDiscountController@create');
            Route::get('charge_discounts/tables/{type}', 'Tenant\ChargeDiscountController@tables');
            Route::get('charge_discounts/record/{charge}', 'Tenant\ChargeDiscountController@record');
            Route::post('charge_discounts', 'Tenant\ChargeDiscountController@store');
            Route::delete('charge_discounts/{charge}', 'Tenant\ChargeDiscountController@destroy');

            //Items Ecommerce
            Route::get('items_ecommerce', 'Tenant\ItemController@index_ecommerce')->name('tenant.items_ecommerce.index');

            //Items
            Route::get('items', 'Tenant\ItemController@index')->name('tenant.items.index')->middleware('redirect.level');
            Route::get('services', 'Tenant\ItemController@indexServices')->name('tenant.services')->middleware('redirect.level');
            Route::get('items/columns', 'Tenant\ItemController@columns');
            Route::post('items/add_item_warehouse', 'Tenant\ItemController@addItemWarehouse');
            Route::post('items/attributes', 'Tenant\ItemController@attributes');
            Route::post('items/update-observation-apportionment', 'Tenant\ItemController@updateObservationApportionment');
            Route::get('items/formats/import', 'Tenant\ItemController@getFormatImport');
            Route::delete('items/item-supply/{id}', 'Tenant\ItemController@deleteSupply');
            Route::get('items/formats/items-update-prices-warehouses', 'Tenant\ItemController@templateUpdatePricesWarehouses');
            Route::get('items/formats/items-update-prices-presentation', 'Tenant\ItemController@templateUpdatePricesPresentation');
            Route::get('items/formats/items-update-prices-person-type', 'Tenant\ItemController@templateUpdatePricesPersonType');
            Route::get('items/records', 'Tenant\ItemController@records');
            Route::get('items/erase/{item_id}', 'Tenant\ItemController@erase');
            Route::get('items/details/{item_id}', 'Tenant\ItemController@details');
            Route::get('items/tables', 'Tenant\ItemController@tables');
            Route::get('items/record/{item}', 'Tenant\ItemController@record');
            Route::post('items', 'Tenant\ItemController@store');
            Route::post('items-input', 'Tenant\ItemController@storeInput');
            Route::get('check-restrict-stock/{id}', 'Tenant\ItemController@restrictStock');
            Route::delete('items/{item}', 'Tenant\ItemController@destroy');
            Route::delete('items/item-unit-type/{item}', 'Tenant\ItemController@destroyItemUnitType');
            Route::post('items/import', 'Tenant\ItemController@import');
            Route::post('items/catalog', 'Tenant\ItemController@catalog');
            Route::get('items/import/tables', 'Tenant\ItemController@tablesImport');
            Route::post('items/upload', 'Tenant\ItemController@upload');
            Route::post('items/update_stock', 'Tenant\ItemController@updateStock');
            Route::post('items/visible_store', 'Tenant\ItemController@visibleStore');
            Route::post('items/update_label_color', 'Tenant\ItemController@updateLabelColor');
            Route::post('items/duplicate', 'Tenant\ItemController@duplicate');
            Route::get('items/disable/{item}', 'Tenant\ItemController@disable');
            Route::get('items/enable/{item}', 'Tenant\ItemController@enable');
            Route::get('items/images/{item}', 'Tenant\ItemController@images');
            Route::get('items/images/delete/{id}', 'Tenant\ItemController@delete_images');
            Route::get('items/export', 'Tenant\ItemController@export')->name('tenant.items.export');
            Route::get('items/export_migration', 'Tenant\ItemController@export_migration');
            Route::get('items/export_migration_v3', 'Tenant\ItemController@export_migration_v3');
            Route::get('items/export/wp', 'Tenant\ItemController@exportWp')->name('tenant.items.export.wp');
            Route::get('items/export/digemid', 'Tenant\ItemController@exportDigemid');
            Route::get('items/export/digemid-csv', 'Tenant\ItemController@exportDigemidCsv');
            Route::get('items/search-items', 'Tenant\ItemController@searchItems');
            Route::get('items/search/item/{item}', 'Tenant\ItemController@searchItemById');
            Route::get('items/{item}/lots', 'Tenant\ItemController@getItemLots');
            Route::get('items/item/tables-index', 'Tenant\ItemController@item_tables_index');
            Route::get('items/item/tables', 'Tenant\ItemController@item_tables');
            Route::get('items/export/barcode', 'Tenant\ItemController@exportBarCode')->name('tenant.items.export.barcode');
            Route::get('items/export/extra_atrributes/PDF', 'Tenant\ItemController@downloadExtraDataPdf');
            Route::get('items/export/extra_atrributes/XLSX', 'Tenant\ItemController@downloadExtraDataItemsExcel');
            Route::get('items/export/barcode_full', 'Tenant\ItemController@exportBarCodeFull');
            Route::get('items/export/barcode/print', 'Tenant\ItemController@printBarCode')->name('tenant.items.export.barcode.print');
            Route::get('items/export/barcode/print_x', 'Tenant\ItemController@printBarCodeX')->name('tenant.items.export.barcode.print.x');
            Route::get('items/export/barcode/print_0', 'Tenant\ItemController@printBarCode0')->name('tenant.items.export.barcode.print.0');
            Route::get('items/export/barcode/print_2_b', 'Tenant\ItemController@printBarCode2d')->name('tenant.items.export.barcode.print.2_b');
            Route::get('items/export/barcode/print_d', 'Tenant\ItemController@printBarCodeD')->name('tenant.items.export.barcode.print.d');
            Route::get('items/export/barcode/print_e', 'Tenant\ItemController@printBarCodeE')->name('tenant.items.export.barcode.print.e');
            Route::get('items/export/barcode/last', 'Tenant\ItemController@itemLast')->name('tenant.items.last');
            Route::post('get-items', 'Tenant\ItemController@getAllItems');

            //Persons
            Route::prefix('persons')->group(function () {
                /**
                 *persons/columns
                 *persons/tables
                 *persons/{type}
                 *persons/{type}/records
                 *persons/
                 *persons/{person}
                 *persons/import
                 *persons/enabled/{type}/{person}
                 *persons/{type}/exportation
                 */

                Route::get('/test-zone/{person_id}', 'Tenant\PersonController@testZone');
                Route::get('/generate-document-number-to-no-doc', 'Tenant\PersonController@generateDocumentNumberToNoDoc');
                Route::post('/update-info', 'Tenant\PersonController@updateInfo');
                Route::get('/last-no-document', 'Tenant\PersonController@getLastDocument');
                Route::get('/columns', 'Tenant\PersonController@columns');
                Route::get('/tables', 'Tenant\PersonController@tables');
                Route::get('/drivers', 'Tenant\PersonController@drivers')->name('tenant.persons_drivers.index');
                Route::get('/{type}', 'Tenant\PersonController@index')->name('tenant.persons.index');
                Route::get('/{type}/records', 'Tenant\PersonController@records');
                Route::get('/record/{person}', 'Tenant\PersonController@record');
                Route::post('', 'Tenant\PersonController@store');
                Route::delete('/{person}', 'Tenant\PersonController@destroy');
                Route::post('/import', 'Tenant\PersonController@import');
                Route::get('/enabled/{type}/{person}', 'Tenant\PersonController@enabled');
                Route::get('/{type}/export_migration', 'Tenant\PersonController@export_migration');
                Route::get('/{type}/exportation', 'Tenant\PersonController@export')->name('tenant.persons.export');
                Route::get('/export/barcode/print', 'Tenant\PersonController@printBarCode')->name('tenant.persons.export.barcode.print');
                Route::get('/barcode/{item}', 'Tenant\PersonController@generateBarcode');
                Route::get('/search/{barcode}', 'Tenant\PersonController@getPersonByBarcode');
                Route::get('/search/customers-by-id/{id}', 'Tenant\PersonController@searchCustomersById');
                Route::get('accumulated-points/{id}', 'Tenant\PersonController@getAccumulatedPoints');
            });

            //cancha
            Route::prefix('canchas')->group(function () {
                Route::get('/', [CanchaController::class, 'index'])->name('tenant.canchas.index');
                Route::get('/records', [CanchaController::class, 'records']);
                Route::get('/columns', [CanchaController::class, 'columns']);
                Route::get('/record/{id}', [CanchaController::class, 'record'])->name('tenant.canchas.show');
                Route::post('/', [CanchaController::class, 'store'])->name('tenant.canchas.store');
                Route::delete('/{id}', [CanchaController::class, 'destroy'])->name('tenant.canchas.destroy');

                Route::post('/tipo', [CanchaController::class, 'storeCanchasTipo'])->name('tenant.canchas_tipo.store');
                Route::get('/tipo/filter', [CanchaController::class, 'filterCanchasTipo'])->name('tenant.canchas_tipo.filter');
                Route::delete('/canchas_tipo/{id}', [CanchaController::class, 'destroyCanchasTipo'])->name('tenant.canchas_tipo.destroy');
                Route::post('/anular/{id}', [CanchaController::class, 'anular'])->name('tenant.canchas.anular');
                Route::get('/canchas_tipo/{id}/edit', [CanchaController::class, 'editCanchasTipo'])->name('tenant.canchas_tipo.edit');
                Route::put('/canchas_tipo/{id}', [CanchaController::class, 'updateCanchasTipo'])->name('tenant.canchas_tipo.update');
                Route::prefix('types')->group(function () {
                    Route::get('/', [CanchaController::class, 'indexTypes'])->name('tenant.canchas_types.index');
                    Route::get('/records', [CanchaController::class, 'recordsTypes']);
                    Route::get('/columns', [CanchaController::class, 'columnsTypes']);
                    Route::get('/record/{id}', [CanchaController::class, 'recordTypes']);
                    Route::post('/', [CanchaController::class, 'storeTypes']);
                    Route::delete('/{id}', [CanchaController::class, 'destroyTypes']);
                });
            });

            Route::prefix('cupones')->group(function () {
                Route::get('/', [CuponesController::class, 'index'])->name('tenant.coupons.index');
                Route::get('/create', [CuponesController::class, 'create'])->name('tenant.coupons.create');
                Route::post('/', [CuponesController::class, 'store'])->name('tenant.coupons.store');
                Route::get('/{id}', [CuponesController::class, 'show'])->name('tenant.coupons.show');
                Route::put('/{id}', [CuponesController::class, 'update'])->name('tenant.coupons.update');
                Route::delete('/{id}', [CuponesController::class, 'destroy'])->name('tenant.coupons.destroy');

                Route::get('/api/coupons', [CuponesController::class, 'getCoupons'])->name('tenant.coupons.api.index');
                Route::get('/{id}/edit', [CuponesController::class, 'edit'])->name('tenant.coupons.edit');
            });


            Route::prefix('comanda')->group(function () {
                Route::get('/', [ComandaController::class, 'index'])->name('tenant.comanda.index');
                Route::get('/pedido/create', [ComandaController::class, 'createPedido'])->name('tenant.pedidos.create');
                Route::post('/pedido', [ComandaController::class, 'storePedido'])->name('tenant.pedidos.store');
                Route::get('/rol/create', [ComandaController::class, 'createRol'])->name('tenant.roles.create');
                Route::post('/rol', [ComandaController::class, 'storeRol'])->name('tenant.roles.store');
                Route::get('/personal/create', [ComandaController::class, 'createPersonal'])->name('tenant.personal.create');
                Route::post('/personal', [ComandaController::class, 'storePersonal'])->name('tenant.personal.store');
                Route::delete('/personal/{id}', [ComandaController::class, 'destroyPersonal'])->name('tenant.personal.destroy');
                Route::delete('/rol/{id}', [ComandaController::class, 'destroyRol'])->name('tenant.roles.destroy');
            });


            Route::get('customers/top', 'Tenant\Reporttopcliente@index')->name('tenant.reports.customers.top');
            Route::get('customers-top/export', 'Tenant\Reporttopcliente@export')->name('tenant.reports.customers.export');
            Route::get('customers-top/records', 'Tenant\Reporttopcliente@records')->name('tenant.reports.customers.export');
            Route::get('/customers/check-customer-field-to-create-sale-note/{id}',[PersonController::class, 'checkCustomerFieldToCreateSaleNote']);



            Route::get('reports/items/top', [Reportitemstop::class, 'index'])->name('tenant.reports.items.top');
            Route::get('reports/items/export', [Reportitemstop::class, 'export'])->name('tenant.reports.items.export');



            //Documents

            // Route::post('documents-recurrence', 'Tenant\DocumentController@documentRecurrence');
            Route::prefix('documents-recurrence')->group(function () {
                Route::post('', [DocumentRecurrenceController::class, 'store']);
                Route::get('', [DocumentRecurrenceController::class, 'index'])->name('tenant.documents_recurrence.index');
                Route::get('/records', [DocumentRecurrenceController::class, 'records']);
                Route::get('/record/{id}', [DocumentRecurrenceController::class, 'record']);
                Route::get('/columns', [DocumentRecurrenceController::class, 'columns']);
                Route::get('/records-recurrence-emitted', [DocumentRecurrenceController::class, 'recordsRecurrenceEmitted']);
                Route::get('/records-recurrence', [DocumentRecurrenceController::class, 'recordsRecurrenceEmitted']);
                Route::post('/update-recurrence', [DocumentRecurrenceController::class, 'updateRecurrenceEmission']);
                Route::delete('/{id}', [DocumentRecurrenceController::class, 'destroy']);
            });
            Route::get('documents/data-table/items', 'Tenant\DocumentController@getDataTableItem');
            Route::get('documents/message/{document_id}', 'Tenant\DocumentController@message_whatsapp');
            Route::get('documents/summaries-totals', 'Tenant\DocumentController@summariesTotals');
            Route::get('documents/image-from-ticket/{document_id}', 'Tenant\DocumentController@getImageFromTicket');
            Route::get('documents/auditor_history', 'Tenant\DocumentController@auditorHistory');
            Route::get('documents/change-state-delivery/{document_id}/{state_delivery_id}', 'Tenant\DocumentController@changeStateDelivery');
            Route::get('documents/concar', 'Tenant\DocumentController@exportConcar');
            Route::get('documents/table-export', 'Tenant\DocumentController@exportTable');
            Route::get('documents/system', 'Tenant\DocumentController@exportSystem');
            Route::post('documents/check-series', 'Tenant\DocumentController@checkSeries');
            Route::get('documents/massive-note/documents', 'Tenant\DocumentController@documentForMassiveNote');
            Route::get('documents/to-delete', 'Tenant\DocumentController@getToDelete');
            Route::post('documents/deletes', 'Tenant\DocumentController@deletes');
            Route::get('documents/person_packers_dispatchers', 'Tenant\DocumentController@personPackersDispatchers');
            Route::get('documents/change-person-packer/{document_id}/{person_packer_id}', 'Tenant\DocumentController@changePersonPacker');
            Route::get('documents/change-person-dispatcher/{document_id}/{person_dispatcher_id}', 'Tenant\DocumentController@changePersonDispatcher');
            Route::get('documents/unpaid/{customer_id}', 'Tenant\DocumentController@hasUnpaid');
            Route::get('documents/tables-company/{company_id}', 'Tenant\DocumentController@tablesCompany');
            Route::get('documents/update-user/{user_id}/{document_id}', 'Tenant\DocumentController@updateUser');
            Route::get('documents/change_sire/{id}/{appendix}', 'Tenant\DocumentController@changeSire');
            Route::get('documents/check_pse/{id}', 'Tenant\DocumentController@checkPse');
            Route::get('documents/voided_pse/{id}', 'Tenant\DocumentController@anulatePse');
            Route::get('documents/voided_check_pse/{id}', 'Tenant\DocumentController@anulatePseCheck');
            Route::get('documents/json_pse/{id}', 'Tenant\DocumentController@jsonPse');
            Route::get('documents/voided_pdf/{id}', 'Tenant\DocumentController@voidedPdf');
            Route::post('documents/categories', 'Tenant\DocumentController@storeCategories');
            Route::post('documents/brands', 'Tenant\DocumentController@storeBrands');
            Route::get('documents/ind/{id}', 'Tenant\DocumentController@sendInd');
            Route::get('documents/get-document/{id}', 'Tenant\DocumentController@getDocument');
            Route::get('documents/change_state/{state_id}/{document_id}', 'Tenant\DocumentController@change_state');
            Route::get('documents/res/{id}', 'Tenant\DocumentController@sendRes');
            Route::get('documents/search/customers', 'Tenant\DocumentController@searchCustomers');
            Route::get('documents/search/customers-limit', 'Tenant\DocumentController@searchCustomersLimit');

            Route::get('documents/search/suppliers', 'Tenant\DocumentController@searchSuppliers');
            Route::get('documents/search/customers-lite', 'Tenant\DocumentController@searchCustomersLite');
            Route::get('documents/copy/{id}', 'Tenant\DocumentController@copy');
            Route::get('documents/send_pse/{id}', 'Tenant\DocumentController@sendPse');

            Route::get('documents/search/customer/{id}', 'Tenant\DocumentController@searchCustomerById');
            Route::get('documents/search/externalId/{external_id}', 'Tenant\DocumentController@searchExternalId');

            Route::get('documents', 'Tenant\DocumentController@index')->name('tenant.documents.index')->middleware(['redirect.level', 'tenant.internal.mode']);
            Route::get('documents/columns', 'Tenant\DocumentController@columns');
            Route::get('documents/records', 'Tenant\DocumentController@records');
            Route::get('documents/recordsTotal', 'Tenant\DocumentController@recordsTotal');
            Route::get('documents/create', 'Tenant\DocumentController@create')->name('tenant.documents.create')->middleware(['redirect.level', 'tenant.internal.mode']);
            Route::get('documents/create_tensu', 'Tenant\DocumentController@create_tensu')->name('tenant.documents.create_tensu');
            Route::get('documents/{id}/edit', 'Tenant\DocumentController@edit')->middleware(['redirect.level', 'tenant.internal.mode']);
            Route::get('documents/{id}/show', 'Tenant\DocumentController@show');

            Route::get('documents/tables', 'Tenant\DocumentController@tables');
            Route::get('documents/tables-critical', 'Tenant\DocumentController@tablesCritical');
            Route::post('documents/series-critical', 'Tenant\DocumentController@getSeriesCritial');
            Route::get('documents/tables-secondary', 'Tenant\DocumentController@tablesSecondary');
            Route::get('documents/duplicate/{id}', 'Tenant\DocumentController@duplicate');
            Route::get('documents/record/{document}', 'Tenant\DocumentController@record');
            Route::post('documents', 'Tenant\DocumentController@store');
            Route::post('documents/{id}/update', 'Tenant\DocumentController@update');
            Route::get('documents/send/{document}', 'Tenant\DocumentController@send');
            // Route::get('documents/remove/{document}', 'Tenant\DocumentController@remove');
            // Route::get('documents/consult_cdr/{document}', 'Tenant\DocumentController@consultCdr');
            Route::get('documents/note_nv/{sale_note_id}', 'Tenant\NoteController@createNv');
            Route::post('documents/email', 'Tenant\DocumentController@email');
            Route::get('documents/note/search-no-used', 'Tenant\NoteController@searchNoUsed');
            Route::get('documents/note/{document}', 'Tenant\NoteController@create');
            Route::get('documents/note_other', 'Tenant\NoteController@createOther');
            Route::get('documents/note/record/{document}', 'Tenant\NoteController@record');
            Route::get('documents/item/tables', 'Tenant\DocumentController@item_tables');
            Route::get('documents/table/{table}', 'Tenant\DocumentController@table');
            Route::get('documents/re_store/{document}', 'Tenant\DocumentController@reStore');
            Route::get('documents/locked_emission', 'Tenant\DocumentController@messageLockedEmission');
            Route::get('documents/note/has-documents/{document}', 'Tenant\NoteController@hasDocuments');
            Route::get('documents/verify-cdr-and-tags/{document}', 'Tenant\DocumentController@verifyCdrAndTags');
            Route::get('documents/massive-verify-cdr-and-tags', 'Tenant\DocumentController@massiveVerifyCdrAndTags');
            Route::get('documents/massive-update-date-issue-to-cdr', 'Tenant\DocumentController@massiveUpdateDateIssueToCdr');
            Route::get('documents/update-date-issue-to-cdr/{document}', 'Tenant\DocumentController@updateDateIssueToCdr');


            Route::get('document_payments/records/{document_id}', 'Tenant\DocumentPaymentController@records');
            Route::get('document_payments/document/{document_id}', 'Tenant\DocumentPaymentController@document');
            Route::get('document_payments/tables', 'Tenant\DocumentPaymentController@tables');
            Route::get('document_payments/fee/document_fee/{document_fee_id}', 'Tenant\DocumentPaymentController@document_fee');
            Route::get('document_payments/fee/document_fee/records/{document_fee_id}', 'Tenant\DocumentPaymentController@records_fee');
            Route::post('document_payments/fee', 'Tenant\DocumentPaymentController@store_fee');
            Route::post('document_payments', 'Tenant\DocumentPaymentController@store');
            Route::get('document_payments/record/{document_payment}', 'Tenant\DocumentPaymentController@record');
            Route::post('document_payments/update/{document_payment}', 'Tenant\DocumentPaymentController@updateRecord');
            Route::delete('document_payments/{document_payment}', 'Tenant\DocumentPaymentController@destroy');
            Route::get('document_payments/initialize_balance', 'Tenant\DocumentPaymentController@initialize_balance');
            Route::get('document_payments/report/{start}/{end}/{report}', 'Tenant\DocumentPaymentController@report');
            Route::post('/cuenta/upload_payment_files', [ClientPaymentController::class, 'uploadPaymentFiles']);




            Route::get('documents/send_server/{document}/{query?}', 'Tenant\DocumentController@sendServer');
            Route::get('documents/check_server/{document}', 'Tenant\DocumentController@checkServer');
            Route::get('documents/change_to_registered_status/{document}', 'Tenant\DocumentController@changeToRegisteredStatus');

            Route::post('documents/import', 'Tenant\DocumentController@import');
            Route::post('documents/import_second_format', 'Tenant\DocumentController@importTwoFormat');
            Route::get('documents/data_table', 'Tenant\DocumentController@data_table');
            Route::get('documents/data_table_update_massive', 'Tenant\DocumentController@data_table_update_massive');
            Route::get('documents/update_massive', 'Tenant\DocumentController@records_massive_update');
            Route::post('documents/update_massive', 'Tenant\DocumentController@update_massive');
            Route::get('documents/payments/excel/{month}/{anulled}', 'Tenant\DocumentController@report_payments')->name('tenant.document.payments.excel');
            Route::get('documents/payments-complete', 'Tenant\DocumentController@report_payments');


            Route::post('documents/import_excel_format', 'Tenant\DocumentController@importExcelFormat');
            Route::get('documents/import_excel_tables', 'Tenant\DocumentController@importExcelTables');


            Route::delete('documents/delete_document/{document_id}', 'Tenant\DocumentController@destroyDocument');
            Route::get('documents/kill/{document_id}', 'Tenant\DocumentController@killDocument');

            Route::get('documents/retention/{document}', 'Tenant\DocumentController@retention');
            Route::post('documents/retention', 'Tenant\DocumentController@retentionStore');
            Route::post('documents/retention/upload', 'Tenant\DocumentController@retentionUpload');
            Route::get('documents/message/{document_id}', 'Tenant\DocumentController@message_whatsapp');
            Route::get('documents/{id}/items', 'Tenant\DocumentController@items');

            //Contingencies
            Route::get('contingencies', 'Tenant\ContingencyController@index')->name('tenant.contingencies.index')->middleware('redirect.level', 'tenant.internal.mode');
            Route::get('contingencies/columns', 'Tenant\ContingencyController@columns');
            Route::get('contingencies/records', 'Tenant\ContingencyController@records');
            Route::get('contingencies/create', 'Tenant\ContingencyController@create')->name('tenant.contingencies.create');

            //Summaries
            Route::get('summaries', 'Tenant\SummaryController@index')->name('tenant.summaries.index')->middleware('redirect.level', 'tenant.internal.mode');
            Route::get('summaries/records', 'Tenant\SummaryController@records');
            Route::post('summaries/documents', 'Tenant\SummaryController@documents');
            Route::post('summaries', 'Tenant\SummaryController@store');
            Route::get('summaries/status/{summary}', 'Tenant\SummaryController@status');
            Route::get('summaries/columns', 'Tenant\SummaryController@columns');
            Route::delete('summaries/{summary}', 'Tenant\SummaryController@destroy');
            Route::get('summaries/record/{summary}', 'Tenant\SummaryController@record');
            Route::get('summaries/regularize/{summary}', 'Tenant\SummaryController@regularize');
            Route::get('summaries/cancel-regularize/{summary}', 'Tenant\SummaryController@cancelRegularize');
            Route::get('summaries/tables', 'Tenant\SummaryController@tables');

            //Voided
            Route::get('voided', 'Tenant\VoidedController@index')->name('tenant.voided.index')->middleware('redirect.level', 'tenant.internal.mode');
            Route::get('voided/columns', 'Tenant\VoidedController@columns');
            Route::get('voided/records', 'Tenant\VoidedController@records');
            Route::post('voided', 'Tenant\VoidedController@store');
            //            Route::get('voided/download/{type}/{voided}', 'Tenant\VoidedController@download')->name('tenant.voided.download');
            Route::get('voided/status/{voided}', 'Tenant\VoidedController@status');
            Route::get('voided/status_masive', 'Tenant\VoidedController@status_masive');

            Route::delete('voided/{voided}', 'Tenant\VoidedController@destroy');
            //            Route::get('voided/ticket/{voided_id}/{group_id}', 'Tenant\VoidedController@ticket');

            //Retentions
            Route::get('retentions', 'Tenant\RetentionController@index')->name('tenant.retentions.index');
            Route::get('retentions/columns', 'Tenant\RetentionController@columns');
            Route::get('retentions/records', 'Tenant\RetentionController@records');
            Route::get('retentions/create', 'Tenant\RetentionController@create')->name('tenant.retentions.create');
            Route::get('retentions/tables', 'Tenant\RetentionController@tables');
            Route::get('retentions/send/{retention}', 'Tenant\RetentionController@send');
            Route::get('retentions/record/{retention}', 'Tenant\RetentionController@record');
            Route::post('retentions', 'Tenant\RetentionController@store');
            Route::delete('retentions/{retention}', 'Tenant\RetentionController@destroy');
            Route::get('retentions/document/tables', 'Tenant\RetentionController@document_tables');
            Route::get('retentions/table/{table}', 'Tenant\RetentionController@table');
            Route::get('retentions/voided/{retention}', 'Tenant\RetentionVoidedController@store');
            Route::get('retentions/voided/status/{voided}', 'Tenant\RetentionVoidedController@status');

            /** Dispatches
             * dispatches
             * dispatches/columns
             * dispatches/records
             * dispatches/create/{document?}/{type?}/{dispatch?}
             * dispatches/tables
             * dispatches
             * dispatches/record/{id}
             * dispatches/sendSunat/{document}
             * dispatches/email
             * dispatches/generate/{sale_note}
             * dispatches/record/{id}/tables
             * dispatches/record/{id}/set-document-id
             * dispatches/search/customers
             * dispatches/search/customer/{id}
             */
            Route::prefix('order-delivery')->group(function () {
                Route::get('', 'Tenant\DispatchController@index')
                    ->name('tenant.order_delivery.index')
                    ->defaults('type', 'internal');
                Route::get('/create/{document?}/{type?}/{dispatch?}', 'Tenant\DispatchController@create')
                    ->defaults('type', 'internal');
                Route::get('create_new/{table}/{id}', 'Tenant\DispatchController@createNew')
                    ->defaults('type', 'internal');
            });

            Route::get('dispatches-transfers/tables', 'Tenant\DispatchController@getTablesTransfers');
            Route::prefix('dispatches')->group(function () {
                Route::get('auditor_history', 'Tenant\DispatchController@auditorHistory');
                Route::get('change_state/{state_id}/{document_id}', 'Tenant\DispatchController@change_state');
                Route::get('', 'Tenant\DispatchController@index')->name('tenant.dispatches.index');
                Route::get('/internal-voided/{id}', 'Tenant\DispatchController@internalVoided');
                Route::get('/columns', 'Tenant\DispatchController@columns');
                Route::get('/records', 'Tenant\DispatchController@records');
                Route::get('/view-dispatch/{id}', 'Tenant\DispatchController@ticket_dispatch');
                Route::get('/export-excel', 'Tenant\DispatchController@exportExcel');
                Route::get('/create/{document?}/{type?}/{dispatch?}', 'Tenant\DispatchController@create');
                Route::post('/tables', 'Tenant\DispatchController@tables');
                Route::post('', 'Tenant\DispatchController@store');
                Route::get('/record/{id}', 'Tenant\DispatchController@record');
                Route::post('/sendSunat/{document}', 'Tenant\DispatchController@sendDispatchToSunat');
                Route::post('/email', 'Tenant\DispatchController@email');
                Route::get('/check_pse/{id}', 'Tenant\DispatchController@download_file');
                Route::get('/send_pse/{id}', 'Tenant\DispatchController@send_pse');
                Route::get('/json_pse/{id}', 'Tenant\DispatchController@json_pse');
                Route::get('/generate/{sale_note}', 'Tenant\DispatchController@generate');
                Route::get('/record/{id}/tables', 'Tenant\DispatchController@generateDocumentTables');
                Route::post('/record/{id}/set-document-id', 'Tenant\DispatchController@setDocumentId');
                Route::get('/client/{id}', 'Tenant\DispatchController@dispatchesByClient');
                Route::post('/client-transfers', 'Tenant\DispatchController@dispatchesByClientTransfers');
                Route::post('/items', 'Tenant\DispatchController@getItemsFromDispatches');
                Route::post('/getDocumentType', 'Tenant\DispatchController@getDocumentTypeToDispatches');
                Route::get('/data_table', 'Tenant\DispatchController@data_table');
                Route::get('/search/customers', 'Tenant\DispatchController@searchCustomers');
                Route::get('/search/customer/{id}', 'Tenant\DispatchController@searchClientById');
                Route::post('/status_ticket', 'Tenant\Api\DispatchController@statusTicket');
                Route::get('create_new/{table}/{id}', 'Tenant\DispatchController@createNew');
                Route::get('/get_origin_addresses/{establishment_id}', 'Tenant\DispatchController@getOriginAddresses');
                Route::get('/get_delivery_addresses/{person_id}', 'Tenant\DispatchController@getDeliveryAddresses');
                Route::get('message/{document_id}', 'Tenant\DispatchController@message_whatsapp');
                Route::post('preview', 'Tenant\DispatchController@preview');
            });

            Route::prefix('dispatch_carrier')->group(function () {
                Route::post('preview', 'Tenant\DispatchCarrierController@preview');
                Route::get('auditor_history', 'Tenant\DispatchCarrierController@auditorHistory');
                Route::get('change_state/{state_id}/{document_id}', 'Tenant\DispatchCarrierController@change_state');
                Route::get('', 'Tenant\DispatchCarrierController@index')->name('tenant.dispatch_carrier.index');
                Route::get('/columns', 'Tenant\DispatchCarrierController@columns');
                Route::get('/records', 'Tenant\DispatchCarrierController@records');
                Route::get('/export-excel', 'Tenant\DispatchCarrierController@ExportExcel');
                Route::get('/create/{document?}/{type?}/{dispatch?}', 'Tenant\DispatchCarrierController@create');
                Route::post('/tables', 'Tenant\DispatchCarrierController@tables');
                Route::post('', 'Tenant\DispatchCarrierController@store');
                Route::get('/record/{id}', 'Tenant\DispatchCarrierController@record');
                Route::post('/sendSunat/{document}', 'Tenant\DispatchCarrierController@sendDispatchToSunat');
                Route::post('/email', 'Tenant\DispatchCarrierController@email');
                Route::get('/generate/{sale_note}', 'Tenant\DispatchCarrierController@generate');
                Route::get('/record/{id}/tables', 'Tenant\DispatchCarrierController@generateDocumentTables');
                Route::post('/record/{id}/set-document-id', 'Tenant\DispatchCarrierController@setDocumentId');
                Route::get('/client/{id}', 'Tenant\DispatchCarrierController@dispatchesByClient');
                Route::post('/items', 'Tenant\DispatchCarrierController@getItemsFromDispatches');
                Route::post('/getDocumentType', 'Tenant\DispatchCarrierController@getDocumentTypeToDispatches');
                Route::get('/data_table', 'Tenant\DispatchCarrierController@data_table');
                Route::get('/search/customers', 'Tenant\DispatchCarrierController@searchCustomers');
                Route::get('/search/customer/{id}', 'Tenant\DispatchCarrierController@searchClientById');
                Route::post('/status_ticket', 'Tenant\Api\DispatchCarrierController@statusTicket');
                Route::get('create_new/{table}/{id}', 'Tenant\DispatchCarrierController@createNew');
                Route::get('/get_origin_addresses/{establishment_id}', 'Tenant\DispatchCarrierController@getOriginAddresses');
                Route::get('/get_delivery_addresses/{person_id}', 'Tenant\DispatchCarrierController@getDeliveryAddresses');
            });

            Route::get('customers/listById/{id}', 'Tenant\PersonController@clientsForGenerateCPEById');
            Route::get('customers/list', 'Tenant\PersonController@clientsForGenerateCPE');
            Route::get('suppliers/list', 'Tenant\PersonController@suppliersForGenerateCPE');
            Route::get('reports/consistency-documents', 'Tenant\ReportConsistencyDocumentController@index')->name('tenant.consistency-documents.index')->middleware('tenant.internal.mode');
            Route::post('reports/consistency-documents/lists', 'Tenant\ReportConsistencyDocumentController@lists');

            Route::post('options/delete_documents', 'Tenant\OptionController@deleteDocuments');
            Route::post('options/delete_items', 'Tenant\OptionController@delete_items');
            Route::post('options/flush_cache', 'Tenant\OptionController@flushCache');

            // apiperu no usa estas rutas - revisar
            Route::get('services/ruc/{number}', 'Tenant\Api\ServiceController@ruc');
            Route::get('services/dni/{number}', 'Tenant\Api\ServiceController@dni');
            Route::post('services/exchange_rate', 'Tenant\Api\ServiceController@exchange_rate');
            Route::post('services/search_exchange_rate', 'Tenant\Api\ServiceController@searchExchangeRateByDate');
            Route::get('services/exchange_rate/{date}', 'Tenant\Api\ServiceController@exchangeRateTest');

            //BUSQUEDA DE DOCUMENTOS
            // Route::get('busqueda', 'Tenant\SearchController@index')->name('search');
            // Route::post('busqueda', 'Tenant\SearchController@index')->name('search');

            //Codes
            Route::get('codes/records', 'Tenant\Catalogs\CodeController@records');
            Route::get('codes/tables', 'Tenant\Catalogs\CodeController@tables');
            Route::get('codes/record/{code}', 'Tenant\Catalogs\CodeController@record');
            Route::post('codes', 'Tenant\Catalogs\CodeController@store');
            Route::delete('codes/{code}', 'Tenant\Catalogs\CodeController@destroy');

            //Units
            Route::get('unitmeasure/records', 'Tenant\UnitTypeController@tables');
            Route::get('unit_types/records', 'Tenant\UnitTypeController@records');
            Route::get('unit_types/record/{code}', 'Tenant\UnitTypeController@record');
            Route::post('unit_types', 'Tenant\UnitTypeController@store');
            Route::delete('unit_types/{code}', 'Tenant\UnitTypeController@destrofy');
            Route::get('unit_types/show_symbol/{code}', 'Tenant\UnitTypeController@show_symbol');

            //Transfer Reason Types
            Route::get('transfer-reason-types/records', 'Tenant\TransferReasonTypeController@records');
            Route::get('transfer-reason-types/record/{code}', 'Tenant\TransferReasonTypeController@record');
            Route::post('transfer-reason-types', 'Tenant\TransferReasonTypeController@store');
            Route::delete('transfer-reason-types/{code}', 'Tenant\TransferReasonTypeController@destroy');

            //Detractions
            Route::get('detraction_types/records', 'Tenant\DetractionTypeController@records');
            Route::get('detraction_types/tables', 'Tenant\DetractionTypeController@tables');
            Route::get('detraction_types/record/{code}', 'Tenant\DetractionTypeController@record');
            Route::post('detraction_types', 'Tenant\DetractionTypeController@store');
            Route::delete('detraction_types/{code}', 'Tenant\DetractionTypeController@destroy');

            //Banks
            Route::get('banks/records', 'Tenant\BankController@records');
            Route::get('banks/record/{bank}', 'Tenant\BankController@record');
            Route::post('banks', 'Tenant\BankController@store');
            Route::delete('banks/{bank}', 'Tenant\BankController@destroy');

            //Exchange Rates
            Route::get('exchange_rates/records', 'Tenant\ExchangeRateController@records');
            Route::post('exchange_rates', 'Tenant\ExchangeRateController@store');

            //Currency Types
            Route::get('exchange_currency', 'Tenant\ExchangeCurrencyController@index')->name('tenant.exchange_currency.index');
            Route::post('exchange_currency', 'Tenant\ExchangeCurrencyController@store');
            Route::get('exchange_currency/tables', 'Tenant\ExchangeCurrencyController@tables');
            Route::get('exchange_currency/records', 'Tenant\ExchangeCurrencyController@records');
            Route::get('exchange_currency/record/{id}', 'Tenant\ExchangeCurrencyController@record');
            Route::get('exchange_currency/{date}/{currency_id}', 'Tenant\ExchangeCurrencyController@exchange_date');

            Route::get('currency_types/records', 'Tenant\CurrencyTypeController@records');
            Route::get('currency_types/record/{currency_type}', 'Tenant\CurrencyTypeController@record');
            Route::post('currency_types', 'Tenant\CurrencyTypeController@store');
            Route::delete('currency_types/{currency_type}', 'Tenant\CurrencyTypeController@destroy');

            //Perceptions
            Route::get('perceptions', 'Tenant\PerceptionController@index')->name('tenant.perceptions.index');
            Route::get('perceptions/columns', 'Tenant\PerceptionController@columns');
            Route::get('perceptions/records', 'Tenant\PerceptionController@records');
            Route::get('perceptions/create', 'Tenant\PerceptionController@create')->name('tenant.perceptions.create');
            Route::get('perceptions/tables', 'Tenant\PerceptionController@tables');
            Route::get('perceptions/record/{perception}', 'Tenant\PerceptionController@record');
            Route::post('perceptions', 'Tenant\PerceptionController@store');
            Route::delete('perceptions/{perception}', 'Tenant\PerceptionController@destroy');
            Route::get('perceptions/document/tables', 'Tenant\PerceptionController@document_tables');
            Route::get('perceptions/table/{table}', 'Tenant\PerceptionController@table');

            //Tribute Concept Type
            Route::get('tribute_concept_types/records', 'Tenant\TributeConceptTypeController@records');
            Route::get('tribute_concept_types/record/{id}', 'Tenant\TributeConceptTypeController@record');
            Route::post('tribute_concept_types', 'Tenant\TributeConceptTypeController@store');
            Route::delete('tribute_concept_types/{id}', 'Tenant\TributeConceptTypeController@destroy');

            //purchases
            Route::get('purchases', 'Tenant\PurchaseController@index')->name('tenant.purchases.index');
            Route::post('purchases/exist', 'Tenant\PurchaseController@existPurchase');
            Route::get('purchases/columns', 'Tenant\PurchaseController@columns');
            Route::get('purchases/records', 'Tenant\PurchaseController@records');
            Route::post('purchases/save-apportionment-items', 'Tenant\PurchaseController@saveApportionmentItems');
            Route::get('purchases/create/{purchase_order_id?}', 'Tenant\PurchaseController@create')->name('tenant.purchases.create');
            Route::get('purchases/tables', 'Tenant\PurchaseController@tables');
            Route::get('purchases/table/{table}', 'Tenant\PurchaseController@table');
            Route::post('purchases', 'Tenant\PurchaseController@store');
            Route::post('purchases/update', 'Tenant\PurchaseController@update');
            Route::get('purchases/record/{document}', 'Tenant\PurchaseController@record');
            Route::get('purchases/edit/{id}', 'Tenant\PurchaseController@edit');
            Route::get('purchases/anular/{id}', 'Tenant\PurchaseController@anular');
            Route::post('purchases/guide/{purchase}', 'Tenant\PurchaseController@processGuides');
            Route::post('purchases/guide-file/upload', 'Tenant\PurchaseController@uploadAttached');
            Route::post('purchases/guide-file/upload', 'Tenant\PurchaseController@uploadAttached');
            Route::get('purchases/guides-file/download-file/{purchase}/{filename}', 'Tenant\PurchaseController@downloadGuide');
            Route::post('purchases/save_guide/{purchase}', 'Tenant\PurchaseController@processGuides');
            Route::get('purchases/delete/{id}', 'Tenant\PurchaseController@delete');
            Route::post('purchases/import', 'Tenant\PurchaseController@import');
            // Route::get('purchases/print/{external_id}/{format?}', 'Tenant\PurchaseController@toPrint');
            Route::get('purchases/search-items', 'Tenant\PurchaseController@searchItems');
            Route::get('purchases/pdf', 'Tenant\PurchaseController@pdf');
            Route::get('purchases/excel', 'Tenant\PurchaseController@excel');

            Route::get('purchases/search/item/{item}', 'Tenant\PurchaseController@searchItemById');
            Route::post('purchases/search/purchase_order', 'Tenant\PurchaseController@searchPurchaseOrder');
            // Route::get('purchases/item_resource/{id}', 'Tenant\PurchaseController@itemResource');

            // Route::get('documents/send/{document}', 'Tenant\DocumentController@send');
            // Route::get('documents/consult_cdr/{document}', 'Tenant\DocumentController@consultCdr');
            // Route::post('documents/email', 'Tenant\DocumentController@email');
            // Route::get('documents/note/{document}', 'Tenant\NoteController@create');
            Route::get('purchases/item/tables', 'Tenant\PurchaseController@item_tables');
            // Route::get('documents/table/{table}', 'Tenant\DocumentController@table');

            Route::delete('purchases/destroy_purchase_item/{purchase_item}', 'PurchaseController@destroy_purchase_item');

            Route::get('purchases-responsible/records', [PurchaseResponsibleLicenseController::class, 'responsible_records']);
            Route::post('purchases-responsible', [PurchaseResponsibleLicenseController::class, 'store_responsible']);
            Route::get('purchases-responsible/record/{id}', [PurchaseResponsibleLicenseController::class, 'responsible_record']);
            Route::get('purchases-license/records', [PurchaseResponsibleLicenseController::class, 'license_records']);
            Route::post('purchases-license', [PurchaseResponsibleLicenseController::class, 'store_license']);
            Route::post('purchases-license/record/{id}', [PurchaseResponsibleLicenseController::class, 'license_record']);
            //quotations
            Route::prefix('quotations-technicians')->group(function () {
                Route::get('', 'Tenant\QuotationTechnicianController@index')->name('tenant.quotations_technicians.index')->middleware('redirect.level');
                Route::get('columns', 'Tenant\QuotationTechnicianController@columns');
                Route::get('all-records', 'Tenant\QuotationTechnicianController@allRecords');
                Route::get('records', 'Tenant\QuotationTechnicianController@records');
                Route::get('create', 'Tenant\QuotationTechnicianController@create')->name('tenant.quotations_technicians.create')->middleware('redirect.level');
                Route::get('edit/{id}', 'Tenant\QuotationTechnicianController@edit')->middleware('redirect.level');
                Route::get('tables', 'Tenant\QuotationTechnicianController@tables');
                Route::post('', 'Tenant\QuotationTechnicianController@store');
                Route::get('record/{id}', 'Tenant\QuotationTechnicianController@record');
                Route::post('', 'Tenant\QuotationTechnicianController@store');
                Route::delete('{id}', 'Tenant\QuotationTechnicianController@destroy');
            });

            //quotations technicians

            Route::get('quotations', 'Tenant\QuotationController@index')->name('tenant.quotations.index')->middleware('redirect.level');
            Route::get('quotations/columns', 'Tenant\QuotationController@columns');
            Route::get('quotations/records', 'Tenant\QuotationController@records');
            Route::get('quotations/create/{saleOpportunityId?}', 'Tenant\QuotationController@create')->name('tenant.quotations.create')->middleware('redirect.level');
            Route::get('quotations/edit/{id}', 'Tenant\QuotationController@edit')->middleware('redirect.level');
            Route::get('quotations/{id}/items', 'Tenant\QuotationController@items');
            Route::get('quotations/state-type/{state_type_id}/{id}', 'Tenant\QuotationController@updateStateType');
            Route::get('quotations/filter', 'Tenant\QuotationController@filter');
            Route::get('quotations/tables', 'Tenant\QuotationController@tables');
            Route::get('quotations/by-client-id/{id}', 'Tenant\QuotationController@byClientId');
            Route::get('quotations/table/{table}', 'Tenant\QuotationController@table');
            Route::post('quotations', 'Tenant\QuotationController@store');
            Route::post('quotations/update', 'Tenant\QuotationController@update');
            Route::get('quotations/record/{quotation}', 'Tenant\QuotationController@record');
            Route::get('quotations/anular/{id}', 'Tenant\QuotationController@anular');
            Route::get('quotations/item/tables', 'Tenant\QuotationController@item_tables');
            Route::get('quotations/option/tables/{id?}', 'Tenant\QuotationController@option_tables');
            Route::get('quotations/search/customers', 'Tenant\QuotationController@searchCustomers');
            Route::get('quotations/search/customer/{id}', 'Tenant\QuotationController@searchCustomerById');
            Route::get('quotations/download/{external_id}/{format?}', 'Tenant\QuotationController@download');
            Route::get('quotations/message/{document_id}', 'Tenant\QuotationController@message_whatsapp');
            // Route::get('quotations/print/{external_id}/{format?}', 'Tenant\QuotationController@toPrint');
            Route::post('quotations/email', 'Tenant\QuotationController@email');
            Route::post('quotations/duplicate', 'Tenant\QuotationController@duplicate');
            Route::get('quotations/record2/{quotation}', 'Tenant\QuotationController@record2');
            Route::get('quotations/changed/{quotation}', 'Tenant\QuotationController@changed');
            Route::post('quotations/change-description/{quotation}', 'Tenant\QuotationController@changed_description');

            Route::get('quotations/search-items', 'Tenant\QuotationController@searchItems');
            Route::get('quotations/search/item/{item}', 'Tenant\QuotationController@searchItemById');
            Route::get('quotations/item-warehouses/{item}', 'Tenant\QuotationController@itemWarehouses');
            //production-orders
            Route::prefix('message-integrate-system')
                ->group(function () {
                    Route::get('/', 'Tenant\MessageIntegrateSystemController@index')
                        ->name('tenant.message_integrate_system.index');
                    Route::get('/columns', 'Tenant\MessageIntegrateSystemController@columns');
                    Route::get('/records', 'Tenant\MessageIntegrateSystemController@records');
                    Route::get('/record/{id}', 'Tenant\MessageIntegrateSystemController@record');
                    Route::post('/', 'Tenant\MessageIntegrateSystemController@store');
                });
            //production-orders
            Route::prefix('agency')->group(function () {
                Route::get('columns', 'Tenant\AgencyController@columns');
                Route::get('records', 'Tenant\AgencyController@records');
                Route::get('record/{agency}', 'Tenant\AgencyController@record');
                Route::post('', 'Tenant\AgencyController@store');
                Route::post('/agency-dispatch', 'Tenant\AgencyController@saveAgencyDispatch');
                Route::get('/agency-dispatch/{record}', 'Tenant\AgencyController@getAgencyDispatch');
                Route::delete('/agency-dispatch/{record}', 'Tenant\AgencyController@deleteAgencyDispatch');
            });
            Route::prefix('integrate-system')
                ->group(function () {
                    Route::get('user-types', 'Tenant\IntegrateSystemController@userTypes');
                });
            Route::prefix('production-order')->group(function () {
                Route::get('', 'Tenant\ProductionOrderController@index')->name('tenant.production_order.index')->middleware('redirect.level');
                Route::get('index-inventory', 'Tenant\ProductionOrderController@index_inventory')->name('tenant.production_order.index_inventory')->middleware('redirect.level');
                Route::get('columns', 'Tenant\ProductionOrderController@columns');
                Route::get('states', 'Tenant\ProductionOrderController@states');
                Route::get('tables', 'Tenant\ProductionOrderController@tables');
                Route::post('change-status-many', 'Tenant\ProductionOrderController@changeStatusMany');
                Route::get('columns2', 'Tenant\ProductionOrderController@columns2');
                Route::get('search/customers', 'Tenant\ProductionOrderController@searchCustomers');
                Route::get('search/customer/{id}', 'Tenant\ProductionOrderController@searchCustomerById');
                Route::get('create/{dispatchorder?}', 'Tenant\ProductionOrderController@create')->name('tenant.production_order.create');
                Route::get('records', 'Tenant\ProductionOrderController@records');
                Route::post('', 'Tenant\ProductionOrderController@store');
                Route::get('record2/{order_production_id}', 'Tenant\ProductionOrderController@record2');
                Route::get('paymentdestinations/{userid?}', 'Tenant\ProductionOrderController@paymentdestinations');
                Route::get('users', 'Tenant\ProductionOrderController@users');
                Route::get('responsibles', 'Tenant\ProductionOrderController@responsibles');
                Route::get('set-responsible/{production_order_id}/{user_id}', 'Tenant\ProductionOrderController@setResponsible');
                Route::post('generate/{sale_note_id}', 'Tenant\ProductionOrderController@generateFromSaleNote');
                Route::get('records', 'Tenant\ProductionOrderController@records');
                Route::get('record/{production_order_id}', 'Tenant\ProductionOrderController@record');
                Route::get('change-state/{production_order_id}/{state_id}', 'Tenant\ProductionOrderController@changeState');
                Route::get('downloadExternal/{external_id}/{format?}', 'Tenant\ProductionOrderController@downloadExternal');

                Route::prefix('inventory-review')->group(function () {
                    Route::get('', 'Tenant\InventoryReviewProductionOrdenController@index')->name('tenant.inventory-review-production-orden.index');
                    Route::get('filters', 'Tenant\InventoryReviewProductionOrdenController@filters');
                    Route::get('records', 'Tenant\InventoryReviewProductionOrdenController@records');
                    Route::get('records-paginate', 'Tenant\InventoryReviewProductionOrdenController@recordsPaginate');
                    Route::get('records-paginate-items-id-picking', 'Tenant\InventoryReviewProductionOrdenController@recordsPaginateItemIdsPicking');
                    Route::get('records-paginate-items-id', 'Tenant\InventoryReviewProductionOrdenController@recordsPaginateItemIds');
                    Route::post('export', 'Tenant\InventoryReviewProductionOrdenController@export');
                    Route::prefix('download')->group(function () {
                        Route::get('pdf', 'Tenant\InventoryReviewProductionOrdenController@downloadPdf');
                        Route::get('pdf-landscape', 'Tenant\InventoryReviewProductionOrdenController@downloadPdfLandscape');
                        Route::get('pdf-ticket', 'Tenant\InventoryReviewProductionOrdenController@downloadPdfTicket');
                        Route::get('xlsx', 'Tenant\InventoryReviewProductionOrdenController@downloadExcel');
                    });
                });
            });
            Route::prefix('production-order-payments')->group(function () {
                Route::get('document/{production_order_id}', 'Tenant\ProductionOrderPaymentController@document');
                Route::get('records/{production_order}', 'Tenant\ProductionOrderPaymentController@records');
            });
            //dispatch-orders
            Route::prefix('dispatch-order')->group(function () {
                Route::get('test-mail', 'Tenant\DispatchOrderController@testMail');
                Route::get('{type}/print/{external_id}/ticket', 'Tenant\DispatchOrderController@toPrintDispatchOrder');
                Route::get('', 'Tenant\DispatchOrderController@index')->name('tenant.dispatch_order.index')->middleware('redirect.level');
                Route::get('/list', 'Tenant\DispatchOrderController@index_list')->name('tenant.dispatch_order.index_list')->middleware('redirect.level');
                Route::post('/waze-url', 'Tenant\DispatchOrderController@getWazeUrl');
                Route::get('/search', 'Tenant\DispatchOrderController@getDispatchOrder');
                Route::get('/get-route-items/{id}', 'Tenant\DispatchOrderController@getRouteItems');
                Route::get('/get-route', 'Tenant\DispatchOrderController@getRoutes');
                Route::post('/save-route', 'Tenant\DispatchOrderController@saveRoute');
                Route::get('/pdf-route/{id}/{format}', 'Tenant\DispatchOrderController@pdfRoute');
                Route::get('columns', 'Tenant\DispatchOrderController@columns');
                Route::get('columns2', 'Tenant\DispatchOrderController@columns2');
                Route::get('states', 'Tenant\DispatchOrderController@states');
                Route::get('tables', 'Tenant\DispatchOrderController@tables');
                Route::get('search/customers', 'Tenant\DispatchOrderController@searchCustomers');
                Route::get('search/customer/{id}', 'Tenant\DispatchOrderController@searchCustomerById');
                Route::get('create/{dispatchorder?}', 'Tenant\DispatchOrderController@create')->name('tenant.dispatch_order.create');
                Route::get('records', 'Tenant\DispatchOrderController@records');
                Route::post('', 'Tenant\DispatchOrderController@store');
                Route::get('record2/{dispatch_order_id}', 'Tenant\DispatchOrderController@record2');
                Route::get('paymentdestinations/{userid?}', 'Tenant\DispatchOrderController@paymentdestinations');
                Route::get('responsibles', 'Tenant\DispatchOrderController@responsibles');
                Route::get('users', 'Tenant\DispatchOrderController@users');
                Route::get('set-responsible/{dispatch_order_id}/{user_id}', 'Tenant\DispatchOrderController@setResponsible');
                Route::post('generate/{dispatch_order_id}', 'Tenant\DispatchOrderController@generateFromProductionOrder');
                Route::get('records', 'Tenant\DispatchOrderController@records');
                Route::get('record/{dispatch_order_id}', 'Tenant\DispatchOrderController@record');
                Route::get('change-state/{dispatch_order_id}/{state_id}', 'Tenant\DispatchOrderController@changeState');
                Route::get('downloadExternal/{external_id}/{format?}', 'Tenant\DispatchOrderController@downloadExternal');
                Route::get('get-route/{id}', 'Tenant\DispatchOrderController@getRoute');
            });
            Route::prefix('dispatch-order-payments')->group(function () {
                Route::get('document/{dispatch_order_id}', 'Tenant\DispatchOrderPaymentController@document');
                Route::get('records/{dispatch_order}', 'Tenant\DispatchOrderPaymentController@records');
            });
            //sale-notes

            Route::get('sale-notes', 'Tenant\SaleNoteController@index')->name('tenant.sale_notes.index')->middleware('redirect.level');
            Route::post('sale-notes/deletes', 'Tenant\SaleNoteController@deletes');
            Route::get('sale-notes/to-delete', 'Tenant\SaleNoteController@getToDelete');
            Route::get('sale-notes/change-person-packer/{document_id}/{person_packer_id}', 'Tenant\SaleNoteController@changePersonPacker');
            Route::get('sale-notes/change-person-dispatcher/{document_id}/{person_dispatcher_id}', 'Tenant\SaleNoteController@changePersonDispatcher');
            Route::get('sale-notes/columns', 'Tenant\SaleNoteController@columns');
            Route::get('sale-notes/columns2', 'Tenant\SaleNoteController@columns2');
            Route::get('sale-notes/paymentdestinations/{userid?}', 'Tenant\SaleNoteController@paymentdestinations');
            Route::get('sale-notes/records', 'Tenant\SaleNoteController@records');

            Route::get('sale-notes/totals', 'Tenant\SaleNoteController@totals');
            // Route::get('sale-notes/create', 'Tenant\SaleNoteController@create')->name('tenant.sale_notes.create');
            Route::get('sale-notes/create/{salenote?}', 'Tenant\SaleNoteController@create')->name('tenant.sale_notes.create')->middleware('redirect.level');
            Route::get('sale-notes/receipt/{id}', 'Tenant\SaleNoteController@receipt');

            Route::get('sale-notes/tables', 'Tenant\SaleNoteController@tables');
            Route::post('sale-notes/UpToOther', 'Tenant\SaleNoteController@EnviarOtroSitio');
            Route::post('sale-notes/getUpToOther', 'Tenant\SaleNoteController@getSaleNoteToOtherSite');
            Route::post('sale-notes/urlUpToOther', 'Tenant\SaleNoteController@getSaleNoteToOtherSiteUrl');
            Route::post('sale-notes/duplicate', 'Tenant\SaleNoteController@duplicate');
            Route::post('sale-notes/next-payment', 'Tenant\SaleNoteController@nextPayment');
            Route::get('sale-notes/table/{table}', 'Tenant\SaleNoteController@table');
            Route::post('sale-notes', 'Tenant\SaleNoteController@store');
            Route::get('sale-notes/record/{salenote}', 'Tenant\SaleNoteController@record');
            Route::get('sale-notes/item/tables', 'Tenant\SaleNoteController@item_tables');
            Route::get('sale-notes/search/customers-limit', 'Tenant\SaleNoteController@searchCustomersLimit');
            Route::get('sale-notes/search/customers', 'Tenant\SaleNoteController@searchCustomers');
            Route::get('sale-notes/search/customer/{id}', 'Tenant\SaleNoteController@searchCustomerById');
            // Route::get('sale-notes/print/{external_id}/{format?}', 'Tenant\SaleNoteController@toPrint');
            Route::get('sale-notes/change-state-payment/{salenote}/{stateEnum}', 'Tenant\SaleNoteController@changeStatePayment');
            Route::post('sale-notes/send-state-payment-email', 'Tenant\SaleNoteController@sendStatePaymentEmail');
            Route::get('sale-notes/record2/{salenote}', 'Tenant\SaleNoteController@record2');
            Route::get('sale-notes/option/tables/{id?}', 'Tenant\SaleNoteController@option_tables');
            Route::get('sale-notes/changed/{salenote}', 'Tenant\SaleNoteController@changed');
            Route::post('sale-notes/email', 'Tenant\SaleNoteController@email');
            Route::post('sale-notes/send_email', 'Tenant\SaleNoteController@sendEmail');
            Route::get('sale-notes/print-a5/{sale_note_id}/{format}', 'Tenant\SaleNotePaymentController@toPrint');
            Route::get('sale-notes/dispatches', 'Tenant\SaleNoteController@dispatches');
            Route::delete('sale-notes/destroy_sale_note_item/{sale_note_item}', 'Tenant\SaleNoteController@destroy_sale_note_item');
            Route::get('sale-notes/search-items', 'Tenant\SaleNoteController@searchItems');
            Route::get('sale-notes/search/item/{item}', 'Tenant\SaleNoteController@searchItemById');
            Route::get('sale-notes/list-by-client', 'Tenant\SaleNoteController@saleNotesByClient');
            Route::get('sale-notes/list-by-transport-format', 'Tenant\SaleNoteController@saleNotesByTransportFormat');
            Route::post('sale-notes/items', 'Tenant\SaleNoteController@getItemsFromNotes');
            Route::get('sale-notes/generate-format', 'Tenant\SaleNoteController@generateFormat');
            Route::get('sale-notes/config-group-items', 'Tenant\SaleNoteController@getConfigGroupItems');
            Route::get('sale-notes/get-sellers', 'Tenant\SaleNoteController@getSellers');
            Route::post('sale_note_payments/discount', 'Tenant\SaleNotePaymentController@discount');
            Route::get('sale_note_payments/detail/{sale_note_payment_id}', 'Tenant\SaleNotePaymentController@getDetailPrePayment');
            Route::get('sale_note_payments/get_document_prepayments/{sale_note_id}', 'Tenant\SaleNotePaymentController@getPrepayment');
            Route::get('sale_note_payments/records/{sale_note}', 'Tenant\SaleNotePaymentController@records');
            Route::get('sale_note_payments/document/{sale_note}', 'Tenant\SaleNotePaymentController@document');
            Route::get('sale_note_payments/document_suscription/{sale_note}', 'Tenant\SaleNotePaymentController@documentSuscription');
            Route::get('sale_note_payments/tables', 'Tenant\SaleNotePaymentController@tables');
            Route::post('sale_note_payments', 'Tenant\SaleNotePaymentController@store');
            Route::post('sale_note_payments/full-suscription', 'Tenant\SaleNotePaymentController@storeFullSuscriptionPayment');
            Route::get('sale_note_payments/record/{sale_note_payment}', 'Tenant\SaleNotePaymentController@record');
            Route::post('sale_note_payments/update/{sale_note_payment}', 'Tenant\SaleNotePaymentController@updateRecord');
            Route::delete('sale_note_payments/{sale_note_payment}', 'Tenant\SaleNotePaymentController@destroy');

            Route::post('sale-notes/enabled-concurrency', 'Tenant\SaleNoteController@enabledConcurrency');

            Route::get('sale-notes/anulate/{id}', 'Tenant\SaleNoteController@anulate');

            Route::get('sale-notes/downloadExternal/{external_id}/{format?}', 'Tenant\SaleNoteController@downloadExternal');

            // Auditoría - Historial de cambios
            Route::get('audits/history', 'Tenant\AuditController@history');

            Route::post('sale-notes/transform-data-order', 'Tenant\SaleNoteController@transformDataOrder');
            Route::post('sale-notes/items-by-ids', 'Tenant\SaleNoteController@getItemsByIds');
            Route::post('sale-notes/delete-relation-invoice', 'Tenant\SaleNoteController@deleteRelationInvoice');
            Route::get('sale-notes/kill/{id}', 'Tenant\SaleNoteController@killDocument');
            Route::get('sale-notes/message/{document_id}', 'Tenant\SaleNoteController@message_whatsapp');

            Route::prefix('warranty_document')->group(function () {
                Route::get('', 'Tenant\WarrantyDocumentController@index')->name('tenant.warranty_document.index');
                Route::get('columns', 'Tenant\WarrantyDocumentController@columns');
                Route::get('records', 'Tenant\WarrantyDocumentController@records');
                Route::get('filter', 'Tenant\WarrantyDocumentController@filter');
                Route::get('record/{id}', 'Tenant\WarrantyDocumentController@record');
                Route::post('', 'Tenant\WarrantyDocumentController@store');
                Route::get('return_warranty/{id}', 'Tenant\WarrantyDocumentController@return_warranty');

                Route::prefix('report')->group(function () {
                    Route::get('', 'Tenant\WarrantyDocumentController@report_index')->name('tenant.warranty_document.report_index');
                    Route::get('columns', 'Tenant\WarrantyDocumentController@columns');
                    Route::get('filter', 'Tenant\WarrantyDocumentController@filter');
                    Route::get('records', 'Tenant\WarrantyDocumentController@report_records');
                    Route::get('excel', 'Tenant\WarrantyDocumentController@report_records_excel');
                });
            });
            // Route::get('sale-notes/record-generate-document/{salenote}', 'Tenant\SaleNoteController@recordGenerateDocument');

            //POS
            Route::get('pos', 'Tenant\PosController@index')->name('tenant.pos.index');
            Route::get('pos_full', 'Tenant\PosController@index_full')->name('tenant.pos_full.index');
            Route::get('pos/search-customers', 'Tenant\PosController@searchCustomers');
            Route::post('pos/images-batch', 'Tenant\PosController@getImageBatch');
            Route::get('pos/validate_stock/{item}/{quantity}', 'Tenant\PosController@validate_stock');
            Route::get('pos/last-sale', 'Tenant\PosController@last_sale');
            Route::get('pos/search_items', 'Tenant\PosController@search_items');
            Route::get('pos/tables', 'Tenant\PosController@tables');
            Route::get('pos/tables-critical', 'Tenant\PosController@tablesCritical');
            Route::get('pos/tables-secondary', 'Tenant\PosController@tablesSecondary');
            Route::get('pos/get-item-service', 'Tenant\PosController@get_item_service_pos');
            Route::get('pos/table/{table}', 'Tenant\PosController@table');
            Route::get('pos/payment_tables', 'Tenant\PosController@payment_tables');
            Route::get('pos/payment', 'Tenant\PosController@payment')->name('tenant.pos.payment');
            Route::get('pos/status_configuration', 'Tenant\PosController@status_configuration');
            Route::get('pos/items', 'Tenant\PosController@item');
            Route::post('pos/favorite', 'Tenant\PosController@favorite');
            Route::get('pos/search_items_cat', [PosController::class, 'search_items_cat']);
            Route::get('pos/get-item-base', [PosController::class, 'getItemBase']);
            Route::prefix('advances')->group(function () {
                Route::get('/', [AdvancesController::class, 'index'])->name('tenant.advances.index');
                Route::post('/', [AdvancesController::class, 'store']);
                Route::get('/records', [AdvancesController::class, 'records']);
                Route::get('/record/{id}', [AdvancesController::class, 'record']);
                Route::post('/advance_document', [AdvancesController::class, 'advanceDocument']);
                Route::get('/columns', [AdvancesController::class, 'columns']);
                Route::get('/type/{type}', [AdvancesController::class, 'index'])->name('tenant.advances.index');
                Route::delete('/{id}', [AdvancesController::class, 'destroy']);
                Route::get('/persons/{type}', [AdvancesController::class, 'persons']);
                Route::get('/get-advance/{person_id}', [AdvancesController::class, 'getAdvance']);
                Route::get('report-a4/{cash}', [AdvancesController::class, 'reportA4']);
                Route::get('report-ticket/{cash}/{format?}', [AdvancesController::class, 'reportTicket']);
                Route::get('report-excel/{cash}', [AdvancesController::class, 'reportExcel']);
                Route::get('simple/report-a4/{cash}', [AdvancesController::class, 'reportSimpleA4']);
                // Route::get('report-cash-income-egress/{cash}', [AdvancesController::class, 'reportCashIncomeEgress']);
            });
            Route::get('cash', 'Tenant\CashController@index')->name('tenant.cash.index');
            Route::get('cash/get_cash/{user_id}', 'Tenant\CashController@getCashSeller');
            Route::post('cash/get_initial_balance', 'Tenant\CashController@getInitialBalance');
            Route::get('cash/columns', 'Tenant\CashController@columns');
            Route::get('cash/records', 'Tenant\CashController@records');
            Route::post('cash/get_exchange_rate_sale', 'Tenant\CashController@getExchangeRateSale');
            Route::get('cash/create', 'Tenant\CashController@create')->name('tenant.sale_notes.create');
            Route::get('cash/tables', 'Tenant\CashController@tables');
            Route::get('cash/opening_cash', 'Tenant\CashController@opening_cash');
            Route::get('cash/opening_cash_check/{user_id}', 'Tenant\CashController@opening_cash_check');

            Route::post('cash', 'Tenant\CashController@store');
            Route::post('cash/cash_document', 'Tenant\CashController@cash_document');
            // Route::get('cash/close/{cash}', 'Tenant\CashController@close');
            Route::post('cash/close/{cash}', 'Tenant\CashController@close');
            Route::get('cash/re_open/{cash}', 'Tenant\CashController@re_open');
            Route::get('cash/report/{cash}', 'Tenant\CashController@report');
            Route::get('cash/report', 'Tenant\CashController@report_general');

            Route::get('cash/record/{cash}', 'Tenant\CashController@record');
            Route::delete('cash/{cash}', 'Tenant\CashController@destroy');
            Route::get('cash/item/tables', 'Tenant\CashController@item_tables');
            Route::get('cash/search/customers', 'Tenant\CashController@searchCustomers');
            Route::get('cash/search/customer/{id}', 'Tenant\CashController@searchCustomerById');

            Route::get('cash/report/products/{cash}/{is_garage?}', 'Tenant\CashController@report_products');
            Route::get('cash/report/products-ticket/{cash}/{is_garage?}', 'Tenant\CashController@report_products_ticket');
            Route::get('cash/report/products-excel/{cash}', 'Tenant\CashController@report_products_excel');
            Route::get('cash/report/cash-excel/{cash}', 'Tenant\CashController@report_cash_excel');

            //POS VENTA RAPIDA
            Route::get('pos/fast', 'Tenant\PosController@fast')->name('tenant.pos.fast');
            Route::get('pos/garage', 'Tenant\PosController@garage')->name('tenant.pos.garage');

            Route::get('shortcuts', 'Tenant\TutorialsController@index')->name('shortcuts.index');
            Route::get('shortcuts/columns', 'Tenant\TutorialsController@columns');
            Route::get('shortcuts/records', 'Tenant\TutorialsController@records');
            Route::get('shortcuts/record/{tag}', 'Tenant\TutorialsController@record');
            Route::post('shortcuts/uploads', 'Tenant\TutorialsController@subir_imagen');
            Route::post('shortcuts', 'Tenant\TutorialsController@store');
            Route::delete('shortcuts/{tag}', 'Tenant\TutorialsController@destroy');

            //Tagsf
            Route::get('tags', 'Tenant\TagController@index')->name('tenant.tags.index');
            Route::get('tags/columns', 'Tenant\TagController@columns');
            Route::get('tags/records', 'Tenant\TagController@records');
            Route::post('tags/{id}/upload', 'Tenant\TagController@updateFavicon');
            Route::get('tags/record/{tag}', 'Tenant\TagController@record');
            Route::post('tags', 'Tenant\TagController@store');
            Route::delete('tags/{tag}', 'Tenant\TagController@destroy');

            //Label Colors
            Route::get('label_colors', 'Tenant\LabelColorController@index')->name('tenant.label_colors.index');
            Route::get('label_colors/columns', 'Tenant\LabelColorController@columns');
            Route::get('label_colors/options', 'Tenant\LabelColorController@options');
            Route::get('label_colors/records', 'Tenant\LabelColorController@records');
            Route::get('label_colors/{label_color}', 'Tenant\LabelColorController@show');
            Route::post('label_colors', 'Tenant\LabelColorController@store');
            Route::delete('label_colors/{label_color}', 'Tenant\LabelColorController@destroy');

            //Promotion
            Route::get('promotions', 'Tenant\PromotionController@index')->name('tenant.promotion.index');
            Route::get('promotions/columns', 'Tenant\PromotionController@columns');
            Route::get('promotions/tables', 'Tenant\PromotionController@tables');
            Route::get('promotions/records', 'Tenant\PromotionController@records');
            Route::get('promotions/record/{tag}', 'Tenant\PromotionController@record');
            Route::post('promotions', 'Tenant\PromotionController@store');
            Route::delete('promotions/{promotion}', 'Tenant\PromotionController@destroy');
            Route::post('promotions/upload', 'Tenant\PromotionController@upload');

            Route::get('item-sets', 'Tenant\ItemSetController@index')->name('tenant.item_sets.index')->middleware('redirect.level');
            Route::get('item-sets/columns', 'Tenant\ItemSetController@columns');
            Route::get('item-sets/records-individuals', 'Tenant\ItemSetController@recordsIndividuals');
            Route::get('item-sets/records-individuals-not-set', 'Tenant\ItemSetController@recordsIndividualsNotSet');
            Route::get('item-sets/records', 'Tenant\ItemSetController@records');
            Route::get('item-sets/history', 'Tenant\ItemSetController@history');
            Route::get('item-sets/tables', 'Tenant\ItemSetController@tables');
            Route::get('item-sets/record/{item}', 'Tenant\ItemSetController@record');
            Route::get('item-sets/replace/{item}/{replace_item}', 'Tenant\ItemSetController@replace');
            Route::post('item-sets', 'Tenant\ItemSetController@store');
            Route::delete('item-sets/{item}', 'Tenant\ItemSetController@destroy');
            Route::delete('item-sets/item-unit-type/{item}', 'Tenant\ItemSetController@destroyItemUnitType');
            Route::post('item-sets/import', 'Tenant\ItemSetController@import');
            Route::post('item-sets/upload', 'Tenant\ItemSetController@upload');
            Route::post('item-sets/visible_store', 'Tenant\ItemSetController@visibleStore');
            Route::get('item-sets/item/tables', 'Tenant\ItemSetController@item_tables');

            Route::get('person-types/columns', 'Tenant\PersonTypeController@columns');
            Route::get('person-types', 'Tenant\PersonTypeController@index')->name('tenant.person_types.index');
            Route::get('person-types/records', 'Tenant\PersonTypeController@records');
            Route::get('person-types/record/{person}', 'Tenant\PersonTypeController@record');
            Route::post('person-types', 'Tenant\PersonTypeController@store');
            Route::delete('person-types/{person}', 'Tenant\PersonTypeController@destroy');

            //Cuenta
            Route::get('cuenta/payment_index', 'Tenant\AccountController@paymentIndex')->name('tenant.payment.index');
            Route::get('cuenta/configuration', 'Tenant\AccountController@index')->name('tenant.configuration.index');
            Route::get('cuenta/payment_records', 'Tenant\AccountController@paymentRecords');
            Route::get('cuenta/tables', 'Tenant\AccountController@tables');
            Route::post('cuenta/update_plan', 'Tenant\AccountController@updatePlan');
            Route::post('cuenta/payment_culqui', 'Tenant\AccountController@paymentCulqui')->name('tenant.account.payment_culqui');

            //Payment Methods
            Route::get('payment_method/records', 'Tenant\PaymentMethodTypeController@records');
            Route::get('payment_method/record/{code}', 'Tenant\PaymentMethodTypeController@record');
            Route::post('payment_method', 'Tenant\PaymentMethodTypeController@store');
            Route::delete('payment_method/{code}', 'Tenant\PaymentMethodTypeController@destroy');
            Route::post('payment-method-types/change-show-in-pos', 'Tenant\PaymentMethodTypeController@changeShowInPos');
            Route::get('payment-method-types/destinations', 'Tenant\PaymentMethodTypeController@destinations');

            //formats PDF
            Route::get('templates', 'Tenant\FormatTemplateController@records');
            // Configuración del Login
            Route::get('login-page', 'Tenant\LoginConfigurationController@index')->name('tenant.login_page')->middleware('redirect.level');
            Route::post('login-page/upload-bg-image', 'Tenant\LoginConfigurationController@uploadBgImage');
            Route::post('login-page/update', 'Tenant\LoginConfigurationController@update');


            Route::post('extra_info/items', 'Tenant\ExtraInfoController@getExtraDataForItems');

            //liquidacion de compra
            Route::get('purchase-settlements', 'Tenant\PurchaseSettlementController@index')->name('tenant.purchase-settlements.index');
            Route::get('purchase-settlements/columns', 'Tenant\PurchaseSettlementController@columns');
            Route::get('purchase-settlements/records', 'Tenant\PurchaseSettlementController@records');

            Route::get('purchase-settlements/create/{order_id?}', 'Tenant\PurchaseSettlementController@create')->name('tenant.purchase-settlements.create');

            Route::post('purchase-settlements', 'Tenant\PurchaseSettlementController@store');
            Route::get('purchase-settlements/tables', 'Tenant\PurchaseSettlementController@tables');
            Route::get('purchase-settlements/table/{table}', 'Tenant\PurchaseSettlementController@table');
            Route::get('purchase-settlements/record/{document}', 'Tenant\PurchaseSettlementController@record');

            //Almacen de columnas por usuario
            Route::post('validate_columns', 'Tenant\SettingController@getColumnsToDatatable');

            // Channels Routes
            Route::prefix('channels')->group(function () {
                Route::get('/', 'Tenant\ChannelController@index')->name('tenant.channels.index');
                Route::get('/columns', 'Tenant\ChannelController@columns');
                Route::get('/all-records', 'Tenant\ChannelController@allRecords');
                Route::get('/records', 'Tenant\ChannelController@records');
                Route::get('/record/{id}', 'Tenant\ChannelController@record');
                Route::post('/', 'Tenant\ChannelController@store')->name('tenant.channels.store');
                Route::delete('/{id}', 'Tenant\ChannelController@destroy')->name('tenant.channels.destroy');
            });

            Route::post('general-upload-temp-image', 'Controller@generalUploadTempImage');
            Route::post('quotations-technicians/upload', 'Tenant\QuotationTechnicianController@uploadImage');

            Route::get('general-get-current-warehouse', 'Controller@generalGetCurrentWarehouse');
            Route::get('questions', 'Tenant\WhatsappController@questions')->name('tenant.questions');
            Route::get('answers', 'Tenant\WhatsappController@answers')->name('tenant.answers');
            Route::get('account_whatsapp', 'Tenant\WhatsappController@account_whatsapp')->name('tenant.account.whatsapp');
            // test theme
            // Route::get('testtheme', function () {
            //     return view('tenant.layouts.partials.testtheme');
            // });
            Route::post('/check-reference', [DocumentController::class, 'checkReference']);
            //aqui las rutas se separan por tenant y distribuidor a partir de la linea 1300 pertenecen al distribuidor, lo de arriba al tenant
            Route::post('/documents/save-or-update-box', [DocumentController::class, 'saveOrUpdateBox'])->name('documents.save-or-update-box');
            Route::post('/documents/preview', [DocumentController::class, 'preview']);
            Route::get('/documents/adjust-kardex/{id}', [DocumentController::class, 'adjustKardex'])->name('documents.adjust-kardex');
            Route::post('/sale-notes/save-or-update-box', [SaleNoteController::class, 'saveOrUpdateBox'])->name('sale-notes.save-or-update-box');
            Route::prefix('massive-messages')->group(function () {
                Route::get('/', [MassiveMessageController::class, 'index'])->name('massive_messages.index');
                Route::get('columns', [MassiveMessageController::class, 'columns']);
                Route::get('records', [MassiveMessageController::class, 'records']);
                Route::get('persons', [MassiveMessageController::class, 'persons']);
                Route::post('send-message/{id}', [MassiveMessageController::class, 'sendMessage']);
                Route::post('send-message-query/{id}', [MassiveMessageController::class, 'sendMessageQuery']);
                Route::post('/', [MassiveMessageController::class, 'store'])->name('massive_messages.store');
                Route::get('/{id}', [MassiveMessageController::class, 'show'])->name('massive_messages.show');
                Route::put('/{id}', [MassiveMessageController::class, 'update'])->name('massive_messages.update');
                Route::delete('/{id}', [MassiveMessageController::class, 'destroy'])->name('massive_messages.destroy');
                Route::get('{id}/history', [MassiveMessageController::class, 'history']);
                Route::post('{messageId}/resend/{detailId}', [MassiveMessageController::class, 'resend']);
            });

            Route::prefix('documents')->group(function () {
                // ... otras rutas ...
                Route::post('attributes', [DocumentController::class, 'attributes']);
                Route::get('pse-companies-states', 'Tenant\DocumentController@getPseCompaniesStates');
                Route::post('search-pse', 'Tenant\DocumentController@searchPse');
                Route::prefix('massive-emit')->group(function () {
                    Route::get('tables', [DocumentController::class, 'getTableDataMassiveEmit']);
                    Route::post('upload-file', [DocumentController::class, 'uploadFileMassiveEmit']);
                    Route::get('export', [DocumentController::class, 'massiveEmitExport']);
                });
                Route::prefix('documents-sale')->group(function () {
                    Route::post('upload-file', [DocumentController::class, 'uploadFileDocumentsSale']);
                });
            });

            Route::prefix('transport-formats')->group(function () {
                Route::get('/', 'Tenant\TransportFormatController@records');
            });

            Route::prefix('discount-types')->group(function () {
                Route::get('/', [DiscountTypeController::class, 'index'])->name('tenant.discount_types.index');
                Route::get('/columns', [DiscountTypeController::class, 'columns']);
                Route::get('/records', [DiscountTypeController::class, 'records']);
                Route::get('/record/{id}', [DiscountTypeController::class, 'record']);
                Route::post('/', [DiscountTypeController::class, 'store']);
                Route::delete('/{id}', [DiscountTypeController::class, 'destroy']);
                Route::get('/categories', [DiscountTypeController::class, 'getCategories']);
                Route::get('/brands', [DiscountTypeController::class, 'getBrands']);
                Route::get('/categories/search', [DiscountTypeController::class, 'searchCategories']);
                Route::get('/brands/search', [DiscountTypeController::class, 'searchBrands']);
                Route::post('/upload-temp-file', [DiscountTypeController::class, 'uploadTempFile']);
                Route::get('/items/search', [DiscountTypeController::class, 'searchItems']);
                Route::get('/items/{id}', [DiscountTypeController::class, 'getDiscountItems']);
                Route::post('/items/{id}', [DiscountTypeController::class, 'addDiscountItem']);
                Route::delete('/items/{id}', [DiscountTypeController::class, 'removeDiscountItem']);
                Route::post('/items/import/{id}', [DiscountTypeController::class, 'importItems']);
                Route::post('/upload-temp-image', [DiscountTypeController::class, 'uploadTempImage']);
                Route::get('/id-exists-in-discount-type-items/{id}/{discount_type_id}', [DiscountTypeController::class, 'idExistsInDiscountTypeItems']);
            });

            Route::prefix('charge-types')->group(function () {
                Route::get('/', [ChargeTypeController::class, 'index'])->name('tenant.charge_types.index');
                Route::get('/columns', [ChargeTypeController::class, 'columns']);
                Route::get('/records', [ChargeTypeController::class, 'records']);
                Route::get('/record/{id}', [ChargeTypeController::class, 'record']);
                Route::post('/', [ChargeTypeController::class, 'store']);
                Route::delete('/{id}', [ChargeTypeController::class, 'destroy']);
                Route::get('/categories', [ChargeTypeController::class, 'getCategories']);
                Route::get('/brands', [ChargeTypeController::class, 'getBrands']);
                Route::get('/categories/search', [ChargeTypeController::class, 'searchCategories']);
                Route::get('/brands/search', [ChargeTypeController::class, 'searchBrands']);
                Route::post('/upload-temp-file', [ChargeTypeController::class, 'uploadTempFile']);
                Route::get('/items/search', [ChargeTypeController::class, 'searchItems']);
                Route::get('/items/{id}', [ChargeTypeController::class, 'getChargeItems']);
                Route::post('/items/{id}', [ChargeTypeController::class, 'addChargeItem']);
                Route::delete('/items/{id}', [ChargeTypeController::class, 'removeChargeItem']);
                Route::post('/items/import/{id}', [ChargeTypeController::class, 'importItems']);
                Route::post('/upload-temp-image', [ChargeTypeController::class, 'uploadTempImage']);
                Route::get('/id-exists-in-charge-type-items/{id}/{charge_type_id}', [ChargeTypeController::class, 'idExistsInChargeTypeItems']);
            });

            Route::prefix('price-adjustments')->group(function () {
                Route::get('/', [PriceAdjustmentController::class, 'index'])->name('tenant.price_adjustments.index');
                Route::get('/records', [PriceAdjustmentController::class, 'records']);
                Route::get('/record/{id}', [PriceAdjustmentController::class, 'record']);
                Route::post('/', [PriceAdjustmentController::class, 'store']);
                Route::delete('/{id}', [PriceAdjustmentController::class, 'destroy']);
                Route::post('/apply/{id}', [PriceAdjustmentController::class, 'apply']);
                Route::get('/preview/{id}', [PriceAdjustmentController::class, 'preview']);
                Route::get('/categories', [PriceAdjustmentController::class, 'getCategories']);
                Route::get('/brands', [PriceAdjustmentController::class, 'getBrands']);
                Route::get('/categories/search', [PriceAdjustmentController::class, 'searchCategories']);
                Route::get('/brands/search', [PriceAdjustmentController::class, 'searchBrands']);
                Route::get('/items/search', [PriceAdjustmentController::class, 'searchItems']);
            });

            // Plate Numbers Routes
            Route::prefix('plate-numbers-documents')->group(function () {
                Route::get('columns', 'Tenant\PlateNumberDocumentController@columns');
                Route::get('/records', 'Tenant\PlateNumberDocumentController@records');
            });
            Route::prefix('plate-numbers')->group(function () {
                Route::get('search', 'Tenant\PlateNumberController@search');
                Route::get('get-by-id/{id}', 'Tenant\PlateNumberController@getById');

                // Brands
                Route::get('brands', 'Tenant\PlateNumberBrandController@index');
                Route::post('brands', 'Tenant\PlateNumberBrandController@store');
                Route::put('brands/{id}', 'Tenant\PlateNumberBrandController@update');
                Route::delete('brands/{id}', 'Tenant\PlateNumberBrandController@destroy');

                // Models
                Route::get('models', 'Tenant\PlateNumberModelController@index');
                Route::post('models', 'Tenant\PlateNumberModelController@store');
                Route::put('models/{id}', 'Tenant\PlateNumberModelController@update');
                Route::delete('models/{id}', 'Tenant\PlateNumberModelController@destroy');

                // Colors
                Route::get('colors', 'Tenant\PlateNumberColorController@index');
                Route::post('colors', 'Tenant\PlateNumberColorController@store');
                Route::put('colors/{id}', 'Tenant\PlateNumberColorController@update');
                Route::delete('colors/{id}', 'Tenant\PlateNumberColorController@destroy');

                // Types
                Route::get('types', 'Tenant\PlateNumberTypeController@index');
                Route::post('types', 'Tenant\PlateNumberTypeController@store');
                Route::put('types/{id}', 'Tenant\PlateNumberTypeController@update');
                Route::delete('types/{id}', 'Tenant\PlateNumberTypeController@destroy');

                // Plate Numbers
                Route::get('/', 'Tenant\PlateNumberController@index')->name('tenant.plate_numbers.index');
                Route::get('/columns', 'Tenant\PlateNumberController@columns');
                Route::get('/records', 'Tenant\PlateNumberController@records');
                Route::post('/', 'Tenant\PlateNumberController@store');

                Route::get('/details/{id}', 'Tenant\PlateNumberController@show');
                // KMs
                Route::get('kms', 'Tenant\PlateNumberKmController@index');
                Route::post('kms', 'Tenant\PlateNumberKmController@store');
                Route::put('kms/{id}', 'Tenant\PlateNumberKmController@update');
                Route::delete('kms/{id}', 'Tenant\PlateNumberKmController@destroy');
                Route::get('/{id}', 'Tenant\PlateNumberController@show');
                Route::put('/{id}/update-km', 'Tenant\PlateNumberController@updateKilometers');
                Route::put('/{id}', 'Tenant\PlateNumberController@update');
                Route::delete('/{id}', 'Tenant\PlateNumberController@destroy');
            });

            // PDF Viewer routes
            Route::middleware(['auth', 'locked.tenant'])->prefix('pdf-viewer')->group(function () {
                Route::get('/', 'Tenant\PdfViewerController@index');
                Route::post('/upload', 'Tenant\PdfViewerController@upload');
                Route::get('/show/{filename}', 'Tenant\PdfViewerController@show')->name('pdf.show');
            });
        });
    });
} else {
    $prefix = env('PREFIX_URL', null);
    $prefix = !empty($prefix) ? $prefix . "." : '';
    $app_url = $prefix . env('APP_URL_BASE');

    Route::domain($app_url)->group(function () {
        Route::get('login', 'System\LoginController@showLoginForm')->name('login');
        Route::post('login', 'System\LoginController@login');
        Route::post('logout', 'System\LoginController@logout')->name('logout');
        Route::get('phone', 'System\UserController@getPhone');
        Route::middleware('auth:admin')->group(function () {
            Route::get('php_version', function () {
                phpinfo();
            });
            Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
            Route::get('/', function () {
                return redirect()->route('system.dashboard');
            });
            Route::get('dashboard', 'System\HomeController@index')->name('system.dashboard')->middleware('secondary.admin');
            Route::get('users', 'System\UserController@index')->name('system.users.index');
            Route::post("users/create_admin", "System\UserController@create_admin");
            Route::post("users/create_secondary_admin", "System\UserController@create_secondary_admin");
            Route::post("users/update-permission", "System\UserController@updatePermission");
            Route::delete("users/delete_secondary_admin/{id}", "System\UserController@delete_secondary_admin");
            // Route::post("users/delete_admin", "System\UserController@delete_admin");

            Route::prefix('documents')->group(function () {
                Route::get('/', 'System\ClientController@documents')->name('system.documents');
                Route::get('/send/{id}/{document_id}', 'System\ClientController@send');
                Route::get('/clients/tables', 'System\ClientController@tables_clients');
                Route::get('/status_voided/{id}/{voided_id}', 'System\ClientController@status_voided');
                // Route::get('/filter/{id}', 'System\ClientController@records_clients');
                Route::get('/filter/{id}', 'System\ClientController@records_clients_optimized');
                Route::get('/validate/{hostname_id}/{document_id}', 'System\ClientController@validate_documents');

            });
            Route::get('clients/documents_not_send/{client_id}', 'System\ClientController@documents_not_send');
            Route::get('clients/documents_to_anulated/{client_id}', 'System\ClientController@documents_to_anulated');
            //Clients
            Route::get('clients_uuids', 'System\ClientController@getTenantNotClients');
            Route::post('clients/clear_cache', 'System\ClientController@clearCache');
            Route::post('delete_folders', 'System\ClientController@deleteFolders');
            Route::post('delete_pdfs_file', 'System\ClientController@deletePdfsFile');
            Route::get('clients', 'System\ClientController@index')->name('system.clients.index');
            Route::post('clients/set_telephone', 'System\ClientController@setTelephone');
            Route::post('clients/set_monto', 'System\ClientController@setMonto');
            Route::post('clients/set_tiempo', 'System\ClientController@setTiempo');
            Route::post('clients/set_comment', 'System\ClientController@setComment');
            // Route::get('clients/records', 'System\ClientController@records');
            Route::get('clients/records', 'System\ClientController@records_optimized');
            Route::get('clients/simple-records', 'System\ClientController@simpleRecords');
            Route::get('clients/record/{client}', 'System\ClientController@record');

            Route::post('clients/download-database', 'System\ClientController@downloadDatabase');
            Route::post('clients/download-documents', 'System\ClientController@downloadDocuments');
            Route::post('clients/change_show_eyes_in_login', 'System\ClientController@changeShowEyesInLogin');
            Route::get('clients/create', 'System\ClientController@create');
            Route::post('clients/mail', 'System\ClientController@email');
            Route::get('clients/tables', 'System\ClientController@tables');
            Route::get('clients/charts', 'System\ClientController@charts');
            Route::get('clients/global_notifications', 'System\ClientController@global_notifications');
            Route::post('clients', 'System\ClientController@store');
            Route::post('clients/update', 'System\ClientController@update');

            Route::delete('clients/{client}/{input_validate}', 'System\ClientController@destroy');
            // Route::delete('clients/{client}', 'System\ClientController@destroy');

            Route::post('clients/password/{client}', 'System\ClientController@password');
            Route::post('clients/locked_emission', 'System\ClientController@lockedEmission');
            Route::post('clients/locked_tenant', 'System\ClientController@lockedTenant');
            Route::post('clients/config_system_env', 'System\ClientController@config_system_env');

            Route::post('clients/active_tenant', 'System\ClientController@activeTenant');
            // Route::post('clients/locked_tenant', 'System\ClientController@lockedTenant'); //Linea repetida

            Route::post('clients/locked_user', 'System\ClientController@lockedUser');
            Route::post('clients/locked_item', 'System\ClientController@lockedItem');
            Route::post('clients/renew_plan', 'System\ClientController@renewPlan');
            Route::post('clients/set_certificate_due', 'System\ClientController@setCertificateDue');



            Route::post('clients/set_billing_cycle', 'System\ClientController@startBillingCycle');
            Route::post('clients/end_billing_cycle', 'System\ClientController@endBillingCycle');
            Route::post('clients/locked-by-column', 'System\ClientController@lockedByColumn');

            Route::post('clients/upload', 'System\ClientController@upload');
            Route::post('clients/cert/{type}/{client_id}', 'System\ClientController@store_cert_file');
            Route::delete('clients/cert/{type}/{client_id}', 'System\ClientController@delete_cert_file');

            Route::get('client_payments/records/{client_id}', 'System\ClientPaymentController@records');
            Route::get('client_payments/client/{client_id}', 'System\ClientPaymentController@client');
            Route::get('client_payments/tables', 'System\ClientPaymentController@tables');
            Route::delete('client_payments/delete_file/{payment_id}', 'System\ClientPaymentController@delete_file_payment');
            Route::post('client_payments', 'System\ClientPaymentController@store');
            Route::post('client_payments/file/{payment_id}', 'System\ClientPaymentController@store_file_payment');
            Route::delete('client_payments/{client_payment}', 'System\ClientPaymentController@destroy');
            Route::get('client_payments/cancel_payment/{client_payment_id}', 'System\ClientPaymentController@cancel_payment');

            Route::get('client_account_status/records/{client_id}', 'System\AccountStatusController@records');
            Route::get('client_account_status/client/{client_id}', 'System\AccountStatusController@client');
            Route::get('client_account_status/tables', 'System\AccountStatusController@tables');

            //Planes
            Route::get('plans', 'System\PlanController@index')->name('system.plans.index')->middleware('secondary.admin');
            Route::get('plans/records', 'System\PlanController@records');
            Route::get('plans/tables', 'System\PlanController@tables');
            Route::get('plans/record/{plan}', 'System\PlanController@record');
            Route::post('plans', 'System\PlanController@store');
            Route::delete('plans/{plan}', 'System\PlanController@destroy');
            Route::get('customers/top', 'Reporttopcliente@index')->name('tenant.reports.customers.top');
            //Users
            Route::get('users/create', 'System\UserController@create')->name('system.users.create');
            Route::get('users/record', 'System\UserController@record');
            Route::get('users/records', 'System\UserController@records');
            Route::get('users/columns', 'System\UserController@columns');
            Route::post('users/columns', 'System\UserController@columns');
            Route::post('users', 'System\UserController@store');

            Route::get('services/ruc/{number}', 'System\ServiceController@ruc');

            Route::get('certificates/record', 'System\CertificateController@record');
            Route::post('certificates/uploads', 'System\CertificateController@uploadFile');
            Route::post('certificates/saveSoapUser', 'System\CertificateController@saveSoapUser');
            Route::delete('certificates', 'System\CertificateController@destroy');

            //xd
            Route::get('403', 'System\ErrorsController@index')->name('system.403.index')->middleware('secondary.admin');
            Route::post('/errors/update', 'System\ErrorsController@update')->name('errors.update');
            Route::post('/envios/store', 'System\ErrorsController@storeEnvio')->name('envios.store');
            Route::delete('/envios/{id}', 'System\ErrorsController@destroyEnvio')->name('envios.destroy');
            Route::post('send-message/{clientId}/{envioId}', [ErrorsController::class, 'sendMessage'])->name('send.message');
            Route::post('send-messages', 'System\ErrorsController@sendMessages')->name('send-messages');


            Route::get('configurations', 'System\ConfigurationController@index')->name('system.configuration.index')->middleware('secondary.admin');
            Route::get('configurations/custom-tenant', 'System\ConfigurationController@customTenant')->name('system.configuration.custom-tenant')->middleware('secondary.admin');
            Route::post('configurations/update-field', 'System\ConfigurationController@updateField');
            Route::post('configurations/login', 'System\ConfigurationController@storeLoginSettings');
            Route::post('configurations/upload-default-image', 'System\ConfigurationController@uploadDefaultImage');
            Route::delete('configurations/delete-default-image/{type}', 'System\ConfigurationController@deleteDefaultImage');
            Route::post('configurations/bg', 'System\ConfigurationController@storeBgLogin');
            Route::post('configurations/other-configuration', 'System\ConfigurationController@storeOtherConfiguration');
            Route::get('configurations/other-configuration', 'System\ConfigurationController@getOtherConfiguration');
            Route::post('configurations/bg_imagen', 'System\ConfigurationController@bg_imagen');

            Route::get('companies/record', 'System\CompanyController@record');
            Route::post('companies', 'System\CompanyController@store');

            // auto-update
            Route::get('auto-update', 'System\UpdateController@index')->name('system.update')->middleware('secondary.admin');
            Route::get('auto-update/branch', 'System\UpdateController@branch')->name('system.update.branch');
            Route::get('auto-update/pull/{branch}', 'System\UpdateController@pull')->name('system.update.pull');
            Route::get('auto-update/artisan/migrate', 'System\UpdateController@artisanMigrate')->name('system.update.artisan.migrate');
            Route::get('auto-update/artisan/migrate/tenant', 'System\UpdateController@artisanTenancyMigrate')->name('system.update.artisan.tenancy.migrate');
            Route::get('auto-update/artisan/clear', 'System\UpdateController@artisanClear')->name('system.update.artisan.clear');
            Route::get('auto-update/composer/install', 'System\UpdateController@composerInstall')->name('system.update.composer.install');
            Route::get('auto-update/keygen', 'System\UpdateController@keygen')->name('system.update.keygen');
            Route::get('auto-update/version', 'System\UpdateController@version')->name('system.update.version');
            Route::get('auto-update/changelog', 'System\UpdateController@changelog')->name('system.changelog');

            //Configuration

            Route::post('configurations', 'System\ConfigurationController@store');
            Route::get('configurations/record', 'System\ConfigurationController@record');
            Route::get('information', 'System\ConfigurationController@InfoIndex')->name('system.information')->middleware('secondary.admin');
            Route::get('status/history', 'System\StatusController@history')->name('system.status');
            Route::get('status/memory', 'System\StatusController@memory')->name('system.status.memory');
            Route::get('status/cpu', 'System\StatusController@cpu')->name('system.status.cpu');
            Route::get('configurations/apiruc', 'System\ConfigurationController@apiruc');
            Route::get('configurations/apkurl', 'System\ConfigurationController@apkurl');

            Route::get('configurations/update-tenant-discount-type-base', 'System\ConfigurationController@updateTenantDiscountTypeBase');


            // backup
            Route::get('backup', 'System\BackupController@index')->name('system.backup')->middleware('secondary.admin');
            Route::post('backup/db', 'System\BackupController@db')->name('system.backup.db');
            Route::post('backup/files', 'System\BackupController@files')->name('system.backup.files');
            Route::post('backup/upload', 'System\BackupController@upload')->name('system.backup.upload');

            Route::get('backup/last-backup', 'System\BackupController@mostRecent');
            Route::get('backup/download/{filename}', 'System\BackupController@download');

            Route::post('configurations/digemid', 'System\ConfigurationController@update_digemid');
            Route::get('configurations/digemid', 'System\ConfigurationController@records_digemid');
            Route::get('configurations/digemid/record/{id}', 'System\ConfigurationController@record_digemid');
            Route::delete('configurations/digemid/{id}', 'System\ConfigurationController@remove_digemid');
            Route::post('configurations/digemid/insert', 'System\ConfigurationController@insert_digemid');
            Route::post('configurations/digemid/delete', 'System\ConfigurationController@delete_digemid');
        });
    });
}
