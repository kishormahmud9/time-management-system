<?php

namespace App\Http\Controllers\Party;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PartyController extends Controller
{
    use UserActivityTrait;
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

            $party = Party::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'address' => $request->address,
                'remarks' => $request->remarks,
                'party_type' => $request->party_type,
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
            $parties = Party::all();

            return response()->json([
                'success' => true,
                'data' => $parties
            ]);
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
            $parties = Party::where('party_type', 'client')->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ]);
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
            $parties = Party::where('party_type', 'vendor')->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ]);
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
            $parties = Party::where('party_type', 'employee')->get();

            return response()->json([
                'success' => true,
                'data' => $parties
            ]);
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
            $party = Party::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $party
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Party not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }


    // Update role
    public function update(Request $request, $id)
    {
        try {
            $party = Party::findOrFail($id);

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


            $party->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'address' => $request->address,
                'remarks' => $request->remarks,
                'party_type' => $request->party_type,
            ]);

            $this->logActivity("update_{$request->party_type}");
            return response()->json([
                'success' => true,
                'message' => 'Party updated successfully',
                'data' => $party
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete role
    public function delete($id)
    {
        try {
            $party = Party::findOrFail($id);
            $party->delete();
            $this->logActivity('delete_party');
            return response()->json([
                'success' => true,
                'message' => 'Party deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Party',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
