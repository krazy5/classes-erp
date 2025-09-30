<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->nullable();  // Student's score in that exam for that subject
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_subject_id', 'student_id']);  // One mark per student per exam-subject
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
