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
        Schema::table('plagiarism_checks', function (Blueprint $table) {
            if (! Schema::hasColumn('plagiarism_checks', 'chapters')) {
                $table->json('chapters')->nullable()->after('sources_checked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plagiarism_checks', function (Blueprint $table) {
            if (Schema::hasColumn('plagiarism_checks', 'chapters')) {
                $table->dropColumn('chapters');
            }
        });
    }
};
