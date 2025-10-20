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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('language', 10)->default('en');

            $table->text('subject');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();

            $table->json('placeholders')->nullable();
            $table->string('insert_location')->nullable();
            $table->json('used_by')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('version')->default(1);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
