<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InternalUser;
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

class InternalUserController extends Controller
{
    use UserActivityTrait;
    protected RoleService $roleService;
    protected UserAccessService $access;


    public function __construct(RoleService $roleService, UserAccessService $access)
    {
        $this->roleService = $roleService;
        $this->access = $access;
    }

    // Create new Internal User
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:internal_users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'rate' => 'nullable|numeric|min:0',
            'role' => 'required|in:bd_manager,ac_manager,recruiter',
            'commission_on' => 'nullable|in:gross-margin,net-margin',
            'rate_type' => 'nullable|in:percentage,fixed',
            'recuesive' => 'nullable|boolean',
            'month' => 'nullable|in:all_months,january,february,march,april,may,june,july,august,september,october,november,december',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Auth check
        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check Permission
        if (! $actor->hasPermissionTo('create_internal_user')) {
             return response()->json([
                 'success' => false,
                 'message' => 'You do not have permission to create internal users.'
             ], 403);
        }

        DB::beginTransaction();
        try {

            // ✅ Image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('internal_users', $imageName, 'public');
                $imagePath = 'internal_users/' . $imageName;
            }

            // ✅ Create Internal User
            $internalUser = InternalUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'image' => $imagePath,
                'rate' => $request->rate,
                'role' => $request->role, // ✅ Direct assign
                'commission_on' => $request->commission_on ?? 'gross-margin',
                'rate_type' => $request->rate_type ?? 'percentage',
                'recuesive' => $request->recuesive ?? 0,
                'month' => $request->month ?? 'all_months',
                'business_id' => $actor->business_id, // ✅ secure
            ]);

            DB::commit();

            // Log activity
            $this->logActivity('create_internal_user');

            return response()->json([
                'success' => true,
                'message' => 'Internal user created successfully',
                'data' => $internalUser,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create internal user',
                'error' => $e->getMessage(),
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

            if (! $actor->hasPermissionTo('view_internal_user')) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to view internal users.'], 403);
            }

            $internalUsers = InternalUser::where('business_id', $actor->business_id)->get();

            return response()->json([
                'success' => true,
                'data' => $internalUsers
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch internal Users',
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

            if (! $actor->hasPermissionTo('view_internal_user')) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to view internal users.'], 403);
            }

            $internalUser = InternalUser::with(['accountManagerDetails', 'bdManagerDetails', 'recruiterDetails'])
                ->findOrFail($id);

            // Authorization check using service
            if (! $this->access->canViewResource($actor, $internalUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this user.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $internalUser
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
        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (! $actor->hasPermissionTo('update_internal_user')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update internal users.'], 403);
        }

        $internalUser = InternalUser::findOrFail($id);

        // Authorization check
        if (! $this->access->canModifyResource($actor, $internalUser)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to modify this user.'
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:internal_users,email,' . $internalUser->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'rate' => 'nullable|numeric|min:0',
            'role' => 'required|in:bd_manager,ac_manager,recruiter',
            'commission_on' => 'nullable|in:gross-margin,net-margin',
            'rate_type' => 'nullable|in:percentage,fixed',
            'recuesive' => 'nullable|boolean',
            'month' => 'nullable|in:all_months,january,february,march,april,may,june,july,august,september,october,november,december',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            if ($request->hasFile('image')) {
                // delete old image
                if ($internalUser->image && Storage::disk('public')->exists($internalUser->image)) {
                    Storage::disk('public')->delete($internalUser->image);
                }

                $image = $request->file('image');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('internal_users', $imageName, 'public');
                $internalUser->image = 'internal_users/' . $imageName;
            }

            $internalUser->name = $request->name;
            $internalUser->email = $request->email;
            $internalUser->phone = $request->phone;
            $internalUser->gender = $request->gender;
            $internalUser->rate = $request->rate;
            $internalUser->role = $request->role;
            $internalUser->commission_on = $request->commission_on ?? $internalUser->commission_on;
            $internalUser->rate_type = $request->rate_type ?? $internalUser->rate_type;
            $internalUser->recuesive = $request->recuesive ?? $internalUser->recuesive;
            $internalUser->month = $request->month ?? $internalUser->month;

            $internalUser->save();

            DB::commit();

            // Activity log
            $this->logActivity('update_internal_user');

            return response()->json([
                'success' => true,
                'message' => 'Internal user updated successfully',
                'data' => $internalUser,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update internal user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // Delete User
    public function delete($id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (! $actor->hasPermissionTo('delete_internal_user')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete internal users.'], 403);
        }

        try {
            $internalUser = InternalUser::findOrFail($id);

            // Authorization check
            if (! $this->access->canModifyResource($actor, $internalUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this internal user.'
                ], 403);
            }

            // Extra safety: business isolation
            if ($internalUser->business_id !== $actor->business_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized business access.'
                ], 403);
            }

            DB::beginTransaction();

            // Delete profile image if exists
            if ($internalUser->image && Storage::disk('public')->exists($internalUser->image)) {
                Storage::disk('public')->delete($internalUser->image);
            }


            // Delete internal user
            $internalUser->delete();

            DB::commit();

            // Log activity
            $this->logActivity('delete_internal_user');

            return response()->json([
                'success' => true,
                'message' => 'Internal user deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Internal user not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete internal user',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Update Internal User Role
    public function roleUpdate(Request $request, $id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (! $actor->hasPermissionTo('role_update_internal_user')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update internal user roles.'], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:bd_manager,ac_manager,recruiter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $internalUser = InternalUser::findOrFail($id);

            // Authorization check
            if (! $this->access->canModifyResource($actor, $internalUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to change this internal user role.'
                ], 403);
            }

            // Extra safety: business isolation
            if ($internalUser->business_id !== $actor->business_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized business access.'
                ], 403);
            }

            DB::beginTransaction();

            // Update role
            $internalUser->role = $request->role;
            $internalUser->save();

            DB::commit();

            // Activity log (explicit & searchable)
            $this->logActivity("{$request->role}_internal_user");
            return response()->json([
                'success' => true,
                'message' => 'Internal user role updated successfully',
                'data' => [
                    'id' => $internalUser->id,
                    'name' => $internalUser->name,
                    'role' => $internalUser->role,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Internal User Not Found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update internal user role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
