<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateUsedBy;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmailTemplateController extends Controller
{
    use UserActivityTrait;
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

            $template = EmailTemplate::create($request->only([
                'template_name',
                'template_type',
                'subject',
                'body',
            ]));


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
            $templates = EmailTemplate::where('status', 'active')->get();

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
            $template = EmailTemplate::findOrFail($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
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
            $template = EmailTemplate::findOrFail($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
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
            $template = EmailTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
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
