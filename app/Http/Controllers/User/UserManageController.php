<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RoleService;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserManageController extends Controller
{
    use UserActivityTrait;
    protected RoleService $roleService;
    protected UserAccessService $access;


    public function __construct(RoleService $roleService, UserAccessService $access)
    {
        $this->roleService = $roleService;
        $this->access = $access;
    }

    // Create new User
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
            'role_id' => 'required|integer|exists:roles,id',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Ensure actor (the one creating/assigning) exists
        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        DB::beginTransaction();
        try {
            // Image upload (same as your code)
            $imagePath = null;
            $signaturePath = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = rand(100000, 999999) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('users/images', $imageName, 'public');
                $imagePath = 'users/images/' . $imageName;  // ✅ Fixed: Removed 'storage/' prefix
            }

            if ($request->hasFile('signature')) {
                $signature = $request->file('signature');
                $signatureName = rand(100000, 999999) . '_' . time() . '.' . $signature->getClientOriginalExtension();
                $signature->storeAs('users/signatures', $signatureName, 'public');
                $signaturePath = 'users/signatures/' . $signatureName;  // ✅ Fixed: Removed 'storage/' prefix
            }

            // Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => generateUniqueUsername($request->name),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'image' => $imagePath,
                'signature' => $signaturePath,
                'business_id' => $actor->business_id,
                'status' => 'approved',
            ]);

            // Use RoleService to assign the role (this may throw exceptions on auth/validation)
            $assignedRoleName = $this->roleService->assignRole($actor, $user, (int)$request->role_id);

            // All good -> commit
            DB::commit();

            // Log activity
            $this->logActivity('create_user');

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
                'assigned_role' => $assignedRoleName,
            ], 201);
        } catch (Exception $e) {
            // rollback DB if anything went wrong
            DB::rollBack();

            // Map common messages to proper status codes if you like
            $msg = $e->getMessage();

            if ($msg === 'You are not allowed to assign this role.' || $msg === 'You do not have permission to assign roles.') {
                // Role assignment unauthorized by actor
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 403);
            }

            if ($msg === 'User already has this role.') {
                // unlikely at creation, but handle anyway
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 409);
            }

            // Generic failure
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function view()
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $users = $this->access->filterByBusiness($actor, \App\Models\User::class)->with('roles')->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $user = User::findOrFail($id);

            // Authorization check using service
            if (! $this->access->canViewResource($actor, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this user.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update User
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $user = User::findOrFail($id);

            // Authorization: must be allowed to modify
            if (! $this->access->canModifyResource($actor, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this user.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'phone' => 'required|string|max:20',
                'role_id' => 'required|integer|exists:roles,id',
                'address' => 'nullable|string|max:255',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // handle image / signature
            // NOTE: store relative paths (recommended) so Storage::disk('public')->exists() works.
            $imagePath = $user->image;
            $signaturePath = $user->signature;

            if ($request->hasFile('image')) {
                // delete old if exists (ensure stored path is relative to disk root)
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                $image = $request->file('image');
                $imageName = rand(100000, 999999) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('users/images', $imageName, 'public');
                $imagePath = 'users/images/' . $imageName; // save relative path
            }

            if ($request->hasFile('signature')) {
                if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                    Storage::disk('public')->delete($user->signature);
                }

                $signature = $request->file('signature');
                $signatureName = rand(100000, 999999) . '_' . time() . '.' . $signature->getClientOriginalExtension();
                $signature->storeAs('users/signatures', $signatureName, 'public');
                $signaturePath = 'users/signatures/' . $signatureName; // save relative path
            }

            // Update user fields
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'image' => $imagePath,
                'signature' => $signaturePath,
            ]);

            // Role update via RoleService (ensures same auth rules for role assignment)
            $assignedRoleName = null;
            if ($request->filled('role_id')) {
                try {
                    $assignedRoleName = $this->roleService->syncUserRole($actor, $user, (int)$request->role_id);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
                } catch (Exception $e) {
                    DB::rollBack();
                    $msg = $e->getMessage();
                    if ($msg === 'You are not allowed to assign this role.' || $msg === 'You do not have permission to assign roles.') {
                        return response()->json(['success' => false, 'message' => $msg], 403);
                    }
                    return response()->json(['success' => false, 'message' => 'Failed to update role', 'error' => $msg], 500);
                }
            }

            DB::commit();

            // Activity log
            $this->logActivity('update_user');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
                'assigned_role' => $assignedRoleName
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update User',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Delete User
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $user = User::findOrFail($id);

            // Authorization: only allowed actors can delete
            if (! $this->access->canModifyResource($actor, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this user.'
                ], 403);
            }

            DB::beginTransaction();

            // Delete image file if exists (assumes stored relative path like 'users/images/..')
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Delete signature file if exists
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }

            // Optionally remove roles/relations if needed
            // $user->roles()->detach();

            // Delete user from database
            $user->delete();

            DB::commit();

            // Log activity
            $this->logActivity('delete_user');

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update User Status
    public function statusUpdate(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,rejected,pending',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::findOrFail($id);

            // Authorization: only allowed actors can change status
            if (! $this->access->canModifyResource($actor, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to change this user status.'
                ], 403);
            }

            DB::beginTransaction();

            // Update status
            $user->status = $request->status;
            $user->save();

            DB::commit();

            // Log activity (e.g., 'approved_user' or 'rejected_user')
            $this->logActivity("{$request->status}_user");

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->status,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
