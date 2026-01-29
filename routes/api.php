<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Chart\ChartController;
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
use App\Http\Controllers\SystemDashboardController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\UserDashboardController;

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
        Route::post('/company-update', 'companyUpdate');
        Route::get  ('/company', 'companyView');
        Route::post ('/update-weekend', 'updateWeekend');
    });

    //**** Timesheet Related Route (All Authenticated Users) ****//
    Route::controller(TimesheetManageController::class)->group(function () {
        Route::post('/timesheet', 'store');
        Route::get('/timesheet', 'view');
        Route::get('/timesheet/{id}', 'viewDetails');
        // Route::put('/timesheet/{id}', 'update');
        // Route::delete('/timesheet/{id}', 'delete');
        // Route::patch('/timesheet/{id}', 'statusUpdate');
        Route::get('/timesheet-defaults', 'getDefaults');
        Route::get('/user/{id}/timesheet-defaults', 'getUserDefaults');
        Route::get('/scheduler', 'scheduler');
        Route::get('/attachment/{id}/download', 'downloadAttachment');
    });

    //**** Chart/Analytics Related Route (All Authenticated Users) ****//
    Route::controller(ChartController::class)->group(function () {
        Route::get('/chart/summary', 'summary');
        Route::get('/chart/trend', 'trend');
    });

    Route::get('/revenue/dashboard-data', [\App\Http\Controllers\Chart\RevenueReportController::class, 'index']);
    Route::get('/consultant/dashboard-data', [\App\Http\Controllers\Chart\ConsultantDashboardController::class, 'index']);
    Route::get('/hours/dashboard-data', [\App\Http\Controllers\Chart\HoursDashboardController::class, 'index']);
    Route::get('/user-dashboard-data', [UserDashboardController::class, 'index']);


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
        Route::get('/user-permissions', 'userPermission');
        Route::get('/supervisor-permissions', 'supervisorPermission');
        Route::get('/supervisor-available-permissions', 'supervisorAvailablePermission');
        Route::get('/user-available-permissions', 'userAvailablePermission');
        Route::get('/permission/{id}', 'viewDetails');
    });

    //**** Role Related Route ****//
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles', 'view');
        Route::get('/role/{id}', 'viewDetails');
    });

    //**** Holiday Related Route (All Authenticated Users) ****//
    Route::controller(HolidayController::class)->group(function () {
        Route::get('/holidays', 'view');
        Route::get('/holiday/{id}', 'viewDetails');
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

    //**** Holiday CRUD Routes (Business Admin) ****//
    Route::controller(HolidayController::class)->group(function () {
        Route::post('/holiday', 'store');
        Route::post('/holiday/{id}', 'update');
        Route::delete('/holiday/{id}', 'delete');
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

    //**** SystemDashboard related route ****//
    Route::controller(SystemDashboardController::class)->group(function () {
        Route::get('/system-dashboard', 'view');
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

    //**** Timesheet Related Route (All Authenticated Users) ****//
    Route::controller(TimesheetManageController::class)->group(function () {
        Route::post('/timesheet/{id}', 'update');
        Route::delete('/timesheet/{id}', 'delete');
        Route::patch('/timesheet/{id}', 'statusUpdate');
    });

    //**** StaffDashboard related route ****//
    Route::controller(StaffDashboardController::class)->group(function () {
        Route::get('/staff-dashboard', 'view');
    });

    //**** SupervisorDashboard related route ****//
    Route::controller(\App\Http\Controllers\SupervisorDashboardController::class)->group(function () {
        Route::get('/supervisor-dashboard-data', 'view');
    });

    //**** Email Template Related Route ****//
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::post('/email-template', 'store');
        Route::put('/email-template/{id}', 'update');
        Route::delete('/email-template/{id}', 'delete');
    });
});
