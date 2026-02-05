<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessPermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BusinessPermissionService
{
    /**
     * Get permission for a business
     */
    public function getPermissions($businessId)
    {
        if (!$businessId) return null;

        return Cache::remember("business_permissions_{$businessId}", 3600, function () use ($businessId) {
            return BusinessPermission::where('business_id', $businessId)->first();
        });
    }

    /**
     * Check if users in this business can login
     */
    public function canLogin($businessId)
    {
        $permissions = $this->getPermissions($businessId);
        return $permissions ? (bool)$permissions->user_can_login : true;
    }

    /**
     * Check if business can use email templates
     */
    public function canUseTemplates($businessId)
    {
        $permissions = $this->getPermissions($businessId);
        return $permissions ? (bool)$permissions->template_can_add : true;
    }

    /**
     * Check if business can use commission features
     */
    public function canUseCommissions($businessId)
    {
        $permissions = $this->getPermissions($businessId);
        return $permissions ? (bool)$permissions->commission : true;
    }

    /**
     * Check if business can create more users
     */
    public function canCreateUser($businessId)
    {
        $permissions = $this->getPermissions($businessId);
        if (!$permissions || $permissions->user_limit <= 0) {
            return true; // 0 or null means unlimited
        }

        $currentUsersCount = User::where('business_id', $businessId)->count();
        return $currentUsersCount < $permissions->user_limit;
    }

    /**
     * Get user limit for a business
     */
    public function getUserLimit($businessId)
    {
        $permissions = $this->getPermissions($businessId);
        return $permissions ? $permissions->user_limit : 0;
    }

    /**
     * Clear cache for business permissions
     */
    public function clearCache($businessId)
    {
        Cache::forget("business_permissions_{$businessId}");
    }
}
