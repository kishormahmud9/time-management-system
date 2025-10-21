<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\RoleAndPermission\PermissionController;
use App\Http\Controllers\RoleAndPermission\RoleController;
use App\Http\Controllers\RoleAndPermission\RoleHasPermission;

// ////////////////// Auth Related Route /////////////////
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // ////////////////// Profile Related Route /////////////////
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'view');
        Route::post('/profile-edit', 'edit');
        Route::post('/change-password', 'changePassword');
    });
});
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/otp-varification', [AuthController::class, 'otpVerify']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



// ////////////////// Role Related Route /////////////////
Route::controller(RoleController::class)->group(function () {
    Route::post('/role', 'store');
    Route::get('/roles', 'view');
    Route::get('/role/{id}', 'edit');
    Route::post('/role/{id}', 'update');
    Route::delete('/role/{id}', 'delete');
});

// ////////////////// Permission Related Route /////////////////
Route::controller(PermissionController::class)->group(function () {
    Route::post('/permission', 'store');
    Route::get('/permissions', 'view');
    Route::get('/permission/{id}', 'edit');
    Route::post('/permission/{id}', 'update');
    Route::delete('/permission/{id}', 'delete');
});


// ////////////////// RoleHasPermission Related Routes /////////////////
Route::controller(RoleHasPermission::class)->group(function () {
    Route::post('/role-has-permission', 'store');
});
