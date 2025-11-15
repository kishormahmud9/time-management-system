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
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        $data = [
            'user_id'   => $user->id,
            'action'    => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // If user is NOT a system admin, attach business_id (if available)
        // Replace 'system admin' with your actual role name if different.
        if (! $user->hasRole('system admin')) {
            // only add business_id if it exists on user model
            $data['business_id'] = $user->business_id ?? null;
        }

        UserLog::create($data);
    }
}
