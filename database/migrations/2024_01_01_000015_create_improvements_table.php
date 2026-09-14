<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('improvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plagiarism_check_id')->nullable()->constrained()->onDelete('set null');
            $table->longText('original_content');
            $table->longText('improved_content')->nullable();
            $table->json('suggestions')->nullable(); // Array of suggestions per sentence
            $table->enum('mode', ['automatic', 'manual'])->default('manual');
            $table->enum('status', ['pending', 'analyzing', 'completed', 'failed'])->default('pending');
            $table->string('file_path')->nullable();
            $table->decimal('original_similarity', 5, 2)->nullable();
            $table->decimal('improved_similarity', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('improvements');
    }
};
