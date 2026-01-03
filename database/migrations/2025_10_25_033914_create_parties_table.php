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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->index();
            $table->string('zip_code', 50);
            $table->string('address', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->enum('party_type', ['client', 'vendor', 'employee']);
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['business_id', 'phone'], 'uniq_party_phone_per_business');

            // performance
            $table->index(['business_id', 'party_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
