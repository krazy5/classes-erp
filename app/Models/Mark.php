<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mark extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'exam_subject_id', 'student_id', 'score'];

    protected $casts = [
        'score' => 'integer',
    ];

    // Relationships
    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
