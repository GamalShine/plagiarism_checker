<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('snap_token')->nullable();

            // Temporary file data (before check runs)
            $table->string('temp_file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->json('sources')->nullable();

            // Pricing
            $table->integer('amount'); // in IDR
            $table->string('currency')->default('IDR');

            // Midtrans status: pending | paid | failed | expired
            $table->string('status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('midtrans_payload')->nullable();

            // Link to plagiarism check (after payment success)
            $table->foreignId('plagiarism_check_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
