<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class UserAccessService
{
    /**
     * Generic check: can $actor modify (update/delete) the given $resource?
     *
     * Rules (example):
     *  - System Admin -> can modify anything
     *  - Business Admin -> can modify resources that belong to same business_id
     *      * additionally: Business Admin cannot modify System Admin user (if resource is User)
     *  - Others -> cannot modify
     *
     * @param User $actor
     * @param Model $resource  Any Eloquent model that has 'business_id' attribute (or User model)
     * @return bool
     */
    public function canModifyResource(User $actor, Model $resource): bool
    {
        // System Admin -> full access
        if ($actor->hasRole('System Admin')) {
            return true;
        }

        // Only Business Admins can modify resources in their business
        if ($actor->hasRole('Business Admin')) {
            // Special rule for User model: don't allow Business Admin to modify System Admin
            if ($resource instanceof User) {
                if (method_exists($resource, 'hasRole') && $resource->hasRole('System Admin')) {
                    return false;
                }
            }

            // Resource must have business_id attribute
            if (! $this->resourceHasBusinessId($resource)) {
                return false;
            }

            return (int)$actor->business_id === (int)$resource->getAttribute('business_id');
        }

        // default: no permission
        return false;
    }

    /**
     * Generic view check: can $actor view $resource?
     * (Usually view is less restrictive: same business OR System Admin)
     */
    public function canViewResource(User $actor, Model $resource): bool
    {
        if ($actor->hasRole('System Admin')) {
            return true;
        }

        if (! $this->resourceHasBusinessId($resource)) {
            return false;
        }

        return (int)$actor->business_id === (int)$resource->getAttribute('business_id');
    }

    /**
     * Return a query builder for $modelClass filtered by actor's business (or unfiltered for System Admin).
     *
     * Usage: $query = $this->access->filterByBusiness($actor, \App\Models\Party::class);
     *        $items = $query->where(...)->get();
     *
     * @param User $actor
     * @param class-string<Model> $modelClass
     * @return Builder
     */
    public function filterByBusiness(User $actor, string $modelClass): Builder
    {
        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("$modelClass is not an Eloquent model");
        }

        $q = $modelClass::query();

        if ($actor->hasRole('System Admin')) {
            return $q;
        }

        // assume model has business_id column
        return $q->where('business_id', $actor->business_id);
    }

    /**
     * Helper: check if resource/model instance has business_id attribute
     */
    protected function resourceHasBusinessId(Model $resource): bool
    {
        // getAttribute returns null if not exists
        // also allow relation access: $resource->business_id
        return array_key_exists('business_id', $resource->getAttributes()) || $resource->offsetExists('business_id');
    }




    // /**
    //  * Check if actor can view target user.
    //  */
    // public function canViewUser(User $actor, User $target): bool
    // {
    //     // System Admin → can see any user
    //     if ($actor->hasRole('System Admin')) {
    //         return true;
    //     }

    //     // Non-system users: same business only
    //     return $actor->business_id === $target->business_id;
    // }

    // /**
    //  * Check if actor can modify (update/delete) target user.
    //  * Adjust rules as per your requirements.
    //  */
    // public function canModifyUser(User $actor, User $target): bool
    // {
    //     // System Admin -> can modify anyone
    //     if ($actor->hasRole('System Admin')) {
    //         return true;
    //     }

    //     // Business Admin -> can modify users inside same business, but cannot modify System Admin
    //     if ($actor->hasRole('Business Admin')) {
    //         if ($target->hasRole('System Admin')) {
    //             return false;
    //         }
    //         return $actor->business_id === $target->business_id;
    //     }

    //     // Other roles (Staff, User etc.) -> no modify permission by default
    //     return false;
    // }

    // /**
    //  * Filter users list based on actor role
    //  */
    // public function filterUsersBasedOnAccess(User $actor)
    // {
    //     if ($actor->hasRole('System Admin')) {
    //         return User::with('roles')->get();
    //     }

    //     return User::with('roles')
    //         ->where('business_id', $actor->business_id)
    //         ->get();
    // }
}
