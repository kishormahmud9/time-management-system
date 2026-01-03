<?php

namespace App\Http\Controllers\Timesheet;

use App\Http\Controllers\Controller;
use App\Mail\TimesheetSubmittedEmail;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\TimesheetDefault;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Project;
use App\Models\Party;
use App\Models\Holiday;
use App\Models\User;
use App\Models\UserDetail;
use App\Notifications\TimesheetSubmitted;
use App\Notifications\TimesheetStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TimesheetManageController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Create new timesheet
     */
    // public function store(Request $request)
    // {
    //     try {
    //         $actor = Auth::user();
    //         if (!$actor) {
    //             return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    //         }

    //         // Validation
    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|integer|exists:users,id',
    //             'client_id' => 'nullable|integer|exists:parties,id',
    //             'project_id' => 'nullable|integer|exists:projects,id',
    //             'start_date' => 'required|date',
    //             'end_date' => 'required|date|after_or_equal:start_date',
    //             'status' => 'nullable|in:draft,submitted,approved,rejected',
    //             'remarks' => 'nullable|string|max:1000',
    //             'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls|max:5120',
    //             'entries' => 'nullable|array',
    //             'entries.*.entry_date' => 'required|date|between:' . $request->start_date . ',' . $request->end_date,
    //             'entries.*.daily_hours' => 'required|numeric|min:0|max:24',
    //             'entries.*.extra_hours' => 'nullable|numeric|min:0|max:24',
    //             'entries.*.vacation_hours' => 'nullable|numeric|min:0|max:24',
    //             'entries.*.note' => 'nullable|string|max:500',
    //             'email' => 'nullable|array',
    //             'email.to' => 'required_with:email|email',
    //             'email.subject' => 'nullable|string',
    //             'email.body' => 'nullable|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }


    //         if ($request->client_id) {
    //             $client = Party::find($request->client_id);
    //             if (!$client || $client->business_id !== $actor->business_id) {
    //                 return response()->json(['success' => false, 'message' => 'Invalid Client ID'], 422);
    //             }
    //         }

    //         // Validate Holidays
    //         if ($request->has('entries')) {
    //             $holidayDates = Holiday::where('business_id', $actor->business_id)
    //                 ->whereIn('date', array_column($request->entries, 'entry_date'))
    //                 ->pluck('date')
    //                 ->toArray();

    //             if (!empty($holidayDates)) {
    //                 return response()->json([
    //                     'success' => false, 
    //                     'message' => 'Cannot submit timesheet for holiday dates: ' . implode(', ', $holidayDates)
    //                 ], 422);
    //             }
    //         }

    //         DB::beginTransaction();

    //         // Handle file upload
    //         $attachmentPath = null;
    //         if ($request->hasFile('attachment')) {
    //             $file = $request->file('attachment');
    //             $fileName = time() . '_' . $file->getClientOriginalName();
    //             $file->storeAs('timesheets/attachments', $fileName, 'public');
    //             $attachmentPath = 'timesheets/attachments/' . $fileName;
    //         }

    //         // Create timesheet
    //         $timesheet = Timesheet::create([
    //             'business_id' => $actor->business_id,
    //             'user_id' => $request->user_id,
    //             'client_id' => $request->client_id,
    //             'project_id' => $request->project_id,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'status' => $request->status ?? 'draft',
    //             'remarks' => $request->remarks,
    //             'attachment_path' => $attachmentPath,
    //             'total_hours' => 0,
    //         ]);

    //         // Create entries if provided
    //         if ($request->has('entries') && is_array($request->entries)) {
    //             foreach ($request->entries as $entryData) {
    //                 $timesheet->entries()->create([
    //                     'business_id' => $actor->business_id,
    //                     'entry_date' => $entryData['entry_date'],
    //                     'daily_hours' => $entryData['daily_hours'] ?? 0,
    //                     'extra_hours' => $entryData['extra_hours'] ?? 0,
    //                     'vacation_hours' => $entryData['vacation_hours'] ?? 0,
    //                     'note' => $entryData['note'] ?? null,
    //                 ]);
    //             }

    //             // Update total hours
    //             $timesheet->updateTotalHours();
    //         }

    //         DB::commit();

    //         // Send email notification if provided
    //         if ($request->has('email') && $request->email['to']) {
    //             try {
    //                 Mail::to($request->email['to'])->send(
    //                     new TimesheetSubmittedEmail($timesheet, $request->email)
    //                 );
    //             } catch (Exception $mailException) {
    //                 \Log::error('Failed to send timesheet email: ' . $mailException->getMessage());
    //             }
    //         }

    //         // Notify Approver if submitted
    //         if ($timesheet->status === 'submitted') {
    //             // Find approvers (e.g., Business Admin)
    //             $approvers = User::role('Business Admin')->where('business_id', $actor->business_id)->get();
    //             Notification::send($approvers, new TimesheetSubmitted($timesheet));
    //         }

    //         $this->logActivity('create_timesheet');

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Timesheet created successfully',
    //             'data' => $timesheet->load('entries')
    //         ], 201);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create timesheet',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $actor = Auth::user();
        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Validation (role based)
    |--------------------------------------------------------------------------
    */
        $rules = [
            'client_id' => 'nullable|exists:parties,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
            'remarks' => 'nullable|string|max:1000',

            'entries' => 'required|array|min:1',
            'entries.*.entry_date' =>
            'required|date|between:' . $request->start_date . ',' . $request->end_date,
            'entries.*.daily_hours' => 'required|numeric|min:0|max:24',
            'entries.*.extra_hours' => 'nullable|numeric|min:0|max:24',
            'entries.*.vacation_hours' => 'nullable|numeric|min:0|max:24',
            'entries.*.note' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
        ];

        // 🔐 Role rule
        if ($actor->hasRole('User')) {
            // normal user → cannot pass user_id
            $rules['user_id'] = 'nullable|prohibited';
        } else {
            // admin / staff → must pass user_id
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        /*
    |--------------------------------------------------------------------------
    | Resolve target user
    |--------------------------------------------------------------------------
    */
        $targetUserId = $actor->hasRole('User')
            ? $actor->id
            : $validated['user_id'];

        /*
    |--------------------------------------------------------------------------
    | Resolve active UserDetail (MANDATORY)
    |--------------------------------------------------------------------------
    */
        $userDetail = UserDetail::where('user_id', $targetUserId)
            ->where('business_id', $actor->business_id)
            ->where('active', true)
            ->first();

        if (!$userDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Active user detail not found for this user'
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate client belongs to same business
    |--------------------------------------------------------------------------
    */
        if (!empty($validated['client_id'])) {
            $client = Party::where('id', $validated['client_id'])
                ->where('business_id', $actor->business_id)
                ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid client for this business'
                ], 422);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Holiday validation
    |--------------------------------------------------------------------------
    */
        $entryDates = collect($validated['entries'])->pluck('entry_date');

        $holidayDates = Holiday::where('business_id', $actor->business_id)
            ->whereIn('holiday_date', $entryDates)
            ->pluck('holiday_date')
            ->toArray();

        if (!empty($holidayDates)) {
            return response()->json([
                'success' => false,
                'message' => 'Timesheet contains holiday dates: ' . implode(', ', $holidayDates)
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Store Timesheet + Entries
    |--------------------------------------------------------------------------
    */
        DB::beginTransaction();

        try {
            // Create timesheet
            $timesheet = Timesheet::create([
                'business_id' => $actor->business_id,
                'user_id' => $targetUserId,
                'user_detail_id' => $userDetail->id,
                'client_id' => $validated['client_id'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'] ?? 'draft',
                'remarks' => $validated['remarks'] ?? null,
                'submitted_at' => ($validated['status'] ?? 'draft') === 'submitted' ? now() : null,
                'total_hours' => 0,
            ]);

            $totalHours = 0;

            foreach ($validated['entries'] as $entry) {
                $daily = $entry['daily_hours'];
                $extra = $entry['extra_hours'] ?? 0;
                $vacation = $entry['vacation_hours'] ?? 0;

                $totalHours += ($daily + $extra + $vacation);

                $timesheet->entries()->create([
                    'business_id' => $actor->business_id,
                    'entry_date' => $entry['entry_date'],
                    'daily_hours' => $daily,
                    'extra_hours' => $extra,
                    'vacation_hours' => $vacation,
                    'note' => $entry['note'] ?? null,


                    // rate snapshots (critical)
                    'client_rate_snapshot' => $userDetail->client_rate,
                    'consultant_rate_snapshot' => $userDetail->consultant_rate,
                ]);
            }

            $timesheet->update([
                'total_hours' => $totalHours
            ]);

            DB::commit();

            // activity log
            $this->logActivity('create_timesheet');

            return response()->json([
                'success' => true,
                'message' => 'Timesheet Created Successfully',
                'data' => $timesheet->load('entries')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Timesheet store failed', [
                'actor_id' => $actor->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create timesheet'
            ], 500);
        }
    }

    /**
     * Get all timesheets
     */
    public function view(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Filter by business
            $query = $this->access->filterByBusiness($actor, Timesheet::class)
                ->with(['user', 'client', 'project', 'approver', 'entries']);

            // Optional filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Date range filter
            if ($request->has('from_date')) {
                $query->where('start_date', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('end_date', '<=', $request->to_date);
            }

            $timesheets = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $timesheets
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timesheets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single timesheet details
     */
    public function viewDetails($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $timesheet = Timesheet::with(['user', 'client', 'project', 'approver', 'entries'])
                ->findOrFail($id);

            // Check access permission
            if (!$this->access->canViewResource($actor, $timesheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to view this timesheet.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $timesheet
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Timesheet not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update timesheet
     */
    public function update(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $timesheet = Timesheet::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $timesheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this timesheet.'
                ], 403);
            }

            // Only allow editing draft timesheets
            if (!$timesheet->isDraft()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft timesheets can be edited.'
                ], 400);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'client_id' => 'nullable|integer|exists:parties,id',
                'project_id' => 'nullable|integer|exists:projects,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'remarks' => 'nullable|string|max:1000',
                'entries' => 'nullable|array',
                'entries.*.entry_date' => 'required|date|between:' . $request->start_date . ',' . $request->end_date,
                'entries.*.daily_hours' => 'required|numeric|min:0|max:24',
                'entries.*.extra_hours' => 'nullable|numeric|min:0|max:24',
                'entries.*.vacation_hours' => 'nullable|numeric|min:0|max:24',
                'entries.*.note' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update timesheet
            $timesheet->update([
                'client_id' => $request->client_id,
                'project_id' => $request->project_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'remarks' => $request->remarks,
            ]);

            // Update entries if provided
            if ($request->has('entries')) {
                // Delete existing entries
                $timesheet->entries()->delete();

                // Create new entries
                foreach ($request->entries as $entryData) {
                    $timesheet->entries()->create([
                        'business_id' => $actor->business_id,
                        'entry_date' => $entryData['entry_date'],
                        'daily_hours' => $entryData['daily_hours'] ?? 0,
                        'extra_hours' => $entryData['extra_hours'] ?? 0,
                        'vacation_hours' => $entryData['vacation_hours'] ?? 0,
                        'note' => $entryData['note'] ?? null,
                    ]);
                }

                // Update total hours
                $timesheet->updateTotalHours();
            }

            DB::commit();

            $this->logActivity('update_timesheet');

            return response()->json([
                'success' => true,
                'message' => 'Timesheet updated successfully',
                'data' => $timesheet->load('entries')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timesheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete timesheet
     */
    public function delete($id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $timesheet = Timesheet::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $timesheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to delete this timesheet.'
                ], 403);
            }

            // Only allow deleting draft timesheets
            if (!$timesheet->isDraft()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft timesheets can be deleted.'
                ], 400);
            }

            DB::beginTransaction();

            $timesheet->delete(); // Entries will be cascade deleted

            DB::commit();

            $this->logActivity('delete_timesheet');

            return response()->json([
                'success' => true,
                'message' => 'Timesheet deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timesheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update timesheet status
     */
    public function statusUpdate(Request $request, $id)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $timesheet = Timesheet::findOrFail($id);

            // Check modify permission
            if (!$this->access->canModifyResource($actor, $timesheet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to modify this timesheet.'
                ], 403);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:draft,submitted,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = ['status' => $request->status];

            // Set timestamps based on status
            if ($request->status === 'submitted') {
                $updateData['submitted_at'] = now();
            } elseif ($request->status === 'approved') {
                $updateData['approved_by'] = $actor->id;
                $updateData['approved_at'] = now();
            }

            $timesheet->update($updateData);

            // Notify User on status change
            if (in_array($request->status, ['approved', 'rejected'])) {
                $timesheet->user->notify(new TimesheetStatusUpdated($timesheet, $request->status));
            }

            // Notify Approver if submitted
            if ($request->status === 'submitted') {
                $approvers = User::role('Business Admin')->where('business_id', $actor->business_id)->get();
                Notification::send($approvers, new TimesheetSubmitted($timesheet));
            }

            DB::commit();

            $this->logActivity("{$request->status}_timesheet");

            return response()->json([
                'success' => true,
                'message' => "Timesheet {$request->status} successfully",
                'data' => $timesheet
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timesheet status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timesheet defaults for a user
     */
    public function getDefaults(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $userId = $request->query('user_id');

            // Get defaults (user-specific or business-wide)
            $defaults = TimesheetDefault::getDefaults($actor->business_id, $userId);

            if (!$defaults) {
                // Return system defaults if no custom defaults found
                return response()->json([
                    'success' => true,
                    'data' => [
                        'default_daily_hours' => 8.00,
                        'default_extra_hours' => 0.00,
                        'default_vacation_hours' => 0.00,
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'default_daily_hours' => $defaults->default_daily_hours,
                    'default_extra_hours' => $defaults->default_extra_hours,
                    'default_vacation_hours' => $defaults->default_vacation_hours,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch defaults',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
