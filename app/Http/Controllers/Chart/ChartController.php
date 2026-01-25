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
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    use UserActivityTrait;

    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Get summary statistics for dashboard
     */
    public function summary(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Build base query with user_detail relationship
            $query = $this->access->filterByBusiness($actor, Timesheet::class)
                ->with('userDetail');

            // Apply filters
            $query = $this->applyFilters($query, $request, $actor);

            // Get timesheets
            $timesheets = $query->get();

            // Calculate metrics
            $stats = $this->calculateMetrics($timesheets, $actor);

            $this->logActivity('view_chart_summary');

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trend/time-series data for charts
     */
    public function trend(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            // Build base query with user_detail relationship
            $query = $this->access->filterByBusiness($actor, Timesheet::class)
                ->with('userDetail');

            // Apply filters
            $query = $this->applyFilters($query, $request, $actor);

            // Determine grouping
            $groupBy = $request->input('group_by', 'month');

            // Get timesheets
            $timesheets = $query->get();

            // Group by period and calculate
            $grouped = $timesheets->groupBy(function ($timesheet) use ($groupBy) {
                return $this->formatPeriod($timesheet->start_date, $groupBy);
            });

            $trends = $grouped->map(function ($periodTimesheets, $period) use ($actor) {
                $metrics = $this->calculateMetrics($periodTimesheets, $actor);
                return [
                    'period' => $period,
                    'timesheet_count' => $metrics['timesheet_count'],
                    'total_hours' => $metrics['total_hours'],
                    'gross_margin' => $metrics['total_gross_margin'],
                    'net_margin' => $metrics['total_net_margin'],
                    'expense' => $metrics['total_expense'],
                    'internal_expense' => $metrics['total_internal_expense'],
                ];
            })->sortBy('period')->values();

            // Calculate overall summary
            $summary = [
                'total_hours' => round($trends->sum('total_hours'), 2),
                'total_gross_margin' => round($trends->sum('gross_margin'), 2),
                'total_net_margin' => round($trends->sum('net_margin'), 2),
                'total_expense' => round($trends->sum('expense'), 2),
                'total_internal_expense' => round($trends->sum('internal_expense'), 2),
                'total_timesheets' => $trends->sum('timesheet_count'),
            ];

            $this->logActivity('view_chart_trend');

            return response()->json([
                'success' => true,
                'data' => $trends,
                'summary' => $summary
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trend data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function applyFilters($query, Request $request, $actor)
    {
        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // User filter (for admins/staff)
        if ($request->has('user_id') && !$actor->hasRole('User')) {
            $query->where('user_id', $request->user_id);
        } elseif ($actor->hasRole('User')) {
            // Regular users see only their own data
            $query->where('user_id', $actor->id);
        }

        // Client filter
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Date filters
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Month filter (YYYY-MM format)
        if ($request->has('month')) {
            $query->whereRaw('DATE_FORMAT(start_date, "%Y-%m") = ?', [$request->month]);
        }

        // Year filter
        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        return $query;
    }

    /**
     * Calculate financial metrics for timesheets
     */
    private function calculateMetrics($timesheets, $actor)
    {
        $totalHours = 0;
        $totalGrossMargin = 0;
        $totalExpense = 0;
        $totalInternalExpense = 0;
        $totalNetMargin = 0;

        $statusCounts = [
            'approved' => 0,
            'submitted' => 0,
            'draft' => 0,
            'rejected' => 0,
        ];

        foreach ($timesheets as $timesheet) {
            $totalHours += (float) $timesheet->total_hours;
            $statusCounts[$timesheet->status] = ($statusCounts[$timesheet->status] ?? 0) + 1;

            if ($timesheet->userDetail) {
                $userDetail = $timesheet->userDetail;
                $grossMargin = $timesheet->total_hours * $userDetail->client_rate;
                $totalGrossMargin += $grossMargin;

                $expense = $userDetail->w2 > 0
                    ? ($timesheet->total_hours * $userDetail->other_rate) +
                      ($timesheet->total_hours * $userDetail->w2 + ($userDetail->w2 * $userDetail->ptax) / 100)
                    : ($timesheet->total_hours * $userDetail->other_rate) +
                      ($timesheet->total_hours * $userDetail->c2c_or_other);

                $totalExpense += $expense;

                $internalExpense = ($timesheet->total_hours * $userDetail->account_manager_commission) +
                                   ($timesheet->total_hours * $userDetail->business_development_manager_commission) +
                                   ($timesheet->total_hours * $userDetail->recruiter_commission);

                $totalInternalExpense += $internalExpense;
                $totalNetMargin += ($grossMargin - $expense - $internalExpense);
            }
        }

        return [
            'total_hours' => round($totalHours, 2),
            'total_gross_margin' => round($totalGrossMargin, 2),
            'total_net_margin' => round($totalNetMargin, 2),
            'total_expense' => round($totalExpense, 2),
            'total_internal_expense' => round($totalInternalExpense, 2),
            'timesheet_count' => $timesheets->count(),
            'approved_count' => $statusCounts['approved'],
            'pending_count' => $statusCounts['submitted'],
            'draft_count' => $statusCounts['draft'],
            'rejected_count' => $statusCounts['rejected'],
        ];
    }

    /**
     * Format date based on grouping type
     */
    private function formatPeriod($date, $groupBy)
    {
        $carbonDate = \Carbon\Carbon::parse($date);

        return match ($groupBy) {
            'day' => $carbonDate->format('Y-m-d'),
            'week' => $carbonDate->format('Y') . '-' . $carbonDate->weekOfYear,
            'month' => $carbonDate->format('Y-m'),
            'year' => $carbonDate->format('Y'),
            default => $carbonDate->format('Y-m'),
        };
    }

    /**
     * Get MySQL date format based on grouping type
     */
    private function getDateFormat($groupBy)
    {
        return match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };
    }
}
