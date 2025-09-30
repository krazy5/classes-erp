<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('title');
            }

            if (!Schema::hasColumn('exams', 'class_group_id')) {
                $table->foreignId('class_group_id')->nullable()->after('scheduled_at')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'class_group_id')) {
                $table->dropConstrainedForeignId('class_group_id');
            }

            if (Schema::hasColumn('exams', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
