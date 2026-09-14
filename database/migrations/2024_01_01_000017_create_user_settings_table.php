<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('dark_mode')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->json('default_sources')->nullable();
            $table->string('serpapi_key')->nullable();
            $table->string('elsevier_api_key')->nullable();
            $table->boolean('elsevier_enabled')->default(false);
            $table->string('google_cse_key')->nullable();
            $table->string('google_cse_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
