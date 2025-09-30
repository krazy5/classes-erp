<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'name'];

    // Relationships
    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_subjects')
                    ->withTimestamps()
                    ->withPivot('id', 'deleted_at')
                    ->wherePivotNull('deleted_at');
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}
