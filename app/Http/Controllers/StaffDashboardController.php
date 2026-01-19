<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\Timesheet;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    /**
     * Get Staff Dashboard statistics and timesheets
     */
    public function view(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Find matching InternalUser by email and business
            $internalUser = InternalUser::where('email', $actor->email)
                ->where('business_id', $actor->business_id)
                ->first();

            if (!$internalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal user record not found for this staff member.'
                ], 404);
            }

            $role = $internalUser->role; // ac_manager, bd_manager, recruiter
            $internalUserId = $internalUser->id;

            // Base query for timesheets managed by this staff member
            $query = Timesheet::with(['user', 'userDetail', 'client'])
                ->where('business_id', $actor->business_id);

            // Filter by staff member's role in UserDetail
            $query->whereHas('userDetail', function ($q) use ($role, $internalUserId) {
                if ($role === 'ac_manager') {
                    $q->where('account_manager_id', $internalUserId);
                } elseif ($role === 'bd_manager') {
                    $q->where('business_development_manager_id', $internalUserId);
                } elseif ($role === 'recruiter') {
                    $q->where('recruiter_id', $internalUserId);
                }
            });

            // Get total managed timesheets count
            $totalManaged = (clone $query)->count();

            // Get total hours managed
            $totalHours = (clone $query)->sum('total_hours');

            // Get summary (Gross Margin, Net Margin, Expenses)
            $summary = (clone $query)->selectRaw('
                    SUM(gross_margin) as total_gross_margin,
                    SUM(net_margin) as total_net_margin,
                    SUM(expanse) as total_expanse
                ')->first();

            // Get the list of timesheets
            $timesheets = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'staff_info' => [
                        'name' => $internalUser->name,
                        'role' => $role,
                        'email' => $internalUser->email,
                    ],
                    'stats' => [
                        'total_managed_timesheets' => $totalManaged,
                        'total_hours' => $totalHours,
                        'total_gross_margin' => $summary->total_gross_margin ?? 0,
                        'total_net_margin' => $summary->total_net_margin ?? 0,
                        'total_expanse' => $summary->total_expanse ?? 0,
                    ],
                    'timesheets' => $timesheets->map(function ($ts) {
                        return [
                            'id' => $ts->id,
                            'consultant_name' => $ts->user ? $ts->user->name : 'N/A',
                            'client_name' => $ts->client ? $ts->client->party_name : 'N/A',
                            'start_date' => $ts->start_date,
                            'end_date' => $ts->end_date,
                            'total_hours' => $ts->total_hours,
                            'status' => $ts->status,
                            'gross_margin' => $ts->gross_margin,
                            'net_margin' => $ts->net_margin,
                            'expanse' => $ts->expanse,
                            'cost_data' => [
                                'w2' => $ts->userDetail ? $ts->userDetail->w2 : null,
                                'c2c' => $ts->userDetail ? $ts->userDetail->c2c_or_other : null,
                            ]
                        ];
                    })
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch staff dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
