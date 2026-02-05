<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING APPROVAL TEMPLATE ===\n\n";

$template = DB::table('email_templates')
    ->where('template_type', 'timesheet_approve')
    ->first();

if ($template) {
    echo "Template ID: {$template->id}\n";
    echo "Template Name: {$template->template_name}\n\n";
    echo "CURRENT BODY:\n";
    echo "---\n";
    echo $template->body;
    echo "\n---\n\n";
    
    $hasTable = strpos($template->body, '{timesheet_table}') !== false;
    echo "Has {timesheet_table}? " . ($hasTable ? "YES ✅" : "NO ❌") . "\n";
    
    if (!$hasTable) {
        echo "\n⚠️ PROBLEM: Template does NOT have {timesheet_table} placeholder!\n";
        echo "This is why the table is not showing in emails.\n";
    }
} else {
    echo "❌ NO APPROVAL TEMPLATE FOUND!\n";
}
