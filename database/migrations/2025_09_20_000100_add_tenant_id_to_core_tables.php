<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'users',
        'students',
        'teachers',
        'class_groups',
        'subjects',
        'timetables',
        'attendances',
        'exams',
        'exam_subjects',
        'marks',
        'announcements',
        'fee_records',
        'installments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $column = $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();

                    if (Schema::hasColumn($tableName, 'id')) {
                        $column->after('id');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }
    }
};
