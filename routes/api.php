<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Company\BusinessController;
use App\Http\Controllers\Mail\EmailTemplateController;
use App\Http\Controllers\Party\ClientController;
use App\Http\Controllers\Party\EmployeeController;
use App\Http\Controllers\Party\PartyController;
use App\Http\Controllers\Party\VendorController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\RoleAndPermission\PermissionController;
use App\Http\Controllers\RoleAndPermission\RoleController;
use App\Http\Controllers\RoleAndPermission\RoleHasPermissionController;
use App\Http\Controllers\RoleAndPermission\UserHasRoleController;
use App\Http\Controllers\User\UserActivityLogController;
use App\Http\Controllers\User\UserManageController;

//////////////////// Auth Related Route /////////////////
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'registerBusinessOwner');
    // Route::post('/register', 'register');
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

    //**** Activity Related Routes ****//
    Route::controller(UserActivityLogController::class)->group(function () {
        Route::get('/manage-activity', 'view');
    });

    //**** User Manage Related Route ****//
    Route::controller(UserManageController::class)->group(function () {
        Route::post('/user', 'store');
        Route::get('/users', 'view');
        Route::get('/user/{id}', 'viewDetails');
        Route::put('/user/{id}', 'update');
        Route::delete('/user/{id}', 'delete');
        Route::patch('/user/{id}', 'statusUpdate');
    });

    //**** Party Related Route ****//
    Route::controller(PartyController::class)->group(function () {
        Route::post('/party', 'store');
        Route::get('/parties', 'view');
        Route::get('/clients', 'getClient');
        Route::get('/vendors', 'getVendor');
        Route::get('/employees', 'getEmployee');
        Route::get('/party/{id}', 'viewDetails');
        Route::put('/party/{id}', 'update');
        Route::delete('/party/{id}', 'delete');
    });

    //**** Email Template Related Route ****//
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::post('/email-template', 'store');
        Route::get('/email-template', 'view');
        Route::get('/email-template/{id}', 'viewDetails');
        Route::put('/email-template/{id}', 'update');
        Route::delete('/email-template/{id}', 'delete');
    });


    //**** Business Related Route ****//
    Route::controller(BusinessController::class)->group(function () {
        Route::post('/business', 'store');
        Route::get('/business', 'view');
        Route::get('/business/{id}', 'viewDetails');
        Route::post('/business/{id}', 'update');
        Route::delete('/business/{id}', 'delete');
        Route::patch('/business/{id}', 'statusUpdate');
    });
});
