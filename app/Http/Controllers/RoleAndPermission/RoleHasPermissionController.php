<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;

class RoleHasPermissionController extends Controller
{
    use UserActivityTrait;
    public function store(Request $request)
    {

        try {
            // ✅ Validate input
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|integer|exists:roles,id',
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Find role
            $role = Role::findOrFail($request->role_id);

            // ✅ Fetch all valid permission names from IDs
            $permissionNames = Permission::whereIn('id', $request->permissions)
                ->pluck('name')
                ->toArray();

            // ✅ Sync permissions with role
            $role->syncPermissions($permissionNames);

            $this->logActivity('assign_permission_to_role');
            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned successfully to role',
                'data' => [
                    'role' => $role->name,
                    'permissions' => $permissionNames
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions to role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
