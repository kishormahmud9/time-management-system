<?php

namespace App\Services;

use App\Models\UserDetail;
use App\Models\TimesheetDefault;
use App\Models\TimesheetDefaultEntry;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimesheetDefaultService
{
    /**
     * Synchronize timesheet defaults based on user detail contract.
     */
    public function syncDefaults(UserDetail $userDetail)
    {
        if (!$userDetail->start_date || !$userDetail->time_sheet_period) {
            return;
        }

        DB::transaction(function () use ($userDetail) {
            $startDate = Carbon::parse($userDetail->start_date);
            $endDate = $userDetail->end_date ? Carbon::parse($userDetail->end_date) : Carbon::now()->addYear();
            $frequency = $userDetail->time_sheet_period;

            $currentStart = $startDate->copy();

            while ($currentStart->lte($endDate)) {
                $currentEnd = $this->calculatePeriodEnd($currentStart, $frequency, $endDate);

                // 1. Create/Update the TimesheetDefault record for THIS period
                $tsDefault = TimesheetDefault::updateOrCreate(
                    [
                        'user_details_id' => $userDetail->id,
                        'start_date' => $currentStart->toDateString(),
                        'end_date' => $currentEnd->toDateString(),
                    ],
                    [
                        'business_id' => $userDetail->business_id,
                        'user_id' => $userDetail->user_id,
                        'time_sheet_period' => $frequency,
                        'is_business_default' => false,
                    ]
                );

                // 2. Ensure daily default entries exist for this period
                $this->populateDefaultEntries($tsDefault);

                // 3. Update total hours for the period
                $tsDefault->updateTotalHours();

                $currentStart = $currentEnd->copy()->addDay();
            }
        });
    }

    /**
     * Populate daily default entries (Mon-Fri = 8h, Sat-Sun = 0h).
     */
    private function populateDefaultEntries(TimesheetDefault $tsDefault)
    {
        $start = Carbon::parse($tsDefault->start_date);
        $end = Carbon::parse($tsDefault->end_date);

        // Fetch user's custom weekend from userDetail
        $weekend = $tsDefault->userDetail ? $tsDefault->userDetail->weekend : null;

        // If no custom weekend is set, default to Saturday and Sunday
        if (!$weekend || !is_array($weekend)) {
            $weekend = ['Saturday', 'Sunday'];
        }

        $date = $start->copy();
        while ($date->lte($end)) {
            $dayOfWeek = $date->dayOfWeek; // Carbon: 0=Sun, 1=Mon, ..., 6=Sat
            $dayName = $date->format('l'); // Full day name (e.g., "Saturday")

            // Check if current day is in user's weekend list
            $isWeekend = in_array($dayName, $weekend);
            $hours = $isWeekend ? 0 : 8;

            TimesheetDefaultEntry::updateOrCreate(
                [
                    'timesheet_default_id' => $tsDefault->id,
                    'day_of_week' => $dayOfWeek, 
                ],
                [
                    'default_daily_hours' => $hours,
                    'default_extra_hours' => 0,
                    'default_vacation_hours' => 0,
                ]
            );
            $date->addDay();
        }
    }

    private function calculatePeriodEnd(Carbon $start, string $frequency, Carbon $limit)
    {
        $end = match (strtolower($frequency)) {
            'weekly' => $start->copy()->endOfWeek(),
            'bi-weekly' => $start->copy()->addDays(13),
            'monthly' => $start->copy()->endOfMonth(),
            default => $start->copy()->addDay(),
        };

        return $end->gt($limit) ? $limit : $end;
    }
}
