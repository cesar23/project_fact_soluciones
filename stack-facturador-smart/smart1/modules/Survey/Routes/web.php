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

use App\Http\Controllers\Tenant\ItemController;
use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\RespondetController;
use Modules\Survey\Http\Controllers\SurveyController;
use Modules\Survey\Http\Controllers\SurveyQuestionController;
use Modules\Survey\Http\Controllers\SurveyResolveController;
use Modules\Survey\Http\Controllers\SurveySectionController;

Route::get('survey-resolve/resolve/{uuid}', [SurveyResolveController::class, 'login'])->name('survey.resolve');
Route::post('survey-resolve/resolve', [SurveyResolveController::class, 'login_check']);
Route::middleware(['survey.resolve'])->group(function () {
    Route::prefix('resolve')->group(function () {
        Route::get('fill/{uuid}', [SurveyResolveController::class, 'index'])->name('survey.resolve.fill');
        Route::get('sections/{uuid}', [SurveyResolveController::class, 'sections']);
        Route::get('sections-resolve/{uuid}', [SurveyResolveController::class, 'sectionsResolve']);
        Route::get('answers/{uuid}/{section_id}', [SurveyResolveController::class, 'getAnswers']);
        Route::post('answer/{uuid}', [SurveyResolveController::class, 'setAnswer']);
        Route::get('tables/{uuid}', [SurveyResolveController::class, 'tables']);
        Route::post('update-ubigeo/{uuid}', [SurveyResolveController::class, 'setUbigeo']);
        Route::post('check/{uuid}', [SurveyResolveController::class, 'checkAnswers']);
        Route::post('check-completed/{uuid}', [SurveyResolveController::class, 'checkCompleted']);
        Route::post('check-all/{uuid}', [SurveyResolveController::class, 'checkAnswersSections']);
    });
});
Route::middleware(['auth', 'redirect.module', 'locked.tenant', 'locked.user'])->group(function () {
    Route::prefix('survey')->group(function () {
        Route::get('/', [SurveyController::class, 'index'])->name('survey.index');
        Route::post('/', [SurveyController::class, 'store']);
        Route::get('records', [SurveyController::class, 'records']);
        Route::post('image/upload', [ItemController::class, 'upload']);

        Route::get('record/{id}', [SurveyController::class, 'record']);
        Route::get('get-totals/{id}', [SurveyController::class, 'getTotals']);
        Route::get('export-excel/{id}', [SurveyController::class, 'excel']);
        Route::get('export-excel-all/{id}', [SurveyController::class, 'excelAll']);
        Route::get('export-pdf/{id}', [SurveyController::class, 'pdf']);
        Route::get('answers/{respondet_id}/{survey_id}', [SurveyController::class, 'answers']);
        Route::get('get-answers/{survey_response_id}', [SurveyController::class, 'getAnswers']);
        Route::get('get-respondet/{survey_response_id}', [SurveyController::class, 'getRespondet']);

        Route::prefix('section')->group(function () {
            Route::get('/{survey_uuid}', [SurveySectionController::class, 'index'])->name('survey.section.index');
            Route::post('/', [SurveySectionController::class, 'store']);
            Route::get('records/{survey_uuid}', [SurveySectionController::class, 'records']);
            Route::get('record/{id}', [SurveySectionController::class, 'record']);
        });
        Route::prefix('question')->group(function () {
            Route::post('/', [SurveyQuestionController::class, 'store']);
            Route::get('records/{section_id}', [SurveyQuestionController::class, 'records']);
            Route::get('record/{id}', [SurveyQuestionController::class, 'record']);
            Route::delete('{id}', [SurveyQuestionController::class, 'removeRecord']);
        });
        Route::prefix('respondet')->group(function () {
            Route::get('/', [RespondetController::class, 'index'])->name('survey.respondet.index');
            Route::post('/', [RespondetController::class, 'store']);
            Route::post('/{id}/update-password', [RespondetController::class, 'updatePassword']);
            Route::get('records', [RespondetController::class, 'records']);
            Route::get('list/{id}', [RespondetController::class, 'recordsSurvey']);
            Route::get('record/{id}', [RespondetController::class, 'record']);
            Route::get('columns', [RespondetController::class, 'columns']);
        });
    });
});
