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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('business_id')->nullable()->constrained('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('loyalty_card_id')->nullable()->constrained('loyalty_cards')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('total_visits')->default(0);
            $table->date('last_visit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
