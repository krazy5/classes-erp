<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('fee_record_id')->constrained('fee_records')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255)->nullable();
            $table->date('granted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'granted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
