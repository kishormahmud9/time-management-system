<?php

namespace App\Mail;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimesheetSubmittedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Timesheet $timesheet;
    public array $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(Timesheet $timesheet, array $emailData = [])
    {
        $this->timesheet = $timesheet;
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailData['subject'] ?? 'Timesheet Submitted - ' . $this->timesheet->user->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.timesheet-submitted',
            with: [
                'timesheet' => $this->timesheet,
                'customBody' => $this->emailData['body'] ?? null,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
