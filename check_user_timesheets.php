<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Timesheet;

echo "=== USERS WITH 'USER' ROLE (Business 1) ===\n\n";

$users = User::with('roles')
    ->where('business_id', 1)
    ->get()
    ->filter(function($u) {
        return $u->roles && $u->roles->contains('name', 'User');
    });

foreach ($users as $user) {
    $timesheetCount = Timesheet::where('user_id', $user->id)->count();
    $hasTimesheets = $timesheetCount > 0 ? "✅ YES ($timesheetCount)" : "❌ NO";
    
    echo sprintf(
        "ID: %d | Name: %-20s | Email: %-30s | Timesheets: %s\n",
        $user->id,
        $user->name,
        $user->email,
        $hasTimesheets
    );
}

echo "\n=== TIMESHEETS BREAKDOWN ===\n";
$timesheets = Timesheet::with('user')->where('business_id', 1)->get();

foreach ($timesheets as $ts) {
    echo sprintf(
        "Timesheet ID: %d | User ID: %s | User Name: %s | Hours: %s\n",
        $ts->id,
        $ts->user_id ?: 'NULL',
        $ts->user ? $ts->user->name : 'N/A',
        $ts->total_hours
    );
}
