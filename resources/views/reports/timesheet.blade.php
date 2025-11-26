<!DOCTYPE html>
<html>
<head>
    <title>Timesheet Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Timesheet Report</h1>
        @if($business)
            <h2>{{ $business->name }}</h2>
        @endif
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Project</th>
                <th>Period</th>
                <th>Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timesheets as $timesheet)
            <tr>
                <td>{{ $timesheet->user->name ?? 'N/A' }}</td>
                <td>{{ $timesheet->project->name ?? 'N/A' }}</td>
                <td>{{ $timesheet->start_date }} to {{ $timesheet->end_date }}</td>
                <td>{{ $timesheet->total_hours }}</td>
                <td>{{ ucfirst($timesheet->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
