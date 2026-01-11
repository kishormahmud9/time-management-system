<?php

namespace App\Http\Controllers\Timesheet;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\TimesheetDefault;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserDetail;
use App\Notifications\TimesheetSubmitted;
use App\Notifications\TimesheetStatusUpdated;
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
    public function store(Request $request)
    {
        $actor = Auth::user();

        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        if ($actor->hasRole('User')) {
            $rules['user_id'] = 'prohibited';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }


        $targetUserId = $actor->hasRole('User') ? $actor->id : $request->user_id;

        $userDetail = UserDetail::where([
            'user_id' => $targetUserId,
            'business_id' => $actor->business_id,
        ])->firstOrFail();

        return DB::transaction(function () use ($request, $actor, $targetUserId, $userDetail) {

            $timesheet = Timesheet::create([
                'business_id' => $actor->business_id,
                'user_id' => $targetUserId,
                'user_detail_id' => $userDetail->id,
                'client_id' => $request->client_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status ?? 'draft',
                'remarks' => $request->remarks ?? null,
                'mail_template_id' => $request->mail_template_id ?? null,
                'send_to' => $request->send_to ?? null,
                'submitted_at' => ($request->status ?? 'draft') === 'submitted' ? now() : null,
                'total_hours' => 0,
            ]);

            $totalHours = 0;

            foreach ($request->entries as $entry) {
                $hours = ($entry['daily_hours'] ?? 0)
                    + ($entry['extra_hours'] ?? 0)
                    + ($entry['vacation_hours'] ?? 0);

                $totalHours += $hours;

                $timesheet->entries()->create([
                    'business_id' => $actor->business_id,
                    'entry_date' => $entry['entry_date'],
                    'daily_hours' => $entry['daily_hours'],
                    'extra_hours' => $entry['extra_hours'] ?? 0,
                    'vacation_hours' => $entry['vacation_hours'] ?? 0,
                    'note' => $entry['note'] ?? null,
                    'client_rate_snapshot' => $userDetail->client_rate,
                ]);
            }


            $userDetail->update([
                'account_manager_commission_rate_count_on' => $totalHours * $userDetail->account_manager_commission,
                'business_development_manager_commission_rate_count_on' => $totalHours * $userDetail->business_development_manager_commission,
                'recruiter_rate_count_on' => $totalHours * $userDetail->recruiter_commission,
            ]);






            $this->logActivity('create_timesheet');

            return response()->json([
                'success' => true,
                'message' => 'Timesheet created successfully',
                'data' => $timesheet->load('entries')
            ], 201);
        });
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
                ->with(['user', 'client', 'mail', 'approver', 'entries']);

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

            $timesheet = Timesheet::with(['user', 'client', 'mail', 'approver', 'entries'])
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
            if (!in_array($timesheet->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected timesheets can be edited.'
                ], 400);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'client_id' => 'nullable|integer|exists:parties,id',
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
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'remarks' => $request->remarks,
                'mail_template_id' => $request->mail_template_id ?? null,
                'send_to' => $request->send_to ?? null,
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


            // Only allow delete draft timesheets
            if (!in_array($timesheet->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected timesheets can be deleted.'
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
            $userDetail = UserDetail::where([
                'id' => $timesheet->user_detail_id,
                'business_id' => $actor->business_id,
            ])->firstOrFail();

            $grossMargin = $timesheet->total_hours * $userDetail->client_rate;
            $expanse = $userDetail->w2 > 0 ?  ($timesheet->total_hours * $userDetail->other_rate) + ($timesheet->total_hours * $userDetail->w2 + ($userDetail->w2 * $userDetail->ptax) / 100) : ($timesheet->total_hours * $userDetail->other_rate) + ($timesheet->total_hours * $userDetail->c2c_or_other);
            $internalExpanse = $timesheet->total_hours * $userDetail->account_manager_commission + $timesheet->total_hours * $userDetail->business_development_manager_commission + $timesheet->total_hours * $userDetail->recruiter_commission;


            $updateData = [
                'status' => $request->status,
                'gross_margin' => $grossMargin,
                'expanse' => $expanse,
                'internal_expanse' => $internalExpanse,
                'net_margin' => $grossMargin - $expanse - $internalExpanse,
            ];

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
