<?php

namespace App\Http\Controllers\Party;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartyController extends Controller
{
    use UserActivityTrait;
    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }
    // Create new Party
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'zip_code' => 'required|string|max:50',
                'address' => 'nullable|string|max:255',
                'remarks' => 'nullable|string|max:255',
                'party_type' => 'required|in:client,vendor,employee',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }


            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $party = Party::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'address' => $request->address,
                'remarks' => $request->remarks,
                'party_type' => $request->party_type,
                'business_id' => $actor->business_id
            ]);

            $this->logActivity("create_{$request->party_type}");
            return response()->json([
                'success' => true,
                'message' => 'Party created successfully',
                'data' => $party
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all Party
    public function view()
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // if your service returns Collection, remove ->get()
            $parties = $this->access->filterByBusiness($actor, Party::class)->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all Client
    public function getClient()
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $parties = $this->access
                ->filterByBusiness($actor, Party::class)
                ->where('party_type', 'client')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all Vendor
    public function getVendor()
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $parties = $this->access
                ->filterByBusiness($actor, Party::class)
                ->where('party_type', 'vendor')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Vendor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all Employee
    public function getEmployee()
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $parties = $this->access
                ->filterByBusiness($actor, Party::class)
                ->where('party_type', 'employee')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single Party
    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $party = Party::with(['projects', 'timesheets', 'userDetails'])->findOrFail($id);

            if (! $this->access->canViewResource($actor, $party)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this party.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $party
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update party
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $party = Party::findOrFail($id);

            // Authorization: pass actual Party model (NOT a stdClass)
            if (! $this->access->canModifyResource($actor, $party)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this party.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'zip_code' => 'required|string|max:50',
                'address' => 'nullable|string|max:255',
                'remarks' => 'nullable|string|max:255',
                'party_type' => 'required|in:client,vendor,employee',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $party->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'address' => $request->address,
                'remarks' => $request->remarks,
                'party_type' => $request->party_type,
            ]);

            DB::commit();

            $this->logActivity("update_{$request->party_type}");
            return response()->json([
                'success' => true,
                'message' => 'Party updated successfully',
                'data' => $party
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Party not found'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete party
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $party = Party::findOrFail($id);

            if (! $this->access->canModifyResource($actor, $party)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this party.'
                ], 403);
            }

            DB::beginTransaction();

            $party->delete();

            DB::commit();

            $this->logActivity('delete_party');
            return response()->json([
                'success' => true,
                'message' => 'Party deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Party not found'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
