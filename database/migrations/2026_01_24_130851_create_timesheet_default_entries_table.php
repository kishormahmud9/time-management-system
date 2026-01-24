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
        Schema::create('timesheet_default_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('timesheet_default_id')
                ->constrained('timesheet_defaults')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

            $table->decimal('default_daily_hours', 6, 2)->default(8);
            $table->decimal('default_extra_hours', 6, 2)->default(0);
            $table->decimal('default_vacation_hours', 6, 2)->default(0);

            $table->timestamps();

            // One default entry per day per template
            $table->unique(['timesheet_default_id', 'day_of_week'], 'uniq_ts_def_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_default_entries');
    }
};
