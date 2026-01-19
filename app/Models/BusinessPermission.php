<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPermission extends Model
{
    protected $guarded = [];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
