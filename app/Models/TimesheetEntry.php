<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    protected $table = 'timesheet_entries';

    protected $fillable = [
        'business_id',
        'timesheet_id',
        'entry_date',
        'daily_hours',
        'extra_hours',
        'vacation_hours',
        'note',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'daily_hours' => 'decimal:2',
        'extra_hours' => 'decimal:2',
        'vacation_hours' => 'decimal:2',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    // Helper methods
    public function getTotalHours(): float
    {
        return (float) ($this->daily_hours + $this->extra_hours);
    }

    public function getAllHours(): float
    {
        return (float) ($this->daily_hours + $this->extra_hours + $this->vacation_hours);
    }

    public function isWeekend(): bool
    {
        return in_array($this->entry_date->dayOfWeek, [0, 6]); // Sunday = 0, Saturday = 6
    }

    public function isWeekday(): bool
    {
        return !$this->isWeekend();
    }
}
