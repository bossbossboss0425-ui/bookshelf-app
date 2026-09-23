<?php

use App\Http\Controllers\Api\v1\BookApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('books', BookApiController::class);
});
