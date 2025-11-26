<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Upload new attachment
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
                'file' => 'required|file|max:10240', // 10MB max
                'attachable_type' => 'required|string',
                'attachable_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify attachable exists and belongs to business
            // This is a simplified check. In production, you'd want to dynamically check the model.
            // For now, we assume the user has access if they can upload.

            DB::beginTransaction();

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attachments', $fileName, 'public');

            $attachment = Attachment::create([
                'business_id' => $actor->business_id,
                'uploaded_by' => $actor->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'attachable_type' => $request->attachable_type,
                'attachable_id' => $request->attachable_id,
            ]);

            DB::commit();

            $this->logActivity('upload_attachment');

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $attachment
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download attachment
     */
    public function download($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $attachment = Attachment::findOrFail($id);

            // Check access permission
            if (!$this->access->canViewResource($actor, $attachment)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to access this file.'
                ], 403);
            }

            if (!Storage::disk('public')->exists($attachment->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attachment
     */
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $attachment = Attachment::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $attachment)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this file.'
                ], 403);
            }

            DB::beginTransaction();

            // Delete from storage
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();

            DB::commit();

            $this->logActivity('delete_attachment');

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
