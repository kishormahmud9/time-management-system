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
        Schema::create('internal_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 20)->nullable()->index();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('image', 100)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->enum('role', ['bd_manager', 'ac_manager', 'recruiter'])->nullable();
            $table->enum('commission_on', ['gross-margin', 'net-margin'])->default('gross-margin');
            $table->enum('rate_type', ['percentage', 'fixed'])->default('percentage');
            $table->tinyInteger('recuesive')->default(0);
            $table->enum('month', ['all_months', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'])->nullable();
            $table->timestamps();

            // 🔐 multi-tenant uniqueness
            $table->unique(
                ['business_id', 'email'],
                'uniq_internal_user_email_per_business'
            );

            // ⚡ performance indexes
            $table->index('business_id');
            $table->index(['business_id', 'role'], 'idx_internal_users_business_role');
            $table->index(['business_id', 'phone'], 'idx_internal_users_business_phone');
            $table->index(['business_id', 'recuesive'], 'idx_internal_users_recursive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_users');
    }
};
