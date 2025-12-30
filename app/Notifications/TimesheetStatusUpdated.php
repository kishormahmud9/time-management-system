<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $timesheet;
    protected $status;

    public function __construct(Timesheet $timesheet, $status)
    {
        $this->timesheet = $timesheet;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
                    ->subject('Timesheet ' . ucfirst($this->status));

        if ($this->status === 'approved') {
            $message->line('Your timesheet has been approved.')
                    ->line('Approved By: ' . ($this->timesheet->approver->name ?? 'Manager'));
        } else {
            $message->line('Your timesheet has been rejected.')
                    ->line('Please review and resubmit.');
        }

        return $message->line('Period: ' . $this->timesheet->start_date . ' to ' . $this->timesheet->end_date)
                       ->action('View Timesheet', url('/timesheets/' . $this->timesheet->id));
    }
}
