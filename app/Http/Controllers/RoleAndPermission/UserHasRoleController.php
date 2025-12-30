<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RoleService;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserHasRoleController extends Controller
{
    use UserActivityTrait;
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        // optionally apply middleware here
        // $this->middleware('auth:api');
    }

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

            // Fetch target user
            $user = User::find($request->user_id);
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }


            // Use RoleService to handle authorization & assignment
            $assignedRoleName = $this->roleService->assignRole($actor, $user, (int)$request->role_id);


            // Log activity
            $this->logActivity("assign_role_to_user");

            return response()->json([
                'success' => true,
                'message' => "Role '{$assignedRoleName}' assigned to user '{$user->name}' successfully.",
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
