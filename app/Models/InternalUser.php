<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalUser extends Model
{
    protected $guarded = [];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function accountManagerDetails()
    {
        return $this->hasMany(UserDetail::class, 'account_manager_id');
    }

    public function bdManagerDetails()
    {
        return $this->hasMany(UserDetail::class, 'business_development_manager_id');
    }

    public function recruiterDetails()
    {
        return $this->hasMany(UserDetail::class, 'recruiter_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class, 'user_id'); // Assuming user_id in timesheets can refer to InternalUser id if they share the same space or if it's relevant
    }
}
