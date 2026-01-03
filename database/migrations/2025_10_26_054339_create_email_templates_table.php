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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100);
            $table->enum('template_type', [
                'timesheet_submit',
                'timesheet_approve',
                'timesheet_reject',
                'pending_timesheet_reminder',
                'general'
            ])->nullable();
            $table->text('subject');
            $table->longText('body');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->boolean('is_locked')->default(false);
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->timestamps();

            $table->unique(
                ['business_id', 'template_name', 'template_type'],
                'uniq_business_template'
            );

            // ⚡ performance
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'template_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
