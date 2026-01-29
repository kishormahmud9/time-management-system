<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== All Users with 'User' Role ===\n\n";

$users = User::with('roles')->get()->filter(function($u) {
    return $u->roles && $u->roles->contains('name', 'User');
});

foreach ($users as $user) {
    echo sprintf("ID: %d, Name: %s, Email: %s\n", $user->id, $user->name, $user->email);
}

echo "\n=== Checking specific users ===\n";
echo "User ID 6: " . (User::find(6) ? User::find(6)->name : 'Not found') . "\n";
echo "User ID 23: " . (User::find(23) ? User::find(23)->name : 'Not found') . "\n";
