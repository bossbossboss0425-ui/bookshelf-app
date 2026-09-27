<?php

use App\Http\Controllers\Api\v1\BookApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Sanctum 認証が必要な書き込み系エンドポイント
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);
    });
});
