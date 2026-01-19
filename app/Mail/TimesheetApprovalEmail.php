<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimesheetApprovalEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $timesheet;
    public $template;
    public $processedBody;
    public $processedSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Timesheet $timesheet, EmailTemplate $template)
    {
        $this->timesheet = $timesheet;
        $this->template = $template;
        $this->processTemplate();
    }

    /**
     * Process the template placeholders.
     */
    protected function processTemplate()
    {
        $data = [
            '{user_name}'        => $this->timesheet->user->name ?? 'User',
            '{timesheet_period}' => ($this->timesheet->start_date ?? '') . ' to ' . ($this->timesheet->end_date ?? ''),
            '{total_hours}'      => $this->timesheet->total_hours ?? '0',
            '{status}'           => ucfirst($this->timesheet->status ?? 'approved'),
            '{remarks}'          => $this->timesheet->remarks ?? 'N/A',
            '{client_name}'      => $this->timesheet->client->name ?? 'N/A',
            '{gross_margin}'     => number_format($this->timesheet->gross_margin ?? 0, 2),
            '{expanse}'          => number_format($this->timesheet->expanse ?? 0, 2),
            '{net_margin}'       => number_format($this->timesheet->net_margin ?? 0, 2),
        ];

        $this->processedBody = str_replace(array_keys($data), array_values($data), $this->template->body);
        $this->processedSubject = str_replace(array_keys($data), array_values($data), $this->template->subject);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->processedSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.dynamic_template',
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
