<?php

use App\Http\Controllers\FetchCommandController;
use App\Http\Controllers\GoogleSheetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FetchController;
use App\Http\Controllers\DocumentController;

Route::get('/', [DocumentController::class, 'index']);

Route::get('/fetch1/{count?}', [FetchController::class, 'fetch'])
    ->where('count', '\d+')
    ->name('fetch.data1');

Route::get('/fetch/{count?}', [FetchCommandController::class, 'fetch'])
    ->where('count', '\d+')
    ->name('fetch.data');

Route::prefix('api')->group(function () {
    // Документы
    Route::prefix('documents')->group(function () {

        Route::get('/', [DocumentController::class, 'getDocuments']);

        Route::post('/', [DocumentController::class, 'store']);
        Route::post('/generate', [DocumentController::class, 'generate']);
        Route::post('/clear', [DocumentController::class, 'clear']);

        Route::put('/{document}', [DocumentController::class, 'update']);
        Route::delete('/{document}', [DocumentController::class, 'destroy']);
    });

    Route::prefix('google-sheet')->group(function () {
        Route::post('/set-url', [GoogleSheetController::class, 'setUrl']);
        Route::get('/sync', [GoogleSheetController::class, 'syncNow']);
    });
});
