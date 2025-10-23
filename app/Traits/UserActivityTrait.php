<?php

namespace App\Traits;

use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

trait UserActivityTrait
{
    /**
     * Log user activity
     *
     * @param string $action
     * @return void
     */
    public function logActivity(string $action): void
    {
        if (Auth::check()) {
            UserLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
