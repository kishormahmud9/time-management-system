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
            // ✅ Validation
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
            $businesses = Business::get();
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
            $business = Business::findOrFail($id);

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
                'email' => 'required|string|email|max:100|unique:users',
                'phone' => 'nullable|string|max:20',
                'name' => 'required|string|max:100',
                'address' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

            $this->logActivity('update_business_owner');

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully',
                'data' => $business
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

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,pending',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $business->update($request->only(['status']));
            $owner = User::findOrFail($business->owner_id);

            // Update owner status based on business status
            if ($request->status === 'active') {
                $owner->update(['status' => 'approved']);
            } elseif ($request->status === 'inactive') {
                $owner->update(['status' => 'rejected']);
            }

            $this->logActivity("{$request->status}_business_owner");
            return response()->json([
                'success' => true,
                'message' => 'status updated successfully',
                'data' => $business
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
