<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'business_id',
        'client_id',
        'name',
        'code',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function client()
    {
        return $this->belongsTo(Party::class, 'client_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }
}
