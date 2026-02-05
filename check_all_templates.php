<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALL EMAIL TEMPLATES ===\n\n";

$templates = DB::table('email_templates')
    ->orderBy('id')
    ->get(['id', 'template_name', 'template_type', 'body']);

foreach ($templates as $template) {
    echo "ID: {$template->id}\n";
    echo "Name: {$template->template_name}\n";
    echo "Type: {$template->template_type}\n";
    
    $hasTable = strpos($template->body, '{timesheet_table}') !== false;
    echo "Has {timesheet_table}? " . ($hasTable ? "YES ✅" : "NO ❌") . "\n";
    
    if ($template->template_type === 'timesheet_approve' && !$hasTable) {
        echo "⚠️ THIS IS AN APPROVAL TEMPLATE WITHOUT TABLE!\n";
        echo "Body preview: " . substr($template->body, 0, 150) . "...\n";
    }
    
    echo "---\n\n";
}

// Check the most recent timesheet
echo "\n=== MOST RECENT TIMESHEET ===\n\n";
$timesheet = DB::table('timesheets')
    ->orderBy('id', 'desc')
    ->first();

if ($timesheet) {
    echo "Timesheet ID: {$timesheet->id}\n";
    echo "Status: {$timesheet->status}\n";
    echo "Mail Template ID: {$timesheet->mail_template_id}\n";
    
    if ($timesheet->mail_template_id) {
        $usedTemplate = DB::table('email_templates')
            ->where('id', $timesheet->mail_template_id)
            ->first();
        
        if ($usedTemplate) {
            echo "\nUsed Template:\n";
            echo "  Name: {$usedTemplate->template_name}\n";
            echo "  Type: {$usedTemplate->template_type}\n";
            $hasTable = strpos($usedTemplate->body, '{timesheet_table}') !== false;
            echo "  Has {timesheet_table}? " . ($hasTable ? "YES ✅" : "NO ❌") . "\n";
        }
    }
}
