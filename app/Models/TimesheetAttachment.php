<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetAttachment extends Model
{
    protected $fillable = [
        'timesheet_id',
        'file_path',
        'original_filename',
        'file_type',
        'file_size'
    ];

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }
}
