<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('qr_code_id')->nullable()->after('present')->constrained('qr_codes')->nullOnDelete();
            $table->timestamp('marked_at')->nullable()->after('qr_code_id');
            $table->string('marked_via', 32)->nullable()->after('marked_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('qr_code_id');
            $table->dropColumn(['marked_at', 'marked_via']);
        });
    }
};
