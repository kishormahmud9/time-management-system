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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->date('holiday_date');
            $table->text('description')->nullable();
            $table->boolean('recurring')->default(false); // yearly recurring?

            $table->timestamps();

            // ✅ Multi-tenant safe unique constraint
            $table->unique(['business_id', 'holiday_date'], 'uniq_business_holiday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
