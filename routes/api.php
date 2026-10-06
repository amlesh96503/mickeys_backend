<?php
// routes/api.php

use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\AdminAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('user')->group(function () {

    // ========== PUBLIC ==========
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login',    [UserAuthController::class, 'login']);

    // ========== PROTECTED ==========
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile',  [UserAuthController::class, 'profile']);
        Route::put('profile',  [UserAuthController::class, 'updateProfile']);
        Route::post('logout',  [UserAuthController::class, 'logout']);

        Route::get('all',     [UserAuthController::class, 'index']);
        Route::get('{id}',    [UserAuthController::class, 'show']);
        Route::delete('{id}', [UserAuthController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {

    // ========== PUBLIC ==========
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login',    [AdminAuthController::class, 'login']);

    // ========== PROTECTED ==========
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AdminAuthController::class, 'profile']);
        Route::put('profile', [AdminAuthController::class, 'updateProfile']);
        Route::post('logout', [AdminAuthController::class, 'logout']);

        // Sab admins view kar sakte hain
        Route::get('all',  [AdminAuthController::class, 'index']);
        Route::get('{id}', [AdminAuthController::class, 'show']);

        // Only super_admin
        Route::middleware('role:super_admin')->group(function () {
            Route::put('{id}',    [AdminAuthController::class, 'update']);
            Route::delete('{id}', [AdminAuthController::class, 'destroy']);
        });
    });
});
