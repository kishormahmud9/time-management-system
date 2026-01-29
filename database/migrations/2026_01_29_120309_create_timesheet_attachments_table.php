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
        Schema::create('timesheet_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timesheet_id')
                ->constrained('timesheets')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // in bytes
            $table->timestamps();
            
            $table->index('timesheet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_attachments');
    }
};
