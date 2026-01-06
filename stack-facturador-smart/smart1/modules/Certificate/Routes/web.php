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
use Modules\Certificate\Http\Controllers\CertificateController;

Route::get('/certificate/print/{uuid}', [CertificateController::class, 'print'])->name('certificate.print');
Route::middleware(['web', 'auth'])->group(function () {

    Route::prefix('certificate')->group(function () {


        Route::prefix('certificate-template')->group(function () {
            Route::get('/', [CertificateController::class, 'template'])->name('certificate.template.index');
            Route::post('/', [CertificateController::class, 'storeTemplate'])->name('certificate.template.store');
            Route::get('/create/{id?}', [CertificateController::class, 'createTemplate'])->name('certificate.template.create');
            Route::get('/records', [CertificateController::class, 'recordsTemplate'])->name('certificate.template.records');
            Route::get('/all-records', [CertificateController::class, 'allRecordsTemplate'])->name('certificate.template.all-records');
            Route::get('/{id}', [CertificateController::class, 'recordTemplate'])->name('certificate.template.show');
            Route::delete('/{id}', [CertificateController::class, 'deleteTemplate'])->name('certificate.template.delete');
            Route::post('/upload', [CertificateController::class, 'upload'])->name('certificate.template.upload');
        });
        Route::get('/', [CertificateController::class, 'index'])->name('certificate.index');
        Route::post('/import', [CertificateController::class, 'import'])->name('certificate.import');
        Route::get('/records', [CertificateController::class, 'records'])->name('certificate.records');
        Route::get('/create/{id?}', [CertificateController::class, 'create'])->name('certificate.create');
        Route::get('/{id}', [CertificateController::class, 'record'])->name('certificate.show');
        Route::post('/', [CertificateController::class, 'store'])->name('certificate.store');
        Route::get('/create-qr/{id}', [CertificateController::class, 'createQr'])->name('certificate.create-qr');
        Route::delete('/{id}', [CertificateController::class, 'delete'])->name('certificate.delete');
    });
});
