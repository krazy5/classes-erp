<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->morphs('payable');
            $table->decimal('amount', 12, 2);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('due_on')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
