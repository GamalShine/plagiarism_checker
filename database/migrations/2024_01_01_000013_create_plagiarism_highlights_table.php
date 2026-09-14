<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plagiarism_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plagiarism_check_id')->constrained()->onDelete('cascade');
            $table->foreignId('plagiarism_source_id')->constrained()->onDelete('cascade');
            $table->text('original_text');
            $table->text('matched_text')->nullable();
            $table->string('color_code');
            $table->integer('start_position')->default(0);
            $table->integer('end_position')->default(0);
            $table->decimal('match_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plagiarism_highlights');
    }
};
