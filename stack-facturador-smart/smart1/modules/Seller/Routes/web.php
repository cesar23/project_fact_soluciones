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

use Illuminate\Support\Facades\Route;
use Modules\Seller\Http\Controllers\ComissionController;
use Modules\Seller\Http\Controllers\SellerController;

Route::middleware(['auth', 'redirect.module', 'locked.tenant', 'locked.user'])->group(function () {
    Route::prefix('seller')->group(function () {
        Route::get('/', [SellerController::class, 'index'])->name('tenant.seller.index');
        Route::prefix('monthly-sales')->group(function () {
            Route::get('', [SellerController::class, 'monthySalesIndex'])->name('tenant.seller.monthly-sales');
            Route::get('records', [SellerController::class, 'records']);
        });
        Route::prefix('comission')->group(function () {
            Route::get('', [ComissionController::class, 'index'])->name('tenant.comission.index');
            Route::get('records', [ComissionController::class, 'records']);
            Route::get('record/{id}', [ComissionController::class, 'record']);
            Route::get('columns', [ComissionController::class, 'columns']);
            Route::post('', [ComissionController::class, 'store']);
        });
    });
});
