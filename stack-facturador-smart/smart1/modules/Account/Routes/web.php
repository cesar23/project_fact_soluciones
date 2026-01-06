<?php

use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountAutomaticController;
use Modules\Account\Http\Controllers\AccountSubDiaryController;
use Modules\Account\Http\Controllers\LedgerAccountController;
use Modules\Account\Http\Controllers\System\SystemLedgerAccountController;
use Modules\Account\Http\Controllers\System\SystemSubdiariesController;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'redirect.module', 'locked.tenant'])->group(function () {

            Route::prefix('account')->group(function () {
                Route::prefix('ledger_accounts')->group(function () {
                    Route::get('/form0710/{period_id}', [LedgerAccountController::class, 'form0710']);
                    Route::get('/', [LedgerAccountController::class, 'indexAccountLedger'])->name('tenant.account_ledger_accounts.index');
                    Route::get('/records', [LedgerAccountController::class, 'records']);
                    Route::get('/export', [LedgerAccountController::class, 'exportRecords']);
                    Route::get('/record/desactive/{code}', [LedgerAccountController::class, 'desactive']);
                    Route::get('/record/active/{code}', [LedgerAccountController::class, 'active']);
                    Route::post('/', [LedgerAccountController::class, 'store']);
                    Route::delete('/{code}', [LedgerAccountController::class, 'destroy']);
                });
                Route::prefix('automatic')->group(function () {});
                Route::prefix('periods')->group(function () {
                    Route::get('/', [\Modules\Account\Http\Controllers\AccountPeriodController::class, 'index'])->name('tenant.account_periods.index');
                    Route::get('records', [\Modules\Account\Http\Controllers\AccountPeriodController::class, 'records']);
                    Route::get('record/{id}', [\Modules\Account\Http\Controllers\AccountPeriodController::class, 'record']);
                    Route::post('/', [\Modules\Account\Http\Controllers\AccountPeriodController::class, 'store']);
                    Route::delete('{id}', [\Modules\Account\Http\Controllers\AccountPeriodController::class, 'destroy']);
                });

                Route::prefix('months')->group(function () {
                    Route::get('period/{period_id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'index'])->name('tenant.account_months.index');
                    Route::get('records/{period_id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'records']);
                    Route::get('record/{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'record']);
                    Route::post('/', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'store']);
                    Route::delete('{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'destroy']);
                    Route::get('export-balance-monthly/{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'exportBalanceMonthly']);
                    Route::get('export-balance-annual/{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'exportBalanceAnnual']);
                    Route::get('export-diary-monthly/{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'exportDiary']);
                    Route::get('export-diary-simplified/{id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'exportDiarySimplified']);
                    Route::get('export-major/{period_id}', [\Modules\Account\Http\Controllers\AccountMonthController::class, 'exportMajor']);
                });

                Route::prefix('automatic')->group(function () {
                    Route::post('/disable', [AccountAutomaticController::class, 'disable']);
                    Route::post('/enable', [AccountAutomaticController::class, 'enable']);
                    Route::get('/records', [AccountAutomaticController::class, 'records']);
                    Route::post('/', [AccountAutomaticController::class, 'store']);
                    Route::get('/{id}', [AccountAutomaticController::class, 'record']);
                    Route::post('/delete', [AccountAutomaticController::class, 'delete']);
                    Route::post('/process-documents', [AccountAutomaticController::class, 'processDocuments']);
                });
                Route::prefix('sub_diaries')->group(function () {
                    Route::get('/', [AccountSubDiaryController::class, 'index'])->name('tenant.account_sub_diaries.index');
                    Route::get('month/{month_id}', [AccountSubDiaryController::class, 'index'])->name('tenant.account_sub_diaries.month');
                    Route::get('record', [AccountSubDiaryController::class, 'record']);
                    Route::get('records', [AccountSubDiaryController::class, 'records']);
                    Route::get('records/{month_id}', [AccountSubDiaryController::class, 'records']);
                    Route::get('items/{id}', [AccountSubDiaryController::class, 'items']);
                    Route::post('/', [AccountSubDiaryController::class, 'store']);
                    Route::get('create', [AccountSubDiaryController::class, 'createWithSelection'])->name('tenant.account_sub_diaries.create_with_selection');
                    Route::get('create_automatic', [AccountAutomaticController::class, 'index'])->name('tenant.account_sub_diaries.create_automatic');
                    Route::post('check-adjustments', [AccountAutomaticController::class, 'checkAndApplyAdjustments'])->name('tenant.account_sub_diaries.check_adjustments');
                    Route::get('accounts', [AccountSubDiaryController::class, 'getAccounts']);
                    Route::get('accounts-by-name-or-code', [AccountSubDiaryController::class, 'getAccountsByNameOrCode']);
                    Route::get('account-by-code/{code}', [AccountSubDiaryController::class, 'getAccountByCode']);
                    Route::get('create/{month_id}', [AccountSubDiaryController::class, 'create'])->name('tenant.account_sub_diaries.create');
                    Route::delete('{id}', [AccountSubDiaryController::class, 'destroy']);
                });
                Route::get('detail/{code}', [AccountSubDiaryController::class, 'detail']);
                Route::get('/', 'AccountController@index')->name('tenant.account.index');
                Route::get('download', 'AccountController@download');
                Route::get('format', 'FormatController@index')->name('tenant.account_format.index');
                Route::get('format/download', 'FormatController@download');
                Route::get('ple', 'PleController@index')->name('tenant.account_ple.index');
                Route::get('ple/generate', 'PleController@generate');
                Route::get('tax_return', 'TaxReturnController@index')->name('tenant.tax_return.index');
                Route::get('tax_return/records', 'TaxReturnController@records');


                Route::get('summary-report', 'SummaryReportController@index')->name('tenant.account_summary_report.index');
                Route::get('summary-report/records', 'SummaryReportController@records');
                Route::get('summary-report/format/download', 'SummaryReportController@download');
            });

            Route::prefix('company_accounts')->group(function () {
                Route::get('create', 'CompanyAccountController@create')->name('tenant.company_accounts.create')->middleware('redirect.level');
                Route::get('record', 'CompanyAccountController@record');
                Route::post('', 'CompanyAccountController@store');
            });

            Route::prefix('accounting_ledger')->group(function () {
                Route::get('/', 'LedgerAccountController@index')->name('tenant.accounting_ledger.create');
                // accounting_ledger?date_end=2021-10-24&date_start=2021-10-24&month_end=2021-10&month_start=2021-10&period=month
                Route::get('/excel/', 'LedgerAccountController@excel');
                //->middleware('redirect.level')
                Route::post('record', 'LedgerAccountController@record');
            });
        });
    });
} else {

    $prefix = env('PREFIX_URL', null);
    $prefix = !empty($prefix) ? $prefix . "." : '';
    $app_url = $prefix . env('APP_URL_BASE');

    Route::domain($app_url)->group(function () {

        Route::middleware('auth:admin')->group(function () {

            Route::prefix('accounting')->group(function () {

                Route::get('', 'System\AccountingController@index')->name('system.accounting.index');
                Route::get('records', 'System\AccountingController@records');
                Route::get('download', 'System\AccountingController@download');


                Route::prefix('ledger_accounts')->group(function () {
                    Route::get('', [SystemLedgerAccountController::class, 'index'])->name('system.accounting.ledger_accounts');
                    Route::get('records', [SystemLedgerAccountController::class, 'records']);
                    Route::post('edit', [SystemLedgerAccountController::class, 'edit']);
                    Route::delete('delete/{code}', [SystemLedgerAccountController::class, 'delete']);
                    
                });

                Route::prefix('subdiaries')->group(function () {
                    Route::get('', [SystemSubdiariesController::class, 'index'])->name('system.accounting.subdiaries');
                    Route::get('records', [SystemSubdiariesController::class, 'records']);
                    Route::get('accounts', [SystemSubdiariesController::class, 'accounts']);
                    Route::post('store', [SystemSubdiariesController::class, 'store']);
                });
            });
        });
    });
}
