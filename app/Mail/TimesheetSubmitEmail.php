<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimesheetSubmitEmail extends Mailable implements ShouldQueue
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
    private function processTemplate()
    {
        // Get user details for commission information
        $userDetail = $this->timesheet->userDetail;
        
        // Calculate totals from entries
        $dailyHours = $this->timesheet->entries->sum('daily_hours');
        $extraHours = $this->timesheet->entries->sum('extra_hours');
        $vacationHours = $this->timesheet->entries->sum('vacation_hours');
        
        // Calculate financial metrics
        $clientRate = $userDetail ? $userDetail->client_rate : 0;
        $payRate = 0;
        
        if ($userDetail) {
            // Calculate pay rate based on W2 or C2C
            if ($userDetail->w2 > 0) {
                $payRate = $userDetail->w2 + ($userDetail->w2 * $userDetail->ptax / 100);
            } elseif ($userDetail->c2c_or_other > 0) {
                $payRate = $userDetail->c2c_or_other;
            }
        }
        
        $totalBillAmount = ($this->timesheet->total_hours ?? 0) * $clientRate;
        $totalPayAmount = ($this->timesheet->total_hours ?? 0) * $payRate;
        $netMarginPercentage = $totalBillAmount > 0 
            ? (($totalBillAmount - $totalPayAmount) / $totalBillAmount) * 100 
            : 0;

        // Prepare data array for placeholder replacement
        $data = [
            // User Information
            '{user_name}'        => $this->timesheet->user->name ?? 'N/A',
            '{user_email}'       => $this->timesheet->user->email ?? 'N/A',
            
            // Timesheet Information
            '{start_date}'       => $this->timesheet->start_date ?? 'N/A',
            '{end_date}'         => $this->timesheet->end_date ?? 'N/A',
            '{status}'           => ucfirst($this->timesheet->status ?? 'N/A'),
            '{remarks}'          => $this->timesheet->remarks ?? 'N/A',
            
            // Client Information
            '{client_name}'      => $this->timesheet->client->name ?? 'N/A',
            
            // Hours Breakdown
            '{total_hours}'      => number_format($this->timesheet->total_hours ?? 0, 2),
            '{daily_hours}'      => number_format($dailyHours, 2),
            '{extra_hours}'      => number_format($extraHours, 2),
            '{vacation_hours}'   => number_format($vacationHours, 2),
            
            // Financial Information
            '{total_bill_amount}' => number_format($totalBillAmount, 2),
            '{total_pay_amount}'  => number_format($totalPayAmount, 2),
            '{gross_margin}'     => number_format($this->timesheet->gross_margin ?? 0, 2),
            '{expanse}'          => number_format($this->timesheet->expanse ?? 0, 2),
            '{net_margin}'       => number_format($netMarginPercentage, 2),
            '{internal_expanse}' => number_format($this->timesheet->internal_expanse ?? 0, 2),
            
            // Rates (from user details)
            '{client_rate}'      => $userDetail ? number_format($userDetail->client_rate ?? 0, 2) : '0.00',
            '{consultant_rate}'  => $userDetail ? number_format($userDetail->consultant_rate ?? 0, 2) : '0.00',
            '{w2_rate}'          => $userDetail ? number_format($userDetail->w2 ?? 0, 2) : '0.00',
            '{c2c_rate}'         => $userDetail ? number_format($userDetail->c2c_or_other ?? 0, 2) : '0.00',
            '{ptax}'             => $userDetail ? number_format($userDetail->ptax ?? 0, 2) : '0.00',
            
            // Commission Information
            '{am_commission}'    => $userDetail ? number_format($userDetail->account_manager_commission ?? 0, 2) : '0.00',
            '{bdm_commission}'   => $userDetail ? number_format($userDetail->business_development_manager_commission ?? 0, 2) : '0.00',
            '{recruiter_commission}' => $userDetail ? number_format($userDetail->recruiter_commission ?? 0, 2) : '0.00',
            
            // Additional Information
            '{approver_name}'    => $this->timesheet->approver->name ?? 'N/A',
            '{approved_by}'      => $this->timesheet->approved_by ?? 'N/A',
            '{created_at}'       => $this->timesheet->created_at ? $this->timesheet->created_at->format('Y-m-d') : 'N/A',
            '{updated_at}'       => $this->timesheet->updated_at ? $this->timesheet->updated_at->format('Y-m-d') : 'N/A',
        ];

        // Replace placeholders in subject and body
        $this->processedSubject = str_replace(array_keys($data), array_values($data), $this->template->subject);
        $this->processedBody = str_replace(array_keys($data), array_values($data), $this->template->body);
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
            view: 'emails.timesheet',
            with: [
                'body' => nl2br(e($this->processedBody)),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->timesheet->attachments) {
            foreach ($this->timesheet->attachments as $attachment) {
                $attachments[] = Attachment::fromStorage('public/' . $attachment->file_path)
                    ->as($attachment->original_filename)
                    ->withMime($attachment->file_type);
            }
        }

        return $attachments;
    }
}
