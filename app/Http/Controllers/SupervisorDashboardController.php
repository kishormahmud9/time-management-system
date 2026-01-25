<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\UserDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    /**
     * Get data for Supervisor Dashboard
     */
    public function view(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $businessId = $actor->business_id;

            // 1. Monthly W2 vs C2C Stats (Current Year)
            $year = $request->input('year', Carbon::now()->year);
            
            $monthlyStats = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthName = Carbon::create()->month($m)->format('M');
                
                $w2Hours = Timesheet::where('business_id', $businessId)
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $m)
                    ->whereHas('userDetail', function($q) {
                        $q->where('w2_or_c2c_type', 'W2');
                    })
                    ->sum('total_hours');

                $c2cHours = Timesheet::where('business_id', $businessId)
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $m)
                    ->whereHas('userDetail', function($q) {
                        $q->where('w2_or_c2c_type', 'C2C');
                    })
                    ->sum('total_hours');

                $monthlyStats[] = [
                    'month' => $monthName,
                    'w2' => (float)$w2Hours,
                    'c2c' => (float)$c2cHours
                ];
            }

            // 2. Status Stats (Pie Chart)
            $statusCounts = Timesheet::where('business_id', $businessId)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get();

            $pieData = [];
            $colors = [
                'approved' => '#1B654A',
                'pending' => '#F46B6A',
                'rejected' => '#E5D416',
                'reject' => '#E5D416',
                'submitted' => '#F46B6A',
            ];

            foreach ($statusCounts as $stat) {
                $status = strtolower($stat->status);
                $pieData[] = [
                    'name' => ucfirst($status),
                    'value' => (int)$stat->total,
                    'color' => $colors[$status] ?? '#8884d8'
                ];
            }

            // 3. Recent Timesheets
            $recentTimesheets = Timesheet::with(['client'])
                ->where('business_id', $businessId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($ts) {
                    return [
                        'id' => $ts->id,
                        'client' => $ts->client ? $ts->client->party_name : 'N/A',
                        'startDate' => Carbon::parse($ts->start_date)->format('d M Y'),
                        'endDate' => Carbon::parse($ts->end_date)->format('d M Y'),
                        'status' => ucfirst($ts->status),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_stats' => $monthlyStats,
                    'status_stats' => $pieData,
                    'recent_timesheets' => $recentTimesheets
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
