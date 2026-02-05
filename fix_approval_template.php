<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check current approval templates
echo "=== CURRENT APPROVAL TEMPLATES ===\n\n";
$templates = DB::table('email_templates')
    ->where('template_type', 'timesheet_approve')
    ->get(['id', 'template_name', 'subject', 'body']);

foreach ($templates as $template) {
    echo "ID: {$template->id}\n";
    echo "Name: {$template->template_name}\n";
    echo "Subject: {$template->subject}\n";
    echo "Body Preview: " . substr($template->body, 0, 200) . "...\n";
    echo "Has {timesheet_table}? " . (strpos($template->body, '{timesheet_table}') !== false ? 'YES' : 'NO') . "\n";
    echo "---\n\n";
}

// Update ALL approval templates to include the table
$newBody = "Hello,\n\n" .
           "Your timesheet has been approved.\n\n" .
           "Consultant: {user_name}\n" .
           "Client: {client_name}\n" .
           "Period: {start_date} to {end_date}\n\n" .
           "--- TIMESHEET DETAILS ---\n" .
           "{timesheet_table}\n\n" .
           "--- FINANCIAL SUMMARY ---\n" .
           "Total Hours: {total_hours}\n" .
           "Total Bill Amount: \${total_bill_amount}\n" .
           "Total Pay Amount: \${total_pay_amount}\n" .
           "Gross Margin: \${gross_margin}\n" .
           "Net Margin: {net_margin}%\n\n" .
           "Thank you.";

$result = DB::table('email_templates')
    ->where('template_type', 'timesheet_approve')
    ->update([
        'body' => $newBody
    ]);

echo "\n✅ Updated $result template(s)\n\n";

// Verify update
echo "=== AFTER UPDATE ===\n\n";
$updatedTemplates = DB::table('email_templates')
    ->where('template_type', 'timesheet_approve')
    ->get(['id', 'template_name', 'body']);

foreach ($updatedTemplates as $template) {
    echo "ID: {$template->id}\n";
    echo "Name: {$template->template_name}\n";
    echo "Has {timesheet_table}? " . (strpos($template->body, '{timesheet_table}') !== false ? 'YES ✅' : 'NO ❌') . "\n";
    echo "---\n\n";
}
