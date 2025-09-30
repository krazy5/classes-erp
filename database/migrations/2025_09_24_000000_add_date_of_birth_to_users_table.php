<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'date_of_birth')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('date_of_birth')->nullable()->after('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'date_of_birth')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('date_of_birth');
            });
        }
    }
};
