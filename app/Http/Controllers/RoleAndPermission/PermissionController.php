<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;

class PermissionController extends Controller
{
    use UserActivityTrait;
    // Create new permission
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:permissions,name',
                'guard_name' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name ?? 'api',
            ]);

            $this->logActivity('create_permission');
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all permissions
    public function view()
    {
        try {
            $permissions = Permission::all();

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get staff available permissions
    public function staffAvailablePermission()
    {
        try {
            $user = auth()->user();
            $staffRole = Role::where('name', 'Staff')->first();
            
            if (!$staffRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff role not found'
                ], 404);
            }

            $rolePermissions = $staffRole->permissions;
            
            // Filter permissions that the logged-in user actually has
            $permissions = $rolePermissions->filter(function ($permission) use ($user) {
                return $user->hasPermissionTo($permission->name);
            })->values();

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get user available permissions
    public function userAvailablePermission()
    {
        try {
            $user = auth()->user();
            $userRole = Role::where('name', 'User')->first();

            if (!$userRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User role not found'
                ], 404);
            }

            $rolePermissions = $userRole->permissions;

            // Filter permissions that the logged-in user actually has
            $permissions = $rolePermissions->filter(function ($permission) use ($user) {
                return $user->hasPermissionTo($permission->name);
            })->values();

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get Staff permissions
   public function staffPermission()  
    {
        try {
           $permissions = Permission::whereIn('name', [
              'create_user',
            'view_user',
            'manage_roles',
            'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'submit_timesheet',
            'view_party',
            'view_project',
            'view_reports',
            'create_user_details',
            'view_user_details',
            'update_user_details',
            'delete_user_details',
            'create_internal_user',
            'view_internal_user',
            'update_internal_user',
            'delete_internal_user',
            'status_update_internal_user',
            ])->get();


            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get User permissions
   public function userPermission()  
    {
        try {
            $permissions = Permission::whereIn('name', [
               'create_timesheet',
            'view_timesheet',
            'update_timesheet',
            'submit_timesheet',
            'view_party',
            'view_project',
            ])->get();


            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Get single permission
    public function viewDetails($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $permission
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update permission
    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:permissions,name,' . $permission->id,
                'guard_name' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $permission->update([
                'name' => $request->name,
                'guard_name' => $request->guard_name ?? $permission->guard_name,
            ]);
            $this->logActivity('update_permission');
            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete permission
    public function delete($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            $this->logActivity('delete_permission');
            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
