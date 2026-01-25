<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\UserDetail;
use App\Services\UserAccessService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ConsultantDashboardController extends Controller
{
    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Get consolidated consultant dashboard data
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
                ->with(['user', 'client', 'userDetail.accountManager', 'userDetail.businessDevelopmentManager', 'userDetail.recruiter']);

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

            $timesheets = $query->orderBy('start_date', 'desc')->get();

            // 3. Process Data
            $barChart = $this->calculateBarChartData($timesheets, $request->year ?? date('Y'));
            $pieChart = $this->calculatePieChartData($timesheets);
            $table = $this->calculateTableData($timesheets);

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
                'message' => 'Failed to fetch consultant dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateBarChartData($timesheets, $year)
    {
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $data = [];

        foreach (range(1, 12) as $index) {
            $monthTimesheets = $timesheets->filter(function ($ts) use ($index) {
                return Carbon::parse($ts->start_date)->month == $index;
            });

            $w2Hours = 0;
            $c2cHours = 0;

            foreach ($monthTimesheets as $ts) {
                $hours = (float)$ts->total_hours;
                if ($ts->userDetail) {
                    if ($ts->userDetail->w2 > 0) {
                        $w2Hours += $hours;
                    } else {
                        $c2cHours += $hours;
                    }
                }
            }

            $data[] = [
                'month' => $months[$index - 1],
                'w2' => round($w2Hours, 2),
                'c2c' => round($c2cHours, 2)
            ];
        }

        return $data;
    }

    private function calculatePieChartData($timesheets)
    {
        return [
            ['name' => "Approved", 'value' => $timesheets->where('status', 'approved')->count(), 'color' => "#1E5F43"],
            ['name' => "Submitted", 'value' => $timesheets->where('status', 'submitted')->count(), 'color' => "#F2E8A0"],
            ['name' => "Rejected", 'value' => $timesheets->where('status', 'rejected')->count(), 'color' => "#F3C1C1"],
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
                'dailyHours' => $ts->total_hours, // Simple map for now
                'extraHours' => "0.00",
                'vacationHours' => "0.00",
                'totalHours' => $ts->total_hours,
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
