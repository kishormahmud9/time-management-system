<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserHasRoleController extends Controller
{
    public function store(Request $request)
    {
        try {
            // ✅ Step 1: Validate input
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

            // ✅ Step 2: Get user and role
            $user = User::find($request->user_id);
            $role = Role::find($request->role_id);

            // ✅ Step 3: Assign role to user
            if ($user->hasRole($role->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has this role.',
                ], 409);
            }

            $user->assignRole($role->name);

            // ✅ Step 4: Success response
            return response()->json([
                'success' => true,
                'message' => "Role '{$role->name}' assigned to user '{$user->name}' successfully.",
            ], 200);
        } catch (\Exception $e) {
            // ✅ Step 5: Catch unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while assigning role.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
