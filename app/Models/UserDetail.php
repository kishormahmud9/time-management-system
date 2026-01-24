<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Core Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Users (Roles)
    |--------------------------------------------------------------------------
    */

    public function accountManager()
    {
        return $this->belongsTo(
            InternalUser::class,
            'account_manager_id'
        );
    }

    public function businessDevelopmentManager()
    {
        return $this->belongsTo(
            InternalUser::class,
            'business_development_manager_id'
        );
    }

    public function recruiter()
    {
        return $this->belongsTo(
            InternalUser::class,
            'recruiter_id'
        );
    }

    public function timesheetDefault()
    {
        return $this->hasOne(TimesheetDefault::class, 'user_details_id');
    }
}
