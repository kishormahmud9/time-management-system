<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateUsedBy;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    use UserActivityTrait;
    
    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Create new template
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_name' => 'required|string|unique:email_templates,template_name',
                'template_type' => 'nullable|string|max:100',
                'subject'       => 'required|string',
                'body'          => 'required|string',
                'used_by'       => 'required|array',
                'used_by.*'     => 'exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $actor = Auth::user();
            if (! $actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $template = EmailTemplate::create([
                'template_name' => $request->template_name,
                'template_type' => $request->template_type,
                'subject' => $request->subject,
                'body' => $request->body,
                'business_id' => $actor->business_id
            ]);


            foreach ($request->used_by as $roleId) {
                EmailTemplateUsedBy::create([
                    'role_id'          => $roleId,
                    'mail_template_id' => $template->id,
                ]);
            }


            $this->logActivity('create_email_template');

            return response()->json([
                'success' => true,
                'message' => 'Email Template created successfully.',
                'data'    => $template
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Email Template',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Get Active Email Template
    public function view()
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Get user's role IDs
            $userRoleIds = $actor->roles->pluck('id')->toArray();

            // Get templates that are either:
            // 1. Default templates (business_id = null)
            // 2. User's own business templates
            // AND assigned to user's role
            $templates = EmailTemplate::with('usedBy')
                ->where('status', 'active')
                ->where(function ($query) use ($actor) {
                    $query->whereNull('business_id') // Default templates
                          ->orWhere('business_id', $actor->business_id); // User's business templates
                })
                ->whereHas('usedBy', function ($query) use ($userRoleIds) {
                    $query->whereIn('role_id', $userRoleIds);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Email Template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single Email Template
    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $template = EmailTemplate::with(['usedBy'])->findOrFail($id);

            // ✅ Check access permission
            // Allow access if template is default (business_id = null) OR belongs to user's business
            if ($template->business_id !== null && !$this->access->canViewResource($actor, $template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this template.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $template
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email Template not found',
                'error' => $e->getMessage()
            ], 404);
        }
        
    }


    // Update Email Template
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $template = EmailTemplate::findOrFail($id);

            // ✅ Prevent modifying default templates
            if ($template->business_id === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify default system templates.'
                ], 403);
            }

            // ✅ Check modify permission
            if (!$this->access->canModifyResource($actor, $template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this template.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'template_name' => 'required|string|unique:email_templates,template_name',
                'template_type' => 'nullable|string|max:100',
                'subject'       => 'required|string',
                'body'          => 'required|string',
                'used_by'       => 'required|array',
                'used_by.*'     => 'exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $template->update($request->only(['template_name', 'template_type', 'subject', 'body', 'status']));

            EmailTemplateUsedBy::where('mail_template_id', $template->id)->delete();

            foreach ($request->used_by as $roleId) {
                EmailTemplateUsedBy::create([
                    'role_id' => $roleId,
                    'mail_template_id' => $template->id,
                ]);
            }
            $this->logActivity('update_email_template');

            return response()->json([
                'success' => true,
                'message' => 'Email Template updated successfully',
                'data' => $template
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Email Template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete Email Template
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $template = EmailTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // ✅ Prevent deleting default templates
            if ($template->business_id === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete default system templates.'
                ], 403);
            }

            // ✅ Check modify permission
            if (!$this->access->canModifyResource($actor, $template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this template.'
                ], 403);
            }

            $template->delete();
            $this->logActivity('delete_email_template');
            return response()->json([
                'success' => true,
                'message' => 'Email Template deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Email Template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
