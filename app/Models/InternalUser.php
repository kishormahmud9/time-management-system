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
}
