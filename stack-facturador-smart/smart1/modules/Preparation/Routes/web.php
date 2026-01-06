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
use Modules\Preparation\Http\Controllers\OrderTransformationController;
use Modules\Preparation\Http\Controllers\PreparationController;
use Modules\Preparation\Http\Controllers\RegisterInputsMovementController;

Route::middleware(['auth', 'locked.tenant'])->group(function () {
    Route::prefix('preparation')->group(function () {
        Route::get('/', [PreparationController::class, 'index'])->name('tenant.preparation.index');

        Route::prefix('order-transformation')->group(function () {
            Route::get('/', [OrderTransformationController::class, 'index'])->name('tenant.order-transformation.index');
            Route::get('create/{id?}', [OrderTransformationController::class, 'create'])->name('tenant.order-transformation.create');
            Route::get('columns', [OrderTransformationController::class, 'columns']);
            Route::get('records', [OrderTransformationController::class, 'records']);
            Route::get('tables', [OrderTransformationController::class, 'tables']);
            Route::get('search-persons', [OrderTransformationController::class, 'searchPersons']);
            Route::get('search-raw-materials', [OrderTransformationController::class, 'searchRawMaterials']);
            Route::get('search-final-products', [OrderTransformationController::class, 'searchFinalProducts']);
            Route::get('get-item-stock', [OrderTransformationController::class, 'getItemStock']);
            Route::post('', [OrderTransformationController::class, 'store']);
            Route::get('record/{id}', [OrderTransformationController::class, 'record']);
            Route::get('{id}/pdf', [OrderTransformationController::class, 'pdf']);
            Route::post('{id}', [OrderTransformationController::class, 'update']);
            Route::post('{id}/change-status', [OrderTransformationController::class, 'changeStatus']);
            Route::delete('{id}', [OrderTransformationController::class, 'destroy']);
        });

        Route::prefix('register-inputs-movements')->group(function () {
            Route::get('/', [RegisterInputsMovementController::class, 'index'])->name('tenant.register-inputs-movements.index');
            Route::get('columns', [RegisterInputsMovementController::class, 'columns']);
            Route::get('records', [RegisterInputsMovementController::class, 'records']);
            Route::get('tables', [RegisterInputsMovementController::class, 'tables']);
            Route::get('search-items', [RegisterInputsMovementController::class, 'searchItems']);
            Route::get('search-providers', [RegisterInputsMovementController::class, 'searchProviders']);
            Route::post('', [RegisterInputsMovementController::class, 'store']);
            Route::get('record/{id}', [RegisterInputsMovementController::class, 'record']);
            Route::post('{id}', [RegisterInputsMovementController::class, 'update']);
            Route::delete('{id}', [RegisterInputsMovementController::class, 'destroy']);
        });
    });
});
