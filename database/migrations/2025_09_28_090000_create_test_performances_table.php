<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('assessment_type', 60)->nullable();
            $table->string('term', 60)->nullable();
            $table->date('test_date')->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 12)->nullable();
            $table->json('metadata')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'test_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_performances');
    }
};
