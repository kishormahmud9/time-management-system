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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // =========================
            // Account Manager
            // =========================
            $table->float('account_manager_commission');
            $table->string('account_manager_commission_rate_count_on')->nullable();
            $table->integer('account_manager_commission_rate_type');
            $table->boolean('account_manager_recurssive')->default(false);
            $table->integer('account_manager_recurssive_month')->nullable();
            $table->foreignId('account_manager_id')
                ->nullable()
                ->constrained('internal_users');
            // =========================
            // Business Development Manager
            // =========================
            $table->float('business_development_manager_commission');
            $table->string('business_development_manager_commission_rate_count_on')->nullable();
            $table->integer('business_development_manager_commission_rate_type');
            $table->boolean('business_development_manager_recurssive')->default(false);
            $table->integer('business_development_manager_recurssive_month')->nullable();
            $table->foreignId('business_development_manager_id')
                ->nullable()
                ->constrained('internal_users');
            // =========================
            // Recruiter
            // =========================
            $table->float('recruiter_commission');
            $table->string('recruiter_rate_count_on')->nullable();
            $table->integer('recruiter_rate_type');
            $table->boolean('recruiter_recurssive')->default(false);
            $table->integer('recruiter_recurssive_month')->nullable();
            $table->foreignId('recruiter_id')
                ->nullable()
                ->constrained('internal_users');
            // =========================
            // Client / Employer / Vendor
            // =========================
            $table->foreignId('party_id')
                ->nullable()
                ->constrained('parties');

            // =========================
            // Rates & Contract
            // =========================
            $table->float('client_rate');
            $table->float('consultant_rate')->nullable();
            $table->float('w2')->nullable();
            $table->float('c2c_or_other')->nullable();
            $table->integer('w2_or_c2c_type')->nullable();

            $table->boolean('c2c_or_other_recurssive')->default(false);
            $table->integer('c2c_or_other_recurssive_month')->nullable();
            $table->integer('c2c_or_other_rate_type')->nullable();
            // =========================
            // Employer Info
            // =========================
            $table->string('employer_name')->nullable();
            $table->string('employer_email')->nullable();
            $table->string('employer_phone')->nullable();
            // =========================
            // Tax & Timesheet
            // =========================
            $table->float('ptax')->nullable();
            $table->string('time_sheet_period')->nullable();
            // =========================
            // Contract Period
            // =========================
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            // =========================
            // Misc
            // =========================
            $table->boolean('active')->nullable();
            $table->string('address')->nullable();
            $table->string('invoice_to')->nullable();
            $table->string('file_folder')->nullable();

            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->timestamps();


            // ⚡ performance indexes
            $table->index(['business_id', 'active'], 'idx_ud_business_active');

            $table->index(
                ['business_id', 'account_manager_id', 'active'],
                'idx_ud_business_am'
            );

            $table->index(
                ['business_id', 'business_development_manager_id', 'active'],
                'idx_ud_business_bdm'
            );

            $table->index(
                ['business_id', 'recruiter_id', 'active'],
                'idx_ud_business_recruiter'
            );

            $table->index(
                ['business_id', 'start_date', 'end_date'],
                'idx_ud_business_contract'
            );

            $table->index(
                ['business_id', 'party_id'],
                'idx_ud_business_party'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
