<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Company\BusinessController;
use App\Http\Controllers\User\InternalUserController;
use App\Http\Controllers\Mail\EmailTemplateController;
use App\Http\Controllers\Party\PartyController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\RoleAndPermission\PermissionController;
use App\Http\Controllers\RoleAndPermission\RoleController;
use App\Http\Controllers\RoleAndPermission\RoleHasPermissionController;
use App\Http\Controllers\RoleAndPermission\UserHasRoleController;
use App\Http\Controllers\Timesheet\TimesheetManageController;
use App\Http\Controllers\User\UserActivityLogController;
use App\Http\Controllers\User\UserManageController;
use App\Http\Controllers\User\UserDetailsController;

Route::get('/', function () {
    return response()->json([
        'message' => 'Timesheet is running'
    ]);
});
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

    //**** Timesheet Related Route (All Authenticated Users) ****//
    Route::controller(TimesheetManageController::class)->group(function () {
        Route::post('/timesheet', 'store');
        Route::get('/timesheet', 'view');
        Route::get('/timesheet/{id}', 'viewDetails');
        Route::put('/timesheet/{id}', 'update');
        Route::delete('/timesheet/{id}', 'delete');
        Route::patch('/timesheet/{id}', 'statusUpdate');
        Route::get('/timesheet-defaults', 'getDefaults');
    });


    //**** Party Related Route ****//
    Route::controller(PartyController::class)->group(function () {
        Route::get('/parties', 'view');
        Route::get('/clients', 'getClient');
        Route::get('/vendors', 'getVendor');
        Route::get('/employees', 'getEmployee');
        Route::get('/party/{id}', 'viewDetails');
    });
    //**** Email Template Related Route ****//
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::get('/email-template', 'view');
        Route::get('/email-template/{id}', 'viewDetails');
    });

    //**** Permission Related Route ****//
    Route::controller(PermissionController::class)->group(function () {
        Route::get('/permissions', 'view');
        Route::get('/permission/{id}', 'viewDetails');
    });

    //**** Role Related Route ****//
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles', 'view');
        Route::get('/role/{id}', 'viewDetails');
    });
});


//////////////////// Private Route For User Role  /////////////////
Route::middleware(['auth:api', 'role:User'])->group(function () {});

//////////////////// Private Route For Staff Role  /////////////////
Route::middleware(['auth:api', 'role:Staff'])->group(function () {});

//////////////////// Private Route For Business Admin Role  /////////////////
Route::middleware(['auth:api', 'role:Business Admin'])->group(function () {

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

    //**** Email Template Related Route ****//
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::post('/email-template', 'store');
        Route::put('/email-template/{id}', 'update');
        Route::delete('/email-template/{id}', 'delete');
    });
});

//////////////////// Private Route For System Admin Role  /////////////////
Route::middleware(['auth:api', 'role:System Admin'])->group(function () {

    //**** Business Related Route ****//
    Route::controller(BusinessController::class)->group(function () {
        Route::post('/business', 'store');
        Route::get('/business', 'view');
        Route::get('/business/{id}', 'viewDetails');
        Route::post('/business/{id}', 'update');
        Route::delete('/business/{id}', 'delete');
        Route::patch('/business/{id}', 'statusUpdate');
    });


    //**** Permission Related Route ****//
    Route::controller(PermissionController::class)->group(function () {
        Route::post('/permission', 'store');
        Route::post('/permission/{id}', 'update');
        Route::delete('/permission/{id}', 'delete');
    });

    //**** Role Related Route ****//
    Route::controller(RoleController::class)->group(function () {
        Route::post('/role', 'store');
        Route::post('/role/{id}', 'update');
        Route::delete('/role/{id}', 'delete');
    });
});

//////////////////// Role & Permission Route Only for Sytem Admin and Busines Admin /////////////////
Route::middleware(['auth:api', 'role:Business Admin|Staff'])->group(function () {

    //**** User Manage Related Route ****//
    Route::controller(UserManageController::class)->group(function () {
        Route::post('/user', 'store');
        Route::get('/users', 'view');
        Route::get('/user/{id}', 'viewDetails');
        Route::post('/user/{id}', 'update');
        Route::delete('/user/{id}', 'delete');
        Route::patch('/user/{id}', 'statusUpdate');
    });

    //**** Internal User Related Route ****//
    Route::controller(InternalUserController::class)->group(function () {
        Route::post('/internaluser', 'store');
        Route::get('/internalusers', 'view');
        Route::get('/internaluser/{id}', 'viewDetails');
        Route::post('/internaluser/{id}', 'update');
        Route::delete('/internaluser/{id}', 'delete');
        Route::patch('/internaluser/{id}', 'roleUpdate');
    });

    //**** Party Related Route ****//
    Route::controller(PartyController::class)->group(function () {
        Route::post('/party', 'store');
        Route::put('/party/{id}', 'update');
        Route::delete('/party/{id}', 'delete');
    });

    //**** User Details Related Route ****//
    Route::controller(UserDetailsController::class)->group(function () {
        Route::post('/user-details', 'store');
        Route::get('/user-details', 'view');
        Route::get('/user-details/{id}', 'viewDetails');
        Route::post('/user-details/{id}', 'update');
        Route::delete('/user-details/{id}', 'delete');
    });
});
