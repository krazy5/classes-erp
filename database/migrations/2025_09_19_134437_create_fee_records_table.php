<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 8, 2);   // total fee amount for the student
            $table->boolean('is_paid')->default(false);  // flag if fully paid or not (optional)
            $table->timestamps();
            $table->softDeletes();  // adds deleted_at for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_records');
    }
};
