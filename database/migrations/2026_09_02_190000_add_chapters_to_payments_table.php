<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'chapters')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->json('chapters')->nullable()->after('sources');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'chapters')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('chapters');
            });
        }
    }
};