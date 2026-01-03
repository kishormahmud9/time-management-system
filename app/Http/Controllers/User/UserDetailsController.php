<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Services\RoleService;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserDetailsController extends Controller
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

            // ========================
            // Core Relations
            // ========================
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('user_details')
                    ->where(
                        fn($q) =>
                        $q->where('business_id', $request->business_id)
                            ->where('active', true)
                    ),
            ],

            'party_id'    => 'nullable|exists:parties,id',

            // ========================
            // Rates (Business Critical)
            // ========================
            'client_rate'     => 'required|numeric|min:0',
            'consultant_rate' => 'nullable|numeric|min:0',

            // ========================
            // Contract Dates
            // ========================
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',

            // ========================
            // Account Manager
            // ========================
            'account_manager_id' => 'nullable|exists:internal_users,id',
            'account_manager_commission' => 'nullable|numeric|min:0',
            'account_manager_commission_rate_type' => 'nullable|in:1,2',
            'account_manager_commission_rate_count_on' => 'nullable|string',
            'account_manager_recurssive' => 'nullable|boolean',
            'account_manager_recurssive_month' =>
            'nullable|required_if:account_manager_recurssive,true|integer|min:1',

            // ========================
            // Business Development Manager
            // ========================
            'business_development_manager_id' => 'nullable|exists:internal_users,id',
            'business_development_manager_commission' => 'nullable|numeric|min:0',
            'business_development_manager_commission_rate_type' => 'nullable|in:1,2',
            'business_development_manager_commission_rate_count_on' => 'nullable|string',
            'business_development_manager_recurssive' => 'nullable|boolean',
            'business_development_manager_recurssive_month' =>
            'nullable|required_if:business_development_manager_recurssive,true|integer|min:1',

            // ========================
            // Recruiter
            // ========================
            'recruiter_id' => 'nullable|exists:internal_users,id',
            'recruiter_commission' => 'nullable|numeric|min:0',
            'recruiter_rate_type' => 'nullable|in:1,2',
            'recruiter_rate_count_on' => 'nullable|string',
            'recruiter_recurssive' => 'nullable|boolean',
            'recruiter_recurssive_month' =>
            'nullable|required_if:recruiter_recurssive,true|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $actor = Auth::user();
        if (! $actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        DB::beginTransaction();
        try {
            $userDetail = UserDetail::create([
                'user_id' => $request->user_id,
                'business_id' => $actor->business_id,
                'party_id' => $request->party_id,

                // Rates
                'client_rate' => $request->client_rate,
                'consultant_rate' => $request->consultant_rate,

                // Account Manager
                'account_manager_commission' => $request->account_manager_commission ?? 0,
                'account_manager_commission_rate_count_on' => $request->account_manager_commission_rate_count_on,
                'account_manager_commission_rate_type' => $request->account_manager_commission_rate_type ?? 1,
                'account_manager_recurssive' => $request->account_manager_recurssive ?? false,
                'account_manager_recurssive_month' => $request->account_manager_recurssive_month,
                'account_manager_id' => $request->account_manager_id,

                // Business Development Manager
                'business_development_manager_commission' => $request->business_development_manager_commission ?? 0,
                'business_development_manager_commission_rate_count_on' => $request->business_development_manager_commission_rate_count_on,
                'business_development_manager_commission_rate_type' => $request->business_development_manager_commission_rate_type ?? 1,
                'business_development_manager_recurssive' => $request->business_development_manager_recurssive ?? false,
                'business_development_manager_recurssive_month' => $request->business_development_manager_recurssive_month,
                'business_development_manager_id' => $request->business_development_manager_id,

                // Recruiter
                'recruiter_commission' => $request->recruiter_commission ?? 0,
                'recruiter_rate_count_on' => $request->recruiter_rate_count_on,
                'recruiter_rate_type' => $request->recruiter_rate_type ?? 1,
                'recruiter_recurssive' => $request->recruiter_recurssive ?? false,
                'recruiter_recurssive_month' => $request->recruiter_recurssive_month,
                'recruiter_id' => $request->recruiter_id,

                // Contract
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,

                // Misc
                'active' => true,
                'address' => $request->address,
                'invoice_to' => $request->invoice_to,
                'file_folder' => $request->file_folder,
            ]);


            DB::commit();

            // Activity Log
            $this->logActivity('assign_client_to_user');

            return response()->json([
                'success' => true,
                'message' => 'User details stored successfully',
                'data' => $userDetail,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to store user details',
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

            $userDetail = UserDetail::where('business_id', $actor->business_id)->get();

            return response()->json([
                'success' => true,
                'data' => $userDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details',
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

            $userDetail = UserDetail::findOrFail($id);

            // Authorization check using service
            if (! $this->access->canViewResource($actor, $userDetail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this user.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $userDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User Details not found',
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
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userDetail = UserDetail::findOrFail($id);

        // Authorization
        if (! $this->access->canModifyResource($actor, $userDetail)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to modify this user detail.',
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'party_id' => 'nullable|exists:parties,id',

            // Rates
            'client_rate' => 'required|numeric|min:0',
            'consultant_rate' => 'nullable|numeric|min:0',
            'w2' => 'nullable|numeric|min:0',
            'c2c_or_other' => 'nullable|numeric|min:0',
            'w2_or_c2c_type' => 'nullable|integer',

            // Account Manager
            'account_manager_id' => 'nullable|exists:internal_users,id',
            'account_manager_commission' => 'nullable|numeric|min:0',
            'account_manager_commission_rate_type' => 'nullable|integer',
            'account_manager_commission_rate_count_on' => 'nullable|string',
            'account_manager_recurssive' => 'nullable|boolean',
            'account_manager_recurssive_month' => 'nullable|integer',

            // Business Development Manager
            'business_development_manager_id' => 'nullable|exists:internal_users,id',
            'business_development_manager_commission' => 'nullable|numeric|min:0',
            'business_development_manager_commission_rate_type' => 'nullable|integer',
            'business_development_manager_commission_rate_count_on' => 'nullable|string',
            'business_development_manager_recurssive' => 'nullable|boolean',
            'business_development_manager_recurssive_month' => 'nullable|integer',

            // Recruiter
            'recruiter_id' => 'nullable|exists:internal_users,id',
            'recruiter_commission' => 'nullable|numeric|min:0',
            'recruiter_rate_type' => 'nullable|integer',
            'recruiter_rate_count_on' => 'nullable|string',
            'recruiter_recurssive' => 'nullable|boolean',
            'recruiter_recurssive_month' => 'nullable|integer',

            // Contract
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            // Misc
            'active' => 'nullable|boolean',
            'address' => 'nullable|string|max:255',
            'invoice_to' => 'nullable|string|max:255',
            'file_folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userDetail->update($request->only([
                'party_id',

                'client_rate',
                'consultant_rate',
                'w2',
                'c2c_or_other',
                'w2_or_c2c_type',

                'account_manager_id',
                'account_manager_commission',
                'account_manager_commission_rate_type',
                'account_manager_commission_rate_count_on',
                'account_manager_recurssive',
                'account_manager_recurssive_month',

                'business_development_manager_id',
                'business_development_manager_commission',
                'business_development_manager_commission_rate_type',
                'business_development_manager_commission_rate_count_on',
                'business_development_manager_recurssive',
                'business_development_manager_recurssive_month',

                'recruiter_id',
                'recruiter_commission',
                'recruiter_rate_type',
                'recruiter_rate_count_on',
                'recruiter_recurssive',
                'recruiter_recurssive_month',

                'start_date',
                'end_date',

                'active',
                'address',
                'invoice_to',
                'file_folder',
            ]));

            DB::commit();

            $this->logActivity('update_user_detail', [
                'user_detail_id' => $userDetail->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User detail updated successfully',
                'data' => $userDetail->fresh(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user detail',
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

        try {
            $userDetail = UserDetail::findOrFail($id);

            // Authorization check
            if (! $this->access->canModifyResource($actor, $userDetail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this internal user.'
                ], 403);
            }

            // Extra safety: business isolation
            if ($userDetail->business_id !== $actor->business_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized business access.'
                ], 403);
            }

            DB::beginTransaction();


            // Delete user detail
            $userDetail->delete();

            DB::commit();

            // Log activity
            $this->logActivity('delete_user_detail');

            return response()->json([
                'success' => true,
                'message' => 'User detail deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Internal user details not found'
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
}
