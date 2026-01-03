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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id(); // same as bigIncrements('id')

            // relations
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // same as onDelete('set null')

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('user_detail_id')
                ->constrained('user_details')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('parties')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // columns
            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('gross_margin', 10, 2)->nullable();
            $table->decimal('net_margin', 10, 2)->nullable();

            $table->decimal('account_manager_commission_amount', 10, 2)->nullable();
            $table->decimal('bdm_commission_amount', 10, 2)->nullable();
            $table->decimal('recruiter_commission_amount', 10, 2)->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                ->default('draft');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('approved_at')->nullable();

            $table->timestampsTz();

            // indexes
            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index(['business_id', 'user_detail_id']);
            $table->index('status');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
