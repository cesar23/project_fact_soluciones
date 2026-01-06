<?php


use Illuminate\Support\Facades\Route;

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($current_hostname) {
    Route::domain($current_hostname->fqdn)
        ->middleware(['redirect.level'])
        ->group(function () {
            Route::middleware(['auth', 'locked.tenant'])
                ->prefix('suscription')
                ->group(function () {
                    /**
                     * suscription/client
                     */

                    Route::prefix('periods')->group(function () {
                        Route::post('/', 'ClientSuscriptionController@save_periods');
                        Route::get('/{document_type_id}/{document_id}', 'ClientSuscriptionController@get_periods');
                    });
                    Route::prefix('client')->group(function () {
                        Route::get('/', 'ClientSuscriptionController@index')->name('tenant.suscription.client.index');
                        Route::get('/childrens', 'ClientSuscriptionController@indexChildren')->name('tenant.suscription.client_children.index');
                        Route::get('/get_child_from_document/{document_id}/{parent_id}/{type}', 'ClientSuscriptionController@get_child_from_document');
                        Route::post('/', 'ClientSuscriptionController@store');
                        Route::get('/files/{person_id}', 'ClientSuscriptionController@get_files');
                        Route::delete('/files/{id}', 'ClientSuscriptionController@delete_file');
                        Route::post('/files/{person_id}', 'ClientSuscriptionController@upload_files');
                        Route::get('/suscription_name', 'ClientSuscriptionController@suscription_name')->name('tenant.suscription_names.index');
                        Route::post('/suscription_name', 'ClientSuscriptionController@suscription_name_store');
                        Route::get('/suscription_name/names', 'ClientSuscriptionController@suscription_name_records');
                        Route::post('/suscription/setcolor', 'ClientSuscriptionController@set_color');
                        Route::post('/suscription/setdate', 'ClientSuscriptionController@set_date');

                        Route::get('/columns', 'ClientSuscriptionController@Columns');
                        Route::post('/records', 'ClientSuscriptionController@Records');
                        Route::get('/excel', 'ClientSuscriptionController@ExcelRecords');
                        Route::post('/tables', 'ClientSuscriptionController@Tables');
                        Route::post('/record', 'ClientSuscriptionController@Record');
                        Route::post('/itemPlan', 'ClientSuscriptionController@itemPlan');
                    });
                    /**
                     * suscription/service
                     */
                    Route::prefix('service')->group(function () {
                        Route::get('/', 'ServiceSuscriptionController@index')
                            ->name('tenant.suscription.service.index')
                            ->middleware(['redirect.level']);
                        /*

                        Route::get('/columns', 'ServiceSuscriptionController@Columns');
                        Route::post('/records', 'ServiceSuscriptionController@Records');
                        Route::post('/tables', 'ServiceSuscriptionController@Tables');
                        Route::post('/record', 'ServiceSuscriptionController@Record');
                        */
                    });
                    // items/export/barcode/last

                    /**
                     * suscription/plans
                     */
                    Route::prefix('plans')->group(function () {
                        Route::get('/', 'PlansSuscriptionController@index')
                            ->name('tenant.suscription.plans.index')
                            ->middleware(['redirect.level']);
                        Route::post('/', 'PlansSuscriptionController@store');

                        Route::get('/columns', 'PlansSuscriptionController@Columns');
                        Route::post('/records', 'PlansSuscriptionController@Records');
                        Route::post('/tables', 'PlansSuscriptionController@Tables');
                        Route::post('/record', 'PlansSuscriptionController@Record');

                        Route::delete('/{id}', 'PlansSuscriptionController@destroy');
                    });

                    /**
                     * suscription/payments
                     */
                    Route::prefix('payments')->group(function () {

                        /*
                        Route::get('/', 'SuscriptionController@payments_index')
                            ->name('tenant.suscription.payments.index')
                            ->middleware(['redirect.level']);
                        */

                        Route::get('/', 'PaymentsSuscriptionController@index')
                            ->name('tenant.suscription.payments.index')
                            ->middleware(['redirect.level']);
                        Route::post('/', 'PaymentsSuscriptionController@store');

                        Route::get('/columns', 'PaymentsSuscriptionController@Columns');
                        Route::post('/records', 'PaymentsSuscriptionController@Records');
                        Route::post('/tables', 'PaymentsSuscriptionController@Tables');
                        Route::post('/record', 'PaymentsSuscriptionController@Record');
                        Route::post('/search/customers', 'PaymentsSuscriptionController@searchCustomer');
                        Route::delete('/delete/{id}', 'PaymentsSuscriptionController@Delete');
                    });
                    /**
                     * suscription/payment_receipt
                     */
                    Route::prefix('payment_receipt')->group(function () {
                        Route::get('/', 'PaymentReceiptSuscriptionController@index')
                            ->name('tenant.suscription.payment_receipt.index');
                        
                        Route::get('/columns', 'PaymentReceiptSuscriptionController@columns');
                        Route::get('/records', 'PaymentReceiptSuscriptionController@records');
                    });


                    // grados y secciones
                    Route::get('grade_section', 'SuscriptionController@indexGradeSection')->name('tenant.suscription.grade_section.index');

                    Route::prefix('grades')->group(function () {

                        Route::get('records', 'GradeController@records');
                        Route::get('columns', 'GradeController@columns');
                        Route::get('record/{id}', 'GradeController@record');
                        Route::post('', 'GradeController@store');
                        Route::delete('{id}', 'GradeController@destroy');
                    });

                    Route::prefix('sections')->group(function () {

                        Route::get('records', 'SectionController@records');
                        Route::get('columns', 'SectionController@columns');
                        Route::get('record/{id}', 'SectionController@record');
                        Route::post('', 'SectionController@store');
                        Route::delete('{id}', 'SectionController@destroy');
                    });
                    // grados y secciones
                    Route::prefix('college')->group(function () {

                        Route::get('section_grades', 'CollegeController@records');
                        Route::post('save_observation', 'CollegeController@save_observation');
                        Route::get('documents/{month}/{year}/{child_id}/{parent_id}', 'CollegeController@documents');
                    });

                    Route::post('CommonData', 'SuscriptionController@Tables');
                });
        });
}
