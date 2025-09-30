<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('installment_id')->constrained('installments')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('assessed_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('waived_at')->nullable();
            $table->string('waived_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'assessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
