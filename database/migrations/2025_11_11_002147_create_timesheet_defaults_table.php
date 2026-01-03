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

            $table->decimal('default_daily_hours', 6, 2)->default(8);
            $table->decimal('default_extra_hours', 6, 2)->default(0);
            $table->decimal('default_vacation_hours', 6, 2)->default(0);

            // explicit meaning
            $table->boolean('is_business_default')->default(false);

            $table->timestamps();

            // enforce one rule per scope
            $table->unique(['business_id', 'user_id'], 'uniq_timesheet_defaults');
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
