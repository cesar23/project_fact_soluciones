<?php


use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'locked.tenant'])->group(function () {
    Route::prefix('optometry-services')->group(function () {
        Route::get('', 'OptometryServiceController@index')->name('tenant.optometry_services.index')->middleware(['redirect.level']);
        Route::get('/columns', 'OptometryServiceController@columns');
        Route::get('/records', 'OptometryServiceController@records');
        Route::get('/tables', 'OptometryServiceController@tables');
        Route::post('', 'OptometryServiceController@store');
        Route::get('/record/{contract}', 'OptometryServiceController@record');
        Route::get('/search/customers', 'OptometryServiceController@searchCustomers');
        Route::get('/search/customer/{id}', 'OptometryServiceController@searchCustomerById');
        Route::get('/download/{id}/{format?}', 'OptometryServiceController@download');
        Route::get('/print/{id}/{format?}', 'OptometryServiceController@toPrint');
        Route::get('/format_vehicle/{id}', 'OptometryServiceController@format_vehicle');
        Route::delete('/{id}', 'OptometryServiceController@destroy');
    });

    Route::prefix('optometry-service-payments')->group(function () {

        Route::get('/records/{record}', 'OptometryServicePaymentController@records');
        Route::get('/document/{record}', 'OptometryServicePaymentController@document');
        Route::get('/tables', 'OptometryServicePaymentController@tables');
        Route::post('', 'OptometryServicePaymentController@store');
        Route::delete('/{record_payment}', 'OptometryServicePaymentController@destroy');
    });
});
