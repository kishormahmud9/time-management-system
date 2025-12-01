<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'date',
        'type', // e.g., 'public', 'company'
        'description',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
