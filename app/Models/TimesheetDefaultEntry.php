<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetDefaultEntry extends Model
{
    protected $guarded = [];

    public function timesheetDefault(): BelongsTo
    {
        return $this->belongsTo(TimesheetDefault::class);
    }
}
