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
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->cascadeOnDelete();

            $table->date('entry_date');

            $table->decimal('daily_hours', 6, 2)->default(0);
            $table->decimal('extra_hours', 6, 2)->default(0);
            $table->decimal('vacation_hours', 6, 2)->default(0);

            $table->text('note')->nullable();
            $table->boolean('is_locked')->default(false);

            $table->decimal('client_rate_snapshot', 10, 2)->nullable();
            $table->decimal('consultant_rate_snapshot', 10, 2)->nullable();


            $table->timestamps();

            // constraints & indexes
            $table->unique(['timesheet_id', 'entry_date'], 'ts_entries_unique_timesheet_date');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
