<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Timesheet;
use App\Models\User;

echo "=== Checking Timesheets ===\n\n";

$userIds = Timesheet::select('user_id')->distinct()->pluck('user_id');

echo "Users with timesheets:\n";
foreach ($userIds as $userId) {
    $user = User::find($userId);
    $count = Timesheet::where('user_id', $userId)->count();
    echo "- User ID: $userId, Name: " . ($user ? $user->name : 'Unknown') . ", Timesheets: $count\n";
}

echo "\n=== Checking User 23 (simple User) ===\n";
$user23 = User::find(23);
if ($user23) {
    echo "User found: {$user23->name}\n";
    echo "Timesheets count: " . Timesheet::where('user_id', 23)->count() . "\n";
} else {
    echo "User  23 not found\n";
}
