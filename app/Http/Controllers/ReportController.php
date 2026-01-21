<?php

namespace App\Http\Controllers;

use App\Exports\TimesheetExport;
use App\Models\Timesheet;
use App\Services\UserAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected UserAccessService $access;

    public function __construct(UserAccessService $access)
    {
        $this->access = $access;
    }

    /**
     * Generate report
     */
    public function generate(Request $request)
    {
        try {
            $actor = Auth::user();
            if (!$actor) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $query = $this->access->filterByBusiness($actor, Timesheet::class)
                ->with(['user', 'client', 'project', 'entries', 'userDetail']);

            // Filters
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            // Consultant type filter
            if ($request->has('consultant_type') && $request->consultant_type !== 'All') {
                $query->whereHas('userDetail', function ($q) use ($request) {
                    if ($request->consultant_type === 'W2') {
                        $q->where('w2', '>', 0);
                    } elseif ($request->consultant_type === 'C2C') {
                        $q->where('w2', '=', 0);
                    }
                });
            }

            // Manager filter
            if ($request->has('manager_id') && $request->manager_id !== 'All') {
                $query->whereHas('userDetail', function ($q) use ($request) {
                    $q->where(function($sq) use ($request) {
                        $sq->where('account_manager_id', $request->manager_id)
                          ->orWhere('business_development_manager_id', $request->manager_id)
                          ->orWhere('recruiter_id', $request->manager_id);
                    });
                });
            }

            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Month filter
            if ($request->has('month')) {
                if (preg_match('/^\d{4}-\d{2}$/', $request->month)) {
                    $query->where('start_date', 'like', $request->month . '%');
                } elseif (is_numeric($request->month)) {
                    $query->whereMonth('start_date', $request->month);
                }
            }

            // Year filter
            if ($request->has('year')) {
                $query->whereYear('start_date', $request->year);
            }

            $timesheets = $query->latest()->get();

            $type = $request->query('type', 'json');

            if ($type === 'pdf') {
                $pdf = Pdf::loadView('reports.timesheet', ['timesheets' => $timesheets, 'business' => $actor->business]);
                return $pdf->download('timesheet_report.pdf');
            } elseif ($type === 'excel') {
                return Excel::download(new TimesheetExport($timesheets), 'timesheet_report.xlsx');
            } elseif ($type === 'csv') {
                return Excel::download(new TimesheetExport($timesheets), 'timesheet_report.csv', \Maatwebsite\Excel\Excel::CSV);
            }

            return response()->json([
                'success' => true,
                'data' => $timesheets
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
