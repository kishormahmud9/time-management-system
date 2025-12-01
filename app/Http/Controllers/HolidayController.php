<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Create new holiday
     */
    public function store(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'date' => 'required|date',
                'type' => 'nullable|string|in:public,company,other',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $holiday = Holiday::create([
                'business_id' => $actor->business_id,
                'name' => $request->name,
                'date' => $request->date,
                'type' => $request->type ?? 'public',
                'description' => $request->description,
            ]);

            DB::commit();

            $this->logActivity('create_holiday');

            return response()->json([
                'success' => true,
                'message' => 'Holiday created successfully',
                'data' => $holiday
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create holiday',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all holidays
     */
    public function view(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Filter by business
            $query = $this->access->filterByBusiness($actor, Holiday::class);

            // Optional filters
            if ($request->has('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->has('month')) {
                $query->whereMonth('date', $request->month);
            }

            $holidays = $query->orderBy('date')->get();

            return response()->json([
                'success' => true,
                'data' => $holidays
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch holidays',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single holiday details
     */
    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $holiday = Holiday::findOrFail($id);

            // Check access permission
            if (!$this->access->canViewResource($actor, $holiday)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this holiday.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $holiday
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Holiday not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update holiday
     */
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $holiday = Holiday::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $holiday)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this holiday.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'date' => 'nullable|date',
                'type' => 'nullable|string|in:public,company,other',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $holiday->update($request->all());

            DB::commit();

            $this->logActivity('update_holiday');

            return response()->json([
                'success' => true,
                'message' => 'Holiday updated successfully',
                'data' => $holiday
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update holiday',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete holiday
     */
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $holiday = Holiday::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $holiday)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this holiday.'
                ], 403);
            }

            DB::beginTransaction();

            $holiday->delete();

            DB::commit();

            $this->logActivity('delete_holiday');

            return response()->json([
                'success' => true,
                'message' => 'Holiday deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete holiday',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
