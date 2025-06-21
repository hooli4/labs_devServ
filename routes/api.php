<?php

use App\Http\Controllers\GitHookController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LogRequestController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;

Route::prefix('/auth')->middleware(['throttle:api', 'redirectIfAuth', 'LogRequest'])->group(function() {
    Route::post("/login", [AuthController::class, 'UserLogin']);
    Route::post("/register", [AuthController::class, 'UserRegister']);
});

Route::prefix('/auth')->middleware(['throttle:api', 'guest', 'LogRequest'])->group(function() {
    Route::post('/requestCode', [TwoFactorAuthController::class, 'requestCode']);
    Route::post('/confirmCode', [TwoFactorAuthController::class, 'confirmCode'])->middleware('redirectIfAuth');
});

Route::prefix("/auth")->middleware(['throttle:api', 'auth:sanctum', 'LogRequest'])->group(function () {
    Route::get("/me", [AuthController::class, "userInfo"]);
    Route::post("/out", [AuthController::class, "logout"]);
    Route::get("/tokens", [AuthController::class, "getTokens"]);
    Route::post("/out_all", [AuthController::class, "deleteTokens"]);
    Route::put("/changePassword", [AuthController::class,"changePassword"]);
    Route::post('/toggle', [TwoFactorAuthController::class, 'toggle']);
    Route::get('/accountUsageInfo', [TwoFactorAuthController::class, 'accountUsageInfo']);
});

Route::prefix("/ref/policy")->middleware(['auth:sanctum', 'LogRequest'])->group(function () {
    Route::get('/role', [RoleController::class, 'getRolesList']);
    Route::get('/role/{id}', [RoleController::class, 'getRole']);
    Route::post('/role', [RoleController::class, 'createRole']);
    Route::put('/role/{id}', [RoleController::class,'updateRole']);
    Route::delete('/role/{id}', [RoleController::class, 'deleteRole']);
    Route::delete('/role/{id}/soft', [RoleController::class,'softDeleteRole']);
    Route::post('/role/{id}/restore', [RoleController::class,'restoreRole']);
    Route::get('/role/{id}/story', [LogController::class, 'showRoleStory']);
    Route::put('/role/{role_id}/log/{log_id}', [LogController::class, 'getBackToRoleLog']);

    Route::post('/role/{role_id}/permission/{permission_id}', [RoleController::class,'ConnectRoleAndPermission']);
    Route::delete('/role/{role_id}/permission/{permission_id}', [RoleController::class,'DeleteConnectRoleAndPermission']);
    Route::delete('/role/{role_id}/permission/{permission_id}/soft', [RoleController::class,'SoftDeleteConnectRoleAndPermission']);
    Route::post('/role/{role_id}/permission/{permission_id}/restore', [RoleController::class,'RestoreConnectRoleAndPermission']);

    Route::get('/permission', [PermissionController::class, 'getPermissionsList']);
    Route::get('/permission/{id}', [PermissionController::class, 'getPermission']);
    Route::post('/permission', [PermissionController::class, 'createPermission']);
    Route::put('/permission/{id}', [PermissionController::class,'updatePermission']);
    Route::delete('/permission/{id}', [PermissionController::class, 'deletePermission']);
    Route::delete('/permission/{id}/soft', [PermissionController::class,'softDeletePermission']);
    Route::post('/permission/{id}/restore', [PermissionController::class,'restorePermission']);
    Route::get('/permission/{id}/story', [LogController::class, 'showPermissionStory']);
    Route::put('/permission/{permission_id}/log/{log_id}', [LogController::class, 'getBackToPermissionLog']);
});

Route::prefix('/ref/user')->middleware(['auth:sanctum', 'LogRequest'])->group(function () {
    Route::get('/', [UserController::class, 'getUsersList']);
    Route::put('/{id}/changeInfo', [UserController::class,'changeUserInfo']);
    Route::get('/{id}/story', [LogController::class,'showUserStory']);
    Route::get('/{user_id}/role', [UserController::class,'getUserRoles']);
    Route::post('/{user_id}/role/{role_id}', [UserController::class,'setUserRole']);
    Route::delete('/{user_id}/role/{role_id}', [UserController::class,'deleteUserRole']);
    Route::delete('/{user_id}/role/{role_id}/soft', [UserController::class,'softDeleteUserRole']);
    Route::post('/{user_id}/role/{role_id}/restore', [UserController::class,'restoreUserRole']);
    Route::put('/{user_id}/log/{log_id}', [LogController::class, 'getBackToUserLog']);
});

Route::prefix('/ref/log/request')->middleware(['auth:sanctum', 'LogRequest'])->group(function () {
    Route::get('', [LogRequestController::class, 'showListLog']);
    Route::get('/{id}', [LogRequestController::class, 'showLog']);
    Route::delete('/{id}', [LogRequestController::class, 'deleteLog']);
});

Route::post('hooks/git', [GitHookController::class, 'handle']);

Route::prefix('/reports')->group(function () {
    Route::get('/getEntityStats', [ReportController::class, 'getEntityStats']);
    Route::get('/getMethodsStats', [ReportController::class, 'getMethodsStats']);
    Route::get('/getUserStats', [ReportController::class, 'getUserStats']);
    Route::get('/generate', [ReportController::class, 'generateReport']);
    Route::get('/create', [ReportController::class, 'create']);
});