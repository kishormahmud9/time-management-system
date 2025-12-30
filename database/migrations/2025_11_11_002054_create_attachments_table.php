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
        Schema::create('attachments', function (Blueprint $table) {
             $table->id();

    $table->foreignId('business_id')
          ->constrained('businesses')
          ->cascadeOnDelete();

    $table->foreignId('timesheet_id')
          ->constrained('timesheets')
          ->cascadeOnDelete();

    $table->string('file_name')->nullable();
    $table->string('file_path')->nullable(); // e.g., S3 key or storage path

    $table->foreignId('uploaded_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

    $table->timestamps();

    $table->index('timesheet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
