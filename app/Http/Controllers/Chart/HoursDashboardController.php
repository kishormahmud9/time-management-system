<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Services\UserAccessService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HoursDashboardController extends Controller
{
    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Get consolidated hours dashboard data
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
                ->with(['user', 'client', 'userDetail.accountManager', 'userDetail.businessDevelopmentManager', 'userDetail.recruiter', 'userDetail.party']);

            // 2. Apply Filters
            if ($request->has('year')) {
                $query->whereYear('start_date', $request->year);
            }
            if ($request->has('user_id') && $request->user_id !== 'All user') {
                $query->where('user_id', $request->user_id);
            }
            if ($request->has('client_id') && $request->client_id !== 'All client') {
                $query->where('client_id', $request->client_id);
            }

            $timesheetIds = $query->pluck('id');

            // 3. Process Data
            $barChart = $this->calculateBarChartData($timesheetIds, $request->year ?? date('Y'));
            $pieChart = $this->calculatePieChartData($query);
            $table = $this->calculateTableData($query->with(['user', 'client'])->orderBy('start_date', 'desc')->get());

            return response()->json([
                'success' => true,
                'data' => [
                    'barChart' => $barChart,
                    'pieChart' => $pieChart,
                    'table' => $table
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hours dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateBarChartData($timesheetIds, $year)
    {
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        
        $aggregates = TimesheetEntry::whereIn('timesheet_id', $timesheetIds)
            ->whereYear('entry_date', $year)
            ->select(
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('SUM(daily_hours) as daily'),
                DB::raw('SUM(extra_hours) as extra'),
                DB::raw('SUM(vacation_hours) as vacation')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $data = [];
        foreach (range(1, 12) as $m) {
            $entry = $aggregates->get($m);
            $data[] = [
                'month' => $months[$m - 1],
                'daily' => $entry ? round((float)$entry->daily, 2) : 0,
                'extra' => $entry ? round((float)$entry->extra, 2) : 0,
                'vacation' => $entry ? round((float)$entry->vacation, 2) : 0,
            ];
        }

        return $data;
    }

    private function calculatePieChartData($query)
    {
        $counts = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            ['name' => "Approved", 'value' => $counts->has('approved') ? $counts->get('approved')->total : 0, 'color' => "#1E5F43"],
            ['name' => "Submitted", 'value' => $counts->has('submitted') ? $counts->get('submitted')->total : 0, 'color' => "#F2E8A0"],
            ['name' => "Rejected", 'value' => $counts->has('rejected') ? $counts->get('rejected')->total : 0, 'color' => "#F3C1C1"],
        ];
    }

    private function calculateTableData($timesheets)
    {
        return $timesheets->map(function ($ts, $index) {
            $ud = $ts->userDetail;
            return [
                'id' => $ts->id,
                'no' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name_client' => ($ts->user->name ?? 'N/A') . ' / ' . ($ts->client->name ?? 'N/A'),
                'dailyHours' => round($ts->entries()->sum('daily_hours'), 2),
                'extraHours' => round($ts->entries()->sum('extra_hours'), 2),
                'vacationHours' => round($ts->entries()->sum('vacation_hours'), 2),
                'totalHours' => round($ts->total_hours, 2),
                'status' => ($ud && $ud->w2 > 0) ? 'W2 Consultant' : 'C2C Consultant',
                
                // Detailed data for Modal
                'modalData' => [
                    'consultant_name' => $ts->user->name ?? 'N/A',
                    'consultant_email' => $ts->user->email ?? 'N/A',
                    'client_name' => $ts->client->name ?? 'N/A',
                    'vendor_name' => $ud->party->name ?? 'N/A',
                    'employer_name' => $ud->employer_name ?? 'N/A',
                    'start_date' => $ts->start_date ? Carbon::parse($ts->start_date)->format('M-d-Y') : 'N/A',
                    'end_date' => $ts->end_date ? Carbon::parse($ts->end_date)->format('M-d-Y') : 'N/A',
                    'employer_phone' => $ud->employer_phone ?? 'N/A',
                    'address' => $ts->user->address ?? 'N/A',
                    'am_name' => $ud->accountManager->name ?? 'not available',
                    'bdm_name' => $ud->businessDevelopmentManager->name ?? 'not available',
                    'rec_name' => $ud->recruiter->name ?? 'not available',
                    'client_rate' => $ud->client_rate ?? 0,
                    'consultant_rate' => $ud->consultant_rate ?? 0,
                    'w2' => $ud->w2 ?? 0,
                    'ptax' => ($ud->ptax ?? 0) . '%',
                    'am_commission' => ($ud->account_manager_commission ?? 0) . ' Fix',
                    'bdm_commission' => ($ud->business_development_manager_commission ?? 0) . ' Fix',
                    'rec_commission' => ($ud->recruiter_commission ?? 0) . ' Fix',
                    'c2c_other' => ($ud->c2c_or_other ?? 0) . ' Fix'
                ]
            ];
        });
    }
}
