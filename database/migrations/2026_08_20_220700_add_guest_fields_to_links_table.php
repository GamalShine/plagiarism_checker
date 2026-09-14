<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (!Schema::hasColumn('links', 'plagiarism_check_id')) {
                $table->foreignId('plagiarism_check_id')->nullable()->after('used_at')->constrained('plagiarism_checks')->nullOnDelete();
            }
            if (!Schema::hasColumn('links', 'used_ip')) {
                $table->string('used_ip', 45)->nullable()->after('used_at');
            }
            if (!Schema::hasColumn('links', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (Schema::hasColumn('links', 'plagiarism_check_id')) {
                $table->dropConstrainedForeignId('plagiarism_check_id');
            }
            if (Schema::hasColumn('links', 'used_ip')) {
                $table->dropColumn('used_ip');
            }
            if (Schema::hasColumn('links', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
