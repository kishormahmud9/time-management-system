<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timesheet Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .timesheet-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
            width: 40%;
            display: table-cell;
        }
        .detail-value {
            color: #111827;
            display: table-cell;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Timesheet Submitted</h1>
    </div>

    <div class="content">
        @if($customBody)
            <div style="margin-bottom: 20px;">
                {!! nl2br(e($customBody)) !!}
            </div>
        @else
            <p>Hello,</p>
            <p>A new timesheet has been submitted for your review.</p>
        @endif

        <div class="timesheet-details">
            <h3 style="margin-top: 0; color: #4F46E5;">Timesheet Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Employee:</span>
                <span class="detail-value">{{ $timesheet->user->name }}</span>
            </div>

            @if($timesheet->client)
            <div class="detail-row">
                <span class="detail-label">Client:</span>
                <span class="detail-value">{{ $timesheet->client->name }}</span>
            </div>
            @endif

            @if($timesheet->project)
            <div class="detail-row">
                <span class="detail-label">Project:</span>
                <span class="detail-value">{{ $timesheet->project->name }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="detail-label">Period:</span>
                <span class="detail-value">
                    {{ $timesheet->start_date->format('M d, Y') }} - {{ $timesheet->end_date->format('M d, Y') }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Total Hours:</span>
                <span class="detail-value">{{ number_format($timesheet->total_hours, 2) }} hours</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="text-transform: capitalize; font-weight: bold; color: #4F46E5;">
                    {{ $timesheet->status }}
                </span>
            </div>

            @if($timesheet->remarks)
            <div class="detail-row">
                <span class="detail-label">Remarks:</span>
                <span class="detail-value">{{ $timesheet->remarks }}</span>
            </div>
            @endif
        </div>

        <p>Please review and approve the timesheet at your earliest convenience.</p>

        <div class="footer">
            <p>Thank you!</p>
            <p style="font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>
