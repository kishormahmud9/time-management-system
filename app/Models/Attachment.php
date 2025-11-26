<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'business_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachable_type',
        'attachable_id',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachable()
    {
        return $this->morphTo();
    }
}
