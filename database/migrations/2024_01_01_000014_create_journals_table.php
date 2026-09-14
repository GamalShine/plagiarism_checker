<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('author');
            $table->string('institution')->nullable();
            $table->string('email')->nullable();
            $table->text('abstract');
            $table->string('keywords');
            $table->longText('content'); // JSON: sections array
            $table->enum('template_type', ['template_a', 'template_b'])->default('template_a');
            $table->string('file_path_pdf')->nullable();
            $table->string('file_path_docx')->nullable();
            $table->enum('status', ['draft', 'generated'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
