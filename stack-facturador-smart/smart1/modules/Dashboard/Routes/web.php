<?php

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Route;

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'locked.tenant'])->group(function () {

            Route::get('/', function () {
                $user = auth()->user();
                $init_route = $user->init_route;
                if ($init_route ==  null) {
                    $init_route = '/documents/create';
                    $user_db = User::find($user->id);
                    $user_db->init_route = $init_route;
                    $user_db->save();
                }
                return redirect($init_route);
            });

            Route::prefix('dashboard')->group(function () {
                Route::get('/', 'DashboardController@index')->name('tenant.dashboard.index');
                Route::get('/dashboard', 'DashboardController@index')->name('tenant.dashboard.index');
                Route::get('/sales_purchases', 'DashboardController@sales_purchases')->name('tenant.dashboard.sales');
                Route::get('get_sum_year/{year}', 'DashboardController@get_sum_year');
                Route::get('filter', 'DashboardController@filter');
                Route::post('data', 'DashboardController@data');
                Route::post('data_aditional', 'DashboardController@data_aditional');
                // Route::post('unpaid', 'DashboardController@unpaid');
                // Route::get('unpaidall', 'DashboardController@unpaidall')->name('unpaidall');
                Route::get('stock-by-product/records', 'DashboardController@stockByProduct');
                Route::get('product-of-due/records', 'DashboardController@productOfDue');
                Route::get('product-of-due-lots/records', 'DashboardController@productOfDueLots');
                Route::post('utilities', 'DashboardController@utilities');
                Route::get('global-data', 'DashboardController@globalData');
                Route::get('sales-by-product', 'DashboardController@salesByProduct');
                Route::get('/api/top-clientes', [DashboardController::class, 'topClientes']);

            });

            //Commands
            Route::get('command/df', 'DashboardController@df')->name('command.df');
        });
    });
}
