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
        Schema::create('timesheet_defaults', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            // null = business-level default
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('user_details_id')
                ->nullable()
                ->constrained('user_details')
                ->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('time_sheet_period')->nullable(); // weekly, bi-weekly, monthly
            $table->decimal('total_hours', 10, 2)->default(0);

            $table->boolean('is_business_default')->default(false);

            $table->timestamps();
            
            // Explicitly NO unique constraint on business_id/user_id 
            // because we now have multiple records per user (one per period).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_defaults');
    }
};
