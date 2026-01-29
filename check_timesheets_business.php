<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Timesheet;
use App\Models\Client;

echo "=== CURRENT LOGGED USER ===\n";
// Simulating John Doe login - need to check who is logged in
$currentUser = User::where('email', 'john@demotech.com')->first();
if (!$currentUser) {
    echo "User john@demotech.com not found, trying other emails...\n";
    $currentUser = User::first();
}

echo "Logged in as: " . ($currentUser ? $currentUser->name . " (ID: {$currentUser->id}, Business: {$currentUser->business_id})" : "No user") . "\n\n";

echo "=== ALL TIMESHEETS ===\n";
$timesheets = Timesheet::with(['user', 'client'])->get();

foreach ($timesheets as $ts) {
    $userName = $ts->user ? $ts->user->name : 'N/A';
    $userBusinessId = $ts->user ? $ts->user->business_id : 'N/A';
    $clientName = $ts->client ? $ts->client->name : 'Unknown';
    
    echo sprintf(
        "Timesheet ID: %d | User: %s (ID:%s, Business:%s) | Client: %s | Hours: %s\n",
        $ts->id,
        $userName,
        $ts->user_id,
        $userBusinessId,
        $clientName,
        $ts->total_hours
    );
}

echo "\n=== USERS WITH TIMESHEETS (Grouped) ===\n";
$userIds = Timesheet::select('user_id')->distinct()->pluck('user_id');

foreach ($userIds as $userId) {
    if ($userId) {
        $user = User::find($userId);
        $count = Timesheet::where('user_id', $userId)->count();
        echo sprintf(
            "User ID: %d | Name: %s | Business ID: %s | Timesheet Count: %d\n",
            $userId,
            $user ? $user->name : 'NOT FOUND',
            $user ? $user->business_id : 'N/A',
            $count
        );
    } else {
        $count = Timesheet::whereNull('user_id')->count();
        echo "User ID: NULL | Timesheet Count: $count\n";
    }
}
