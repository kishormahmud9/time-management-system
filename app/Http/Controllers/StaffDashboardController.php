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

            // Base query for timesheets in this business
            $query = Timesheet::with(['user', 'userDetail', 'client'])
                ->where('business_id', $actor->business_id);

            // Optional Filter by Internal User ID
            if ($request->filled('internal_user_id')) {
                $internalUserId = $request->internal_user_id;
                $query->whereHas('userDetail', function ($q) use ($internalUserId) {
                    $q->where('account_manager_id', $internalUserId)
                        ->orWhere('business_development_manager_id', $internalUserId)
                        ->orWhere('recruiter_id', $internalUserId);
                });
            }

            // Get staff info if filtered
            $staffInfo = null;
            if ($request->filled('internal_user_id')) {
                $iu = InternalUser::find($request->internal_user_id);
                if ($iu) {
                    $staffInfo = [
                        'name' => $iu->name,
                        'role' => $iu->role,
                        'email' => $iu->email,
                    ];
                }
            }

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
                    'staff_info' => $staffInfo,
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
