<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING APPROVAL EMAIL ISSUE ===\n\n";

// 1. Check the most recent approved timesheet
echo "1. MOST RECENT APPROVED TIMESHEET:\n";
echo "---\n";
$timesheet = DB::table('timesheets')
    ->where('status', 'approved')
    ->orderBy('approved_at', 'desc')
    ->first();

if ($timesheet) {
    echo "Timesheet ID: {$timesheet->id}\n";
    echo "User ID: {$timesheet->user_id}\n";
    echo "Status: {$timesheet->status}\n";
    echo "Send To: {$timesheet->send_to}\n";
    echo "Mail Template ID: {$timesheet->mail_template_id}\n";
    echo "Approved At: {$timesheet->approved_at}\n\n";
    
    // 2. Check what template was used
    if ($timesheet->mail_template_id) {
        echo "2. TEMPLATE THAT WAS USED (from timesheet record):\n";
        echo "---\n";
        $usedTemplate = DB::table('email_templates')
            ->where('id', $timesheet->mail_template_id)
            ->first();
        
        if ($usedTemplate) {
            echo "Template ID: {$usedTemplate->id}\n";
            echo "Template Name: {$usedTemplate->template_name}\n";
            echo "Template Type: {$usedTemplate->template_type}\n";
            $hasTable = strpos($usedTemplate->body, '{timesheet_table}') !== false;
            echo "Has {timesheet_table}? " . ($hasTable ? "YES ✅" : "NO ❌") . "\n";
            
            if (!$hasTable) {
                echo "\n⚠️ PROBLEM #1: The template used does NOT have {timesheet_table}!\n";
                echo "Body preview:\n";
                echo substr($usedTemplate->body, 0, 300) . "...\n";
            }
        }
    }
    
    // 3. Check what template SHOULD be used (approval template)
    echo "\n3. APPROVAL TEMPLATE THAT SHOULD BE USED:\n";
    echo "---\n";
    $approvalTemplate = DB::table('email_templates')
        ->where('template_type', 'timesheet_approve')
        ->whereNull('business_id')
        ->first();
    
    if ($approvalTemplate) {
        echo "Template ID: {$approvalTemplate->id}\n";
        echo "Template Name: {$approvalTemplate->template_name}\n";
        echo "Template Type: {$approvalTemplate->template_type}\n";
        $hasTable = strpos($approvalTemplate->body, '{timesheet_table}') !== false;
        echo "Has {timesheet_table}? " . ($hasTable ? "YES ✅" : "NO ❌") . "\n\n";
    } else {
        echo "❌ NO APPROVAL TEMPLATE FOUND!\n\n";
    }
    
    // 4. Check if there are timesheet entries
    echo "4. TIMESHEET ENTRIES:\n";
    echo "---\n";
    $entries = DB::table('timesheet_entries')
        ->where('timesheet_id', $timesheet->id)
        ->get();
    
    echo "Number of entries: " . count($entries) . "\n";
    if (count($entries) > 0) {
        echo "Sample entry:\n";
        $entry = $entries[0];
        echo "  Date: {$entry->entry_date}\n";
        echo "  Daily Hours: {$entry->daily_hours}\n";
        echo "  Extra Hours: {$entry->extra_hours}\n";
        echo "  Vacation Hours: {$entry->vacation_hours}\n";
    }
    
    // 5. Check TimesheetApprovalEmail code
    echo "\n5. CHECKING CODE:\n";
    echo "---\n";
    $controllerFile = __DIR__ . '/app/Http/Controllers/Timesheet/TimesheetManageController.php';
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if approval email uses correct template
    if (strpos($controllerContent, "where('template_type', 'timesheet_approve')") !== false) {
        echo "✅ Controller is configured to use 'timesheet_approve' template\n";
    } else {
        echo "❌ Controller is NOT using 'timesheet_approve' template!\n";
    }
    
    // Check if TimesheetApprovalEmail exists
    $mailableFile = __DIR__ . '/app/Mail/TimesheetApprovalEmail.php';
    if (file_exists($mailableFile)) {
        echo "✅ TimesheetApprovalEmail.php exists\n";
        $mailableContent = file_get_contents($mailableFile);
        
        if (strpos($mailableContent, '{timesheet_table}') !== false) {
            echo "✅ TimesheetApprovalEmail generates {timesheet_table}\n";
        } else {
            echo "❌ TimesheetApprovalEmail does NOT generate {timesheet_table}!\n";
        }
        
        if (strpos($mailableContent, 'dynamic_template') !== false) {
            echo "✅ Uses dynamic_template view\n";
        } else {
            echo "❌ Does NOT use dynamic_template view!\n";
        }
    } else {
        echo "❌ TimesheetApprovalEmail.php does NOT exist!\n";
    }
    
    echo "\n=== DIAGNOSIS ===\n";
    echo "The email you received was sent at: {$timesheet->approved_at}\n";
    echo "Check if this was BEFORE or AFTER the fix was applied.\n";
    echo "If BEFORE: The email is old, new approvals will work.\n";
    echo "If AFTER: There's still an issue with the code.\n";
    
} else {
    echo "No approved timesheets found!\n";
}
