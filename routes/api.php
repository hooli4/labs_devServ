<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('/auth')->middleware(['throttle:api'])->group(function() {
    Route::post("/login", [AuthController::class, 'UserLogin']);
    Route::post("/register", [AuthController::class, 'UserRegister'])->middleware('guest');
});

Route::prefix("/auth")->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get("/me", [AuthController::class, "userInfo"]);
    Route::post("/out", [AuthController::class, "logout"]);
    Route::get("/tokens", [AuthController::class, "getTokens"]);
    Route::post("/out_all", [AuthController::class, "deleteTokens"]);
    Route::post("/changePassword", [AuthController::class,"changePassword"]);
});