<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TimesheetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $timesheets;

    public function __construct($timesheets)
    {
        $this->timesheets = $timesheets;
    }

    public function collection()
    {
        return $this->timesheets;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Client',
            'Project',
            'Start Date',
            'End Date',
            'Total Hours',
            'Status',
            'Submitted At',
            'Approved At',
        ];
    }

    public function map($timesheet): array
    {
        return [
            $timesheet->id,
            $timesheet->user->name ?? 'N/A',
            $timesheet->client->name ?? 'N/A',
            $timesheet->project->name ?? 'N/A',
            $timesheet->start_date,
            $timesheet->end_date,
            $timesheet->total_hours,
            ucfirst($timesheet->status),
            $timesheet->submitted_at,
            $timesheet->approved_at,
        ];
    }
}
