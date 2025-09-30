<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_records', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_records', 'fee_structure_id')) {
                $table->foreignId('fee_structure_id')->nullable()->after('tenant_id')->constrained('fee_structures')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_records', function (Blueprint $table) {
            if (Schema::hasColumn('fee_records', 'fee_structure_id')) {
                $table->dropConstrainedForeignId('fee_structure_id');
            }
        });
    }
};
