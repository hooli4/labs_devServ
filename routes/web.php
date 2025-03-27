<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::prefix('info')->group(function () {
    Route::get('/server', [MainController::class, 'server_info']);

    Route::get('/client', [MainController::class, 'client_info']);

    Route::get('/database', [MainController::class, 'database_info']);
});


