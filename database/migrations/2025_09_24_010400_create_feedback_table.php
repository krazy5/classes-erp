<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 100)->nullable();
            $table->string('subject', 150);
            $table->text('message');
            $table->string('status', 50)->default('open');
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('responded_at')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
