<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Create new project
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
                'code' => 'nullable|string|max:50',
                'client_id' => 'required|integer|exists:parties,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'nullable|in:active,completed,on_hold,cancelled',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $project = Project::create([
                'business_id' => $actor->business_id,
                'client_id' => $request->client_id,
                'name' => $request->name,
                'code' => $request->code,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status ?? 'active',
                'description' => $request->description,
            ]);

            DB::commit();

            $this->logActivity('create_project');

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all projects
     */
    public function view(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Filter by business
            $query = $this->access->filterByBusiness($actor, Project::class)
                ->with(['client']);

            // Optional filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            $projects = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $projects
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single project details
     */
    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $project = Project::with(['client', 'timesheets'])->findOrFail($id);

            // Check access permission
            if (!$this->access->canViewResource($actor, $project)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this project.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $project
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update project
     */
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $project = Project::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $project)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this project.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'code' => 'nullable|string|max:50',
                'client_id' => 'nullable|integer|exists:parties,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'nullable|in:active,completed,on_hold,cancelled',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $project->update($request->all());

            DB::commit();

            $this->logActivity('update_project');

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete project
     */
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $project = Project::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $project)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this project.'
                ], 403);
            }

            DB::beginTransaction();

            $project->delete();

            DB::commit();

            $this->logActivity('delete_project');

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
