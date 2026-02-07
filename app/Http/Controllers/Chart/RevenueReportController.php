<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\UserDetail;
use App\Services\UserAccessService;
use App\Traits\UserActivityTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class RevenueReportController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Get consolidated dashboard data (Summary, Chart, and Table)
     */
    public function index(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // 1. Build Base Query for Timesheets
            $query = $this->access->filterByBusiness($actor, Timesheet::class)
                ->with(['user', 'client', 'userDetail', 'attachments']);

            // 2. Apply Header Filters
            $query = $this->applyDashboardFilters($query, $request);

            // 3. Fetch Timesheets
            $timesheets = $query->get();

            // 4. Calculate Data Parts
            $summary = $this->calculateSummary($timesheets);
            $chartData = $this->calculateChartData($timesheets);
            $tableData = $this->calculateTableData($timesheets);

            $this->logActivity('view_unified_revenue_dashboard');

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'chart' => $chartData,
                    'table' => $tableData
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

    /**
     * Apply header filters to the timesheet query
     */
    private function applyDashboardFilters($query, Request $request)
    {
        // STRICT: Only Approved timesheets
        $query->where('status', 'approved');

        // Year filter
        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // Month filter (YYYY-MM)
        if ($request->has('month')) {
            $query->whereRaw('DATE_FORMAT(start_date, "%Y-%m") = ?', [$request->month]);
        }

        // User (Consultant) filter
        if ($request->has('user_id') && $request->user_id !== 'All user') {
            \Log::info('Revenue Dashboard Filter - user_id received:', ['user_id' => $request->user_id]);
            $query->where('user_id', $request->user_id);
        }

        // Consultant Type filter
        if ($request->has('consultant_type') && $request->consultant_type !== 'All') {
            $type = strtolower($request->consultant_type);
            $query->whereHas('userDetail', function ($q) use ($type) {
                if ($type === 'w2') {
                    $q->where('w2', '>', 0);
                } elseif ($type === 'c2c') {
                    $q->where('c2c_or_other', '>', 0);
                }
            });
        }

        // Internal User filter (Manager/Recruiter)
        if ($request->has('internal_user_id')) {
            $internalUserId = $request->internal_user_id;
            $query->whereHas('userDetail', function ($q) use ($internalUserId) {
                $q->where(function ($sub) use ($internalUserId) {
                    $sub->where('account_manager_id', $internalUserId)
                        ->orWhere('business_development_manager_id', $internalUserId)
                        ->orWhere('recruiter_id', $internalUserId);
                });
            });
        }

        return $query;
    }

    /**
     * Calculate global summary metrics
     */
    private function calculateSummary($timesheets)
    {
        $totalHours = 0;
        $totalGrossRevenue = 0;
        $totalExpense = 0;
        $totalCommission = 0;

        foreach ($timesheets as $ts) {
            $hours = (float)$ts->total_hours;
            $totalHours += $hours;

            if ($ts->userDetail) {
                $ud = $ts->userDetail;
                
                // Gross Revenue
                $totalGrossRevenue += $hours * $ud->client_rate;

                // Expense (Consolidating External Cost)
                $expense = $ud->w2 > 0
                    ? ($hours * $ud->consultant_rate) + ($hours * $ud->w2 + ($ud->w2 * $ud->ptax) / 100)
                    : ($hours * $ud->consultant_rate) + ($hours * $ud->c2c_or_other);
                
                $totalExpense += $expense;

                // Commission (Internal Expense)
                $totalCommission += ($hours * $ud->account_manager_commission) +
                                  ($hours * $ud->business_development_manager_commission) +
                                  ($hours * $ud->recruiter_commission);
            }
        }

        $grossMargin = $totalGrossRevenue - $totalExpense;
        $netMargin = $grossMargin - $totalCommission;

        return [
            'total_gross_revenue' => round($totalGrossRevenue, 2),
            'total_expense' => round($totalExpense, 2),
            'total_gross_margin' => round($grossMargin, 2),
            'total_net_margin' => round($netMargin, 2),
            'total_commission' => round($totalCommission, 2),
            'total_hours' => round($totalHours, 2)
        ];
    }

    /**
     * Calculate chart data (Monthly trend)
     */
    private function calculateChartData($timesheets)
    {
        $grouped = $timesheets->groupBy(function ($ts) {
            return Carbon::parse($ts->start_date)->format('M');
        });

        $chart = [];
        // Ensure month order if needed, but for now simple map
        foreach ($grouped as $month => $monthTimesheets) {
            $summary = $this->calculateSummary($monthTimesheets);
            $chart[] = [
                'label' => $month,
                'revenue' => $summary['total_net_margin'], // Net Revenue as per UI "Revenue" line
                'expense' => $summary['total_expense']
            ];
        }

        return $chart;
    }

    /**
     * Calculate tabular report data (per user/client)
     */
    private function calculateTableData($timesheets)
    {
        // Group by user to satisfy "User-wise" ledger requirement
        $grouped = $timesheets->groupBy('user_id');

        return $grouped->map(function ($userTimesheets) {
            $first = $userTimesheets->first();
            $userSummary = $this->calculateSummary($userTimesheets);
            
            // Step 2 & 3: Individual timesheet rows with detailed breakdown
            $timesheetRows = $userTimesheets->map(function($ts) {
                $tsSummary = $this->calculateSummary(collect([$ts]));
                
                return [
                    'id' => $ts->id,
                    'period' => Carbon::parse($ts->start_date)->format('d M Y') . ' to ' . Carbon::parse($ts->end_date)->format('d M Y'),
                    'total_hours' => $tsSummary['total_hours'],
                    'revenue' => $tsSummary['total_gross_revenue'], // Bill Amount
                    'expense' => $tsSummary['total_expense'],       // Cost Amount
                    'gross_margin' => $tsSummary['total_gross_margin'],
                    'net_margin' => $tsSummary['total_net_margin'],
                    'commission' => $tsSummary['total_commission'],
                    'subject' => $ts->subject,
                    'status' => $ts->status,
                ];
            });

            return [
                'user_info' => $first->user,
                'user_detail' => $first->userDetail,
                'summary' => $userSummary,
                'timesheets' => $timesheetRows
            ];
        })->values();
    }
}
