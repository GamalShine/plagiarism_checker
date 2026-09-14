<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plagiarism_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plagiarism_check_id')->constrained()->onDelete('cascade');
            $table->string('source_name'); // web, google_scholar, elsevier, openalex, crossref, crossref_posted, publications
            $table->string('source_label'); // Display name
            $table->decimal('similarity_score', 5, 2)->default(0);
            $table->string('url')->nullable();
            $table->text('title')->nullable();
            $table->text('snippet')->nullable();
            $table->string('authors')->nullable();
            $table->string('published_year')->nullable();
            $table->string('color_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plagiarism_sources');
    }
};
