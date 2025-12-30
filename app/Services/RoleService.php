<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Exception;

class RoleService
{
    /**
     * Assign a role to $targetUser, checking $actor's permission rules.
     *
     * @param \App\Models\User $actor   // current authenticated user
     * @param \App\Models\User $target  // user to receive role
     * @param int $roleId
     * @return void
     * @throws \Exception on failure (you can customize)
     */
    public function assignRole(User $actor, User $target, int $roleId): void
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new Exception('Role not found.');
        }

        $targetRoleName = $role->name;

        // Authorization rules centralized here
        if ($actor->hasRole('System Admin')) {
            // allowed everything
        } elseif ($actor->hasRole('Business Admin')) {
            $allowed = ['User', 'Staff', 'Business Admin'];
            if (! in_array($targetRoleName, $allowed, true)) {
                throw new Exception('You are not allowed to assign this role.');
            }
        } else {
            throw new Exception('You do not have permission to assign roles.');
        }

        if ($target->hasRole($targetRoleName)) {
            throw new Exception('User already has this role.');
        }

        $target->assignRole($targetRoleName);

        // Optional: log or activity
        // you can call your UserActivityTrait methods from the controller or inject logger here
        Log::info("Role '{$targetRoleName}' assigned to user '{$target->id}' by '{$actor->id}'");
    }

    /**
     * Sync (replace) user's roles with the given roleId after authorization checks.
     * Used on update: remove old roles and assign this new one.
     */
    public function syncUserRole(User $actor, User $target, int $roleId): string
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new Exception("Role not found.");
        }

        $targetRoleName = $role->name;

        // same authorization checks
        if ($actor->hasRole('System Admin')) {
            // allowed
        } elseif ($actor->hasRole('Business Admin')) {
            $allowed = ['User', 'Staff', 'Business Admin'];
            if (! in_array($targetRoleName, $allowed, true)) {
                throw new Exception('You are not allowed to assign this role.');
            }
        } else {
            throw new Exception('You do not have permission to assign roles.');
        }

        // If the target already has exactly this role, nothing to do:
        if ($target->hasRole($targetRoleName) && $target->roles->count() === 1) {
            return $targetRoleName;
        }

        // Replace roles atomically
        $target->syncRoles([$targetRoleName]);

        Log::info("Role updated to '{$targetRoleName}' for user '{$target->id}' by actor '{$actor->id}'");

        return $targetRoleName;
    }
}
