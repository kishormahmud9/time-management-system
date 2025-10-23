<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserManageController extends Controller
{
    use UserActivityTrait;
    // Create new User
    public function store(Request $request)
    {
        try {
            // ✅ Validation
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

            // ✅ Image Upload (with random prefix + real upload)
            $imagePath = null;
            $signaturePath = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = rand(100000, 999999) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('users/images', $imageName, 'public');
                $imagePath = 'storage/users/images/' . $imageName;
            }

            if ($request->hasFile('signature')) {
                $signature = $request->file('signature');
                $signatureName = rand(100000, 999999) . '_' . time() . '.' . $signature->getClientOriginalExtension();
                $signature->storeAs('users/signatures', $signatureName, 'public');
                $signaturePath = 'storage/users/signatures/' . $signatureName;
            }

            // ✅ Create User
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
                'status' => 'approved',
            ]);

            // ✅ Assign Role
            $role = Role::find($request->role_id);
            $user->assignRole($role->name);

            // ✅ Log Activity
            $this->logActivity('create_user');

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all Users
    public function view()
    {
        try {
            $users = User::all();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch User',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single users
    public function viewDetails($id)
    {
        try {
            $user = User::findOrFail($id);

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
            $user = User::findOrFail($id);

            // ✅ Validation
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

            $imagePath = $user->image;
            $signaturePath = $user->signature;

            if ($request->hasFile('image')) {

                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                $image = $request->file('image');
                $imageName = rand(100000, 999999) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('users/images', $imageName, 'public');
                $imagePath = 'storage/users/images/' . $imageName;
            }

            if ($request->hasFile('signature')) {
                if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                    Storage::disk('public')->delete($user->signature);
                }

                $signature = $request->file('signature');
                $signatureName = rand(100000, 999999) . '_' . time() . '.' . $signature->getClientOriginalExtension();
                $signature->storeAs('users/signatures', $signatureName, 'public');
                $signaturePath = 'storage/users/signatures/' . $signatureName;
            }


            // ✅ User update
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

            // ✅ Role update (remove old & assign new)
            if ($request->role_id) {
                $role = Role::find($request->role_id);
                $user->syncRoles([$role->name]);
            }

            // ✅ Activity Log
            $this->logActivity('update_user');

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (Exception $e) {
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
            $user = User::findOrFail($id);
            // ✅ Delete image file if exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // ✅ Delete signature file if exists
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }

            // ✅ Delete user from database
            $user->delete();

            // ✅ Log activity
            $this->logActivity('delete_user');

            return response()->json([
                'success' => true,
                'message' => 'User Deleted Successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete User',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Update User Status
    public function statusUpdate(Request $request, $id)
    {
        try {
            // ✅ Validation (status required & must be active/inactive)
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,rejected,pending', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // ✅ Find user
            $user = User::findOrFail($id);

            // ✅ Update status
            $user->status = $request->status;
            $user->save();

            // ✅ Log activity
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
