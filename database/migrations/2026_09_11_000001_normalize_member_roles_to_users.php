<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'member')
            ->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Existing member roles cannot be restored reliably after normalization.
    }
};
