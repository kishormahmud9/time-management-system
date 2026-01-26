<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class UserDashboardController extends Controller
{
    /**
     * Get dashboard data for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // 1. Get User Details (for Weekend Settings)
            $userDetails = $user->userDetails;
            $weekend = $userDetails ? ($userDetails->weekend ?? ['Saturday', 'Sunday']) : ['Saturday', 'Sunday'];

            // 2. Timesheet Analytics (Pie Chart)
            $timesheetStats = Timesheet::where('user_id', $user->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $pieChart = [
                ['name' => 'Approved', 'value' => $timesheetStats['approved'] ?? 0, 'color' => '#1B654A'],
                ['name' => 'Pending', 'value' => $timesheetStats['submitted'] ?? 0, 'color' => '#F46B6A'],
                ['name' => 'Rejected', 'value' => $timesheetStats['rejected'] ?? 0, 'color' => '#E5D416'],
                ['name' => 'Draft', 'value' => $timesheetStats['draft'] ?? 0, 'color' => '#D9DFFF'],
            ];

            // 3. Hours Bar Chart Data (Last 7 Days)
            $barChart = $this->calculateBarChartData($user->id);

            // 4. Recent Timesheets (Table)
            $recentTimesheets = Timesheet::where('user_id', $user->id)
                ->with('client')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($ts) {
                    return [
                        'id' => $ts->id,
                        'client' => $ts->client->name ?? 'N/A',
                        'startDate' => Carbon::parse($ts->start_date)->format('d M Y'),
                        'endDate' => Carbon::parse($ts->end_date)->format('d M Y'),
                        'status' => ucfirst($ts->status),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'pieChart' => $pieChart,
                    'barChart' => $barChart,
                    'recentTimesheets' => $recentTimesheets,
                    'weekend' => $weekend
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateBarChartData($userId)
    {
        $data = [];
        // Last 7 days including today
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->format('D'); // Sat, Sun, Mon, etc.

            $entries = TimesheetEntry::whereHas('timesheet', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereDate('entry_date', $date)
            ->get();

            $daily = $entries->sum('daily_hours');
            $extra = $entries->sum('extra_hours');
            $vacation = $entries->sum('vacation_hours');

            $data[] = [
                'day' => $dayName,
                'daily' => (float) $daily,
                'extra' => (float) $extra,
                'vacation' => (float) $vacation,
            ];
        }

        return $data;
    }
}
