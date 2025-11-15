<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserHasRoleController extends Controller
{
    use UserActivityTrait;
    public function store(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Current authenticated user (the one trying to assign)
            $actor = Auth::user();
            if (! $actor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Fetch target user and the role object
            $user = User::find($request->user_id);
            $role = Role::find($request->role_id);

            if (! $user || ! $role) {
                return response()->json([
                    'success' => false,
                    'message' => 'User or Role not found.'
                ], 404);
            }

            $targetRoleName = $role->name;

            // Authorization rules:
            // - System Admin can assign any role
            // - Business Admin can assign only a limited set
            if ($actor->hasRole('System Admin')) {
                // allowed: anything
            } elseif ($actor->hasRole('Business Admin')) {
                // Business Admin allowed roles:
                $allowed = ['User', 'Staff', 'Business Admin'];

                if (! in_array($targetRoleName, $allowed, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not allowed to assign this role.'
                    ], 403);
                }
            } else {
                // Neither System Admin nor Business Admin
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign roles.'
                ], 403);
            }

            // Prevent re-assigning same role
            if ($user->hasRole($targetRoleName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has this role.',
                ], 409);
            }

            // Assign role
            $user->assignRole($targetRoleName);

            // Log activity
            $this->logActivity("assign_role_to_user");

            return response()->json([
                'success' => true,
                'message' => "Role '{$targetRoleName}' assigned to user '{$user->name}' successfully.",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while assigning role.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
