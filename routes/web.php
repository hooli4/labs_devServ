<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GitHookController;
use App\Http\Controllers\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [AuthController::class, 'test']);

Route::post('/hooks/git', [GitHookController::class, 'handle']);