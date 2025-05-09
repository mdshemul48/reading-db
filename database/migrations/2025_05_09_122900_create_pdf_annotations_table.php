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
        Schema::create('pdf_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->integer('page_number');
            $table->text('text_content')->nullable(); // The highlighted text
            $table->string('annotation_type')->default('highlight'); // highlight, note, etc.
            $table->text('note')->nullable(); // User's note about the highlight
            $table->json('position_data')->nullable(); // JSON data for positioning the highlight
            $table->string('color')->default('#ffff00'); // Default yellow highlight color
            $table->timestamps();

            // Index for faster lookups
            $table->index(['user_id', 'book_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_annotations');
    }
};
