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

            $table->string('status')->default('draft');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('approved_at')->nullable();

            $table->timestampsTz();

            // indexes
            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index('status');
            $table->index('client_id');
            $table->timestamps();
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
