<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('package_key')->nullable()->after('role');
            $table->unsignedInteger('package_credits')->default(0)->after('package_key');
            $table->timestamp('package_expires_at')->nullable()->after('package_credits');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('package_key')->nullable()->after('amount');
            $table->string('package_name')->nullable()->after('package_key');
            $table->unsignedInteger('package_quota')->nullable()->after('package_name');
            $table->unsignedInteger('package_days')->nullable()->after('package_quota');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['package_key', 'package_name', 'package_quota', 'package_days']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['package_key', 'package_credits', 'package_expires_at']);
        });
    }
};
