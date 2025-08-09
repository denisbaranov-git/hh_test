<?php

use App\Http\Controllers\FetchCommandController;
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
    Route::get('/documents', [DocumentController::class, 'getDocuments']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::post('/documents/generate', [DocumentController::class, 'generate']);
    Route::post('/documents/clear', [DocumentController::class, 'clear']);
    Route::post('/documents/set-sheet-url', [DocumentController::class, 'setGoogleSheetUrl']);

    Route::get('/documents/sync-to-google', [DocumentController::class, 'syncNow']);
});
