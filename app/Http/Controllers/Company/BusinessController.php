<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use App\Services\BusinessRegistrationService;
use App\Services\SlugService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class BusinessController extends Controller
{
    use UserActivityTrait;

    protected $businessService;

    public function __construct(BusinessRegistrationService $businessService)
    {
        $this->businessService = $businessService;
    }
    /**
     * Create new Businees
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:20',
                'company_name' => 'required|string|max:100',
                'role_id' => 'nullable|integer|exists:roles,id',
                'address' => 'nullable|string|max:255',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'user_can_login' => 'nullable|in:0,1',
                'commission' => 'nullable|in:0,1',
                'template_can_add' => 'nullable|in:0,1',
                'qb_integration' => 'nullable|in:0,1',
                'user_limit' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $data = $this->businessService->createOwnerByAdmin($request->all());

            $this->logActivity('create_business_owner');

            return response()->json([
                'success' => true,
                'message' => 'Business Owner Created Successfully',
                'data' => $data
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Business',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Get Active Busineess
    public function view()
    {
        try {
            $businesses = Business::with(['owner', 'permission', 'users'])->get();
            if (!$businesses) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Business found'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $businesses
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single Business
    public function viewDetails($id)
    {
        try {
            $business = Business::with(['owner', 'permission', 'users'])->findOrFail($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $business
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    // Update Business
    public function update(Request $request, $id)
    {
        // dd($request->all());
        try {
            $business = Business::findOrFail($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:100|unique:businesses,email,' . $business->id,
                'phone' => 'nullable|string|max:20',
                'name' => 'required|string|max:100',
                'address' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'user_can_login' => 'nullable|in:0,1',
                'commission' => 'nullable|in:0,1',
                'template_can_add' => 'nullable|in:0,1',
                'qb_integration' => 'nullable|in:0,1',
                'user_limit' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->hasFile('logo')) {
                if ($business->logo && file_exists(public_path($business->logo))) {
                    unlink(public_path($business->logo)); // file remove
                }
                $logoPath = $this->businessService->uploadFile($request->file('logo'), 'businesses/logos');
                $business->logo = $logoPath;
            }

            $business->update($request->only(['name', 'phone', 'email', 'address']));

            // Update Business Permissions
            $this->businessService->updateBusinessPermissions($business, $request->all());

            $this->logActivity('update_business_owner');

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully',
                'data' => $business->load('permission')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Business',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete Business
    public function delete($id)
    {
        try {
            $business = Business::find($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }
            if ($business->logo && file_exists(public_path($business->logo))) {
                unlink(public_path($business->logo));
            }
            $business->delete();
            $this->logActivity('delete_business');
            return response()->json([
                'success' => true,
                'message' => 'Business deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Business',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Business Status Update Business
    public function statusUpdate(Request $request, $id)
    {
        try {
            $business = Business::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,pending',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Begin transaction for atomicity
            DB::beginTransaction();

            $oldStatus = $business->status;
            $newStatus = $request->status;

            // Update business status
            $business->update(['status' => $newStatus]);

            // Find owner
            $owner = User::findOrFail($business->owner_id);

            // Only act on role when status actually changes (optional but recommended)
            if ($oldStatus !== $newStatus) {

                if ($newStatus === 'active') {
                    // mark owner approved
                    $owner->update(['status' => 'approved']);

                    // assign Business Admin role if not already assigned
                    if (! $owner->hasRole('Business Admin')) {
                        $owner->assignRole('Business Admin');
                    }

                    // optionally remove any 'rejected' or 'pending' related roles:
                    // $owner->removeRole('SomeOtherRole');

                } elseif ($newStatus === 'inactive') {
                    // mark owner rejected
                    $owner->update(['status' => 'rejected']);

                    // remove Business Admin role if exists
                    if ($owner->hasRole('Business Admin')) {
                        $owner->removeRole('Business Admin');
                    }

                    // optionally assign a default 'User' role:
                    // if (! $owner->hasRole('User')) {
                    //     $owner->assignRole('User');
                    // }

                } elseif ($newStatus === 'pending') {
                    // don't change roles; maybe set owner status accordingly
                    $owner->update(['status' => 'pending']);
                }
            }

            // log activity (you already had this)
            $this->logActivity("{$newStatus}_business_owner");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status Updated successfully',
                'data' => $business
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // public function statusUpdate(Request $request, $id)
    // {
    //     // dd($id);
    //     try {
    //         $business = Business::findOrFail($id);

    //         if (!$business) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Business not found'
    //             ], 404);
    //         }
    //         $validator = Validator::make($request->all(), [
    //             'status' => 'required|in:active,inactive,pending',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $business->update($request->only(['status']));
    //         $owner = User::findOrFail($business->owner_id);

    //         // Update owner status based on business status
    //         if ($request->status === 'active') {
    //             $owner->update(['status' => 'approved']);
    //         } elseif ($request->status === 'inactive') {
    //             $owner->update(['status' => 'rejected']);
    //         }

    //         $this->logActivity("{$request->status}_business_owner");
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Status Updated successfully',
    //             'data' => $business
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update status',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
