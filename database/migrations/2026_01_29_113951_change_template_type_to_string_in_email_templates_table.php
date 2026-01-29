<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Change template_type from enum to string
            $table->string('template_type', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Revert back to enum (for rollback purposes)
            $table->enum('template_type', [
                'timesheet_submit',
                'timesheet_approve',
                'timesheet_reject',
                'pending_timesheet_reminder',
                'general'
            ])->nullable()->change();
        });
    }
};
