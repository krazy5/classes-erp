<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Exam extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['tenant_id', 'title', 'scheduled_at', 'class_group_id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_subjects')
                    ->withTimestamps()
                    ->withPivot('id', 'deleted_at')
                    ->wherePivotNull('deleted_at');
    }

    // Media collections (optional documents for the exam)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
    }
}
