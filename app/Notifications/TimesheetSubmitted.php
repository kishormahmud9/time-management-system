<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $timesheet;

    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Timesheet Submitted')
                    ->line('A new timesheet has been submitted by ' . $this->timesheet->user->name)
                    ->line('Period: ' . $this->timesheet->start_date . ' to ' . $this->timesheet->end_date)
                    ->line('Total Hours: ' . $this->timesheet->total_hours)
                    ->action('View Timesheet', url('/timesheets/' . $this->timesheet->id))
                    ->line('Please review and approve.');
    }
}
