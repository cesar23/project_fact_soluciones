<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
    
    Route::prefix('dashboard')->group(function () {
        Route::get('filter', 'DashboardController@filter');
        Route::get('global-data', 'DashboardController@globalData');
        Route::post('data', 'DashboardController@data');
        Route::post('data_aditional', 'DashboardController@data_aditional');
        Route::post('utilities', 'DashboardController@utilities');
    });
});