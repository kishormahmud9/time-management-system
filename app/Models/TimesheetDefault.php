<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetDefault extends Model
{
    protected $guarded = [];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default hours for a user or business
     * 
     * @param int $businessId
     * @param int|null $userId
     * @return self|null
     */
    public static function getDefaults(int $businessId, ?int $userId = null): ?self
    {
        // Try user-specific default first
        if ($userId) {
            $userDefault = self::where('business_id', $businessId)
                ->where('user_id', $userId)
                ->first();

            if ($userDefault) {
                return $userDefault;
            }
        }

        // Fall back to business-wide default (user_id = null)
        return self::where('business_id', $businessId)
            ->whereNull('user_id')
            ->first();
    }

    /**
     * Check if this is a business-wide default
     */
    public function isBusinessDefault(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Check if this is a user-specific default
     */
    public function isUserDefault(): bool
    {
        return $this->user_id !== null;
    }
}
