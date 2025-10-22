<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\RoleAndPermission\PermissionController;
use App\Http\Controllers\RoleAndPermission\RoleController;
use App\Http\Controllers\RoleAndPermission\RoleHasPermissionController;
use App\Http\Controllers\RoleAndPermission\UserHasRoleController;

//////////////////// Auth Related Route /////////////////
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/forget-password', 'forgetPassword');
    Route::post('/otp-varification', 'otpVerify');
    Route::post('/reset-password', 'resetPassword');
});

/////////////////// Private Route for All Role /////////////////
Route::middleware('auth:api')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
    });

    //**** Profile Related Route ****//
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'view');
        Route::post('/profile-edit', 'edit');
        Route::post('/change-password', 'changePassword');
    });
});


//////////////////// Private Route For User Role  /////////////////
Route::middleware(['auth:api', 'role:User'])->group(function () {});

//////////////////// Private Route For Staff Role  /////////////////
Route::middleware(['auth:api', 'role:Staff'])->group(function () {});

//////////////////// Private Route For Business Admin Role  /////////////////
Route::middleware(['auth:api', 'role:Business Admin'])->group(function () {});

//////////////////// Private Route For System Admin Role  /////////////////
Route::middleware(['auth:api', 'role:System Admin'])->group(function () {});

//////////////////// Role & Permission Route Only for Sytem Admin and Busines Admin /////////////////
Route::middleware(['auth:api', 'role:System Admin|Business Admin'])->group(function () {

    //**** Role Related Route ****//
    Route::controller(RoleController::class)->group(function () {
        Route::post('/role', 'store');
        Route::get('/roles', 'view');
        Route::get('/role/{id}', 'viewDetails');
        Route::post('/role/{id}', 'update');
        Route::delete('/role/{id}', 'delete');
    });

    //**** Permission Related Route ****//
    Route::controller(PermissionController::class)->group(function () {
        Route::post('/permission', 'store');
        Route::get('/permissions', 'view');
        Route::get('/permission/{id}', 'viewDetails');
        Route::post('/permission/{id}', 'update');
        Route::delete('/permission/{id}', 'delete');
    });

    //**** RoleHasPermission Related Routes ****//
    Route::controller(RoleHasPermissionController::class)->group(function () {
        Route::post('/role-has-permission', 'store');
    });

    //**** UserHasRole Related Routes ****//
    Route::controller(UserHasRoleController::class)->group(function () {
        Route::post('/user-has-role', 'store');
    });
});
