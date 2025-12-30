<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $businessId = $actor->business_id;

            // Total Employees
            $totalEmployees = User::where('business_id', $businessId)->count();

            // Total Projects
            $totalProjects = Project::where('business_id', $businessId)->count();

            // Timesheet Stats
            $timesheetStats = Timesheet::where('business_id', $businessId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $submittedTimesheets = $timesheetStats['submitted'] ?? 0;
            $approvedTimesheets = $timesheetStats['approved'] ?? 0;
            $pendingApprovals = $submittedTimesheets; // Assuming submitted means pending approval

            // Monthly Overview (Last 6 months)
            $monthlyOverview = Timesheet::where('business_id', $businessId)
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("strftime('%Y-%m', created_at) as month, count(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_employees' => $totalEmployees,
                    'total_projects' => $totalProjects,
                    'submitted_timesheets' => $submittedTimesheets,
                    'approved_timesheets' => $approvedTimesheets,
                    'pending_approvals' => $pendingApprovals,
                    'monthly_overview' => $monthlyOverview
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
