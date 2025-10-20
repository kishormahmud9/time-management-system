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
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('business_id')->nullable()->constrained('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null')->onUpdate('cascade');
            $table->integer('points')->default(0);
            $table->string('reason', 255)->nullable();
            $table->enum('transaction_type', ['increase', 'decrease'])->default('increase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_points');
    }
};
