<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Str;
use App\Events\MessageEvent;
use App\Http\Controllers\Tenant\ConfigurationController;
use App\Http\Controllers\Tenant\RestaurantController;
use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Route;
use Modules\Item\Http\Controllers\CategoryController;
use Modules\Restaurant\Http\Controllers\PosController;
use Modules\Restaurant\Http\Controllers\CashController;
use Modules\Restaurant\Http\Controllers\IncomesController;
use Modules\Restaurant\Http\Controllers\QrCartController;


Route::get('qr', [QrCartController::class,'show'])->name('restaurant.pdf.show');

Route::prefix('restaurant')->group(function () {
     
    // Route::get('sale-notes', [App\Http\Controllers\SaleNoteController::class, 'pos'])->name('pos.sale_notes.index')->middleware('redirect.level');
    //Route::get('documents', [App\Http\Controllers\DocumentController::class, 'documents'])->name('restaurant.documents.index');
    //Route::get('documents/create/{documents?}', [App\Http\Controllers\DocumentController::class, 'create_pos'])->name('tenant.documents_pos.create')->middleware(['redirect.level', 'tenant.internal.mode']);
    //Route::get('documents/data_table', [App\Http\Controllers\DocumentController::class, 'data_table']);
    //Route::get('documents/records', [App\Http\Controllers\DocumentController::class, 'records']);
    Route::get('print-ticket-orden', 'OrdenController@printTicket');
    Route::get('report-boxes', 'BoxesController@report')->name('restaurant.report.boxes');
    Route::get('report-boxes/tables', 'BoxesController@tables');
    Route::get('report-boxes/reports_type', 'BoxesController@reports_type');

    Route::get('report-boxes/reports_resumen_type', 'BoxesController@reports_resumen_type');
    Route::get('cash/balance-final/{date_closed}', 'BoxesController@balance_final');

    Route::get('report-boxes/reports_categoria_type', 'BoxesController@reports_categoria_type');
    Route::get('report-boxes/reports_bancario_type', 'BoxesController@reports_bancario_type');
    Route::get('report-boxes/reports', 'BoxesController@reports_results');
    Route::get('login', 'RestaurantController@loginWorker');

    Route::middleware(['auth', 'locked.tenant'])->group(function () {
    Route::get('kitchen', function () {
            $configuration = Configuration::first();
            if ($configuration->socket_channel == null) {
                $configuration->socket_channel = Str::random(25);
                $configuration->save();
                ConfigurationController::clearCache();
                $event_name = $configuration->socket_channel;
            }
            return view('restaurant::kitchen', compact('configuration'));
        });
        //*** ORDENS */
        Route::prefix('qr-cart')->group(function () {
            Route::get('', [QrCartController::class,'index'])->name('restaurant.qr-cart');
            Route::post('upload', [QrCartController::class,'upload']);  
        });
        
        // Ruta fija para mostrar el PDF del menú
        Route::get("categories",[CategoryController::class,"records"]);
        Route::get("items",[RestaurantController::class,"getDataTableItem"]);
        Route::get("tables-config",[RestaurantController::class,"getTableConfig"]);
        Route::get("items/category/{category_id}",[RestaurantController::class,"getItemsByCategory"]);
        Route::get('attention', 'AttentionController@index')->name('restaurant.attention');
        // Route::get('tables/records', 'TableController@records');
        Route::get('ordens', 'OrdenController@index')->name('restaurant.ordens');
        Route::get('ordens-pending', 'OrdenController@pending');
        Route::post('ordens/change-status', 'OrdenController@changeStatus');
        Route::post('ordens', 'OrdenController@store');
        Route::delete('ordens/delete-item/{orden_item_id}', 'OrdenController@deleteItem');
        Route::get('ordens/release/{table_id}', 'OrdenController@releaseTable');
        Route::get('ordens/table/{table_id}', 'OrdenController@getOrdenByTable');
        Route::get('ordens/records', 'OrdenController@records');
        Route::get('ordens/listfoods/{date}', 'BoxesController@listfoods');
        Route::get('ordens/payment/{type}/{document_id}/{orderid}', 'BoxesController@paymentorden');
        Route::get('ordens/take-away', 'OrdenController@takeAwayOrders');
        Route::get('ordens/{orden_id}', 'OrdenController@getOrdenById');
        Route::get('ordens/to-pay/{orden_id}', 'OrdenController@getOrdenByIdToPay');
        Route::get('ordens/get-order-id-by-table/{table_id}', 'OrdenController@getOrderIdByTable');
        //**** COMIDAS / BEBIDAS */
        Route::get('food-list', 'FoodController@index')->name('restaurant.food_list');
        Route::get('food-list/records', 'FoodController@records');
        Route::get('food-list/record/{id}', 'FoodController@record');
        Route::get('food-list/delete-image/{id}', 'FoodController@deleteImage');
        Route::get('food-list/{id}', 'FoodController@active');
        Route::post('food-list/upload-image', 'FoodController@uploadImage');
        Route::post('food-list', 'FoodController@store');

        // Route::get('category-food', 'CategoryFoodController@index')->name('restaurant.category_food');
        // Route::get('category-food/records', 'CategoryFoodController@records');
        // Route::get('category-food/{id}', 'CategoryFoodController@active');
        // Route::get('category-food/record/{id}', 'CategoryFoodController@record');
        // Route::post('category-food', 'CategoryFoodController@store');
        //**** TIPO DE TRABAJADORES */
        Route::get('workers-type', 'WorkersTypeController@index')->name('restaurant.workers_type');
        Route::get('workers-type/columns', 'WorkersTypeController@columns');
        Route::get('workers-type/records', 'WorkersTypeController@records');
        Route::get('workers-type/actives', 'WorkersTypeController@actives');
        Route::get('workers-type/record/{id}', 'WorkersTypeController@record');
        Route::get('workers-type/{id}', 'WorkersTypeController@active');
        Route::post('workers-type', 'WorkersTypeController@store');
        //**** AREAS */
        Route::prefix('areas')->group(function () {
            Route::get('', 'AreaController@index')->name('restaurant.areas');
            Route::get('columns', 'AreaController@columns');
            Route::get('records', 'AreaController@records');
            Route::get('actives', 'AreaController@actives');
            Route::get('record/{id}', 'AreaController@record');
            Route::delete('{id}', 'AreaController@destroy');
            Route::post('', 'AreaController@store');
        });
        //**** ESTADO DE MESAS */
        Route::get('status-tables', 'StatusTableController@index')->name('restaurant.status_table');
        Route::get('status-tables/records', 'StatusTableController@records');
        Route::get('status-tables/columns', 'StatusTableController@columns');
        Route::get('status-tables/{id}', 'StatusTableController@active');
        Route::get('status-tables/record/{id}', 'StatusTableController@record');
        Route::post('status-tables', 'StatusTableController@store');
        //**** ESTADO DE ORDEN */
        Route::get('status-orden', 'StatusOrdenController@index')->name('restaurant.status_orden');
        Route::get('status-orden/records', 'StatusOrdenController@records');
        Route::get('status-orden/columns', 'StatusOrdenController@columns');
        Route::get('status-orden/{id}', 'StatusOrdenController@active');
        Route::get('status-orden/record/{id}', 'StatusOrdenController@record');
        Route::post('status-orden', 'StatusOrdenController@store');
        //**** MESAS */
        Route::prefix('tables')->group(function () {
            Route::get('', 'TableController@index')->name('restaurant.tables');
            Route::get('columns', 'TableController@columns');
            Route::get('records', 'TableController@records');
            Route::get('all-records', 'TableController@allRecords');
            Route::get('record/{id}', 'TableController@record');
            Route::post('', 'TableController@store');
            Route::post('massive', 'TableController@massive');
            Route::delete('{id}', 'TableController@destroy');
            Route::get('enable/{id}', 'TableController@enable');
            Route::get('disable/{id}', 'TableController@disable');
        });
        //**** TRABAJADORES */
        Route::get('workers', 'WorkerController@index')->name('restaurant.workers');
        Route::get('workers/records', 'WorkerController@records');
        Route::get('workers/{id}', 'WorkerController@active');

        Route::get('workers/record/{id}', 'WorkerController@record');
        Route::post('workers', 'WorkerController@store');

        Route::get('pos', 'PosController@index')->name('restaurant.pos');
        Route::get('pos/search_orden', 'PosController@search');

        Route::post('pos/orden_payment', 'PosController@payment');
        Route::get('pos/foods', 'PosController@foods');
        Route::get('boxes', 'BoxesController@index')->name('restaurant.boxes');
    });
    //VISTA TRABAJADORES

    Route::middleware(['auth', 'locked.tenant'])->group(function () {
        //**** MESAS */
        Route::prefix('worker')->group(function () {
            Route::post('logout', 'RestaurantController@logout');
            Route::get('totales_sales', 'PosController@total_sales');
            Route::get('dashboard', 'DashboardController@index')->name('restaurant.workers.dashboard');
            Route::get('dashboard-kitchen', 'DashboardController@kitchen')->name('restaurant.kitchen.dashboard');
            Route::get('dashboard-pos', 'DashboardController@pos')->name('restaurant.pos.dashboard');
    
            //Gastos
            Route::get('expenses', [PosController::class, 'expenses'])->name('restaurant.expenses.index');
            Route::get('expenses/records', [PosController::class, 'records']);
            Route::get('expenses/record/{id}', [PosController::class, 'record']);
            Route::post('expenses', [PosController::class, 'store']);
            Route::delete('expenses/{id}', [PosController::class, 'destroy']);
            Route::get('expenses/columns',[PosController::class, 'columns']);
            Route::get('expenses/tables',[PosController::class, 'tables']);

            //Ingresos
            Route::get('incomes', [IncomesController::class, 'incomes'])->name('restaurant.incomes.index');
            Route::get('incomes/records', [IncomesController::class, 'records']);
            Route::get('incomes/record/{id}', [IncomesController::class, 'record']);
            Route::post('incomes', [IncomesController::class, 'store']);
            Route::delete('incomes/{id}', [IncomesController::class, 'destroy']);
            Route::get('incomes/columns',[IncomesController::class, 'columns']);
            Route::get('incomes/tables',[IncomesController::class, 'tables']);


            //Ingresos

            //Cash
            Route::get('cash',[CashController::class, 'index'])->name('restaurant.cash.index');
            Route::get('cash/columns',[CashController::class, 'columns']);
            Route::get('cash/records',[CashController::class, 'records']);
            Route::get('cash/create',[CashController::class, 'create'])->name('tenant.cash_pos.create');
            Route::get('cash/tables',[CashController::class, 'tables']);
            Route::get('cash/opening_cash',[CashController::class, 'opening_cash']);
            Route::get('cash/opening_cash_check/{user_id}',[CashController::class, 'opening_cash_check']);
            Route::post('cash',[CashController::class, 'store']);
            Route::get('cash/close/{cash}/{final_balance}',[CashController::class, 'close']);
            Route::get('cash/report/{cash}',[CashController::class, 'report']);
            Route::get('cash/report',[CashController::class, 'report_general']);
            Route::get('cash/record/{cash}',[CashController::class, 'record']);
            Route::delete('cash/{cash}',[CashController::class, 'destroy']);
            Route::get('cash/item/tables',[CashController::class, 'item_tables']);
            Route::get('cash/search/customers',[CashController::class, 'searchCustomers']);
            Route::get('cash/search/customer/{id}',[CashController::class, 'searchCustomerById']);
            Route::get('cash/report/products/{cash}',[CashController::class, 'report_products']);


            Route::get('dashboard/tables/{area_id}', 'DashboardController@tables');
            //
            Route::post('pos/orden_payment', 'PosController@payment');
            Route::get('pos/search_orden', 'PosController@search');
            Route::get('pos/listtables', 'PosController@listtables');
            Route::get('pos/selecttabled/{idOrden}', 'PosController@selecttabled');
            Route::get('pos/foods', 'PosController@foods');
            Route::get('record/{id}', 'OrdenController@record');

            //ORDERS
            Route::post('send-orden', 'OrdenController@store');
            Route::get('search_orden_document', 'PosController@search_orden_document');

            Route::post('cancel-orden', 'OrdenController@cancelOrden');
            Route::get('destroyorden/{ordenid}', 'OrdenController@destroyorden');


            Route::post('change-observation', 'OrdenItemController@updateObservation');
            Route::get('ordens-items', 'OrdenItemController@records');
            Route::get('ordens-list', 'OrdenController@ordenslist');

            Route::get('list-ordens-items', 'OrdenItemController@list_ordens_items');
            Route::get('ordens-ready/{id}', 'OrdenItemController@ordenReady');
            Route::get('ordens-status', 'OrdenItemController@ordensStatus');
            Route::delete('delete-orden/{id}', 'OrdenItemController@ordenDelete');
            //ACT. MESAS
            Route::get('tables/records-area/{area_id}', 'TableController@recordsByArea');
        });


        Route::post('send-order', 'RestaurantController@sendOrder');
        Route::post('receive-order', 'RestaurantController@receiveOrder');
    });
});
