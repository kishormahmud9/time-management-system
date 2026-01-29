<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== ALL USERS IN DATABASE ===\n\n";

$users = User::with('roles')->orderBy('id')->get();

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->implode(', ');
    echo sprintf("ID: %d | Name: %-20s | Email: %-30s | Roles: %s\n", 
        $user->id, 
        $user->name, 
        $user->email,
        $roles
    );
}

echo "\n=== USERS NAMED 'simple User' ===\n";
$simpleUsers = User::where('name', 'simple User')->get();
foreach ($simpleUsers as $user) {
    echo sprintf("ID: %d | Email: %s\n", $user->id, $user->email);
}

echo "\n=== TOTAL COUNT ===\n";
echo "Total users: " . User::count() . "\n";
echo "Users named 'simple User': " . User::where('name', 'simple User')->count() . "\n";
