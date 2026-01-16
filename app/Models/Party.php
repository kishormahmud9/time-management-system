<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $guarded = [];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class, 'client_id');
    }

    public function userDetails()
    {
        return $this->hasMany(UserDetail::class, 'party_id');
    }
}
