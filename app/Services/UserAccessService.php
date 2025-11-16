<?php

namespace App\Services;

use App\Models\User;

class UserAccessService
{
    /**
     * Check if actor can view target user.
     */
    public function canViewUser(User $actor, User $target): bool
    {
        // System Admin → can see any user
        if ($actor->hasRole('System Admin')) {
            return true;
        }

        // Non-system users: same business only
        return $actor->business_id === $target->business_id;
    }

    /**
     * Check if actor can modify (update/delete) target user.
     * Adjust rules as per your requirements.
     */
    public function canModifyUser(User $actor, User $target): bool
    {
        // System Admin -> can modify anyone
        if ($actor->hasRole('System Admin')) {
            return true;
        }

        // Business Admin -> can modify users inside same business, but cannot modify System Admin
        if ($actor->hasRole('Business Admin')) {
            if ($target->hasRole('System Admin')) {
                return false;
            }
            return $actor->business_id === $target->business_id;
        }

        // Other roles (Staff, User etc.) -> no modify permission by default
        return false;
    }

    /**
     * Filter users list based on actor role
     */
    public function filterUsersBasedOnAccess(User $actor)
    {
        if ($actor->hasRole('System Admin')) {
            return User::with('roles')->get();
        }

        return User::with('roles')
            ->where('business_id', $actor->business_id)
            ->get();
    }
}
