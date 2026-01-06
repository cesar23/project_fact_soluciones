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
use Modules\Payroll\Http\Controllers\PayrollController;

Route::prefix('payroll')->group(function() {
    Route::get('/', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/', [PayrollController::class, 'store']);
    Route::get('/records', [PayrollController::class, 'records']);
    Route::get('/tables', [PayrollController::class, 'tables']);
    Route::get('/get-code', [PayrollController::class, 'getCode']);
    Route::get('/columns', [PayrollController::class, 'columns']);
    Route::get('/record/{id}', [PayrollController::class, 'record']);
    Route::delete('/{id}', [PayrollController::class, 'destroy']);
    Route::get('/export-excel', [PayrollController::class, 'exportExcel']);
    Route::get('/export-pdf', [PayrollController::class, 'exportPdf']); // New route for PDF export

});
